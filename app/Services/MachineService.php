<?php


namespace App\Services;


use App\Models\Image;
use App\Models\Machine;
use App\Models\Plan;
use App\Repositories\ImageRepository;
use App\Repositories\MachineRepository;
use OpenStack\OpenStack;

class MachineService
{
    private $openstack;
    private $compute;

    function __construct()
    {
        return;
        $this->openstack = new OpenStack([
            'authUrl' => config('openstack.authUrl'),
            'region' => config('openstack.region'),
            'user' => [
                'id' => config('openstack.userId'),
                'password' => config('openstack.password')
            ],
            'scope' => ['project' => ['id' => config('openstack.projectId')]]
        ]);

        $this->compute = $this->openstack->computeV2(['region' => config('openstack.region')]);
    }

    function createMachineFromImage(string $name,int $user_id,int $plan_id,int $image_id,$ssh_key_id=null): bool
    {
        $image = Image::find($image_id);
        $plan = Plan::find($plan_id);
        $machine = MachineRepository::createMachine($name,$user_id,$plan_id,$image_id,$ssh_key_id);
        return true;

        $options = [
            // Required
            'name' => $name,
            'imageId' => $image->remote_id,
            'flavorId' => $plan->remote_id,

            // Required if multiple network is defined
            'networks' => [
                ['uuid' => config('openstack.networkId')]
            ],
        ];

        // Create the server
        /**@var OpenStack\Compute\v2\Models\Server $server */
        $server = $this->compute->createServer($options);
    }

    function powerOn(string $id)
    {
        $server = $this->compute->getServer(['id' => $id]);
        $server->start();
    }

    function powerOff(string $id)
    {
        $server = $this->compute->getServer(['id' => $id]);
        $server->stop();
    }

    function console(string $id)
    {
        $server = $this->compute->getServer(['id' => $id]);
        return $server->getConsoleOutput();
    }

    function rename(string $id,string $newname){
        $server = $this->compute->getServer(['id' => $id]);
        $server->name = $newname;
        $server->update();
    }

    function takeSnapshot(string $id,string $name){

    }

    function remove(string $id){
        $server = $this->compute->getServer(['id' => $id]);
        $server->delete();
    }

}

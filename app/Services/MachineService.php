<?php


namespace App\Services;


use OpenStack\OpenStack;

class MachineService
{
    private $openstack;
    private $compute;

    function __construct()
    {
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

    function createMachineFromImage(): bool
    {
        $options = [
            // Required
            'name' => '{serverName}',
            'imageId' => '{imageId}',
            'flavorId' => '{flavorId}',

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

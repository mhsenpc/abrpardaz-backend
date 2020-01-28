<?php


namespace App\Services;


use App\Models\Image;
use App\Models\Machine;
use App\Models\Plan;
use App\Models\Snapshot;
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

    function createMachineFromImage(int $machine_id, string $name, int $user_id, int $plan_id, int $image_id, $ssh_key_id = null): bool
    {
        try {
            $image = Image::find($image_id);
            $plan = Plan::find($plan_id);
            Machine::find($machine_id)->updateRemoteID('test');
            return true;

            $options = [
                // Required
                'name' => $name."-".$machine_id,
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

            $server->waitUntil('Active');

            Machine::find($machine_id)->updateRemoteID($server->id);
        }
        catch (\Exception $exception){
            //TODO: save a notification for this user
            Machine::find($machine_id)->updateRemoteID('failed');
        }
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

    function rename(string $id, string $newname)
    {
        return true;
        $server = $this->compute->getServer(['id' => $id]);
        $server->name = $newname;
        $server->update();
    }

    function takeSnapshot(string $remote_id, string $name, int $snapshot_id)
    {
        return true;
        try {
            $server = $this->compute->getServer(['id' => $remote_id]);

            $image = $server->createImage([
                'name' => $name,
            ]);

            $server->waitUntil('Active');

            //update size and remote id in snapshots
            Snapshot::find($snapshot_id)->updateSizeAndRemoteId($remote_id,$image->size);
        }
        catch (\Exception $exception){
            $snapshot = Snapshot::find($snapshot_id);
            $snapshot->delete();
        }
    }

    function remove(string $remote_id)
    {
        return;
        $server = $this->compute->getServer(['id' => $remote_id]);
        $server->delete();
    }
}

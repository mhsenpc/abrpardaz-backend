<?php


namespace App\Services;


use App\Models\Image;
use App\Models\Machine;
use App\Models\Plan;
use App\Models\Snapshot;
use App\Repositories\ImageRepository;
use App\Repositories\MachineRepository;
use App\Repositories\SnapshotRepository;
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

    function createMachineFromImage(string $name, int $user_id, int $plan_id, int $image_id, $ssh_key_id = null): bool
    {
        $image = Image::find($image_id);
        $plan = Plan::find($plan_id);
        $machine = MachineRepository::createMachine($name, $user_id, $plan_id, $image_id, $ssh_key_id);
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

    function rename(string $id, string $newname)
    {
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
            $snapshot = (new SnapshotRepository(Snapshot::find($snapshot_id)));
            $snapshot->updateSizeAndRemoteId($remote_id,$image->size);
        }
        catch (\Exception $exception){
            $snapshot = Snapshot::find($snapshot_id);
            $snapshot->delete();
        }
    }

    function remove(string $id)
    {
        $machine = Machine::find($id);
        $machine->delete();
        $server = $this->compute->getServer(['id' => $id]);
        $server->delete();
    }
}

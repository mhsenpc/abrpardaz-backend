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

class SnapshotService
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

    function rename(string $id, string $newname): bool
    {
        return true;
        try {
            $service = $this->openstack->blockStorageV2();

            $snapshot_remote = $service->getSnapshot($id);
            $snapshot_remote->name = $newname;
            $snapshot_remote->update();
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    function remove(string $id): bool
    {
        return true;
        try {
            $service = $this->openstack->blockStorageV2();

            $snapshot_remote = $service->getSnapshot($id);
            $snapshot_remote->delete();
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}

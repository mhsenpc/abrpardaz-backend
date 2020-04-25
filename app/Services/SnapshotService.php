<?php


namespace App\Services;


use OpenStack\OpenStack;

class SnapshotService
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

    function rename(string $id, string $newname): bool
    {
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

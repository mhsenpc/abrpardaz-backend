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

    function rename(string $id, string $newname)
    {
        $service = $this->openstack->imagesV2();

        $image = $service->getImage($id);
        $image->update([
            'name'       => $newname,
        ]);
    }

    function remove(string $id)
    {
        $service = $this->openstack->imagesV2();

        $image = $service->getImage($id);
        $image->delete();
    }
}

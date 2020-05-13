<?php


namespace App\Services;


use OpenStack\OpenStack;

class SnapshotService
{
    private $openstack;
    private $compute;

    function __construct(string $user_id, string $password, string $project_id)
    {
        $this->openstack = new OpenStack([
            'authUrl' => config('openstack.authUrl'),
            'region' => config('openstack.region'),
            'user' => [
                'id' => $user_id,
                'password' => $password
            ],
            'scope' => ['project' => ['id' => $project_id ]]
        ]);

        $this->compute = $this->openstack->computeV2(['region' => config('openstack.region')]);
    }

    function rename(string $id, string $newname)
    {
        $service = $this->openstack->imagesV2();

        $image = $service->getImage($id);
        $image->update([
            'name' => $newname,
        ]);
    }

    function remove(string $id)
    {
        $service = $this->openstack->imagesV2();

        $image = $service->getImage($id);
        $image->delete();
    }
}

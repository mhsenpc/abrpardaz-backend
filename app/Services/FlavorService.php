<?php


namespace App\Services;


use OpenStack\OpenStack;

class FlavorService
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

    function getFlavors()
    {
        $flavors = $this->compute->listFlavors(['status' => 'ACTIVE']);
        return $flavors;
    }

    function getFlavor(string $remote_id)
    {
        $flavor = $this->compute->getFlavor(['id' => $remote_id]);
        $flavor->retrieve();
        return $flavor;
    }
}

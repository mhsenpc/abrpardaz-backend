<?php


namespace App\Services;


use OpenStack\OpenStack;

class ImageService
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

    function getImages(){
        $images = $this->compute->listImages(['status' => 'ACTIVE']);
        return $images;
    }

    function getImage(string $remote_id){
        $image = $this->compute->getImage(['id' => $remote_id]);
        $image->retrieve();
        return $image;
    }
}

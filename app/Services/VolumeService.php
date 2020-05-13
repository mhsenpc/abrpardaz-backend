<?php


namespace App\Services;


use OpenStack\OpenStack;

class VolumeService
{
    private $openstack;
    private $service;

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

        $service = $this->openstack->blockStorageV2();
    }

    function create(string $name, float $size)
    {
        $a = new \stdClass();
        $a->id = '54as4d';
        return $a;
        $service = $this->service->blockStorageV2();

        $volume = $service->createVolume([
            'size' => $size,
            'name' => $name,
        ]);
        return $volume;
    }

    function rename(string $remote_id, string $name)
    {
        return;
        $volume = $this->service->getVolume($remote_id);
        $volume->name = $name;
        $volume->update();
    }

    function remove(string $remote_id)
    {
        return;

        $volume = $this->service->getVolume($remote_id);
        $volume->delete();

    }

    function attachVolumeToMachine(string $machine_remote_id, string $volume_remote_id)
    {
        return;
        $compute = $this->openstack->computeV2();

        /**@var OpenStack\Compute\v2\Models\Server $server */
        $server = $compute->getServer(['id' => $machine_remote_id]);

        /**@var VolumeAttachment $volumeAttachment*/
        $volumeAttachment = $server->attachVolume($volume_remote_id);
    }

    function detachVolumeFromMachine(string $machine_remote_id, string $volume_remote_id)
    {
        return;
        $compute = $this->openstack->computeV2();

        /**@var OpenStack\Compute\v2\Models\Server $server */
        $server = $compute->getServer(['id' => $machine_remote_id]);

        /**@var VolumeAttachment $volumeAttachment*/
        $volumeAttachment = $server->detachVolume($volume_remote_id);
    }

    function findMachineRootVolume(string $machine_remote_id){
        return "ad574";
        $compute = $this->openstack->computeV2();

        $server = $compute->getServer(['id' => $machine_remote_id]);

        foreach ($server->listVolumeAttachments() as $volumeAttachment) {
            /**@var VolumeAttachment $volumeAttachment*/
            return $volumeAttachment->id; ///XXX: not sure if that'd be right
        }
    }
}

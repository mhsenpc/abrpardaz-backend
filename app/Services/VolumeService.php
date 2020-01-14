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

class VolumeService
{
    private $openstack;
    private $service;

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

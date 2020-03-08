<?php


namespace App\Services;


use App\Models\Image;
use App\Models\Plan;
use App\Models\SshKey;
use OpenStack\OpenStack;

class MachineService
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

    function getServer(string $remote_id)
    {
        $server = $this->compute->getServer(['id' => $remote_id]);
        $server->retrieve();
        return $server;
    }

    function createMachineFromImage(int $machine_id, string $name, int $user_id, int $plan_id, int $image_id, $ssh_key_id = null)
    {
        $image = Image::find($image_id);
        $plan = Plan::find($plan_id);
        if (!empty($ssh_key_id)) {
            $ssh_key = SshKey::find($ssh_key_id);
        }

        $options = [
            // Required
            'name' => $name . "-" . $machine_id,
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

        return $server;
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

    function takeSnapshot(string $remote_id, string $name)
    {
        $server = $this->compute->getServer(['id' => $remote_id]);

        $server->createImage([
            'name' => $name,
        ]);

        $server->waitUntil('Active');
    }

    function rebuild(string $remote_id, string $image_remote_id,string $admin_pass)
    {
        $server = $this->compute->getServer([
            'id' => $remote_id,
        ]);

        $server->rebuild([
            'imageId' => $image_remote_id,
            'adminPass' => $admin_pass
        ]);
        $server->waitUntil('Active');
        return true;
    }

    function remove(string $remote_id)
    {
        $server = $this->compute->getServer(['id' => $remote_id]);
        $server->delete();
    }
}

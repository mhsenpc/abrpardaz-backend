<?php


namespace App\Services;


use App\Models\Image;
use App\Models\Plan;
use App\Models\SshKey;
use Illuminate\Support\Facades\Log;
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

    function createMachineFromImage(int $machine_id, string $name, string $password, int $user_id, int $plan_id, string $source_remote_id, array $meta_data, $ssh_key_id = null)
    {
        $plan = Plan::find($plan_id);

        $options = [
            // Required
            'name' => $name . "-" . $machine_id,
            'imageId' => $source_remote_id,
            'flavorId' => $plan->remote_id,

            // Required if multiple network is defined
            'networks' => [
                ['uuid' => config('openstack.networkId')]
            ],
            'userData' => "#cloud-config
chpasswd:
  list: |
    root:$password
  expire: False",

        ];

        if (!empty($ssh_key_id)) {
            $ssh_key = SshKey::find($ssh_key_id);

            $data = [
                'name' => $ssh_key->name . $user_id,
                'publicKey' => $ssh_key->content
            ];

            /** @var \OpenStack\Compute\v2\Models\Keypair $keypair */
            $keypair = $this->compute->getKeypair()->createKeypair($data);

            $options['keyName'] = $ssh_key->name . $user_id;
        }

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

    function softReboot(string $id)
    {
        $server = $this->compute->getServer(['id' => $id]);
        $server->reboot('SOFT');
    }

    function hardReboot(string $id)
    {
        $server = $this->compute->getServer(['id' => $id]);
        $server->reboot('HARD');
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

    function takeSnapshot(string $remote_id, string $name,int $snapshot_id)
    {
        $server = $this->compute->getServer(['id' => $remote_id]);
        $final_name = $name."-".$snapshot_id;

        $server->createImage([
            'name' => $final_name,
        ]);

        $server->waitUntil('Active');

        $images = $this->openstack->imagesV2()
            ->listImages();

        foreach ($images as $image) {
            if($image->name == $final_name){
                return $image;
            }
        }

        throw new \Exception('Failed to find snapshot id after taking snapshot #'.$name);
    }

    function rebuild(string $remote_id, string $image_remote_id, string $admin_pass)
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

    function rescale(string $remote_id,string $flavor_id){
        $server = $this->compute->getServer(['id' => $remote_id]);

        $server->resize($flavor_id);
        $server->confirmResize();
    }

    function attachImage(string $remote_id,string $image_id,string $admin_pass){
        $server = $this->compute->getServer(['id' => $remote_id]);
        $server->rescue([
            'imageId'   => $image_id,
            'adminPass' => $admin_pass,
        ]);
    }

    function detachImage(string $remote_id){
        $server = $this->compute->getServer(['id' => $remote_id]);
        $server->unrescue();
    }

    function resetPassword(string $remote_id,string $password){
        $server = $this->compute->getServer(['id' => $remote_id]);
        $server->changePassword($password);
    }

    function remove(string $remote_id)
    {
        $server = $this->compute->getServer(['id' => $remote_id]);
        $server->delete();
    }
}

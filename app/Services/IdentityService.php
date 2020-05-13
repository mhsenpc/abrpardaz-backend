<?php


namespace App\Services;


use OpenStack\OpenStack;

class IdentityService
{
    private $openstack;
    private $identity;

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

        $this->identity = $this->openstack->identityV3(['region' => config('openstack.region')]);
    }

    function createUser(string $email,string $password)
    {
        $user = $this->identity->createUser([
            'domainId' => 'default',
            'email' => $email,
            'enabled' => true,
            'name' => $email,
            'password' => $password
        ]);

        return $user;
    }

    function createProject(string $name ,string $user_remote_id)
    {
        $project = $this->identity->createProject([
            'enabled' => true,
            'name' => $name
        ]);

        $admin_role_id = "";
        foreach ( $this->identity->listRoles() as $role){
            if($role->name == "admin"){
                $admin_role_id = $role->id;
            }
        }

        $project->grantUserRole([
            'userId' => $user_remote_id,
            'roleId' => $admin_role_id,
        ]);

        return $project;
    }

}

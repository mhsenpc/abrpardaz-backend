<?php


namespace App\Services;


use App\Models\Plan;

class FlavorSyncerService
{
    static function sync(){
        static::importFlavors();
        static::healthCheckFlavors();
    }

    static function healthCheckFlavors(){
        $flavor_service = new FlavorService();
        $local_plans = Plan::all();
        foreach ($local_plans as $local_plan){
            try{
                $remote_flavor = $flavor_service->getFlavor($local_plan->remote_id);
                echo 'Flavor ' . $local_plan->name." is ok!\n";
            }
            catch(\Exception $exception){
                echo 'Flavor ' . $local_plan->name." is broken. deleting!\n";
                $local_plan->delete();
            }
        }
    }

    static function importFlavors(){
        $flavor_service = new FlavorService();
        $remote_flavors = $flavor_service->getFlavors();
        foreach ($remote_flavors as $remote_flavor){
            $new_plan  = Plan::where('remote_id',$remote_flavor->id)->first();
            $remote_flavor->retrieve();
            $disk  = $remote_flavor->disk;
            $ram  = $remote_flavor->ram  / 1024;
            $vcpu  = $remote_flavor->vcpus;
            $name  = $remote_flavor->name;

            if($new_plan){
                //update metadata
                $new_plan->disk = $disk;
                $new_plan->ram = $ram;
                $new_plan->vcpu = $vcpu;
                $new_plan->name = $name;
                $new_plan->save();
                echo "$name metadata updated \n";
            }
            else{
                //insert new one in db
                $new_plan = new Plan();
                $new_plan->remote_id = $remote_flavor->id;
                $new_plan->disk = $disk;
                $new_plan->ram = $ram;
                $new_plan->vcpu = $vcpu;
                $new_plan->name = $name;
                $new_plan->hourly_price = 100;
                $new_plan->save();
                echo "$name inserted \n";
            }
        }
    }
}

<?php


namespace App\Services;


use App\Models\Plan;

class FlavorSyncerService
{
    private $separator = "\n";

    public function setRenderHtml(bool $render_html)
    {
        if ($render_html) {
            $this->separator = '<br/>';
        } else {
            $this->separator = "\n";
        }
    }

    function sync()
    {
        $result = "";
        $result .= $this->importFlavors();
        $result .= $this->healthCheckFlavors();
        return $result;
    }

    function healthCheckFlavors()
    {
        $result = "";
        $flavor_service = new FlavorService();
        $local_plans = Plan::all();
        foreach ($local_plans as $local_plan) {
            try {
                $remote_flavor = $flavor_service->getFlavor($local_plan->remote_id);
                $result .= 'Flavor ' . $local_plan->name . " is ok!" . $this->separator;
            } catch (\Exception $exception) {
                $result .= 'Flavor ' . $local_plan->name . " is broken. deleting!" . $this->separator;
                $local_plan->delete();
            }
        }
        return $result;
    }

    function importFlavors()
    {
        $result = "";
        $flavor_service = new FlavorService();
        $remote_flavors = $flavor_service->getFlavors();
        foreach ($remote_flavors as $remote_flavor) {
            $new_plan = Plan::where('remote_id', $remote_flavor->id)->first();
            $remote_flavor->retrieve();
            $disk = $remote_flavor->disk;
            $ram = $remote_flavor->ram / 1024;
            $vcpu = $remote_flavor->vcpus;
            $name = $remote_flavor->name;

            if ($new_plan) {
                //update metadata
                $new_plan->disk = $disk;
                $new_plan->ram = $ram;
                $new_plan->vcpu = $vcpu;
                $new_plan->name = $name;
                $new_plan->save();
                $result .= "$name metadata updated!" . $this->separator;
            } else {
                //insert new one in db
                $new_plan = new Plan();
                $new_plan->remote_id = $remote_flavor->id;
                $new_plan->disk = $disk;
                $new_plan->ram = $ram;
                $new_plan->vcpu = $vcpu;
                $new_plan->name = $name;
                $new_plan->hourly_price = 100;
                $new_plan->save();
                $result .= "$name inserted!" . $this->separator;
            }
        }
        return $result;
    }
}

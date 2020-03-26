<?php


namespace App\Services;


use App\Models\Image;

class ImageSyncerService
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
        $this->importImages();
        $this->healthCheckImages();
    }

    function healthCheckImages()
    {
        $image_service = new ImageService();
        $local_images = Image::all();
        foreach ($local_images as $local_image) {
            try {
                $remote_image = $image_service->getImage($local_image->remote_id);
                echo 'Image ' . $local_image->name . " is ok!".$this->separator;
            } catch (\Exception $exception) {
                echo 'Image ' . $local_image->name . " is broken. deleting!".$this->separator;
                $local_image->delete();
            }
        }
    }

    function importImages()
    {
        $image_service = new ImageService();
        $remote_images = $image_service->getImages();
        foreach ($remote_images as $remote_image) {
            $local_image = Image::where('remote_id', $remote_image->id)->first();
            $min_disk = $remote_image->metadata['min_disk'];
            $min_ram = $remote_image->metadata['min_ram'];
            $os_name = $remote_image->metadata['os_name'];
            $os_version = $remote_image->metadata['os_version'];

            //FAKE data
            $min_disk = 1;
            $min_ram = 1;
            $os_name = $remote_image->name;
            $os_version = 2;

            if ($local_image) {
                //update metadata
                $local_image->min_disk = $min_disk;
                $local_image->min_ram = $min_ram;
                $local_image->name = $os_name;
                $local_image->version = $os_version;
                $local_image->save();
                echo "$os_name metadata updated!".$this->separator;
            } else {
                //insert new one in db
                $new_image = new Image();
                $new_image->remote_id = $remote_image->id;
                $new_image->min_disk = $min_disk;
                $new_image->min_ram = $min_ram;
                $new_image->name = $os_name;
                $new_image->version = $os_version;
                $new_image->save();
                echo "$os_name inserted!".$this->separator;
            }
        }
    }
}

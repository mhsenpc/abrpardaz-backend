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
        $result = "";
        $result .= $this->importImages();
        $result .= $this->healthCheckImages();
        return $result;
    }

    function healthCheckImages()
    {
        $result = "";
        $image_service = new ImageService();
        $local_images = Image::all();
        foreach ($local_images as $local_image) {
            try {
                $remote_image = $image_service->getImage($local_image->remote_id);
                $result .=  'Image ' . $local_image->name . " is ok!".$this->separator;
            } catch (\Exception $exception) {
                $result .=  'Image ' . $local_image->name . " is broken. deleting!".$this->separator;
                $local_image->delete();
            }
        }
        return $result;
    }

    function importImages()
    {
        $result = "";
        $image_service = new ImageService();
        $remote_images = $image_service->getImages();
        foreach ($remote_images as $remote_image) {
            $local_image = Image::where('remote_id', $remote_image->id)->first();
            $os_name = $remote_image->name;

            if ($local_image) {
                //update metadata
                $local_image->name = $os_name;
                $local_image->save();
                $result .=  "$os_name metadata updated!".$this->separator;
            } else {
                //insert new one in db
                $new_image = new Image();
                $new_image->remote_id = $remote_image->id;
                $new_image->min_disk = 1;
                $new_image->min_ram = 1;
                $new_image->name = $os_name;
                $new_image->version = 2;
                $new_image->save();
                $result .=  "$os_name inserted!".$this->separator;
            }
        }

        return $result;
    }
}

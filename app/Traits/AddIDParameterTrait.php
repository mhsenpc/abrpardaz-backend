<?php

namespace App\Traits;

trait AddIDParameterTrait
{
    public function all($keys = null)
    {
        $data = parent::all($keys);
        $data['id'] = $this->route('id');
        return $data;
    }

}

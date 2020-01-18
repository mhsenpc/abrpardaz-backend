<?php
namespace App\Traits;

trait GetUserNameTrait
{
    function getUserName()
    {
        if (!empty($this->profile->first_name && $this->profile->last_name))
            return $this->profile->first_name . ' ' . $this->profile->last_name . ' عزیز';
        else if (!empty($this->profile->first_name))
            return $this->profile->first_name . ' عزیز';
        else if (!empty($this->profile->last_name))
            return 'جناب ' . $this->profile->last_name . ' عزیز';
        else
            return "کاربر عزیز";
    }
}

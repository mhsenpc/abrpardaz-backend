<?php


namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InvoiceUserIDScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if (strpos(php_sapi_name(), 'cli') !== false)
            return;

        $user = Auth::user();
        if($user->hasPermissionTo('Invoice Operator'))
            return;
        $builder->where('user_id',  $user->id);
    }
}

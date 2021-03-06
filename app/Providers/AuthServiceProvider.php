<?php

namespace App\Providers;

use App\Permission;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
        'App\Post'  => 'App\Policies\PostPolicy',
        'App\Task'  => 'App\Policies\TaskPolicy',
        'App\Comment'  => 'App\Policies\CommentPolicy',
        'App\Announce'  => 'App\Policies\AnnouncePolicy',
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        foreach ($this->getPermissions() as $permission)
        {
            $gate->define($permission->name, function($user) use($permission) {
                return $user->hasRole($permission->roles);
            });
        }
    }

    protected function getPermissions()
    {
        //Permission::with('roles')->get();
        return [];
    }
}

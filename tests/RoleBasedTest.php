<?php

namespace Rappasoft\Lockout\Tests;

use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\User as Authenticatable;

class RoleBasedTest extends TestCase
{
    /** @test */
    public function user_with_role_attribute_bypasses_lockout()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.allowed_roles' => ['admin']]);

        $user = new class extends Authenticatable {
            public $role = 'admin';
        };

        $this->actingAs($user)
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function user_without_allowed_role_is_blocked()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.allowed_roles' => ['admin']]);

        $user = new class extends Authenticatable {
            public $role = 'user';
        };

        $this->actingAs($user)
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function user_with_hasRole_method_bypasses_lockout()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.allowed_roles' => ['admin']]);

        $user = new class extends Authenticatable {
            public function hasRole($role)
            {
                return $role === 'admin';
            }
        };

        $this->actingAs($user)
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function unauthenticated_user_is_blocked()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.allowed_roles' => ['admin']]);

        $this->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function multiple_allowed_roles_work()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.allowed_roles' => ['admin', 'super-admin', 'maintenance']]);

        $admin = new class extends Authenticatable {
            public $role = 'admin';
        };

        $superAdmin = new class extends Authenticatable {
            public $role = 'super-admin';
        };

        $maintenance = new class extends Authenticatable {
            public $role = 'maintenance';
        };

        $this->actingAs($admin)
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_OK);

        $this->actingAs($superAdmin)
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_OK);

        $this->actingAs($maintenance)
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function empty_allowed_roles_blocks_everyone()
    {
        config(['lockout.enabled' => true]);
        config(['lockout.locked_types' => ['post']]);
        config(['lockout.allowed_roles' => []]);

        $user = new class extends Authenticatable {
            public $role = 'admin';
        };

        $this->actingAs($user)
            ->call('POST', 'post')
            ->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

}


<?php

namespace Tests\Feature\Collaborator;

use App\Models\Collaborator;
use App\Models\LegalEntity;
use App\Models\User;

describe('SelfServiceController', function () {
    describe('profile', function () {
        it('allows collaborator to view own profile', function () {
            LegalEntity::factory()->create();
            $user = User::factory()->create();
            Collaborator::factory()->create(['user_id' => $user->id]);

            $this->actingAs($user)
                ->get('/self-service/profile')
                ->assertOk()
                ->assertInertia(fn ($page) => $page
                    ->component('self-service/Profile')
                    ->where('collaborator.user_id', $user->id)
                );
        });

        it('allows admin to view self-service profile when they have a collaborator record', function () {
            LegalEntity::factory()->create();
            $admin = User::factory()->admin()->create();
            Collaborator::factory()->create(['user_id' => $admin->id]);

            $this->actingAs($admin)
                ->get('/self-service/profile')
                ->assertOk();
        });

        it('returns 404 when user has no collaborator record', function () {
            $user = User::factory()->create();

            $this->actingAs($user)
                ->get('/self-service/profile')
                ->assertNotFound();
        });

        it('redirects guests to login', function () {
            $this->get('/self-service/profile')->assertRedirect('/login');
        });
    });
});

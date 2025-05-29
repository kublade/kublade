<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Kubernetes\Clusters\Cluster;
use App\Models\Projects\Deployments\Deployment;
use App\Models\Projects\Projects\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Class UserTest.
 *
 * Unit tests for the User model.
 *
 * @author Marcel Menk <marcel.menk@ipvx.io>
 */
class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create([
            'name'     => 'Test User',
            'email'    => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);
    }

    /**
     * @test
     */
    public function itHasCorrectTableName(): void
    {
        $this->assertEquals('users', $this->user->getTable());
    }

    /**
     * @test
     */
    public function itHasCorrectFillableAttributes(): void
    {
        $fillable = [
            'name',
            'email',
            'password',
        ];

        $this->assertEquals($fillable, $this->user->getFillable());
    }

    /**
     * @test
     */
    public function itHasCorrectHiddenAttributes(): void
    {
        $hidden = [
            'password',
            'remember_token',
        ];

        $this->assertEquals($hidden, $this->user->getHidden());
    }

    /**
     * @test
     */
    public function itHasCorrectCasts(): void
    {
        $casts = [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'id'                => 'int',
        ];

        $this->assertEquals($casts, $this->user->getCasts());
    }

    /**
     * @test
     */
    public function itCanHaveProjects(): void
    {
        // Create projects for the user
        $projects = Project::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->user->projects);
        $this->assertCount(3, $this->user->projects);
        $this->assertInstanceOf(Project::class, $this->user->projects->first());
        $this->assertEquals($projects->pluck('id')->toArray(), $this->user->projects->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->user->projects()->exists());
        $this->assertEquals(3, $this->user->projects()->count());
    }

    /**
     * @test
     */
    public function itCanHaveClusters(): void
    {
        // Create clusters for the user
        $clusters = Cluster::factory()->count(2)->create([
            'user_id' => $this->user->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->user->clusters);
        $this->assertCount(2, $this->user->clusters);
        $this->assertInstanceOf(Cluster::class, $this->user->clusters->first());
        $this->assertEquals($clusters->pluck('id')->toArray(), $this->user->clusters->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->user->clusters()->exists());
        $this->assertEquals(2, $this->user->clusters()->count());
    }

    /**
     * @test
     */
    public function itCanHaveDeployments(): void
    {
        // Create deployments for the user
        $deployments = Deployment::factory()->count(4)->create([
            'user_id' => $this->user->id,
        ]);

        // Test the relationship
        $this->assertInstanceOf(Collection::class, $this->user->deployments);
        $this->assertCount(4, $this->user->deployments);
        $this->assertInstanceOf(Deployment::class, $this->user->deployments->first());
        $this->assertEquals($deployments->pluck('id')->toArray(), $this->user->deployments->pluck('id')->toArray());

        // Test relationship methods
        $this->assertTrue($this->user->deployments()->exists());
        $this->assertEquals(4, $this->user->deployments()->count());
    }

    /**
     * @test
     */
    public function itImplementsJWTSubject(): void
    {
        // Test JWT identifier
        $this->assertEquals($this->user->id, $this->user->getJWTIdentifier());

        // Test JWT custom claims
        $this->assertEquals([], $this->user->getJWTCustomClaims());
    }

    /**
     * @test
     */
    public function itHandlesPermissionsCorrectly(): void
    {
        $user       = User::factory()->create();
        $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'api']);
        $role       = Role::create(['name' => 'test-role', 'guard_name' => 'api']);
        $role->givePermissionTo($permission);

        $user->assignRole($role);

        $this->assertTrue($user->hasRole($role, 'api'));
        $this->assertTrue($user->hasPermissionTo($permission, 'api'));
        $this->assertTrue($user->hasPermissionTo('test-permission', 'api'));
    }

    /**
     * @test
     */
    public function firstUserHasAllPermissions(): void
    {
        // Clean up all users and related data
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('roles')->delete();
        DB::table('permissions')->delete();
        User::query()->delete();

        // Create first user
        $firstUser  = User::factory()->create();
        $permission = Permission::create(['name' => 'test-permission', 'guard_name' => 'api']);

        // First user should have all permissions
        $this->assertTrue($firstUser->hasPermissionTo($permission, 'api'));
        $this->assertTrue($firstUser->hasPermissionTo('test-permission', 'api'));
        $this->assertTrue($firstUser->hasPermissionTo('any-permission', 'api'));

        // Create second user
        $secondUser = User::factory()->create();
        $this->assertFalse($secondUser->hasPermissionTo($permission, 'api'));
        $this->assertFalse($secondUser->hasPermissionTo('test-permission', 'api'));
        $this->assertFalse($secondUser->hasPermissionTo('any-permission', 'api'));
    }

    /**
     * @test
     */
    public function itCanHandleEmailVerification(): void
    {
        // Create a new user without email verification
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // Test unverified user
        $this->assertNull($user->email_verified_at);
        $this->assertFalse($user->hasVerifiedEmail());

        // Verify the user
        $user->email_verified_at = now();
        $user->save();

        // Test verified user
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());

        // Test unverifying user
        $user->email_verified_at = null;
        $user->save();
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    /**
     * @test
     */
    public function itCanHandleRememberToken(): void
    {
        // Create a new user without remember token
        $user = User::factory()->create([
            'remember_token' => null,
        ]);

        // Test without remember token
        $this->assertNull($user->remember_token);

        // Set remember token
        $token                = 'test-token';
        $user->remember_token = $token;
        $user->save();

        // Test with remember token
        $this->assertEquals($token, $user->fresh()->remember_token);

        // Test token update
        $newToken             = 'new-token';
        $user->remember_token = $newToken;
        $user->save();
        $this->assertEquals($newToken, $user->fresh()->remember_token);
    }

    /**
     * @test
     */
    public function itCanHandlePasswordHashing(): void
    {
        // Test password hashing
        $this->user->password = 'new-password';
        $this->user->save();

        $this->assertNotEquals('new-password', $this->user->password);
        $this->assertTrue(Hash::check('new-password', $this->user->password));

        // Test password update
        $this->user->password = 'updated-password';
        $this->user->save();

        $this->assertNotEquals('updated-password', $this->user->password);
        $this->assertTrue(Hash::check('updated-password', $this->user->password));
        $this->assertFalse(Hash::check('new-password', $this->user->password));
    }

    /**
     * @test
     */
    public function itCanHandleMultipleRoles(): void
    {
        $user  = User::factory()->create();
        $role1 = Role::create(['name' => 'role-1', 'guard_name' => 'api']);
        $role2 = Role::create(['name' => 'role-2', 'guard_name' => 'api']);

        $user->assignRole($role1);
        $this->assertTrue($user->hasRole($role1, 'api'));
        $this->assertFalse($user->hasRole($role2, 'api'));

        $user->assignRole($role2);
        $this->assertTrue($user->hasRole($role1, 'api'));
        $this->assertTrue($user->hasRole($role2, 'api'));

        $user->removeRole($role1);
        $this->assertFalse($user->hasRole($role1, 'api'));
        $this->assertTrue($user->hasRole($role2, 'api'));
    }

    /**
     * @test
     */
    public function itCanHandleDirectPermissions(): void
    {
        $user       = User::factory()->create();
        $permission = Permission::create(['name' => 'direct-permission', 'guard_name' => 'api']);

        $user->givePermissionTo($permission);
        $this->assertTrue($user->hasPermissionTo($permission, 'api'));
        $this->assertTrue($user->hasPermissionTo('direct-permission', 'api'));

        $user->revokePermissionTo($permission);
        $this->assertFalse($user->hasPermissionTo($permission, 'api'));
        $this->assertFalse($user->hasPermissionTo('direct-permission', 'api'));
    }

    /**
     * @test
     */
    public function itCanCheckFirstRegistration(): void
    {
        // Clean up all users and related data
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('role_has_permissions')->delete();
        DB::table('roles')->delete();
        DB::table('permissions')->delete();
        DB::table('projects')->delete();
        DB::table('clusters')->delete();
        DB::table('deployments')->delete();
        User::query()->delete();

        // Reset the auto-increment counter
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');

        // Create first user
        $firstUser = User::factory()->create();
        $this->assertEquals(1, $firstUser->id);
        $this->assertTrue($firstUser->isFirstRegistration());

        // Create second user
        $secondUser = User::factory()->create();
        $this->assertEquals(2, $secondUser->id);
        $this->assertFalse($secondUser->isFirstRegistration());

        // Verify first user is still first after creating more users
        User::factory()->count(3)->create();
        $this->assertTrue($firstUser->fresh()->isFirstRegistration());
        $this->assertFalse($secondUser->fresh()->isFirstRegistration());
    }
}

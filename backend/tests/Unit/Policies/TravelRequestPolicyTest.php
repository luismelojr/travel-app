<?php

namespace Tests\Unit\Policies;

use App\Enums\TravelRequestStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\TravelRequest;
use App\Models\User;
use App\Policies\TravelRequestPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelRequestPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TravelRequestPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TravelRequestPolicy();
    }

    public function test_view_any_returns_true_for_authenticated_users(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_returns_true_for_own_travel_request(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->make(['user_id' => 1]);

        $this->assertTrue($this->policy->view($user, $travelRequest));
    }

    public function test_view_returns_true_for_admin_viewing_any_request(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'role' => UserRoleEnum::ADMIN
        ]);

        $travelRequest = TravelRequest::factory()->make(['user_id' => 2]); // Different user

        $this->assertTrue($this->policy->view($user, $travelRequest));
    }

    public function test_view_returns_false_for_other_users_request(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->make(['user_id' => 2]); // Different user

        $this->assertFalse($this->policy->view($user, $travelRequest));
    }

    public function test_create_returns_true_for_authenticated_users(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $this->assertTrue($this->policy->create($user));
    }

    public function test_update_returns_true_for_admin(): void
    {
        $admin = User::factory()->make(['role' => UserRoleEnum::ADMIN]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 2, // Different user
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        $this->assertTrue($this->policy->update($admin, $travelRequest));
    }

    public function test_update_returns_false_for_regular_user_own_request(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 1,
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        $this->assertFalse($this->policy->update($user, $travelRequest));
    }

    public function test_update_returns_false_for_regular_user_others_request(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 2,
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        $this->assertFalse($this->policy->update($user, $travelRequest));
    }

    public function test_update_status_returns_true_for_admin(): void
    {
        $user = User::factory()->make(['role' => UserRoleEnum::ADMIN]);

        $travelRequest = TravelRequest::factory()->make();

        $this->assertTrue($this->policy->updateStatus($user, $travelRequest));
    }

    public function test_update_status_returns_false_for_regular_user(): void
    {
        $user = User::factory()->make(['role' => UserRoleEnum::USER]);

        $travelRequest = TravelRequest::factory()->make();

        $this->assertFalse($this->policy->updateStatus($user, $travelRequest));
    }

    public function test_cancel_returns_true_for_user_own_requested_request(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 1,
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        $this->assertTrue($this->policy->cancel($user, $travelRequest));
    }

    public function test_cancel_returns_false_for_user_approved_request(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 1,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $this->assertFalse($this->policy->cancel($user, $travelRequest));
    }

    public function test_cancel_returns_true_for_admin_any_non_approved_request(): void
    {
        $admin = User::factory()->make(['role' => UserRoleEnum::ADMIN]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 2, // Different user
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        $this->assertTrue($this->policy->cancel($admin, $travelRequest));
    }

    public function test_cancel_returns_false_for_admin_approved_request(): void
    {
        $admin = User::factory()->make(['role' => UserRoleEnum::ADMIN]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 2,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $this->assertFalse($this->policy->cancel($admin, $travelRequest));
    }

    public function test_cancel_returns_false_for_user_others_request(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 2,
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        $this->assertFalse($this->policy->cancel($user, $travelRequest));
    }

    public function test_cancel_after_approval_returns_false_always(): void
    {
        $admin = User::factory()->make(['role' => UserRoleEnum::ADMIN]);

        $travelRequest = TravelRequest::factory()->make([
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        // Esta funcionalidade foi removida
        $this->assertFalse($this->policy->cancelAfterApproval($admin, $travelRequest));
    }

    /** @deprecated Backward compatibility test for cancelOwn method */
    public function test_cancel_own_redirects_to_cancel_method(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 1,
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        // cancelOwn should redirect to cancel method
        $this->assertTrue($this->policy->cancelOwn($user, $travelRequest));
        $this->assertEquals(
            $this->policy->cancel($user, $travelRequest),
            $this->policy->cancelOwn($user, $travelRequest)
        );
    }

    public function test_delete_returns_true_for_own_requested_request(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 1,
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        $this->assertTrue($this->policy->delete($user, $travelRequest));
    }

    public function test_delete_returns_false_for_own_approved_request(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 1,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $this->assertFalse($this->policy->delete($user, $travelRequest));
    }

    public function test_delete_returns_false_for_own_cancelled_request(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 1,
            'status' => TravelRequestStatusEnum::CANCELLED
        ]);

        $this->assertFalse($this->policy->delete($user, $travelRequest));
    }

    public function test_delete_returns_false_for_other_users_request(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 2,
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        $this->assertFalse($this->policy->delete($user, $travelRequest));
    }

    public function test_restore_returns_true_for_admin(): void
    {
        $user = User::factory()->make(['role' => UserRoleEnum::ADMIN]);

        $travelRequest = TravelRequest::factory()->make();

        $this->assertTrue($this->policy->restore($user, $travelRequest));
    }

    public function test_restore_returns_false_for_regular_user(): void
    {
        $user = User::factory()->make(['role' => UserRoleEnum::USER]);

        $travelRequest = TravelRequest::factory()->make();

        $this->assertFalse($this->policy->restore($user, $travelRequest));
    }

    public function test_force_delete_returns_true_for_admin(): void
    {
        $user = User::factory()->make(['role' => UserRoleEnum::ADMIN]);

        $travelRequest = TravelRequest::factory()->make();

        $this->assertTrue($this->policy->forceDelete($user, $travelRequest));
    }

    public function test_force_delete_returns_false_for_regular_user(): void
    {
        $user = User::factory()->make(['role' => UserRoleEnum::USER]);

        $travelRequest = TravelRequest::factory()->make();

        $this->assertFalse($this->policy->forceDelete($user, $travelRequest));
    }

    public function test_admin_can_view_all_requests(): void
    {
        $admin = User::factory()->make([
            'id' => 1,
            'role' => UserRoleEnum::ADMIN
        ]);

        $userRequest = TravelRequest::factory()->make(['user_id' => 2]);

        $this->assertTrue($this->policy->view($admin, $userRequest));
    }

    public function test_user_cannot_view_others_requests(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'role' => UserRoleEnum::USER
        ]);

        $otherRequest = TravelRequest::factory()->make(['user_id' => 2]);

        $this->assertFalse($this->policy->view($user, $otherRequest));
    }

    public function test_user_cannot_update_status(): void
    {
        $user = User::factory()->make(['role' => UserRoleEnum::USER]);

        $travelRequest = TravelRequest::factory()->make(['user_id' => 1]);

        $this->assertFalse($this->policy->updateStatus($user, $travelRequest));
    }

    public function test_user_cannot_cancel_after_approval(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'role' => UserRoleEnum::USER
        ]);

        $approvedRequest = TravelRequest::factory()->make([
            'user_id' => 1, // Own request
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $this->assertFalse($this->policy->cancelAfterApproval($user, $approvedRequest));
    }

    public function test_cancel_returns_true_for_user_own_cancelled_request(): void
    {
        $user = User::factory()->make([
            'id' => 1,
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 1,
            'status' => TravelRequestStatusEnum::CANCELLED
        ]);

        // Cancelled requests cannot be cancelled again
        $this->assertFalse($this->policy->cancel($user, $travelRequest));
    }

    public function test_cancel_returns_true_for_admin_cancelled_request(): void
    {
        $admin = User::factory()->make(['role' => UserRoleEnum::ADMIN]);

        $travelRequest = TravelRequest::factory()->make([
            'user_id' => 2,
            'status' => TravelRequestStatusEnum::CANCELLED
        ]);

        // Even admins cannot cancel already cancelled requests
        $this->assertFalse($this->policy->cancel($admin, $travelRequest));
    }

    public function test_policy_methods_exist(): void
    {
        $methods = [
            'viewAny', 'view', 'create', 'update', 'updateStatus',
            'cancel', 'cancelAfterApproval', 'cancelOwn', 'delete',
            'restore', 'forceDelete'
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists($this->policy, $method),
                "Method {$method} does not exist in TravelRequestPolicy"
            );
        }
    }

    public function test_all_status_combinations_for_cancel(): void
    {
        $user = User::factory()->make(['id' => 1]);
        $admin = User::factory()->make(['role' => UserRoleEnum::ADMIN]);

        $statusResults = [
            TravelRequestStatusEnum::REQUESTED->value => [
                'user_own' => true,
                'admin_any' => true,
                'status' => TravelRequestStatusEnum::REQUESTED
            ],
            TravelRequestStatusEnum::APPROVED->value => [
                'user_own' => false,
                'admin_any' => false,
                'status' => TravelRequestStatusEnum::APPROVED
            ],
            TravelRequestStatusEnum::CANCELLED->value => [
                'user_own' => false,
                'admin_any' => false,
                'status' => TravelRequestStatusEnum::CANCELLED
            ]
        ];

        foreach ($statusResults as $statusValue => $expectedResults) {
            $ownRequest = TravelRequest::factory()->make([
                'user_id' => 1,
                'status' => $expectedResults['status']
            ]);

            $otherRequest = TravelRequest::factory()->make([
                'user_id' => 2,
                'status' => $expectedResults['status']
            ]);

            // Test user with own request
            $this->assertEquals(
                $expectedResults['user_own'],
                $this->policy->cancel($user, $ownRequest),
                "User cancel own {$expectedResults['status']->value} request should be {$expectedResults['user_own']}"
            );

            // Test admin with any request
            $this->assertEquals(
                $expectedResults['admin_any'],
                $this->policy->cancel($admin, $otherRequest),
                "Admin cancel any {$expectedResults['status']->value} request should be {$expectedResults['admin_any']}"
            );
        }
    }

    public function test_policy_authorization_consistency(): void
    {
        $regularUser = User::factory()->make(['role' => UserRoleEnum::USER]);
        $admin = User::factory()->make(['role' => UserRoleEnum::ADMIN]);

        $travelRequest = TravelRequest::factory()->make();

        // Admin should have more permissions than regular user
        $adminPermissions = [
            'update' => $this->policy->update($admin, $travelRequest),
            'updateStatus' => $this->policy->updateStatus($admin, $travelRequest),
            'restore' => $this->policy->restore($admin, $travelRequest),
            'forceDelete' => $this->policy->forceDelete($admin, $travelRequest),
        ];

        $userPermissions = [
            'update' => $this->policy->update($regularUser, $travelRequest),
            'updateStatus' => $this->policy->updateStatus($regularUser, $travelRequest),
            'restore' => $this->policy->restore($regularUser, $travelRequest),
            'forceDelete' => $this->policy->forceDelete($regularUser, $travelRequest),
        ];

        foreach ($adminPermissions as $permission => $adminHasPermission) {
            $userHasPermission = $userPermissions[$permission];
            
            // If admin doesn't have permission, user shouldn't either
            if (!$adminHasPermission) {
                $this->assertFalse(
                    $userHasPermission,
                    "User should not have {$permission} permission if admin doesn't"
                );
            }
        }

        // Admin should have all administrative permissions
        $this->assertTrue($adminPermissions['update']);
        $this->assertTrue($adminPermissions['updateStatus']);
        $this->assertTrue($adminPermissions['restore']);
        $this->assertTrue($adminPermissions['forceDelete']);

        // Regular user should not have administrative permissions
        $this->assertFalse($userPermissions['update']);
        $this->assertFalse($userPermissions['updateStatus']);
        $this->assertFalse($userPermissions['restore']);
        $this->assertFalse($userPermissions['forceDelete']);
    }

    public function test_delete_only_works_for_requested_status(): void
    {
        $user = User::factory()->make(['id' => 1]);

        $statuses = [
            TravelRequestStatusEnum::REQUESTED->value => ['status' => TravelRequestStatusEnum::REQUESTED, 'expected' => true],
            TravelRequestStatusEnum::APPROVED->value => ['status' => TravelRequestStatusEnum::APPROVED, 'expected' => false],
            TravelRequestStatusEnum::CANCELLED->value => ['status' => TravelRequestStatusEnum::CANCELLED, 'expected' => false],
        ];

        foreach ($statuses as $statusValue => $data) {
            $travelRequest = TravelRequest::factory()->make([
                'user_id' => 1,
                'status' => $data['status']
            ]);

            $this->assertEquals(
                $data['expected'],
                $this->policy->delete($user, $travelRequest),
                "Delete permission for {$data['status']->value} status should be {$data['expected']}"
            );
        }
    }

    public function test_view_any_always_returns_true(): void
    {
        $regularUser = User::factory()->make(['role' => UserRoleEnum::USER]);
        $admin = User::factory()->make(['role' => UserRoleEnum::ADMIN]);

        $this->assertTrue($this->policy->viewAny($regularUser));
        $this->assertTrue($this->policy->viewAny($admin));
    }

    public function test_create_always_returns_true(): void
    {
        $regularUser = User::factory()->make(['role' => UserRoleEnum::USER]);
        $admin = User::factory()->make(['role' => UserRoleEnum::ADMIN]);

        $this->assertTrue($this->policy->create($regularUser));
        $this->assertTrue($this->policy->create($admin));
    }
}

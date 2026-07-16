<?php

namespace Modules\PerformanceReview\Policies;

use App\Models\User;
use Modules\PerformanceReview\Models\PerformanceReview;

class PerformanceReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'ceo', 'viewer', 'ops']);
    }

    public function view(User $user, PerformanceReview $review): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'ceo', 'viewer', 'ops']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr']);
    }

    public function update(User $user, PerformanceReview $review): bool
    {
        if ($review->status?->value === 'finalized' || $review->status?->value === 'cancelled') {
            return false;
        }
        return $user->hasAnyRole(['system_admin', 'hr']);
    }

    public function delete(User $user, PerformanceReview $review): bool
    {
        return $user->hasRole('system_admin');
    }

    public function finalize(User $user, PerformanceReview $review): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr']);
    }
}

<?php

namespace App\Policies;

use App\Models\Summary;
use App\Models\User;

class SummaryPolicy
{
    public function view(User $user, Summary $summary): bool
    {
        return $user->id === $summary->user_id;
    }

    public function update(User $user, Summary $summary): bool
    {
        return $user->id === $summary->user_id;
    }

    public function delete(User $user, Summary $summary): bool
    {
        return $user->id === $summary->user_id;
    }
}
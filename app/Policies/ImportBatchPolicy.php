<?php

namespace App\Policies;

use App\Models\ImportBatch;
use App\Models\User;

class ImportBatchPolicy
{
    public function view(User $user, ImportBatch $batch): bool
    {
        return $batch->user_id === $user->id;
    }

    public function update(User $user, ImportBatch $batch): bool
    {
        return $batch->user_id === $user->id;
    }

    public function delete(User $user, ImportBatch $batch): bool
    {
        return $batch->user_id === $user->id;
    }
}

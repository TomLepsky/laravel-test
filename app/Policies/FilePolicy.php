<?php

namespace App\Policies;

use App\Models\File;
use App\Models\User;

class FilePolicy
{
    public const DOWNLOAD = 'download';

    public const DELETE = 'delete';

    public const PATCH = 'patch';

    public function download(User $user, File $file) : bool
    {
        return $user->id === $file->userId;
    }

    public function delete(User $user, File $file) : bool
    {
        return $user->id === $file->userId;
    }

    public function patch(User $user, File $file) : bool
    {
        return $user->id === $file->userId;
    }
}

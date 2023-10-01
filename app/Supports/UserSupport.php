<?php

namespace App\Supports;

use App\Models\User;
use Illuminate\Support\Str;

class UserSupport
{
    /**
     * Generate a random and safety transfer_key
     */
    public static function generateSafeTransferKey(): string
    {
        $generated = strtoupper(self::randomTransferKey());

        if (User::where('transfer_key', $generated)->first()) {
            return self::generateSafeTransferKey();
        }

        return $generated;
    }

    private static function randomTransferKey(): string
    {
        return Str::random(8);
    }
}

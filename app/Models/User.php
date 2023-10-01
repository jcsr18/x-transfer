<?php

namespace App\Models;

use App\Supports\UserSupport;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'balance',
        'transfer_key',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];

    protected function transferKey(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => UserSupport::generateSafeTransferKey(),
        );
    }

    public function ownTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'sender_id');
    }
}

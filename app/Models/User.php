<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {

        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Determine if the user has the administrator role.
     */
    public function isAdmin(): bool
    {

        return $this->role === 'admin';
    }

    /**
     * Determine if the user account has been blocked by an administrator.
     */
    public function isBlocked(): bool
    {

        return $this->status === 'blocked';
    }

    /**
     * Only users with the customer role.
     *
     * @param  Builder<User>  $query
     */
    #[Scope]
    protected function customers(Builder $query): void
    {

        $query->where('role', 'customer');
    }

    /**
     * Only users with the admin role (the store team).
     *
     * @param  Builder<User>  $query
     */
    #[Scope]
    protected function admins(Builder $query): void
    {

        $query->where('role', 'admin');
    }

    public function products(): HasMany
    {

        return $this->hasMany(Product::class);
    }

    public function comments(): HasMany
    {

        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {

        return $this->hasMany(Like::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function bancos(): HasMany
    {

        return $this->hasMany(Banco::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function proofpayment(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }
}

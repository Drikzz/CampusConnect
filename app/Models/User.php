<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements HasName
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'first_name',
        'middle_name',
        'last_name',
        'wmsu_email',
        'phone',     // Added phone
        'gender',    // Added gender
        'date_of_birth',  // Added date of birth
        'user_type_id',
        'wmsu_dept_id',
        'grade_level_id',
        'profile_picture',
        'wmsu_id_front',
        'wmsu_id_back',
        'is_seller',
        'seller_code',
        'is_verified',
        'verified_at',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
     * Get the user's display name for Filament.
     */
    // public function getFilamentName(): string
    // {
    //     return $this->first_name ?? 'Admin'; // Use `first_name` as the display name
    // }

    public function getFilamentName(): string
    {
        return $this->getAttributeValue('first_name');
    }

    /**
     * Check if the user can access Filament.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin; // Only allow admin users to access Filament
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function userType(): HasMany
    {
        return $this->hasMany(UserType::class);
    }

    public function department(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function gradeLevel(): HasMany
    {
        return $this->hasMany(GradeLevel::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function purchasedOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id'); // as buyer
    }

    public function soldOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'seller_code', 'seller_code'); // as seller
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

}

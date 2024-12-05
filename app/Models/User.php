<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
        'first_name',
        'last_name',
        'user_type_id',
        'wmsu_email',
        'wmsu_dept_id',
        'grade_level_id',
        'password',
        'profile_picture',
        'wmsu_id_front',
        'wmsu_id_back',
        'is_seller',
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
        return $this->hasMany(Order::class, 'user_id'); // as seller
    }
}

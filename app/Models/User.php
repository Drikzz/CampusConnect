<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
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
     * Get the email address that should be used for verification.
     *
     * @return string
     */
    public function getEmailForVerification()
    {
        return $this->wmsu_email;
    }

    /**
     * Route notifications for the mail channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return array|string
     */
    public function routeNotificationForMail($notification)
    {
        // Return the wmsu_email address
        return $this->wmsu_email;
    }

    /**
     * Get the email address for the user.
     * 
     * @return string
     */
    public function getEmailAttribute()
    {
        // This creates a virtual 'email' attribute that returns wmsu_email
        // This helps Laravel's built-in verification to work properly
        return $this->wmsu_email;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function userType()
    {
        return $this->belongsTo(UserType::class, 'user_type_id');
    }

    public function department()
    {
        // Change from hasMany to belongsTo since a user belongs to a department
        return $this->belongsTo(Department::class, 'wmsu_dept_id');
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

    public function verification()
    {
        return $this->hasOne(UserVerification::class);
    }

    public function usernameHistory()
    {
        return $this->hasMany(UsernameHistory::class);
    }

    public function meetupLocations()
    {
        return $this->hasMany(MeetupLocation::class);
    }

    public function getDefaultMeetupLocation()
    {
        return $this->meetupLocations()
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    public function wallet()
    {
        return $this->hasOne(SellerWallet::class, 'seller_code', 'seller_code');
    }

    /**
     * Get reviews written by this user.
     */
    public function writtenReviews()
    {
        return $this->hasMany(SellerReview::class, 'reviewer_id');
    }

    /**
     * Get reviews about this seller.
     */
    public function receivedReviews()
    {
        return $this->hasMany(SellerReview::class, 'seller_code', 'seller_code');
    }

    /**
     * Get average rating for this seller
     */
    public function getAverageRatingAttribute()
    {
        // Fetch all reviews to ensure we have complete data
        $reviews = $this->receivedReviews()->get();
        
        // Get the count of reviews for each rating value using a more reliable method
        $ratingCounts = [];
        foreach ($reviews as $review) {
            $rating = (int)$review->rating;
            if (!isset($ratingCounts[$rating])) {
                $ratingCounts[$rating] = 0;
            }
            $ratingCounts[$rating]++;
        }
        
        // Calculate total number of reviews
        $totalReviews = count($reviews);
        
        // Calculate sum of (rating × count) for each rating
        $weightedSum = 0;
        foreach ($ratingCounts as $rating => $count) {
            $weightedSum += $rating * $count;
        }
        
        // Return the average using the correct formula
        return $totalReviews > 0 ? $weightedSum / $totalReviews : 0;
    }

    /**
     * Get total number of reviews for this seller
     */
    public function getReviewsCountAttribute()
    {
        return $this->receivedReviews()->count();
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
/**
 * @property int $id
 * @property string $name
 * @property string $role
 * @property int|null $current_store_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Store[] $stores
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'last_login_at',
        'welcome_shown',
        'slug',
        'suspension_reason',
        'subscription_end_at',
        'expires_at',
        'plan_id',
        'allowed_stores',
        'allowed_accountants',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login_at'        => 'datetime',
        'expires_at'           => 'date',
        'subscription_end_at'  => 'date',
        'role'                 => 'string',
        'status'               => 'string',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot Events
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->slug = Str::slug($user->name) . '-' . Str::random(6);

            if (empty($user->subscription_end_at)) {
                $user->subscription_end_at = now()->addDays(3);
            }
        });

        static::created(function ($user) {
            $user->stores()->create([
                'name' => 'المتجر الرئيسي',
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | العلاقات
    |--------------------------------------------------------------------------
    */
    /**
     * جلب المتاجر التابعة للمالك
     * * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Store>
     */

    public function stores()
    {
        return $this->hasMany(\App\Models\Store::class)->orderBy('id');
    }

    public function accountants()
    {
        return $this->hasMany(Accountant::class);
    }

    public function employees()
    {
        return $this->hasManyThrough(Employee::class, Store::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /*
    |--------------------------------------------------------------------------
    | سكوبات
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /*
    |--------------------------------------------------------------------------
    | حدود الاشتراك
    |--------------------------------------------------------------------------
    */

    public function totalStores()
    {
        return $this->stores()->withTrashed()->count();
    }

    public function canCreateStore()
    {
        return $this->plan && $this->totalStores() < $this->plan->allowed_stores;
    }

    public function totalAccountants()
    {
        return $this->accountants()->withTrashed()->count();
    }

    public function canCreateAccountant()
    {
        return $this->plan && $this->totalAccountants() < $this->plan->allowed_accountants;
    }







    
}

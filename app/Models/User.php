<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'deleted_at',
    ];


    protected $appends = ['credit_total', 'debit_total'];

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
            'created_at' => 'datetime:Y-m-d H:i:s',
            'updated_at' => 'datetime:Y-m-d H:i:s',
            'deleted_at' => 'datetime:Y-m-d H:i:s'
        ];
    }

    /**
    * Get the JWT identifier.
    *
    * @return int
    */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
    * Get the JWT custom claims.
    *
    * @return array
    */
    public function getJWTCustomClaims()
    {
        return []; // You can add custom claims here if needed
    }

    public function getCreditTotalAttribute()
    {
        $id = $this->id;
        $transactions = Transaction::whereHas('customers', function($query) use($id) {
                                            $query->where('user_id', $id);
                                        })
                                    ->where('transaction_type', 1)
                                    ->sum('amount');
        return $transactions;
    }

    public function getDebitTotalAttribute()
    {
        $id = $this->id;
        $transactions = Transaction::whereHas('customers', function($query) use($id) {
                                            $query->where('user_id', $id);
                                        })
                                        ->where('transaction_type', 0)
                                        ->sum('amount');
        return $transactions;
    }
}

<?php

namespace App\Models;

use App\Notifications\MailResetPasswordNotification;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Customer extends Model
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    protected $casts = [
        'last_login' => 'datetime'
    ];
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'last_login',
        'pro_pic',
        'birth_date',
        'status',
        'type',
    ];
    protected static function booted()
    {
        static::updating(function ($customer) {
            if ($customer->pro_pic != $customer->getOriginal('pro_pic')) {
                if (Storage::exists($customer->getOriginal('pro_pic')))
                    Storage::delete($customer->getOriginal('pro_pic'));
            }
        });

        static::deleting(function ($customer) {
            if ($customer->pro_pic && Storage::exists($customer->pro_pic))
                Storage::delete($customer->pro_pic);
        });
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MailResetPasswordNotification($token));
    }
}
<?php

namespace Kaikon2\Kaikondb\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User as AppUser; // 変換用

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'login_failed'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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

    // App\Models\User → Kaikon2\Kaikondb\Models\User 変換用メソッドを実装
    public static function fromAppUser(AppUser $appUser): self
    {
        $attributes = [
            'id'                   => $appUser->id,
            'name'                 => $appUser->name,
            'email'                => $appUser->email,
            'password'             => $appUser->password,
            'login_failed'         => $appUser->login_failed,
            'email_verified_at'    => $appUser->email_verified_at,
            'remember_token'       => $appUser->remember_token,
        ];
    
        $user = new self();
        // 第二引数を true にすることで、すでに DB から読み込んだ状態（同期済み）として構築する
        $user->setRawAttributes($attributes, true); 
        return $user;
    }

    public function roles(){
        return $this->belongsToMany(\Kaikon2\Kaikondb\Models\Role::class);
    }

    public function profile(){
        return $this->hasOne(\Kaikon2\Kaikondb\Models\Profile::class);
    }

    public function user_login_logs(){
        return $this->hasMany(\Kaikon2\Kaikondb\Models\UserLoginLog::class);
    }
    
    public function last_login(){
        return $this->user_login_logs()->where('status','=','success')->latest('login_at')->value('login_at');
    }
    
    public function isAdmin()
    {
        return $this->roles->pluck('name')->contains('Administrator');
    }

    public function isModerator()
    {
        return $this->roles->pluck('name')->contains('Moderator');
    }
}

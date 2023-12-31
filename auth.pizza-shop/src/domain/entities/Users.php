<?php

namespace pizzashop\auth\api\domain\entities;

/**
 * Class Users qui permet de gérer les utilisateurs
 */
class Users extends \Illuminate\Database\Eloquent\Model
{

    protected $connection = 'auth';
    protected $table = 'users';
    protected $primaryKey = 'email';
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['email', 'password', 'active','activation_token', 'activation_token_expiration_date', 'refresh_token', 'refresh_token_expiration_date', 'reset_passwd_token', 'reset_passwd_token_expiration_date', 'username'];

}
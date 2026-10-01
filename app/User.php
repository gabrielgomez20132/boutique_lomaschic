<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    //use SyncsWithFirebase;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre', 
        'dni', 
        'dni', 
        'email',
        'password',  
        'id_uType',
        'activo', 
        'role_id', 
        'dni_cliente', 
        'telefono_cliente', 
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    /**
     * Admin
     *
     * 
     */
    const ADMIN_TYPE = 1;
    
    public function isAdmin()
    {        
        return $this->id_uType === self::ADMIN_TYPE;    
    }
}

<?php

namespace App;

class Auth
{
    static private $user;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    static public function user()
    {
        return self::$user;
    }

    static public function set_user(array|object|null $user)
    {
        self::$user = $user;
    }

    static public function check()
    {
        return (bool)self::$user;
    }
    
}

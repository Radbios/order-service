<?php

namespace App\Providers;

use App\Auth;
use Illuminate\Support\ServiceProvider;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(Request $request): void
    {
        Auth::set_user(
            $this->getUserFromToken($request)
        );
    }

      /**
     * Obtenha as informações do usuário a partir do token JWT
     *
     * @param  \Illuminate\Http\Request  $request
     * @return object|null
     */
    protected function getUserFromToken(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return null;
        }
        
        try {
            $payload = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            return (object) array_merge(['id' => $payload->sub], $payload->user ?? []);
        } catch (\Throwable $th) {
            return null;
        }

    }
}

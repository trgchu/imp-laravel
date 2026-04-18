<?php

namespace App\Providers;

use App\Models\Message;
use App\Policies\MessagePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * El mapeo de políticas para la aplicación.
     * Aquí vinculamos el Modelo con su Guardia (Policy).
     *
     * @var array
     */
    protected $policies = [
        Message::class => MessagePolicy::class,
    ];

    /**
     * Registra cualquier servicio de autenticación / autorización.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        /**
         * Opcional: Definir un Super Admin.
         * Esto permite que el usuario con ID 1 se salte las reglas de las policies
         * (Muy útil para debugear el chat).
         */
        Gate::before(function ($user, $ability) {
            return $user->id === 1 ? true : null;
        });
    }
}

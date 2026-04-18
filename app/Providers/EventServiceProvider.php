<?php

namespace App\Providers;

use App\Events\MessageSent;
use App\Events\ConversationUpdated;
use App\Events\UserJoinedConversation;
use App\Events\UserLeftConversation;
use App\Listeners\SendMessageNotification;
use App\Listeners\UpdateConversationUsers;
use App\Listeners\NotifyUserJoined;
use App\Listeners\NotifyUserLeft;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        MessageSent::class => [
            SendMessageNotification::class,
        ],
        ConversationUpdated::class => [
            UpdateConversationUsers::class,
        ],
        UserJoinedConversation::class => [
            NotifyUserJoined::class,
        ],
        UserLeftConversation::class => [
            NotifyUserLeft::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}

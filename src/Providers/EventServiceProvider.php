<?php

namespace Modules\Bigcommerce\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\ServingFusion' => [
            'Modules\Bigcommerce\Listeners\BootstrapAdminMenu',
        ],

        'App\Events\UserRegistered' => [
            'Modules\Bigcommerce\Listeners\LinkCustomerToCart',
        ],
    ];
}
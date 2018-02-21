<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class H5pNotification extends Notification implements ShouldQueue {

    public function handle(OrderShipped $event) {
        //
    }

    public function failed(OrderShipped $event, $exception) {
        //
    }

}

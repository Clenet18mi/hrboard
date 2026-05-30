<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public static function notify(User $user, string $message, string $type)
    {
        return Notification::create([
            'user_id' => $user->id,
            'message' => $message,
            'type' => $type,
        ]);
    }

    public static function notifyRH(string $message, string $type)
    {
        $rhs = User::role(['hr', 'super_admin'])->get();
        
        foreach ($rhs as $rh) {
            self::notify($rh, $message, $type);
        }
    }
}

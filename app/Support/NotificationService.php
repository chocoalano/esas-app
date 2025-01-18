<?php

namespace App\Support;

use App\Models\User;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Notifications\Actions\Action;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    public static function sendNotification(string $title, string $body, string $routeName = null, ?int $senderTo = null): void
    {
        // Retrieve the authenticated user
        $recipient = User::find($senderTo);

        if (!$recipient) {
            throw new \Exception('User is not authenticated.');
        }

        // Generate the URL for the notification
        $url = $routeName ? route($routeName) : '';

        // Dispatch a broadcast event
        broadcast(new MessageSent($body, Auth::user()->id, $senderTo, $url));

        // Send a database notification
        $recipient->notify(
            FilamentNotification::make()
                ->title($title)
                ->success()
                ->body($body)
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url($url)
                        ->markAsRead(),
                ])
                ->toDatabase()
        );

        // Send a broadcast notification
        $recipient->notify(
            FilamentNotification::make()
                ->title($title)
                ->success()
                ->body($body)
                ->actions([
                    Action::make('view')
                        ->button()
                        ->url($url)
                        ->markAsRead(),
                ])
                ->toBroadcast()
        );
    }
}

<?php

namespace App\Support;

use App\Models\FcmModel;
use App\Models\User;
use App\Support\NotificationService;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Support\Facades\Log;
use Throwable;

class FcmService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = app('firebase.messaging');
    }

    /**
     * Summary of sendFcmPermit
     * @param array $userIds
     * @return bool
     */
    public function sendFcmPermit(array $userIds): bool
    {
        $status = null;
        foreach ($userIds as $userId) { // Loop langsung userIds, lebih efisien
            $user = User::find($userId);
            if (!$user) {
                Log::warning("User not found: {$userId}");
                continue;
            }

            $token = FcmModel::where('user_id', $userId)->value('device_token'); // Ambil token per user
            if (!$token) {
                Log::warning("No FCM token found for user: {$userId}");
                continue;
            }

            $msg = "Halo, saya ({$user->name} - {$user->nip}) mengajukan permintaan, silakan periksa sekarang.";
            $url = route('filament.app.resources.permits.index');

            $localNotificationResult = NotificationService::sendNotification('Informasi permintaan', $msg, $url, $userId);

            if (!$localNotificationResult) {
                Log::error("Failed to send local notification for user: {$userId}");
                continue;
            }

            $message = CloudMessage::new()
                ->withNotification([
                    "title" => 'Informasi permintaan',
                    "body" => $msg
                ])
                ->withAndroidConfig(AndroidConfig::new())
                ->withApnsConfig(ApnsConfig::new());

            try {
                $this->messaging->send($message, $token);
                $status = true;
            } catch (Throwable $e) {
                Log::error("Failed to send FCM notification for user: {$userId}: " . $e->getMessage());
                $status =  false;
            }
        }
        return $status;
    }
}

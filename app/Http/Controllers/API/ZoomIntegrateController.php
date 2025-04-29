<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ZoomIntegrateController extends Controller
{

    public $clientID;
    public $clientSecret;
    public $accountID;

    public function __construct()
    {
        $this->clientID     = config('services.zoom.client_id');
        $this->clientSecret = config('services.zoom.client_secret');
        $this->accountID    = config('services.zoom.account_id');
    }
    private static function getZoomAccessToken()
    {
        $instance = new self();
        $response = Http::withBasicAuth($instance->clientID, $instance->clientSecret)
                    ->asForm()
                    ->post('https://zoom.us/oauth/token', [
                        'grant_type' => 'account_credentials',
                        'account_id' => $instance->accountID,
                    ]);

        return $response->json('access_token');
    }
    public static function createZoomMeeting($data){
        $accessToken = self::getZoomAccessToken();
        $response    = Http::withToken($accessToken)
                        ->post('https://api.zoom.us/v2/users/me/meetings', [
                            'topic' => $data['topic'],
                            'type' => 2,
                            'start_time' => $data['start_time'],
                            'duration' => $data['duration'],
                            'timezone' => 'UTC',
                            'agenda' => $data['agenda'],
                            'settings' => [
                                'host_video' => true,
                                'participant_video' => true,
                                'mute_upon_entry' => false,
                                'join_before_host' => true,
                                'waiting_room' => false,
                                // 'auto_recording' => 'cloud',
                            ],
                        ]);

        return $response->json();

    }

}

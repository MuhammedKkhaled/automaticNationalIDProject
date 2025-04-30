<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Services\ZoomService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class ZoomIntegrateController extends Controller
{

    public $clientID;
    public $clientSecret;
    public $accountID;

    protected $baseUrl;
    public function __construct()
    {
        $this->clientID     = config('services.zoom.client_id');
        $this->clientSecret = config('services.zoom.client_secret');
        $this->accountID    = config('services.zoom.account_id');
        $this->baseUrl      = config('services.zoom.url');
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
    public static function createZoomMeeting($data)
    {
        $accessToken = self::getZoomAccessToken();
        $response    = Http::withToken($accessToken)
            ->post('https://api.zoom.us/v2/users/me/meetings', [
                'topic' => $data['topic'],
                'type' => 1,
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

    public static function participants($meetingID)
    {
        $accessToken = self::getZoomAccessToken();
        // dd($accessToken);
        $response = Http::withToken($accessToken)
                        ->get("https://api.zoom.us/v2/report/meetings/".$meetingID."/participants");

                        return $response->json();


    }

    public static function getHubs(){
        $accessToken = self::getZoomAccessToken();
        $response = Http::withToken($accessToken)
                        ->get("https://api.zoom.us/v2/zoom_events/hubs?role_type=host");

                        return $response->json();
    }

    public static function createEvent($data){
        $instance = new Self();
        $accessToken = self::getZoomAccessToken();
        $response = Http::withToken($accessToken)
        ->withHeader('Content-Type' , 'application/json')
        ->post($instance->baseUrl . "/zoom_events/events" , [
            'name' => $data['name'],
            'description' => $data['description'],
            'event_type' => $data['event_type'],
            'access_level'=> $data['access_level'],
            "timezone"=> "Africa/Cairo",
            // "start_time" => "2025-06-03T20:51:00Z",
            "calendar"=> [

                  "start_time"=> "2025-07-28T13:00:00Z",
                  "end_time"=>"2025-07-30T13:00:00Z"
            ],
            "attendance_type" => 'VIRTUAL',
            'hub_id'=>"23asdfasdf3asdf",
            // 'consolidation_calendars' => [
            //     [
            //         'calendar_type' => 'SESSION_BASED',
            //     ]
            // ],
        ]);

        return $response->json();
    }

    public static function createWebinar($data ){

        $instance = new Self();

        $accessToken = self::getZoomAccessToken();

        $response = Http::withToken($accessToken)
                ->withHeader('Content-Type' , 'application/json')
                ->post($instance->baseUrl . "/users/me/webinars" , [
                    'topic' => $data['topic'],
                    'type' => 1,
                    'start_time' => $data['start_time'], // in ISO 8601 format
                    'duration' => $data['duration'], // in minute
                    'timezone' => 'Africa/Cairo',
                    'agenda' => $data['agenda'],
                    'settings' => [
                        "allow_multiple_devices" =>true,
                        "approval_type" => 0,
                        // "alternative_hosts"=> "mohamedkhaled25179@gmail.com",
                        'host_video' => true,
                        'participant_video' => true,
                        'mute_upon_entry' => false,
                        'join_before_host' => true,
                        'waiting_room' => false,
                        // 'auto_recording' => 'cloud',
                    ],
                    'schedule_for' => $data['schedule_for'], /// moez@career-180.com
                    "is_simulive" =>false,
                ]);

        return $response->json();
    }
}

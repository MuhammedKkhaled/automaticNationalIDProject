<?php

namespace App;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait UseZoom
{
    public $client;

    public $jwt;

    public $headers ;

    public $accessToken;

    public function __construct(){
        $this->client = new Client();
        $this->accessToken = "_srHZDBCQK2zhNNn2s6JHg";

        $this->headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'content-type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    function generateZoomAccessToken(){

        $apiKey = env('ZOOM_CLIENT_ID');

        $apiSecret = env('ZOOM_CLIENT_SECRECT');

        $account_id = env('ACCOUNT_ID');

        $base64credentials = base64_encode($apiKey . ':' . $apiSecret);

        $url = 'https://zoom.us/oauth/token?grant_type=account_credentials&account_id=' . $account_id;

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $base64credentials,
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->post($url);

        $responseData = $response->json();
        if (isset($responseData['access_token'])) {
            return $responseData['access_token'];
        } else {
            // Log or print the entire response for debugging purposes.
            Log::error('Zoom OAuth Token Response: ' . json_encode($responseData));

            // Handle the error as needed.
            return null; // You might want to return null or throw an exception here.
        }

    }



    public function toZoomTimeFormat(string $dateTime)
    {
        try {
            $date = new \DateTime($dateTime);

            return $date->format('Y-m-d\TH:i:s');
        } catch (\Exception $e) {
            Log::error('ZoomJWT->toZoomTimeFormat : ' . $e->getMessage());

            return '';
        }
    }



    public function create($data)
    {

        $accessToken = $this->generateZoomAccessToken();

        // $doctor = User::findOrfail(auth()->user()->id);


        $url = 'https://api.zoom.us/v2/users/me/meetings';

        $response = Http::withToken($accessToken)->post($url, [
            'topic'      => 'Online Meeting',
            'type'       => self::MEETING_TYPE_SCHEDULE,
            'start_time' => $this->toZoomTimeFormat($data['start_time']),
            'duration'   => 1,
            'agenda'     => 'Meeting for Patient',
            'timezone' => 'Africa/Cairo',
        ]);
            return [
                'success' => $response->getStatusCode() === 201,
                'data'    => json_decode($response->getBody(), true),
            ];


        if ($response->successful()) {
            return [
                'success' => $response->getStatusCode() === 201,
                'data'    => json_decode($response->getBody(), true),
            ];
        } else {
            return [
                'success' => $response->getStatusCode() === 201,
                'data'    => json_decode($response->getBody(), true),
            ];
            return response()->json(['error' => 'Failed to create a Zoom meeting'], 500);
        }
    }

}

<?php
namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ZoomService {

    protected $clientId;
    protected $clientSecret;
    protected $accountId;
    public function __construct()
    {
        $this->clientId = config('services.zoom.client_id');
        $this->clientSecret = config('services.zoom.client_secret');
        $this->accountId = config('services.zoom.account_id');
    }

    protected function getAccessToken()
    {
            $response = Http::asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post('https://zoom.us/oauth/token', [
                    'grant_type' => 'account_credentials',
                    'account_id' => $this->accountId,
                ]);

            if ($response->failed()) {
                throw new \Exception('Failed to fetch Zoom Access Token');
            }

            return $response->json('access_token');
    }

    protected function makeRequest($method, $endpoint, $data = [])
    {
        $accessToken = $this->getAccessToken();

        $url = 'https://api.zoom.us/v2/' . ltrim($endpoint, '/');

        $request = Http::withToken($accessToken);

        if ($method === 'GET') {
            $response = $request->get($url, $data);
        } elseif ($method === 'POST') {
            $response = $request->post($url, $data);
        } elseif ($method === 'PATCH') {
            $response = $request->patch($url, $data);
        } elseif ($method === 'DELETE') {
            $response = $request->delete($url);
        } else {
            throw new \Exception('Invalid HTTP method');
        }

        if ($response->failed()) {
            throw new \Exception('Zoom API Error: ' . $response->body());
        }

        return $response->json();
    }


    public function getMeetingParticipants($meetingId, $pageSize = 30)
    {
    return $this->makeRequest('GET', "report/meetings/{$meetingId}/participants", [
        'page_size' => $pageSize,
    ]);
    }
}

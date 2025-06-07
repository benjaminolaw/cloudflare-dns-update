<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CloudflareController extends Controller
{
    protected $apiUrl;
    protected $token;

    public function __construct(){
        $this->apiUrl = config('services.cloudflare.url');
        $this->token = config('services.cloudflare.token');
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' .$this->token,
            'Content-Type' => 'application/json'
        ];

    }

    /**
     * @throws ConnectionException
     */
    public function getZones()
    {
        $response = Http::withHeaders($this->headers())->get($this->apiUrl.'/zones');

        return $response->json()['result'] ?? null;
    }

    /**
     * @throws ConnectionException
     */
    public function getARecords(string $zoneId){
        $response = Http::withHeaders($this->headers())->get($this->apiUrl.'/zones/'.$zoneId.'/dns_records', [
            'type' => 'A'
        ]);
        return $response->json()['result'] ?? null;
    }

    /**
     * @throws ConnectionException
     */
    public function updateARecords(string $zoneId, array $record, string $newIp){
        $data = [
            'type' => 'A',
            'name' => $record['name'],
            'content' => $newIp,
            'ttl' => $record['ttl'],
            'proxied' => $record['proxied'],
        ];

        return Http::withHeaders($this->headers())->patch("{$this->apiUrl}/zones/{$zoneId}/dns_records/{$record['id']}", $data)->json();
    }



    public function updateAllARecords($newIp){
        $zones = $this->getZones();
        $results = [];

        foreach($zones as $zone){
            $zoneId = $zone['id'];
            $zoneName = $zone['name'];
            $aRecords = $this->getARecords($zoneId);

            foreach($aRecords as $record){
                $response = $this->updateARecords($zoneId, $record, $newIp);
                $results[] = [
                    'domain' => $zoneName,
                    'record' => $record['name'],

                    'success' => $response['success'] ?? false,

                    'message' => $response['errors'][0]['message'] ?? "Record Updated",
                ];
            }
        }
        return $results;



   }
}

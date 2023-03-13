<?php

namespace App\Service\Http;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImmutableXClient
{
    private const USER_AGENT = 'Wash trading UA';
    private const API_BASE = 'https://api.x.immutable.com';

    private HttpClientInterface $client;

    public function __construct()
    {
        $this->client = HttpClient::createForBaseUri(self::API_BASE, [
            'headers' => [
                'User-Agent' => self::USER_AGENT,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json, text/plain, */*',
            ]
        ]);
    }

    public function getTransfers(\DateTime $date, string $cursor = null): array
    {
        $endpoint = '/v1/transfers?min_timestamp=' . $date->format('Y-m-d') . 'T00:00:00Z' .
            '&max_timestamp=' . $date->format('Y-m-d') . 'T23:59:59Z' .
            '&status=success&order_by=created_at&direction=asc';

        if ($cursor) {
            $endpoint .= '&cursor=' . $cursor;
        }

        $response = $this->client->request('GET', $endpoint);

        return $response->toArray();
    }
}

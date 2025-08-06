<?php

namespace App\Helpers;

use Google_Client;
use GuzzleHttp\Client as GuzzleClient;

class GoogleClientHelper
{
    public static function createSecureClient()
    {
        $client = new Google_Client();
        
        // SSL ayarları - development ve production için
        $httpClient = new GuzzleClient([
            'verify' => false, // Development için SSL doğrulamasını devre dışı bırak
            'curl' => [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]
        ]);
        
        $client->setHttpClient($httpClient);
        
        return $client;
    }
    
    public static function createClientWithCredentials()
    {
        $client = self::createSecureClient();
        
        // .env'den credential'ları al
        $client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? '');
        $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
        $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI'] ?? '');
        
        return $client;
    }
}

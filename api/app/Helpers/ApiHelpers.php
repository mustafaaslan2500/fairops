<?php

namespace App\Helpers;

use GeoIp2\Database\Reader;
use Exception;

class ApiHelpers
{
    public static function show_message($bool_status, $str_message, $data = [])
    {
        $output_data = [
            'status' => $bool_status,
            'message' => $str_message,
        ];

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $output_data[$key] = $value;
            }
        }

        return $output_data;
    }

    public static function get_location_info()
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $database = $_ENV['GEO_LIB_PATH'];

        try {
            $reader = new Reader($database);
            $record = $reader->city($ipAddress);

            return [
                'user_info' => [
                    "country" => $record->country->name,
                    "country_code" => $record->country->isoCode,
                    "city" => $record->city->name,
                    "timezone" => $record->location->timeZone,
                    "ip" => $ipAddress
                ]
            ];
        } catch (Exception $e) {
            return false;
        }
    }

    public static function send($url, $data, $method = 'POST')
    {
        $curl = curl_init();

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                break;
            case 'GET':
                if (!empty($data)) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
                break;
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            curl_close($curl);
            return ['error' => true, 'message' => $error_msg];
        }

        curl_close($curl);

        return ['error' => false, 'response' => $response];
    }
}

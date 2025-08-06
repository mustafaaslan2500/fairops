<?php

namespace App\Controllers;

use App\Models\User;

class ApiController
{
    public static function test()
    {
        $startOverall = microtime(true);

        $startDb = microtime(true);
        $user = User::getUser(37);
        $endDb = microtime(true);
        $dbTime = $endDb - $startDb;

        $startHash = microtime(true);
        $passwordValid = password_verify('password123', $user->u_password);
        $endHash = microtime(true);
        $hashTime = $endHash - $startHash;

        $endOverall = microtime(true);
        $overallTime = $endOverall - $startOverall;

        return [
            'status' => 'success',
            'db_query_time' => number_format($dbTime, 4) . ' seconds',
            'password_verify_time' => number_format($hashTime, 4) . ' seconds',
            'overall_process_time' => number_format($overallTime, 4) . ' seconds',
            'server_ip' => $_SERVER['SERVER_ADDR'],
            'server_name' => $_SERVER['SERVER_NAME']
        ];
    }
}

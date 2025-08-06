<?php

namespace App\Models;

use PDO;
use PDOException;

class Connect
{
    private static $pdo;

    /**
     * Veritabanı bağlantısını başlat ve döndür
     */
    public static function initialize($dbName = null): PDO
    {
        try {
            $host = $_ENV['DB_HOST'];
            $dbname = $dbName ?? $_ENV['DB_DATABASE'];
            $username = $_ENV['DB_USERNAME'];
            $password = $_ENV['DB_PASSWORD'];
            $charset = $_ENV['DB_CHARSET'];

            return new PDO(
                "mysql:host=$host;dbname=$dbname;charset=$charset",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
}

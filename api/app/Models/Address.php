<?php

namespace App\Models;

use App\Models\Connect;
use PDO;
use PDOException;

class Address
{
    public static function getCountries()
    {
        $pdo = Connect::initialize();

        try {
            $sql = "SELECT id,names FROM countries WHERE active = 1";

            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getCities($countryId)
    {
        $pdo = Connect::initialize();

        try {
            $sql = "SELECT id,name FROM cities WHERE country_id = :countryId AND active = 1";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':countryId', $countryId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getDistricts($cityId)
    {
        $pdo = Connect::initialize();

        try {
            $sql = "SELECT id,name FROM districts WHERE city_id = :cityId AND active = 1";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':cityId', $cityId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getDetailLocation($countryCode, $cityName)
    {
        if (empty($countryCode) || empty($cityName)) return false;
        $pdo = Connect::initialize();

        try {
            $sql = "CALL get_location_by_country_code_and_city_name(:countryCode,:cityName);";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':countryCode', $countryCode, PDO::PARAM_INT);
            $stmt->bindParam(':cityName', $cityName, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getAddressInfo($countryId, $cityId, $districtId)
    {
        $pdo = Connect::initialize();

        try {
            $sql = "CALL get_location(:countryId, :cityId, :districtId);";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':countryId', $countryId, PDO::PARAM_INT);
            $stmt->bindParam(':cityId', $cityId, PDO::PARAM_INT);
            $stmt->bindParam(':districtId', $districtId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }
}

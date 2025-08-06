<?php

namespace App\Models;

use App\Models\Connect;
use PDO;
use PDOException;

class User
{
    /**
     * Get user by ID
     */
    public static function getUser($u_id)
    {
        if (empty($u_id)) return false;

        $pdo = Connect::initialize();

        try {
            $stmt = $pdo->prepare("
                SELECT u.*, ut.ut_token
                FROM users AS u
                JOIN user_tokens AS ut ON u.u_id = ut.ut_user_id
                WHERE u.u_id = :u_id
                LIMIT 1
            ");
            $stmt->bindParam(':u_id', $u_id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            // Log the exception or handle it
            return false;
        }
    }

    /**
     * Get user by token
     */
    public static function getUserWithToken($token)
    {
        if (empty($token)) return false;

        $pdo = Connect::initialize();

        try {
            $stmt = $pdo->prepare("
                SELECT u.*, ut.ut_token
                FROM users AS u
                JOIN user_tokens AS ut ON u.u_id = ut.ut_user_id
                WHERE ut.ut_token = :token
                LIMIT 1
            ");
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            // Log the exception or handle it
            return false;
        }
    }

    /**
     * Get user by email
     */
    public static function getUserWithEmail($email)
    {
        if (empty($email)) return false;

        $pdo = Connect::initialize();

        try {
            $stmt = $pdo->prepare("
                SELECT u.*, ut.ut_token
                FROM users AS u
                JOIN user_tokens AS ut ON u.u_id = ut.ut_user_id
                WHERE u.u_email = :email
                LIMIT 1
            ");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            // Log the exception or handle it
            return false;
        }
    }

    /**
     * Check if email exists
     */
    public static function emailControl($email)
    {
        if (empty($email)) return false;

        $pdo = Connect::initialize();

        try {
            $stmt = $pdo->prepare("
                SELECT u_id
                FROM users
                WHERE u_email = :email
                LIMIT 1
            ");
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            // Log the exception or handle it
            return false;
        }
    }

    /**
     * Save a new user
     */
    public static function userSave($user_data)
    {
        if (empty($user_data)) return false;

        $pdo = Connect::initialize();

        try {
            $columns = implode(", ", array_keys($user_data));
            $placeholders = ":" . implode(", :", array_keys($user_data));

            $stmt = $pdo->prepare("
                INSERT INTO users ($columns)
                VALUES ($placeholders)
            ");

            foreach ($user_data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();

            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            // Log the exception or handle it
            return false;
        }
    }

    public static function saveLoginLog($userId, $data, $detailData = null)
    {
        if (empty($userId) || empty($data)) return false;

        $pdo = Connect::initialize();

        try {
            if (!empty($detailData)) {
                $stmt = $pdo->prepare("
                UPDATE users SET u_user_info = :userInfo, u_last_active_date = NOW(), u_current_country = :cCountryId, u_current_city = :cCityId WHERE u_id = :uId
            ");
                $stmt->bindValue(':uId', $userId);
                $stmt->bindValue(':userInfo', $data);
                $stmt->bindValue(':cCountryId', $detailData->country_id);
                $stmt->bindValue(':cCityId', $detailData->city_id);
                $stmt->execute();
            } else {
                $stmt = $pdo->prepare("
                UPDATE users SET u_user_info = :userInfo, u_last_active_date = NOW()WHERE u_id = :uId
            ");
                $stmt->bindValue(':uId', $userId);
                $stmt->bindValue(':userInfo', $data);
                $stmt->execute();
            }

            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getUserNumber($u_id)
    {
        if (empty($u_id)) return false;

        $pdo = Connect::initialize();

        try {
            $stmt = $pdo->prepare("
                SELECT u_phone_number
                FROM users
                WHERE u_id = :u_id
                LIMIT 1
            ");
            $stmt->bindParam(':u_id', $u_id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_OBJ)->u_phone_number ?? null;
        } catch (PDOException $e) {
            // Log the exception or handle it
            return false;
        }
    }

    /**
     * Create a user token
     */
    public static function userTokenCreate($token_data)
    {
        if (empty($token_data)) return false;

        $pdo = Connect::initialize();

        try {
            $columns = implode(", ", array_keys($token_data));
            $placeholders = ":" . implode(", :", array_keys($token_data));

            $stmt = $pdo->prepare("
                INSERT INTO user_tokens ($columns)
                VALUES ($placeholders)
            ");

            foreach ($token_data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            // Log the exception or handle it
            return false;
        }
    }
}

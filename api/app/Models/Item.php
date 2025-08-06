<?php

namespace App\Models;

use App\Models\Connect;

use PDO;
use PDOException;

class Item
{
    /**
     * Save a new item
     */
    public static function itemSave($user_data)
    {
        if (empty($user_data)) return false;

        $pdo = Connect::initialize();

        try {
            $columns = implode(", ", array_keys($user_data));
            $placeholders = ":" . implode(", :", array_keys($user_data));

            $stmt = $pdo->prepare("
                INSERT INTO posts ($columns)
                VALUES ($placeholders)
            ");

            foreach ($user_data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            $stmt->execute();

            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Update an existing item
     */
    public static function ItemUpdate($updateData, $condition)
    {
        if (empty($updateData) || empty($condition)) return false;

        $pdo = Connect::initialize();

        try {
            $setParts = [];
            foreach ($updateData as $key => $value) {
                $setParts[] = "$key = :$key";
            }
            $setClause = implode(", ", $setParts);

            $whereParts = [];
            foreach ($condition as $key => $value) {
                $whereParts[] = "$key = :cond_$key";
            }
            $whereClause = implode(" AND ", $whereParts);

            $stmt = $pdo->prepare("UPDATE posts SET $setClause WHERE $whereClause");

            foreach ($updateData as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            foreach ($condition as $key => $value) {
                $stmt->bindValue(':cond_' . $key, $value);
            }

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Get item by ID
     */
    public static function getItem($i_id, $user_id = null)
    {
        if (empty($i_id)) return false;

        $pdo = Connect::initialize();

        try {
            $sql = "SELECT * FROM posts WHERE id = :i_id";

            if (!empty($user_id)) {
                $sql .= " AND user_id = :user_id";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':i_id', $i_id, PDO::PARAM_INT);

            if (!empty($user_id)) {
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            }

            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getItemFeatures($i_id)
    {
        if (empty($i_id)) return false;

        $pdo = Connect::initialize();

        try {
            $sql = "SELECT * FROM post_features WHERE post_id = :i_id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':i_id', $i_id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getModelsWithCategoryId($categoryId)
    {
        if (empty($categoryId)) return false;

        $pdo = Connect::initialize();

        try {
            $sql = "SELECT id,name,parent_id FROM models WHERE category_id = :c_id AND active = 1";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':c_id', $categoryId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function addSelectedFlag($postId)
    {
        if (empty($postId)) return false;

        $pdo = Connect::initialize();

        try {
            $sql = "UPDATE posts SET selected_feature = 1 WHERE id = :c_id";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':c_id', $postId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function saveSelectedFeatures($selectedFeatures, $postId)
    {
        if (empty($selectedFeatures) || empty($postId)) {
            return false;
        }

        $pdo = Connect::initialize();

        try {
            $sql = "INSERT INTO post_features (post_id, feature_code, feature_value_id) VALUES ";

            $placeholders = array_fill(0, count($selectedFeatures), "(?, ?, ?)");
            $sql .= implode(", ", $placeholders);

            $values = [];
            foreach ($selectedFeatures as $feature) {
                $values[] = (int) $postId;
                $values[] = htmlspecialchars($feature['p_code'], ENT_QUOTES, 'UTF-8');
                $values[] = (int) $feature['value'];
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);

            return true;
        } catch (PDOException $e) {
            error_log("Veritabanı hatası: " . $e->getMessage());
            return false;
        }
    }
}

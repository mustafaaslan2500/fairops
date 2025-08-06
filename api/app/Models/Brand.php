<?php

namespace App\Models;

use App\Models\Connect;
use PDO;
use PDOException;

class Brand
{
    public static function findBrand($searchTerm, $multi = null)
    {
        $pdo = Connect::initialize();

        if ($multi) {
            $searchTerms = explode(" ", $searchTerm);
            $conditions = [];

            foreach ($searchTerms as $term) {
                $conditions[] = "name LIKE :term_" . md5($term);
            }

            $query = "SELECT * FROM brands WHERE " . implode(" OR ", $conditions);
            $stmt = $pdo->prepare($query);

            foreach ($searchTerms as $term) {
                $stmt->bindValue(":term_" . md5($term), '%' . $term . '%');
            }
        } else {
            $query = "SELECT * FROM brands WHERE name LIKE :term";
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':term', '%' . $searchTerm . '%');
        }

        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function getBrandWithCategoryId($categoryId)
    {
        if (empty($categoryId)) return false;

        $pdo = Connect::initialize();

        try {
            $sql = "SELECT id,name FROM brands WHERE category_id = :c_id AND active = 1";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':c_id', $categoryId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }
}

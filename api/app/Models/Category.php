<?php

namespace App\Models;

use App\Models\Connect;
use App\Models\Brand;
use App\Models\Features;
use PDO;
use PDOException;

class Category
{
    /**
     * Get user by ID
     */
    public static function getAllCategories()
    {
        $pdo = Connect::initialize();

        try {
            $stmt = $pdo->prepare("SELECT * FROM categories");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getCategory($categoryId)
    {
        if (empty($categoryId)) return false;

        $pdo = Connect::initialize();

        try {
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :cId LIMIT 1");
            $stmt->bindParam(':cId', $categoryId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getTopCategory()
    {
        $pdo = Connect::initialize();

        try {
            $stmt = $pdo->prepare("SELECT category_id FROM top_categories");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function findCategory($searchTerm, $multi = null)
    {
        $pdo = Connect::initialize();

        if ($multi) {
            $searchTerms = explode(" ", mb_strtolower($searchTerm, 'UTF-8'));
            $conditions = [];

            foreach ($searchTerms as $term) {
                $conditions[] = "LOWER(name) LIKE :term_" . md5($term);
            }

            $query = "SELECT * FROM categories WHERE " . implode(" AND ", $conditions);
            $stmt = $pdo->prepare($query);

            foreach ($searchTerms as $term) {
                $stmt->bindValue(":term_" . md5($term), '%' . $term . '%');
            }
        } else {
            $query = "SELECT * FROM categories WHERE LOWER(name) LIKE LOWER(:term)";
            $stmt = $pdo->prepare($query);
            $stmt->bindValue(':term', '%' . mb_strtolower($searchTerm, 'UTF-8') . '%');
        }

        try {
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $scoredResults = [];
            foreach ($results as $result) {
                $score = 0;
                foreach ($searchTerms as $term) {
                    if (mb_stripos(mb_strtolower($result['name'], 'UTF-8'), mb_strtolower($term, 'UTF-8'), 0, 'UTF-8') !== false) {
                        $score++;
                    }
                }
                $result['score'] = $score;
                $scoredResults[] = $result;
            }

            usort($scoredResults, function ($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            return $scoredResults;
        } catch (PDOException $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function fixFeaturesField($getArray)
    {
        $getNewArray = [];
        foreach ($getArray as $key => $model) {
            $data = [
                "id" => (int) $model->id,
                "name" => $model->name
            ];

            if (!empty($model->parent_id)) $data['parent_id'] = (int) $model->parent_id;

            $getNewArray[] = $data;
        }

        return $getNewArray;
    }

    public static function getCategoryFeatures($categoryId)
    {
        if (empty($categoryId)) return false;

        $getBrands = Brand::getBrandWithCategoryId($categoryId);
        $getModels = Item::getModelsWithCategoryId($categoryId);
        $getFeatures = Features::getFeaturesWithCategoryId($categoryId);

        if (!empty($getBrands)) $getBrands = self::fixFeaturesField($getBrands);
        if (!empty($getModels)) $getModels = self::fixFeaturesField($getModels);
        if (!empty($getFeatures)) $getFeatures = self::transformToSelections($getFeatures);

        $data = [
            "selections" => []
        ];

        if (!empty($getBrands)) {
            $data["selections"][] = [
                "p_code" => "b",
                "parent" => "brand",
                "parent_name" => "Marka",
                "parent_choose" => false,
                "up_parent" => "",
                "data" => $getBrands
            ];
        }

        if (!empty($getModels)) {
            $data["selections"][] = [
                "p_code" => "m",
                "parent" => "model",
                "parent_name" => "Model",
                "parant_choose" => true,
                "up_parent" => "brand",
                "data" => $getModels
            ];
        }

        if (!empty($getFeatures)) {
            foreach ($getFeatures as $key => $feature) {
                $data["selections"][] = $feature;
            }
        }

        return $data;
    }

    public static function transformToSelections($array)
    {
        $groupedData = [];

        foreach ($array as $item) {
            $id = $item->id;
            $name = $item->name;

            if (!isset($groupedData[$id])) {
                $groupedData[$id] = [
                    "p_code" => "f-" . $id,
                    "parent" => "",
                    "parent_name" => $name,
                    "parent_choose" => false,
                    "up_parent" => "",
                    "data" => []
                ];
            }

            $groupedData[$id]['data'][] = [
                "id" => $item->value_id,
                "name" => $item->value
            ];
        }

        return array_values($groupedData);
    }
}

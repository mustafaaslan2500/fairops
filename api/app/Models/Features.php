<?php

namespace App\Models;

use App\Models\Connect;
use PDO;
use PDOException;

class Features
{
    public static function getFeaturesWithCategoryId($categoryId)
    {
        if (empty($categoryId)) return false;

        $pdo = Connect::initialize();

        try {
            $sql = "
            SELECT
                f.id,
                f.name,
                fv.id AS value_id,
                fv.name AS value
            FROM
                features AS f
            INNER JOIN
                feature_values AS fv
            ON
                fv.feature_id = f.id AND f.category_id = :c_id
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':c_id', $categoryId, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            return false;
        }
    }

    public static function getFormatedFeatureList($list)
    {
        if (empty($list)) return false;

        $pdo = Connect::initialize();

        $brandId = null;
        $modelId = null;
        $featureIds = [];
        $featureValues = [];

        foreach ($list as $item) {
            if ($item->feature_code === 'b') {
                $brandId = $item->feature_value_id;
            } elseif ($item->feature_code === 'm') {
                $modelId = $item->feature_value_id;
            } elseif (preg_match('/f-(\d+)/', $item->feature_code, $matches)) {
                $featureId = (int)$matches[1];
                $featureIds[] = $featureId;
                $featureValues[$featureId][] = $item->feature_value_id;
            }
        }

        $formattedFeatures = [];

        if ($brandId) {
            $stmt = $pdo->prepare("SELECT id, name FROM brands WHERE id = :value_id");
            $stmt->execute(['value_id' => $brandId]);
            $brand = $stmt->fetch(PDO::FETCH_OBJ);

            if ($brand) {
                $formattedFeatures[] = [
                    'feature_id' => $brand->id,
                    'feature_name' => 'Marka',
                    'feature_value_name' => $brand->name
                ];
            }
        }

        if ($modelId) {
            $stmt = $pdo->prepare("SELECT id, name FROM models WHERE id = :value_id");
            $stmt->execute(['value_id' => $modelId]);
            $model = $stmt->fetch(PDO::FETCH_OBJ);

            if ($model) {
                $formattedFeatures[] = [
                    'feature_id' => $model->id,
                    'feature_name' => 'Model',
                    'feature_value_name' => $model->name
                ];
            }
        }

        if (!empty($featureIds)) {
            $placeholders = implode(',', array_fill(0, count($featureIds), '?'));

            $stmt = $pdo->prepare("SELECT id, name FROM features WHERE id IN ($placeholders)");
            $stmt->execute($featureIds);
            $features = $stmt->fetchAll(PDO::FETCH_OBJ);

            foreach ($features as $feature) {
                if (isset($featureValues[$feature->id])) {
                    $valuePlaceholders = implode(',', array_fill(0, count($featureValues[$feature->id]), '?'));
                    $params = array_merge([$feature->id], $featureValues[$feature->id]);

                    $stmt = $pdo->prepare("
                        SELECT id, name 
                        FROM feature_values 
                        WHERE feature_id = ? AND id IN ($valuePlaceholders)
                    ");
                    $stmt->execute($params);
                    $values = $stmt->fetchAll(PDO::FETCH_OBJ);

                    foreach ($values as $value) {
                        $formattedFeatures[] = [
                            'feature_id' => $feature->id,
                            'feature_name' => $feature->name,
                            'feature_value_name' => $value->name
                        ];
                    }
                }
            }
        }

        return $formattedFeatures;
    }
}

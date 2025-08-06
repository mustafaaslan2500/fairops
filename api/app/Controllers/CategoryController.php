<?php

namespace App\Controllers;

use App\Helpers\Lang;
use App\Controllers\UserController;
use App\Helpers\ApiHelpers;
use App\Models\Category;

class CategoryController
{
    public function __construct()
    {
        session_start();
        Lang::setLanguage(!empty($_SESSION['SYSTEM_LANG']) ? $_SESSION['SYSTEM_LANG'] : "tr");
    }

    public function get_categories()
    {
        $lang = Lang::importLang("category_page_lang");

        if (!UserController::loginControl()) {
            return ApiHelpers::show_message(false, $lang['please_log_in']);
        }

        $categories = [];
        $all_categories = Category::getAllCategories();
        $categories = self::buildCategoryTree($all_categories);

        $data_to_cache = [
            'categories_count' => count($categories),
            'categories' => $categories
        ];

        return ApiHelpers::show_message(true, "", $data_to_cache);
    }

    public static function findCategoryTree($categoryId)
    {
        if (empty($categoryId)) {
            return [];
        }
    
        $chain = [];
        $currentId = $categoryId;
    
        while ($cat = Category::getCategory($currentId)) {
            $chain[] = $cat;
            if (empty($cat->sub_id)) {
                break;
            }
            $currentId = $cat->sub_id;
        }
    
        if (empty($chain)) {
            return [];
        }
    
        if (empty(end($chain)->sub_id)) {
            array_pop($chain);
        }
    
        $chain = array_reverse($chain);
    
        $tree = null;
        foreach ($chain as $cat) {
            $tree = [
                "id"                => $cat->id,
                "name"              => $cat->name,
                "description"       => $cat->description,
                "icon"              => $cat->icon ?? "",
                "sub_category_info" => [
                    "sub_count"      => $tree ? 1 : 0,
                    "sub_categories" => $tree ? [$tree] : []
                ]
            ];
        }
    
        return $tree;
    }

    public static function buildCategoryTree($all_categories, $parent_id = null)
    {
        $branch = [];
        foreach ($all_categories as $category) {
            if ($category->sub_id == $parent_id) {
                $children = self::buildCategoryTree($all_categories, $category->id);
                $branch[] = [
                    "id" => $category->id,
                    "name" => $category->name,
                    "description" => $category->description,
                    "icon" => $category->icon ?? "",
                    "sub_category_info" => [
                        "sub_count" => count($children),
                        "sub_categories" => $children
                    ]
                ];
            }
        }
        return $branch;
    }
}

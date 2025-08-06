<?php

namespace App\Controllers;

use App\Helpers\Lang;
use App\Controllers\CategoryController;
use App\Models\Category;
use App\Helpers\ApiHelpers;
use App\Helpers\Validator;
use App\Models\Features;
use App\Models\Item;
use App\Models\Image;
use App\Models\Address;
use App\Models\User;

class ItemController
{
    private string $locale;
    private array $lang;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->locale = $_SESSION['SYSTEM_LANG'] ?? 'tr';
        Lang::setLanguage($this->locale);
        $this->lang = Lang::importLang("address_page_lang");
    }

    public function get_select_category_page()
    {
        $get_category_tree = Category::getAllCategories();
        $create_category_tree = CategoryController::buildCategoryTree($get_category_tree);
        $get_top_categories = Category::getTopCategory();
        $top_ids = !empty($get_top_categories) ? array_column($get_top_categories, 'category_id') : [];
        $filter_vate = self::filterCategories($create_category_tree, $top_ids);
        $data = [
            "page_info" => [
                "title" => "İlan Kategorisi Seç",
                "page_texts" => [
                    "normal_text_1" => "Ne İlanı Vereceksin",
                    "input_text_1" => "Kategori Ara"
                ]
            ],
            "category_tree" => $filter_vate
        ];
        return ApiHelpers::show_message(true, "", $data);
    }

    public function get_search_category_page($params)
    {
        $searchTerm = trim($params['sentence']);

        if (empty($searchTerm)) return "fill the sentence params";

        $fixer_request = ApiHelpers::send($_ENV['PYTHON_SENTENCE_FIX_SERVICE'], [
            'sentence' => $params['sentence'],
        ], 'POST');

        $ai_fix_sentence = json_decode($fixer_request['response'], true)['fixed_sentence'];
        $search_category = Category::findCategory($ai_fix_sentence, true);
        $search_result = array_map(fn($item) => [
            'id' => $item['id'],
            'name' => $item['name']
        ], $search_category);

        $data = [
            "search_message" => count($search_result) . " Sonuç Bulundu.",
            "categories" => $search_result
        ];

        return ApiHelpers::show_message(true, "", $data);
    }

    public function filterCategories($categories, $allowedIds)
    {
        $filteredCategories = [];

        foreach ($categories as $category) {
            if (in_array($category['id'], $allowedIds)) {
                $filteredCategories[] = $category;
            } elseif (!empty($category['sub_category_info']['sub_categories'])) {
                $subCategories = self::filterCategories($category['sub_category_info']['sub_categories'], $allowedIds);

                if (!empty($subCategories)) {
                    $filteredCategories = array_merge($filteredCategories, $subCategories);
                }
            }
        }

        return $filteredCategories;
    }

    public function UploadPhoto()
    {
        if (isset($_FILES['photo'])) {
            $image = new Image();
            $result = $image->upload($_FILES['photo']);
            echo json_encode($result);
        } else {
            echo json_encode(['error' => 'No file uploaded']);
        }

        $demo_data = [
            "jpg" => [
                "lg" => "",
                "md" => "",
                "sm" => ""
            ],
            "webp" => [
                "lg" => "",
                "md" => "",
                "sm" => ""
            ]
        ];
    }

    public function GetItem($params, $justItem = null)
    {
        $lang = Lang::importLang("add_page_lang");

        if (empty($params['item_id'])) return ApiHelpers::show_message(false, $lang['invalid_create_item_id']);

        $item = Item::getItem($params['item_id']);
        if (!$item) return ApiHelpers::show_message(false, $lang['invalid_create_item_id']);

        $featuresQu = Item::getItemFeatures($item->id);
        $features = Features::getFormatedFeatureList($featuresQu);
        $contentData = $item->media ? self::getContentData($item->media) : [];

        $getItemAdressInfo = Address::getAddressInfo(
            $item->address_country_id,
            $item->address_city_id,
            $item->address_district_id
        )[0];

        return 

        $data = [
            "item_info" => [
                "id" => $item->id,
                "title" => $item->title,
                "description" => $item->description,
                "price" => $item->price,
                "show_number" => $item->show_number,
                "user_number" => User::getUserNumber($item->user_id)
            ],
            "item_media" => $contentData,
            "item_location" => [
                "country" => json_decode(($getItemAdressInfo->country_names ?? ""), true)[$this->locale]['title'],
                "city" => $getItemAdressInfo->city_name,
                "streets" => $getItemAdressInfo->district_name,
            ],
            "item_features" => $features,
            "category_tree" => CategoryController::findCategoryTree($item->category_id)
        ];

        if ($justItem) {
            return $data;
        } else {
            return ApiHelpers::show_message(true, "", $data);
        }
    }

    public function getContentData($media)
    {
        $media = json_decode($media, true);

        if (empty($media)) return null;
        $data = [];
        $dir = "https://cdn.paylastika.com/" . $media['dir_code'] . "/";

        if ($media['contents']['cover_content']['type'] == "image") {
            $data['cover_content'] = [
                "content_type" => "image",
                "big" => $dir . $media['contents']['cover_content']['code'] . "_big.jpg",
                "lg" => $dir . $media['contents']['cover_content']['code'] . "_lg.jpg",
                "md" => $dir . $media['contents']['cover_content']['code'] . "_md.jpg",
                "sm" => $dir . $media['contents']['cover_content']['code'] . "_sm.jpg",
            ];
        } else {
            $data['cover_content'] = [
                "content_type" => "video",
                "1080" => $dir . $media['contents']['cover_content']['code'] . "_1080p.mp4",
                "orginal" => $dir . $media['contents']['cover_content']['code'] . "_original.mp4",
                "thumb" => $dir . $media['contents']['cover_content']['code'] . "_thumb.jpg"
            ];
        }

        if (!empty($media['contents']['sub_content'])) {
            foreach ($media['contents']['sub_content'] as $key => $value) {
                if ($value['type'] == "image") {
                    $data['sub_contents'][] = [
                        "content_type" => "image",
                        "big" => $dir . $value['code'] . "_big.jpg",
                        "lg" => $dir . $value['code'] . "_lg.jpg",
                        "md" => $dir . $value['code'] . "_md.jpg",
                        "sm" => $dir . $value['code'] . "_sm.jpg",
                    ];
                } else {
                    $data['sub_contents'][] = [
                        "content_type" => "video",
                        "1080" => $dir . $value['code'] . "_1080p.mp4",
                        "orginal" => $dir . $value['code'] . "_original.mp4",
                        "thumb" => $dir . $value['code'] . "_thumb.jpg"
                    ];
                }
            }
        }

        return $data;
    }

    public function PreviewItem($params)
    {
        $lang = Lang::importLang("add_page_lang");

        $data = [
            "page_info" => [
                "title" => "İlan Önizleme",
                "page_texts" => [
                    "normal_text_1" => "İlanı Yayınla",
                ]
            ],
            "item" => self::GetItem($params, true)
        ];
        return ApiHelpers::show_message(true, "", $data);
    }

    public function ItemPublish($params)
    {
        $lang = Lang::importLang("add_page_lang");

        if (empty($params['item_id'])) return ApiHelpers::show_message(false, $lang['invalid_create_item_id']);

        $item = Item::getItem($params['item_id'], $_SESSION['user_id']);
        if (!$item) return ApiHelpers::show_message(false, $lang['invalid_create_item_id']);

        if ($item->publish_status == 1) return ApiHelpers::show_message(false, $lang['already_publish']);

        $data = Item::ItemUpdate([
            "publish_status" => 1
        ], ["id" => $params['item_id']]);

        if ($data) {
            return ApiHelpers::show_message(true, $lang['publish_success']);
        } else {
            return ApiHelpers::show_message(true, $lang['publish_error']);
        }
    }

    public function AddItem($params)
    {
        $lang = Lang::importLang("add_page_lang");
        if (empty($_SESSION['user_id'])) return ApiHelpers::show_message(true, $lang['please_log_in']);

        $validator = new Validator(
            $params,
            [
                'title' => 'required|max:100',
                'description' => 'required|max:200',
                'price' => 'required|max:20',
                'category_id' => 'required|numeric|max:11',
                'country_id' => 'required|numeric|max:11',
                'city_id' => 'required|numeric|max:11',
                'district_id' => 'required|numeric|max:11'
            ],
            [
                'title' => [
                    $lang['title_cannot_be_empty'],
                    $lang['title_can_be_up_to_100_characters']
                ],
                'description' => [
                    $lang['description_cannot_be_empty'],
                    $lang['description_can_be_up_to_200_characters']
                ],
                'price' => [
                    $lang['price_cannot_be_empty'],
                    $lang['invalid_price']
                ],
                'category_id' => [
                    $lang['category_id_cannot_be_empty'],
                    $lang['category_id_is_invalid'],
                    $lang['category_id_is_invalid']
                ],
                'country_id' => [
                    $lang['country_id_cannot_be_empty'],
                    $lang['country_id_is_invalid'],
                    $lang['country_id_is_invalid']
                ],
                'city_id' => [
                    $lang['city_id_cannot_be_empty'],
                    $lang['city_id_is_invalid'],
                    $lang['city_id_can_be_up_to_teen_characters']
                ],
                'district_id' => [
                    $lang['district_id_cannot_be_empty'],
                    $lang['district_id_is_invalid'],
                    $lang['district_id_can_be_up_to_teen_characters']
                ]
            ]
        );

        if ($validator->fails()) {
            $error_detail = $validator->errors();
            return ApiHelpers::show_message(false, current($error_detail));
        }

        if (!empty($params['media'])) {
            $photoValidate = self::validatePhotoData($params['media']);
            if ($photoValidate['validFormat'] != true) return ApiHelpers::show_message(true, $lang['wrong_format']);
            if ($photoValidate['hasCoverContent'] != true) return ApiHelpers::show_message(true, $lang['cover_image_is_empty']);
            $savePhotos = self::savePhotos($params['media']);
        }

        $save_data = [
            'user_id' => intval($_SESSION['user_id']),
            'title' => $params['title'],
            'description' => $params['description'],
            'price' => $params['price'],
            'media' => (!empty($savePhotos) ? $savePhotos['data'] : ""),
            'show_number' => (intval($params['show_number']) == 1 ? 1 : 0),
            'category_id' => intval($params['category_id']),
            'address_country_id' => $params['country_id'],
            'address_city_id' => $params['city_id'],
            'address_district_id' => $params['district_id'],
            'create_date' => date('Y-m-d H:i:s')
        ];

        if (!empty($params['show_map']) && $params['show_map'] == 1 && !empty($params['map_lat']) && !empty($params['map_let'])) {
            $save_data['location_lat'] = $params['map_lat'];
            $save_data['location_let'] = $params['map_let'];
        }

        $item_saved_data_id = Item::itemSave($save_data);
        return ApiHelpers::show_message(true, $lang['item_create_success'], [
            "create_item_id" => $item_saved_data_id
        ]);
    }

    public function savePhotos($contents)
    {
        $contentData = json_decode($contents, true);

        if (!isset($contentData['contents'])) {
            throw new \Exception("Invalid content format.");
        }

        $baseImagePath = 'C:\\python_service\\paylastika\\uploads\\images\\';
        $baseVideoPath = 'C:\\python_service\\paylastika\\uploads\\videos\\';
        $cdnBasePath   = 'C:\\paylastika_cdn\\';
        $newCode = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 2)), 0, 30);


        $fileVariants = [
            'image' => ['big.jpg', 'lg.jpg', 'md.jpg', 'sm.jpg'],
            'video' => ['1080p.mp4', 'original.mp4', 'thumb.jpg'],
        ];

        $allContents = [];

        if (isset($contentData['contents']['cover_content'])) {
            $allContents[] = $contentData['contents']['cover_content'];
        }
        if (isset($contentData['contents']['sub_content']) && is_array($contentData['contents']['sub_content'])) {
            $allContents = array_merge($allContents, $contentData['contents']['sub_content']);
        }

        foreach ($allContents as $item) {
            if (!isset($item['code'], $item['type'])) {
                continue;
            }

            $code = $item['code'];
            $type = $item['type'];

            if (!in_array($type, ['image', 'video'], true)) {
                continue;
            }

            $sourcePath = ($type === 'image') ? $baseImagePath : $baseVideoPath;
            $variants   = $fileVariants[$type];

            $targetFolder = $cdnBasePath . $newCode . '\\';
            if (!is_dir($targetFolder)) {
                mkdir($targetFolder, 0777, true);
            }

            foreach ($variants as $variant) {
                $fileName   = $code . '_' . $variant;
                $sourceFile = $sourcePath . $fileName;
                $targetFile = $targetFolder . $fileName;

                if (file_exists($sourceFile)) {
                    rename($sourceFile, $targetFile);
                }
            }
        }

        $contentData['dir_code'] = $newCode;

        return [
            'dir_code' => $newCode,
            'data' => json_encode($contentData)
        ];
    }


    public function validatePhotoData(string $json): array
    {
        $payload = json_decode($json, true);

        if (!isset($payload['contents']) || !is_array($payload['contents'])) {
            return ['validFormat' => false, 'hasCoverContent' => false, 'subContentCount' => 0];
        }

        $contents = $payload['contents'];

        $hasCover = isset($contents['cover_content'])
            && is_array($contents['cover_content'])
            && isset($contents['cover_content']['code'], $contents['cover_content']['type'])
            && is_string($contents['cover_content']['code'])
            && $contents['cover_content']['code'] !== ''
            && in_array($contents['cover_content']['type'], ['image', 'video'], true);

        $subContent = $contents['sub_content'] ?? null;
        $valid = is_array($subContent);

        $count = 0;
        if ($valid) {
            foreach ($subContent as $item) {
                if (
                    is_array($item)
                    && isset($item['code'], $item['type'])
                    && is_string($item['code'])
                    && $item['code'] !== ''
                    && in_array($item['type'], ['image', 'video'], true)
                ) {
                    $count++;
                }
            }
        }

        return [
            'validFormat'      => $valid,
            'hasCoverContent'  => $hasCover,
            'subContentCount'  => $count,
        ];
    }



    public function ListFeatures($params)
    {
        $lang = Lang::importLang("add_page_lang");
        if (empty($_SESSION['user_id'])) return ApiHelpers::show_message(true, $lang['please_log_in']);
        if (empty($params['create_item_id']) || !is_numeric($params['create_item_id'])) return ApiHelpers::show_message(true, $lang['invalid_create_item_id']);
        $item_id = $params['create_item_id'];

        $item = Item::getItem($item_id, $_SESSION['user_id']);
        if (!$item) return ApiHelpers::show_message(false, $lang['not_found_item']);
        $get_all_features = Category::getCategoryFeatures($item->category_id);


        return ApiHelpers::show_message(true, "", $get_all_features);
    }

    public function SelectFeatures($params)
    {
        $lang = Lang::importLang("add_page_lang");
        if (empty($_SESSION['user_id'])) return ApiHelpers::show_message(true, $lang['please_log_in']);
        if (empty($params['create_item_id']) || !is_numeric($params['create_item_id'])) return ApiHelpers::show_message(true, $lang['invalid_create_item_id']);

        $item = Item::getItem($params['create_item_id']);
        if (!$item) return ApiHelpers::show_message(true, $lang['invalid_create_item_id']);
        if ($item->selected_feature == 1) return ApiHelpers::show_message(true, "özellikler zaten eklenmiş");

        $features_data = self::ListFeatures($params);
        $selections = $features_data['selections'];
        $postId = intval($params['create_item_id']);
        $selectionCodes = array_column($selections, 'p_code');

        $paramsKeys = array_keys($params);
        $successfulCodes = [];

        foreach ($selections as $selection) {
            $p_code = $selection['p_code'];
            $parent_name = $selection['parent_name'];

            if (!array_key_exists($p_code, $params) || empty($params[$p_code])) {
                return ApiHelpers::show_message(false, $parent_name . " seçilmedi.");
            }

            if (!is_numeric($params[$p_code])) {
                return ApiHelpers::show_message(false, $parent_name . " geçersiz. Sadece sayısal değer girin.");
            }

            $data_ids = array_column($selection['data'], 'id');

            if (!in_array($params[$p_code], $data_ids)) {
                return ApiHelpers::show_message(false, "Geçersiz " . strtolower($parent_name) . " değeri.");
            }

            $successfulCodes[] = [
                "p_code" => $p_code,
                "value" => $params[$p_code]
            ];
        }

        $save_data = Item::saveSelectedFeatures($successfulCodes, $postId);
        if ($save_data) Item::addSelectedFlag($postId);

        return ApiHelpers::show_message(true, "Özellik seçimleri başarı ile eklendi");
    }

    public function GetItemPage()
    {
        $data = [
            "page_info" => [
                "title" => "İlan Bilgileri",
                "page_texts" => [
                    "text_1" => "İlan Fotoğrafları",
                    "text_2" => "Kapak Fotoğrafı",
                    "text_3" => "İlan Bilgileri",
                    "text_4" => "İlan Başlığı",
                    "text_5" => "Başlık giriniz.",
                    "text_6" => "İlan Açıklaması",
                    "text_7" => "Açıklama giriniz.",
                    "text_8" => "İlan Fiyat Bilgisi",
                    "text_9" => "Fiyat giriniz.",
                    "text_10" => "İlan Adresi Seçiniz",
                    "text_11" => "Adres Seç",
                    "text_12" => "Numaranız İlanda Gözüksün mü?",
                    "text_13" => "Devam Et",
                ]
            ]
        ];
        return ApiHelpers::show_message(true, "", $data);
    }
}

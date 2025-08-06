<?php

namespace App\Controllers;

use App\Helpers\Lang;
use App\Models\Address;
use App\Helpers\ApiHelpers;

use App\Models\User;

class HomeController
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

    public function Home()
    {
        if (empty($_SESSION['user_id'])) return ApiHelpers::show_message(false, $this->lang['please_log_in']);
        $user = User::getUser($_SESSION['user_id']);

        return [
            "current_address" => [
                "address_text" => "Sultanbeyliğ",
                "location" => [
                    "country" => [
                        "id" => 1,
                        "name" => "Türkiye"
                    ],
                    "city" =>  [
                        "id" => 1,
                        "name" => "İstanbul"
                    ],
                    "streets" =>  [
                        "id" => 3,
                        "name" => "Sultanbeyliğ"
                    ]
                ]
            ],
            "history_stacks" => [
                "stack_count" => 32,
                "stacks" => []
            ],
            "scroll_content" => [
                [
                    "product_info" => [
                        "id" => 9854,
                        "name" => "Samsung a71 cihazımı satıyorum daha 2 ay bile kulanılmadı",
                        "description" => "",
                        "price" => 1.0,
                        "location_city" => "İstanbul",
                        "location_street" => "Çekmeköy",
                        "favorite_count" => 24,
                        "comment_count" => 3,
                        "media" => [
                            "cover" => [
                                "url" => "https://content.paylastika.com/dfgwedtrhwh456453.mp4",
                                "type" => "video"
                            ],
                            "sub_content" => [
                                [
                                    "url" => "https://content.paylastika.com/dfgwedtrhwh456453.jpg",
                                    "type" => "photo"
                                ],
                                [
                                    "url" => "https://content.paylastika.com/dfgwedtrhwh456453.jpg",
                                    "type" => "photo"
                                ]
                            ]
                        ]
                    ],
                    "user_info" => [
                        "id" => 678,
                        "name" => "Kerem Seçgin",
                        "profile_photo" => "https://content.paylastika.com/profile_photo/iiyrf66438787"
                    ]

                ],
                [
                    "product_info" => [
                        "id" => 9854,
                        "name" => "Samsung a71 cihazımı satıyorum daha 2 ay bile kulanılmadı",
                        "description" => "",
                        "price" => 1.0,
                        "location_city" => "İstanbul",
                        "location_street" => "Sancaktepe",
                        "favorite_count" => 24,
                        "comment_count" => 3,
                        "media" => [
                            "cover" => [
                                "url" => "https://content.paylastika.com/dfgwedtrhwh456453.mp4",
                                "type" => "video"
                            ],
                            "sub_content" => [
                                [
                                    "url" => "https://content.paylastika.com/dfgwedtrhwh456453.jpg",
                                    "type" => "photo"
                                ],
                                [
                                    "url" => "https://content.paylastika.com/dfgwedtrhwh456453.jpg",
                                    "type" => "photo"
                                ]
                            ]
                        ]
                    ],
                    "user_info" => [
                        "id" => 678,
                        "name" => "Kerem Seçgin",
                        "profile_photo" => "https://content.paylastika.com/profile_photo/iiyrf66438787"
                    ]

                ]
            ]
        ];
    }
}

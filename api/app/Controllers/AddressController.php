<?php

namespace App\Controllers;

use App\Helpers\Lang;
use App\Models\Address;
use App\Helpers\ApiHelpers;

class AddressController
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

    public function getCountryList()
    {
        if (empty($_SESSION['user_id'])) return ApiHelpers::show_message(false, $this->lang['please_log_in']);

        $data = [];
        $allCountries = Address::getCountries();

        foreach ($allCountries as $country) {
            $data[] = [
                "id" => $country->id,
                "name" => json_decode($country->names, true)[$this->locale]['title']
            ];
        }

        return ApiHelpers::show_message(true, '', [
            "countries" => $data
        ]);
    }

    public function getCityList($params)
    {
        if (empty($_SESSION['user_id'])) return ApiHelpers::show_message(false, $this->lang['please_log_in']);
        if (empty($params['country_id']) && is_numeric($params['country_id'])) return ApiHelpers::show_message(false, $this->lang['invalid_country_id']);

        $allCity = Address::getCities($params['country_id']);

        return ApiHelpers::show_message(true, '', [
            "cities" => $allCity
        ]);
    }

    public function getDistrictList($params)
    {
        if (empty($_SESSION['user_id'])) return ApiHelpers::show_message(false, $this->lang['please_log_in']);
        if (empty($params['city_id']) && is_numeric($params['city_id'])) return ApiHelpers::show_message(false, $this->lang['invalid_city_id']);

        $allDistrict = Address::getDistricts($params['city_id']);

        return ApiHelpers::show_message(true, '', [
            "distircts" => $allDistrict
        ]);
    }
}

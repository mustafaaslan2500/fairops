<?php

namespace App\Controllers;

use App\Http\Request;
use App\Helpers\Lang;
use App\Helpers\ApiHelpers;
use App\Helpers\Validator;

use App\Models\Module;

class ModuleController
{
    private string $locale;
    private array $lang;
    private Request $request;
    private Module $module;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $this->locale = $_SESSION['SYSTEM_LANG'] ?? 'tr';
        Lang::setLanguage($this->locale);
        $this->lang = Lang::importLang("module_page_lang");

        $this->request = new Request();
        $this->module  = new Module();

        if ($this->request->isError()) {
            echo json_encode(ApiHelpers::show_message(false, $this->request->isError()));
            http_response_code(400);
            exit;
        }
    }

    public function moduleSearch()
    {
        $postData = $this->request->all();

        $validator = new Validator(
            $postData,
            [
                'search_term' => 'required|max:100'
            ],
            [
                'search_term' => [
                    $this->lang['search_term_cannot_be_empty'],
                    $this->lang['search_term_can_be_up_to_100_characters']
                ]
            ]
        );

        if ($validator->fails()) {
            $error_detail = $validator->errors();
            return ApiHelpers::show_message(false, current($error_detail));
        }

        $modules = $this->module->searchModules($postData['search_term']);

        if ($modules) {
            return ApiHelpers::show_message(true, 'Arama sonuçları çıkarıldı', [
                'modules' => $modules
            ]);
        } else {
            return ApiHelpers::show_message(false, $this->lang['no_modules_found']);
        }
    }
}

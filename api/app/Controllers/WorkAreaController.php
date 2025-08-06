<?php

namespace App\Controllers;

use App\Http\Request;
use App\Helpers\Lang;
use App\Helpers\ApiHelpers;
use App\Helpers\Validator;

use App\Models\WorkArea;

class WorkAreaController
{
    private string $locale;
    private array $lang;
    private Request $request;
    private WorkArea $workAreaModel;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $this->locale = $_SESSION['SYSTEM_LANG'] ?? 'tr';
        Lang::setLanguage($this->locale);
        $this->lang = Lang::importLang("work_area_lang");

        $this->request = new Request();
        $this->workAreaModel = new WorkArea();

        if ($this->request->isError()) {
            echo json_encode(ApiHelpers::show_message(false, $this->request->isError()));
            http_response_code(400);
            exit;
        }
    }

    public function createWorkArea()
    {
        $postData = $this->request->all();

        $validator = new Validator(
            $postData,
            [
                'company_name' => 'required|max:200',
                'company_address' => 'max:500',
                'company_number' => 'max:20',
                'subdomain' => 'required|regex:/^[a-z0-9_]+$/|max:100',
                'admin_users.first_name' => 'required|regex:/^[\pL\s]+$/u|max:100',
                'admin_users.last_name' => 'required|regex:/^[\pL\s]+$/u|max:100',
                'admin_users.email' => 'required|email|max:250',
                'admin_users.password' => 'required|min:8|max:100'
            ],
            [
                'company_name' => [
                    $this->lang['company_name_cannot_be_empty'],
                    $this->lang['company_name_can_be_up_to_200_characters']
                ],
                'company_address' => [
                    $this->lang['company_address_can_be_up_to_500_characters'] ?? 'Company address can be up to 500 characters'
                ],
                'company_number' => [
                    $this->lang['company_number_can_be_up_to_20_characters'] ?? 'Company number can be up to 20 characters'
                ],
                'subdomain' => [
                    $this->lang['namespace_cannot_be_empty'],
                    $this->lang['name_can_only_contain_letters'],
                    $this->lang['name_can_be_up_to_100_characters']
                ],
                'admin_users.first_name' => [
                    $this->lang['name_cannot_be_empty'],
                    $this->lang['name_can_only_contain_letters'],
                    $this->lang['name_can_be_up_to_100_characters']
                ],
                'admin_users.last_name' => [
                    $this->lang['surname_cannot_be_empty'],
                    $this->lang['surname_can_only_contain_letters'],
                    $this->lang['surname_can_be_up_to_100_characters']
                ],
                'admin_users.email' => [
                    $this->lang['email_address_cannot_be_empty'],
                    $this->lang['email_address_is_invalid'],
                    $this->lang['email_address_can_be_up_to_250_characters']
                ],
                'admin_users.password' => [
                    $this->lang['password_cannot_be_empty'],
                    $this->lang['password_must_be_at_least_8_characters'],
                    $this->lang['password_can_be_up_to_100_characters']
                ]
            ]
        );

        if ($validator->fails()) {
            $error_detail = $validator->errors();
            return ApiHelpers::show_message(false, current($error_detail));
        }

        if ($this->workAreaModel->domainControl($postData['subdomain']) > 0) {
            return ApiHelpers::show_message(false, $this->lang['error_domain_exists']);
        }

        if (!is_array($postData['modules'])) {
            return ApiHelpers::show_message(false, $this->lang['error_invalid_modules']);
        }

        $selectedModulesData = $this->workAreaModel->getModules($postData['modules']);

        $data = [
            'name' => $postData['company_name'],
            'address' => $postData['company_address'] ?? null,
            'phone' => $postData['company_number'] ?? null,
            'domain' => $postData['subdomain'],
            'db_name' => $postData['subdomain'] . '_db',
            'modules' => $selectedModulesData
        ];

        $result = $this->workAreaModel->createArea($data);

        if (!$this->workAreaModel->createCompanyDB($postData['company_name'], $postData['subdomain'] . '_db')) {
            return ApiHelpers::show_message(false, 'Database creation failed');
        }

        $this->workAreaModel->setAdminUser([
            'first_name' => $postData['admin_users']['first_name'] ?? '',
            'last_name' => $postData['admin_users']['last_name'] ?? '',
            'email' => $postData['admin_users']['email'] ?? '',
            'password' => $postData['admin_users']['password'],
        ], $postData['subdomain'] . '_db');

        $this->workAreaModel->setModules($selectedModulesData, $result);

        return ApiHelpers::show_message(true, $this->lang['success_work_area_created'], [
            'company_id' => $result,
        ]);
    }

    public function domainControl()
    {
        $subdomain = $this->request->get('domain');

        if (!preg_match('/^[a-z0-9_]+$/', $subdomain)) {
            return ApiHelpers::show_message(false, $this->lang['error_invalid_subdomain_format']);
        }

        if (strlen($subdomain) > 100) {
            return ApiHelpers::show_message(false, $this->lang['error_subdomain_too_long']);
        }

        if ($this->workAreaModel->domainControl($subdomain) > 0) {
            return ApiHelpers::show_message(true, "", [
                'subdomain' => $subdomain,
                'domain_info' => []
            ]);
        }

        return ApiHelpers::show_message(false, $this->lang['subdomain_is_valid'], ['subdomain' => $subdomain]);
    }

    public function getWorkAreaDetail()
    {
        $postData = $this->request->all();

        if (empty($postData['company_id'])) {
            return ApiHelpers::show_message(false, $this->lang['error_company_id_required']);
        }

        $getCompanyData = $this->workAreaModel->getWorkAreaDetail($postData['company_id']);
        if (!$getCompanyData) {
            return ApiHelpers::show_message(false, $this->lang['error_no_work_area_found']);
        }

        $getCompanyData['company_info'] = $this->buildWorkInfo($getCompanyData['company_info']);

        return ApiHelpers::show_message(true, $this->lang['success_work_area_info_retrieved'], $getCompanyData);
    }

    public function removeWorkArea()
    {
        $postData = $this->request->all();

        if (empty($postData['company_id'])) {
            return ApiHelpers::show_message(false, $this->lang['error_company_id_required']);
        }

        $result = $this->workAreaModel->removeWorkArea($postData['company_id']);

        if (!$result) {
            return ApiHelpers::show_message(false, $this->lang['error_failed_to_remove_work_area']);
        }

        return ApiHelpers::show_message(true, $this->lang['success_work_area_removed'], [
            'company_id' => $postData['company_id']
        ]);
    }

    public function editWorkArea()
    {
        $postData = $this->request->all();

        $validator = new Validator(
            $postData,
            [
                'company_id' => 'required|numeric',
                'company_name' => 'max:200',
                'company_address' => 'max:500',
                'company_number' => 'max:20',
                'add_modules' => 'array',
                'removed_modules' => 'array'
            ],
            [
                'company_id' => [
                    $this->lang['error_company_id_required'],
                    $this->lang['error_company_id_must_be_numeric']
                ],
                'company_name' => [
                    $this->lang['company_name_cannot_be_empty'],
                    $this->lang['company_name_can_be_up_to_200_characters']
                ],
                'company_address' => [
                    $this->lang['company_address_can_be_up_to_500_characters'] ?? 'Company address can be up to 500 characters'
                ],
                'company_number' => [
                    $this->lang['company_number_can_be_up_to_20_characters'] ?? 'Company number can be up to 20 characters'
                ],
                'add_modules' => [$this->lang['error_invalid_modules']],
                'removed_modules' => [$this->lang['error_invalid_modules']],
            ]
        );

        if ($validator->fails()) {
            $error_detail = $validator->errors();
            return ApiHelpers::show_message(false, current($error_detail));
        }

        $addModulesData = $this->workAreaModel->getModules($postData['add_modules']);
        $result = $this->workAreaModel->editWorkArea($postData, $addModulesData);

        if (!$result) {
            return ApiHelpers::show_message(false, $this->lang['error_failed_to_add_work_area']);
        }

        return ApiHelpers::show_message(true, $this->lang['success_work_area_created'], $result);
    }

    public function getWorkAreaInfo()
    {
        $postData = $this->request->all();

        if (empty($postData['company_id'])) {
            return ApiHelpers::show_message(false, $this->lang['error_company_id_required']);
        }

        $workAreaInfo = $this->workAreaModel->getWorkAreaInfo($postData['company_id']);

        if (empty($workAreaInfo)) {
            return ApiHelpers::show_message(false, $this->lang['error_no_work_area_found']);
        }

        return ApiHelpers::show_message(true, $this->lang['success_work_area_info_retrieved'], [
            'work_area_info' => $this->buildWorkInfo($workAreaInfo)
        ]);
    }

    public function buildWorkInfo($workAreaInfo)
    {
        $modules = (!empty(json_decode($workAreaInfo['modules'], true)) ? json_decode($workAreaInfo['modules'], true) : []);

        return [
            'id' => $workAreaInfo['id'],
            'name' => $workAreaInfo['name'],
            'address' => $workAreaInfo['address'] ?? null,
            'phone' => $workAreaInfo['number'] ?? null,
            'domain' => $workAreaInfo['domain'],
            //'modules' => $this->workAreaModel->getModules(array_column($modules, 'id'), true),
            'work_area_url' => "https://" . $workAreaInfo['domain'] . '.' . $_ENV['SITE_URL'],
            'created_at' => $workAreaInfo['created_at'],
        ];
    }

    public function addUserToWorkArea()
    {
        $postData = $this->request->all();

        $validator = new Validator(
            $postData,
            [
                'company_id' => 'required|numeric',
                'user.first_name' => 'required|regex:/^[\pL\s]+$/u|max:100',
                'user.last_name' => 'required|regex:/^[\pL\s]+$/u|max:100',
                'user.email' => 'required|email|max:250',
                'user.password' => 'required|min:8|max:100',
                'user.is_admin' => 'required|boolean'
            ],
            [
                'company_id' => [
                    $this->lang['error_company_id_and_user_id_required'],
                    $this->lang['error_company_id_must_be_numeric']
                ],
                'user.first_name' => [
                    $this->lang['name_cannot_be_empty'],
                    $this->lang['name_can_only_contain_letters'],
                    $this->lang['name_can_be_up_to_100_characters']
                ],
                'user.last_name' => [
                    $this->lang['surname_cannot_be_empty'],
                    $this->lang['surname_can_only_contain_letters'],
                    $this->lang['surname_can_be_up_to_100_characters']
                ],
                'user.email' => [
                    $this->lang['email_address_cannot_be_empty'],
                    $this->lang['email_address_is_invalid'],
                    $this->lang['email_address_can_be_up_to_250_characters']
                ],
                'user.password' => [
                    $this->lang['password_cannot_be_empty'],
                    $this->lang['password_must_be_at_least_8_characters'],
                    $this->lang['password_can_be_up_to_100_characters']
                ],
                'user.is_admin' => [
                    $this->lang['is_admin_cannot_be_empty'],
                    $this->lang['error_invalid_is_admin_value']
                ]
            ]
        );

        if ($validator->fails()) {
            $error_detail = $validator->errors();
            return ApiHelpers::show_message(false, current($error_detail));
        }

        $result = $this->workAreaModel->addUserToWorkArea($postData['company_id'], $postData['user']);

        if ($result === false) {
            return ApiHelpers::show_message(false, $this->lang['error_failed_to_add_user_to_work_area']);
        } else if ($result === "already_exists") {
            return ApiHelpers::show_message(false, $this->lang['error_user_already_exists']);
        } else if ($result === "user_limit_exceeded") {
            return ApiHelpers::show_message(false, $this->lang['error_user_limit_exceeded']);
        }

        return ApiHelpers::show_message(true, $this->lang['success_user_added_to_work_area']);
    }

    public function editWorkAreaUser()
    {
        $postData = $this->request->all();

        $validator = new Validator(
            $postData,
            [
                'company_id' => 'required|numeric',
                'user_id' => 'required|numeric',
                'user.first_name' => 'required|regex:/^[\pL\s]+$/u|max:100',
                'user.last_name' => 'required|regex:/^[\pL\s]+$/u|max:100',
                'user.email' => 'required|email|max:250',
                'user.is_admin' => 'required|boolean'
            ],
            [
                'company_id' => [
                    $this->lang['error_company_id_and_user_id_required'],
                    $this->lang['error_company_id_must_be_numeric']
                ],
                'user_id' => [
                    $this->lang['error_company_id_and_user_id_required']
                ],
                'user.first_name' => [
                    $this->lang['name_cannot_be_empty'],
                    $this->lang['name_can_only_contain_letters'],
                    $this->lang['name_can_be_up_to_100_characters']
                ],
                'user.last_name' => [
                    $this->lang['surname_cannot_be_empty'],
                    $this->lang['surname_can_only_contain_letters'],
                    $this->lang['surname_can_be_up_to_100_characters']
                ],
                'user.email' => [
                    $this->lang['email_address_cannot_be_empty'],
                    $this->lang['email_address_is_invalid'],
                    $this->lang['email_address_can_be_up_to_250_characters']
                ],
                'user.is_admin' => [
                    $this->lang['is_admin_cannot_be_empty'],
                    $this->lang['error_invalid_is_admin_value']
                ]
            ]
        );

        if (!empty($postData['user']['password'])) {
            $validator->addRule('user.password', 'required|min:8|max:100');
            $validator->addCustomMessage('user.password', [
                $this->lang['password_cannot_be_empty'],
                $this->lang['password_must_be_at_least_8_characters'],
                $this->lang['password_can_be_up_to_100_characters']
            ]);
        } else {
            $postData['user']['password'] = null;
        }

        if ($validator->fails()) {
            $error_detail = $validator->errors();
            return ApiHelpers::show_message(false, current($error_detail));
        }

        $result = $this->workAreaModel->editWorkAreaUser($postData['company_id'], $postData['user_id'], $postData['user']);

        if ($result === false) {
            return ApiHelpers::show_message(false, $this->lang['error_failed_to_edit_work_area_user']);
        }

        return ApiHelpers::show_message(true, $this->lang['success_work_area_user_edited']);
    }

    public function removeWorkAreaUser()
    {
        $postData = $this->request->all();

        if (empty($postData['company_id']) || empty($postData['user_id'])) {
            return ApiHelpers::show_message(false, $this->lang['error_company_id_and_user_id_required']);
        }

        $result = $this->workAreaModel->removeWorkAreaUser($postData['company_id'], $postData['user_id']);

        if ($result === false) {
            return ApiHelpers::show_message(false, $this->lang['error_failed_to_remove_work_area_user']);
        }

        return ApiHelpers::show_message(true, $this->lang['success_work_area_user_removed']);
    }

    public function listWorkAreas()
    {
        $postData = $this->request->all();
        $workAreas = $this->workAreaModel->listWorkAreas(intval($postData['page'] ?? 1), 20, $postData['search_term'] ?? null);
        $workAreaCount = $this->workAreaModel->getWorkAreaCount($postData['search_term'] ?? null);

        if (empty($workAreas)) {
            return ApiHelpers::show_message(false, $this->lang['error_no_work_areas_found']);
        }

        return ApiHelpers::show_message(true, $this->lang['success_work_areas_retrieved'], [
            'current_page' => intval($postData['page'] ?? 1),
            'total_pages' => ceil($workAreaCount / 20),
            'work_areas' => $this->buildWorkList($workAreas)
        ]);
    }

    public function buildWorkList($list)
    {
        $workList = [];
        foreach ($list as $item) {
            $workList[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'domain' => $item['domain'],
                'work_area_url' => "https://" . $item['domain'] . '.' . $_ENV['SITE_URL']
            ];
        }
        return $workList;
    }
}

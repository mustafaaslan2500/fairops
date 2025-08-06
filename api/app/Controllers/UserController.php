<?php

namespace App\Controllers;

use App\Http\Request;
use App\Models\User;
use App\Helpers\ApiHelpers;
use App\Helpers\Validator;
use App\Helpers\Lang;
use App\Models\Address;
use App\Helpers\GoogleClientHelper;
use Google_Client;
use Google_Service_Oauth2;
use GuzzleHttp\Client as GuzzleClient;
use Exception;

class UserController
{
    private string $locale;
    private array $lang;
    private Request $request;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $this->locale = $_SESSION['SYSTEM_LANG'] ?? 'tr';
        Lang::setLanguage($this->locale);
        $this->lang = Lang::importLang("user_page_lang");

        $this->request = new Request();
    }

    public function get_my_info()
    {
        $lang = Lang::importLang("user_page_lang");

        if (empty($_SESSION['user_id'])) return ApiHelpers::show_message(true, $lang['please_login']);

        $user = User::getUser(intval($_SESSION['user_id']));

        if ($user) {
            $user = self::UserField($user);
            return ApiHelpers::show_message(true, '', [
                "user_data" => $user
            ]);
        } else {
            self::UserLogout(true);
            return ApiHelpers::show_message(true, $lang['user_not_found']);
        }
    }

    public static function UserField($user)
    {
        if (empty($user)) return false;
        return [
            'id' => (int) $user->u_id,
            'name' => $user->u_first_name,
            'surname' => $user->u_last_name,
            'email' => $user->u_email,
            'phone' => $user->u_phone_number,
            'is_admin' => (bool) $user->u_admin,
            'token' => $user->ut_token,
        ];
    }

    public function UserLogin($adminUser = false)
    {
        if (isset($_SESSION['user_id'])) {
            self::UserLogout(true);
        }

        $params = $this->request->all();

        if (!isset($params['email']) || !isset($params['password'])) {
            if (!isset($params['token'])) {
                return ApiHelpers::show_message(false, $this->lang['email_address_or_password_cannot_be_empty']);
            } else {
                $user = User::getUserWithToken($params['token']);

                if (!$user) {
                    return ApiHelpers::show_message(false, $this->lang['invalid_token']);
                }

                $_SESSION['user_id'] = $user->u_id;
                $user = self::UserField($user);
            }
        } else {
            if (!filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
                return ApiHelpers::show_message(false, $this->lang['invalid_email']);
            }

            $user = User::getUserWithEmail($params['email']);

            if (!$user) {
                return ApiHelpers::show_message(false, $this->lang['the_email_address_or_password_is_incorrect']);
            }

            if (!password_verify($params['password'], $user->u_password)) {
                return ApiHelpers::show_message(false, $this->lang['the_email_address_or_password_is_incorrect']);
            }

            $_SESSION['user_id'] = $user->u_id;
            $_SESSION['user_token'] = $user->ut_token;
            $_SESSION['SYSTEM_LANG'] = $user->u_lang ?? 'TR';

            $user = self::UserField($user);
        }

        $get_location_info = ApiHelpers::get_location_info();

        $get_detail_location = Address::getDetailLocation(
            $get_location_info['user_info']['country_code'],
            $get_location_info['user_info']['city']
        );

        $get_location_info = (!empty($get_location_info) ? json_encode($get_location_info) : false);
        User::saveLoginLog($user['id'], $get_location_info, $get_detail_location[0] ?? []);

        return ApiHelpers::show_message(true, $this->lang['login_successful'], [
            "user_data" => $user
        ]);
    }

    public function GoogleLogin()
    {
        $params = $this->request->all();
        
        if (!isset($params['access_token'])) {
            return ApiHelpers::show_message(false, 'Access token gerekli');
        }

        try {
            // Google Client yapılandırması - SSL güvenli
            $client = GoogleClientHelper::createSecureClient();
            
            // Access token'ı set et
            $client->setAccessToken(['access_token' => $params['access_token']]);
            
            // Google OAuth2 servisi ile kullanıcı bilgilerini al
            $oauth2 = new Google_Service_Oauth2($client);
            $userInfo = $oauth2->userinfo->get();
            
            // Google'dan gelen kullanıcı bilgileri
            $googleEmail = $userInfo->email;
            $googleName = $userInfo->givenName ?? '';
            $googleSurname = $userInfo->familyName ?? '';
            
            if (empty($googleEmail)) {
                return ApiHelpers::show_message(false, 'Google hesabından email alınamadı');
            }
            
            // Mevcut kullanıcıyı kontrol et
            $existingUser = User::getUserWithEmail($googleEmail);
            
            if ($existingUser) {
                // Mevcut kullanıcı varsa giriş yap
                $_SESSION['user_id'] = $existingUser->u_id;
                $_SESSION['user_token'] = $existingUser->ut_token;
                $_SESSION['SYSTEM_LANG'] = $existingUser->u_lang ?? 'TR';
                
                $user = self::UserField($existingUser);
                
                return ApiHelpers::show_message(true, 'Giriş başarılı', [
                    "user_data" => $user
                ]);
            } else {
               return apihelpers::show_message(false, 'Google hesabı ile kayıtlı kullanıcı bulunamadı.');
            }
            
        } catch (Exception $e) {
            return ApiHelpers::show_message(false, 'Google giriş hatası: ' . $e->getMessage());
        }
    }

    public function UserLogout($output = null)
    {
        $lang = Lang::importLang("user_page_lang");
        session_destroy();
        return $output === null ? ApiHelpers::show_message(true, $lang['logout_successful']) : true;
    }

    public static function UserRegister($params)
    {
        $lang = Lang::importLang("user_page_lang");

        switch ($params['step']) {
            case '1':
                $step = self::register_step_1($params, $lang);
                if ($step['status'] == true) {
                    return ApiHelpers::show_message(true, 'Validation successful.', ['next_step' => '2']);
                } else {
                    return ApiHelpers::show_message(false, 'Validation failed.', ['step_error_message' => $step['message']]);
                }
            case '2':
                $step = self::register_step_2($params, $lang);

                if ($step['status'] == true) {
                    return self::register_finish($params, $lang);
                } else {
                    return ApiHelpers::show_message(false, 'Validation failed.', ['step_error_message' => $step['message']]);
                }
            default:
                return ApiHelpers::show_message(false, 'Transaction not found');
        }
    }

    public static function register_step_1($params, $lang)
    {
        $validator = new Validator(
            $params,
            [
                'user_first_name' => 'required|regex:/^[\pL\s]+$/u|max:200',
                'user_last_name' => 'required|alpha|max:200',
                'user_email' => 'required|email|max:250',
            ],
            [
                'user_first_name' => [
                    $lang['namespace_cannot_be_empty'],
                    $lang['name_can_only_contain_letters'],
                    $lang['name_can_be_up_to_100_characters']
                ],
                'user_last_name' => [
                    $lang['surname_cannot_be_empty'],
                    $lang['surname_can_only_contain_letters'],
                    $lang['surname_can_be_up_to_100_characters']
                ],
                'user_email' => [
                    $lang['email_address_cannot_be_empty'],
                    $lang['email_address_is_invalid'],
                    $lang['email_address_can_be_up_to_250_characters']
                ],
            ]
        );

        if ($validator->fails()) {
            $error_detail = $validator->errors();
            return ApiHelpers::show_message(false, current($error_detail));
        } else {

            if (!filter_var($params['user_email'], FILTER_VALIDATE_EMAIL)) {
                return ApiHelpers::show_message(false, $lang['invalid_email']);
            }

            $user_email_control = User::emailControl($params['user_email']);

            if (!$user_email_control) {

                return ApiHelpers::show_message(true, 'Validation successful.');
            } else {
                return ApiHelpers::show_message(false, $lang['email_already_used']);
            }
        }
    }

    public static function register_step_2($params, $lang)
    {
        $validator = new Validator(
            $params,
            [
                'user_first_name' => 'required|regex:/^[\pL\s]+$/u|max:200',
                'user_last_name' => 'required|alpha|max:200',
                'user_email' => 'required|email|max:250',
                'user_password' => 'required|max:100|min:8',
            ],
            [
                'user_first_name' => [
                    $lang['namespace_cannot_be_empty'],
                    $lang['name_can_only_contain_letters'],
                    $lang['name_can_be_up_to_100_characters']
                ],
                'user_last_name' => [
                    $lang['surname_cannot_be_empty'],
                    $lang['surname_can_only_contain_letters'],
                    $lang['surname_can_be_up_to_100_characters']
                ],
                'user_email' => [
                    $lang['email_address_cannot_be_empty'],
                    $lang['email_address_is_invalid'],
                    $lang['email_address_can_be_up_to_250_characters']
                ],
                'user_password' => [
                    $lang['password_cannot_be_empty'],
                    $lang['password_must_be_at_least_8_characters'],
                    $lang['password_can_be_up_to_100_characters']
                ],
            ]
        );


        if ($validator->fails()) {
            $error_detail = $validator->errors();
            return ApiHelpers::show_message(false, current($error_detail));
        } else {
            if (!filter_var($params['user_email'], FILTER_VALIDATE_EMAIL)) {
                return ApiHelpers::show_message(false, $lang['invalid_email']);
            }

            $user = User::emailControl($params['user_email']);

            if ($user == false) {
                return ApiHelpers::show_message(true, 'Validation successful.');
            } else {
                if ($user->u_email == $params['user_email']) {
                    return ApiHelpers::show_message(false, $lang['this_email_address_is_used']);
                }
            }
        }
    }

    public static function register_finish($params, $lang)
    {
        $user_ip = $_SERVER['REMOTE_ADDR'];
        $get_location_info = ApiHelpers::get_location_info();
        $get_location_info = json_encode($get_location_info);

        $user_token = self::create_token();

        $save_data = [
            'u_first_name' => $params['user_first_name'],
            'u_last_name' => $params['user_last_name'],
            'u_email' => $params['user_email'],
            'u_password' => password_hash($params['user_password'], PASSWORD_DEFAULT),
            'u_register_date' => date('Y-m-d H:i:s'),
            'u_user_info' => $get_location_info,
            'u_lang' => 'TR'
        ];

        $user_saved_data_id = User::userSave($save_data);

        if ($user_saved_data_id) {

            $save_token_data = [
                'ut_user_id' => $user_saved_data_id,
                'ut_token' => $user_token,
                'ut_ip' => $user_ip,
                'ut_register_date' => date('Y-m-d H:i:s'),
                'ut_last_ip' => $user_ip,
            ];

            User::userTokenCreate($save_token_data);
            $user = User::getUser($user_saved_data_id);
            $user = self::UserField($user);

            return ApiHelpers::show_message(true, $lang['registration_successful'], [
                "user_data" => $user
            ]);
        }
    }

    public static function loginControl()
    {
        if (empty($_SESSION['user_id'])) {
            return false;
        } else {
            return true;
        }
    }


    public static function create_token()
    {
        // Modern PHP için daha güvenli UUID v4 oluşturma
        if (function_exists('random_bytes')) {
            $data = random_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // Version 4
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // Variant bits
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        }
        
        // Fallback for older PHP versions
        if (function_exists('com_create_guid')) {
            return trim(com_create_guid(), '{}');
        } else {
            // Explicit int conversion to avoid deprecation warning
            mt_srand((int)(microtime(true) * 10000));
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
            return $uuid;
        }
    }
}

<?php

namespace App\Routes;       
use App\Controllers\UserController;
use App\Controllers\ApiController;
use App\Controllers\ModuleController;
use App\Controllers\AddressController;
use App\Controllers\WorkAreaController;

class Api
{
    public function handleRequest()
    {
        $requestUri = $_SERVER['REQUEST_URI'];
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestPostData = $_POST;

        if ($requestUri === '/get_my_info' && $requestMethod === 'POST') {
            $controller = new UserController();
            $res = $controller->get_my_info();
        } else if ($requestUri === '/user/logout' && $requestMethod === 'POST') {
            $controller = new UserController();
            $res = $controller->UserLogout();
        } else if ($requestUri === '/user/login' && $requestMethod === 'POST') {
            $controller = new UserController();
            $res = $controller->UserLogin($requestPostData);
        } else if ($requestUri === '/user/register' && $requestMethod === 'POST') {
            $controller = new UserController();
            $res = $controller->UserRegister($requestPostData);
        } else if ($requestUri === '/admin/google-auth-url' && $requestMethod === 'GET') {
            $controller = new UserController();
            $res = $controller->getGoogleAuthUrl();
        } else if ($requestUri === '/admin/google-callback' && $requestMethod === 'POST') {
            $controller = new UserController();
            $res = $controller->handleGoogleCallback();
        } else if ($requestUri === '/address/list/country' && $requestMethod === 'POST') {
            $controller = new AddressController();
            $res = $controller->getCountryList();
        } else if ($requestUri === '/address/list/city' && $requestMethod === 'POST') {
            $controller = new AddressController();
            $res = $controller->getCityList($requestPostData);
        } else if ($requestUri === '/address/list/distirct' && $requestMethod === 'POST') {
            $controller = new AddressController();
            $res = $controller->getDistrictList($requestPostData);
        } else if ($requestUri === '/api_speed_test' && $requestMethod === 'POST') {
            $controller = new ApiController();
            $res = $controller->test();
        } else if ($requestUri === '/admin/work-area/create' && $requestMethod === 'POST') {
            $controller = new WorkAreaController();
            $res = $controller->createWorkArea();
        } else if ($requestUri === '/admin/work-area/domain-control' && $requestMethod === 'POST') {
            $controller = new WorkAreaController();
            $res = $controller->domainControl();
        } else if ($requestUri === '/admin/work-area/remove' && $requestMethod === 'POST') {
            $controller = new WorkAreaController();
            $res = $controller->removeWorkArea();
        } else if ($requestUri === '/admin/work-area/edit' && $requestMethod === 'POST') {
            $controller = new WorkAreaController();
            $res = $controller->editWorkArea();
        } else if ($requestUri === '/admin/work-area/list' && $requestMethod === 'POST') {
            $controller = new WorkAreaController();
            $res = $controller->listWorkAreas();
        } else if ($requestUri === '/admin/work-area/get-info' && $requestMethod === 'POST') {
            $controller = new WorkAreaController();
            $res = $controller->getWorkAreaInfo();
        } else if ($requestUri === '/admin/module/search' && $requestMethod === 'POST') {
            $controller = new ModuleController();
            $res = $controller->moduleSearch();
        } else if ($requestUri === '/admin/work-area/detail' && $requestMethod === 'POST') {
            $controller = new WorkAreaController();
            $res = $controller->getWorkAreaDetail();
        } else if ($requestUri === '/admin/work-area/add-user' && $requestMethod === 'POST') {
            $controller = new WorkAreaController();
            $res = $controller->addUserToWorkArea();
        } else if ($requestUri === '/admin/work-area/edit-user' && $requestMethod === 'POST') {
            $controller = new WorkAreaController();
            $res = $controller->editWorkAreaUser();
        } else if ($requestUri === '/admin/work-area/remove-user' && $requestMethod === 'POST') {
            $controller = new WorkAreaController();
            $res = $controller->removeWorkAreaUser();
        } else if ($requestUri === '/admin/login' && $requestMethod === 'POST') {
            $controller = new UserController();
            $res = $controller->UserLogin([], true);
        } else if ($requestUri === '/admin/google-login' && $requestMethod === 'POST') {
            $controller = new UserController();
            $res = $controller->GoogleLogin();
        } else {
            http_response_code(404);
            $res = ['error' => 'Route not found'];
        }

        header('Content-Type: application/json');
        echo json_encode($res);
    }
}

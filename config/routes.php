<?php

declare(strict_types=1);

/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

use Hyperf\HttpServer\Router\Router;
use App\Middleware\JwtAuthMiddleWare;
use App\Middleware\PermissionMiddleware;

$authMiddleWare = [
    JwtAuthMiddleWare::class,
];

//debug
Router::addRoute(['GET', 'POST', 'HEAD'], '/wsdebug', function ()
{
    $wsdebug = new \Firstphp\Wsdebug\Wsdebug();
    $response = new \Hyperf\HttpServer\Response();
    return $response->raw($wsdebug->getHtml())->withHeader('content-type', 'text/html; charset=utf-8');
});

Router::addServer('ws', function ()
{
    Router::get('/', Firstphp\Wsdebug\Wsdebug::class);
});

//游客访问路由
Router::addGroup('', function ()
{
    Router::get('/captcha', 'App\Controller\Captcha\CaptchaController@show');
    Router::addGroup('/sms', function ()
    {
        Router::post('', 'App\Controller\Sms\SmsController@store');
    });
    //User
    Router::addGroup('/user', function ()
    {
        Router::get('/email', 'App\Controller\Email\EmailController@verifyEmail');
        Router::post('/email', 'App\Controller\Email\EmailController@sendVerifyEmail');
        Router::post('', 'App\Controller\User\UserController@store');
        Router::post('/token', 'App\Controller\Token\TokenController@store');
        Router::patch('/password', 'App\Controller\User\UserController@resetPassword');
    });

});
//用户访问路由
Router::addGroup('/user', function ()
{
    Router::patch('', 'App\Controller\User\UserController@update');
    Router::delete('', 'App\Controller\User\UserController@delete');
    Router::post('/avatar', 'App\Controller\File\FileController@uploadAvatar');
    Router::patch('/token', 'App\Controller\Token\TokenController@update');
    Router::delete('/token', 'App\Controller\Token\TokenController@delete');
    Router::get('/addresses', 'App\Controller\User\UserAddressesController@show');
    Router::post('/addresses', 'App\Controller\User\UserAddressesController@store');
    Router::patch('/addresses', 'App\Controller\User\UserAddressesController@update');
    Router::delete('/addresses', 'App\Controller\User\UserAddressesController@delete');
}, ['middleware' => $authMiddleWare]);

//
Router::addGroup('/center', function ()
{
    //Admin
    Router::addGroup('/admin', function ()
    {
        Router::get('', 'App\Controller\Center\AdminController@show');
        Router::post('', 'App\Controller\Center\AdminController@store');
        Router::patch('', 'App\Controller\Center\AdminController@update');
        Router::delete('', 'App\Controller\Center\AdminController@delte');
        Router::delete('/status', 'App\Controller\Center\AdminController@disable');
        Router::delete('/password', 'App\Controller\Center\AdminController@resetPassword');
        Router::delete('/role', 'App\Controller\Center\AdminController@AssigningRole');
    });

    //Permission
    Router::addGroup('/permission', function ()
    {
        Router::get('', 'App\Controller\Center\PermissionController@show');
        Router::post('', 'App\Controller\Center\PermissionController@store');
        Router::patch('', 'App\Controller\Center\PermissionController@update');
        Router::delete('', 'App\Controller\Center\PermissionController@delete');
    });

    //Role
    Router::addGroup('/role', function ()
    {
        Router::get('', 'App\Controller\Center\RoleController@show');
        Router::post('', 'App\Controller\Center\RoleController@store');
        Router::patch('', 'App\Controller\Center\RoleController@update');
        Router::delete('', 'App\Controller\Center\RoleController@delete');
        Router::patch('/permission', 'App\Controller\Center\RoleController@assigningPermission');
    });

}, ['middleware' => [
    JwtAuthMiddleWare::class,
    PermissionMiddleware::class,
]]);
<?php
/**
 * 用户验证器
 */

namespace app\common\validate;

class UserValidate extends Validate
{
    protected $rule = [
        'user_level_id|用户等级' => 'require',
        'username|用户名'       => 'require',
        'mobile|手机号'         => 'require',
        'nickname|昵称'        => 'require',
        'password|密码'        => 'require',
        'status|是否启用'        => 'require',
        'openid|openid'        => 'require',
        'avatar|头像'        => 'require',
        'gender|性别'        => 'require',
        'province|省份/地区'        => 'require',
        'city|城市'        => 'require',
        'access_token|access_token'    =>'require',
    ];

    protected $message = [
        'user_level_id.require' => '用户等级不能为空',
        'username.require'      => '用户名不能为空',
        'mobile.require'        => '手机号不能为空',
        'nickname.require'      => '昵称不能为空',
        'password.require'      => '密码不能为空',
        'status.require'        => '是否启用不能为空',
        'openid.require'        => 'openid不能为空',
        'avatar.require'        => '头像地址不能为空',
        'gender.require'        => '性别不能为空',
        'province.require'        => '省份/地区不能为空',
        'city.require'        => '城市不能为空',
        'access_token.require'        => 'access_token不能为空',

    ];

    protected $scene = [
        'add'       => ['user_level_id', 'username', 'mobile', 'nickname', 'password', 'status',],
        'edit'      => ['user_level_id', 'username', 'mobile', 'nickname', 'password', 'status',],
        'api_register'  => ['username', 'nickname', 'password'],
        'api_login' => ['username', 'password'],
        'wx_register' => ['openid', 'nickname','avatar','gender','province','city'],
        'wx_login' => ['code'],
        'wx_login_app' => ['openid', 'nickname','avatar','gender','access_token']
        //'wx_login_app' => ['openid', 'nickname','avatar','gender','province','city','access_token']
    ];


}

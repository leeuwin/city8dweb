<?php

namespace app\admin\controller;

use app\common\model\AdminUser;
use think\App;
use tool\Auth;

class Index extends Base
{
    public function index()
    {
        $authModel = new Auth();
        $menu = $authModel->getAuthMenu(session('admin_role_id'));

//        $menu = $this->convertMenuInfo($menu);

        $this->assign([
            'menu' => ($menu),
        ]);

        return $this->fetch();
    }

    protected function convertMenuInfo($menu)
    {
        $newMenus = [];
        $newMenus['homeInfo'] = [
            'title' => "首页",
            'href' => '#'
        ];
        $newMenus['logoInfo'] = [
            'title' => "管理后台",
            'image' => '#',
            'href' => '#'
        ];
        $newMenus['menuInfo'] = [];

        foreach ($menu as $key => $val) {
            $newMenu = [];
            $newMenu['title'] = $val['title'];
            $newMenu['icon'] = $val['node_icon'];
            $newMenu['href'] = $val['node_path'];
            $newMenu['target'] = '_self';
            if (count($val['children']) > 0) {
                $newChildMenus = [];
                foreach ($val['children'] as $k1 => $val1) {
                    $newChildMenu = [];
                    $newChildMenu['title'] = $val1['title'];
                    $newChildMenu['icon'] = $val1['node_icon'];
                    $newChildMenu['href'] = $val1['node_path'];
                    $newChildMenu['target'] = '_self';
                    array_push($newChildMenus, $newChildMenu);
                }
            }

            array_push($newMenus['menuInfo'], $newMenu);
        }

        return $newMenus;
    }

    public function home()
    {
        $this->assign([
            'tp_version' => App::VERSION,
        ]);

        return $this->fetch();
    }

    // 修改密码
    public function editPwd()
    {
        if (request()->isPost()) {

            $param = input('post.');

            if ($param['new_password'] != $param['rep_password']) {
                return json(['code' => -1, 'data' => '', 'msg' => '两次密码输入不一致']);
            }

            // 检测旧密码
            $admin = new AdminUser();
            $adminInfo = $admin->getAdminInfo(session('admin_user_id'));

            if (0 != $adminInfo['code'] || empty($adminInfo['data'])) {
                return json(['code' => -2, 'data' => '', 'msg' => '管理员不存在']);
            }

            if (!checkPassword($param['password'], $adminInfo['data']['admin_password'])) {
                return json(['code' => -3, 'data' => '', 'msg' => '旧密码错误']);
            }

            $admin->updateAdminInfoById(session('admin_user_id'), [
                'admin_password' => makePassword($param['new_password']),
            ]);

            return json(['code' => 0, 'data' => '', 'msg' => '修改密码成功']);
        }

        return $this->fetch('pwd');
    }
}

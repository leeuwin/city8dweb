<?php

namespace app\admin\controller;

use think\Controller;
use tool\Auth;

class Base extends Controller
{
    public function initialize()
    {
        if ((session('last_time') - time()) <= 0){
            session('admin_user_name', '');
//            $this->redirect('login/index');

            $this->error('登录过期，请重新登录！', 'login/index');
        }

        if(empty(session('admin_user_name'))){

            $this->redirect(url('login/index'));
        }

        $controller = lcfirst(request()->controller());
        $action = request()->action();
        $checkInput = $controller . '/' . $action;

        $authModel = Auth::instance();
        $skipMap = $authModel->getSkipAuthMap();

        if (!isset($skipMap[$checkInput])) {

            $flag = $authModel->authCheck($checkInput, session('admin_role_id'));

            if (!$flag) {
                if (request()->isAjax()) {
                    return json(reMsg(-403, '', '无操作权限'));
                } else {
                    $this->error('无操作权限');
                }
            }
        }

        $this->assign([
            'admin_name' => session('admin_user_name'),
            'admin_id' => session('admin_user_id')
        ]);
    }
}
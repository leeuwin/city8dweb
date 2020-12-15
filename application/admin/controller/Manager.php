<?php

namespace app\admin\controller;

use app\common\model\AdminUser;
use app\common\validate\AdminValidate;
use tool\OperatorLog;

class Manager extends Base
{
    // 管理员列表
    public function index()
    {
        if(request()->isAjax()) {

            $limit = input('param.limit');
            $adminName = input('param.admin_name');

            $where = [];
            if (!empty($adminName)) {
                $where[] = ['admin_name', 'like', $adminName . '%'];
            }

            $admin = new AdminUser();
            $list = $admin->getAdmins($limit, $where);

            if(0 == $list['code']) {

                return json(['code' => 0, 'msg' => 'ok', 'count' => $list['data']->total(), 'data' => $list['data']->all()]);
            }

            return json(['code' => 0, 'msg' => 'ok', 'count' => 0, 'data' => []]);
        }

        return $this->fetch();
    }

    // 添加管理员
    public function addAdmin()
    {
        if(request()->isPost()) {

            $param = input('post.');

            $validate = new AdminValidate();
            if(!$validate->check($param)) {
                return ['code' => -1, 'data' => '', 'msg' => $validate->getError()];
            }

            $param['admin_password'] = makePassword($param['admin_password']);
            $param['add_time'] = date('Y-m-d H:i:s', time());

            $admin = new AdminUser();
            $res = $admin->addAdmin($param);

            OperatorLog::write("添加管理员：" . $param['admin_name']);

            return json($res);
        }

        $this->assign([
            'roles' => (new \app\common\model\Role())->getAllRoles()['data']
        ]);

        return $this->fetch('add');
    }

    // 编辑管理员
    public function editAdmin()
    {
        if(request()->isPost()) {

            $param = input('post.');

            $validate = new AdminValidate();
            if(!$validate->scene('edit')->check($param)) {
                return ['code' => -1, 'data' => '', 'msg' => $validate->getError()];
            }

            if(isset($param['admin_password'])) {
                $param['admin_password'] = makePassword($param['admin_password']);
            }

            $admin = new AdminUser();
            $res = $admin->editAdmin($param);

            OperatorLog::write("编辑管理员：" . $param['admin_name']);

            return json($res);
        }

        $adminId = input('param.admin_id');
        $admin = new AdminUser();

        $this->assign([
            'admin' => $admin->getAdminById($adminId)['data'],
            'roles' => (new \app\common\model\Role())->getAllRoles()['data']
        ]);

        return $this->fetch('edit');
    }

    /**
     * 删除管理员
     * @return \think\response\Json
     */
    public function delAdmin()
    {
        if(request()->isAjax()) {

            $adminId = input('param.id');

            $admin = new AdminUser();
            $res = $admin->delAdmin($adminId);

            OperatorLog::write("删除管理员：" . $adminId);

            return json($res);
        }
    }
}
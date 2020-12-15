<?php

namespace app\admin\controller;

use app\common\model\Node as NodeModel;
use app\common\validate\NodeValidate;
use tool\OperatorLog;

class Node extends Base
{
    // 节点列表
    public function index()
    {
        $node = new NodeModel();
        $list = $node->getNodesList();

        $this->assign([
            'tree' => makeTree($list['data'])
        ]);

        return $this->fetch();
    }

    // 添加节点
    public function add()
    {
        $nodeModel = new NodeModel();
        if (request()->isAjax()) {

            $param = input('post.');

            $validate = new NodeValidate();
            if(!$validate->check($param)) {
                return ['code' => -1, 'data' => '', 'msg' => $validate->getError()];
            }

            $res = $nodeModel->addNode($param);

            OperatorLog::write("添加节点：" . $param['node_name']);

            return json($res);
        }

        $pid = input('param.pid');

        $node = $nodeModel->getNodeInfoById($pid);

        $this->assign([
            'pid' => input('param.pid'),
            'pname' => $node['data']['node_name']
        ]);

        return $this->fetch();
    }

    // 编辑节点
    public function edit()
    {
        if (request()->isAjax()) {

            $param = input('post.');

            $validate = new NodeValidate();
            if(!$validate->check($param)) {
                return ['code' => -1, 'data' => '', 'msg' => $validate->getError()];
            }

            $nodeModel = new NodeModel();
            $res = $nodeModel->editNode($param);

            OperatorLog::write("编辑节点：" . $param['node_name']);

            return json($res);
        }

        $id = input('param.id');
        $pid = input('param.pid');

        $nodeModel = new NodeModel();

        if (0 == $pid) {
            $pNode = '顶级节点';
        } else {
            $pNode = $nodeModel->getNodeInfoById($pid)['data']['node_name'];
        }

        $this->assign([
            'node_info' => $nodeModel->getNodeInfoById($id)['data'],
            'p_node' => $pNode
        ]);

        return $this->fetch();
    }

    // 删除节点
    public function delete()
    {
        if (request()->isAjax()) {

            $id = input('param.id');

            $nodeModel = new NodeModel();
            $res = $nodeModel->deleteNodeById($id);

            OperatorLog::write("删除节点：" . $id);

            return json($res);
        }
    }

    public function icon(){
        return $this->fetch();
    }
}
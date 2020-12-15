<?php

namespace app\admin\controller;


class SystemConfig extends Base
{

    public function index(){
        if ($this->request->isPost()){

            foreach ($this->request->post() as $key => $vo) {
                sysconf($key, $vo);
            }

            \tool\OperatorLog::write('系统参数配置成功');

            return reMsg(0,[],'系统参数配置成功');
        }

        return $this->fetch();
    }

    public function upload(){

        $file = $this->request->file('file');

        if (checkUploadSize($file->getSize())){
            return json(['code' => 1, 'msg' => '文件传输大于配置传输大小', 'data' => '']);
        }

        if (!checkUploadFileType($file->getInfo()['name'])){
            return json(['code' => 1, 'msg' => '该文件类型不能上传！', 'data' => '']);
        }

        if ($file) {
            $dir = ROOT_PATH . 'public' . DS . 'uploads';
            !is_dir($dir) && mkdir($dir, '755', true);
            $info = $file->move($dir);

            if ($info){
                return json(['code' => 0, 'msg' => '上传成功!', 'data' => DS . 'uploads' . DS . $info->getSaveName()]);
            } else {
                return json(['code' => 1, 'msg' => $file->getError(), 'data' => '']);
            }
        }

    }

}
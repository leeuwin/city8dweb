<?php
namespace app\update\controller;

use think\response\Json;

class Index
{
    public function index()
    {
        $result['update'] = true;
        $result['wgtUrl'] = 'http://dati.ppihb.com/static/__UNI__E14E33D.wgt';
        $result['pkgUrl'] = '';

        return $this->result('success', $result, 0);
	}

    function result($msg = 'fail', $data = '', $code = 500)
    {
        $header = [];
        //处理跨域请求问题
        if (config('api.cross_domain.allow')) {
            $header = ['Access-Control-Allow-Origin' => '*'];
            if (request()->isOptions()) {
                $header = config('api.cross_domain.header');
                return json('',200,$header);
            }
        }

        return json([
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ], $code, $header);
    }
}

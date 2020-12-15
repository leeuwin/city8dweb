<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use app\common\service\DataService;
use think\Db;
use tool\Auth;

/**
 * 生产密码
 * @param $password
 * @return string
 */
function makePassword($password)
{

    return md5($password . config('whisper.salt'));
}

/**
 * 检测密码
 * @param $dbPassword
 * @param $inPassword
 * @return bool
 */
function checkPassword($inPassword, $dbPassword)
{

    return (makePassword($inPassword) == $dbPassword);
}

/**
 * 获取mysql 版本
 * @return string
 */
function getMysqlVersion()
{

    $conn = mysqli_connect(
        config('database.hostname') . ":" . config('database.hostport'),
        config('database.username'),
        config('database.password'),
        config('database.database')
    );

    return mysqli_get_server_info($conn);
}

/**
 * 生成layui子孙树
 * @param $data
 * @return array
 */
function makeTree($data)
{

    $res = [];
    $tree = [];

    // 整理数组
    foreach ($data as $key => $vo) {
        $res[$vo['id']] = $vo;
        $res[$vo['id']]['children'] = [];
    }
    unset($data);

    // 查询子孙
    foreach ($res as $key => $vo) {
        if ($vo['pid'] != 0) {
            $res[$vo['pid']]['children'][] = &$res[$key];
        }
    }

    // 去除杂质
    foreach ($res as $key => $vo) {
        if ($vo['pid'] == 0) {
            $tree[] = $vo;
        }
    }
    unset($res);

    return $tree;
}

/**
 * 打印调试函数
 * @param $data
 */
function dump($data)
{

    echo "<pre>";
    print_r($data);
}

/**
 * 标准返回
 * @param $code
 * @param $data
 * @param $msg
 * @return \think\response\Json
 */
function reMsg($code, $data, $msg)
{

    return json(['code' => $code, 'data' => $data, 'msg' => $msg]);
}

/**
 * model返回标准函数
 * @param $code
 * @param $data
 * @param $msg
 * @return array
 */
function modelReMsg($code, $data, $msg)
{

    return ['code' => $code, 'data' => $data, 'msg' => $msg];
}

/**
 * 标准返回
 * @param $code
 * @param $data
 * @param $count
 * @param $msg
 * @return \think\response\Json
 */
function layReMsg($code, $data,$count, $msg)
{

    return json(['code' => $code,  'msg' => $msg,'count' =>$count,'data' => $data]);
}

/**
 * 根据ip定位
 * @param $ip
 * @return string
 * @throws Exception
 */
function getLocationByIp($ip)
{
    $ip2region = new \Ip2Region();
    $info = $ip2region->btreeSearch($ip);

    $info = explode('|', $info['region']);

    $address = '';
    foreach ($info as $vo) {
        if ('0' !== $vo) {
            $address .= $vo . '-';
        }
    }

    return rtrim($address, '-');
}

/**
 * 按钮检测
 * @param $input
 * @return bool
 */
function buttonAuth($input)
{
    $authModel = Auth::instance();
    return $authModel->authCheck($input, session('admin_role_id'));
}


/**
 * 设备或配置系统参数
 * @param string $name 参数名称
 * @param bool $value 默认是null为获取值，否则为更新
 * @return string|bool
 */
function sysconf($name, $value = null)
{
    static $config = [];
    if ($value !== null) {

        list($config, $data) = [[], ['name' => $name, 'value' => $value]];
        return DataService::save('SystemConfig', $data, 'name');
    }
    if (empty($config)) {
        $config = Db::name('SystemConfig')->column('name,value');
    }
    return isset($config[$name]) ? $config[$name] : '';
}

function checkUploadSize($size){
    return ($size / 1024) > sysconf('max_upload_file');
}

function checkUploadFileType($fileName){
    $type = preg_split('/\./', $fileName)[1];

    $types = preg_split('/\|/', sysconf('upload_file_type'));

    return in_array($type, $types);
}

/**
 * 判断是否为正常的手机号
 * @param  string  $mobile 手机号
 * @return boolean
 */
function is_mobile_number($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('/^(?:(?:\+|00)86)?1[3-9]\d{9}$/', $mobile) ? true : false;
}
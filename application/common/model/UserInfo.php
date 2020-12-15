<?php
/**
 * 用户模型
 */

namespace app\common\model;

use think\Db;
use think\Exception;
use think\Log;
use think\model\concern\SoftDelete;
use think\Model;

class UserInfo extends Model
{
    use SoftDelete;
    public $softDelete = true;
    protected $table = 'userinfo';
//    protected $autoWriteTimestamp = true;

    public static function init()
    {
        //添加自动加密密码
        self::event('before_insert', static function ($data) {
            $data->password1 = base64_encode(password_hash($data->password1, 1));
        });

        //修改密码自动加密
        self::event('before_update', function ($data) {
            $old = (new static())::get($data->id);
            if ($data->password1 !== $old->password1) {
                $data->password1 = base64_encode(password_hash($data->password1, 1));
            }
        });
    }


    public function getUserListByPage($page, $limit){
        try{
            $res = Db::table($this->table)->field('*')->page($page, $limit)->select();
        }catch (Exception $e){
            Log::error($e->getMessage());
            return modelReMsg(-1, [], $e->getMessage());
        }

        return $res;
    }

    public function getUserListById($id){
        try{
            $res = Db::table($this->table)->field('*')->where('id', $id)->find();
        }catch (Exception $e){
            Log::error($e->getMessage());
            return modelReMsg(-1, [], $e->getMessage());
        }

        return $res;
    }


    /**
     * 微信登录
     * @param $param
     * @return mixed
     * @throws \Exception
     */
    public static function wxlogin($param)
    {
        $openid = $param['openid'];
//        $user     = User::get(['username' => $username]);
        $userinfo = Db::table('userinfo')->where(['openid'=>$openid])->find();
        if (!$userinfo) {
            exception('用户不存在');
        }
        if ((int)$userinfo['status'] !== 1) {
            exception('用户被冻结');
        }
        $user = Db::table('user')->where(['id'=>$userinfo['id']])->find();
        if (!$user) {
            exception('用户不存在');
        }
        if ((int)$user['status'] !== 1) {
            exception('用户被冻结');
        }

        return $user;
    }

    //加密字符串，用在登录的时候加密处理
    protected function getSignStrAttr($value, $data)
    {
        $ua = request()->header('user-agent');
        return sha1($data['id'] . $data['username'] . $ua);
    }
}

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

class User extends Model
{
    use SoftDelete;
    public $softDelete = true;
//    protected $name = 'user';
    protected $table = 'user';
//    protected $autoWriteTimestamp = true;

    public static function init()
    {
        //添加自动加密密码
        self::event('before_insert', static function ($data) {
            $data->password = base64_encode(password_hash($data->password, 1));
        });

        //修改密码自动加密

        self::event('before_update', function ($data) {
            $old = (new static())::get($data->id);
            if ($data->password !== $old->password) {
                $data->password = base64_encode(password_hash($data->password, 1));
            }
        });
    }

    //关联用户等级
    public function userLevel(): \think\model\relation\BelongsTo
    {
        return $this->belongsTo(UserLevel::class);
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
     * 用户登录
     * @param $param
     * @return mixed
     * @throws \Exception
     */
    public static function login($param)
    {
        $username = $param['username'];
        $password = $param['password'];
//        $user     = User::get(['username' => $username]);
        $user = Db::table('user')->where(['username'=>$username])->find();
        if (!$user) {
            exception('用户不存在');
        }

        if (!password_verify($password, base64_decode($user['password']))) {
            exception('密码错误');
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

<?php


namespace app\api\controller;

use app\common\model\User;
use app\common\model\UserInfo;
use app\common\model\UserToken;
use app\common\validate\UserValidate;
use Exception;
use think\facade\Log;
use think\process\exception\Failed;
use think\Request;
use think\response\Json;
use app\common\service\HttpService;
use think\Db;
use think\facade\Config;
use tool\CommonTools;
use app\api\controller\TLSSigAPIv2 as TLSSigAPIv2;


class Auth extends Controller
{

    protected $authExcept = [
        'login',
        'wxlogin',
        'register',
        'smslogin',
        'index'
    ];

    public function index(Request $request){

        $sdkappid = "1400440274";
        $key = "85ae726024c6f04e4d8e487164318e1cc3d006506a2710dc009190fc9cfabfc9";
        $a = new TLSSigAPIv2($sdkappid,$key);
        $uid = "test";
        $sig = $a->genUserSig($uid);
        $user['code'] = 1;
        $user['data'] = "hello world";
        return success($user);
    }
    /**d
     * 登录并发放token
     * @param Request $request
     * @param User $model
     * @param UserValidate $validate
     * @return Json|void
     */
    public function login(Request $request, User $model,UserToken $userToken, UserValidate $validate)
    {
        $param = $request->param();
        //数据验证
        $validate_result = $validate->scene('api_login')->check($param);
        if (!$validate_result) {
            return error($validate->getError());
        }

        //登录逻辑
        try {
            $user  = $model::login($param);
            $token_time = time();
            $token = $this->getToken($user['id'], $token_time);
            $userToken->addUserToken($user['id'], $token, $token_time);
            //return success($param);
        } catch (Exception $e) {
            return error($e->getMessage());
        }

        $userinfo = Db::table('userinfo')->where(['id'=>$user['id']])->find();
        if (!$userinfo) {
            return error('用户信息不存在');
        }
        $result = array_merge($user,$userinfo);
        $result['token'] = $token;
        unset($result['password']);
        unset($result['password1']);
        unset($result['status']);
        unset($result['openid']);
        unset($result['sessionkey']);

        $domain = \think\facade\Request::domain();
        $result['avatar'] = $domain.$result['avatar'];
        //返回数据
        return success($result, '登录成功');
    }

    public function smslogin(Request $request,UserToken $userToken, User $user, UserInfo $userinfo)
    {
        $param = $request->param();
        $action = 'checkcode';// getcode or checkcode
        if(isset($param['action'])){
            $action = $param['action'];
        }
        if(!isset($param['mobile'])){
            return error('请设置手机号');
        }
        if($action == 'getcode'){
            $res = CommonTools::getSmsCode($param['mobile']);
            return $res;
            /*
            $res = $res->getData();

            if( 0 == $res['code']) {
                return success($res['msg']);
            }else
            {
                return error($res['msg']);
            }
            */
        }elseif ($action == 'checkcode'){
            if(!isset($param['code'])){
                return error('请设置验证码参数');
            }
            $res = CommonTools::verifySmsCode($param['mobile'],$param['code']);
            if($res === true){
                //验证成功，返回登录成功信息
                $user_db = Db::table('user')->where(['mobile'=>$param['mobile']])->find();
                if(!$user_db){
                    //return error('您输入的手机号有误');//改为直接自动注册
                    //自动注册
                    $user_param['username'] = 'phone'.rand(1000,10000);
                    $user_param['avatar'] = '/static/images/avatar.png';
                    $user_param['nickname'] = $user_param['username'];
                    $user_param['password'] = '123456';
                    $user_param['mobile'] = $param['mobile'];
                    $user_param['status'] = 1;                  //标记为2代表预注册
                    if(isset($param['nickname'])){
                        $user_param['nickname'] = $param['nickname'];
                    }
                    $res = $user::create($user_param);

                    $userinfo_param['id']=(int)($res->getKey());
                    if(empty($userinfo_param['id'])){
                        return error('注册失败');
                    }

                    $userinfo_param['invitecode'] = Config::get('jwt.invitecode_offset')+$userinfo_param['id'];
                    $userinfo_param['openid'] = '';
                    $userinfo_param['sessionkey'] = '';
                    $userinfo_param['password1'] = '123456';
                    $userinfo_param['status'] = 1;
                    if(isset($param['avatar'])){
                        $userinfo_param['avatar'] = $param['avatar'];
                    }
                    if(isset($param['gender'])){
                        $userinfo_param['gender'] = $param['gender'];
                    }
                    if(isset($param['city'])){
                        $userinfo_param['city'] = $param['city'];
                    }
                    if(isset($param['province'])){
                        $userinfo_param['province'] = $param['province'];
                    }
                    //检查是否有代理参数
                    if(isset($param['invitecode'])){
                        //找出代理
                        $proxy_user = Db::table('userinfo')->where('invitecode', $param['invitecode'])->find();
                        if($proxy_user)
                        {
                            $userinfo_param['proxyparent'] = $proxy_user['id'];//建立代理关系
                            $userinfo_param['connect_time'] = time();
                        }
                    }
                    $res = $userinfo::create($userinfo_param);
                    if(empty($res->getKey())){
                        $user->whereIn('id', $userinfo_param['id'])->delete();
                        return error('注册失败');
                    }

                    //登录逻辑
                    try {
                        $token_time = time();
                        $token = $this->getToken( $userinfo_param['id'], $token_time);
                        $userToken->addUserToken( $userinfo_param['id'], $token, $token_time);
                    } catch (Exception $e) {
                        return error($e->getMessage());
                    }
                    //返回
                    $userinfo_db = Db::table('userinfo')->where(['id'=>$userinfo_param['id']])->find();
                    $result = array_merge($user_param,$userinfo_db);
                    $result['token'] = $token;
                    unset($result['password']);
                    unset($result['password1']);
                    unset($result['status']);
                    unset($result['openid']);
                    unset($result['sessionkey']);
                    //返回数据
                    return success($result, '登录成功');
                }
                try{
                    $token_time = time();
                    $token = $this->getToken($user_db['id'], $token_time);
                    $userToken->addUserToken($user_db['id'], $token, $token_time);
                } catch (Exception $e) {
                    return error($e->getMessage());
                }
                $userinfo_db = Db::table('userinfo')->where(['id'=>$user_db['id']])->find();
                //返回数据
                $result = array_merge($user_db,$userinfo_db);
                $result['token'] = $token;
                unset($result['password']);
                unset($result['password1']);
                unset($result['status']);
                unset($result['openid']);
                unset($result['sessionkey']);
                $domain = \think\facade\Request::domain();
                $result['avatar'] = $domain.$result['avatar'];

                return success($result, '登录成功');
            }else{
                return error($res);
            }
        }else{
            return error('动作请求参数有误!');
        }
    }

    /**d
     * wx登录并发放token
     * @param Request $request
     * @param User $model
     * @param UserValidate $validate
     * @return Json|void
     */
    public function wxlogin(Request $request, User $user,UserInfo $userinfo, UserToken $userToken, UserValidate $validate)
    {
        $param = $request->param();
        if(!isset($param['code'])){
            return error('请设置wxcode参数');
        }
        //miniapp
        //$appid = sysconf('wechat_xcx_app_id');
        if(empty($appid)){
            $appid='wx72f8912d30adb441';
        }
        //$secret = sysconf('wechat_xcx_app_secret');
        if(empty($secret)){
            $secret='46cfd45178c548c4fd2887f05771360e';
        }
        $code=$param['code'];
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $appid ;
        $url .=  '&secret=' . $secret;
        $url .=  '&js_code=' . $code;
        $url .= '&grant_type=authorization_code';

        //$returnjson = HttpService::get($url);
        //{"session_key":"Kd2Ha5P\/6pqVV1lNNeV8hw==","openid":"oE1zk5I3qEb8JT3TH6KaZq4e-c3s"}       success
        //{"errcode":40029,"errmsg":"invalid code, hints: [ req_id: fIJBu.wgE-xRUbRA ]"}            fail
        $returnjson = '{"session_key":"Kd2Ha5P/6pqVV1lNNeV8hw==","openid":"owzZL5GODiegcmE8hsEBI68VSd3B"}';
        $returnjson = json_decode($returnjson, true);

        if (isset($returnjson['errcode'])) {
            return error('网络繁忙，请稍候重试~');
        }
        $openid = $returnjson['openid'];
        $session_key = $returnjson['session_key'];

        //检查用户是否已注册
        $info = Db::table('user')->where('openid',$openid)->find();
        Log::info(json_encode($info));
        // 没有注册 进行注册
        if (empty($info)) {
            Log::info("注册用户".$openid);
            //先默认插入用户基本信息，然后要求用户上传信息更新；
            //构造user表的信息
            $user_param['username'] = $openid;
            $user_param['password'] = '123456';
            $user_param['status'] = 1;                  //标记为2代表预注册
            if(isset($param['nickname'])){
                $user_param['nickname'] = $param['nickname'];
            }
            $user_param['openid'] = $openid;
            $user_param['session_key'] = $session_key;
            $user_param['register_time'] = time();
            $res = $user::create($user_param);

            $userinfo_param['uid']=(int)($res->getKey());
            if(empty($userinfo_param['uid'])){
                return error('注册失败');
            }
            //构造userinfo表信息
            $userinfo_param['invitecode'] = Config::get('jwt.invitecode_offset')+$userinfo_param['uid'];
            $userinfo_param['password1'] = '123456';
            $userinfo_param['status'] = 1;

            //检查是否有代理参数
            if(isset($param['invitecode'])){
                //找出代理
                $proxy_user = Db::table('userinfo')->where('invitecode', $param['invitecode'])->find();
                if($proxy_user)
                {
                    $userinfo_param['proxyparent'] = $proxy_user['id'];//建立代理关系
                    $userinfo_param['connect_time'] = time();
                }
            }
            $res = $userinfo::create($userinfo_param);
            if(empty($res->getKey())){
                $user->whereIn('id', $userinfo_param['uid'])->delete();
                return error('注册失败');
            }

            //登录逻辑
            try {
                $token_time = time();
                $token = $this->getToken( $userinfo_param['uid'], $token_time);
                $userToken->addUserToken( $userinfo_param['uid'], $token, $token_time);
            } catch (Exception $e) {
                return error($e->getMessage());
            }
            //返回
            $userinfo_db = Db::table('userinfo')->where(['uid'=>$userinfo_param['uid']])->find();
            $result = array_merge($user_param,$userinfo_db);
            $result['token'] = $token;
            unset($result['password']);
            unset($result['password1']);
            unset($result['status']);
            unset($result['openid']);
            unset($result['sessionkey']);
            //返回数据
            return success($result, '登录成功');

        }else{
            $user_param = $info;
            //return success($info);
            try {
                //更新token时间
                //$user  = $model::login($info);
                $token_time = time();
                $token = $this->getToken($user_param['id'], $token_time);
                $userToken->addUserToken($user_param['id'], $token, $token_time);
            } catch (Exception $e) {
                return error($e->getMessage());
            }

            //返回用户基本信息&token
            //返回用户基本信息&token
            $userinfo_param = Db::table('userinfo')->where('uid',$user_param['id'])->find();
            if(empty($userinfo_param))
            {
                $result = $user_param;
            }else{

                $result = array_merge($user_param,$userinfo_param);
            }
            //返回数据
            $result['token'] = $token;
            unset($result['password']);
            unset($result['password1']);
            unset($result['status']);
            unset($result['openid']);
            unset($result['sessionkey']);
            return success($result, '登录成功');
        }

    }


    //用户普通注册
    public function register(Request $request, User $user, UserInfo $userinfo, UserToken $userToken, UserValidate $validate)
    {
        $param           = $request->param();
        $validate_result = $validate->scene('api_register')->check($param);
        if (!$validate_result) {
            return error($validate->getError());
        }

        $user_param['username'] = $param['username'];
        $user_param['avatar'] = '/static/images/avatar.png';
        $user_param['nickname'] = $param['username'];
        $user_param['password'] = $param['password'];
        $user_param['status'] = 1;

        //检查用户名是否合法
        $res = Db::table('user')->where(['username'=>$user_param['username']])->find();
        if ($res) {
            return error('用户名已存在');
        }

        //自动注册添加到user表中
        $res = $user::create($user_param);

        $userinfo_param['id'] = $res->getKey();
        if(empty($userinfo_param['id'])){
            return error('注册失败');
        }
        $userinfo_param['invitecode'] = Config::get('jwt.invitecode_offset')+$userinfo_param['id'];
        $userinfo_param['avatar'] = '';//默认头像可设置
        $userinfo_param['gender'] = 0;
        $userinfo_param['password1'] = '123456';
        //检查是否有代理参数
        if(isset($param['invitecode'])){
            //找出代理
            $proxy_user = Db::table('userinfo')->where('invitecode', $param['invitecode'])->find();
            if($proxy_user)
            {
                $userinfo_param['proxyparent'] = $proxy_user['id'];//建立代理关系
                $userinfo_param['connect_time'] = time();
            }
        }
        $res = $userinfo::create($userinfo_param);
        if(empty($res->getKey())){
            $user->whereIn('id', $userinfo_param['id'])->delete();
            return error('注册失败');
        }

        //登录逻辑
        try {
            $token_time = time();
            $token = $this->getToken( $userinfo_param['id'], $token_time);
            $userToken->addUserToken( $userinfo_param['id'], $token, $token_time);
        } catch (Exception $e) {
            return error($e->getMessage());
        }
        $userinfo_db = Db::table('userinfo')->where(['id'=>$userinfo_param['id']])->find();
        //返回错误信息，提示用户上传信息进行注册；
        $result = array_merge($user_param,$userinfo_db);
        $result['token'] = $token;
        unset($result['password']);
        unset($result['password1']);
        unset($result['status']);
        unset($result['openid']);
        unset($result['sessionkey']);
        $domain = \think\facade\Request::domain();
        $result['avatar'] = $domain.$result['avatar'];
        return success($result,'注册成功');
    }

    /**
     * 退出登录，设置token过期
     * @param Request $request
     * @param User $model
     * @param UserToken $userToken
     * @return Json
     */
    public function logout(UserToken $userToken){

        $uid = input('uid', '');

        $res = $userToken->updateUserToken($uid, time());
        if ($res){
            //返回数据
            return success([], '退出登录成功');
        }

        return success([], '退出登录成功');
    }
}
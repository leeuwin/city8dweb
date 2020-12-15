<?php
/**
 * Created by PhpStorm.
 * User: think
 * Date: 2020/4/8
 * Time: 10:48
 */

namespace app\api\controller;


use think\Db;
use think\Request;
use app\common\record\Record;
use think\facade\Log;

class Message extends Controller
{
    //protected $message_type_list = ['unknown','announcement','strength','gold','rand_gold'];
    protected $message_type_list = ['announcement','strength','gold','rand_gold','strength_grow'];
    protected $message_info_list = [
        'unknown'=>'0|未知消息|',
        'announcement'=>'1|公告|',
        'strength'=>'2|领体力|',
        'gold'=>'3|领金币|',
        'rand_gold'=>'4|领随机金币|',
        'strength_grow'=>'5|体力恢复|',
    ];

    //客户端心跳，定时调用查看是否有新消息
    public function request(Request $request, Record $record)
    {
        //获取相关配置
        $message_update_list = [
            'announcement'=>600,
            'strength'=>300,
            'gold'=>300,
            'rand_gold'=>300,
            'strength_grow'=>180,
        ];
        // 值类型|值|action|百分比倍数
        $message_content_list = [
            'unknown'=>'0|未知消息|0|0',
            'announcement'=>'1|好消息好消息！邀请好友赢取大礼包！|0|0',
            'strength'=>'2|1|2|200',
            'gold'=>'2|15|2|200',
            'rand_gold'=>'2|10-40|2|200',
            'strength_grow'=>'2|1|0|0',
        ];

        $max_strength = 10;
        //检测体力值，未达到上限时，符合条件则增长；
        $userinfo = Db::table('userinfo')->where('id',$this->uid)->find();
        if(!$userinfo){
            return error('用户不存在！');
        }
        $msglist = array();
        //检查记录，决定是否返回新消息；
        foreach ($this->message_type_list as $message_type){
            if(!isset($this->message_info_list[$message_type]))
            {
                continue;
            }
            $message_info = explode('|',$this->message_info_list[$message_type]);

            //如果是和体力相关，那检查用户体力是否达到上限，若达到上限，则可不必处理
            if($message_type == 'strength' || $message_type == 'strength_grow'){
                if($userinfo['strength'] >= $max_strength)
                {
                    if($message_type == 'strength_grow') {
                        $msg['type'] = $message_info[0];
                        $msg['title'] = $message_info[1];

                        //获取消息的详情内容
                        $content = explode('|', $message_content_list[$message_type]);

                        $msg['msg']['msgtype'] = 2;

                        $userinfo = Db::table('userinfo')->where('id', $this->uid)->find();
                        $msg['msg']['content'] = $userinfo['strength'];

                        $msg['msg']['action'] = 0;                      //将action标记为0代表此次没有执行体力恢复
                        $msg['msg']['reward'] = 0;                      //将reward标记为0代表体力值已满暂停恢复增长；

                        $msglist[] = $msg;
                        unset($msg);
                    }
                    continue;
                }//else就根据记录执行
            }

            //获取消息的刷新间隔
            $interval = -1;
            if(isset($message_update_list[$message_type]))
            {
                $interval = $message_update_list[$message_type];
            }

            //查看消息刷新记录
            $award_record = Db::table('award_record')
                ->where(['uid'=>$this->uid,'type'=>$message_info[0]])
                ->order('updatetime desc')
                ->limit(1)
                ->find();
            if($award_record){
                //从配置中获取消息刷新间隔
                $duration = time() - $award_record['updatetime'];

                if($duration < $interval)//不需要更新，继续下一个
                {
                    if($message_type == 'strength_grow') {//如果是体力增长,返回倒计时读秒
                        $msg['type'] = $message_info[0];
                        $msg['title'] = $message_info[1];

                        //获取消息的详情内容
                        $content = explode('|', $message_content_list[$message_type]);

                        $msg['msg']['msgtype'] = 2;

                        $userinfo = Db::table('userinfo')->where('id',$this->uid)->find();
                        $msg['msg']['content'] = $userinfo['strength'];

                        $msg['msg']['action'] = 0;                      //将action标记为0代表此次没有执行体力恢复
                        $msg['msg']['reward'] = $interval-$duration;

                        $msglist[] = $msg;
                        unset($msg);
                    }
                    continue;
                }//否则继续
            }
            //------此消息应该刷新-------按具体消息，执行刷新逻辑-----//
            $msg['type'] = $message_info[0];
            $msg['title'] = $message_info[1];

            //获取消息的详情内容
            $content = explode('|', $message_content_list[$message_type]);

            $msg['msg']['msgtype'] = intval($content[0]);
            if ('rand_gold' == $message_type) {
                $range = explode('-', $content[1]);
                $msg['msg']['content'] = rand($range[0], $range[1]);
            } else {
                if($content[0] == '2'){
                    $msg['msg']['content'] = intval($content[1]);
                }else{
                    $msg['msg']['content'] = $content[1];
                }
            }
            $msg['msg']['action'] = intval($content[2]);
            $msg['msg']['reward'] = intval($content[3]);

            //如果是公告信息，那默认mark记录一下,隔一段时间再更新（因为不存在用户点击公告领取的操作）
            if ('announcement' == $message_type ) {
                //记录体力领取
                $recordinfo['uid'] = $this->uid;
                $recordinfo['amount'] = 0;
                $record->setrecord('award', 'announcement', $recordinfo);
            }elseif ('strength_grow' == $message_type ){
                Db::table('userinfo')->where('id',$this->uid)->setInc('strength',1);
                //获取当前用户最新信息
                $userinfo = Db::table('userinfo')->where('id',$this->uid)->find();
                $msg['msg']['content'] = $userinfo['strength'];
                $msg['msg']['action'] = 1;
                $msg['msg']['reward'] = $interval;
                //插入领取体力记录
                $recordinfo['uid'] = $this->uid;
                $recordinfo['amount'] = 1;
                $record->setrecord('award', 'grow', $recordinfo);
                $record->setrecord('strength', 'grow', $recordinfo);
            }

            $msglist[] = $msg;
            unset($msg);
        }

        $data['amount'] = count($msglist);
        $data['unreadmsg'] = $msglist;
        return success($data);
    }

    public function submit(Request $request, Record $record)
    {
        $msglist = array();
        $param = $request->param();
        if(!isset($param['type'])){
            return error('请设置消息类型！');
        }
        if(0 == $param['type']){
            //unknown
        }elseif (1 == $param['type']){
            //公告
        }elseif (2 == $param['type'] ){
            if(!isset($param['value1'])){
                return error('请设置体力值');
            }
            $userinfo_param['strength'] = $param['value1'];
            $userinfo_param['id'] = $this->uid;
            $result = Db::table('userinfo')->where('id',$this->uid)->setInc('strength',$param['value1']);
            $userinfo_db = Db::table('userinfo')->field('strength')->where('id',$this->uid)->find();

            //记录体力领取  奖励部分
            $recordinfo['uid'] = $this->uid;
            $recordinfo['amount'] = $param['value1'];
            $record->setrecord('award','main_strength',$recordinfo);
            //记录体力变化    体力变化跟踪
            $record->setrecord('strength','recieve',$recordinfo);

            return success($userinfo_db,'领取体力成功');

        }elseif (3 == $param['type']|| 4 == $param['type'] || 10 == $param['type']){
            if(!isset($param['value1'])){
                return error('请设置金币值');
            }
            $result = Db::table('userinfo')->where('id',$this->uid)->setInc('gold',$param['value1']);
            $userinfo_db = Db::table('userinfo')->field('gold')->where('id',$this->uid)->find();

            //记录金币领取
            $recordinfo['uid'] = $this->uid;
            $recordinfo['amount'] = $param['value1'];
            if(3 == $param['type']) {
                $record->setrecord('award', 'main_gold', $recordinfo);
                $record->setrecord('gold','recieve',$recordinfo);
            }elseif(4 == $param['type']){
                $record->setrecord('award', 'main_rand_gold', $recordinfo);
                $record->setrecord('gold','recieve',$recordinfo);
            }elseif(10 == $param['type']){
                $record->setrecord('gold','treasure',$recordinfo);
            }

            return success($userinfo_db,'领取金币成功');
        }elseif (5 == $param['type']){

        }elseif (6 == $param['type']){

        }

        return success();
    }

    public function sms(Request $request)
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
            return success('','验证码已发送,假装收到，请输入666888验证');
        }elseif ($action == 'checkcode') {
            if (!isset($param['code'])) {
                return error('请设置验证码参数');
            }
            if($param['code']=='666888'){
                return success('','验证成功');
            }else
            {
                return error('验证码错误');
            }
        }
    }
}
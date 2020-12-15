<?php

namespace app\api\controller;

use think\Db;
use think\Request;
use app\common\record\Record;
use app\common\validate\QuestionValidate;

class Question extends Controller
{
    protected $authExcept = [];
    public function index()
    {
        return 'hello world!';
	}
    public function peekmap()
    {
        $maps = Db::table('map')
            ->where('status','=',1)
            ->order('rank')
            ->select();
        $map_record = Db::table('map_record')
            ->where('uid','=',$this->uid)
            ->select();
        $map_id_record = array_column($map_record,'mapid');
        $map_dic = array();
        foreach ($map_record as $map){
            $map_dic[$map['mapid']] = $map;
        }

        $maplist = array();
        $max_resolved_rank = -1;
        foreach ($maps as $map){
            $tempmap = $map;
            //关闭无需展示的消息；
            unset($tempmap['nodelist']);
            unset($tempmap['awardlist']);
            unset($tempmap['status']);

            $tempmap['resolved'] = 0;
            $tempmap['nodeindex'] = 0;
            if(in_array($map['id'],$map_id_record)){
                $tempmap['resolved'] = 1;                                   //如果有记录，则标记此地图用户已经解锁；
                $tempmap['nodeindex'] = $map_dic[$map['id']]['nodeindex'];  //当前到达第几关；
                if($map['rank'] > $max_resolved_rank){
                    $max_resolved_rank = $map['rank'];
                }
            }
            $maplist[] = $tempmap;
        }
        if($max_resolved_rank<0){
            if(isset($maplist[0])){
                $maplist[0]['resolved'] = 1;
            }
        }else {
            for ($i=0; $i<count($maplist); $i++) {
                if ($maplist[$i]['rank'] < $max_resolved_rank){
                       $maplist[$i]['resolved'] = 1;
                }
            }
        }
        $result['maplist'] = $maplist;
        return success($result);
    }

    public function getmap()
    {
        $maprecord = Db::table('map_record')
            ->alias('a')
            ->join('map b','a.mapid=b.id')
            ->field('a.*,b.name,b.gold,b.nodecount,b.quscount,b.nodelist,b.awardlist,b.url,b.rank,b.status')
            ->where('a.resolved','=',0)
            ->where('a.uid','=',$this->uid)
            ->limit(1)
            ->find();

        $result= $maprecord;
        if(empty($maprecord)){
            $result['nodeindex'] = 0;
            $maprecord['qusindex']=0;

            $mapinfo = Db::table('map')
                ->where('status','=',1)
                ->order('rank')
                ->limit(1)
                ->find();
            $result['id'] = $mapinfo['id'];
            $result['name'] = $mapinfo['name'];
            $result['url'] = $mapinfo['url'];
            $result['gold'] = $mapinfo['gold'];
            $result['nodecount'] = $mapinfo['nodecount'];

            $result['qcount'] = $mapinfo['quscount'];
            $result['qleft'] = $mapinfo['quscount'];

        }else{
            $result['qleft'] = $maprecord['quscount'] - $maprecord['qusindex'];
        }
        $result['awardlist'] = json_decode($result['awardlist']);
        $result['nextmilestone'] = $result['awardlist'][$result['nodeindex']];
        $result['nodelist'] = json_decode($result['nodelist'],true);
        return success($result);
    }

	public function getquestion()
    {
        //判断用户体力值是否支持答题
        $userinfo = Db::table('userinfo')->where('id',$this->uid)->find();
        if($userinfo['strength']<1){
            return error('您的体力值不足');
        }
        //体力值减1
        Db::table('userinfo')->where('id',$this->uid)->setDec('strength');
        //从配置or用户信息获取生命值&提示次数
        $result['trytimes'] = 3;
        $result['instructtimes'] = 3;
        $quscount = 5;

        $result['milestone'] = array();
        $result['questionlist'] = array();

        $questionlist = Db::table('question')
            ->orderRaw('rand()')
            ->limit($quscount)
            ->select();

        /*
        $questionlist = Db::table('question')
            ->order('id')
            ->limit($quscount)
            ->select();
        */
        if($questionlist)
        {
            $milestonelist = array();
            for($i=0; $i<count($questionlist); $i++){
                $questionlist[$i]['gold'] = 5;          //此题的奖励金币--------------可根据难度或者从配置中读取
                if(4 == $questionlist[$i]['qustype']){//如果是成语接龙类型，那么用矩阵描述
                    //取出矩阵大小
                    $square = json_decode($questionlist[$i]['ext'], true);
                    $questionlist[$i]['width'] = $square['width'];
                    $questionlist[$i]['height'] = $square['height'];
                    $questionlist[$i]['quscontent'] = '成语拼龙';
                }
                unset($questionlist[$i]['ext']);

                $anslist = Db::table('answer')->where('qusid',$questionlist[$i]['id'])->select();
                if(4 == $questionlist[$i]['qustype']) {
                    for ($j = 0; $j < count($anslist); $j++) {
                        $anslist[$j]['ansext'] = json_decode($anslist[$j]['ansext'], true);
                    }
                }

                $questionlist[$i]['anslist'] = $anslist;

                $milestone['type'] = 0;
                $milestone['gold'] = 0;
                $milestone['action'] = 0;
                $milestone['reward'] = 200;
                $milestonelist[$i] = $milestone;
            }

            //根据配置增加奖励
            //宝箱奖励
            $milestonelist[2]['gold'] = 10;
            $milestonelist[2]['action'] = 2;
            $milestonelist[2]['reward'] = 200;
            $milestonelist[3]['type'] = 1;
            $milestonelist[3]['gold'] = rand(10,30);
            $milestonelist[3]['action'] = 2;
            $milestonelist[3]['reward'] = 200;
            $milestonelist[4]['gold'] = rand(20,40);
            $milestonelist[4]['action'] = 2;
            $milestonelist[4]['reward'] = 200;

            //完整答一轮奖励
            $milestonelist[$i-1]['type'] = 2;
            $milestonelist[$i-1]['gold'] = 20;
            $milestonelist[$i-1]['action'] = 2;
            $milestonelist[$i-1]['reward'] = 200;

            $result['milestone'] = $milestonelist;
            $result['questionlist'] = $questionlist;
        }
        return success($result);
	}

    //每次问题提交上报结果；
	public function submitanswer(Request $request, Record $record, QuestionValidate $validate){
        $param = $request->param(); //wingold,correctcount,qcount,mapid,instruct,try,adclick
        //validate
        $validate_result = $validate->scene('submit_ans')->check($param);
        if (!$validate_result) {
            return error($validate->getError());
        }
        // return success($ansrcd);
        $rcd['uid'] = $this->uid;
        $rcd['qusid'] = $param['qid'];
        $rcd['correct'] = $param['iscorrect'];
        $rcd['instruct'] = $param['isinstruct'];
        $rcd['try'] = $param['try'];
        $rcd['begtime'] = date('Y-m-d H:i:s',$param['begtime']);
        $rcd['endtime'] = date('Y-m-d H:i:s',$param['endtime']);
        Db::table('answer_record')->insert($rcd);

        //更新用户的账户信息；
        $res = Db::table('userinfo')->where('id',$this->uid)->setInc('gold',$param['wingold']);
        if(!$res){
            return error('更新金币失败');
        }else{
            //记录金币变化
            $recordinfo['amount'] = $param['wingold'];
            $recordinfo['uid'] = $this->uid;
            $record->setrecord('gold','question',$recordinfo);
        }

        //获取用户的账户余额，与剩余体力
        $result = Db::table('userinfo')->field('gold,strength')->where('id',$this->uid)->find();


        return success($result);
    }

    //结束一轮答题，更新进度
    public function finishanswer(Request $request, Record $record, QuestionValidate $validate)
    {
        $param = $request->param(); //mapid,ispass,wingold,qusindex, adclick
        //validate
        $validate_result = $validate->scene('finish_ans')->check($param);
        if (!$validate_result) {
            return error($validate->getError());
        }

        if(!isset($param['adclick'])) {
            $param['adclick'] = 0;
        }

        $userinfo = Db::table('userinfo')->where('id',$this->uid)->find();
        if(!$userinfo){
            return error('用户信息不存在');
        }

        $allawardgold = 0;
        $ispasscheckpoint = 0;
        if(1 == $param['ispass']){//完整答一轮了
            //0、查看绑定代理状体，如果未激活，激活绑定代理状态；
            if(0 == $userinfo['proxystatus']){
                $userinfo['proxystatus'] = 1;
                //给爸爸，爷爷上级代理执行分享成功奖励
                $this->sharereward($userinfo,$record);
            }
            //1、领取完整答题一轮奖金
            if($param['wingold']>0) {
                $userinfo['gold'] += $param['wingold'];
                $allawardgold += $param['wingold'];
                //记录金币变化
                $recordinfo['amount'] = $param['wingold'];
                $recordinfo['uid'] = $this->uid;
                $record->setrecord('gold', 'round', $recordinfo);
            }
        }
        /* 0、查看用户当然地图进度信息
         * 1、更新记录里程碑
         * 2、查询判断奖励
         * 3、代理返佣计算
         * 4、分红猫进度
         * 5、返回用户信息
        */

        //查找用户当前地图记录以及对应的地图信息；
        $curmap = Db::table('map_record')
            ->alias('a')
            ->join('map b','a.mapid=b.id')
            ->field('a.*,b.name,b.gold,b.nodecount,b.quscount,b.nodelist,b.awardlist,b.url,b.rank,b.status')
            ->where('a.resolved','=',0)
            ->where('a.uid','=',$this->uid)
            ->limit(1)
            ->find();

        if(empty($curmap)){//用户暂时无记录，那默认从第一张地图开始；
            //先找到第一张map信息
            $mapinfo = Db::table('map')
                ->where('status','=',1)
                ->order('rank')
                ->limit(1)
                ->find();
            //将起点设置为初始化0
            //插入一条地图记录
            $new_map_record['uid'] = $this->uid;
            $new_map_record['mapid'] = $mapinfo['id'];
            $new_map_record['nodeindex'] = 0;
            $new_map_record['resolved'] = 0;
            $new_map_record['qusindex'] = $param['qusindex'];

            Db::table('map_record')->insert($new_map_record);
        }else{
            //--更新地图进度
            /*
             * 查看当前节点和题目，将答题数累加
             */
            $map_record['qusindex'] = $curmap['qusindex']+$param['qusindex'];
            if($map_record['qusindex']>=$curmap['quscount']-1) {//说明过了一关
                $ispasscheckpoint = 1;
                //过关赏金
                $awardlist = json_decode($curmap['awardlist'],true);
                $awardgold = isset($awardlist[$curmap['nodeindex']])?$awardlist[$curmap['nodeindex']]:0;
                if($awardgold>0) {
                    $userinfo['gold'] += $awardgold;
                    //记录金币变化
                    $recordinfo['amount'] = $awardgold;
                    $recordinfo['uid'] = $this->uid;
                    $record->setrecord('gold', 'milestone', $recordinfo);
                }

                $map_record['qusindex'] = ($map_record['qusindex']+1)%$curmap['quscount'];
                $map_record['nodeindex'] = $curmap['nodeindex']+1;
                //判断地图是否通关
                if($map_record['nodeindex'] >= $curmap['nodecount']-1){//因为地图节点从0开始计

                    $ispasscheckpoint = 1;                          //标记过了一关
                    //同时，插入一条新地图的记录
                    //获取下一关map信息
                    $mapinfo = Db::table('map')
                        ->where('status','=',1)
                        ->where('rank','>',$curmap['rank'])
                        ->order('rank')
                        ->limit(1)
                        ->find();
                    if(!$mapinfo){//如果没有下一关地图，从第一关开始
                        $mapinfo = Db::table('map')
                            ->where('status','=',1)
                            ->order('rank')
                            ->limit(1)
                            ->find();
                    }
                    //插入一条地图记录
                    $new_map_record['uid'] = $this->uid;
                    $new_map_record['mapid'] = $mapinfo['id'];
                    $new_map_record['nodeindex'] = 0;
                    $new_map_record['resolved'] = 0;
                    $new_map_record['qusindex'] = ($map_record['qusindex']+1) % $curmap['quscount'];
                    Db::table('map_record')->insert($new_map_record);

                    $map_record['qusindex'] = $curmap['quscount']-1;
                    $map_record['resolved'] = 1;        //这里只做标记，在后面统一更新到数据库；
                }
            }
            //这里统一更新地图记录（如果来到了新地图，那依然更新老地图）
            $map_record['id'] = $curmap['id'];
            Db::table('map_record')->update($map_record);
        }

        //计算用户增加的分红进度
        $mydividendpoint = $this->gendividend($param['ispass'],$param['adclick'],$ispasscheckpoint);
        $userinfo['catprogress'] += $mydividendpoint;
        //更新用户信息
        Db::table('userinfo')->update($userinfo);

        //返佣gold/分红赶进度给上级(父亲/爷爷） allawardgold adclickcount anscount
        $this->tribute($userinfo,$allawardgold,$param['adclick'],$param['qusindex'],$record);

        //获取客户当前进度的map信息
        $curmap = Db::table('map_record')
            ->alias('a')
            ->join('map b','a.mapid=b.id')
            ->field('a.*,b.name,b.gold,b.nodecount,b.quscount,b.nodelist,b.awardlist,b.url,b.rank,b.status')
            ->where('a.resolved','=',0)
            ->where('a.uid','=',$this->uid)
            ->limit(1)
            ->find();

        //返回最新地图信息；
        $mapinfo = $curmap;
        unset($mapinfo['awardlist']);
        $mapinfo['qleft'] = $curmap['quscount']- 1 - $curmap['qusindex'];

        $awardlist = json_decode($curmap['awardlist']);
        $mapinfo['nextmilestone'] = $awardlist[$curmap['nodeindex']];
        $mapinfo['nodelist'] = json_decode($curmap['nodelist'],true);
        //返回用户最新信息；
        $result = Db::table('userinfo')->field('gold,strength')->where('id',$this->uid)->find();
        $result['map'] = $mapinfo;

        return success($result);
    }


    //生成分红进度 ispass adclickcount ispasscheckpoint
    //return 分红进度值
    private  function gendividend($ispass,$adclickcount,$ispasscheckpoint){
        $dividend = 0;
        $dividend_full_score = sysconf('dividend_full_socre');
        if(empty($dividend_full_score)){
            $dividend_full_score = 10000;
        }
        //1、完整答一轮
        $answer_round_untilfull = sysconf('answer_round_untilfull');
        if(empty($answer_round_untilfull))
        {
            $answer_round_untilfull = 5000;
        }
        if($ispass ){
            $add_dividend = $dividend_full_score*1/$answer_round_untilfull;
            $add_dividend = 1>$add_dividend?1:$add_dividend;
            $dividend += $add_dividend;
        }
        //2、广告点击次数
        $click_ad_untilfull = sysconf('click_ad_untilfull');
        if(empty($click_ad_untilfull))
        {
            $click_ad_untilfull = 500;
        }
        $add_dividend = $click_ad_untilfull*$adclickcount/$answer_round_untilfull;
        $add_dividend = 1>$add_dividend?1:$add_dividend;
        $dividend += $add_dividend;
        //3、是否过关
        $answer_checkpoint_untilfull = sysconf('answer_checkpoint_untilfull');
        if(empty($answer_checkpoint_untilfull))
        {
            $answer_checkpoint_untilfull = 1000;
        }
        $add_dividend = $answer_checkpoint_untilfull*$ispasscheckpoint/$answer_round_untilfull;
        $add_dividend = 1>$add_dividend?1:$add_dividend;
        $dividend += $add_dividend;

        return $dividend;
    }

    //分享成功奖励，分红猫 基数  allawardgold adclickcount anscount
    private  function sharereward($userinfo, Record $record)
    {
        //-------------gold
        $invite_son_gold_award = sysconf('invite_son_gold_award');
        if(empty($invite_son_gold_award)){
            $invite_son_gold_award = 100;
        }
        $invite_grandson_gold_award = sysconf('invite_grandson_gold_award');
        if(empty($invite_grandson_gold_award)){
            $invite_grandson_gold_award = 5;
        }
        //-------------dividend
        $invite_son_untilfull = sysconf('invite_son_untilfull');
        if(empty($invite_son_untilfull)){
            $invite_son_untilfull = 100;
        }
        $invite_grandson_untilfull = sysconf('invite_grandson_untilfull');
        if(empty($invite_grandson_untilfull)){
            $invite_grandson_untilfull = 5000;
        }
        $dividend_full_score = sysconf('dividend_full_socre');
        if(empty($dividend_full_score)){
            $dividend_full_score = 10000;
        }
        $son_tribute_dividend = 0;
        $grandson_tribute_dividend = 0;

        $add_dividend = $dividend_full_score/$invite_son_untilfull;
        $add_dividend = 1>$add_dividend?1:$add_dividend;
        $son_tribute_dividend += $add_dividend;

        $add_dividend = $dividend_full_score/$invite_grandson_untilfull;
        $add_dividend = 1>$add_dividend?1:$add_dividend;
        $grandson_tribute_dividend += $add_dividend;

        //---------------更新父亲，爷爷的gold和分红进度 信息， 添加记录；
        $parentinfo = Db::table('userinfo')
            ->where('id',$userinfo['proxyparent'])
            ->where('status',1)
            ->find();
        if(!$parentinfo){
            //父亲不存在；
            return 0;
        }
        $parentinfo['isproxy'] = 1;     //确认父亲已经成为代理了
        $parentinfo['gold'] += $invite_son_gold_award;
        $parentinfo['son_tribute_gold'] += $invite_son_gold_award;
        $parentinfo['catprogress'] += $son_tribute_dividend;
        Db::table('userinfo')->update($parentinfo);

        $recordinfo['uid'] = $parentinfo['id'];
        $recordinfo['amount'] = $invite_son_gold_award;
        $record->setrecord('gold','share',$recordinfo);

        if(0 == $parentinfo['proxystatus']){
            //爷爷不存在；
            return 1;
        }
        $grandpainfo = Db::table('userinfo')
            ->where('id',$parentinfo['proxyparent'])
            ->where('status',1)
            ->find();
        if(!$grandpainfo){
            //爷爷不存在；
            return 1;
        }
        $grandpainfo['gold'] += $invite_grandson_gold_award;
        $grandpainfo['grandson_tribute_gold'] += $invite_grandson_gold_award;
        $grandpainfo['catprogress'] += $grandson_tribute_dividend;
        Db::table('userinfo')->update($grandpainfo);
        $recordinfo['uid'] = $grandpainfo['id'];
        $recordinfo['amount'] = $invite_grandson_gold_award;
        $record->setrecord('gold','son_share',$recordinfo);

        return 2;
    }

    //根据配置，给上级返佣gold，分红猫 基数  allawardgold adclickcount anscount
    private  function tribute($userinfo,$allawardgold,$adclickcount,$anscount,Record $record){
        //验证代理关系是否正常
        if( 0 == $userinfo['proxystatus']){
            return 0;
        }
        $tax_full_score = 1000;
        //根据gold和dividend和配置的比例，执行对父亲/爷爷的返佣
        //--------------gold
        $son_pay_tax = sysconf('son_pay_tax');
        if(empty($son_pay_tax)){
            $son_pay_tax = 10;
        }
        $grandson_pay_tax = sysconf('grandson_pay_tax');
        if(empty($grandson_pay_tax)){
            $grandson_pay_tax = 1;
        }
        $son_pay_gold = $allawardgold * $son_pay_tax / $tax_full_score;
        $son_pay_gold = 1>$son_pay_gold?1:$son_pay_gold;
        $grandson_pay_gold = $allawardgold * $grandson_pay_tax / $tax_full_score;
        $grandson_pay_gold = 1>$grandson_pay_gold?1:$grandson_pay_gold;

        //--------------dividend
        $son_click_ad_untilfull = sysconf('son_click_ad_untilfull');
        if(empty($son_click_ad_untilfull)){
            $son_click_ad_untilfull = 10000;
        }
        $grandson_click_ad_untilfull = sysconf('grandson_click_ad_untilfull');
        if(empty($grandson_click_ad_untilfull)){
            $grandson_click_ad_untilfull = 50000;
        }
        $son_answer_untilfull = sysconf('son_answer_untilfull');
        if(empty($son_answer_untilfull)){
            $son_answer_untilfull = 20000;
        }
        $grandson_answer_untilfull = sysconf('grandson_answer_untilfull');
        if(empty($grandson_answer_untilfull)){
            $grandson_answer_untilfull = 100000;
        }

        $dividend_full_score = sysconf('dividend_full_socre');
        if(empty($dividend_full_score)){
            $dividend_full_score = 10000;
        }
        $son_tribute_dividend = 0;
        $grandson_tribute_dividend = 0;

        $add_dividend = $adclickcount*$dividend_full_score/$son_click_ad_untilfull;
        $add_dividend = 1>$add_dividend?1:$add_dividend;
        $son_tribute_dividend += $add_dividend;

        $add_dividend = $anscount*$dividend_full_score/$son_answer_untilfull;
        $add_dividend = 1>$add_dividend?1:$add_dividend;
        $son_tribute_dividend += $add_dividend;

        $add_dividend = $adclickcount*$dividend_full_score/$grandson_click_ad_untilfull;
        $add_dividend = 1>$add_dividend?1:$add_dividend;
        $grandson_tribute_dividend += $add_dividend;

        $add_dividend = $anscount*$dividend_full_score/$grandson_answer_untilfull;
        $add_dividend = 1>$add_dividend?1:$add_dividend;
        $grandson_tribute_dividend += $add_dividend;

        //---------------更新父亲，爷爷， 添加记录；
        $parentinfo = Db::table('userinfo')
            ->where('id',$userinfo['proxyparent'])
            ->where('status',1)
            ->find();
        if(!$parentinfo){
            //父亲不存在；
            return 0;
        }
        $parentinfo['gold'] += $son_pay_gold;
        $parentinfo['son_tribute_gold'] += $son_pay_gold;
        $parentinfo['catprogress'] += $son_tribute_dividend;
        Db::table('userinfo')->update($parentinfo);

        $recordinfo['uid'] = $parentinfo['id'];
        $recordinfo['amount'] = $son_pay_gold;
        $record->setrecord('gold','son_question',$recordinfo);

        if(0 == $parentinfo['proxystatus']){
            //爷爷不存在；
            return 1;
        }
        $grandpainfo = Db::table('userinfo')
            ->where('id',$parentinfo['proxyparent'])
            ->where('status',1)
            ->find();
        if(!$grandpainfo){
            //爷爷不存在；
            return 1;
        }
        $grandpainfo['gold'] += $grandson_pay_gold;
        $grandpainfo['grandson_tribute_gold'] += $grandson_pay_gold;
        $grandpainfo['catprogress'] += $grandson_tribute_dividend;
        Db::table('userinfo')->update($grandpainfo);
        $recordinfo['uid'] = $grandpainfo['id'];
        $recordinfo['amount'] = $grandson_pay_gold;
        $record->setrecord('gold','grandson_question',$recordinfo);

        return 2;
    }


	//提交答题结果-----------------计划作废
    public function feedback(Request $request, Record $record)
    {
        $param = $request->param(); //wingold,correctcount,qcount,mapid,instruct,try,adclick
        //validate

        $content = json_decode($request->getContent(),true);
        foreach($content['answerlist'] as $ansrcd){
           // return success($ansrcd);
            $rcd['uid'] = $this->uid;
            $rcd['qusid'] = $ansrcd['qid'];
            $rcd['correct'] = $ansrcd['correct'];
            $rcd['instruct'] = $ansrcd['instruct'];
            $rcd['try'] = $ansrcd['try'];
            //$rcd['begtime'] = $ansrcd['begintime'];
            //$rcd['endtime'] = $ansrcd['endtime'];
            Db::table('answer_record')->insert($rcd);
        }


        //更新地图游戏进度
        $map = Db::table('map_record')
            ->alias('a')
            ->join('map b','a.mapid=b.id')
            ->where('a.resolved','=',0)
            ->where('a.uid','=',$this->uid)
            ->limit(1)
            ->find();
        //$map = $maps[0];
        //return success($map);
        if(empty($map)){
            //先找到第一张map信息
            $mapinfo = Db::table('map')
                ->where('status','=',1)
                ->order('rank')
                ->limit(1)
                ->find();
            //将起点设置为初始化0
            $mapinfo['nodeindex'] = 0;

            //插入一条地图记录
            $map_record['uid'] = $this->uid;
            $map_record['mapid'] = $mapinfo['id'];
            $map_record['nodeindex'] = $mapinfo['nodeindex'];
            $map_record['resolved'] = 0;
            $map_record['qusindex'] = 0;

            Db::table('map_record')->insert($map_record);
            $map = Db::table('map_record')
                ->alias('a')
                ->join('map b','a.mapid=b.id')
                ->where('a.resolved','=',0)
                ->where('a.uid','=',$this->uid)
                ->limit(1)
                ->find();

        }else{
            $mapinfo['nodeindex'] = $map['nodeindex'];
            //增加游戏进度
            $map_record['id']=$map['id'];
            //$map_record['qusindex'] = $param['qusindex'];
            $map_record['nodeindex'] = ($map['nodeindex']+1)%$map['nodecount'];
            Db::table('map_record')->update($map_record);
        }

        $mapinfo['id'] = $map['id'];
        $mapinfo['name'] = $map['name'];
        $mapinfo['url'] = $map['url'];
        $mapinfo['gold'] = $map['gold'];
        $mapinfo['nodecount'] = $map['nodecount'];

        $mapinfo['qcount'] = $map['quscount'];
        $mapinfo['qleft'] = $map['quscount'] - $map['qusindex'];

        $awardlist = json_decode($map['awardlist']);
        $mapinfo['nextmilestone'] = $awardlist[$mapinfo['nodeindex']];
        $mapinfo['nodelist'] = $map['nodelist'];

        //更新用户的账户信息；
        $res = Db::table('userinfo')->where('id',$this->uid)->setInc('gold',$param['wingold']);
        if(!$res){
            return error('更新金币失败');
        }else{
            //记录金币变化
            $recordinfo['amount'] = $param['wingold'];
            $recordinfo['uid'] = $this->uid;
            $record->setrecord('gold','question',$recordinfo);
        }

        $res = Db::table('userinfo')->where('id',$this->uid)->setDec('strength',1);
        if(!$res){
            return error('更新体力值失败');
        }
        //获取用户的账户余额，与剩余体力

        $result = Db::table('userinfo')->field('gold,strength')->where('id',$this->uid)->find();

        $result['map'] = $mapinfo;

        return success($result);
    }

    //测试数据库
    public function dbtest()
    {
        $data = Db::table('question')->where('id',1)->select();
        if($data)
        {

            return json_encode($data[0]);

        }
        return '';
    }

    //管理员才能使用的接口
    public function addquestion(Request $request)
    {
        $param = $request->param();
        if(isset($param['type'])) {
            $ques_type = $param['type'];//单选题类型
        }
        $file = "question";
        if(isset($param['file'])) {
            $file = $param['file'];
        }
        $myfile = fopen("./".$file.".txt", "r");
        if($myfile) {
            $line = fgets($myfile);
            while($line) {
                list($id, $qu, $ans1, $ans2, $ans3, $ans4, $ans) = explode("\t", $line);
                //return $qu."---A".$ans1."---B".$ans2."---C".$ans3."---D".$ans4."--[".$ans."]";
                //return $line_list[1];

                //字符串去掉特殊字符
                $qu = trim($qu);
                $ans = trim($ans);
                for ($i = 1; $i <= 4; $i++) {
                    ${'ans' . $i} = trim(${'ans' . $i});
                }
                //将ABCD答案转换为数字1-4;
                if ($ans == 'A') {
                    $a = 1;
                } elseif ($ans == 'B') {
                    $a = 2;
                } elseif ($ans == 'C') {
                    $a = 3;
                } else {
                    $a = 4;
                }

                //生成题目插入数据库
                $qus_data = ['quscontent' => $qu, 'qustype' => $ques_type];
                $qusid = Db::name('question')->insertGetId($qus_data);

                //return $qu."---A".$ans1."---B".$ans2."---C".$ans3."---D".$ans4."--".$ans."   ".$a." queid:".$qusid;
                //生成答案插入数据库
                $ans_list_data = array();
                for ($i = 1; $i <= 4; $i++) {
                    $ans_data = ['qusid' => $qusid, 'anscontent' => ${'ans' . $i}, 'anstype'=>$ques_type,'displayorder' => $i, 'ansorder' => $a == $i ? 1 : 0];
                    $ans_list_data[] = $ans_data;
                }
                Db::table('answer')->insertAll($ans_list_data);

                $line = fgets($myfile);
            }
            fclose($myfile);
            return 'insert ok!';
        }
        return 'open failed!';
    }
}

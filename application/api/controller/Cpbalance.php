<?php
/**
 * @author yupoxiong<i@yufuping.com>
 * @title 首页
 */

namespace app\api\controller;

use app\api\MyConst;
use function PHPSTORM_META\elementType;
use think\facade\Config;
use think\facade\Log;
use think\Request;

class Cpbalance extends Controller
{

    protected $authExcept = [
        'test','get_result','save','load','add_ma'
    ];
    //function cmp($a, $b){return $a['ma']>$b['ma'];}

    //访问测试接口
    public function test(Request $request)
    {
        $param = $request->param();
        $param = $param['param'];
        $param_array = json_decode($param,true);

        $str=null;
        foreach ($param_array as $item) {
            $str .= $item['type'];
            if(isset($item['pos'])) {
                $this->add_ma($item['type'], $item['data'], $item['amount'], $item['pos']);
            }else{
                $this->add_ma($item['type'], $item['data'], $item['amount']);
            }
        }
        //执行计算
        $gain_rate = 0.05;     //期望收益

        $r = $this->get_result($gain_rate);
        $result['r'] = $r;
        $result['gMaTree'] = $this->gMaTree;
        $result['sMaBoard'] = $this->sMaBoard;
        return success($result);
    }

    //---------------------------------内部数据结构----------------------------
    private $sMaBoard=array();      //特指情况，7个位置的拆解合并状态
    private $sMaTree=array();       //存 特指 的信息树
    private $gMaTree=array();       //存 泛指 的信息树

    private $s_pay_peek=0;      //特指 类型赔付预估额
    private $s_pay=0;         //特指 类型赔付精确额
    private $g_pay=0;         //泛指 类型配付精确额
    private $pay=0;           //总配付额

    private $amount=0;        //累计股买额
    private $s_amount=0;
    private $g_amount=0;

    //----------------------------------对外接口------------------------

    //将对象数据保存到缓存
    public function save(){

    }
    //从缓存中恢复对象
    public function load(){

    }
    //加ma
    public function add_ma($type, $data,$amount,$pos=MyConst::TeMa){
        switch ($type){
            case MyConst::S_LM_DX:
            case MyConst::S_LM_DS:
            case MyConst::S_LM_HDX:
            case MyConst::S_LM_HDS:
            case MyConst::S_LM_WDX:
            case MyConst::S_LM_TDX:
            case MyConst::S_LM_QHX:
            case MyConst::S_LM_JYX:
                $this->sLiangMian($type,$data,$amount);
                break;
            case MyConst::G_LM_ZDX:
            case MyConst::G_LM_ZDS:
                $this->gLiangMian($type,$data,$amount);
                break;
            case MyConst::S_TM:
                $this->sTeMa($data,$amount);
                break;
            case MyConst::G_ZM:
                $this->gZhengMa($data,$amount);
                break;
            case MyConst::S_ZMT:
                $this->sZhengMaTe($pos,$data,$amount);
                break;
            case MyConst::S_ZM16_DX:
            case MyConst::S_ZM16_DS:
            case MyConst::S_ZM16_HDX:
            case MyConst::S_ZM16_HDS:
            case MyConst::S_ZM16_WDX:
            case MyConst::S_ZM16_SB:
                $this->sZhengMa1to6($type,$pos,$data,$amount);
                break;
            case MyConst::G_ZMGG:
                $this->gZhengMaGuoGuan($data,$amount);
                break;
            case MyConst::G_LM_4:
            case MyConst::G_LM_3:
            case MyConst::G_LM_3_2:
            case MyConst::G_LM_2:
            case MyConst::G_LM_2_T:
            case MyConst::G_LM_T:
                $this->gLianMa($type,$data,$amount);
                break;
            case MyConst::G_LX_2:
            case MyConst::G_LX_3:
            case MyConst::G_LX_4:
            case MyConst::G_LX_5:
                $this->gLianXiao($type,$data,$amount);
                break;
            case MyConst::G_LW_2:
            case MyConst::G_LW_3:
            case MyConst::G_LW_4:
            case MyConst::G_LW_5:
                $this->gLianTail($type,$data,$amount);
                break;
            case MyConst::G_NO_5:
            case MyConst::G_NO_6:
            case MyConst::G_NO_7:
            case MyConst::G_NO_8:
            case MyConst::G_NO_9:
            case MyConst::G_NO_10:
            case MyConst::G_NO_11:
            case MyConst::G_NO_12:
                $this->gNegate($type,$data,$amount);
                break;
            case MyConst::S_SX_TX:
                $this->sTeXiao($data,$amount);
                break;
            case MyConst::G_SX_ZEX:
            case MyConst::G_SX_YX:
            case MyConst::G_SX_ZOX:
                $this->gShengXiao($type,$data,$amount);
                break;
            case MyConst::S_HX:
                $this->sHeXiao($data,$amount);
                break;
            case MyConst::G_SB_7SB:
                $this->g7color($data,$amount);
                break;
            case MyConst::S_SB_3SB:
            case MyConst::S_SB_BB:
            case MyConst::S_SB_BBB:
                $this->sColor($type,$data,$amount);
                break;
            case MyConst::S_WS_TS:
            case MyConst::S_WS_WS:
                $this->sHeadTail($type,$data,$amount);
                break;
            case MyConst::G_WS_ZTWS:
                $this->gTail($data,$amount);
                break;
            case MyConst::S_5X:
                $this->sFiveElements($data,$amount);
                break;
            case MyConst::G_7M_DS:
            case MyConst::G_7M_DX:
                $this->g7Ma($type,$data,$amount);
                break;
            case MyConst::G_Z1_5:
            case MyConst::G_Z1_6:
            case MyConst::G_Z1_7:
            case MyConst::G_Z1_8:
            case MyConst::G_Z1_9:
            case MyConst::G_Z1_10:
                $this->gZhongOne($type,$data,$amount);
                break;
            default:
                return 1;
        }
        return 0;
    }
    //根据现状生成结果
    /*
     * divation: 误差比例
     * always_win: 是否每次必须赢，否则选择最靠近期望的结果
     * max_times：最大尝试次数
     * */
    public function get_result($gain_rate,$divation=0.1,$always_win=true,$max_times=10000){
        //计算上下限比率
        $max_gain_rate = $gain_rate*(1.0+$divation);
        $min_gain_rate = $gain_rate*(1.0-$divation);
        //计算上下限收益
        $max_gain = 1.0*$max_gain_rate*$this->amount;
        $min_gain = 1.0*$min_gain_rate*$this->amount;
        $aim_gain = 1.0*$gain_rate*$this->amount;

        //mark
        $result['aim'] = $aim_gain;
        $result['aim_rate'] = $gain_rate;
        $result['min_aim'] = $min_gain;
        $result['max_aim'] = $max_gain;

        $status = 0;
        $div_gain = 0;
        $near_gain = 0;
        $near_maboard = array();
        //开始计算
        $gain = 0;

        $maboard = array();
        while($max_times--){//限制最大尝试次数
            $maboard = $this->generate_ma();        //生成一组结果
            $pay = $this->calc($maboard);     //验证结果
            $gain = $this->amount - $pay;
            if($min_gain<$gain && $gain<$max_gain){//如果符合标准
                break;
            }else{                                 //如果超出标准了
                if(0 == count($near_maboard)){
                    //代表这是首次迭代，无论如何先接受
                    $div_gain = abs($gain - $aim_gain);
                    $near_gain = $gain;
                    $near_maboard = $maboard;
                }else {
                    //这已经不是第一次了，直接对比
                    if ($always_win) {//必须赢，保留一个能赢的最接近目标结果
                        //若本轮肯定是赢的情况
                        if ($gain >= 0) {
                            //判断历史记录是赢还是输
                            if ($near_gain < 0 ) {
                                //当前变成最佳选择
                                $div_gain = abs($gain - $aim_gain);
                                $near_gain = $gain;
                                $near_maboard = $maboard;
                            }else{
                                //找最小误差的才是最佳选择
                                if (abs($gain - $aim_gain) < $div_gain) {//如果发现误差更小的情况，更新记录
                                    $div_gain = abs($gain - $aim_gain);
                                    $near_gain = $gain;
                                    $near_maboard = $maboard;
                                }
                            }
                        } else { //若本轮肯定是输
                            //判断历史记录，历史最好成绩也是亏损，需要更新记录维护状态
                            if ($near_gain < 0 && abs($gain - $aim_gain) < $div_gain) {
                                $div_gain = abs($gain - $aim_gain);
                                $near_gain = $gain;
                                $near_maboard = $maboard;
                            }
                            //否则，已经有比此次好的结果了，略过无需处理
                        }
                    } else {
                        //无需考虑输赢，保留一个最接近的理想结果
                        if (abs($gain - $aim_gain) < $div_gain) {
                            $div_gain = abs($gain - $aim_gain);
                            $near_gain = $gain;
                            $near_maboard = $maboard;
                        }
                    }
                }
            }
        }
        if(0 < $max_times){
            $status = 1;
        }else{
            //结果不达标，通过对最后一位进行调整，期望来达到更好的结果
            //将现状描述为目前已知最好的结果
            $status = 2;
            $fix_near_gain = $near_gain;
            $fix_near_maboard = $near_maboard;
            $fix_div_gain = $div_gain;

            //开始优化找新的组合
            $fix_maboard = $near_maboard;
            for($i=1; $i<50; $i++){
                if(in_array($i,$near_maboard))
                {   //新号码不应该已经出现
                    continue;
                }
                //重构结果串
                $fix_maboard[MyConst::TeMa] = $i;
                //重新评估fix_maboard，看是否能出现更好的结果
                $fix_pay = $this->calc($fix_maboard);     //验证结果
                $fix_gain = $this->amount - $fix_pay;
                if($min_gain<$fix_gain && $fix_gain<$max_gain){
                    //如果达到标准，直接返回
                    $fix_near_gain = $fix_gain;
                    $fix_near_maboard = $fix_maboard;
                    $status = 3;
                    break;
                }else{
                    //如果达不到标准，找符合条件最接近的结果
                    if($always_win){//必须赢，保留一个能赢的最接近结果
                        if($fix_gain>0) {
                            //如果此次就是赢的
                            if($fix_near_gain<0){
                                //如果之前最好的结果都是输的，那么当前就是最好的选择
                                $fix_near_gain = $fix_gain;
                                $fix_near_maboard = $fix_maboard;
                                $status = 3;
                            }else{
                                //否则，找最接近的结果
                                if (abs($fix_gain - $aim_gain) < $fix_div_gain) {//如果发现误差更小的情况，更新记录
                                    $fix_div_gain = abs($fix_gain - $aim_gain);
                                    $fix_near_gain = $fix_gain;
                                    $fix_near_maboard = $fix_maboard;
                                    $status = 3;
                                }
                            }

                            /*if ($fix_div_gain != 0 && abs($fix_gain - $aim_gain) < $fix_div_gain) {
                                $fix_div_gain = abs($fix_gain - $aim_gain);
                                $fix_near_gain = $fix_gain;
                                $fix_near_maboard = $fix_maboard;
                            } else {
                                $fix_div_gain = abs($fix_gain - $aim_gain);
                                $fix_near_gain = $fix_gain;
                                $fix_near_maboard = $fix_maboard;
                            }*/
                        }else{
                            if ($fix_near_gain < 0 && abs($fix_gain - $aim_gain) < $fix_div_gain) {
                                $fix_div_gain = abs($fix_gain - $aim_gain);
                                $fix_near_gain = $fix_gain;
                                $fix_near_maboard = $fix_maboard;
                                $status = 3;
                            }
                            //否则已经有比此次好的结果了，略过无需处理
                        }
                    }else {//保留一个最接近的结果即可
                        if (abs($fix_gain - $aim_gain) < $fix_div_gain) {
                            $fix_div_gain = abs($fix_gain - $aim_gain);
                            $fix_near_gain = $fix_gain;
                            $fix_near_maboard = $fix_maboard;
                            $status = 3;
                        }
                    }
                }
            }
            //获取最优结果
            $maboard = $fix_near_maboard;
            $gain = $fix_near_gain;
        }

        $this->pay = $this->amount - $gain;
        //验算$maboard精确结果，目前$gain不够精确
        $this->s_pay_peek = $this->s_calc_board($maboard);
        $this->s_pay = $this->s_calc_tree($maboard);
        $s_pay_offset =  $this->s_pay - $this->s_pay_peek;
        //扣除估算值，改为精确值
        $this->pay = $this->pay  - $this->s_pay_peek + $this->s_pay;

        //校正gain
        $gain = $this->amount - $this->pay;
        //记录g_pay
        $this->g_pay = $this->pay - $this->s_pay;
        //登记返回输出结果
        $result['status'] = $status;
        $result['ma'] = $maboard;
        $result['gain'] = $gain;
        $result['gain_rate'] = $gain*1.0/$this->amount;
        $result['amount'] = $this->amount;
        $result['pay_offset'] = $s_pay_offset;
        $result['s_pay'] = $this->s_pay;
        $result['s_pay_peek'] = $this->s_pay_peek;
        $result['g_pay'] = $this->g_pay;
        $result['count_times_left'] = $max_times;//剩余多少计算次数
        return $result;
    }

    //---------------------------内部实现-----------------------------//


    //计算s的合并大概值
    private function s_calc_board($maboard){
        $pay = 0;
        $num =  count($maboard);
        if($num!=7){
            return 1;
        }
        for($i=0; $i<7; $i++){
            $ma = $maboard[$i];
            if(isset($this->sMaBoard[$i][$ma])){
                $pay += 1.0*$this->sMaBoard[$i][$ma]*MyConst::TypeMultiple[MyConst::S_TM];
            }
        }
        return $pay;
    }
    //计算s的精确值------根据具体的倍率计算精确值；
    private function s_calc_tree($board){
        $pay = 0;
        $maboard = $board;
        foreach ($this->sMaTree as $type=>$node) {
            if (MyConst::S_LM_DX == $type) {
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    if (0 < $tm && $tm < 24) {
                        if(isset($value[1])) {
                            $pay += $value[1] * MyConst::TypeMultiple[$type];
                        }
                    } elseif (24 < $tm && $tm < 49) {
                        if(isset($value[2])) {
                            $pay += $value[2] * MyConst::TypeMultiple[$type];
                        }
                    } else {//退还
                        $pay += ($value[1] + $value[2]) * 1.0;
                    }
                }
            } elseif (MyConst::S_LM_DS == $type) {
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    if ($tm == 49) {//退还
                        $amount = 0;
                        if(isset($value[1])){
                            $amount += $value[1];
                        }
                        if(isset($value[2])){
                            $amount += $value[2];
                        }
                        $pay += $amount * 1.0;
                    }elseif ($tm % 2 == 1) {
                        if(isset($value[1])) {
                            $pay += $value[1] * MyConst::TypeMultiple[$type];
                        }
                    }else{
                        if(isset($value[2])) {
                            $pay += $value[2] * MyConst::TypeMultiple[$type];
                        }
                    }
                }
            }elseif (MyConst::S_LM_HDX == $type) {
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    if ($tm == 49) {//退还
                        $amount = 0;
                        if(isset($value[1])){
                            $amount += $value[1];
                        }
                        if(isset($value[2])){
                            $amount += $value[2];
                        }
                        $pay += $amount * 1.0;
                    } elseif (in_array($tm, MyConst::GROUP_HDX_X)) {
                        if(isset($value[1])) {
                            $pay += $value[1] * MyConst::TypeMultiple[$type];
                        }
                    } else {
                        if(isset($value[2])) {
                            $pay += $value[2] * MyConst::TypeMultiple[$type];
                        }
                    }
                }
            }elseif (MyConst::S_LM_HDS == $type) {
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    if ($tm == 49) {//退还
                        $amount = 0;
                        if(isset($value[1])){
                            $amount += $value[1];
                        }
                        if(isset($value[2])){
                            $amount += $value[2];
                        }
                        $pay += $amount * 1.0;
                    } elseif (in_array($tm, MyConst::GROUP_HDS_D)) {
                        if(isset($value[1])) {
                            $pay += $value[1] * MyConst::TypeMultiple[$type];
                        }
                    } else {
                        if(isset($value[2])) {
                            $pay += $value[2] * MyConst::TypeMultiple[$type];
                        }
                    }
                }
            }elseif (MyConst::S_LM_WDX == $type) {
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    if ($tm == 49) {//退还
                        $amount = 0;
                        if(isset($value[1])){
                            $amount += $value[1];
                        }
                        if(isset($value[2])){
                            $amount += $value[2];
                        }
                        $pay += $amount * 1.0;
                    } elseif (in_array($tm, MyConst::GROUP_WDX_X)) {
                        if(isset($value[1])) {
                            $pay += $value[1] * MyConst::TypeMultiple[$type];
                        }
                    } else {
                        if(isset($value[2])) {
                            $pay += $value[2] * MyConst::TypeMultiple[$type];
                        }
                    }
                }
            }elseif (MyConst::S_LM_TDX == $type) {
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    $xiao = $this->ma_to_xiao($tm);
                    if (in_array($xiao, MyConst::GROUP_SXS_UPPER)) {//天
                        if(isset($value[1])) {
                            $pay += $value[1] * MyConst::TypeMultiple[$type][0];
                        }
                    } else {
                        if(isset($value[2])) {
                            $pay += $value[2] * MyConst::TypeMultiple[$type][1];
                        }
                    }
                }
            }elseif (MyConst::S_LM_JYX == $type) {
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    $xiao = $this->ma_to_xiao($tm);
                    if (in_array($xiao, MyConst::GROUP_SXS_HOME)) {//家
                        if(isset($value[1])) {
                            $pay += $value[1] * MyConst::TypeMultiple[$type][0];
                        }
                    } else {
                        if(isset($value[2])) {
                            $pay += $value[2] * MyConst::TypeMultiple[$type][1];
                        }
                    }
                }
            }elseif (MyConst::S_LM_QHX == $type) {
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    $xiao = $this->ma_to_xiao($tm);
                    if (in_array($xiao, MyConst::GROUP_SXS_FRONT)) {//前
                        if(isset($value[1])) {
                            $pay += $value[1] * MyConst::TypeMultiple[$type][0];
                        }
                    } else {
                        if(isset($value[2])) {
                            $pay += $value[2] * MyConst::TypeMultiple[$type][1];
                        }
                    }
                }
            }elseif (MyConst::S_TM == $type) {
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])){
                    $value = $node[MyConst::TeMa];
                    if (isset($value[$tm])){//中特
                        $pay += $value[$tm]*MyConst::Multiple;
                    }
                }
            }elseif (MyConst::S_ZMT == $type) {
                //遍历正ma
                for ($i=0; $i<6; $i++){
                    $zmt = $maboard[$i];
                    if(isset($node[$i])){
                        $value = $node[$i];
                        if(isset($value[$zmt])){
                            $pay += $value[$zmt]*MyConst::TypeMultiple[$type];
                        }
                    }
                }
            }elseif (MyConst::S_ZM16_DX == $type) {
                //遍历正ma
                for ($i=0; $i<6; $i++) {
                    $zm = $maboard[$i];
                    if (isset($node[$i])) {
                        $value = $node[$i];
                        if (49 == $zm) {//和
                            $amount = 0;
                            if (isset($value[1])) {
                                $amount += $value[1];
                            }
                            if (isset($value[2])) {
                                $amount += $value[2];
                            }
                            $pay += $amount*1.0;//退还
                        }elseif(0<$zm && $zm <25){
                            if (isset($value[1])) {
                                $pay += $value[1] * MyConst::TypeMultiple[$type];
                            }
                        }elseif (24<$zm && $zm <49){
                            if (isset($value[2])) {
                                $pay += $value[2] * MyConst::TypeMultiple[$type];
                            }
                        }
                    }
                }
            }elseif (MyConst::S_ZM16_DS == $type) {
                //遍历正ma
                for ($i=0; $i<6; $i++) {
                    $zm = $maboard[$i];
                    if (isset($node[$i])) {
                        $value = $node[$i];
                        if (49 == $zm) {//和
                            $amount = 0;
                            if (isset($value[1])) {
                                $amount += $value[1];
                            }
                            if (isset($value[2])) {
                                $amount += $value[2];
                            }
                            $pay += $amount*1.0;//退还
                        }elseif(1 == $zm%2){
                            if (isset($value[1])) {
                                $pay += $value[1] * MyConst::TypeMultiple[$type];
                            }
                        }else{
                            if (isset($value[2])) {
                                $pay += $value[2] * MyConst::TypeMultiple[$type];
                            }
                        }
                    }
                }
            }elseif (MyConst::S_ZM16_HDX == $type) {
                //遍历正ma
                for ($i=0; $i<6; $i++) {
                    $zm = $maboard[$i];
                    if (isset($node[$i])) {
                        $value = $node[$i];
                        if (49 == $zm) {//和
                            $amount = 0;
                            if (isset($value[1])) {
                                $amount += $value[1];
                            }
                            if (isset($value[2])) {
                                $amount += $value[2];
                            }
                            $pay += $amount*1.0;//退还
                        }elseif(in_array($zm,MyConst::GROUP_HDX_X)){
                            if (isset($value[1])) {
                                $pay += $value[1] * MyConst::TypeMultiple[$type];
                            }
                        }else{
                            if (isset($value[2])) {
                                $pay += $value[2] * MyConst::TypeMultiple[$type];
                            }
                        }
                    }
                }
            }elseif (MyConst::S_ZM16_HDS == $type) {
                //遍历正ma
                for ($i=0; $i<6; $i++) {
                    $zm = $maboard[$i];
                    if (isset($node[$i])) {
                        $value = $node[$i];
                        if (49 == $zm) {//和
                            $amount = 0;
                            if (isset($value[1])) {
                                $amount += $value[1];
                            }
                            if (isset($value[2])) {
                                $amount += $value[2];
                            }
                            $pay += $amount*1.0;//退还
                        }elseif(in_array($zm,MyConst::GROUP_HDS_D)){
                            if (isset($value[1])) {
                                $pay += $value[1] * MyConst::TypeMultiple[$type];
                            }
                        }else{
                            if (isset($value[2])) {
                                $pay += $value[2] * MyConst::TypeMultiple[$type];
                            }
                        }
                    }
                }
            }elseif (MyConst::S_ZM16_WDX == $type) {
                //遍历正ma
                for ($i=0; $i<6; $i++) {
                    $zm = $maboard[$i];
                    if (isset($node[$i])) {
                        $value = $node[$i];
                        if (49 == $zm) {//和
                            $amount = 0;
                            if (isset($value[1])) {
                                $amount += $value[1];
                            }
                            if (isset($value[2])) {
                                $amount += $value[2];
                            }
                            $pay += $amount*1.0;//退还
                        }elseif(in_array($zm,MyConst::GROUP_WDX_X)){
                            if (isset($value[1])) {
                                $pay += $value[1] * MyConst::TypeMultiple[$type];
                            }
                        }else{
                            if (isset($value[2])) {
                                $pay += $value[2] * MyConst::TypeMultiple[$type];
                            }
                        }
                    }
                }
            }elseif (MyConst::S_ZM16_SB == $type) {
                //遍历正ma
                for ($i=0; $i<6; $i++) {
                    $zm = $maboard[$i];
                    if (isset($node[$i])) {
                        $value = $node[$i];
                        if(in_array($zm,MyConst::GROUP_SB_RED)){//red
                            if (isset($value[1])) {
                                $pay += $value[1] * MyConst::TypeMultiple[$type][0];
                            }
                        }elseif(in_array($zm, MyConst::GROUP_SB_BLUE)){//blue
                            if (isset($value[2])) {
                                $pay += $value[2] * MyConst::TypeMultiple[$type][1];
                            }
                        }else{//green
                            if (isset($value[3])) {
                                $pay += $value[3] * MyConst::TypeMultiple[$type][2];
                            }
                        }
                    }
                }
            }elseif(MyConst::S_SX_TX == $type){
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    $xiao = $this->ma_to_xiao($tm);
                    if(isset($value[$xiao])){
                        $multi = MyConst::TypeMultiple[$type][0];
                        if($xiao == MyConst::THIS_YEAR){
                            $multi = MyConst::TypeMultiple[$type][1];
                        }
                        $pay += $value[$xiao] * $multi;
                    }
                }
            }elseif(MyConst::S_HX == $type){
                //变成TX处理了
            }elseif(MyConst::S_SB_3SB == $type){
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    if(in_array($tm, MyConst::GROUP_SB_RED)){
                        if(isset($value[1])){
                            $pay += $value[1]*MyConst::TypeMultiple[$type][0];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_SB_BLUE)){
                        if(isset($value[2])){
                            $pay += $value[2]*MyConst::TypeMultiple[$type][1];
                        }
                    }else{
                        if(isset($value[3])){
                            $pay += $value[3]*MyConst::TypeMultiple[$type][2];
                        }
                    }
                }
            }elseif(MyConst::S_SB_BB == $type){
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    if(in_array($tm, MyConst::GROUP_RED_DA)){
                        if(isset($value[1])){
                            $pay += $value[1]*MyConst::TypeMultiple[$type][0];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_RED_XIAO)){
                        if(isset($value[2])){
                            $pay += $value[2]*MyConst::TypeMultiple[$type][1];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_RED_DAN)){
                        if(isset($value[3])){
                            $pay += $value[3]*MyConst::TypeMultiple[$type][2];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_RED_SHUANG)){
                        if(isset($value[4])){
                            $pay += $value[4]*MyConst::TypeMultiple[$type][3];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_BLUE_DA)){
                        if(isset($value[5])){
                            $pay += $value[5]*MyConst::TypeMultiple[$type][4];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_BLUE_XIAO)){
                        if(isset($value[6])){
                            $pay += $value[6]*MyConst::TypeMultiple[$type][5];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_BLUE_DAN)){
                        if(isset($value[7])){
                            $pay += $value[7]*MyConst::TypeMultiple[$type][6];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_BLUE_SHUANG)){
                        if(isset($value[8])){
                            $pay += $value[8]*MyConst::TypeMultiple[$type][7];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_GREEN_DA)){
                        if(isset($value[9])){
                            $pay += $value[9]*MyConst::TypeMultiple[$type][8];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_GREEN_XIAO)){
                        if(isset($value[10])){
                            $pay += $value[10]*MyConst::TypeMultiple[$type][9];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_GREEN_DAN)){
                        if(isset($value[11])){
                            $pay += $value[11]*MyConst::TypeMultiple[$type][10];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_GREEN_SHUANG)){
                        if(isset($value[12])){
                            $pay += $value[12]*MyConst::TypeMultiple[$type][11];
                        }
                    }
                }
            }elseif(MyConst::S_SB_BBB == $type){
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    if(in_array($tm, MyConst::GROUP_RED_DA_DAN)){
                        if(isset($value[1])){
                            $pay += $value[1]*MyConst::TypeMultiple[$type][0];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_RED_DA_SHUANG)){
                        if(isset($value[2])){
                            $pay += $value[2]*MyConst::TypeMultiple[$type][1];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_RED_XIAO_DAN)){
                        if(isset($value[3])){
                            $pay += $value[3]*MyConst::TypeMultiple[$type][2];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_RED_XIAO_SHUANG)){
                        if(isset($value[4])){
                            $pay += $value[4]*MyConst::TypeMultiple[$type][3];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_BLUE_DA_DAN)){
                        if(isset($value[5])){
                            $pay += $value[5]*MyConst::TypeMultiple[$type][4];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_BLUE_DA_SHUANG)){
                        if(isset($value[6])){
                            $pay += $value[6]*MyConst::TypeMultiple[$type][5];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_BLUE_XIAO_DAN)){
                        if(isset($value[7])){
                            $pay += $value[7]*MyConst::TypeMultiple[$type][6];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_BLUE_XIAO_SHUANG)){
                        if(isset($value[8])){
                            $pay += $value[8]*MyConst::TypeMultiple[$type][7];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_GREEN_DA_DAN)){
                        if(isset($value[9])){
                            $pay += $value[9]*MyConst::TypeMultiple[$type][8];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_GREEN_DA_SHUANG)){
                        if(isset($value[10])){
                            $pay += $value[10]*MyConst::TypeMultiple[$type][9];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_GREEN_XIAO_DAN)){
                        if(isset($value[11])){
                            $pay += $value[11]*MyConst::TypeMultiple[$type][10];
                        }
                    }elseif(in_array($tm, MyConst::GROUP_GREEN_XIAO_SHUANG)){
                        if(isset($value[12])){
                            $pay += $value[12]*MyConst::TypeMultiple[$type][11];
                        }
                    }
                }
            }elseif(MyConst::S_WS_TS == $type){
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    $head = $tm/10;
                    if(isset($value[$head])){
                        if($head==0){
                            $pay += $value[$head]*MyConst::TypeMultiple[$type][0];
                        }else{
                            $pay += $value[$head]*MyConst::TypeMultiple[$type][1];
                        }
                    }
                }
            }elseif(MyConst::S_WS_WS == $type){
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    $tail = $tm%10;
                    if(isset($value[$tail])){
                        if($tail==0){
                            $pay += $value[$tail]*MyConst::TypeMultiple[$type][0];
                        }else{
                            $pay += $value[$tail]*MyConst::TypeMultiple[$type][1];
                        }
                    }
                }
            }elseif(MyConst::S_5X == $type){
                $tm = $maboard[MyConst::TeMa];
                if(isset($node[MyConst::TeMa])) {
                    $value = $node[MyConst::TeMa];
                    for($i=1; $i<=5; $i++){
                        if(in_array($tm, MyConst::FIVE_ELEMENT_GROUP[$i])){
                            if(isset($value[$i])){
                                $pay += $value[$i]*MyConst::TypeMultiple[$type][$i-1];
                                break;
                            }
                        }
                    }
                }
            }else{
                continue;
            }
        }
        $this->s_pay = $pay;
        return $pay;
    }
    //计算g的精确值
    private function g_calc($board){
        $num =  count($board);
        if($num!=7){
            return 1;
        }
        $pay = 0;
        foreach ($this->gMaTree as $type=>$node){
            $maboard = $board;  //通过临时变量，避免原本变量被修改
            if(MyConst::G_LM_ZDX == $type){
                $ma_sum = 0;
                foreach ($maboard as $ma){
                    $ma_sum += $ma;
                }
                if($ma_sum<175){//small
                    if(isset($node['1'])) {//小
                        $pay += $node['1']*MyConst::TypeMultiple[$type];
                    }
                }else{//big
                    if(isset($node['2'])) {//大
                        $pay += $node['2']*MyConst::TypeMultiple[$type];
                    }
                }
            }elseif(MyConst::G_LM_ZDS == $type){
                $ma_sum = 0;
                foreach ($maboard as $ma){
                    $ma_sum += $ma;
                }
                if(0 != $ma_sum%2){//单
                    if(isset($node['1'])) {
                        $pay += $node['1']*MyConst::TypeMultiple[$type];
                    }
                }else{//双
                    if(isset($node['2'])) {
                        $pay += $node['2']*MyConst::TypeMultiple[$type];
                    }
                }
            }elseif(MyConst::G_ZM == $type){
                for($i=0; $i<6; $i++){
                    $ma =  $maboard[$i];
                    if(isset($node[$ma])){
                        $pay += $node[$ma]*MyConst::TypeMultiple[$type];
                    }
                }
            }elseif(MyConst::G_ZMGG == $type){
                //正码过关，遍历
                foreach ($node as $item_node){// data={'amount'=>$amount,'data'=>[pos=>type]}
                    $amount = $item_node['amount'];
                    $ma_array = $item_node['data'];
                    $multiple = 1.0;
                    foreach ($ma_array as $pos=>$type){
                        //check if $maboard[$pos] is match with $type
                        $ma = $maboard[$pos];
                        if($this->zmgg_match($type,$ma)){
                            $multiple *= MyConst::TypeMultiple[$type][$type];
                        }else{
                            $multiple = 0;
                            break;
                        }
                    }
                    $pay += $amount * $multiple;
                }
            }elseif(MyConst::G_LM_4 == $type){
                //C(6,4)组合
                unset($maboard[6]);//先排除tm
                sort($maboard);
                for ($i=0; $i<3; $i++){
                    for($j=$i+1; $j<4; $j++){
                        for($k=$j+1; $k<5; $k++){
                            for($t=$k+1; $t<6; $t++){
                                $key = $maboard[$i].'|'.$maboard[$j].'|'.$maboard[$k].'|'.$maboard[$t];
                                //check key
                                if(isset($node[$key])){
                                    $pay += $node[$key]*MyConst::TypeMultiple[$type];
                                }
                            }
                        }
                    }
                }
            }elseif(MyConst::G_LM_3 == $type){
                //C(6,3)组合
                unset($maboard[6]);//先排除tm
                sort($maboard);
                for ($i=0; $i<4; $i++){
                    for($j=$i+1; $j<5; $j++){
                        for($k=$j+1; $k<6; $k++){
                            $key = $maboard[$i].'|'.$maboard[$j].'|'.$maboard[$k];
                            //check key
                            if(isset($node[$key])){
                                $pay += $node[$key]*MyConst::TypeMultiple[$type];
                            }
                        }
                    }
                }
            }elseif(MyConst::G_LM_3_2 == $type){
                //遍历比较,3-3大倍率，3-2小倍率
                unset($maboard[6]);//仅针对zhengma
                foreach($node as $key=>$amount){
                    $ma_array = explode('|',$key);
                    $intersect = array_intersect($ma_array,$maboard);
                    $inter_num = count($intersect);
                    if(3 == $inter_num){ //3中3
                        //奖励
                        $pay += $amount*MyConst::TypeMultiple[$type][0];
                    }elseif(2 == $inter_num){//3中2
                        $pay += $amount*MyConst::TypeMultiple[$type][1];
                    }
                }
            }elseif(MyConst::G_LM_2 == $type){
                //C(6，2）组合
                unset($maboard[6]);//先排除tm
                sort($maboard);
                for ($i=0; $i<5; $i++){
                    for($j=$i+1; $j<6; $j++){
                        $key = $maboard[$i].'|'.$maboard[$j];
                        //check key
                        if(isset($node[$key])){
                            $pay += $node[$key]*MyConst::TypeMultiple[$type];
                        }
                    }
                }
            }elseif(MyConst::G_LM_2_T == $type){
                //遍历比较
                //遍历比较,2-2大倍率，2-1+T小倍率
                $tema = $maboard[6];
                unset($maboard[6]);//仅针对zhengma
                foreach($node as $key=>$amount){
                    $ma_array = explode('|',$key);
                    $intersect = array_intersect($ma_array,$maboard);
                    $inter_num = count($intersect);
                    if(2 == $inter_num){ //2中2
                        //奖励
                        $pay += $amount*MyConst::TypeMultiple[$type][0];
                    }elseif(1 == $inter_num){//2中1,check tema
                        if(in_array($tema, $ma_array)) {    //2中t
                            $pay += $amount * MyConst::TypeMultiple[$type][1];
                        }
                    }
                }
            }elseif(MyConst::G_LM_T == $type){
                //遍历比较
                //遍历比较
                //遍历比较,2-2大倍率，2-1+T小倍率
                $tema = $maboard[6];
                unset($maboard[6]);//仅针对zhengma
                foreach($node as $key=>$amount){
                    $ma_array = explode('|',$key);
                    $intersect = array_intersect($ma_array,$maboard);
                    $inter_num = count($intersect);
                    if(1 == $inter_num){//2中1,check tema
                        if(in_array($tema, $ma_array)) {    //te串
                            $pay += $amount * MyConst::TypeMultiple[$type];
                        }
                    }
                }
            }elseif(MyConst::G_LX_2 == $type){
                $xiaos = $this->to_xiaos($maboard);//将7个数字转换为升序的生肖列表
                $num =  count($xiaos);
                //C(num,2)
                if($num<2){
                    //不可能有答案，跳过，下一个
                    continue;
                }
                $multiple = MyConst::TypeMultiple[$type][0];
                for($i=0; $i<$num-1; $i++){
                    if($xiaos[$i] == MyConst::THIS_YEAR){
                        //如果出现当年生肖，倍数应该取小
                        $multiple = MyConst::TypeMultiple[$type][1];
                    }
                    for($j=$i+1; $j<$num; $j++){
                        if($xiaos[$j] == MyConst::THIS_YEAR){
                            //如果出现当年生肖，倍数应该取小
                            $multiple = MyConst::TypeMultiple[$type][1];
                        }
                        $key = $xiaos[$i].'|'.$xiaos[$j];
                        if(isset($node[$key])){
                            $pay += $node[$key]*$multiple;
                        }
                    }
                }

            }elseif(MyConst::G_LX_3 == $type){
                $xiaos = $this->to_xiaos($maboard);//将7个数字转换为升序的生肖列表
                $num =  count($xiaos);
                //C(num,3)
                if($num<3){
                    //不可能有答案，跳过，下一个
                    continue;
                }
                $multiple = MyConst::TypeMultiple[$type][0];
                for($i=0; $i<$num-2; $i++){
                    if($xiaos[$i] == MyConst::THIS_YEAR){
                        //如果出现当年生肖，倍数应该取小
                        $multiple = MyConst::TypeMultiple[$type][1];
                    }
                    for($j=$i+1; $j<$num-1; $j++){
                        if($xiaos[$j] == MyConst::THIS_YEAR){
                            //如果出现当年生肖，倍数应该取小
                            $multiple = MyConst::TypeMultiple[$type][1];
                        }
                        for($k=$j+1; $k<$num; $k++) {
                            if($xiaos[$k] == MyConst::THIS_YEAR){
                                //如果出现当年生肖，倍数应该取小
                                $multiple = MyConst::TypeMultiple[$type][1];
                            }
                            $key = $xiaos[$i] . '|' . $xiaos[$j].'|'.$xiaos[$k];
                            if (isset($node[$key])) {
                                $pay += $node[$key] * $multiple;
                            }
                        }
                    }
                }
            }elseif(MyConst::G_LX_4 == $type){
                $xiaos = $this->to_xiaos($maboard);//将7个数字转换为升序的生肖列表
                $num =  count($xiaos);
                //C(num,4)
                if($num<4){
                    //不可能有答案，跳过，下一个
                    continue;
                }
                $multiple = MyConst::TypeMultiple[$type][0];
                for($i=0; $i<$num-3; $i++){
                    if($xiaos[$i] == MyConst::THIS_YEAR){
                        //如果出现当年生肖，倍数应该取小
                        $multiple = MyConst::TypeMultiple[$type][1];
                    }
                    for($j=$i+1; $j<$num-2; $j++){
                        if($xiaos[$j] == MyConst::THIS_YEAR){
                            //如果出现当年生肖，倍数应该取小
                            $multiple = MyConst::TypeMultiple[$type][1];
                        }
                        for($k=$j+1; $k<$num-1; $k++) {
                            if($xiaos[$k] == MyConst::THIS_YEAR){
                                //如果出现当年生肖，倍数应该取小
                                $multiple = MyConst::TypeMultiple[$type][1];
                            }
                            for($t=$k+1; $t<$num; $t++) {
                                if ($xiaos[$t] == MyConst::THIS_YEAR) {
                                    //如果出现当年生肖，倍数应该取小
                                    $multiple = MyConst::TypeMultiple[$type][1];
                                }
                                $key = $xiaos[$i] . '|' . $xiaos[$j] . '|' . $xiaos[$k].'|'.$xiaos[$t];
                                if (isset($node[$key])) {
                                    $pay += $node[$key] * $multiple;
                                }
                            }
                        }
                    }
                }
            }elseif(MyConst::G_LX_5 == $type){
                $xiaos = $this->to_xiaos($maboard);//将7个数字转换为升序的生肖列表
                $num =  count($xiaos);
                //C(num,5)
                if($num<5){
                    //不可能有答案，跳过，下一个
                    continue;
                }
                $multiple = MyConst::TypeMultiple[$type][0];
                for($i=0; $i<$num-4; $i++){
                    if($xiaos[$i] == MyConst::THIS_YEAR){
                        //如果出现当年生肖，倍数应该取小
                        $multiple = MyConst::TypeMultiple[$type][1];
                    }
                    for($j=$i+1; $j<$num-3; $j++){
                        if($xiaos[$j] == MyConst::THIS_YEAR){
                            //如果出现当年生肖，倍数应该取小
                            $multiple = MyConst::TypeMultiple[$type][1];
                        }
                        for($k=$j+1; $k<$num-2; $k++) {
                            if($xiaos[$k] == MyConst::THIS_YEAR){
                                //如果出现当年生肖，倍数应该取小
                                $multiple = MyConst::TypeMultiple[$type][1];
                            }
                            for($t=$k+1; $t<$num-1; $t++) {
                                if ($xiaos[$t] == MyConst::THIS_YEAR) {
                                    //如果出现当年生肖，倍数应该取小
                                    $multiple = MyConst::TypeMultiple[$type][1];
                                }
                                for($z=$t+1; $z<$num; $z++) {
                                    if ($xiaos[$z] == MyConst::THIS_YEAR) {
                                        //如果出现当年生肖，倍数应该取小
                                        $multiple = MyConst::TypeMultiple[$type][1];
                                    }
                                    $key = $xiaos[$i] . '|' . $xiaos[$j] . '|' . $xiaos[$k] . '|' . $xiaos[$t].'|'.$xiaos[$z];
                                    if (isset($node[$key])) {
                                        $pay += $node[$key] * $multiple;
                                    }
                                }
                            }
                        }
                    }
                }
            }elseif(MyConst::G_LW_2 == $type){
                $weis = $this->to_weis($maboard);
                $num =  count($weis);
                if($num < 2){
                    continue;
                }
                //C(num,2)
                for($i=0; $i<$num; $i++){
                    for($j=$i+1;$j<$num;$j++){
                        $key = $weis[$i].'|'.$weis[$j];
                        if (isset($node[$key])) {
                            $pay += $node[$key] * MyConst::TypeMultiple[$type];
                        }
                    }
                }
            }elseif(MyConst::G_LW_3 == $type){
                $weis = $this->to_weis($maboard);
                $num =  count($weis);
                if($num < 3){
                    continue;
                }
                //C(num,3)
                for($i=0; $i<$num; $i++){
                    for($j=$i+1;$j<$num;$j++){
                        for($k=$j+1;$k<$num;$k++) {
                            $key = $weis[$i] . '|' . $weis[$j].'|'.$weis[$k];
                            if (isset($node[$key])) {
                                $pay += $node[$key] * MyConst::TypeMultiple[$type];
                            }
                        }
                    }
                }
            }elseif(MyConst::G_LW_4 == $type){
                $weis = $this->to_weis($maboard);
                $num =  count($weis);
                if($num < 4){
                    continue;
                }
                //C(num,4)
                for($i=0; $i<$num; $i++){
                    for($j=$i+1;$j<$num;$j++){
                        for($k=$j+1;$k<$num;$k++) {
                            for($t=$k+1;$t<$num;$t++) {
                                $key = $weis[$i] . '|' . $weis[$j] . '|' . $weis[$k].'|'.$weis[$t];
                                if (isset($node[$key])) {
                                    $pay += $node[$key] * MyConst::TypeMultiple[$type];
                                }
                            }
                        }
                    }
                }
            }elseif(MyConst::G_LW_5 == $type){
                $weis = $this->to_weis($maboard);
                $num =  count($weis);
                if($num < 5){
                    continue;
                }
                //C(num,5)
                for($i=0; $i<$num; $i++){
                    for($j=$i+1;$j<$num;$j++){
                        for($k=$j+1;$k<$num;$k++) {
                            for($t=$k+1;$t<$num;$t++) {
                                for($z=$t+1;$z<$num;$z++) {
                                    $key = $weis[$i] . '|' . $weis[$j] . '|' . $weis[$k] . '|' . $weis[$t] . '|' . $weis[$z];
                                    if (isset($node[$key])) {
                                        $pay += $node[$key] * MyConst::TypeMultiple[$type];
                                    }
                                }
                            }
                        }
                    }
                }
            }elseif(MyConst::G_NO_5 <= $type && $type <= MyConst::G_NO_12){
                //5-12不中可合并计算，区别在于奖励倍数不一致
                foreach($node as $key=>$amount){
                    $no_array = explode('|',$key);
                    $intersect = array_intersect($no_array,$maboard);
                    if(0 == count($intersect)){ //说明没有交集，即不中
                        //奖励
                        $pay += $amount*MyConst::TypeMultiple[$type];
                    }
                }
            }elseif(MyConst::G_SX_ZEX == $type){
                unset($maboard[6]);//先排除tm
                foreach ($maboard as $ma){
                    $xiao = $this->ma_to_xiao($ma);
                    if( isset($node[$xiao])){
                        $pay += $node[$xiao]*MyConst::TypeMultiple[$type];
                    }
                }
            }elseif(MyConst::G_SX_YX == $type){
                $xiaos = $this->to_xiaos($maboard);
                foreach ($xiaos as $xiao){
                    if( isset($node[$xiao])){
                        $pay += $node[$xiao]*MyConst::TypeMultiple[$type];
                    }
                }
            }elseif(MyConst::G_SX_ZOX == $type){
                //check xiao num
                $xiaos = $this->to_xiaos($maboard);
                $xiao_num = count($xiaos);
                if($xiao_num<5){
                    $multiple =  MyConst::TypeMultiple[$type][0];
                }elseif(5 == $xiao_num){
                    $multiple =  MyConst::TypeMultiple[$type][1];
                }elseif(6 == $xiao_num){
                    $multiple =  MyConst::TypeMultiple[$type][2];
                }elseif(7 == $xiao_num){
                    $multiple =  MyConst::TypeMultiple[$type][3];
                }else{
                    $multiple = 0;
                }
                if(isset($node[$xiao_num])){
                    $pay += $node[$xiao_num] * $multiple;
                }

                //check xiao odd
                $xiao_num_odd = 0 != $xiao_num%2?true:false;//是否是单数
                if(!$xiao_num_odd){
                    $xiao_num_offset = 8;   //代表xiao数双数
                    $multiple = MyConst::TypeMultiple[$type][5];
                }else{
                    $xiao_num_offset = 9;   //代表xiao数单数
                    $multiple = MyConst::TypeMultiple[$type][4];
                }
                if(isset($node[$xiao_num_offset])){
                    $pay += $node[$xiao_num_offset] * $multiple;
                }

            }elseif(MyConst::G_SB_7SB == $type){
                //check color
                $ans_color = 0;
                $colors = $this->to_colors($maboard);
                foreach ($colors as $color=>$num){
                    if($num == 3){
                        //和 退还其它颜色
                        for($i=1; $i<=3; $i++) {
                            if (isset($node[1])) {
                                $pay += $node[1];
                            }
                        }
                    }elseif($num > 3){
                        //the color is $color
                        $ans_color = $color;
                    }else{
                        //error
                        break;
                    }
                    //1.计算奖金
                    if(isset($node[$ans_color])){
                        $pay += $node[$ans_color]*MyConst::TypeMultiple[$type][$ans_color];
                    }

                    break;//just run once
                }

            }elseif(MyConst::G_WS_ZTWS == $type){
                $weis = $this->to_weis($maboard);
                foreach ($weis as $wei){
                    if(isset($node[$wei])){
                        if( 0 == $wei)
                        {
                            $multiple = MyConst::TypeMultiple[$type][1];
                        }else{
                            $multiple = MyConst::TypeMultiple[$type][0];
                        }
                        $pay += $node[$wei]*$multiple;
                    }
                }

            }elseif(MyConst::G_7M_DS == $type){
                $odd_num = $this->count_odd($maboard);
                if(isset($node[$odd_num])){
                    $pay += $node[$odd_num] * MyConst::TypeMultiple[$type][$odd_num];
                }
            }elseif(MyConst::G_7M_DX == $type){
                $big_num = $this->count_big($maboard);
                if(isset($node[$big_num])){
                    $pay += $node[$big_num] * MyConst::TypeMultiple[$type][$big_num];
                }
            }elseif(MyConst::G_Z1_5 <= $type && $type <= MyConst::G_Z1_10){
                //遍历
                foreach($node as $key=>$amount){
                    $zhong1_array = explode('|',$key);
                    $intersect = array_intersect($zhong1_array,$maboard);
                    if(1 == count($intersect)){ //说明刚好中1
                        //奖励
                        $pay += $amount*MyConst::TypeMultiple[$type];
                    }
                }
            }
            /*elseif(MyConst::G_Z1_5 == $type){

            }elseif(MyConst::G_Z1_6 == $type){

            }elseif(MyConst::G_Z1_7 == $type){

            }elseif(MyConst::G_Z1_8 == $type){

            }elseif(MyConst::G_Z1_9 == $type){

            }elseif(MyConst::G_Z1_10 == $type){

            }*/
            else{
                continue;
            }
        }
        $this->g_pay = $pay;
        return $pay;
    }

    //根据$maboard里的七个数，快速估算赔付额
    private function calc($maboard){
        //计算sMaBoard
        $pay1 = $this->s_calc_board($maboard);

        //计算gMaTree
        $pay2 = $this->g_calc($maboard);

        return $pay1+$pay2;
    }

    //-----------------------辅助函数-----------------------
    private  function zmgg_match($type,$ma){
        if(0 == $type){//dan
            if(0 != $ma%2){
                return true;
            }
        }elseif (1 == $type){//shuang
            if(0 == $ma%2){
                return true;
            }
        }elseif (2 == $type){//big
            if(24 < $ma){
                return true;
            }
        }elseif (3 == $type){//small
            if($ma < 25){
                return true;
            }
        }elseif (4 == $type){//h dan
            if(in_array($ma,MyConst::GROUP_HDS_D)){
                return true;
            }
        }elseif (5 == $type){//h shuang
            if(in_array($ma,MyConst::GROUP_HDS_S)){
                return true;
            }
        }elseif (6 == $type){//h big
            if(in_array($ma,MyConst::GROUP_HDX_D)){
                return true;
            }
        }elseif (7 == $type){//h small
            if(in_array($ma,MyConst::GROUP_HDX_X)){
                return true;
            }
        }elseif (8 == $type){//w big
            if(in_array($ma,MyConst::GROUP_WDX_D)){
                return true;
            }
        }elseif (9 == $type){//w small
            if(in_array($ma,MyConst::GROUP_WDX_X)){
                return true;
            }
        }elseif (10 == $type){//r
            if(in_array($ma,MyConst::GROUP_SB_RED)){
                return true;
            }
        }elseif (11 == $type){//g
            if(in_array($ma,MyConst::GROUP_SB_GREEN)){
                return true;
            }
        }elseif (12 == $type){//b
            if(in_array($ma,MyConst::GROUP_SB_BLUE)){
                return true;
            }
        }
        return false;
    }

    private function count_odd($maboard){
        $count = 0;
        foreach ($maboard as $ma){
            if(0 != $ma%2){
                $count++;
            }
        }
        return $count;
    }
    private function count_big($maboard){
        $count = 0;
        foreach ($maboard as $ma){
            if(24 < $ma){
                $count++;
            }
        }
        return $count;
    }
        //将数字数字转换为肖数组
    private function to_xiaos($maboard){
        $xiaos = array();
        foreach ($maboard as $ma){
            $xiao = $this->ma_to_xiao($ma);
            if(isset($xiaos[$xiao])){
                $xiaos[$xiao] += 1;
            }else{
                $xiaos[$xiao] = 1;
            }
        }
        ksort($xiaos);
        return array_keys($xiaos);
    }
    //将数字转换为对应的xiao
    private function ma_to_xiao($ma)
    {
        $ma = $ma % 12;
        $xiao = (MyConst::THIS_YEAR + 12 - $ma) % 12;
        return MyConst::SX_SHU + $xiao;
    }
    //将数字转换为尾
    private function to_weis($maboard){
        $weis = array();
        foreach ($maboard as $ma){
            $wei = $ma%10;
            if(isset($weis[$wei])){
                $weis[$wei] += 1;
            }else{
                $weis[$wei] = 1;
            }
        }
        //usort($xiaos,function ($a,$b){return $a>$b;});
        ksort($weis);
        return array_keys($weis);
    }
    //将数字转换为color
    private function to_colors($maboard){
        $colors = array();
        foreach ($maboard as $ma){
            if(in_array($ma,MyConst::GROUP_SB_RED)){
                $color = MyConst::COLOR_RED;
            }elseif(in_array($ma,MyConst::GROUP_SB_BLUE)){
                $color = MyConst::COLOR_BLUE;
            }else{
                $color = MyConst::COLOR_GREEN;
            }
            if(isset($colors[$color])){
                $colors[$color] += 1;
            }else{
                $colors[$color] = 1;
            }
        }
        //usort($xiaos,function ($a,$b){return $a>$b;});
        ksort($colors);
        return $colors;
    }

    private function generate_ma(){
        $maResult = array();
        for($i=0; $i<7; $i++){
            $ma = rand(1,49);
            while(in_array($ma, $maResult)){
                $ma = rand(1,49);
            }
            $maResult[] = $ma;
        }
        return $maResult;
    }

    //--------------------------------------以下s前缀代表特指-----------------------------------//
    //两面
    private function sLiangMian($type,$value,$amount){
        $group = array();
        if(MyConst::S_LM_DX == $type){
            if(1 == $value){
                $group = MyConst::GROUP_DX_X;
            }elseif(2 == $value){
                $group = MyConst::GROUP_DX_D;
            }else{
                return 2;
            }
            //为49平局退还下一个虚拟注
            if(isset($this->sMaBoard[MyConst::TeMa][49])) {
                $this->sMaBoard[MyConst::TeMa][49] += $amount / MyConst::Multiple;
            }else{
                $this->sMaBoard[MyConst::TeMa][49] = $amount / MyConst::Multiple;
            }
        }elseif (MyConst::S_LM_DS == $type){
            if(1 == $value){
                $group = MyConst::GROUP_DS_D;
            }elseif(2 == $value){
                $group = MyConst::GROUP_DS_S;
            }else{
                return 2;
            }
            //为49平局退还下一个虚拟注
            if(isset($this->sMaBoard[MyConst::TeMa][49])) {
                $this->sMaBoard[MyConst::TeMa][49] += $amount / MyConst::Multiple;
            }else{
                $this->sMaBoard[MyConst::TeMa][49] = $amount / MyConst::Multiple;
            }
        }elseif (MyConst::S_LM_HDX == $type){
            if(1 == $value){
                $group = MyConst::GROUP_HDX_X;
            }elseif(2 == $value){
                $group = MyConst::GROUP_HDX_D;
            }else{
                return 2;
            }
            //为49平局退还下一个虚拟注
            if(isset($this->sMaBoard[MyConst::TeMa][49])) {
                $this->sMaBoard[MyConst::TeMa][49] += $amount / MyConst::Multiple;
            }else{
                $this->sMaBoard[MyConst::TeMa][49] = $amount / MyConst::Multiple;
            }
        }elseif (MyConst::S_LM_HDS == $type){
            if(1 == $value){
                $group = MyConst::GROUP_HDS_D;
            }elseif(2 == $value){
                $group = MyConst::GROUP_HDS_S;
            }else{
                return 2;
            }
            //为49平局退还下一个虚拟注
            if(isset($this->sMaBoard[MyConst::TeMa][49])) {
                $this->sMaBoard[MyConst::TeMa][49] += $amount / MyConst::Multiple;
            }else{
                $this->sMaBoard[MyConst::TeMa][49] = $amount / MyConst::Multiple;
            }
        }elseif (MyConst::S_LM_WDX == $type){
            if(1 == $value){
                $group = MyConst::GROUP_WDX_X;
            }elseif(2 == $value){
                $group = MyConst::GROUP_WDX_D;
            }else{
                return 2;
            }
            //为49平局退还下一个虚拟注
            if(isset($this->sMaBoard[MyConst::TeMa][49])) {
                $this->sMaBoard[MyConst::TeMa][49] += $amount / MyConst::Multiple;
            }else{
                $this->sMaBoard[MyConst::TeMa][49] = $amount / MyConst::Multiple;
            }
        }elseif(MyConst::S_LM_TDX <= $type && $type <= MyConst::S_LM_JYX) {
            if (MyConst::S_LM_TDX == $type) {
                if (1 == $value) {
                    $sx_group = MyConst::GROUP_SXS_UPPER;
                } elseif (2 == $value) {
                    $sx_group = MyConst::GROUP_SXS_DOWN;
                } else {
                    return 2;
                }
            } elseif (MyConst::S_LM_QHX == $type) {
                if (1 == $value) {
                    $sx_group = MyConst::GROUP_SXS_FRONT;
                } elseif (2 == $value) {
                    $sx_group = MyConst::GROUP_SXS_BACK;
                } else {
                    return 2;
                }
            } elseif (MyConst::S_LM_JYX == $type) {
                if (1 == $value) {
                    $sx_group = MyConst::GROUP_SXS_HOME;
                } elseif (2 == $value) {
                    $sx_group = MyConst::GROUP_SXS_WILD;
                } else {
                    return 2;
                }
            }else{
                return 2;
            }
            //将生肖group转换为ma
            $group = array();
            foreach ($sx_group as $item){
                //根据今年是什么肖决定的
                $year_offset = MyConst::THIS_YEAR-MyConst::SX_SHU;
                $pos = $item - $year_offset;
                if($pos < 1){
                    $pos += 12;
                }
                $group = array_merge($group,MyConst::SX_GROUP[$pos]);
            }
        }else{
            return 1;
        }

        //得到group数
        $num = count($group);
        if($num < 1){
            return 3;
        }
        //合并到sMaBoard中快速计算
        $item_amount = $amount*1.0/$num;
        foreach ($group as $item){
            if(isset($this->sMaBoard[MyConst::TeMa][$item])) {
                $this->sMaBoard[MyConst::TeMa][$item] += $item_amount;
            }else{
                $this->sMaBoard[MyConst::TeMa][$item] = $item_amount;
            }
        }
        //记录到sMaTree中精确验算
        $this->add_s_value($type,$value,$amount);
        return 0;
    }
    //tm
    private function sTeMa($ma, $amount){
        //check param
        if($ma < 1 || $ma > 49){
            return 1;
        }
        //合并到sMaBoard中快速计算
        if(isset($this->sMaBoard[MyConst::TeMa][$ma])) {
            $this->sMaBoard[MyConst::TeMa][$ma] += $amount;
        }else{
            $this->sMaBoard[MyConst::TeMa][$ma] = $amount;
        }
        $type = MyConst::S_TM;
        //记录到tree中精确验算
        $this->add_s_value($type,$ma,$amount);
        return 0;
    }
    private function sZhengMaTe($pos, $ma, $amount){

        //check param
        if($ma < 1 || $ma > 49 || $pos < MyConst::PingMa1 || $pos >MyConst::PingMa6 ){
            return 1;
        }

        //合并到sMaBoard中快速计算
        if(isset($this->sMaBoard[$pos][$ma])) {
            $this->sMaBoard[$pos][$ma] += $amount;
        }else{
            $this->sMaBoard[$pos][$ma] = $amount;
        }

        //记录到tree中精确验算
        $type = MyConst::S_ZMT;
        $this->add_s_value($type,$ma,$amount,$pos);
        return 0;
    }
    //type类型 pos位置1-6, value选定的值， amount额度
    private function sZhengMa1to6($type, $pos, $value, $amount){
        //check param
        $group = array();
        if(MyConst::S_ZM16_DX == $type){
            if(1 == $value){//small
                $group = MyConst::GROUP_DX_X;
            }else if(2 == $value){//big
                $group = MyConst::GROUP_DX_D;
            }else{
                return 1;
            }
        }elseif(MyConst::S_ZM16_DS == $type){
            if(1 == $value){//dan
                $group = MyConst::GROUP_DS_D;
            }else if(2 == $value){//shuang
                $group = MyConst::GROUP_DS_S;
            }else{
                return 1;
            }
        }elseif(MyConst::S_ZM16_HDX == $type){
            if(1 == $value){//small
                $group = MyConst::GROUP_HDX_X;
            }else if(2 == $value){//big
                $group = MyConst::GROUP_HDX_D;
            }else{
                return 1;
            }
        }elseif(MyConst::S_ZM16_HDS == $type){
            if(1 == $value){//dan
                $group = MyConst::GROUP_HDS_D;
            }else if(2 == $value){//shuang
                $group = MyConst::GROUP_HDS_S;
            }else{
                return 1;
            }
        }elseif(MyConst::S_ZM16_WDX == $type){
            if(1 == $value){//small
                $group = MyConst::GROUP_WDX_X;
            }else if(2 == $value){//big
                $group = MyConst::GROUP_WDX_D;
            }else{
                return 1;
            }
        }elseif (MyConst::S_ZM16_SB == $type){
            if(1 == $value){//red
                $group = MyConst::GROUP_SB_RED;
            }elseif(2 == $value){//blue
                $group = MyConst::GROUP_SB_BLUE;
            }elseif(3 == $value){//green
                $group = MyConst::GROUP_SB_GREEN;
            }else{
                return 1;
            }
        }else{
            return 2;
        }

        //合并到sMaBoard中快速计算
        $num = count($group);
        if($num<1){
            return 3;
        }
        $item_amount = $amount*1.0/($num+1);        //这个+1使倍率与实际值更接近
        foreach ($group as $ma){
            if(isset($this->sMaBoard[$pos][$ma])) {
                $this->sMaBoard[$pos][$ma] += $item_amount;
            }else{
                $this->sMaBoard[$pos][$ma] = $item_amount;
            }
        }
        //如果不是color, 49为he，退还
        if(MyConst::S_ZM16_SB != $type){
            if(isset($this->sMaBoard[$pos][49])) {
                $this->sMaBoard[$pos][49] += $amount * 1.0 / MyConst::TypeMultiple[MyConst::S_TM];
            }else{
                $this->sMaBoard[$pos][49] = $amount * 1.0 / MyConst::TypeMultiple[MyConst::S_TM];
            }
        }

        //记录到tree中精确验算,只记录原始值，不记录拆解合并值
        $this->add_s_value($type,$value,$amount,$pos);
        return 0;
    }

    private function sTeXiao($xiao, $amount){
        //找到xiao对应的ma组合
        if($xiao < MyConst::SX_SHU || MyConst::SX_ZHU < $xiao){
            return 1;
        }
        //根据今年是什么肖决定的
        $year_offset = MyConst::THIS_YEAR-MyConst::SX_SHU;
        $pos = $xiao - $year_offset;
        if($pos < 1){
            $pos += 12;
        }
        $group = MyConst::SX_GROUP[$pos];
        $item_account = $amount*1.0/count($group);
        foreach ($group as $item){
            if(isset($this->sMaBoard[MyConst::TeMa][$item])) {
                $this->sMaBoard[MyConst::TeMa][$item] += $item_account;
            }else{
                $this->sMaBoard[MyConst::TeMa][$item] = $item_account;
            }
        }

        //记录到sMaTree中精确验算
        //记录到tree中精确验算,只记录原始值，不记录拆解合并值
        $type = MyConst::S_SX_TX;
        $this->add_s_value($type,$xiao,$amount);
        return 0;
    }
    //当作teXiao合并处理
    private function sHeXiao($data, $amount){
        $xiao_num = count($data);
        if($xiao_num<1){
            return 1;
        }
        $item_amount = $amount*1.0/$xiao_num;
        foreach ($data as $item) {
            $this->sTeXiao($item,$item_amount);
        }
        return 0;
    }

    private function sColor($type, $value, $amount){
        $group = array();
        if(MyConst::S_SB_3SB == $type){
            if(MyConst::COLOR_RED == $value){
                $group = MyConst::GROUP_SB_RED;
            }elseif (MyConst::COLOR_BLUE == $value){
                $group = MyConst::GROUP_SB_BLUE;
            }elseif (MyConst::COLOR_GREEN == $value){
                $group = MyConst::GROUP_SB_GREEN;
            }else{
                return 2;
            }
        }elseif (MyConst::S_SB_BB){
            if(1 == $value){
                $group = MyConst::GROUP_RED_DA;
            }elseif (2 == $value){
                $group = MyConst::GROUP_RED_XIAO;
            }elseif (3 == $value) {
                $group = MyConst::GROUP_RED_DAN;
            }elseif (4 == $value) {
                $group = MyConst::GROUP_RED_SHUANG;
            }elseif (5 == $value) {
                $group = MyConst::GROUP_BLUE_DA;
            }elseif (6 == $value) {
                $group = MyConst::GROUP_BLUE_XIAO;
            }elseif (7 == $value) {
                $group = MyConst::GROUP_BLUE_DAN;
            }elseif (8 == $value) {
                $group = MyConst::GROUP_BLUE_SHUANG;
            }elseif (9 == $value) {
                $group = MyConst::GROUP_GREEN_DA;
            }elseif (10 == $value) {
                $group = MyConst::GROUP_GREEN_XIAO;
            }elseif (11 == $value) {
                $group = MyConst::GROUP_GREEN_DAN;
            }elseif (12 == $value) {
                $group = MyConst::GROUP_GREEN_SHUANG;
            }else{
                return 2;
            }
        }elseif(MyConst::S_SB_BBB == $type){
            if(1 == $value){
                $group = MyConst::GROUP_RED_DA_DAN;
            }elseif (2 == $value){
                $group = MyConst::GROUP_RED_DA_SHUANG;
            }elseif (3 == $value) {
                $group = MyConst::GROUP_RED_XIAO_DAN;
            }elseif (4 == $value) {
                $group = MyConst::GROUP_RED_XIAO_SHUANG;
            }elseif (5 == $value) {
                $group = MyConst::GROUP_BLUE_DA_DAN;
            }elseif (6 == $value) {
                $group = MyConst::GROUP_BLUE_DA_SHUANG;
            }elseif (7 == $value) {
                $group = MyConst::GROUP_BLUE_XIAO_DAN;
            }elseif (8 == $value) {
                $group = MyConst::GROUP_BLUE_XIAO_SHUANG;
            }elseif (9 == $value) {
                $group = MyConst::GROUP_GREEN_DA_DAN;
            }elseif (10 == $value) {
                $group = MyConst::GROUP_GREEN_DA_SHUANG;
            }elseif (11 == $value) {
                $group = MyConst::GROUP_GREEN_XIAO_DAN;
            }elseif (12 == $value) {
                $group = MyConst::GROUP_GREEN_XIAO_SHUANG;
            }else{
                return 2;
            }
        }else{
            return 1;
        }
        $item_num = count($group);
        if($item_num<1){
            return 3;
        }
        $item_amount = $amount*1.0/$item_num;
        foreach ($group as $item){
            if(isset($this->sMaBoard[MyConst::TeMa][$item])) {
                $this->sMaBoard[MyConst::TeMa][$item] += $item_amount;
            }else{
                $this->sMaBoard[MyConst::TeMa][$item] = $item_amount;
            }
        }
        //记录到tree中精确验算,只记录原始值，不记录拆解合并值
        $this->add_s_value($type,$value,$amount);
        return 0;
    }
    private function sHeadTail($type, $value, $amount){
        if(MyConst::S_WX_TS == $type){//头
            if($value<0 || 4 < $value){
                return 2;
            }
            $group = MyConst::HEAD_GROUP[$value];
        }elseif(MyConst::S_WS_WS == $type){//尾
            if($value<0 || 9 < $value){
                return 2;
            }
            $group = MyConst::TAIL_GROUP[$value];
        }else{
            return 1;
        }
        $item_num = count($group);
        if($item_num<1){
            return 3;
        }
        $item_amount = $amount*1.0/$item_num;
        foreach ($group as $item){
            if(isset($this->sMaBoard[MyConst::TeMa][$item])) {
                $this->sMaBoard[MyConst::TeMa][$item] += $item_amount;
            }else{
                $this->sMaBoard[MyConst::TeMa][$item] = $item_amount;
            }
        }
        //记录到tree中精确验算,只记录原始值，不记录拆解合并值
        $this->add_s_value($type,$value,$amount);
        return 0;
    }
    //五行
    private function sFiveElements($value, $amount){
        if($value < 1 || 5 < $value){
            return 1;
        }
        $group = MyConst::FIVE_ELEMENT_GROUP[$value];
        $item_num = count($group);
        if($item_num<1){
            return 3;
        }
        $item_amount = $amount*1.0/$item_num;
        foreach ($group as $item){
            if(isset($this->sMaBoard[MyConst::TeMa][$item])) {
                $this->sMaBoard[MyConst::TeMa][$item] += $item_amount;
            }else{
                $this->sMaBoard[MyConst::TeMa][$item] = $item_amount;
            }
        }

        $type = MyConst::S_5X;
        $this->add_s_value($type,$value,$amount);
        return 0;
    }

    //----------------------------------------------以下g前缀代表泛指---------------------------------------//
    //应该为泛指类型
    private function gZhengMaGuoGuan($data, $amount){//$data = [$pos=>$type,$pos=>$type]
        //check param
        $num = count($data);
        if($num<2 || $num>6){
            return 1;
        }
        ksort($data);
        $lastpos = -1;

        foreach ($data as $item) {
            if($item['pos'] < $lastpos){
                //至少选择两个位置的zhengma
                return 2;
            }
            //辅助校验zhengma有且最多只有一个
            $lastpos = $item['pos'];
        }
        $node['amount'] = $amount;
        $node['data'] = $data;
        //插入tree管理，用数组存储元素，到时候方便验证；
        $type = MyConst::G_ZMGG;
        $this->gMaTree[$type][] = $node;
        //同时累加总额
        $this->amount += $amount;
    }


    private function gLiangMian($type, $value, $amount){
        if(MyConst::G_LM_ZDX == $type){
            $this->add_g_array($type,array($value),$amount);
        }elseif(MyConst::G_LM_ZDS == $type){
            $this->add_g_array($type,array($value),$amount);
        }else{
            return 1;
        }
    }
    //6个正码位置中出现ma则中将
    private function gZhengMa($ma, $amount){
        $type = MyConst::G_ZM;
        $this->add_g_array($type,array($ma),$amount);
    }

    private function gLianMa($type, $data, $amount){
        $num = 0;
        switch ($type){
            case MyConst::G_LM_4://4全中
                $num = 4;
                break;
            case MyConst::G_LM_3://3全中
                $num = 3;
                break;
            case MyConst::G_LM_3_2://3中+3中2
                $num = 3;
                break;
            case MyConst::G_LM_2://2全中
                $num = 2;
                break;
            case MyConst::G_LM_2_T://2中特
                $num = 2;
                break;
            case MyConst::G_LM_T://特串
                $num = 2;
                break;
            default://错误
                Log::error("业务类型错误");
                return 2;
                break;
        }
        if(count($data) != $num){
            //错误
            Log::error("节点数量不正确");
            return 1;
        }
        $this->add_g_array($type,$data,$amount);
        return 0;
    }

    private function gLianXiao($type, $data,$amount){
        $num = 0;
        switch ($type){
            case MyConst::G_LX_2://2连肖
                $num = 2;
                break;
            case MyConst::G_LX_3://3连肖
                $num = 3;
                break;
            case MyConst::G_LX_4://4连肖
                $num = 4;
                break;
            case MyConst::G_LX_5://5连肖
                $num = 5;
                break;
            default://错误
                Log::error("业务类型错误");
                return 2;
                break;
        }
        if(count($data) != $num){
            //错误
            Log::error("节点数量不正确");
            return 1;
        }
        $this->add_g_array($type,$data,$amount);
        return 0;
    }

    //连尾
    private function gLianTail($type, $data, $amount){
        $num = 0;
        switch ($type){
            case MyConst::G_LW_2://2连尾
                $num = 2;
                break;
            case MyConst::G_LW_3://3连尾
                $num = 3;
                break;
            case MyConst::G_LW_4://4连尾
                $num = 4;
                break;
            case MyConst::G_LW_5://5连尾
                $num = 5;
                break;
            default:
                //错误
                Log::error("业务类型错误");
                return 2;
                break;
        }
        if(count($data) != $num){
            //错误
            Log::error("节点数量不正确");
            return 1;
        }
        $this->add_g_array($type,$data,$amount);
        return 0;
    }
    //取反，不中
    private function gNegate($type, $data,$amount){
        $num = 0;
        switch ($type){
            case MyConst::G_NO_5://5不中
                $num = 5;
                break;
            case MyConst::G_NO_6://6不中
                $num = 6;
                break;
            case MyConst::G_NO_7://7不中
                $num = 7;
                break;
            case MyConst::G_NO_8://8不中
                $num = 8;
                break;
            case MyConst::G_NO_9://9不中
                $num = 9;
                break;
            case MyConst::G_NO_10://10不中
                $num = 10;
                break;
            case MyConst::G_NO_11://11不中
                $num = 11;
                break;
            case MyConst::G_NO_12://12不中
                $num = 12;
                break;
            default://错误
                Log::error("业务类型错误");
                return 2;
                break;
        }
        if(count($data) != $num){
            //错误
            Log::error("节点数量不正确");
            return 1;
        }
        $this->add_g_array($type,$data,$amount);
        return 0;

    }
    private function gShengXiao($type, $value, $amount){
        if(MyConst::G_SX_ZEX == $type){//zheng x
            $this->add_g_array($type,array($value),$amount);
        }elseif(MyConst::G_SX_YX == $type){//yi x
            $this->add_g_array($type,array($value),$amount);
        }elseif(MyConst::G_SX_ZOX == $type){//zong x
            $this->add_g_array($type,array($value),$amount);
        }else{
            return 1;
        }
        return 0;
    }

    private function g7color($value,$amount){
        //check value
        if($value<MyConst::COLOR_RED || MyConst::COLOR_GREEN < $value){
            return 1;
        }
        $type = MyConst::G_SB_7SB;
        $this->add_g_array($type,array($value),$amount);
        return 0;
    }
    private function gTail($value,$amount){
        //正特尾数
       $type = MyConst::G_WS_ZTWS;
        $this->add_g_array($type,array($value),$amount);
        return 0;
    }
    private function g7Ma($type, $data,$amount){
        $num = 0;
        switch ($type){
            case MyConst::G_7M_DS:
            case MyConst::G_7M_DX:
                $num = 1;
                break;
            default://错误
                Log::error("业务类型错误");
                return 2;
                break;
        }
        if(count($data) != $num){
            //错误
            Log::error("节点数量不正确");
            return 1;
        }
        $this->add_g_array($type,$data,$amount);
        return 0;
    }
    //只中一
    private function gZhongOne($type, $data,$amount){
        $num = 0;
        switch ($type){
            case MyConst::G_Z1_5://5中1
                $num = 5;
                break;
            case MyConst::G_Z1_6://6中1
                $num = 6;
                break;
            case MyConst::G_Z1_7://7中1
                $num = 7;
                break;
            case MyConst::G_Z1_8://8中1
                $num = 8;
                break;
            case MyConst::G_Z1_9://9中1
                $num = 9;
                break;
            case MyConst::G_Z1_10://10中1
                $num = 10;
                break;
            default://错误
                Log::error("业务类型错误");
                return 2;
                break;
        }
        if(count($data) != $num){
            //错误
            Log::error("节点数量不正确");
            return 1;
        }
        $this->add_g_array($type,$data,$amount);
        return 0;
    }

    //-------------------------------数据结构插入函数----------------------------//
    private function add_s_value($type, $value, $amount,$pos=MyConst::TeMa){
        if(isset($this->sMaTree[$type][$pos][$value])){
            $this->sMaTree[$type][$pos][$value] += $amount;
        }else{
            $this->sMaTree[$type][$pos][$value] = $amount;
        }
        //根节点累积总额
        $this->s_amount += $amount;
        $this->amount = $this->s_amount+$this->g_amount;
    }
    private function add_g_array($type, $data, $amount){
        //将data升序排列
        sort($data);
        //生成key插入关联数组
        $key = implode("|",$data);
        $curNode = &$this->gMaTree[$type];//root
        if(is_null($curNode)){
            $curNode = array($key=>$amount);
        }else{
            if(isset($curNode[$key])){
                $curNode[$key] += $amount;
            }else{
                $curNode[$key] = $amount;
            }
        }
        //根节点累积总额
        $this->g_amount += $amount;
        $this->amount = $this->s_amount+$this->g_amount;
        return 0;
    }
}
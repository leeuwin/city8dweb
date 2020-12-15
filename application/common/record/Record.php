<?php
/**
 * Created by PhpStorm.
 * User: think
 * Date: 2020/4/27
 * Time: 16:58
 */

namespace app\common\record;

use think\Db;

class Record
{
    /*
    protected $namelist = [
        'gold'    =>  '金币',
        'strength'    =>  '体力',
        'map'    =>  '地图',
        'cat'    =>  '分红猫',
        'other'  =>  '其它'
    ];
    */
    //元素格式  表名字|字段|字段|字段...,其中若是type字段不需要提供，在下面的$typelist里定义；
    protected $fieldlist  =   [
        'gold' => 'gold_record|uid|type|amount',
        'strength'  =>  'strength_record|uid|type|amount',
        'award'  =>  'award_record|uid|type|amount',
    ];

    //记录类型和描述定义
    protected $typelist  =   [
        'gold.' =>  '0|金币',
        'gold.question' => '1|答题赢金',
        'gold.treasure'   => '2|宝藏赢金',
        'gold.milestone'    =>  '3|答题过关赢金',
        'gold.recieve'    =>  '4|金币领取',
        'gold.round'    =>  '5|答题全对奖励',
        'gold.son_question'    =>  '6|儿子答题进贡金币',
        'gold.grandson_question'    =>  '7|孙子答题进贡金币',
        'gold.share'    =>  '8|分享推广赢金',
        'gold.son_share'    =>  '9|下级分享推广赢金',
        'strength.'  =>  '0|体力',
        'strength.grow'  =>  '1|体力成长',
        'strength.recieve'  =>  '2|体力领取',
        'award.'  =>  '0|奖励',
        'award.main_gold'  =>  '3|主页领取金币',
        'award.main_rand_gold'  =>  '4|主页领取随机金币',
        'award.main_strength'  =>  '2|主页领取体力',
        'award.announcement'  =>  '1|公告记录',
        'award.grow'  =>  '5|体力成长',
    ];

    public function setrecord($name,$subname,$data){
        $typekey = $name.'.'.$subname;
        if(!isset($this->typelist[$typekey]))
        {
            return false;
        }
        $typeinfo = explode('|', $this->typelist[$typekey]);
        $recordinfo['updatetime'] = time();//date('Y-m-d H:i:s');
        $recordinfo['desc'] = $typeinfo[1];

        //验证数据库属性配置
        if(!isset($this->fieldlist[$name])){
            return false;
        }
        $recordfield = explode('|',$this->fieldlist[$name]);
        $tablename = $recordfield[0];
        unset($recordfield[0]);

        foreach ($recordfield as $field){
            //验证必要属性是否设置
            if('type' == $field)
            {
                $recordinfo[$field] = $typeinfo[0];
            }else{
                if(!isset($data[$field])){
                    return false;
                }
                $recordinfo[$field] = $data[$field];
            }
        }
        //将数据插入对应的表中；
        $res = Db::table($tablename)->insert($recordinfo);
        return $res;
    }
}
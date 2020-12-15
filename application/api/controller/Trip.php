<?php

namespace app\api\controller;


use app\admin\controller\Login;
use app\common\model\User as UserModel;
use app\common\model\Trip as TripModel;
use app\common\model\UserToken;
use app\common\model\UserInfo as UserInfoModel;
use app\common\validate\TripValidate;
use app\common\validate\UserValidate;
use think\App;
use think\Db;
use think\db\Query;
use think\Request;
use think\facade\Log;

class Trip extends Controller
{
    protected $authExcept = ['index','read_list','read','update','delete','publish'];

    //列表
    public function index()
    {
        $data = "please specify function!";
        return success($data);
    }

    //条件检索/获取trip列表
    public function read_list(Request $request, TripModel $model, TripValidate $validate)
    {
        $param = $request->param();

        /*
         * 1、出发城市，区县 src_city, src_district
         * 2、目的城市，区县 dst_city, dst_district
         * 3、出发日期，departure_date
        */

        //查询行程类型     1司机行程 2乘客行程 3寄货行程
        $type = isset($param['type'])?$param['type']:1;     //默认设置1司机行程

        //构建查询条件
        $map = null;

        //校验查询的目标日期是否合法
        if(isset($param['departure_date'])){
            $departure_datestamp = strtotime($param['departure_date']);
            $today_datestamp = strtotime("today");
            if($departure_datestamp>=$today_datestamp){
                //不是过去的时间
                $map['departure_date'] = $param['departure_date'];
            }
        }

        if(isset($param['src_city'])){
            $map['src_city'] = $param['src_city'];
            if(isset($param['src_district'])){
                $map['src_district'] = $param['src_district'];
            }
        }
        if(isset($param['dst_city'])){
            $map['dst_city'] = $param['dst_city'];
            if(isset($param['dst_district'])){
                $map['dst_district'] = $param['dst_district'];
            }
        }
        $rows = 10;
        if(isset($param['page'])){
            $page = $param['page'];
            if(isset($param['rows']))
            {
                $rows = $param['rows'];
            }
        }
        //执行查询
        if(isset($page)) {
            $trips = Db::table('trip')
                ->where('type', 'eq', $type)//限定类型
                ->where('departure_timestamp', 'egt', time())//限定查询目标须是此刻之后的
                ->where($map)
                ->order('departure_timestamp')
                ->page($page,$rows)                                                 //分页
                ->select();
        }else{
            $trips = Db::table('trip')
                ->where('type', 'eq', $type)//限定类型
                ->where('departure_timestamp', 'egt', time())//限定查询目标须是此刻之后的
                ->where($map)
                ->order('departure_timestamp')
                ->select();
        }
        return success($trips);
    }
    //查看某个具体trip详情
    public function read(Request $request, TripModel $model, TripValidate $validate)
    {
        $param = $request->param();
        $validate_result = $validate->scene('trip-read')->check($param);
        if (!$validate_result) {
            return error($validate->getError());
        }
        $trip = $model->getTripListById($param['id']);
        if(is_null($trip)){
            return error('行程不存在');
        }
        return success($trip);
    }
    //发布行程
    public function publish(Request $request, TripModel $model, TripValidate $validate)
    {
        $param = $request->param();
        //查询行程类型    1司机行程  2乘客行程 3寄货行程
        $type = !isset($param['type'])?1:$param['type'];
        if(1 == $type){
            $validate_result = $validate->scene('driver-publish')->check($param);
        }else if(2 == $type){
            $validate_result = $validate->scene('passenger-publish')->check($param);
        }else if(3 == $type){
            $validate_result = $validate->scene('sender-publish')->check($param);
        }else {
            $validate_result = false;
        }
        if($validate_result) {
            //生成随机码
            $passwd = rand(10,99);
            $param['passwd'] = $passwd;
            //发布者id
            if(isset($param['proxy']) && 0 < $param['proxy']){
                //关联一个用户
                $param['publisher'] = $param['proxy'];
            }elseif(isset($param['proxy']) &&  $param['proxy'] < 0){
                $param['publisher'] = $param['proxy'];
            }else {
                $param['publisher'] = $this->uid;
            }
            //最晚出发时间戳
            $departure_datetime_last = $param['departure_date'].' '.$param['departure_time_last'];
            $param['departure_timestamp'] = strtotime($departure_datetime_last);
            $trip = $model::create($param);
            return success($trip,'行程创建成功');
        }else {
            return error($validate->getError());
        }
    }

    //更新
    public function update(Request $request, TripModel $model, TripValidate $validate)
    {
        $param           = $request->param();
        $validate_result = $validate->scene('trip-edit')->check($param);
        if (!$validate_result) {
            return error($validate->getError());
        }
        //$trip = $model->getTripListById($param['id']);
        $trip = $model::get($param['id']);
        if(is_null($trip)){
            return error('行程不存在');
        }
        $result = $trip->save($param);
        return $result ? success($trip) : error('行程更新失败');
    }
    //删除
    public function delete($id, TripModel $model, TripValidate $validate)
    {
        $trip = $model::get($id);
        if(is_null($trip)){
            return error('行程不存在');
        }
        if ($model->softDelete) {
            $result = $model->whereIn('id', $id)->useSoftDelete('delete_time', time())->delete();
        } else {
            $result = $model->whereIn('id', $id)->delete();
        }
        return $result ? success('行程删除成功') : error('行程删除失败');
    }
}

<?php
/**
 * 用户验证器
 */

namespace app\common\validate;

class TripValidate extends Validate
{
    protected $rule = [
        'id|行程id'   => 'require',
        'fromAddrName|出发地名称'   => 'require',
        'fromAddress|出发地地址'   => 'require',
        'fromLongitude|出发地经度'   => 'require',
        'fromLatitude|出发地纬度'   => 'require',
        'throughAddrName|经由地名称'   => 'require',
        'throughAddress|经由地地址'   => 'require',
        'throughLongitude|经由地经度'   => 'require',
        'throughLatitude|经由地纬度'   => 'require',
        'destAddrName|目的地名称'   => 'require',
        'destAddress|目的地地址'   => 'require',
        'destLongitude|目的地经度'   => 'require',
        'destLatitude|目的地纬度'   => 'require',
        'price|行程单价'   => 'require',
        'departure_time_first|出发时间'   => 'require',
        'departure_time_last|最迟出发时间'   => 'require',
        'seatCount|座位数量'   => 'require',
        'cargoCount|行李数量'   => 'require',
        'remarks|备注信息'   => 'require',
        'departure_date|行程日期'   => 'require',
        'weekday|星期几'   => 'require',
        'passwd|乘车码'   => 'require',
    ];

    protected $message = [
        'id.require'    => '请设置行程id',
        'fromAddrName.require' => '出发地名不能为空',
        'fromAddress.require'      => '详细出发地址不能为空',
        'destAddrName.require'        => '目的地名不能为空',
        'destAddress.require'      => '详细目的地地址不能为空',
        'departure_date.require'        => '行程日期不能为空',
        'departure_time_first.require'        => '行程日期不能为空',
        'departure_time_last.require'        => '行程日期不能为空',
        'price.require'      => '座位单价不能为空',

    ];

    protected $scene = [
        'driver-publish' => ['fromAddrName', 'fromAddress','destAddrName','destAddress','departure_date','departure_time_first','departure_time_last','price'],
        'passenger-publish' => ['fromAddrName', 'fromAddress','destAddrName','destAddress','date'],
        'trip-read' => ['id'],
        'trip-edit' => ['id'],
        'trip-delete' => ['id'],
    ];


}

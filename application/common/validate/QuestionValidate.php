<?php
/**
 * Created by PhpStorm.
 * User: think
 * Date: 2020/4/29
 * Time: 14:39
 */

namespace app\common\validate;


class QuestionValidate extends Validate
{
    protected $rule = [
        'wingold|赢得金币' => 'require',
        'iscorrect|是否答对' => 'require',
        'qid|问题编号' => 'require',
        'mapid|地图编号' => 'require',
        'isinstruct|是否使用提示' => 'require',
        'try|尝试回答次数' => 'require',
        'adclick|广告点击次数' => 'require',
        'begtime|开始时间' => 'require',
        'endtime|结束时间' => 'require',
        'ispass|是否通过'   =>  'require',
        'qusindex|问题序号'   =>  'require',
    ];

    protected $message = [
        'wingold.require' => '赢得金币不能为空',
        'iscorrect.require' => '是否答对不能为空',
        'qid.require' => '问题编号不能为空',
        'mapid.require' => '地图编号不能为空',
        'isinstruct.require' => '是否使用提示不能为空',
        'try.require' => '尝试次数不能为空',
        'adclick.require' => '广告点击不能为空',
        'begtime.require' => '开始时间不能为空',
        'endtime.require' => '上交时间不能为空',
        'ispass.require' => '是否通过不能为空',
        'qusindex.require' => '问题序号不能为空',

    ];

    protected $scene = [
        'submit_ans'       => ['wingold', 'iscorrect', 'qid', 'isinstruct', 'try', 'adclick','begtime','endtime'],
        'finish_ans'        =>  ['mapid','ispass','wingold','qusindex'],
    ];
}
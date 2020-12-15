<?php
/**
 * Created by PhpStorm.
 * User: NickBai
 * Email: 876337011@qq.com
 * Date: 2019/10/11
 * Time:  14:02
 */
namespace app\common\model;

use think\Model;

class AdminOperateLog extends Model
{
    protected $table = 'admin_operate_log';

    /**
     * 写操作日志
     * @param $param
     * @return array
     */
    public function writeLog($param)
    {
        try {
            $this->insert($param);
        } catch (\Exception $e) {
            return modelReMsg(-1, '', $e->getMessage());
        }

        return modelReMsg(0, '', '写入成功');
    }

    /**
     * 获取角色列表
     * @param $limit
     * @param $where
     * @return array
     */
    public function getOperateLogList($limit, $where)
    {
        try {

            $res = $this->where($where)->order('log_id', 'desc')->paginate($limit);

        }catch (\Exception $e) {

            return modelReMsg(-1, '', $e->getMessage());
        }

        return modelReMsg(0, $res, 'ok');
    }
}
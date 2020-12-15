<?php
/**
 * 行程模型
 */

namespace app\common\model;

use think\Db;
use think\Exception;
use think\facade\Log;
use think\Model;
use think\model\concern\SoftDelete;

class Trip extends Model
{
    use SoftDelete;
    public $softDelete = true;
    //    protected $name = 'user';
    protected $table = 'Trip';
    //    protected $autoWriteTimestamp = true;

    public function getTripListById($id){
        try{
            $res = Db::table($this->table)->field('*')->where('id', $id)->find();
        }catch (Exception $e){
            Log::error($e->getMessage());
            return modelReMsg(-1, [], $e->getMessage());
        }

        return $res;
    }
}
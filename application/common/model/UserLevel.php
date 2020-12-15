<?php
/**
 * 用户等级模型
 */

namespace app\common\model;

use think\Model;
use think\model\concern\SoftDelete;

class UserLevel extends Model
{
    use SoftDelete;
    public $softDelete = true;
    protected $name = 'user_level';
    protected $table = 'user_level';
    protected $autoWriteTimestamp = true;

    //可搜索字段
    protected $searchField = ['name', 'description',];

    //关联用户
    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function getUserLevelById($id){
        $data = $this->where('id', $id)->find();

        return $data;
    }


}

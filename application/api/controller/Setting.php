<?php


namespace app\api\controller;

use app\common\model\Faq as FaqModel;
use app\common\model\Announcement as AnnouncementModel;
use app\common\model\LoginAd as LoginAdModel;
use app\common\model\Poster as PosterModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use think\facade\Request;
use think\response\Json;


class Setting extends Controller
{
    protected $authExcept = ['appupdate', 'faqs', 'loginad','redrainconfig'];

    /**
     * 获取app更新内容
     * @return Json
     */
    public function appUpdate(){
        $result = [];
        $version = input('version', 0);
        $current_version = sysconf('app_version');

        if ($this->versionCompare($current_version, $version) > 0){
            $result['update'] = true;
            $result['updateWay'] = sysconf('version_update_way') == 1 ? 1 : 0;
            $result['wgtUrl'] = Request::domain() . sysconf('hot_update_url');
            $result['pkgUrl'] = sysconf('android_download_url');
            $result['iosUrl'] = sysconf('ios_download_url');
            $result['versionUpdateInfo'] = sysconf('version_update_info');
        } else {
            $result['update'] = false;
        }
        return success($result);
    }

    /**
     * 常见问题
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function faqs(){
        $FaqModel = new FaqModel();
        $where = [
            'status' => 1,
        ];
        $res = $FaqModel::where($where)->order('sort desc')->select();
        foreach ($res as $key => &$val){
            $val['answer'] = htmlspecialchars_decode($val['answer']);
        }
        return success($res);
    }

    /**
     * 公告记录 -- 不轮播
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function announcements(){
        $announcementModel = new AnnouncementModel();
        $where = [
            'status' => 1,
            'type' => 0,
        ];
        $res = $announcementModel::where($where)->order('sort desc')->select();
        foreach ($res as $key => &$val){
            $val['content'] = htmlspecialchars_decode($val['content']);
        }
        return success($res);
    }

    /**
     * 公告记录轮播
     * @return Json
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     */
    public function announcementForShuffling(){
        $announcementModel = new AnnouncementModel();
        $where = [
            'status' => 1,
            'type' => 1,
        ];
        $res = $announcementModel::where($where)->order('sort desc')->select();
        return success($res);
    }

    /**
     *
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function loginAd(){
        $loginAdModel = new LoginAdModel();
        $where = [
            'status' => 1,
        ];
        $res = $loginAdModel::where($where)->order('sort desc')->select();
        return success($res);
    }

    /**
     * 分享海报
     * @return Json
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function sharePoster(){
        $posterModel = new PosterModel();
        $where = [
            'is_default'=> 1,
            'type' => 1,
        ];

        $res = $posterModel::where($where)->find();
        return success($res);
    }

    public function redRainConfig(){

        $res['redpacketMoneyScope'] = sysconf('redpacket_money_scope');
        $res['redpacketMaxMoney'] = sysconf('redpacket_max_money');
        $res['redpacketWinRate'] = sysconf('redpacket_win_rate');
        $res['nextRedpacketTime'] = sysconf('next_redpacket_time');

        return success($res);
    }

    public function generalConfig(){
        //$res['nextRedpacketTime'] = sysconf('next_redpacket_time');
        $res['maxdividend'] = 10000;
        $res['exchangerate'] = sysconf('coin_money_exchange');
        $res['requestrate'] = 60;
        return success($res);
    }

    //正则提取字符串中的数字
    private function reg($str){
        return preg_replace('/[^0-9]/','', $str);
    }

    //根据length的长度进行补0的操作，$length的值为两个版本号中最长的那个
    private function add($str, $length){
        return str_pad($str, $length,"0");
    }

    //实现逻辑
    private function versionCompare($v1,$v2){
        $length = strlen($this->reg($v1))>strlen($this->reg($v2)) ? strlen($this->reg($v1)): strlen($this->reg($v2));
        $v1 = $this->add($this->reg($v1),$length);
        $v2 = $this->add($this->reg($v2),$length);
        if($v1 == $v2) {
            return 0;
        }else{
            return $v1 > $v2 ? 1 : -1;
        }
    }

}
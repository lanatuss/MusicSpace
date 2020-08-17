<?php


namespace app\api\controller;
use think\Controller;
use Metowolf\Meting;
use think\Request;

class Music extends Controller
{
    private $_cookie = 'os=pc; osver=Microsoft-Windows-10-Professional-build-10586-64bit; appver=2.0.3.131777; channel=netease; __remember_me=true; MUSIC_U=d445d6862598bbd57330ac0077d7be0aff3b508249b598fa24a7b3b7fabdbce833a649814e309366';
    private $_meting = null;
    public static $source_baidu = 'baidu';
    public static $source_kugou = 'kugou';
    public static $source_xiaomi = 'xiami';
    public static $source_tencent = 'tencent';
    public static $source_netease = 'netease';
    public function _initialize()
    {
        $this->_meting = new Meting(self::$source_netease);
        $this->_meting->cookie($this->_cookie);
    }

    public function search(){
        $search = Request::instance()->get('search','');
        $platform = Request::instance()->get('platform','netease');
        if(empty($search)){
            return json(['code'=> -1,'msg'=>'请输入搜索关键字']);
        }
        $platform_str = 'source_'.strtolower($platform);
        if(isset(self::$$platform_str) && $platform!='netease'){
            $this->_meting = new Meting(self::$$platform_str);
            switch (self::$$platform_str){
                case 'tencent':
                    $this->_meting->cookie('pgv_pvi=6086360064; pgv_si=s887317504; pgv_pvid=2050129028; yplayer_open=0; ts_last=y.qq.com/portal/player.html; ts_uid=8405946048; yq_index=0; qqmusic_fromtag=66; player_exist=1');
                    break;
            }
        }
        //
        $search_res = $this->_meting->format(true)->search($search);
        $search_res = json_decode($search_res,true);
        if(count($search_res)>0){
            $music_id = $search_res[0]['id'];
            var_dump($music_id);die();
            $find_res = $this->_meting->format(true)->url($music_id);
            return json(['code'=>1,'msg'=>'success','data'=>json_decode($find_res)]);
        }
        else{
            return json(['code'=> 1,'msg'=>'no data','data'=>[]]);
        }
    }

}
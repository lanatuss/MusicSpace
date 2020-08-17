<?php


namespace app\api\controller;
use think\Controller;
use Metowolf\Meting;
use think\Request;

class Music extends Controller
{
    private $_cookie = 'os=pc; osver=Microsoft-Windows-10-Professional-build-10586-64bit; appver=2.0.3.131777; channel=netease; __remember_me=true; MUSIC_U=d445d6862598bbd57330ac0077d7be0aff3b508249b598fa24a7b3b7fabdbce833a649814e309366';
    private $_meting = null;
    const SOURCE_BAIDU = 'baidu';
    const SOURCE_KUGOU = 'kugou';
    const SOURCE_XIAOMI = 'xiami';
    const SOURCE_TENCENT = 'tencent';
    const SOURCE_NETEASE = 'netease';
    public function _initialize()
    {
        $this->_meting = new Meting(self::SOURCE_NETEASE);
        $this->_meting->cookie($this->_cookie);
    }

    public function search(){
        $search = Request::instance()->get('search','');
        if(empty($search)){
            return false;
        }
        $search_res = $this->_meting->format(true)->search($search);
        if(count($search_res)>0){
            $music_id = $search_res[0]['id'];
            $find_res = $this->_meting->format(true)->url($music_id);
            return json(['code'=>1,'msg'=>'success','data'=>json_decode($search_res)]);
        }
        else{
            return json(['code'=> 1,'msg'=>'no data','data'=>[]]);
        }
    }

}
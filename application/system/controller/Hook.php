<?php
namespace app\system\controller;
use think\Controller;
use think\Log;
use think\Request;

class Hook extends Controller
{
 public $serect = '614637715@qq.com'; //webhooks中配置的密钥
    public function deploy()
    {
        $requestBody = file_get_contents('php://input'); //每次推送的时候，会接收到post过来的数据。
        $payload = json_decode($requestBody, true);    //将数据转成数组，方便取值。
        if(empty($payload)){
            //写日志
            $this->write_log('send fail from github is empty');exit;
        }else{
            //获取github推送代码时经过哈希加密密钥的值
            $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
        }

        if (strlen($signature) > 8 && $this->isFromGithub($requestBody,$signature)) {
            //验证密钥是否正确，如果正确执行命令。
            $res = shell_exec("cd /appdb/nginx/html/MusicSpace && /usr/local/git/bin/git pull origin master 2>&1");
            $res_log = "\n -------------------------".PHP_EOL;
            $header = Request::instance()->header();
            $res_log .= '['.$payload['commits'][0]['author']['name'] . ']' . '向[' . $payload['repository']['name'] . ']项目的' . $payload['ref'] . '分支'.$header['x-github-event'].'了代码。commit信息是：'.$payload['commits'][0]['message'].'。详细信息如下：' . PHP_EOL;
            $res_log .= $res.PHP_EOL;
            http_response_code(200);
            $this->write_log($res_log);
        }else{
            $this->write_log('git 提交失败！');
            abort(403);
        }
    }

    public function isFromGithub($payload,$signature)
    {
        //$hash是github的密钥。然后与本地的密钥做对比。
        list($algo, $hash) = explode("=", $signature, 2);
        return $hash === hash_hmac($algo, $payload, $this->serect);
    }

    public function write_log($data)
    {
        Log::write($data,'error');
        // 此处加载日志类，用来记录git push信息，可以自行写。
    }
}
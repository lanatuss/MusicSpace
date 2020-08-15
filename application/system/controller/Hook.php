<?php
namespace app\system\controller;
use think\Controller;
use think\Log;
use think\Request;

class Hook extends Controller
{
 public $serect = '614637715@qq.com'; //webhooks�����õ���Կ
    public function deploy()
    {
        $requestBody = file_get_contents('php://input'); //ÿ�����͵�ʱ�򣬻���յ�post���������ݡ�
        $payload = json_decode($requestBody, true);    //������ת�����飬����ȡֵ��
        if(empty($payload)){
            //д��־
            $this->write_log('send fail from github is empty');exit;
        }else{
            //��ȡgithub���ʹ���ʱ������ϣ������Կ��ֵ
            $signature = $_SERVER['HTTP_X_HUB_SIGNATURE'];
        }

        if (strlen($signature) > 8 && $this->isFromGithub($requestBody,$signature)) {
            //��֤��Կ�Ƿ���ȷ�������ȷִ�����
            $res = shell_exec("cd /appdb/nginx/html/MusicSpace && /usr/bin/git pull origin master 2>&1");
            $res_log = "\n -------------------------".PHP_EOL;
            $header = Request::instance()->header();
            $res_log .= '['.$payload['commits'][0]['author']['name'] . ']' . '��[' . $payload['repository']['name'] . ']��Ŀ��' . $payload['ref'] . '��֧'.$header['x-github-event'].'�˴��롣commit��Ϣ�ǣ�'.$payload['commits'][0]['message'].'����ϸ��Ϣ���£�' . PHP_EOL;
            $res_log .= $res.PHP_EOL;
            http_response_code(200);
            $this->write_log($res_log);
        }else{
            $this->write_log('git �ύʧ�ܣ�');
            abort(403);
        }
    }

    public function isFromGithub($payload,$signature)
    {
        //$hash��github����Կ��Ȼ���뱾�ص���Կ���Աȡ�
        list($algo, $hash) = explode("=", $signature, 2);
        return $hash === hash_hmac($algo, $payload, $this->serect);
    }

    public function write_log($data)
    {
        Log::write($data,'error');
        // �˴�������־�࣬������¼git push��Ϣ����������д��
    }
}
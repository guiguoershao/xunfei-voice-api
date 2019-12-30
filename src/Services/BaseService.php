<?php


namespace Guiguoershao\XunFeiVoiceApi\Services;


use WebSocket;

abstract class BaseService
{
    // 应用ID
    protected $appId = '';

    //  接口key
    protected $apiKey = '';

    //  接口secret
    protected $apiSecret = '';

    //  socket url
    protected $socketUrl = 'wss://tts-api.xfyun.cn/v2/tts';

    //  请求主机
    protected $reqHost = 'ws-api.xfyun.cn';

    //  请求路径
    protected $reqPath = '/v2/tts';

    //  headers 是参与签名的参数，请注意是固定的参数名（"host date request-line"）
    protected $socketHeaders = 'host date request-line';

    //  请求时间戳
    protected $reqTime = '';

    protected $encryptMethod = 'hmac-sha256';

    public function __construct(array $config = [])
    {
        $this->appId = isset($config['appId']) ? $config['appId'] : '';
        $this->apiKey = isset($config['apiKey']) ? $config['apiKey'] : '';
        $this->apiSecret = isset($config['apiSecret']) ? $config['apiSecret'] : '';
        $this->reqTime = isset($config['reqTime']) ? $config['reqTime'] : time();
    }

    /**
     * 创建socket连接签名
     * @param $rfcDatetime
     * @return string
     */
    protected function createSocketSign($rfcDatetime)
    {
        // $rfcDatetime = date('D, d M Y H:i:s', strtotime('-8 hour', $this->reqTime)) . ' GMT';
        $signature_origin = "host: {$this->reqHost}\n";
        $signature_origin .= "date: {$rfcDatetime}\n";
        $signature_origin .= "GET {$this->reqPath} HTTP/1.1";
        $signature_sha = hash_hmac('sha256', $signature_origin, $this->apiSecret, true);
        $signature_sha = base64_encode($signature_sha);
        // $authorization_origin = 'api_key="' . $this->apiKey . '", algorithm="hmac-sha256", ';
        $authorization_origin = "api_key={$this->apiKey}, algorithm={$this->encryptMethod}, ";
        // $authorization_origin .= 'headers="host date request-line", signature="' . $signature_sha . '"';
        $authorization_origin .= "headers={$this->socketHeaders}, signature={$signature_sha}";
        $authorization = base64_encode($authorization_origin);
        return $authorization;
    }

    /**
     * 生成Url
     * @return string
     */
    protected function createSocketUrl()
    {
        $rfcDatetime = date('D, d M Y H:i:s', strtotime('-8 hour', $this->reqTime)) . ' GMT';
        $authorization = $this->createSocketSign($rfcDatetime);
//        $url = $this->socketUrl . '?' . 'authorization=' . $authorization . '&date=' . urlencode($time) . '&host=ws-api.xfyun.cn';
        $rfcDatetime = urlencode($rfcDatetime);
        $url = $this->socketUrl . "?authorization={$authorization}&date={$rfcDatetime}&host=ws-api.xfyun.cn";
        return $url;
    }


    /**
     * 生成要发送的消息体
     * @param $app_id
     * @param $speed
     * @param $volume
     * @param $pitch
     * @param $draft_content
     * @return array
     */
    protected function createMsg($app_id, $speed, $volume, $pitch, $draft_content)
    {
        return [
            'common' => [
                'app_id' => $app_id,
            ],
            'business' => [
                'aue' => 'raw',
                'auf' => 'audio/L16;rate=16000',
                'vcn' => 'aisbabyxu',
                'speed' => (int)$speed,
                'volume' => (int)$volume,
                'pitch' => (int)$pitch,
                'tte' => 'utf8',
            ],
            'data' => [
                'status' => 2,
                'text' => base64_encode($draft_content),
            ],
        ];
    }
}
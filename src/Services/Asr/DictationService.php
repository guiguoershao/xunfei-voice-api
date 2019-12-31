<?php


namespace Guiguoershao\XunFeiVoiceApi\Services\Asr;


use Guiguoershao\XunFeiVoiceApi\Services\BaseService;
use Guiguoershao\XunFeiVoiceApi\Utils\Response;
use WebSocket;

class DictationService extends BaseService
{
    //  socket url
    protected $socketUrl = 'wss://iat-api.xfyun.cn/v2/iat';

    //  请求主机
    protected $reqHost = 'iat-api.xfyun.cn';

    //  请求路径
    protected $reqPath = '/v2/iat';

    //  headers 是参与签名的参数，请注意是固定的参数名（"host date request-line"）
    protected $socketHeaders = 'host date request-line';

    protected function createReqParams($draftContent)
    {
        return [
            'common' => [
                'app_id' => $this->appId,
            ],
            'business' => [
                'language' => 'zh_cn',
                'domain' => 'iat', // iat：日常用语 medical：医疗
                'accent' => 'mandarin',
//                'vad_eos' => 2000,
            ],
            'data' => [
                'status' => 0,
                'format' => 'audio/L16;rate=16000',
                'encoding' => 'raw',
                'audio' => $draftContent, // base64 音频流
            ],
        ];
    }

    protected function requestWebSocketService(array $params)
    {
        $response = new Response();
        $response->setCode(-1)->setMessage("未知错误");
        // $audioFile = '/data/www/test/file/'.time().'.pcm';
        $audioNewContent = '';
        $audioData = [];
        $url = $this->createSocketUrl();
        $client = new WebSocket\Client($url);

        try {
            //  发送消息
            $client->send(json_encode($params));
            $client->send(json_encode(['data' => ['status' => 2]]));
            $socketResponse = $client->receive();

            $result = @json_decode($socketResponse, true);

            do {
                if ($result['code']) {
                    continue;
//                    return $result;
                }

                //返回的音频需要进行base64解码
                $audioContent = base64_decode($result['data']['audio']);
                // file_put_contents($audioFile, $audioContent, FILE_APPEND);
                $audioNewContent .= $audioContent;
                $audioData[] = $result;

                //继续接收消息
                $result = $client->receive();
                $result = json_decode($result, true);
            } while ($result['data']['status'] != 2);
        } catch (\Exception $exception) {

        }
    }
}
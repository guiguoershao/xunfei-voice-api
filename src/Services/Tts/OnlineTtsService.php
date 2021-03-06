<?php

namespace Guiguoershao\XunFeiVoiceApi\Services\Tts;

use Guiguoershao\XunFeiVoiceApi\Services\BaseService;
use Guiguoershao\XunFeiVoiceApi\Utils\Response;
use WebSocket;

class OnlineTtsService extends BaseService
{
    //  socket url
    protected $socketUrl = 'wss://tts-api.xfyun.cn/v2/tts';

    //  请求主机
    protected $reqHost = 'ws-api.xfyun.cn';

    //  请求路径
    protected $reqPath = '/v2/tts';

    //  headers 是参与签名的参数，请注意是固定的参数名（"host date request-line"）
    protected $socketHeaders = 'host date request-line';

    protected function createReqParams($draft_content, $speed = 50, $volume = 50, $pitch = 50)
    {
        return [
            'common' => [
                'app_id' => $this->appId,
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

        } catch (\WebSocket\ConnectionException $connectionException) {
            $response->setMessage("语音识别异常：{$connectionException->getMessage()}");
        } catch (\Exception $exception) {
            $response->setMessage("语音识别异常：{$exception->getMessage()}");
        } finally {
            $client->close();
            $response->setMessage("finally 语音识别成功");
        }

        $data = [
//            'audioPcmFile' => $audioFile,
            'audioData' => $audioData,
            'audioContent' => $audioNewContent
        ];
        $response->setCode(0)->setMessage("语音识别成功")->setData($data);
        return $response;
    }

    /**
     * @param $draft_content
     * @param int $speed
     * @param int $volume
     * @param int $pitch
     * @return false|Response|mixed|string|null
     */
    public function request($draft_content, $speed = 50, $volume = 50, $pitch = 50)
    {
        $params = $this->createReqParams($draft_content, $speed, $volume, $pitch);

        return $this->requestWebSocketService($params);
    }
}
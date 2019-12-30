<?php

namespace Guiguoershao\XunFeiVoiceApi\Services\Tts;

use Guiguoershao\XunFeiVoiceApi\Services\BaseService;
use Guiguoershao\XunFeiVoiceApi\Utils\Response;
use WebSocket;

class OnlineTtsService extends BaseService
{
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
        $audioFile = '/data/www/test/file/'.time().'.pcm';
        try {
            $url = $this->createSocketUrl();
            $client = new WebSocket\Client($url);
            //  发送消息
            $client->send(json_encode($params));
            $socketResponse = $client->receive();

            $result = @json_decode($socketResponse, true);
            $audioNewContent = '';
            do {
                if ($result['code']) {
                    return $result;
                }

                //返回的音频需要进行base64解码
                $audioContent = base64_decode($result['data']['audio']);
                file_put_contents($audioFile, $audioContent, FILE_APPEND);
                $audioNewContent .= $audioContent;

                //继续接收消息
                $result = $client->receive();
                $result = json_decode($result, true);
            } while ($result['data']['status'] != 2);

            $data = [
                'audioPcmFile' => $audioFile,
                'audioContent' => $audioNewContent
            ];
            $response->setCode(0)->setMessage("语音识别成功")->setData($data);
        } catch (\WebSocket\ConnectionException $connectionException) {
            $response->setCode(0)->setMessage("语音识别异常：{$connectionException->getMessage()}");
        } catch (\Exception $exception) {
            $response->setCode(0)->setMessage("语音识别异常：{$exception->getMessage()}");
        } finally {
            $client->close();
            $response->setCode(0)->setMessage("finally 语音识别成功");
        }

        return $response->setCode(0)->setMessage("语音识别成功");
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
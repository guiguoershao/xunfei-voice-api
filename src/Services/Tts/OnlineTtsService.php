<?php

namespace Guiguoershao\XunFeiVoiceApi\Services\Tts;

use Guiguoershao\XunFeiVoiceApi\Services\BaseService;
use WebSocket;
class OnlineTtsService extends BaseService
{
    protected function createReqParams()
    {

    }

    protected function requestWebSocketService(array $params)
    {
        try {
            $url = $this->createSocketUrl();
            $client = new WebSocket\Client($url);
            //  发送消息
            $client->send(json_encode($params));
            $response = $client->receive();

            $response = @json_decode($response, true);
            do {
                if ($response['code']) {
                    return $response;
                }
                //返回的音频需要进行base64解码
                $audioContent = base64_decode($response['data']['audio']);
                file_put_contents($audio_file, $audioContent, FILE_APPEND);
                //继续接收消息
                $response = $client->receive();
                $response = json_decode($response, true);
            } while ($response['data']['status'] != 2);

        }  catch (\WebSocket\ConnectionException $connectionException) {
            var_dump($connectionException->getMessage());
        } catch (\Exception $exception) {
            var_dump($exception->getMessage());
        } finally {
            var_dump('finally');
            $client->close();
        }
    }
}
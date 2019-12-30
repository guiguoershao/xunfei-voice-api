<?php

$config = [
    'appId' => '5d52239f',
    'apiKey' => '8c03514c1951b0a7a67faad578cffb0d',
    'apiSecret' => '75c1fa573d29b82e3fa6abb4512e8004',
];
$tts = new \Guiguoershao\XunFeiVoiceApi\Services\Tts\OnlineTtsService();
$result = $tts->request('你好世界');
var_dump($result);
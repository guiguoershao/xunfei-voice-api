<?php

$tts = new \Guiguoershao\XunFeiVoiceApi\Services\Tts\OnlineTtsService();
$result = $tts->request('你好世界');
var_dump($result);
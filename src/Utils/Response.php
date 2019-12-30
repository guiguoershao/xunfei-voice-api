<?php


namespace Guiguoershao\XunFeiVoiceApi\Utils;


class Response
{

    public $code = 0;
    public $message = 'ok';
    public $data = [];

    //  请求数据信息
    public $reqData = [];

    //  请求当前时间戳 毫秒
    public $reqMicroTime = '';

    //  详情结果
    public $resData = [];

    //  响应时间戳 毫秒
    public $resMicroTime = '';

    //  请求方法名
    public $reqMethod = '';

    //  需要写日志的方法列表
    const WRITE_LOG_METHOD_LIST = [
    ];

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     * @return Response
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

}
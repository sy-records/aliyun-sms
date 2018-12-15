<?php

class Sms
{
    /**
     * 发送短信
     * @param $mobile 手机号
     * @param $code 验证码
     *
     * @return mixed
     */
    protected function sendSms($mobile, $code)
    {
        // 获取配置信息
        $config = $this->config['sms'];
        $accessKeyId = $config['appId']; // 阿里云 AccessKeyID
        $accessKeySecret = $config['appKey'];  // 阿里云 AccessKeySecret
        $templateCode = $config['tplId'];   // 短信模板ID
        $signName=$config['signName'];   // 短信签名
        $params = array (
            'SignName' => $signName,
            'Format' => 'JSON',
            'Version' => '2017-05-25',
            'AccessKeyId' => $accessKeyId,
            'SignatureVersion' => '1.0',
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => uniqid(),
            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'Action' => 'SendSms',
            'TemplateCode' => $templateCode,
            'PhoneNumbers' => $mobile,
            // 营销短信无需 TemplateParam 参数
            'TemplateParam' => '{"code":"' . $code . '"}'
        );

        // 计算签名并把签名结果加入请求参数
        $params ['Signature'] = $this->computeSignature($params, $accessKeySecret);

        // 发送请求
        $url = 'http://dysmsapi.aliyuncs.com/?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);

        //返回请求结果
        return $result;
    }

    /**
     * 签名
     *
     * @param unknown $parameters
     * @param unknown $accessKeySecret
     * @return string
     */
    protected function computeSignature($parameters, $accessKeySecret) {
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $stringToSign = 'GET&%2F&' . $this->percentencode ( substr ( $canonicalizedQueryString, 1 ) );
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
        return $signature;
    }

    protected function percentEncode($string) {
        $string = urlencode($string);
        $string = preg_replace('/\+/', '%20', $string);
        $string = preg_replace('/\*/', '%2A', $string);
        $string = preg_replace('/%7E/', '~', $string);
        return $string;
    }
}

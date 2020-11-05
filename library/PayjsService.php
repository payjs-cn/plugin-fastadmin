<?php

namespace addons\payjs\library;


class PayjsService
{
    private $url = 'https://payjs.cn/api';  //接口地址
    private $key;            // 填写通信密钥
    private $mchid;          // 商户号
    private $body;          // 订单标题
    private $outTradeNo;          // 订单号
    private $payChannel;          // 支付通道
    private $openid;          // openid
    private $totalFee;          // 订单金额
    private $notifyUrl;          // 接收微信支付异步通知的回调地址
    private $payMode = 'weixin';        //支付模式

    public function __construct(array $configs)
    {
        $this->mchid = $configs['mchid'];;
        $this->key = $configs['appkey'];
        $this->notifyUrl = $configs['notify_url'];
        $this->payChannel = $configs['pay_channel'];
        return $this;
    }

    public function getQrcode($data)
    {
        $this->payMode = $data['type'];
        $this->totalFee = $data['total_fee'];
        $this->outTradeNo = $data['out_trade_no'];
        $this->body = $data['body'] ?? '订单号：' . $this->outTradeNo;
        return $this->native();
    }

    /**
     * native支付
     * @return mixed
     */
    public function native()
    {
        $data = array(
            'mchid' => $this->mchid,
            'total_fee' => $this->totalFee * 100,      // 金额,单位:分
            'out_trade_no' => $this->outTradeNo,       // 订单号
            'body' => $this->body ?: '订单号：' . $this->outTradeNo, // 订单标题
            'notify_url' => $this->notifyUrl,             // 回调地址
        );
        if ($this->payMode == 'alipay') {
            $data['type'] = 'alipay';
        }

        $data['sign'] = $this->sign($data);
        return $this->curlPost($this->url . '/native', $data);
    }

    /**
     * jsapi支付
     * @param $openid
     * @return mixed
     */
    public function jsapi($data)
    {
        $this->payMode = 'weixin';
        $this->totalFee = $data['total_fee'];
        $this->outTradeNo = $data['out_trade_no'];
        $this->body = $data['body'] ?? '订单号：' . $this->outTradeNo;
        $this->openid = $data['openid'];
        $jsapiParameters = $this->getJsApiParameters();
        $commonConfigs = array(
            'subject' => $this->body,               // 订单标题
            'out_trade_no' => $this->outTradeNo,       // 订单号
            'total_fee' => $this->totalFee,             // 金额,单位:元
            'jsapi' => $jsapiParameters['jsapi'],
            'outer_tid' => $jsapiParameters['payjs_order_id'],
        );
        return $commonConfigs;
    }

    public function cashier($data)
    {
        $this->totalFee = $data['total_fee'];
        $this->outTradeNo = $data['out_trade_no'];
        $this->body = $data['body'] ?? '订单号：' . $this->outTradeNo;

        $data = array(
            'mchid' => $this->mchid,               // 商户号
            'total_fee' => $this->totalFee * 100,             // 金额,单位:分
            'out_trade_no' => $this->outTradeNo,       // 订单号
            'body' => $this->body ?: '订单号：' . $this->outTradeNo,               // 订单标题
            'notify_url' => $this->notifyUrl,             // 接收支付异步通知的回调地址
            'callback_url' => '',       // 用户支付成功后，前端跳转地址。留空则支付后关闭webview
            'logo'=>'http://v53.dededemo.com/plus/img/pay-logo.png' //收银台显示的logo图片url
        );
        $data['sign'] = $this->sign($data);
        $this->url = $this->url . '/cashier';
        return $this->url . '?' . http_build_query($data);
    }

    /**
     * 获取jsapi参数
     * @return mixed
     */
    private function getJsApiParameters()
    {
        $data['mchid'] = $this->mchid;
        $data['total_fee'] = $this->totalFee * 100;
        $data['out_trade_no'] = $this->outTradeNo;
        $data['body'] = $this->body;
        $data['notify_url'] = $this->notifyUrl;
        $data['openid'] = $this->openid;
        $data['sign'] = $this->sign($data);
        $result = $this->curlPost($this->url . '/jsapi', $data);
        $result = json_decode($result, true);
        if ($result['return_code'] != 1) {
            exit($result['return_code'] . '：' . $result['return_msg']);
        }
        return $result;
    }

    /**
     * 建立请求，以表单HTML形式构造（默认）
     */
    private function buildRequestForm($para_temp)
    {
        $action = $this->postUrl;
        $sHtml = "<form id='alipaysubmit' name='alipaysubmit' action='{$action}' method='POST'>";
        while (list ($key, $val) = each($para_temp)) {
            $val = str_replace("'", "&apos;", $val);
            $sHtml .= "<input type='hidden' name='" . $key . "' value='" . $val . "'/>";
        }
        //submit按钮控件请不要含有name属性
        $sHtml = $sHtml . "<input type='submit' value='ok' style='display:none;''></form>";
        $sHtml = $sHtml . "<script>document.forms['alipaysubmit'].submit();</script>";
        return $sHtml;
    }

    /**
     * 退款
     * @param $orderid
     * @return mixed
     */
    public function refund($orderid)
    {
        $data = array(
            'payjs_order_id' => $orderid,
        );
        $data['sign'] = $this->sign($data);
        return $this->curlPost($this->url . '/refund', $data);
    }

    /**
     * 订单状态查询
     * @param $orderid
     * @return mixed
     */
    public function orderquery($orderid)
    {
        $data = [
            "payjs_order_id" => $orderid,
        ];
        $data['sign'] = $this->sign($data);
        return $this->curlPost($this->url . '/check', $data);
    }

    /**
     * 获取openid
     * @return mixed
     */
    public function getOpenid()
    {
        if (!isset($_GET['openid'])) {
            //触发微信返回code码
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
            $uri = $_SERVER['PHP_SELF'] . $_SERVER['QUERY_STRING'];
            if ($_SERVER['REQUEST_URI']) $uri = $_SERVER['REQUEST_URI'];
            $baseUrl = urlencode($scheme . $_SERVER['HTTP_HOST'] . $uri);
            $url = $this->__CreateOauthUrlForCode($baseUrl);
            Header("Location: $url");
            exit();
        } else {
            return $_GET['openid'];
        }
    }

    public function __CreateOauthUrlForCode($url)
    {
        return $this->url . '/openid?mchid=' . $this->mchid . '&callback_url=' . $url;
    }

    private function sign(array $attributes)
    {
        ksort($attributes);
        $sign = strtoupper(md5(urldecode(http_build_query($attributes)) . '&key=' . $this->key));
        return $sign;
    }

    private function curlPost($url = '', $postData = '', $options = array())
    {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * 检查签名
     * @param $data
     * @return bool
     */
    public function check($data)
    {
        $_sign = $data['sign'];
        unset($data['sign']);
        $sign = $this->sign($data);
        return $sign == $_sign;
    }

}

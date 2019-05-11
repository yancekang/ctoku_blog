<?php
/**
 * 自动生成缩略名
 *
 * @package BaiduSlug
 * @author Chuck
 * @version 1.0
 */
class BaiduSlug_Action extends Typecho_Widget implements Widget_Interface_Do
{
    /**
     * 插件配置
     *
     * @access private
     * @var Typecho_Config
     */
    private $_config;

    /**
     * 构造方法
     *
     * @access public
     * @var void
     */
    public function __construct($request, $response, $params = NULL)
    {
        parent::__construct($request, $response, $params);
        /* 获取插件配置 */
        $this->_config = parent::widget('Widget_Options')->plugin('BaiduSlug');
    }

    /**
     * 转换为英文或拼音
     *
     * @access public
     * @return void
     */
    public function transform()
    {
        $word = $this->request->filter('strip_tags', 'trim', 'xss')->q;

        if (empty($word)) {
            return;
        }

        $result = call_user_func(array($this, $this->_config->mode), $word);
        $result = preg_replace('/[[:punct:]]/', '', $result);
        $result = str_replace(array('  ', ' '), '-', trim($result));
        $message = array('result' => $result);

        $this->response->throwJson($message);
    }

    //百度加密
    public function buildSign($query, $appID, $salt, $secKey)
    {
        $str = $appID . $query . $salt . $secKey;
        $ret = md5($str);
        return $ret;
    }
    /**
     * 百度翻译
     *
     * @access public
     * @param string $word 待翻译的字符串
     * @return string
     */
    public function baidu($word)
    {
        $data = array('appid' => $this->_config->bdappid, 'q' => $word, 'from' => 'zh', 'to' => 'en', 'salt' => rand(10000,99999));
        $data['sign'] = $this->buildSign($word, $this->_config->bdappid, $data['salt'], $this->_config->bdkey);
        $data = http_build_query($data);
        $url = 'http://api.fanyi.baidu.com/api/trans/vip/translate' . '?' . $data;
        $result = $this->translate($url);
//        var_dump($result);
        if (isset($result['error_code'])) {
            return;
        }

        return $result['trans_result'][0]['dst'];
    }

    /**
     * 发送API请求
     *
     * @access public
     * @param string $url 请求地址
     * @return array
     */
    public function translate($url)
    {
        $client = Typecho_Http_Client::get();
        $client->setTimeout(50)->send($url);

        if (200 === $client->getResponseStatus()) {
            return Json::decode($client->getResponseBody(), true);
        }
    }

    /**
     * 转换成拼音
     *
     * @access public
     * @param string $word 待转换的字符串
     * @return string
     */
    public function pinyin($word)
    {
        require_once 'Pinyin.php';

        $pinyin = new Pinyin();
        return $pinyin->stringToPinyin($word);
    }

    public function random_number($word)
    {
        $str = null;
        $num = $this->_config->length;// 字符串长度
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";//如果不需要小写字母，可以把小写字母都删除
        $max = strlen($strPol)-1;
        for($i=0;$i<$num;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }
    /**
     * 绑定动作
     *
     * @access public
     * @return void
     */
    public function action()
    {
        $this->on($this->request->isAjax())->transform();
    }
}

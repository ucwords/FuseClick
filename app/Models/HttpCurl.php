<?php
/**
 * Created by PhpStorm.
 * User: kontem
 * Date: 2018/1/1
 * Time: 00:21
 */

namespace App\Models;

class HttpCurl
{
    public $ch = null; // curl handle
    private $headers = array();// request header
    private $proxy = null; // http proxy
    private $timeout = 5;    // connnect timeout
    private $httpParams = null;
    public $error_info;


    public function __construct()
    {
        $this->ch = curl_init();
    }

    public function setHeader($header)
    {
        if (is_array($header)) {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
        } else {
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
        }
        // 'Authorization: Basic bGVhbm1vYmk6YjkwN2ViNTczODllNDI5Zjg1Njg2OTc2ZGNjMTQzNzQ='
        return $this;
    }

    public function setCookie($cookie)
    {
        if ($cookie) {
            curl_setopt($this->ch, CURLOPT_COOKIE, $cookie);
        }

        return $this;
    }

    public function setTimeout($time)
    {
        // 不能小于等于0
        if ($time <= 0) {
            $time = 5;
        }
        //只需要设置一个秒的数量就可以
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $time);

        return $this;
    }

    public function setProxy($proxy)
    {
        if ($proxy) {
            curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
        }

        return $this;
    }

    public function setProxyPort($port)
    {
        if (is_int($port)) {
            curl_setopt($this->ch, CURLOPT_PROXYPORT, $port);
        }

        return $this;
    }

    public function setReferer($referer = "")
    {
        if (!empty($referer))
            curl_setopt($this->ch, CURLOPT_REFERER, $referer);

        return $this;
    }

    public function setUserAgent($agent = "")
    {
        if ($agent) {
            // 模拟用户使用的浏览器
            curl_setopt($this->ch, CURLOPT_USERAGENT, $agent);
            curl_setopt($this->ch, CURLOPT_COOKIE, 1);
        }

        return $this;
    }

    public function showResponseHeader($show)
    {
        curl_setopt($this->ch, CURLOPT_HEADER, $show);

        return $this;
    }

    public function setParams($params)
    {
        $this->httpParams = $params;

        return $this;
    }

    public function setCainfo($file)
    {
        curl_setopt($this->ch, CURLOPT_CAINFO, $file);
    }

    public function get($url, $dataType = 'text')
    {
        if (stripos($url, 'https://') !== false) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->ch, CURLOPT_SSLVERSION, 1);
        }
        // 设置get参数  将数组中元素转化为urlEncode后的参数
        if (!empty($this->httpParams) && is_array($this->httpParams)) {
            if (strpos($url, '?') !== false) {
                $url .= "&" . http_build_query($this->httpParams);
            } else {
                $url .= '?' . http_build_query($this->httpParams);
            }
        }
        // end 设置get参数

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($this->ch);
        $status = curl_getinfo($this->ch);
        $this->error_info = [
            'error_no'   => curl_errno($this->ch),
            'error_info' => curl_getinfo($this->ch),
            'error_msg'  => curl_error($this->ch),
            'result'     => $content
        ];
        curl_close($this->ch);
        if (isset($status[ 'http_code' ]) && $status[ 'http_code' ] == 200) {
            if ($dataType == 'json') {
                $content = json_decode($content, true);
            }

            return $content;
        } else {
            return false;
        }
    }


    /**
     * 模拟POST请求
     *
     * @param string $url
     * @param array  $fields
     * @param string $dataType
     * @return mixed
     *
     * HttpCurl::post('http://api.example.com/?a=123', array('abc'=>'123', 'efg'=>'567'), 'json');
     * HttpCurl::post('http://api.example.com/', '这是post原始内容', 'json');
     * 文件post上传
     * HttpCurl::post('http://api.example.com/', array('abc'=>'123', 'file1'=>'@/data/1.jpg'), 'json');
     */
    public function post($url, $dataType = 'text')
    {
        if (stripos($url, 'https://') !== false) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->ch, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($this->ch, CURLOPT_URL, $url);
        // 设置post body
        if (!empty($this->httpParams)) {
            if (is_array($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->httpParams));
            } else if (is_string($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->httpParams);
            }
        }
        // end 设置post body
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_POST, true);
        $content = curl_exec($this->ch);
        $status = curl_getinfo($this->ch);
        $this->error_info = [
            'error_no'   => curl_errno($this->ch),
            'error_info' => curl_getinfo($this->ch),
            'result'     => $content
        ];
        curl_close($this->ch);
        if (isset($status[ 'http_code' ]) && $status[ 'http_code' ] == 200) {
            if ($dataType == 'json') {
                $content = json_decode($content, true);
            }

            return $content;
        } else {
            return false;
        }
    }

    public function patch($url, $dataType = 'text')
    {
        if (stripos($url, 'https://') !== false) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->ch, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($this->ch, CURLOPT_URL, $url);
        // 设置post body
        if (!empty($this->httpParams)) {
            if (is_array($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->httpParams));
            } else if (is_string($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->httpParams);
            }
        }
        // end 设置post body
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        $content = curl_exec($this->ch);
        $status = curl_getinfo($this->ch);
        $this->error_info = [
            'error_no' => curl_errno($this->ch),
            'error_info' => curl_getinfo($this->ch),
            'result' => $content
        ];
        curl_close($this->ch);
        if (isset($status[ 'http_code' ]) && $status[ 'http_code' ] == 200) {
            if ($dataType == 'json') {
                $content = json_decode($content, true);
            }

            return $content;
        } else {
            return false;
        }
    }
    
    public function put($url, $dataType = 'text')
    {
        if (stripos($url, 'https://') !== false) {
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->ch, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($this->ch, CURLOPT_URL, $url);
        // 设置post body
        if (!empty($this->httpParams)) {
            if (is_array($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->httpParams));
            } else if (is_string($this->httpParams)) {
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $this->httpParams);
            }
        }
        // end 设置post body
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($this->ch, CURLOPT_POST, true);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");
        $content = curl_exec($this->ch);
        $status = curl_getinfo($this->ch);
        $this->error_info = [
            'error_no'   => curl_errno($this->ch),
            'error_info' => curl_getinfo($this->ch),
            'result'     => $content
        ];
        curl_close($this->ch);
        if (isset($status[ 'http_code' ]) && $status[ 'http_code' ] == 200) {
            if ($dataType == 'json') {
                $content = json_decode($content, true);
            }

            return $content;
        } else {
            return false;
        }
    }
}

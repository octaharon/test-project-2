<?php

namespace Providers\Curl;

use Models\Application;

abstract class AbstractCurl implements CurlInterface {

    protected $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11';
    protected $curlTimeout = 30;
    protected $referrer = '';
    private $headersAutoUpdate = true;
    protected $cookies = [];
    protected $secureMode = false;
    protected $useProxy = false;
    protected $proxyRetryCount = 5;
    protected $proxySOCKS5 = '80.76.190.188:1080';
    protected $app;
    protected $httpHeaders = [];
    protected $userPwd = false;

    /**
     * для прокси с авторизацией
     */
    protected $proxyUser = "";
    protected  $proxyPassword="";
    /**
     * CURLPROXY_SOCKS5  или CURLPROXY_HTTP
     */
    protected $proxyType = CURLPROXY_HTTP;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    public function setSecureMode($value)
    {
        $this->secureMode = $value;
    }

    public function setUserAgent($string)
    {
        $this->userAgent = $string;
    }

    public function getCookies()
    {
        return $this->cookies;
    }

    public function setCookies(array $value)
    {
        $this->cookies = $value;
    }

    public function setProxy($useProxy, $proxyRetryCount = 5, $proxySOCKS5 = null)
    {
        $this->useProxy = $useProxy;
        $this->proxyRetryCount = $proxyRetryCount;
        if ($proxySOCKS5 !== null) {
            $this->proxySOCKS5 = $proxySOCKS5;
        }
    }

    public function setTimeout($value)
    {
        $this->curlTimeout = $value;
    }

    public function getReferrer()
    {
        return $this->referrer;
    }

    public function setReferrer($string)
    {
        $this->referrer = $string;
    }

    public function setHeadersAutoUpdate($value)
    {
        $this->headersAutoUpdate = $value;
    }

    public function setProxyUser($userName)
    {
        $this->proxyUser = $userName;
    }

    public function setProxyPassword($password)
    {
        $this->proxyPassword = $password;
    }

    public function setProxyType($type)
    {
        $this->proxyType = $type;
    }

    public function setHttpHeaders(array $value) {
        $this->httpHeaders = $value;
    }

    public function setUserPwd($userPwd) {
        $this->userPwd = $userPwd;
    }

    private function executeAndUpdateHeaders($url, $fields, $cookies, $isPost) {

        if ($cookies === null) {
            $cookies = $this->cookies;
        }

        $result = $this->execute($url, $fields, $cookies, $isPost);
        if ($this->headersAutoUpdate) {
            $this->setReferrer($url);
            $this->cookies = $result->getCookies();
        }
        return $result;
    }

    /**
     * @param $url
     * @param array $fields
     * @param array $cookies
     * @param $isPost
     *
     * @return CurlResult
     */
    abstract protected function execute($url, $fields, array $cookies, $isPost);

    abstract public function close();

    public function post($url, $fields = [], $cookies = null) {
        return $this->executeAndUpdateHeaders($url, $fields, $cookies, true);
    }

    public function get($url, array $fields = [], $cookies = null) {
        return $this->executeAndUpdateHeaders($url, $fields, $cookies, false);
    }
} 
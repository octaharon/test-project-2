<?php
/**
 * Created by PhpStorm.
 * User: atckiy
 * Date: 13.08.15
 * Time: 10:16
 */

namespace Providers\Curl;


interface CurlInterface {

    public function setSecureMode($value);

    public function setUserAgent($value);

    public function setCookies(array $value);

    /**
     * @return array
     */
    public function getCookies();

    public function setProxy($useProxy, $proxyRetryCount = 5, $proxySOCKS5 = null);

    public function setProxyUser($userProxy);

    public function setProxyPassword($password);

    public function setProxyType($type);

    public function setTimeout($value);

    public function setReferrer($string);

    public function getReferrer();

    public function setHeadersAutoUpdate($value);

    public function setHttpHeaders(array $value);
    
    public function setUserPwd($userPwd);

    public function close();

    public function getInfo();

    /**
     * @param $url
     * @param array $fields
     * @param null $cookies
     *
     * @return CurlResult
     */
    public function post($url, $fields = [], $cookies = null);

    /**
     * @param $url
     * @param array $fields
     * @param null $cookies
     *
     * @return CurlResult
     */
    public function get($url, array $fields = [], $cookies = null);

} 
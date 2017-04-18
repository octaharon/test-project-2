<?php

namespace Providers\Curl;

class Curl extends AbstractCurl {

    private $curl;

    private $curlInfo;

    public function getInfo() {
        return $this->curlInfo;
    }

    public function __destruct() {
        $this->close();
    }

    public function getLastInfo()
    {
        if (!$this->curl)
            return false;
        $errorData=curl_getinfo($this->curl);
        return array(
            'url'  => $errorData['url'],
            'code' => $errorData['http_code'],
            'error' => curl_error($this->getCurl())
        );
    }

    protected function execute($url, $fields, array $cookies, $isPost)
    {
        if (!$isPost && is_array($fields) && sizeof($fields) > 0) {
            $url = $url.'?'.http_build_query($fields);
        }

        $url=trim($url);

        if (!strlen($url))
            trigger_error("Empty url passed to curl", E_USER_ERROR);

        curl_setopt($this->getCurl(), CURLOPT_URL, $url);

        if ($this->getReferrer()) {
            curl_setopt($this->getCurl(), CURLOPT_REFERER, $this->getReferrer());
        }

        curl_setopt($this->getCurl(), CURLOPT_HEADER, true);
        curl_setopt($this->getCurl(), CURLOPT_RETURNTRANSFER, true);

        if (sizeof($cookies) > 0) {
            $cookieStrings = [];
            foreach ($cookies as $key => $value) {
                $cookieStrings[] = $key.'='.$value;
            }
            curl_setopt($this->getCurl(), CURLOPT_COOKIE, implode('; ', $cookieStrings));
        }

        curl_setopt($this->getCurl(), CURLOPT_CONNECTTIMEOUT, $this->curlTimeout);

        $version = curl_version();

        if ($version['features'] && constant('CURLOPT_IPRESOLVE')) { // if this feature exists (since curl 7.10.8)
            curl_setopt($this->getCurl(), CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        }

        curl_setopt($this->getCurl(), CURLOPT_USERAGENT, $this->userAgent);

        if ($this->secureMode) {
            curl_setopt($this->getCurl(), CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($this->getCurl(), CURLOPT_SSL_VERIFYPEER, 0);
        }

        if ($this->useProxy) {
            /*
                            Для тех кто использует CURL

                Простое задание курл-опции для работы соединения через прокси:
                curl_setopt($ch, CURLOPT_PROXY, ‘XXX.XXX.XXX.XXX’);
                Задание курл-опций для работы через SOCKS4:
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
                curl_setopt($ch, CURLOPT_PROXY, ‘XXX.XXX.XXX.XXX’);
                Задание курл-опций для работы через SOCKS5:
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                curl_setopt($ch, CURLOPT_PROXY, ‘XXX.XXX.XXX.XXX’);
                Задание курл-опции для работы через прокси с авторизацией, 1 вариант:
                curl_setopt($ch, CURLOPT_PROXY, ‘loginassword@XXX.XXX.XXX.XXX’);
                Задание курл-опций для работы через прокси с авторизацией, 2 вариант:
                curl_setopt ($ch, CURLOPT_PROXYUSERPWD, ‘loginassword’);
                curl_setopt($ch, CURLOPT_PROXY, ‘XXX.XXX.XXX.XXX’);
                        curl->userProxy
                        curl->setProxyUser
                        curl->setProxyPassword
                         чтобы короче работало и с паролем и без
            */

            if($this->proxyType === CURLPROXY_SOCKS5) {

                curl_setopt($this->getCurl(), CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);

            } elseif ($this->proxyType === CURLPROXY_HTTP) {

                curl_setopt($this->getCurl(), CURLOPT_PROXYTYPE, CURLPROXY_HTTP);

            }

            if ($this->proxyUser !== "") {
                $login_pass = $this->proxyUser . ":" . $this->proxyPassword;
                curl_setopt($this->getCurl(), CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
                curl_setopt($this->getCurl(), CURLOPT_PROXYUSERPWD, $login_pass);
            }

            curl_setopt($this->getCurl(), CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($this->getCurl(), CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->getCurl(), CURLOPT_SSLVERSION, 3);
            curl_setopt($this->getCurl(), CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->getCurl(), CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($this->getCurl(), CURLOPT_PROXY, $this->proxySOCKS5);
        }

        if ($isPost) {
            if (is_array($fields)) {
                if (sizeof($fields) > 0) {
                    curl_setopt($this->getCurl(), CURLOPT_POSTFIELDS, http_build_query($fields));
                }
            } else {
                curl_setopt($this->getCurl(), CURLOPT_POSTFIELDS, $fields);
            }
        }

        if (sizeof($this->httpHeaders) > 0) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $this->httpHeaders);
        }

        if ($this->userPwd) {
            curl_setopt($this->curl, CURLOPT_USERPWD, $this->userPwd);
        }

        $data = curl_exec($this->getCurl());
        $this->curlInfo=$this->getLastInfo();

        $headers = mb_substr($data, 0, curl_getinfo($this->getCurl(), CURLINFO_HEADER_SIZE));
        $body  = substr($data, curl_getinfo($this->getCurl(), CURLINFO_HEADER_SIZE));

        $result = new CurlResult($body, $headers);

        return $result;
    }

    public function close() {
        if($this->curl !== null) {
            curl_close($this->curl);
            $this->curl = null;
        }
    }

    private function getCurl() {
        if($this->curl === null) {
            $this->curl = curl_init();
        }
        return $this->curl;
    }
}
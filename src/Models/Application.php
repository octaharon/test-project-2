<?php

namespace Models;

use Providers\Curl\CurlInterface;
use Providers\File\FileProvider;
use Providers\Mysql\PDOProvider;
use Providers\ObjectCache\ObjectCacheProvider;
use Providers\Time\TimeProvider;
use Providers\Logger\LoggerProvider;
use Symfony\Component\HttpFoundation\Session\Session;
use Providers\Config\ConfigProvider;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\Request;

class Application extends \Silex\Application {

    /**
     * @return \Twig_Environment
     */
    public function getTwig() {
        return $this['twig'];
    }

    /**
     * @return Session
     */
    public function getSessionProvider() {
        return $this['session'];
    }

    /**
     * @return ConfigProvider
     */
    public function getConfigProvider() {
        return $this['configProvider'];
    }


    /**
     * @return UrlGenerator
     */
    public function getUrlGenerator() {
        return $this['url_generator'];
    }

    /**
     * @return CurlInterface
     */
    public function getCurl() {
        return $this['curl'];
    }

    /**
     * @return Request
     */
    public function getRequest() {
        return $this['request'];
    }


    /**
     * @return FileProvider
     */
    public function getFileManager() {
        return $this['fileManager'];
    }

    /**
     * @return TimeProvider
     */
    public function getTime() {
        return $this['time'];
    }

    /**
     * @return ObjectCacheProvider
     */

    public function getObjectCache() {
        return $this['objectCache'];
    }

    /**
     * @return LoggerProvider
     */
    public function getLoggerProvider() {
        return $this['logger'];
    }


    /**
     * @return PDOProvider
     */

    public function getMysql() {
        return $this['mysql'];
    }
}
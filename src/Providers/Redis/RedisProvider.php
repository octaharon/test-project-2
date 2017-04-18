<?php

namespace Providers\Redis;

use Silex\Application;
use Silex\ServiceProviderInterface;

class RedisProvider implements ServiceProviderInterface {
    private $redis = null;

    /**
     * Injection.
     *
     * @var Application
     */
    private $app;
    private $binary = false;

    public function get($key, $hash = null) {
        if ($hash === null) {
            $value = $this->getConnection()->get($key);
        } else {
            $value = $this->getConnection()->hget($key, $hash);
        }

        return $this->serializedOrSelf($value);
    }

    public function pack($data) {
        return $this->binary ? igbinary_serialize($data) : serialize($data);
    }

    public function unpack($string) {
        return $this->binary ? igbinary_unserialize($string) : unserialize($string);
    }


    public function set($key, $hashOrValue, $value = null) {
        if ($value === null) {
            $this->getConnection()->set($key, $this->prepareValue($hashOrValue));
        } else {
            $this->getConnection()->hset($key, $hashOrValue, $this->prepareValue($value));
        }
    }

    private function getAllSerialized($key) {
        $data = $this->getConnection()->hgetall($key);
        if (!is_array($data)) {
            $data = [];
        }
        return $data;
    }

    public function getAllKeys($key) {
        return array_keys($this->getAllSerialized($key));
    }

    public function getAll($key) {
        return array_map([$this, 'serializedOrSelf'], $this->getAllSerialized($key));
    }

    public function delete($key, $hash = null) {
        if ($hash === null) {
            $this->getConnection()->del($key);
        } else {
            $this->getConnection()->hdel($key, $hash);
        }
    }

    private function prepareValue($value) {
        if ($this->binary || is_array($value) || is_object($value)) {
            $value = $this->pack($value);
        }
        return $value;
    }

    private function isSerialized($data, $strict = true) {
        if ($this->binary)
            return false;
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace)
                return false;
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3)
                return false;
            if (false !== $brace && $brace < 4)
                return false;
        }
        $token = $data[0];
        switch ($token) {
            case 's' :
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
            break;
            case 'a' :
            case 'O' :
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b' :
            case 'i' :
            case 'd' :
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }
        return false;

    }

    private function serializedOrSelf($string) {
        $result = $string;
        if ($this->binary || $this->isSerialized($string)) {
            $result = $this->unpack($string);
        }
        return $result;
    }

    private function getConnection() {
        if ($this->redis === null) {
            $this->redis = new \Redis();
            $this->redis->connect($this->app['config']['redis']['host'], $this->app['config']['redis']['port']);
        }
        return $this->redis;
    }

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app) {
        $app['redis'] = $this;
        if (function_exists('igbinary_serialize')) {
            $this->binary = true;
        }
        $this->app = $app;
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app) {
        // TODO: Implement boot() method.
    }

    public function getLineVersion() {
        return [
            'prematch' => $this->get('prematchLineVersion_' . $this->app->getLocalizationProvider()->getLocale()),
            'live' => $this->get('liveLineVersion_' . $this->app->getLocalizationProvider()->getLocale())
        ];
    }
}

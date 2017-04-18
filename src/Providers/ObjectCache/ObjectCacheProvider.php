<?php

namespace Providers\ObjectCache;

use ORM\DB\TokenWrapper;
use ORM\DB\UserWrapper;
use ORM\DB\RunsWrapper;
use Silex\Application;
use Silex\ServiceProviderInterface;

class ObjectCacheProvider implements ServiceProviderInterface {
    const CONTEXT_QUERY = 'query';

    private $cache = [];
    private $context = [];
    private $app;

    public function setQueryResult($key, $data) {
        $this->set($key, $data, self::CONTEXT_QUERY);
    }

    public function get($key, $throwException = false) {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }
        if ($throwException) {
            throw new \Exception('Key ' . $key . ' is not found in Object Cache');
        }

        return null;
    }

    public function remove($key) {
        if (array_key_exists($key, $this->cache)) {
            unset($this->cache[$key]);
        }
    }

    public function set($key, $object, $context = null) {
        $this->cache[$key] = $object;
        if ($context !== null) {
            if (!array_key_exists($context, $this->context)) {
                $this->context[$context] = [];
            }
            $this->context[$context][] = $key;
        }
    }

    public function clearContext($context) {
        if (array_key_exists($context, $this->context)) {

            foreach ($this->context[$context] as $key => $value) {
                unset($this->cache[$value]);
                unset($this->context[$context][$key]);
            }
        }
    }

    private function getNewWrapper($class) {
        $reflection = new \ReflectionClass($class);

        return $reflection->newInstance($this->app);
    }

    public function getWrapper($class, $new = false) {
        if ($new) {
            return $this->getNewWrapper($class);
        }

        $object = $this->get($class);
        if ($object === null) {
            $object = $this->getNewWrapper($class);
            $this->set($class, $object);

        }

        return $object;
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
        $app['objectCache'] = $this;
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

    }

    /***
     * @return UserWrapper
     */
    public function getUserWrapper() {
        return $this->getWrapper('\\ORM\\DB\\UserWrapper');
    }

    /***
     * @return TokenWrapper
     */
    public function getTokenWrapper() {
        return $this->getWrapper('\\ORM\\DB\\TokenWrapper');
    }

    /***
     * @return RunsWrapper
     */
    public function getRunsWrapper() {
        return $this->getWrapper('\\ORM\\DB\\RunsWrapper');
    }


}
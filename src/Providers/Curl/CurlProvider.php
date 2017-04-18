<?php

namespace Providers\Curl;

use Silex\Application;
use Silex\ServiceProviderInterface;

class CurlProvider implements ServiceProviderInterface {

    private $app;

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app) {
        $app['curl'] = function() {
            return $this->factoryCurl();
        };
        $this->app = $app;
    }

    /**
     * @return CurlInterface
     */
    private function factoryCurl() {
        $curl = new Curl($this->app);
        return $curl;
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
	
}
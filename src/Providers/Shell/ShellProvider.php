<?php

namespace Providers\Shell;

use Silex\Application;
use Silex\ServiceProviderInterface;

class ShellProvider implements ServiceProviderInterface {

    private $app;

    public function execute($command, array $parameters = []) {
        $output = [];
        $returnStatus = 0;

        foreach ($parameters as $key => $value) {

            $argument = $value;
            if (!is_int($key)) {
                $argument = $key.' '.$value;
            }
            $command .= ' '.$this->clearArgument($argument);
        }

        exec($command, $output, $returnStatus);
        $result = new Result($returnStatus, $output);
        return $result;
    }

    private function clearArgument($argument) {
        return escapeshellarg($argument);
    }

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['shell'] = $this;
        $this->app = $app;
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}
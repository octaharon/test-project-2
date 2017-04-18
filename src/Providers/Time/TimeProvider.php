<?php

namespace Providers\Time;

use Silex\Application;
use Silex\ServiceProviderInterface;

class TimeProvider implements ServiceProviderInterface {

    const DATETIME_FORMAT = 'Y-m-d H:i:s';
    const DATETIME_ZERO_SECONDS_FORMAT = 'Y-m-d H:i:00';

    private $time;

    private $app;

    public function now() {
        if (!($this->time instanceof \DateTime)) {

            $this->time = new \DateTime("now", new \DateTimeZone(date_default_timezone_get()));

            if (array_key_exists('currentTime', $this->app['config'])) {

                $year = (int)$this->time->format('Y');
                if (array_key_exists('year', $this->app['config']['currentTime'])) {
                    $year = (int)$this->app['config']['currentTime']['year'];
                }
                $month = (int)$this->time->format('m');
                if (array_key_exists('month', $this->app['config']['currentTime'])) {
                    $month = (int)$this->app['config']['currentTime']['month'];
                }
                $day = (int)$this->time->format('d');
                if (array_key_exists('day', $this->app['config']['currentTime'])) {
                    $day = (int)$this->app['config']['currentTime']['day'];
                }
                $hour = (int)$this->time->format('H');
                if (array_key_exists('hour', $this->app['config']['currentTime'])) {
                    $hour = (int)$this->app['config']['currentTime']['hour'];
                }
                $minute = (int)$this->time->format('i');
                if (array_key_exists('minute', $this->app['config']['currentTime'])) {
                    $minute = (int)$this->app['config']['currentTime']['minute'];
                }
                $second = (int)$this->time->format('s');
                if (array_key_exists('second', $this->app['config']['currentTime'])) {
                    $second = (int)$this->app['config']['currentTime']['second'];
                }

                $this->time->setDate($year, $month, $day);
                $this->time->setTime($hour, $minute, $second);
            }
        }

        return clone $this->time;
    }

    public function setTime($time = null) {
        if ($time instanceof \DateTime) {
            $this->time = clone $time;
        } else {
            $this->time = null;
        }
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
        $app['time'] = $this;
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
}
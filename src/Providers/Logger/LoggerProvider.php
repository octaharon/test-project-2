<?php

namespace Providers\Logger;

use Silex\Application;
use Silex\ServiceProviderInterface;

class LoggerProvider implements ServiceProviderInterface, \Psr\Log\LoggerInterface {

    const LEVEL_DEBUG = 'debug';
    const LEVEL_INFO = 'info';
    const LEVEL_NOTICE = 'notice';
    const LEVEL_WARNING = 'warning';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';
    const LEVEL_ALERT = 'alert';
    const LEVEL_EMERGENCY = 'emergency';

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $defaultType = 'silex';

    private $refreshCallback;

    public function setDefaultType($type) {
        $this->defaultType = $type;
    }

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
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
        $app['logger'] = $this;
        $loggerClass = $app['config']['logger']['class'];
        $this->logger = new $loggerClass($app);
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

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function emergency($message, array $context = array())
    {
        $this->log(self::LEVEL_EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($message, array $context = array())
    {
        $this->log(self::LEVEL_ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($message, array $context = array())
    {
        $this->log(self::LEVEL_CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($message, array $context = array())
    {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($message, array $context = array())
    {
        if($this->app['config']['debug']) {
            $this->log(self::LEVEL_WARNING, $message, $context);
        }
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($message, array $context = array())
    {
        if($this->app['config']['debug']) {
            $this->log(self::LEVEL_NOTICE, $message, $context);
        }
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($message, array $context = array())
    {
        if($this->app['config']['debug']) {
            $this->log(self::LEVEL_INFO, $message, $context);
        }
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($message, array $context = array())
    {
        if($this->app['config']['debug']) {
            $this->log(self::LEVEL_DEBUG, $message, $context);
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return null
     */
    public function log($level, $message, array $context = array())
    {
        $type = $this->defaultType;

        $exception = null;
        if (array_key_exists('exception', $context) && $context['exception'] instanceof \Exception) {
            $exception = $context['exception'];
            unset($context['exception']);
            $type .= '.exception';
        }

        if (array_key_exists('type', $context)) {
            $type = $context['type'];
            unset($context['type']);
        }

        $time = new \DateTime();
        if (array_key_exists('time', $context) && $context['time'] instanceof \DateTime) {
            $time = $context['time'];
            unset($context['time']);
        }

        $this->logger->log($time, $level, $message, $type, $exception, $context);
    }

    public function setRefreshCallback(callable $callback) {
        $this->refreshCallback = $callback;
    }

    public function refresh() {
        if (is_callable($this->refreshCallback)) {
            call_user_func($this->refreshCallback);
        }
    }
}
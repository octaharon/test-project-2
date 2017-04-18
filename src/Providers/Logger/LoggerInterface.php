<?php

namespace Providers\Logger;

use Silex\Application;

interface LoggerInterface {

    public function __construct(Application $app);

    public function log($time, $level, $message, $type, $exception, $context);
}
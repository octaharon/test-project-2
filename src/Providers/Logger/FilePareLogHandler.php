<?php

namespace Providers\Logger;

use Silex\Application;

class FilePareLogHandler extends FileLogHandler {

    public function log($time, $level, $message, $type, $exception, $context)
    {
        $errorLogLevels = [
            LoggerProvider::LEVEL_CRITICAL,
            LoggerProvider::LEVEL_WARNING,
            LoggerProvider::LEVEL_ERROR,
            LoggerProvider::LEVEL_ALERT,
            LoggerProvider::LEVEL_EMERGENCY
        ];

        if (array_search($level, $errorLogLevels) === false) {
            echo $this->getLogMessage($time, $level, $message, $type, $exception, $context)."\n";
        } else {
            file_put_contents('php://stderr', $this->getLogMessage($time, $level, $message, $type, $exception, $context)."\n", FILE_APPEND);
        }
    }
}
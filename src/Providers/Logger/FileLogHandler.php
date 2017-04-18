<?php

namespace Providers\Logger;

use Silex\Application;

class FileLogHandler implements LoggerInterface {

    private $app;

    public function __construct(Application $app) {
        $this->app = $app;
    }

    protected function getLogMessage(\DateTime $time, $level, $message, $type, $exception, array $context) {

        $text = '[' . $time->format('Y-m-d H:i:s') . "]/$type/$level: $message";
        if ($exception instanceof \Exception) {
            $text .= "\n Exception data:";
            $text .= "\n class: " . get_class($exception);
            $text .= "\n code: " . $exception->getCode();
            $text .= "\n message: " . $exception->getMessage();
            $text .= "\n file: " . $exception->getFile();
            $text .= "\n line: " . $exception->getLine();
            $text .= "\n" . $exception->getTraceAsString();
        }

        if (sizeof($context) > 0) {
            $text .= "\n Context:";
            foreach ($context as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                } elseif (is_object($value)) {
                    if (method_exists($value, '__toString ')) {
                        $value = strval($value);
                    } else {
                        $value = 'object ' . get_class($value);
                    }
                }
                $text .= "\n" . $key . ': ' . $value;
            }
        }

        return str_replace(array('\n', "\n"), PHP_EOL, $text);

    }

    public function log($time, $level, $message, $type, $exception, $context) {
        error_log($this->getLogMessage($time, $level, $message, $type, $exception, $context));
    }
}
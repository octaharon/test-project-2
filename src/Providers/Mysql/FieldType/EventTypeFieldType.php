<?php

namespace Providers\Mysql\FieldType;

use Models\Bet\EventType\AbstractEventType;
use Models\Bet\EventType\EventTypeFactory;
use Silex\Application;

class EventTypeFieldType extends AbstractFieldType {

    private $app;
    private $factory;

    public function __construct(Application $app) {
        $this->app = $app;
        $this->factory = new EventTypeFactory($app);
    }

    protected function convertFromDBExtension($value)
    {
        return $this->factory->get($value);
    }

    protected function convertToDBExtension($value)
    {
        if ($value instanceof AbstractEventType) {
            return $value->getName();
        }

        return (string)$value;

    }
}
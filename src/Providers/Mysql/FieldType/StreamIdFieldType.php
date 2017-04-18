<?php

namespace Providers\Mysql\FieldType;

use Models\Display\AbstractStream;
use Models\Display\StreamFactory;
use Models\Display\StreamHistory;
use Models\Display\StreamList;
use Models\Display\StreamNull;
use Models\Display\StreamQueue;
use Models\Display\StreamRunningString;
use Silex\Application;

class StreamIdFieldType extends AbstractFieldType {

    private $app;

    private $factory;

    public function __construct(Application $app) {
        $this->app = $app;
        $this->factory = new StreamFactory($app);
    }

    protected function convertFromDBExtension($value)
    {
        return $this->factory->get($value);
    }

    protected function convertToDBExtension($value)
    {
        if ($value instanceof AbstractStream) {
            return $value->getStreamId();
        }

        return null;
    }
}
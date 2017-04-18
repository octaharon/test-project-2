<?php

namespace Providers\Mysql\FieldType;


use Models\Bet\Type\AbstractBetType;
use Models\Bet\Type\BetTypeFactory;
use Silex\Application;

class BetTypeFieldType extends AbstractFieldType {

    private $app;
    private $betTypeFactory;

    public function __construct(Application $app) {
        $this->app = $app;
        $this->betTypeFactory = new BetTypeFactory($app);
    }

    protected function convertFromDBExtension($value)
    {
        return $this->betTypeFactory->get($value);
    }

    protected function convertToDBExtension($value)
    {
        if ($value instanceof AbstractBetType) {
            return $value->getName();
        }

        return (string)$value;
    }
}
<?php

namespace Providers\Mysql\FieldType;

abstract class AbstractFieldType {

    const DEFAULT_VALUE = '#%DEFAULT%#';

    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'date time';
    const TYPE_TIME = 'time';
    const TYPE_TEXT = 'text';
    const TYPE_HOURS_INTERVAL = 'hours interval';
    const TYPE_PAIR = 'pair';
    const TYPE_JSON = 'json';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATETIME_INTERVALS = 'date time intervals';

    public function convertFromDB($value) {

        return $this->convertFromDBExtension($value);
    }

    public function getPDOType($value) {
        if ($value === self::DEFAULT_VALUE) {
            return false;
        }

        if ($value === null) {
            return \PDO::PARAM_NULL;
        }

        return $this->getPDOTypeExtension();
    }

    public function convertToDB($value) {

        if ($value === self::DEFAULT_VALUE) {
            return 'DEFAULT';
        }

        if ($value === null) {
            return null;
        }

        return $this->convertToDBExtension($value);
    }

    protected abstract function convertFromDBExtension($value);

    protected abstract function convertToDBExtension($value);

    protected function getPDOTypeExtension() {
        return \PDO::PARAM_STR;
    }
}

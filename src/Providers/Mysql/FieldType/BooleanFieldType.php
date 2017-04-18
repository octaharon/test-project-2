<?php

namespace Providers\Mysql\FieldType;


class BooleanFieldType extends AbstractFieldType {

    protected function convertFromDBExtension($value)
    {
        return $value == true;
    }

    protected function convertToDBExtension($value)
    {
        return $value ? 1 : 0;
    }

    public function getPDOTypeExtension() {
        return \PDO::PARAM_INT;
    }
}
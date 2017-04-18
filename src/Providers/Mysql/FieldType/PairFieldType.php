<?php

namespace Providers\Mysql\FieldType;

class PairFieldType extends AbstractFieldType {

    protected function convertFromDBExtension($value)
    {
        if ($value == '') {
            return [null, null];
        }

        return explode('x', $value);
    }

    protected function convertToDBExtension($value)
    {
        if (!is_array($value)) {
            throw new FieldTypeException('PairFieldType value is not array');
        }

        if (sizeof($value) != 2) {
            throw new FieldTypeException('PairFieldType value not correct size');
        }

        return ((int)$value[0]).'x'.((int)$value[1]);
    }
}
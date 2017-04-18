<?php

namespace Providers\Mysql\FieldType;

class IntFieldType extends AbstractFieldType {

    protected function convertFromDBExtension($value)
    {
        if ($value == '') {
            return null;
        }

        return intval($value);
    }

    protected function convertToDBExtension($value)
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if (is_string($value)) {
            $intValue = intval($value);
            if ($intValue === 0 && $value !== '0') {
                throw new FieldTypeException('Can\'t cast string "' . $value . '" to int');
            }
            $value = $intValue;
        }
        if (is_float($value)) {
            $value = intval($value);
        }

        if (is_int($value)) {
            return $value;
        }

        throw new FieldTypeException('convertToDB parameter must be valid string or float or int');
    }

    public function getPDOTypeExtension() {
        return \PDO::PARAM_INT;
    }
}

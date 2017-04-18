<?php

namespace Providers\Mysql\FieldType;

class FloatFieldType extends AbstractFieldType {


    protected function convertFromDBExtension($value)
    {
        if ($value == '') {
            return null;
        }

        return floatval($value);
    }

    protected function convertToDBExtension($value)
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if (is_string($value)) {
            $floatValue = floatval($value);
            if ($floatValue === 0 && $value !== '0') {
                throw new FieldTypeException('Can\'t cast string "' . $value . '" to float');
            }
            $value = $floatValue;
        }

        if (is_float($value) || is_int($value)) {
            return $value;
        }

        throw new FieldTypeException('convertToDB parameter must be valid string or float or int');
    }


    public function getPDOTypeExtension() {
        return \PDO::PARAM_INT;
    }
}

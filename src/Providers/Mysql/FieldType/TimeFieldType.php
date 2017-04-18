<?php

namespace Providers\Mysql\FieldType;

class TimeFieldType extends AbstractFieldType {

    protected function convertFromDBExtension($value)
    {
        if ($value == '') {
            return null;
        }

        return \DateTime::createFromFormat('H:i:s', $value);
    }

    protected function convertToDBExtension($value)
    {
        if (is_string($value)) {
            try {
                $value = new \DateTime($value);
            } catch (\Exception $e) {
                throw new FieldTypeException('Can\'t cast string "' . $value . '" to DateTime');
            }
        }
        if ($value instanceof \DateTime) {
            return $value->format('H:i:s');
        }

        throw new FieldTypeException('convertToDB parameter must be valid string or DateTime');
    }
}
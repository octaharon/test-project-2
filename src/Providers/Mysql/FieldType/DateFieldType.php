<?php

namespace Providers\Mysql\FieldType;

class DateFieldType extends AbstractFieldType {

    protected function convertFromDBExtension($value)
    {
        if ($value == '') {
            return null;
        }

        return \DateTime::createFromFormat('Y-m-d', $value);
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
            return $value->format('Y-m-d');
        }

        throw new FieldTypeException('convertToDB parameter must be valid string or DateTime');
    }
}

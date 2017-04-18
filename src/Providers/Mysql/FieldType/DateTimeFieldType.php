<?php

namespace Providers\Mysql\FieldType;

class DateTimeFieldType extends AbstractFieldType {

    protected function convertFromDBExtension($value)
    {
        if ($value == '') {
            return null;
        }

        $result = \DateTime::createFromFormat('Y-m-d H:i:s', $value);

        if ($result === false) {
            throw new FieldTypeException('Can\'t cast string from DB "' . $value . '" to DateTime');
        }

        return $result;
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
            return $value->format('Y-m-d H:i:s');
        }

        throw new FieldTypeException('convertToDB parameter must be valid string or DateTime');
    }
}
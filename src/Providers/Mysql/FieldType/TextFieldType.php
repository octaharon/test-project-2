<?php

namespace Providers\Mysql\FieldType;

class TextFieldType extends AbstractFieldType {

    protected function convertFromDBExtension($value)
    {
        return (string)$value;
    }

    protected function convertToDBExtension($value)
    {
        return (string)$value;
    }
}

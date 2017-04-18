<?php
/**
 * Created by PhpStorm.
 * User: atckiy
 * Date: 30.09.14
 * Time: 9:52
 */

namespace Providers\Mysql\FieldType;


class JsonFieldType extends AbstractFieldType {

    protected function convertFromDBExtension($value)
    {
        if ($value == '') {
            return false;
        }

        return json_decode($value, true);
    }

    protected function convertToDBExtension($value)
    {
        return json_encode($value);
    }
}
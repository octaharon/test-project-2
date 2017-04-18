<?php

namespace Providers\Mysql\FieldType;


class HoursIntervalFieldType extends AbstractFieldType {

    protected function convertFromDBExtension($value)
    {
        if ($value == '') {
            return null;
        }

        return \DateInterval::createFromDateString($value.' hours');
    }

    protected function convertToDBExtension($value)
    {
        if (!$value instanceof \DateInterval) {
            $value = $this->convertFromDBExtension($value);
        }

        return $value->days*24 + $value->h;
    }

    public function getPDOTypeExtension() {
        return \PDO::PARAM_INT;
    }
}
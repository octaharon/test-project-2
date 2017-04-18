<?php

namespace Providers\Mysql\FieldType;

class DateTimeIntervalsFieldType extends AbstractFieldType {

    protected function convertFromDBExtension($value)
    {
        if (strlen($value) == 0) {
            return [];
        }

        $stringIntervals = explode(',', $value);
        $intervals = [];

        $pattern = '/^[0-9]{1,2}:[0-9]{2}-[0-9]{1,2}:[0-9]{2}$/';



        foreach ($stringIntervals as $stringInterval) {

            if (!preg_match($pattern, $stringInterval)) {
                continue;
            }

            $interval = [];

            $offsets = explode('-', $stringInterval);
            foreach ($offsets as $offset) {
                $timeParts = explode(':', $offset);
                $minutes = ((int)$timeParts[0])*60 + ((int)$timeParts[1]);
                $interval[] = new \DateInterval('PT'.$minutes.'M');
            }

            $intervals[] = $interval;
        }

        return $intervals;
    }

    protected function convertToDBExtension($value)
    {
        if ($value === null) {
            return '';
        }

        if (!is_array($value)) {
            throw new FieldTypeException('convertToDB parameter must be valid array of DateInterval by pares');
        }

        if (sizeof($value) == 0) {
            return '';
        }

        $result = [];

        foreach ($value as $item) {

            if (!is_array($item) || sizeof($item) != 2 || !array_key_exists(0, $item) || !array_key_exists(1, $item)) {
                throw new FieldTypeException('convertToDB parameter must be valid array of DateInterval by pares');
            }
            $result[] = $item[0]->h.':'.str_pad($item[0]->m, 2, 0, STR_PAD_LEFT)
                .'-'.$item[1]->h.':'.str_pad($item[1]->m, 2, 0, STR_PAD_LEFT);
        }

        return implode(',', $result);

    }
}
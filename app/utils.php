<?php

function array_pluck($array, $key) {
    return array_map(function ($v) use ($key) {
        if (is_object($v) && method_exists($v, 'get'))
            return $v->get($key);
        return is_object($v) ? $v->$key : $v[$key];
    }, $array);
}

?>
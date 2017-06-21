<?php

namespace WebAction\ValueType;

class UrlType extends BaseType
{
    public function test($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) === null ? false : true;
    }

    public function parse($value)
    {
        return strval($value);
    }

    public function deflate($value)
    {
        return $value;
    }
}

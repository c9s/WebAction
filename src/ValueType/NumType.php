<?php

namespace WebAction\ValueType;

class NumType extends BaseType
{
    public function test($value)
    {
        return is_numeric($value);
    }

    public function parse($value)
    {
        return intval($value);
    }

    public function deflate($value)
    {
        return $value;
    }
}

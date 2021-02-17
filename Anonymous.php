<?php

class Anonymous
{
    private $attributes = [
        'count' => 2
    ];

    public function __get(string $name)
    {
        return array_key_exists($name, $this->attributes)
            ? $this->attributes[$name]
            : null;
    }
}

$anonymous = new Anonymous();

echo $anonymous->count;
var_dump(empty($anonymous->count));
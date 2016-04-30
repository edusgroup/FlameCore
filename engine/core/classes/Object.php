<?php

namespace core\classes;

class Object
{
    public function __get($name)
    {
        $name = '_'.$name.'_';
        if (isset($this->{$name})) {
            return $this->{$name};
        }
        throw new \Exception('Not found _'.$name.'_ varible');
    }

    public function __set($name, $value)
    {
        $name = '_'.$name.'_';
        if (isset($this->{$name})) {
            $this->{$name} = $value;
        }
        throw new \Exception('Not found _'.$name.'_ varible');
    }
}

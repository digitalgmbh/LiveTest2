<?php

namespace phmLabs\Components\Annovent\Annotation;

/**
 * @Annotation
 */
class Event
{
    public $value;

    public function getNames()
    {
        return (array)$this->value;
    }
}
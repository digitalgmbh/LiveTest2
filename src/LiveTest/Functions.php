<?php

namespace LiveTest;

use Annovent\Exception as AnnoventException;
use phmLabs\Components\NamedParameters\NamedParameters;

class Functions
{
    public static function initializeObject($object, $parameter)
    {
        $result = '';

        if (method_exists($object, 'init')) {
            try {
                $namedParameters = new NamedParameters();
                $result = $namedParameters->callMethod($object, 'init', $parameter);
            } catch (\phmLabs\Components\NamedParameters\Exception $e) {
                throw new ConfigurationException('Unable to initialize object (' . get_class($object) . '). ' . 'Mandatory parameter "' . $e->getMissingParameter() . '" is missing.');
            }
        }

        return $result;
    }
}
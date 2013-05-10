<?php

/*
 * This file is part of the LiveTest package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LiveTest\TestCase\General\Http;

use Base\Http\Request\Request;
use Base\Http\Response\Response;
use LiveTest\TestCase\Exception;
use LiveTest\TestCase\TestCase;

/**
 * This test case is used to check for an speficed http status code.
 *
 * @author Nils Langner
 */
class ExpectedStatusCode implements TestCase
{
    private $statusCode;

    /**
     * Sets the http status code
     * @param int $statusCode
     */
    public function init($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * Checks if the actual http status equals the expected.
     *
     * @see LiveTest\TestCase.HttpTestCase::test()
     */
    public function test(Response $response, Request $request)
    {
        $status = $response->getStatus();
        if ($status != $this->statusCode) {
            throw new Exception('The http status code ' . $status . ' was found, expected code was ' . $this->statusCode);
        }
    }
}
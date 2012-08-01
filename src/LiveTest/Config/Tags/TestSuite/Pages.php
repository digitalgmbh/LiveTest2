<?php

/*
 * This file is part of the LiveTest package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LiveTest\Config\Tags\TestSuite;

use LiveTest\ConfigurationException;

use LiveTest\Config\Parser;
use LiveTest\Connection\Request\Symfony as SymfonyRequest;

/**
 * This tag sets a list of pages to the configuration and stops the inheritance of the config file. If
 * you want to inherit use the IncludePages tag.
 *
 * @example
 * Pages:
 * - /
 * - http://www.example.com
 *
 * @author Nils Langner
 */
class Pages extends Base
{
  /**
   * @see LiveTest\Config\Tags\TestSuite.Base::doProcess()
   */
  protected function doProcess(\LiveTest\Config\TestSuite $config, $parameters)
  {
    $requests = SymfonyRequest::createRequestsFromParameters($parameters, $config->getDefaultDomain());

    if ($config->hasSessions())
    {
      $config->getCurrentSession()->includePageRequests($requests);
    }
    else
    {
      throw new ConfigurationException('You can not use the Page tag outside a session tag.');
    }
  }
}
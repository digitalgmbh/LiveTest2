<?php

/*
 * This file is part of the LiveTest package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace LiveTest\Packages\Reporting\Format;

use LiveTest\ConfigurationException;

use Zend\Test\PHPUnit\Constraint\Exception\ConstraintException;

use LiveTest\TestRun\Information;

use LiveTest\TestRun\Result\ResultSet;
use LiveTest\TestRun\Result\Result;

/**
 * This format converts the given results into a html template.
 *
 * @author Nils Langner
 */

class Html implements Format
{
  private $templateDir = "/templates/";
  private $standardTemplate = 'html.php';

  /**
   * The html template used for rendering
   * @var string
   */
  private $template;

  /**
   * An ordered list of all result statuses
   * @var array
   */
  private $statuses;

  /**
   * This constructor sets the standard values for the html template and the result status order.
   */
  public function __construct()
  {
    $this->statuses = array (Result::STATUS_SUCCESS => 1, Result::STATUS_FAILED => 2, Result::STATUS_ERROR => 3 );
    $this->template = __DIR__ . $this->templateDir . $this->standardTemplate;
  }

  /**
   * Sets the template.
   *
   * @param string $template
   */
  public function init($template = null)
  {
    if (!is_null($template))
    {
      if (file_exists ( __DIR__ . $this->templateDir .  $template)) {
        $this->template = __DIR__ . $this->templateDir .  $template;
      } else if (file_exists ( $template)) {
        $this->template = $template;
      } else {
         throw new ConfigurationException('Template "'.$template.'" not found.');
      }
    }
  }

  /**
   * Formats the given results to a html document.
   *
   * @param ResultSet $set
   * @param array $connectionStatuses
   * @param Information $information
   *
   * @return string
   */
  public function formatSet(ResultSet $set, array $connectionStatuses, Information $information)
  {
    $matrix = array ();
    $tests = array ();
    $testCount = 0;

    foreach ( $set as $result )
    {
      $testCount++;
      $matrix [$result->getRequest()->getUri()] ['tests'] [$result->getTest()->getName()] = $result;
      if (array_key_exists('status', $matrix [$result->getRequest()->getUri()]))
      {
        $matrix [$result->getRequest()->getUri()] ['status'] = max($matrix [$result->getRequest()->getUri()] ['status'], $this->statuses [$result->getStatus()]);
      }
      else
      {
        $matrix [$result->getRequest()->getUri()] ['status'] = $this->statuses [$result->getStatus()];
      }
      $tests [$result->getTest()->getName()] = $result->getTest();
    }
    ob_start();
    require $this->template;
    $content = ob_get_contents();
    ob_clean();

    return $content;
  }
}
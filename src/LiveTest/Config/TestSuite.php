<?php

/*
 * This file is part of the LiveTest package. For the full copyright and license
 * information, please view the LICENSE file that was distributed with this
 * source code.
 */
namespace LiveTest\Config;
use LiveTest\ConfigurationException;
use LiveTest\Connection\Session\Session;
use Base\Www\Uri;
use Base\Http\Request\Request;

/**
 * This class contains all information about the tests and the depending pages.
 *
 * @author Nils Langner
 */
use LiveTest\Config\PageManipulator\PageManipulator;

class TestSuite implements Config
{

    /**
     * The created tests.
     *
     * @var array
     */
    private $testCases = array();

    /**
     * The directory of the yaml file this configuration was created from
     *
     * @var string
     */
    private $baseDir;

    /**
     * The parent configuration.
     * Used to inherit pages.
     *
     * @var TestSuite
     */
    private $parentConfig;

    private $sessions = array();

    private $currentSession;

    private $currentSessionName;

    private $_defaultSession;
    const DEFAULT_SESSION = '_DEFAULT';

    /**
     * The default domain
     *
     * @var Uri $defaultDomain
     */
    private $defaultDomain = null;

    private $pageManipulators = array();

    private $sessionsGroups = array();

    /**
     * Set the parent config if needed.
     *
     * @param TestSuite $parentConfig
     */
    public function __construct (TestSuite $parentConfig = null)
    {
        $this->parentConfig = $parentConfig;
    }

    public function addSessionGroup ($sessionGroupName, array $sessionNames)
    {
        $this->sessionsGroups[$sessionGroupName] = $sessionNames;
    }

    private function getSessionGroup ($sessionGroupName)
    {
        return $this->sessionsGroups[$sessionGroupName];
    }

    public function resolveSessionGroups ()
    {
        foreach ($this->testCases as $testCase) {
            $sessionGroupNames = $testCase->getSessionGroupNames();
            foreach ($sessionGroupNames as $sessionGroupName) {
                $sessions = $this->getSessionGroup($sessionGroupName);
                foreach ($sessions as $session) {
                    $testCase->addSession($session);
                }
            }
        }
    }

    function addSession ($sessionName, Session $session)
    {
        $this->sessions[$sessionName] = $session;
    }

    public function getSession ($sessionName)
    {
        // @todo error handling (hasSession)
        return $this->sessions[$sessionName];
    }

    public function hasSession ($sessionName)
    {
        return array_key_exists($sessionName, $this->sessions);
    }

    public function getSessions ()
    {
        return $this->sessions;
    }

    public function hasSessions ()
    {
        return count($this->sessions) > 0;
    }

    public function setCurrentSession ($sessionName)
    {
        $this->currentSession = $this->sessions[$sessionName];
        $this->currentSessionName = $sessionName;
    }

    public function getCurrentSession ()
    {
        if (! $this->hasCurrentSession()) {
            throw new \Exception('No session has been added.');
        }
        return $this->currentSession;
    }

    public function getCurrentSessionName ()
    {
        return $this->currentSessionName;
    }

    public function hasCurrentSession ()
    {
        return ! is_null($this->currentSession);
    }

    public function _getNewSession ($sessionName, $isCurrentSession = true)
    {
        $session = new Session($this->getDefaultDomain());
        $this->sessions[$sessionName][] = $session;
        if ($isCurrentSession) {
            $this->currentSession = $session;
            $this->currentSessionName = $sessionName;
        }
        return $session;
    }

    public function _setCurrentSession ($sessionName)
    {
        if (! $this->hasSession($sessionName)) {
            throw new ConfigurationException('The session you are trying to access is not available (' . $sessionName . ').');
        }
        $this->currentSession = $this->sessions[$sessionName];
        $this->currentSessionName = $sessionName;
    }

    /**
     *
     * @todo ->getSessionContainer->getCurrentSession( )
     * @todo SessionContainer implements Iteratable
     */
    public function _getCurrentSession ()
    {
        return $this->currentSession;
    }

    public function _switchToDefaultSession ()
    {
        $this->currentSession = $this->defaultSession;
    }

    public function _getSessions ()
    {
        $parentSessions = array();
        if (! is_null($this->parentConfig)) {
            $parentSessions = $this->parentConfig->getSessions();
        }
        return array_merge($this->sessions, $parentSessions);
    }

    /**
     * Sets the base dir.
     * This is needed because some tags need the path to the config
     * entry file.
     *
     * @param string $baseDir
     */
    public function setBaseDir ($baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * Sets the base domain
     *
     * @param Uri $domain
     */
    public function setDefaultDomain (Uri $domain)
    {
        $this->defaultDomain = $domain;
    }

    /**
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
     * gets the base domain
     *
     * @return Uri $defaultDomain
     */
    public function getDefaultDomain ()
    {
        return $this->defaultDomain;
    }

    /**
     * Returns the base directory of the config file.
     *
     * @return string
     */
    public function getBaseDir ()
    {
        if (is_null($this->baseDir)) {
            return $this->parentConfig->getBaseDir();
        }
        return $this->baseDir;
    }

    /**
     * This function adds a test to the config and returns a new config
     * connected to the
     * test.
     *
     * @todo we should use the Test class for this
     *
     * @param string $name
     * @param string $className
     * @param array $parameters
     */
    public function createTestCase ($name, $className, array $parameters, $failOnError = false)
    {
        $testCaseConfig = new TestCaseConfig($className, $parameters, $failOnError);
        $this->testCases[$name] = $testCaseConfig;
    }

    public function hasTestCaseConfig ()
    {
        return count($this->testCases) > 0;
    }

    public function getCurrentTestCaseConfig ()
    {
        if (! $this->hasTestCaseConfig()) {
            // @todo use special exeption
            throw new \Exception('No test case was created. See createTestCase().');
        }
        return end($this->testCases);
    }

    private function getReducedPageRequests (array $includedPageRequest, array $excludedPageRequests)
    {
        foreach ($excludedPageRequests as $identifier => $pageRequest) {
            if (array_key_exists($identifier, $includedPageRequest)) {
                unset($includedPageRequest[$identifier]);
            }
        }

        return $includedPageRequest;
    }

    /**
     * Returns the tests.
     *
     * @return array
     */
    public function getTestCases ()
    {
        return $this->testCases;
    }
}

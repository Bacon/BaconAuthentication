<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication;

use BaconAuthentication\Plugin\AuthenticationPluginInterface;
use BaconAuthentication\Plugin\ChallengePluginInterface;
use BaconAuthentication\Plugin\EventAwarePluginInterface;
use BaconAuthentication\Plugin\ExtractionPluginInterface;
use BaconAuthentication\Plugin\ResetPluginInterface;
use BaconAuthentication\Result\Result;
use BaconAuthentication\Result\ResultInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\Stdlib\PriorityQueue;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class AuthenticationService implements
    AuthenticationServiceInterface,
    EventManagerAwareInterface
{
    /**
     * @var PriorityQueue
     */
    protected $authenticationPlugins;

    /**
     * @var PriorityQueue
     */
    protected $challengePlugins;

    /**
     * @var PriorityQueue
     */
    protected $extractionPlugins;

    /**
     * @var PriorityQueue
     */
    protected $resetPlugins;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * Initiates the plugin containers.
     */
    public function __construct()
    {
        $this->authenticationPlugins = new PriorityQueue();
        $this->challengePlugins      = new PriorityQueue();
        $this->extractionPlugins     = new PriorityQueue();
        $this->resetPlugins          = new PriorityQueue();
    }

    /**
     * Adds a plugin to the service.
     *
     * @param  mixed   $plugin
     * @param  integer $priority
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function addPlugin($plugin, $priority = 1)
    {
        $isValid = false;

        if ($plugin instanceof AuthenticationPluginInterface) {
            $this->authenticationPlugins->insert($plugin, $priority);
            $isValid = true;
        }

        if ($plugin instanceof ChallengePluginInterface) {
            $this->challengePlugins->insert($plugin, $priority);
            $isValid = true;
        }

        if ($plugin instanceof ExtractionPluginInterface) {
            $this->extractionPlugins->insert($plugin, $priority);
            $isValid = true;
        }

        if ($plugin instanceof ResetPluginInterface) {
            $this->resetPlugins->insert($plugin, $priority);
            $isValid = true;
        }

        if ($plugin instanceof EventAwarePluginInterface) {
            $plugin->attachToEvents($this->getEventManager());
            $isValid = true;
        }

        if (!$isValid) {
            throw new Exception\InvalidArgumentException(
                'Plugin does not implement any known feature interface'
            );
        }
    }

    /**
     * authenticate(): defined by AuthenticationServiceInterface.
     *
     * @see    AuthenticationServiceInterface::authenticate()
     * @param  RequestInterface  $request
     * @param  ResponseInterface $response
     * @return ResultInterface
     * @throws Exception\RuntimeException
     */
    public function authenticate(RequestInterface $request, ResponseInterface $response)
    {
        $events = $this->getEventManager();
        $event = new AuthenticationEvent();
        $event->setTarget($this)
              ->setRequest($request)
              ->setResponse($response);

        $shortCircuit = function ($result) {
            if ($result instanceof ResultInterface) {
                return true;
            }

            return false;
        };

        $eventResult = $events->trigger(AuthenticationEvent::EVENT_AUTHENTICATE_PRE, $event, $shortCircuit);

        if ($eventResult->stopped()) {
            $result = $eventResult->last();
        } else {
            $result = $this->runAuthenticationPlugins($request, $response);
        }

        if ($result === null) {
            if ($this->challenge()) {
                $result = new Result(Result::STATE_CHALLENGE);
                $event->setResult($result);
            }
        } else {
            $event->setResult($result);
        }

        $eventResult = $events->trigger(AuthenticationEvent::EVENT_AUTHENTICATE_POST, $event, $shortCircuit);

        if ($eventResult->stopped()) {
            $result = $eventResult->last();
        }

        if ($result === null) {
            throw new Exception\RuntimeException('No plugin was able to generate a result');
        }

        return $result;
    }

    /**
     * resetCredentials(): defined by AuthenticationServiceInterface.
     *
     * @see    AuthenticationServiceInterface::resetCredentials()
     * @param  RequestInterface $request
     * @return void
     */
    public function resetCredentials(RequestInterface $request)
    {
        foreach ($this->resetPlugins as $resetPlugin) {
            /* @var $resetPlugin ResetPluginInterface */
            $resetPlugin->resetCredentials($request);
        }
    }

    /**
     * Runs all authentication plugins to get a result.
     *
     * @param  RequestInterface $request
     * @param  ResponseInterface $response
     * @return ResultInterface|null
     */
    protected function runAuthenticationPlugins(RequestInterface $request, ResponseInterface $response)
    {
        foreach ($this->extractionPlugins as $extractionPlugin) {
            /* @var $extractionPlugin ExtractionPluginInterface */
            $credentials = $extractionPlugin->extractCredentials($request);

            if ($credentials === null) {
                continue;
            }

            if ($credentials instanceof ResultInterface) {
                return $credentials;
            }

            foreach ($this->authenticationPlugins as $authenticationPlugin) {
                /* @var $authenticationPlugin AuthenticationPluginInterface */
                $result = $authenticationPlugin->authenticateCredentials($credentials);

                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null;
    }

    /**
     * Tries to initiate a challenge.
     *
     * @param  RequestInterface $request
     * @param  ResponseInterface $response
     * @return bool
     */
    protected function challenge(RequestInterface $request, ResponseInterface $response)
    {
        foreach ($this->challengePlugins as $challengePlugin) {
            /* @var $challengePlugin ChallengePluginInterface */
            if ($challengePlugin->challenge($request, $response)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets the event manager.
     *
     * @param  EventManagerInterface $eventManager
     * @return AuthenticationService
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(array(
            __CLASS__,
            get_class($this),
        ));

        $this->events = $eventManager;

        return $this;
    }

    /**
     * Returns the event manager.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if ($this->events === null) {
            $this->setEventManager(new EventManager());
        }

        return $this->events;
    }
}

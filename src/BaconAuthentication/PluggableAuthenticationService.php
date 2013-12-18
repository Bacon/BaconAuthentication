<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication;

use BaconAuthentication\Exception;
use BaconAuthentication\Plugin\AuthenticationPluginInterface;
use BaconAuthentication\Plugin\ChallengePluginInterface;
use BaconAuthentication\Plugin\EventAwarePluginInterface;
use BaconAuthentication\Plugin\ExtractionPluginInterface;
use BaconAuthentication\Plugin\ResetPluginInterface;
use BaconAuthentication\Plugin\ResolutionPluginInterface;
use BaconAuthentication\Result\Error;
use BaconAuthentication\Result\Result;
use BaconAuthentication\Result\ResultInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Stdlib\PriorityQueue;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

/**
 * Pluggable authentication service implementation.
 */
class PluggableAuthenticationService implements
    AuthenticationServiceInterface,
    EventManagerAwareInterface
{
    /**
     * @var PriorityQueue|AuthenticationPluginInterface[]
     */
    protected $authenticationPlugins;

    /**
     * @var PriorityQueue|ChallengePluginInterface[]
     */
    protected $challengePlugins;

    /**
     * @var PriorityQueue|ExtractionPluginInterface[]
     */
    protected $extractionPlugins;

    /**
     * @var PriorityQueue|ResetPluginInterface[]
     */
    protected $resetPlugins;

    /**
     * @var PriorityQueue|ResolutionPluginInterface[]
     */
    protected $resolutionPlugins;

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
        $this->resolutionPlugins     = new PriorityQueue();
    }

    /**
     * Adds a plugin to the service.
     *
     * @param  mixed   $plugin
     * @param  integer $priority
     * @return PluggableAuthenticationService
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

        if ($plugin instanceof ResolutionPluginInterface) {
            $this->resolutionPlugins->insert($plugin, $priority);
            $isValid = true;
        }

        if ($plugin instanceof ListenerAggregateInterface) {
            $plugin->attach($this->getEventManager());
            $isValid = true;
        }

        if (!$isValid) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s does not implement any known plugin interface',
                is_object($plugin) ? get_class($plugin) : gettype($plugin)
            ));
        }

        return $this;
    }

    /**
     * authenticate(): defined by AuthenticationServiceInterface.
     *
     * @see    AuthenticationServiceInterface::authenticate()
     * @param  RequestInterface  $request
     * @return ResultInterface
     * @throws Exception\RuntimeException
     */
    public function authenticate(RequestInterface $request)
    {
        $events = $this->getEventManager();
        $event = new AuthenticationEvent();
        $event->setTarget($this)
              ->setRequest($request);

        $shortCircuit = function ($result) {
            return ($result instanceof ResultInterface);
        };

        $eventResult = $events->trigger(AuthenticationEvent::EVENT_AUTHENTICATE_PRE, $event, $shortCircuit);

        if ($eventResult->stopped()) {
            $result = $eventResult->last();
        } else {
            $result = $this->runAuthenticationPlugins($request);
        }

        if ($result === null) {
            if ($this->challenge($request)) {
                $result = new Result(Result::STATE_CHALLENGE);
                $event->setResult($result);
            }
        } else {
            $event->setResult($result);
        }

        $eventResult = $events->trigger(AuthenticationEvent::EVENT_AUTHENTICATE_POST, $event, $shortCircuit);

        if ($eventResult->stopped()) {
            $result = $eventResult->last();
            $event->setResult($result);
        }

        if ($result === null) {
            throw new Exception\RuntimeException('No plugin was able to generate a result');
        }

        if ($result->isSuccess()) {
            $eventResult = $events->trigger(AuthenticationEvent::EVENT_RESOLVE_PRE, $event, $shortCircuit);

            if ($eventResult->stopped()) {
                return $eventResult->last();
            }

            $subject = $this->resolveSubject($result->getPayload());

            if ($subject !== null) {
                $result = new Result(Result::STATE_SUCCESS, $subject);
            } else {
                $result = new Result(Result::STATE_FAILURE, new Error(__CLASS__, 'Subject could not be resolved'));
            }

            $eventResult = $events->trigger(AuthenticationEvent::EVENT_RESOLVE_POST, $event, $shortCircuit);

            if ($eventResult->stopped()) {
                return $eventResult->last();
            }
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
     * @param  RequestInterface  $request
     * @return ResultInterface|null
     */
    protected function runAuthenticationPlugins(RequestInterface $request)
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
    protected function challenge(RequestInterface $request)
    {
        foreach ($this->challengePlugins as $challengePlugin) {
            /* @var $challengePlugin ChallengePluginInterface */
            if ($challengePlugin->challenge($request)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tries to resolve a subject.
     *
     * @param  mixed $identifier
     * @return mixed|null
     */
    protected function resolveSubject($identifier)
    {
        foreach ($this->resolutionPlugins as $resolutionPlugin) {
            /* @var $resolutionPlugin ResolutionPluginInterface */
            $subject = $resolutionPlugin->resolveSubject($identifier);

            if ($subject !== null) {
                return $subject;
            }
        }

        return null;
    }

    /**
     * Sets the event manager.
     *
     * @param  EventManagerInterface $eventManager
     * @return PluggableAuthenticationService
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

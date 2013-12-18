<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication\Plugin;

use BaconAuthentication\AuthenticationEvent;
use BaconAuthentication\Result\Result;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Session\ManagerInterface;
use Zend\Session\Container as SessionContainer;
use Zend\Stdlib\RequestInterface;

/**
 * Plugin responsible for tracking the identity between multiple HTTP requests.
 */
class Session implements ListenerAggregateInterface, ResetPluginInterface
{
    /**
     * Default session namespace.
     */
    const DEFAULT_NAMESPACE = 'baconauthentication';

    /**
     * @var SessionContainer
     */
    protected $session;

    /**
     * @var array
     */
    protected $listeners = [];

    /**
     * @param string|null      $namespace
     * @param ManagerInterface $manager
     */
    public function __construct($namespace = null, ManagerInterface $manager = null)
    {
        if ($namespace === null) {
            $namespace = self::DEFAULT_NAMESPACE;
        }

        $this->session = new SessionContainer($namespace, $manager);
    }

    /**
     * attachToEvents(): defined by EventAwarePluginInterface.
     *
     * @see    EventAwarePluginInterface::attachToEvents()
     * @param  EventManagerInterface $events
     * @return void
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            AuthenticationEvent::EVENT_AUTHENTICATE_PRE,
            array($this, 'checkSession')
        );

        $this->listeners[] = $events->attach(
            AuthenticationEvent::EVENT_AUTHENTICATE_POST,
            array($this, 'storeIdentifier')
        );
    }

    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $idx => $callback) {
            $events->detach($callback);
            unset($this->listeners[$idx]);
        }
    }

    /**
     * resetCredentials(): defined by ResetInterface.
     *
     * @see    ResetInterface::resetCredentials()
     * @param  RequestInterface $request
     * @return void
     */
    public function resetCredentials(RequestInterface $request)
    {
        unset($this->session->identifier);
    }

    /**
     * Checks the session for an already stored identifier.
     *
     * @param  AuthenticationEvent $event
     * @return Result|null
     */
    public function checkSession(AuthenticationEvent $event)
    {
        if (isset($this->session->identifier)) {
            return new Result(Result::STATE_SUCCESS, $this->session->identifier);
        }

        return null;
    }

    /**
     * Stores the identifier if the authentication succeeded.
     *
     * @param  AuthenticationEvent $event
     * @return Result
     */
    public function storeIdentifier(AuthenticationEvent $event)
    {
        $result = $event->getResult();

        if ($result->isSuccess() && is_scalar($result->getPayload())) {
            $this->session->identifier = $result->getPayload();
        }
    }
}



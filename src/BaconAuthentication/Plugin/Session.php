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
use Zend\Session\ManagerInterface;
use Zend\Session\Container as SessionContainer;
use Zend\Stdlib\RequestInterface;

class Session implements
    EventAwarePluginInterface,
    ResetPluginInterface
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
    public function attachToEvents(EventManagerInterface $events)
    {
        $events->attach(AuthenticationEvent::EVENT_AUTHENTICATE_PRE, array($this, 'checkSession'));
        $events->attach(AuthenticationEvent::EVENT_AUTHENTICATE_POST, array($this, 'storeIdentity'));
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
        unset($this->session->identity);
    }

    /**
     * Checks the session for an already stored identity.
     *
     * @param  AuthenticationEvent $event
     * @return Result|null
     */
    public function checkSession(AuthenticationEvent $event)
    {
        if (isset($this->session->identity)) {
            return new Result(Result::STATE_SUCCESS, $this->session->identity);
        }

        return null;
    }

    /**
     * Stores the identity if the authentication succeeded.
     *
     * @param  AuthenticationEvent $event
     * @return Result
     */
    public function storeIdentity(AuthenticationEvent $event)
    {
        $result = $event->getResult();

        if ($result->isSuccess()) {
            $this->session->identity = $result->getPayload();
        }
    }
}



<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthenticationTest\Plugin;

use BaconAuthentication\AuthenticationEvent;
use BaconAuthentication\Result\Result;
use BaconAuthentication\Plugin\Session;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use Zend\Session\Container;

/**
 * @covers BaconAuthentication\Plugin\Session
 */
class SessionTest extends TestCase
{
    protected $previousDefaultManager;

    public function setUp()
    {
        $this->previousDefaultManager = Container::getDefaultManager();

        $manager = $this->getMock('Zend\Session\ManagerInterface');
        $manager->expects($this->any())
                ->method('getStorage')
                ->will($this->returnValue(new \Zend\Session\Storage\ArrayStorage()));

        Container::setDefaultManager($manager);
    }

    public function tearDown()
    {
        Container::setDefaultManager($this->previousDefaultManager);
    }

    public function testDefaultNamespace()
    {
        $session = new Session();
        $this->assertEquals('baconauthentication', $this->getContainer($session)->getName());
    }

    public function testCustomNamespace()
    {
        $session = new Session('foobar');
        $this->assertEquals('foobar', $this->getContainer($session)->getName());
    }

    public function testAttachToEvents()
    {
        $session = new Session();

        $events = $this->getMock('Zend\EventManager\EventManagerInterface');
        $events->expects($this->exactly(2))
               ->method('attach')
               ->with($this->logicalOr(
                   $this->equalTo(AuthenticationEvent::EVENT_AUTHENTICATE_PRE),
                   $this->equalTo(AuthenticationEvent::EVENT_AUTHENTICATE_POST)
               ), $this->logicalOr(
                   $this->equalTo(array($session, 'checkSession')),
                   $this->equalTo(array($session, 'storeIdentifier'))
               ));

        $session->attach($events);
    }

    public function testIdentifierStorageWithSuccessfulResult()
    {
        $session   = new Session();
        $container = $this->getContainer($session);

        $event = new AuthenticationEvent();
        $event->setResult(new Result(Result::STATE_SUCCESS, 'foobar'));
        $session->storeIdentifier($event);

        $this->assertEquals('foobar', $container->identifier);
    }

    public function testIdentifierStorageWithFailedResult()
    {
        $session   = new Session();
        $container = $this->getContainer($session);

        $event = new AuthenticationEvent();
        $event->setResult(new Result(Result::STATE_FAILURE));
        $session->storeIdentifier($event);

        $this->assertNull($container->identifier);
    }

    public function testCheckSessionWithExistingIdentifier()
    {
        $session   = new Session();
        $container = $this->getContainer($session);
        $container->identifier = 'foobar';

        $event  = new AuthenticationEvent();
        $result = $session->checkSession($event);

        $this->assertEquals('foobar', $result->getPayload());
    }

    public function testCheckSessionWithoutIdentifier()
    {
        $session = new Session();
        $event   = new AuthenticationEvent();
        $result  = $session->checkSession($event);

        $this->assertNull($result);
    }

    public function testReset()
    {
        $session   = new Session();
        $container = $this->getContainer($session);

        $container->identifier = 'foobar';
        $session->resetCredentials($this->getMock('Zend\Stdlib\RequestInterface'));

        $this->assertNull($container->identifier);
    }

    /**
     * @param  Session $session
     * @return \Zend\Session\AbstractContainer
     */
    protected function getContainer(Session $session)
    {
        $class     = new ReflectionClass($session);
        $property  = $class->getProperty('session');
        $property->setAccessible(true);
        return $property->getValue($session);
    }
}

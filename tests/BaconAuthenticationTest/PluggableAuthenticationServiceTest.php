<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthenticationTest;

use BaconAuthentication\PluggableAuthenticationService;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers BaconAuthentication\PluggableAuthenticationService
 */
class PluggableAuthenticationServiceTest extends TestCase
{
    public function testAddInvalidPlugin()
    {
        $this->setExpectedException(
            'BaconAuthentication\Exception\InvalidArgumentException',
            'stdClass does not implement any known plugin interface'
        );

        $service = new PluggableAuthenticationService();
        $service->addPlugin(new \stdClass());
    }

    public function testAddEventAwarePlugin()
    {
        $service = new PluggableAuthenticationService();

        $plugin = $this->getMock('Zend\EventManager\ListenerAggregateInterface');
        $plugin->expects($this->once())
               ->method('attach')
               ->with($this->equalTo($service->getEventManager()));

        $service->addPlugin($plugin);
    }

    public function testAuthenticateWithoutResult()
    {
        $this->setExpectedException(
            'BaconAuthentication\Exception\RuntimeException',
            'No plugin was able to generate a result'
        );

        $service = new PluggableAuthenticationService();
        $service->authenticate(
            $this->getMock('Zend\Stdlib\RequestInterface'),
            $this->getMock('Zend\Stdlib\ResponseInterface')
        );
    }

    public function testPreAuthenticateShortCircuit()
    {
        $result  = $this->getMock('BaconAuthentication\Result\ResultInterface');
        $service = new PluggableAuthenticationService();
        $service->getEventManager()->attach(
            'authenticate.pre',
            function () use ($result) {
                return $result;
            }
        );

        $this->assertSame(
            $result,
            $service->authenticate(
                $this->getMock('Zend\Stdlib\RequestInterface'),
                $this->getMock('Zend\Stdlib\ResponseInterface')
            )
        );
    }

    public function testPostAuthenticateShortCircuit()
    {
        $result  = $this->getMock('BaconAuthentication\Result\ResultInterface');
        $service = new PluggableAuthenticationService();
        $service->getEventManager()->attach(
            'authenticate.post',
            function () use ($result) {
                return $result;
            }
        );

        $this->assertSame(
            $result,
            $service->authenticate(
                $this->getMock('Zend\Stdlib\RequestInterface'),
                $this->getMock('Zend\Stdlib\ResponseInterface')
            )
        );
    }

    public function testChallengeIsGeneratedWithoutResult()
    {
        $service = new PluggableAuthenticationService();
        $plugin  = $this->getMock('BaconAuthentication\Plugin\ChallengePluginInterface');
        $plugin->expects($this->once())
               ->method('challenge')
               ->will($this->returnValue(true));

        $service->addPlugin($plugin);
        $result = $service->authenticate(
            $this->getMock('Zend\Stdlib\RequestInterface'),
            $this->getMock('Zend\Stdlib\ResponseInterface')
        );

        $this->assertInstanceOf('BaconAuthentication\Result\ResultInterface', $result);
        $this->assertTrue($result->isChallenge());
    }

    public function testExtractionPluginShortCircuit()
    {
        $result  = $this->getMock('BaconAuthentication\Result\ResultInterface');
        $service = new PluggableAuthenticationService();
        $plugin  = $this->getMock('BaconAuthentication\Plugin\ExtractionPluginInterface');
        $plugin->expects($this->once())
               ->method('extractCredentials')
               ->will($this->returnValue($result));

        $service->addPlugin($plugin);

        $this->assertSame(
            $result,
            $service->authenticate(
                $this->getMock('Zend\Stdlib\RequestInterface'),
                $this->getMock('Zend\Stdlib\ResponseInterface')
            )
        );
    }

    public function testNonSuccessfulExtractionSkipsAuthentication()
    {
        $service = new PluggableAuthenticationService();

        $extractionPlugin = $this->getMock('BaconAuthentication\Plugin\ExtractionPluginInterface');
        $extractionPlugin->expects($this->once())
                         ->method('extractCredentials')
                         ->will($this->returnValue(null));

        $authenticationPlugin = $this->getMock('BaconAuthentication\Plugin\AuthenticationPluginInterface');
        $authenticationPlugin->expects($this->never())
                         ->method('authenticateCredentials');

        $service->addPlugin($extractionPlugin)->addPlugin($authenticationPlugin);

        $this->setExpectedException(
            'BaconAuthentication\Exception\RuntimeException',
            'No plugin was able to generate a result'
        );
        $service->authenticate(
            $this->getMock('Zend\Stdlib\RequestInterface'),
            $this->getMock('Zend\Stdlib\ResponseInterface')
        );
    }

    public function testSuccessfulExtractionWithoutAuthenticationPlugin()
    {
        $credentials = $this->getMock('Zend\Stdlib\Parameters');
        $service     = new PluggableAuthenticationService();

        $extractionPlugin = $this->getMock('BaconAuthentication\Plugin\ExtractionPluginInterface');
        $extractionPlugin->expects($this->once())
                         ->method('extractCredentials')
                         ->will($this->returnValue($credentials));

        $service->addPlugin($extractionPlugin);

        $this->setExpectedException(
            'BaconAuthentication\Exception\RuntimeException',
            'No plugin was able to generate a result'
        );
        $service->authenticate(
            $this->getMock('Zend\Stdlib\RequestInterface'),
            $this->getMock('Zend\Stdlib\ResponseInterface')
        );
    }

    public function testExtractedCredentialsArePassedToAuthenticationPlugin()
    {
        $credentials = $this->getMock('Zend\Stdlib\Parameters');
        $result      = $this->getMock('BaconAuthentication\Result\ResultInterface');
        $service     = new PluggableAuthenticationService();

        $extractionPlugin = $this->getMock('BaconAuthentication\Plugin\ExtractionPluginInterface');
        $extractionPlugin->expects($this->once())
                         ->method('extractCredentials')
                         ->will($this->returnValue($credentials));

        $authenticationPlugin = $this->getMock('BaconAuthentication\Plugin\AuthenticationPluginInterface');
        $authenticationPlugin->expects($this->once())
                         ->method('authenticateCredentials')
                         ->with($this->equalTo($credentials))
                         ->will($this->returnValue($result));

        $service->addPlugin($extractionPlugin)->addPlugin($authenticationPlugin);

        $this->assertSame(
            $result,
            $service->authenticate(
                $this->getMock('Zend\Stdlib\RequestInterface'),
                $this->getMock('Zend\Stdlib\ResponseInterface')
            )
        );
    }

    public function testAuthenticationFailsWithoutResolution()
    {
        $result = $this->getMock('BaconAuthentication\Result\ResultInterface');
        $result->expects($this->any())
               ->method('isSuccess')
               ->will($this->returnValue(true));
        $result->expects($this->any())
               ->method('getPayload')
               ->will($this->returnValue(1));

        $service = new PluggableAuthenticationService();
        $service->getEventManager()->attach(
            'authenticate.post',
            function () use ($result) {
                return $result;
            }
        );

        $result = $service->authenticate(
            $this->getMock('Zend\Stdlib\RequestInterface'),
            $this->getMock('Zend\Stdlib\ResponseInterface')
        );

        $this->assertTrue($result->isFailure());
        $this->assertEquals(
            'BaconAuthentication\PluggableAuthenticationService',
            $result->getPayload()->getScope()
        );
        $this->assertEquals(
            'Subject could not be resolved',
            $result->getPayload()->getMessage()
        );
    }

    public function testSubjectResolution()
    {
        $result = $this->getMock('BaconAuthentication\Result\ResultInterface');
        $result->expects($this->any())
               ->method('isSuccess')
               ->will($this->returnValue(true));
        $result->expects($this->any())
               ->method('getPayload')
               ->will($this->returnValue(1));

        $service = new PluggableAuthenticationService();
        $service->getEventManager()->attach(
            'authenticate.post',
            function () use ($result) {
                return $result;
            }
        );

        $plugin = $this->getMock('BaconAuthentication\Plugin\ResolutionPluginInterface');
        $plugin->expects($this->once())
               ->method('resolveSubject')
               ->with($this->equalTo(1))
               ->will($this->returnValue(array('name' => 'foo')));
        $service->addPlugin($plugin);

        $result = $service->authenticate(
            $this->getMock('Zend\Stdlib\RequestInterface'),
            $this->getMock('Zend\Stdlib\ResponseInterface')
        );

        $this->assertTrue($result->isSuccess());
        $this->assertEquals(
            array('name' => 'foo'),
            $result->getPayload()
        );
    }

    public function testResolutionPreShortCircuit()
    {
        $result = $this->getMock('BaconAuthentication\Result\ResultInterface');
        $result->expects($this->any())
               ->method('isSuccess')
               ->will($this->returnValue(true));
        $result->expects($this->any())
               ->method('getPayload')
               ->will($this->returnValue(1));

        $service = new PluggableAuthenticationService();
        $service->getEventManager()->attach(
            'authenticate.post',
            function () use ($result) {
                return $result;
            }
        );

        $expectedResult = $this->getMock('BaconAuthentication\Result\ResultInterface');

        $service->getEventManager()->attach(
            'resolve.pre',
            function () use ($expectedResult) {
                return $expectedResult;
            }
        );

        $this->assertSame(
            $expectedResult,
            $service->authenticate(
                $this->getMock('Zend\Stdlib\RequestInterface'),
                $this->getMock('Zend\Stdlib\ResponseInterface')
            )
        );
    }

    public function testResolutionPostShortCircuit()
    {
        $result = $this->getMock('BaconAuthentication\Result\ResultInterface');
        $result->expects($this->any())
               ->method('isSuccess')
               ->will($this->returnValue(true));
        $result->expects($this->any())
               ->method('getPayload')
               ->will($this->returnValue(1));

        $service = new PluggableAuthenticationService();
        $service->getEventManager()->attach(
            'authenticate.post',
            function () use ($result) {
                return $result;
            }
        );

        $expectedResult = $this->getMock('BaconAuthentication\Result\ResultInterface');

        $service->getEventManager()->attach(
            'resolve.post',
            function () use ($expectedResult) {
                return $expectedResult;
            }
        );

        $this->assertSame(
            $expectedResult,
            $service->authenticate(
                $this->getMock('Zend\Stdlib\RequestInterface'),
                $this->getMock('Zend\Stdlib\ResponseInterface')
            )
        );
    }

    public function testResetCredentials()
    {
        $request = $this->getMock('Zend\Stdlib\RequestInterface');

        $resetPlugin = $this->getMock('BaconAuthentication\Plugin\ResetPluginInterface');
        $resetPlugin->expects($this->once())
                    ->method('resetCredentials')
                    ->with($this->equalTo($request));

        $service = new PluggableAuthenticationService();
        $this->assertSame($service, $service->addPlugin($resetPlugin));

        $service->resetCredentials($request);
    }
}

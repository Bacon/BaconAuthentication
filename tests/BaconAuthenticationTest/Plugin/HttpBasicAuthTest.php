<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthenticationTest\Plugin;

use BaconAuthentication\Plugin\HttpBasicAuth;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers BaconAuthentication\Plugin\HttpBasicAuth
 */
class HttpBasicAuthTest extends TestCase
{
    public function testExtractionWithIncompatibleRequest()
    {
        $plugin      = new HttpBasicAuth();
        $credentials = $plugin->extractCredentials(
            $this->getMock('Zend\Stdlib\RequestInterface'),
            $this->getMock('Zend\Stdlib\ResponseInterface')
        );

        $this->assertNull($credentials);
    }

    public function testExtractionWithoutCredentials()
    {
        $plugin      = new HttpBasicAuth();
        $credentials = $plugin->extractCredentials(
            $this->getMock('Zend\Http\PhpEnvironment\Request'),
            $this->getMock('Zend\Stdlib\ResponseInterface')
        );

        $this->assertNull($credentials);
    }

    public function testExtractionWithCredentials()
    {
        $request = $this->getMock('Zend\Http\PhpEnvironment\Request');
        $request->expects($this->any())
                ->method('getServer')
                ->will($this->returnCallback(function ($name) {
                    if ($name === 'PHP_AUTH_USER') {
                        return 'foo';
                    } elseif ($name === 'PHP_AUTH_PW') {
                        return 'bar';
                    }
                }));

        $plugin      = new HttpBasicAuth();
        $credentials = $plugin->extractCredentials(
            $request,
            $this->getMock('Zend\Stdlib\ResponseInterface')
        );

        $this->assertInstanceOf('Zend\Stdlib\ParametersInterface', $credentials);
        $this->assertEquals('foo', $credentials->get('identity'));
        $this->assertEquals('bar', $credentials->get('password'));
    }

    public function testChallengeWithIncompatibleResponse()
    {
        $plugin    = new HttpBasicAuth();
        $challenge = $plugin->challenge(
            $this->getMock('Zend\Stdlib\RequestInterface'),
            $this->getMock('Zend\Stdlib\ResponseInterface')
        );

        $this->assertFalse($challenge);
    }

    public function testChallengeWithCompatibleResponse()
    {
        $headers = $this->getMock('Zend\Http\Headers');
        $headers->expects($this->once())
                ->method('addHeaderLine')
                ->with(
                    $this->equalTo('WWW-Authenticate'),
                    $this->equalTo('Basic realm="BaconAuthentication"')
                );

        $response = $this->getMock('Zend\Http\Response');
        $response->expects($this->once())
                 ->method('getHeaders')
                 ->will($this->returnValue($headers));
        $response->expects($this->once())
                 ->method('setStatusCode')
                 ->with($this->equalTo(401));

        $plugin    = new HttpBasicAuth();
        $challenge = $plugin->challenge(
            $this->getMock('Zend\Stdlib\RequestInterface'),
            $response
        );

        $this->assertTrue($challenge);
    }

    public function testChallengeWithCustomRealm()
    {
        $headers = $this->getMock('Zend\Http\Headers');
        $headers->expects($this->once())
                ->method('addHeaderLine')
                ->with(
                    $this->equalTo('WWW-Authenticate'),
                    $this->equalTo('Basic realm="foo\\"baz"')
                );

        $response = $this->getMock('Zend\Http\Response');
        $response->expects($this->once())
                 ->method('getHeaders')
                 ->will($this->returnValue($headers));
        $response->expects($this->once())
                 ->method('setStatusCode')
                 ->with($this->equalTo(401));

        $plugin = new HttpBasicAuth();
        $plugin->setRealm('foo"baz');

        $challenge = $plugin->challenge(
            $this->getMock('Zend\Stdlib\RequestInterface'),
            $response
        );

        $this->assertTrue($challenge);
    }
}

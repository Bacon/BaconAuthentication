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
        $credentials = $plugin->extractCredentials($this->getMock('Zend\Http\PhpEnvironment\Request'));

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
        $credentials = $plugin->extractCredentials($request);

        $this->assertInstanceOf('Zend\Stdlib\ParametersInterface', $credentials);
        $this->assertEquals('foo', $credentials->get('identity'));
        $this->assertEquals('bar', $credentials->get('password'));
    }

    public function testChallengeWithIncompatibleRequest()
    {
        $plugin    = new HttpBasicAuth();
        $challenge = $plugin->challenge(
            $this->getMock('Zend\Stdlib\RequestInterface')
        );

        $this->assertNull($challenge);
    }

    public function testChallengeWithCompatibleRequest()
    {
        $plugin  = new HttpBasicAuth();
        $request = $this->getMock('Zend\Http\PhpEnvironment\Request');

        /** @var \Zend\Http\Response $challenge */
        $challenge = $plugin->challenge($request);

        $this->assertInstanceOf('Zend\Http\Response', $challenge);
        $this->assertEquals(401, $challenge->getStatusCode());

        $header = $challenge->getHeaders()->get('WWW-Authenticate');
        $this->assertEquals($header[0]->getFieldValue(), 'Basic realm="BaconAuthentication"');
    }

    public function testChallengeWithCustomRealm()
    {
        $plugin = new HttpBasicAuth();
        $plugin->setRealm('foo"baz');

        $request = $this->getMock('Zend\Http\PhpEnvironment\Request');

        /** @var \Zend\Http\Response $challenge */
        $challenge = $plugin->challenge($request);

        $this->assertInstanceOf('Zend\Http\Response', $challenge);
        $this->assertEquals(401, $challenge->getStatusCode());

        $header = $challenge->getHeaders()->get('WWW-Authenticate');
        $this->assertEquals($header[0]->getFieldValue(), 'Basic realm="foo\"baz"');
    }
}

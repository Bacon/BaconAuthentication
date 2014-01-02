<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthenticationTest\Plugin;

use BaconAuthentication\Plugin\HttpPost;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers BaconAuthentication\Plugin\HttpPost
 */
class HttpPostTest extends TestCase
{
    public function testExtractionWithIncompatibleRequest()
    {
        $plugin      = new HttpPost(null);
        $credentials = $plugin->extractCredentials(
            $this->getMock('Zend\Stdlib\RequestInterface'),
            $this->getMock('Zend\Stdlib\ResponseInterface')
        );

        $this->assertNull($credentials);
    }

    public function testExtractionWithoutCredentials()
    {
        $plugin      = new HttpPost(null);
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
                ->method('getPost')
                ->will($this->returnCallback(function ($name) {
                    if ($name === 'identity') {
                        return 'foo';
                    } elseif ($name === 'password') {
                        return 'bar';
                    }
                }));

        $plugin      = new HttpPost(null);
        $credentials = $plugin->extractCredentials($request);

        $this->assertInstanceOf('Zend\Stdlib\ParametersInterface', $credentials);
        $this->assertEquals('foo', $credentials->get('identity'));
        $this->assertEquals('bar', $credentials->get('password'));
    }

    public function testExtractionWithDifferentFieldNames()
    {
        $request = $this->getMock('Zend\Http\PhpEnvironment\Request');
        $request->expects($this->any())
                ->method('getPost')
                ->will($this->returnCallback(function ($name) {
                    if ($name === 'username') {
                        return 'foo';
                    } elseif ($name === 'secret') {
                        return 'bar';
                    }
                }));

        $plugin = new HttpPost(null);
        $this->assertSame($plugin, $plugin->setIdentityField('username'));
        $this->assertSame($plugin, $plugin->setPasswordField('secret'));

        $credentials = $plugin->extractCredentials($request);

        $this->assertInstanceOf('Zend\Stdlib\ParametersInterface', $credentials);
        $this->assertEquals('foo', $credentials->get('identity'));
        $this->assertEquals('bar', $credentials->get('password'));
    }

    public function testExtractionWithInputFilter()
    {
        $request = $this->getMock('Zend\Http\PhpEnvironment\Request');
        $request->expects($this->any())
                ->method('getPost')
                ->will($this->returnCallback(function ($name) {
                    if ($name === 'identity') {
                        return 'foo';
                    } elseif ($name === 'password') {
                        return 'bar';
                    }
                }));

        $inputFilter = $this->getMock('Zend\InputFilter\InputFilterInterface');
        $inputFilter->expects($this->once())
                    ->method('setData')
                    ->with($this->equalTo(array('identity' => 'foo', 'password' => 'bar')));
        $inputFilter->expects($this->once())
                    ->method('isValid')
                    ->will($this->returnValue(true));
        $inputFilter->expects($this->once())
                    ->method('getValues')
                    ->will($this->returnValue(array('identity' => 'baz', 'password' => 'bat')));

        $plugin = new HttpPost(null);
        $this->assertSame($plugin, $plugin->setInputFilter($inputFilter));

        $credentials = $plugin->extractCredentials($request);

        $this->assertInstanceOf('Zend\Stdlib\ParametersInterface', $credentials);
        $this->assertEquals('baz', $credentials->get('identity'));
        $this->assertEquals('bat', $credentials->get('password'));
    }

    public function testExtractionWithInputFilterAndInvalidInput()
    {
        $request = $this->getMock('Zend\Http\PhpEnvironment\Request');
        $request->expects($this->any())
                ->method('getPost')
                ->will($this->returnCallback(function ($name) {
                    if ($name === 'identity') {
                        return 'foo';
                    } elseif ($name === 'password') {
                        return 'bar';
                    }
                }));

        $inputFilter = $this->getMock('Zend\InputFilter\InputFilterInterface');
        $inputFilter->expects($this->once())
                    ->method('isValid')
                    ->will($this->returnValue(false));

        $plugin = new HttpPost(null);
        $this->assertSame($plugin, $plugin->setInputFilter($inputFilter));

        $credentials = $plugin->extractCredentials($request);

        $this->assertInstanceOf('BaconAuthentication\Result\ResultInterface', $credentials);
        $this->assertTrue($credentials->isFailure());
        $this->assertInstanceOf('BaconAuthentication\Result\Error', $credentials->getPayload());
        $this->assertEquals('BaconAuthentication\Plugin\HttpPost', $credentials->getPayload()->getScope());
        $this->assertEquals('InputFilter validation failed', $credentials->getPayload()->getMessage());
    }

    public function testChallengeWithIncompatibleResponse()
    {
        $plugin    = new HttpPost(null);
        $challenge = $plugin->challenge(
            $this->getMock('Zend\Stdlib\RequestInterface'),
            $this->getMock('Zend\Stdlib\ResponseInterface')
        );

        $this->assertNull($challenge);
    }

    public function testChallengeWithCompatibleResponse()
    {
        $plugin    = new HttpPost('/login');
        /** @var \Zend\Http\Response $challenge */
        $challenge = $plugin->challenge($this->getMock('Zend\Http\PhpEnvironment\Request'));

        $this->assertInstanceOf('Zend\Http\Response', $challenge);
        $this->assertEquals(302, $challenge->getStatusCode());
        $this->assertEquals('/login', $challenge->getHeaders()->get('location')->getFieldValue());
    }
}

<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthenticationTest;

use BaconAuthentication\AuthenticationEvent;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers BaconAuthentication\AuthenticationEvent
 */
class AuthenticationEventTest extends TestCase
{
    public static function setterGetterProvider()
    {
        return array(
            array(
                'Request',
                'Zend\Stdlib\RequestInterface',
            ),
            array(
                'Result',
                'BaconAuthentication\Result\ResultInterface',
            ),
        );
    }

    /**
     * @dataProvider setterGetterProvider
     * @param        string $name
     * @param        string $interfaceName
     */
    public function testSetterGetter($name, $interfaceName)
    {
        $value = $this->getMock($interfaceName);
        $event = new AuthenticationEvent();

        $this->assertNull($event->{'get' . $name}());
        $this->assertSame($event, $event->{'set' . $name}($value));
        $this->assertSame($value, $event->{'get' . $name}());
    }
}

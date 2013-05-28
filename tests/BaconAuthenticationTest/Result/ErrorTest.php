<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthenticationTest\Result;

use BaconAuthentication\Result\Error;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers BaconAuthentication\Result\Error
 */
class ErrorTest extends TestCase
{
    public function testErrorCreation()
    {
        $error = new Error('foo', 'bar');

        $this->assertEquals('foo', $error->getScope());
        $this->assertEquals('bar', $error->getMessage());
    }
}

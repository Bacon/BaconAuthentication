<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthenticationTest;

use BaconAuthentication\Result\Result;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * @covers BaconAuthentication\Result\Result
 */
class ResultTest extends TestCase
{
    public static function stateProvider()
    {
        return array(
            array(
                Result::STATE_SUCCESS,
                array(
                    'isSuccess'   => true,
                    'isFailure'   => false,
                    'isChallenge' => false,
                ),
            ),
            array(
                Result::STATE_FAILURE,
                array(
                    'isSuccess'   => false,
                    'isFailure'   => true,
                    'isChallenge' => false,
                ),
            ),
            array(
                Result::STATE_CHALLENGE,
                array(
                    'isSuccess'   => false,
                    'isFailure'   => false,
                    'isChallenge' => true,
                ),
            ),
        );
    }

    /**
     * @dataProvider stateProvider
     * @param        string $state
     * @param        array  $methodResults
     */
    public function testResultStates($state, array $methodResults)
    {
        $result = new Result($state);

        foreach ($methodResults as $methodName => $expected) {
            $this->assertEquals($expected, $result->{$methodName}());
        }
    }

    public function testInvalidState()
    {
        $this->setExpectedException(
            'BaconAuthentication\Exception\InvalidArgumentException',
            'foobar is not a valid state'
        );

        new Result('foobar');
    }

    public function testPayload()
    {
        $result = new Result(Result::STATE_SUCCESS, 'foobar');
        $this->assertEquals('foobar', $result->getPayload());
    }
}

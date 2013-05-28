<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication\Result;

/**
 * Generic result interface.
 */
interface ResultInterface
{
    /**
     * Returns whether the authentication was successful.
     *
     * @return bool
     */
    public function isSuccess();

    /**
     * Returns whether the authentication was a failure.
     *
     * @return bool
     */
    public function isFailure();

    /**
     * Returns whether the authentication generated a challenge.
     *
     * @return bool
     */
    public function isChallenge();

    /**
     * Returns the payload associated with the result.
     *
     * For a successful result, the payload should be the identity of the
     * subject. In the case of a failure, it should contain error information.
     * For a challenge, no payload is required.
     *
     * @return mixed|null
     */
    public function getPayload();
}

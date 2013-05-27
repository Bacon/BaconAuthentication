<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication\Result;

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
     * Returns the identity of the subject.
     *
     * If the authentication was successful, the resolved identity of the
     * subject will be returned, null otherwise.
     *
     * @return int|float|string|null
     */
    public function getIdentity();

    /**
     * Returns the error message if any.
     *
     * @return string|null
     */
    public function getError();
}

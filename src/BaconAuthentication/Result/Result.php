<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication\Result;

class Result implements ResultInterface
{
    const STATE_SUCCESS   = 'success';
    const STATE_FAILURE   = 'failure';
    const STATE_CHALLENGE = 'challenge';

    /**
     * @var string
     */
    protected $state;

    /**
     * @var int|float|string|null
     */
    protected $identity;

    /**
     * @var string|null
     */
    protected $error;

    /**
     * @param string                $state
     * @param int|float|string|null $identity
     * @param string|null           $error
     */
    public function __construct($state, $identity = null, $error = null)
    {
        $this->state    = $state;
        $this->identity = $identity;
        $this->message  = $message;
    }

    /**
     * isSuccess(): defined by ResultInterface.
     *
     * @see    ResultInterface::isSuccess()
     * @return bool
     */
    public function isSuccess()
    {
        return $this->state === self::STATE_SUCCESS;
    }

    /**
     * isFailure(): defined by ResultInterface.
     *
     * @see    ResultInterface::isFailure()
     * @return bool
     */
    public function isFailure()
    {
        return $this->state === self::STATE_FAILURE;
    }

    /**
     * isChallenge(): defined by ResultInterface.
     *
     * @see    ResultInterface::isChallenge()
     * @return bool
     */
    public function isChallenge()
    {
        return $this->state === self::STATE_CHALLENGE;
    }

    /**
     * getIdentity(): defined by ResultInterface.
     *
     * @see    ResultInterface::getIdentity()
     * @return int|float|string|null
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * getError(): defined by ResultInterface.
     *
     * @see    ResultInterface::getError()
     * @return string|null
     */
    public function getError();
}

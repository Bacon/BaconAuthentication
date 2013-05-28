<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication\Result;

use BaconAuthentication\Exception;

/**
 * Generic result implementation.
 */
class Result implements ResultInterface
{
    /**
     * @see ResultInterface::isSuccess()
     */
    const STATE_SUCCESS = 'success';

    /**
     * @see ResultInterface::isFailure()
     */
    const STATE_FAILURE = 'failure';

    /**
     * @see ResultInterface::isChallenge()
     */
    const STATE_CHALLENGE = 'challenge';

    /**
     * @var array
     */
    protected static $allowedStates = array('success', 'failure', 'challenge');

    /**
     * @var string
     */
    protected $state;

    /**
     * @var mixed|null
     */
    protected $payload;

    /**
     * @param string     $state
     * @param mixed|null $payload
     */
    public function __construct($state, $payload = null)
    {
        if (!in_array($state, self::$allowedStates)) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s is not a valid state',
                $state
            ));
        }

        $this->state   = $state;
        $this->payload = $payload;
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
     * getPayload(): defined by ResultInterface.
     *
     * @see    ResultInterface::getPayload()
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }
}

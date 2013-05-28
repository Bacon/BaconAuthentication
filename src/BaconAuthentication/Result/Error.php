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
 * Error information container.
 */
class Error
{
    /**
     * @var string
     */
    protected $scope;

    /**
     * @var string
     */
    protected $message;

    /**
     * @param string $scope
     * @param string $message
     */
    public function __construct($scope, $message)
    {
        $this->scope   = $scope;
        $this->message = $message;
    }

    /**
     * Returns the scope of the error.
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Returns the error message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}

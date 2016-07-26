<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication;

use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

/**
 * Generic authentication service interface.
 */
interface AuthenticationServiceInterface
{
    /**
     * Authenticates a request.
     *
     * @param  RequestInterface  $request
     * @return Result\ResultInterface
     */
    public function authenticate(RequestInterface $request);

    /**
     * Resets all credentials to anonymous state.
     *
     * @param  RequestInterface $request
     * @return void
     */
    public function resetCredentials(RequestInterface $request);
}

<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication\Plugin;

use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

/**
 * Plugin interface for challenge generation.
 */
interface ChallengePluginInterface
{
    /**
     * Creates a challenge if the response object is compatible.
     *
     * If the response can be modified to trigger a challenge, true should be
     * returned, false otherwise.
     *
     * @param  RequestInterface $request
     *
     * @return false|\Zend\Stdlib\ResponseInterface
     */
    public function challenge(RequestInterface $request);
}

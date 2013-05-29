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

/**
 * Plugin interface for credential resets.
 */
interface ResetPluginInterface
{
    /**
     * Resets credentials to anyonymous state.
     *
     * @param  RequestInterface $request
     * @return void
     */
    public function resetCredentials(RequestInterface $request);
}

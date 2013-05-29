<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication\Plugin;

use Zend\Stdlib\ParametersInterface;

/**
 * Plugin interface for credential authentication.
 */
interface AuthenticationPluginInterface
{
    /**
     * Tries to authenticate the user with the given credentials.
     *
     * @param  ParametersInterface $credentials
     * @return null
     */
    public function authenticateCredentials(ParametersInterface $credentials);
}

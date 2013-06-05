<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication\Plugin;

/**
 * Plugin interface for subject resolution.
 */
interface ResolutionPluginInterface
{
    /**
     * Tries to resolve a subject from an identifier.
     *
     * @param  int|float|string $identifier
     * @return mixed|null
     */
    public function resolveSubject($identifier);
}

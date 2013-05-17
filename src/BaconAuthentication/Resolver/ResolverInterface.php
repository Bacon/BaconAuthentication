<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication\Resolver;

/**
 * Resolver interface.
 */
interface ResolverInterface
{
    /**
     * Resolves an identity to a principal.
     *
     * @param  int|float|string $identity
     * @return null|mixed
     */
    public function resolve($identity);
}

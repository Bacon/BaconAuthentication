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

interface ExtractionPluginInterface
{
    /**
     * Extracts credentials from a request.
     *
     * If credentials cannot be extracted, the method should return null,
     * a parameters object otheriwse.
     *
     * @param  RequestInterface $request
     * @return null|\Zend\Stdlib\ParametersInterface
     */
    public function extractCredentials(RequestInterface $request);
}

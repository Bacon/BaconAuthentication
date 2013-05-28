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

interface ExtractionPluginInterface
{
    /**
     * Extracts credentials from a request.
     *
     * If credentials cannot be extracted, the method should return null,
     * a parameters object otheriwse. To short-circuit the authentication
     * process and issue a challenge, a Result may be returned as well.
     *
     * @param  RequestInterface  $request
     * @param  ResponseInterface $response
     * @return null|\BaconAuthentication\Result\ResultInterface|\Zend\Stdlib\ParametersInterface
     */
    public function extractCredentials(RequestInterface $request, ResponseInterface $response);
}

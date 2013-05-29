<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication\Plugin;

use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use Zend\Stdlib\Parameters;

/**
 * Identity/Password extraction plugin for HTTP basic auth.
 */
class HttpBasicAuth implements
    ChallengePluginInterface,
    ExtractionPluginInterface
{
    /**
     * @var string
     */
    protected $realm = 'BaconAuthentication';

    /**
     * Sets the realm send with the challenge.
     *
     * @param  string $realm
     * @return void
     */
    public function setRealm($realm)
    {
        $this->realm = (string) $realm;
    }

    /**
     * extractCredentials(): defined by ExtractionPluginInterface.
     *
     * @see    ExtractionPluginInterface::extractCredentials()
     * @param  RequestInterface $request
     * @param  ResponseInterface $response
     * @return null|Parameters
     */
    public function extractCredentials(RequestInterface $request, ResponseInterface $response)
    {
        if (!$request instanceof HttpRequest) {
            return null;
        }

        $identity = $request->getServer('PHP_AUTH_USER');
        $password = $request->getServer('PHP_AUTH_PW');

        if ($identity === null || $password === null) {
            return null;
        }

        return new Parameters(array(
            'identity' => $identity,
            'password' => $password,
        ));
    }

    /**
     * challenge(): defined by ChallengePluginInterface.
     *
     * @see    ChallengePluginInterface::challenge()
     * @param  RequestInterface  $request
     * @param  ResponseInterface $response
     * @return bool
     */
    public function challenge(RequestInterface $request, ResponseInterface $response)
    {
        if (!$response instanceof HttpResponse) {
            return false;
        }

        $response->getHeaders()->addHeaderLine(
            'WWW-Authenticate',
            'Basic realm="' . addslashes($this->realm) . '"'
        );
        $response->setStatusCode(401);

        return true;
    }
}

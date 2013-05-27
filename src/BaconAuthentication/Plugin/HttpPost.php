<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication\Plugin;

use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use Zend\Stdlib\Parameters;

class HttpPost implements
    ChallengePluginInterface,
    ExtractionPluginInterface
{
    /**
     * @var string
     */
    protected $loginFormUrl;

    /**
     * @var string
     */
    protected $identityField = 'identity';

    /**
     * @var string
     */
    protected $passwordField = 'password';

    /**
     * @param string $loginFormUrl
     */
    public function __construct($loginFormUrl)
    {
        $this->loginFormUrl = $loginFormUrl;
    }

    /**
     * Sets the POST name of the identity field.
     *
     * @param  string $identityField
     * @return void
     */
    public function setIdentityField($identityField)
    {
        $this->identityField = (string) $identityField;
    }

    /**
     * Sets the POST name of the password field.
     *
     * @param  string $passwordField
     * @return void
     */
    public function setPasswordField($passwordField)
    {
        $this->passwordField = (string) $passwordField;
    }

    /**
     * extractCredentials(): defined by ExtractionPluginInterface.
     *
     * @see    ExtractionPluginInterface::extractCredentials()
     * @param  RequestInterface $request
     * @return null|Parameters
     */
    public function extractCredentials(RequestInterface $request)
    {
        if (!$request instanceof HttpRequest) {
            return null;
        }

        $identity = $request->getPost($this->identityField);
        $password = $request->getPost($this->passwordField);

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
            'Location',
            $this->loginFormUrl
        );
        $response->setStatusCode(302);

        return true;
    }
}

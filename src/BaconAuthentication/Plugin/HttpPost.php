<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication\Plugin;

use BaconAuthentication\Result\Error;
use BaconAuthentication\Result\Result;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\InputFilter\InputFilterInterface;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use Zend\Stdlib\Parameters;

/**
 * Identity/Password extraction plugin for HTTP POST requests.
 */
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
     * @var InputFilterInterface
     */
    protected $inputFilter;

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
     * @return HttpPost
     */
    public function setIdentityField($identityField)
    {
        $this->identityField = (string) $identityField;
        return $this;
    }

    /**
     * Sets the POST name of the password field.
     *
     * @param  string $passwordField
     * @return HttpPost
     */
    public function setPasswordField($passwordField)
    {
        $this->passwordField = (string) $passwordField;
        return $this;
    }

    /**
     * Sets an input filter for retreiving credentials.
     *
     * @param  InputFilterInterface $inputFilter
     * @return HttpPost
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
        return $this;
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

        $identity = $request->getPost($this->identityField);
        $password = $request->getPost($this->passwordField);

        if ($identity === null || $password === null) {
            return null;
        }

        if ($this->inputFilter !== null) {
            $this->inputFilter->setData(array(
                $this->identityField => $identity,
                $this->passwordField => $password,
            ));

            if (!$this->inputFilter->isValid()) {
                return new Result(
                    Result::STATE_FAILURE,
                    new Error(__CLASS__, 'InputFilter validation failed')
                );
            }

            $values   = $this->inputFilter->getValues();
            $identity = $values[$this->identityField];
            $password = $values[$this->passwordField];
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

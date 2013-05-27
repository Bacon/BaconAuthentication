<?php
/**
 * BaconAuthentication
 *
 * @link      http://github.com/Bacon/BaconAuthentication For the canonical source repository
 * @copyright 2013 Ben Scholzen 'DASPRiD'
 * @license   http://opensource.org/licenses/BSD-2-Clause Simplified BSD License
 */

namespace BaconAuthentication;

use BaconAuthentication\Result\ResultInterface;
use Zend\EventManager\Event;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;

class AuthenticationEvent extends Event
{
    const EVENT_AUTHENTICATE_PRE  = 'authenticate.pre';
    const EVENT_AUTHENTICATE_POST = 'authenticate.post';

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var ResultInterface|null
     */
    protected $result;

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param  RequestInterface $request
     * @return AuthenticationEvent
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param  ResponseInterface $response
     * @return AuthenticationEvent
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return ResultInterface|null
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param  ResultInterface $result
     * @return AuthenticationEvent
     */
    public function setResult(ResultInterface $result)
    {
        $this->result = $result;
        return $this;
    }
}

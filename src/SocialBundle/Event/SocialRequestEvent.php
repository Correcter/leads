<?php

namespace SocialBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Vitaly Dergunov (<v.dergunov@icontext.ru>)
 */
class SocialRequestEvent extends Event
{
    /**
     * @var array
     */
    private $response;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var string
     */
    private $listener;

    /**
     * SocialRequestEvent constructor.
     *
     * @param Request $request
     * @param $listener
     */
    public function __construct(Request $request, $listener)
    {
        $this->request = $request;
        $this->listener = $listener;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return bool
     */
    public function hasResponse(): bool
    {
        return ($this->response) ? true : false;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return $listener|string
     */
    public function getListener(): string
    {
        return $this->listener;
    }
}

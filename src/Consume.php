<?php

namespace Teepluss\Consume;

use InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Container\Container;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Consume
{
    /**
     * Application Container.
     */
    protected $container;

    /**
     * Http request.
     */
    protected $request;

    /**
     * Router.
     */
    protected $router;

    /**
     * HTTP method.
     */
    protected $method = 'GET';

    /**
     * URI path.
     */
    protected $path;

    /**
     * HTTP headers.
     */
    protected $headers = [];

    /**
     * POST parameters.
     */
    protected $parameters = [];

    /**
     * Response from dispatch.
     */
    protected $dispatch;

    /**
     * Constructor.
     *
     * @param Container $container
     * @param Request   $request
     * @param Router    $router
     */
    public function __construct(Container $container, Request $request, Router $router)
    {
        $this->container = $container;
        $this->request = $request;
        $this->router = $router;
    }

    /**
     * Illuminate HTTP request.
     *
     * @param  string $method
     * @param  string $path
     * @param  array  $parameters
     * @return \Teepluss\Consume\Consume
     */
    public function request($method, $path, array $parameters = [])
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->parameters = $this->parseParameters($parameters);

        return $this;
    }

    /**
     * Add header with request.
     *
     * @param  string $key
     * @param  string $value
     * @return \Teepluss\Consume\Consume
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Add authorization header.
     *
     * @param  string $accessToken
     * @param  string $type
     * @return \Teepluss\Consume\Consume
     */
    public function withAccessToken($accessToken, $type = 'Bearer')
    {
        $authorization = $type.' '.$accessToken;
        $this->addHeader('Authorization', $authorization);

        return $this;
    }

    /**
     * Add header Json.
     *
     * @return \Teepluss\Consume\Consume
     */
    public function asJson()
    {
        $this->addHeader('Content-Type', 'application/json')
             ->addHeader('Accept', 'application/json');

        return $this;
    }

    /**
     * Parse request parameters.
     *
     * @param  array $parameters
     * @return array
     */
    protected function parseParameters(array $parameters)
    {
        if (count($parameters)) {
            foreach ($parameters as $key => $value) {
                if ($value instanceof UploadedFile) {
                    $params['files'][$key] = $value;
                } else {
                    $params['form_params'][$key] = $value;
                }
            }
        }
        return $params;
    }

    /**
     * Send the request to URI.
     *
     * @return \Teepluss\Consume\Consume
     */
    public function send()
    {
        $payload = '';
        $originalInput = $this->request->input();
        $originalFile = $this->request->file();

        // Parameters to send with POST\PUT
        $parameters = [];
        if (isset($this->parameters['form_params'])) {
            $this->addHeader('Content-Type', 'application/x-www-form-urlencoded');
            $parameters = $this->parameters['form_params'];
        }

        // File uploader.
        $files = [];
        if (isset($this->parameters['files'])) {
            $this->addHeader('Content-Type', 'multipart/form-data');
            $files = $this->parameters['files'];
        }

        // Create a request and replace current request.
        $request = $this->request->create($this->path, $this->method, $parameters, [], $files, [], $payload);
        $this->request->replace($request->input());
        $this->request->files->replace($request->file());

        // Add HTTP headers.
        $headers = $this->router->getCurrentRequest()->headers;
        foreach ($this->headers as $key => $val) {
            $headers->set($key, $val);
        }

        try {
            $dispatch = $this->router->dispatch($request);
            $this->dispatch = $dispatch;
        } catch (NotFoundHttpException $e) {
            throw new Exception\NotFoundException('Not found.');
        }

        // Restore request.
        $this->request->replace($originalInput);
        $this->request->files->replace($originalFile);

        return $this;
    }

    /**
     * Get response from dispatch.
     *
     * @return Object
     */
    public function getDispatchResponse()
    {
        return $this->dispatch;
    }

    /**
     * Get status code from dispatch.
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->getDispatchResponse()
                    ->getStatusCode();
    }

    /**
     * Get content from dispatch.
     *
     * @return mixed
     */
    public function getContent()
    {
        $response = null;
        $dispatch = $this->getDispatchResponse();
        if ($dispatch instanceof \Illuminate\Http\JsonResponse) {
            $response = $dispatch->getData();
        } else {
            $response = $dispatch->getOriginalContent();
        }

        // Exception request.
        if ($dispatch->isClientError()) {
            $statusCode = $dispatch->getStatusCode();
            $exception = new Exception\ErrorException('Something is wrong!', $statusCode);
            $exception->setContent($response);
            throw $exception;
        }

        return $response;
    }
}

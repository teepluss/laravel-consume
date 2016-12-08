<?php

namespace Teepluss\Consume;

use Exception;
use InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\Http\UploadedFile;
use Illuminate\Contracts\Container\Container;

class Consume
{
    protected $container;
    protected $request;
    protected $router;
    protected $method = 'GET';
    protected $path;
    protected $headers = [];
    protected $parameters = [];

    public function __construct(Container $container, Request $request, Router $router)
    {
        $this->container = $container;
        $this->request = $request;
        $this->router = $router;
    }

    public function request($method, $path, array $parameters = [])
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->parameters = $this->parseParameters($parameters);

        return $this;
    }

    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    protected function parseParameters($parameters)
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
     * Send reuest.
     *
     * @return string
     */
    public function send()
    {
        // $resp = $consume->addHeader('Authorization', $authorization)
        //                 ->addHeader('Content-Type', 'application/json')
        //                 ->addHeader('Accept', 'application/json')
        //                 ->request('POST', '/api/user', [
        //                     'name' => 'Someone',
        //                     'ux' => $userfile
        //                 ])
        //                 ->send();

        // dump($request->get('name'));
        // dump($request->files->get('ux'));

        $payload = '';
        $originalInput = $this->request->input();
        $originalFile = $this->request->file();

        $parameters = [];
        if (isset($this->parameters['form_params'])) {
            $this->addHeader('Content-Type', 'application/x-www-form-urlencoded');
            $parameters = $this->parameters['form_params'];
        }

        $files = [];
        if (isset($this->parameters['files'])) {
            $this->addHeader('Content-Type', 'multipart/form-data');
            $files = $this->parameters['files'];
        }

        $request = $this->request->create($this->path, $this->method, $parameters, [], $files, [], $payload);
        $this->request->replace($request->input());
        $this->request->files->replace($request->file());

        // Add headers.
        $headers = $this->router->getCurrentRequest()->headers;
        foreach ($this->headers as $key => $val) {
            $headers->set($key, $val);
        }

        $dispatch = $this->router->dispatch($request);
        if (method_exists($dispatch, 'getOriginalContent')) {
            $response = $dispatch->getOriginalContent();
        } else {
            $response = $dispatch->getContent();
        }

        // Restore request.
        $this->request->replace($originalInput);
        $this->request->files->replace($originalFile);

        return json_decode($response, true);
    }
}

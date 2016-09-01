<?php

namespace MrCat\SugarCrmWrapper;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

class Request
{
    /**
     * Request parameters
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Default parameter
     *
     * @var array
     */
    private $default = [
        'timeout' => [
            'type' => 'double',
            'value' => 3.0,
        ],
        'method' => [
            'type' => 'string',
            'value' => 'POST',
        ],
        'uri' => [
            'type' => 'string',
            'value' => '',
        ],
        'base_uri' => [
            'type' => 'string',
            'value' => '',
        ],
        'form_params' => [
            'type' => 'array',
            'value' => [],
        ],
    ];

    /**
     * Instance new Client.
     *
     * @return \GuzzleHttp\Client
     */
    public function newClient()
    {
        return new Client([
            'base_uri' => $this->parameters['base_uri'],
            'timeout' => $this->parameters['timeout'],
            'cookies' => true,
        ]);
    }

    /**
     * Request Api.
     *
     * @param $method
     *
     * @return string JSON
     */
    public function call($method)
    {
        $response = $this->newClient()
            ->request(
                $this->parameters['method'],
                $this->parameters['uri'],
                [
                    'form_params' => [
                        'method' => $method,
                        'input_type' => 'JSON',
                        'response_type' => 'JSON',
                        'rest_data' => json_encode($this->parameters['form_params']),
                    ],
                ]);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * Instance New Request.
     *
     * @param $method
     * @param array $parameters
     *
     * @return array
     */
    public static function send($method, array $parameters)
    {
        $instance = new static($parameters);

        return $instance->call($method);
    }

    /**
     * Validates the keys of the parameters.
     *
     * @param array $parameters
     *
     * @throws \Exception
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        foreach ($this->default as $default => $value) {
            if (!array_key_exists($default, $this->parameters)) {
                $this->parameters[$default] = $value['value'];
            }

            if (gettype($this->parameters[$default]) !== $value['type']) {
                throw new SugarCrmWrapperException('Parameter Type Invalid' . $default);
            }
        }
    }

    /**
     * Request constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->setParameters($parameters);
    }
}

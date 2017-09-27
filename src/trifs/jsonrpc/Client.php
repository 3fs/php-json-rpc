<?php
namespace trifs\jsonrpc;

use trifs\jsonrpc\Client\Transporter\TransporterInterface;
use trifs\jsonrpc\Client\Transporter\DefaultTransporter;
use trifs\jsonrpc\Client\Transporter\Exception as TransporterException;

class Client
{
    /**
     * Holds list of requests to be sent.
     *
     * @var array
     */
    private $requests = [];

    /**
     * Holds endpoint URI.
     *
     * @var string
     */
    private $uri;

    /**
     * Holds timeout value in seconds
     *
     * @var float
     */
    private $timeout;

    /**
     * Holds transporter.
     *
     * @var TransporterInterface
     */
    private $transporter;

    /**
     * Constructor, sets endpoint URI, options.
     *
     * @param  string               $uri
     * @param  array                $options Supported keys: timeout
     * @return void
     */
    public function __construct($uri, array $options = [])
    {
        $this->uri = $uri;

        $this->setTimeout(ini_get('default_socket_timeout'));

        if (isset($options['timeout'])) {
            $this->setTimeout($options['timeout']);
        }

        $this->setTransporter(new DefaultTransporter());
    }

    /**
     * Add a request.
     *
     * @see    http://www.jsonrpc.org/specification#request_object
     *
     * @param  string $method a method name
     * @param  array  $params an optional parameter list
     * @return Client
     */
    public function request($method, array $params = [])
    {
        return $this->addRequest($method, $params);
    }

    /**
     * Add a notification request.
     *
     * @see    http://www.jsonrpc.org/specification#notification
     *
     * @param  string $method a method name
     * @param  array  $params an optional parameter list
     * @return Client
     */
    public function notification($method, array $params = [])
    {
        return $this->addRequest($method, $params, true);
    }

    /**
     * Send a single or batch requests.
     *
     * @return mixed|false
     * @throws \RuntimeException if no requests have been defined or could not connect to endpoint
     */
    public function send()
    {
        if (empty($this->requests)) {
            throw new \RuntimeException('No requests have been set.');
        }
        // more than one, treat as batch request
        if (isset($this->requests[1])) {
            $payload = json_encode($this->requests);
        } else {
            $payload = json_encode($this->requests[0]);
        }

        // execute request with configured http transporter
        try {
            $response = $this->transporter->request(
                $this->uri,
                $payload,
                ['timeout' => $this->getTimeout()]
            );
        } catch (TransporterException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        // notification
        if (empty($response)) {
            return true;
        }

        // validate response and act accordingly
        if (null === $result = json_decode($response)) {
            throw new \RuntimeException('Unable to decode JSON: ' . $response);
        }

        $sanitizeResponse = function ($result) {
            if (empty($result->error)) {
                return $result->result;
            }
            return ['error' => $result->error];
        };

        if (is_array($result)) {
            $result = array_map($sanitizeResponse, $result);
        } else {
            $result = $sanitizeResponse($result);
        }

        return $result;
    }

    /**
     * Sets socket timeout
     *
     * @param  mixed $timeout
     * @return Client
     */
    public function setTimeout($timeout)
    {
        if ((float) $timeout <= 0.0) {
            throw new \RuntimeException('Timeout should be greater than 0');
        }
        $this->timeout = (float) $timeout;
        return $this;
    }

    /**
     * Get socket timeout.
     *
     * @return float
     */
    public function getTimeout()
    {
        return (float) $this->timeout;
    }

    /**
     * Adds a request to internal request list.
     *
     * @param  string  $method         a method name
     * @param  array   $params         optional list of parameters
     * @param  boolean $isNotification a flag indicating a request or a notification
     * @return Client
     */
    private function addRequest($method, array $params = [], $isNotification = false)
    {
        $this->requests[] = array_filter([
            'jsonrpc' => '2.0',
            'id'      => $isNotification ? null : $this->createId(),
            'method'  => $method,
            'params'  => $params,
        ]);
        return $this;
    }

    /**
     * Create a unique ID for each request.
     *
     * @see    https://tools.ietf.org/html/rfc4122 A Universally Unique IDentifier (UUID) URN Namespace
     *
     * @return string
     */
    private function createId()
    {
        $data    = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0010
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Set transporter.
     *
     * @param TransporterInterface $transporter
     * @return Client
     */
    public function setTransporter(TransporterInterface $transporter)
    {
        $this->transporter = $transporter;
        return $this;
    }
}

<?php
namespace trifs\jsonrpc\Server\Request;

use trifs\jsonrpc\Server;

class Request implements RequestInterface
{

    /**
     * Holds method name.
     *
     * @var string
     */
    private $method;

    /**
     * Holds request ID.
     *
     * @var mixed
     */
    private $id = null;

    /**
     * Holds optional parameters.
     *
     * @var array
     */
    private $params = [];

    /**
     * Holds JSON request object.
     *
     * @var \stdClass
     */
    private $json;

    /**
     * Constructor, sets JSON request.
     *
     * @param  array $json
     * @return void
     */
    public function __construct(array $json)
    {
        $this->json   = (object)$json;

        $this->id     = isset($json['id'])     ? $json['id']     : null;
        $this->method = isset($json['method']) ? $json['method'] : null;
        $this->params = isset($json['params']) ? $json['params'] : [];
    }

    /**
     * Returns a list of all available requests.
     *
     * @return array of RequestInterface
     */
    public function getRequests()
    {
        return [$this];
    }

    /**
     * Returns request's ID.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns request's method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns request's parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->params;
    }

    /**
     * Returns a boolean flag indicating whether a request is a batch or not.
     *
     * @return boolean
     */
    public function isBatch()
    {
        return false;
    }

    /**
     * Returns a boolean flag indicating whether a request is a notification or not.
     *
     * @return boolean
     */
    public function isNotification()
    {
        return false;
    }

    /**
     * Validates request.
     *
     * @return void
     * @throws \Exception if request is invalid
     */
    public function validate()
    {
        $code   = false;
        $id     = $this->id;
        $method = $this->method;
        $params = $this->params;

        // MUST be exactly "2.0"
        if (empty($this->json->jsonrpc) || $this->json->jsonrpc !== '2.0') {
            $code    = Server::ERROR_INVALID_REQUEST;
            $message = Server::MESSAGE_ERROR_INVALID_REQUEST;
        // An identifier established by the Client that MUST contain a String, Number, or NULL value if included.
        // The value SHOULD normally not be Null and Numbers SHOULD NOT contain fractional parts.
        } elseif (isset($this->json->id) && false === (is_string($id) || is_numeric($id) || is_null($id))) {
            $code    = Server::ERROR_INVALID_REQUEST;
            $message = Server::MESSAGE_ERROR_INVALID_REQUEST;
        // A String containing the name of the method to be invoked.
        } elseif (empty($this->json->method) || false === is_string($method) || is_numeric($method)) {
            $code    = Server::ERROR_INVALID_REQUEST;
            $message = Server::MESSAGE_ERROR_INVALID_REQUEST;
        // Method names that begin with the word rpc followed by a period character (U+002E or ASCII 46) are reserved
        // for rpc-internal methods and extensions and MUST NOT be used for anything else.
        } elseif (0 === strpos(strtolower($method), 'rpc.')) {
            $code    = Server::ERROR_INVALID_REQUEST;
            $message = Server::MESSAGE_ERROR_INVALID_REQUEST;
        // A Structured value that holds the parameter values to be used during the invocation of the method.
        //This member MAY be omitted.
        } elseif (false === empty($this->json->params) && false === (is_array($params) || is_object($params))) {
            $code    = Server::ERROR_INVALID_REQUEST;
            $message = Server::MESSAGE_ERROR_INVALID_REQUEST;
        }

        if ($code) {
            throw new \Exception($message, $code);
        }
    }
}

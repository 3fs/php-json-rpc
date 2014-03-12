<?php
namespace trifs\jsonrpc\Server\Request;

interface RequestInterface
{

    /**
     * Constructor, sets JSON request.
     *
     * @param  array $json
     * @return void
     */
    public function __construct(array $json);

    /**
     * Returns a list of all available requests.
     *
     * @return RequestInterface
     */
    public function getRequests();

    /**
     * Returns request's ID.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Returns request's method name.
     *
     * @return string
     */
    public function getMethod();

    /**
     * Returns request's parameters.
     *
     * @return array
     */
    public function getParameters();

    /**
     * Returns a boolean flag indicating whether a request is a batch or not.
     *
     * @return boolean
     */
    public function isBatch();

    /**
     * Returns a boolean flag indicating whether a request is a notification or not.
     *
     * @return boolean
     */
    public function isNotification();

    /**
     * Validates request.
     *
     * @return void
     * @throws \Exception if request is invalid
     */
    public function validate();
}

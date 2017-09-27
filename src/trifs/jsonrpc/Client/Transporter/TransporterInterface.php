<?php
namespace trifs\jsonrpc\Client\Transporter;

interface TransporterInterface
{
    /**
     * Transports payload to uri.
     *
     * @param  string $uri
     * @param  string $payload
     * @param  array  $params
     * @return string $response
     * @throws Exception if request is invalid
     */
    public function request($uri, $payload, array $params);
}

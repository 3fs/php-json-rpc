<?php
namespace trifs\jsonrpc\Client\Transporter;

class DefaultTransporter implements TransporterInterface
{
    /**
     * Transports payload to uri using file_get_contents().
     *
     * @param  string $uri
     * @param  string $payload
     * @param  array  $params
     * @return string $response
     * @throws Exception if request is invalid
     */
    public function request($uri, $payload, array $params)
    {
        // set timeout in seconds or use default timeout as fallback
        if (!isset($params['timeout'])) {
            throw new Execption("Missing 'timeout' param");
        }

        $options = [
            'http' => [
                'method'        => 'POST',
                'header'        => 'Content-Type: application/json',
                'content'       => $payload,
                'max_redirects' => 0,
                'timeout'       => (float) $params['timeout'],
            ],
        ];


        $response = @file_get_contents($uri, false, stream_context_create($options));

        if ($response === false) {
            throw new Exception(sprintf(
                'Unable to connect to %s, reason: %s',
                $uri,
                error_get_last()['message']
            ));
        }

        return $response;
    }
}

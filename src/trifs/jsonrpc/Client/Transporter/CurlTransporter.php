<?php
namespace trifs\jsonrpc\Client\Transporter;

class CurlTransporter implements TransporterInterface
{
    const HTTP_OK         = 200;
    const HTTP_NO_CONTENT = 204;

    /**
     * Transports payload to uri using curl.
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

        $timeout = (float) $params['timeout'];

        // init curl options
        $options = [
                CURLOPT_RETURNTRANSFER    => true,
                CURLOPT_URL               => $uri,
                CURLOPT_POST              => true,
                CURLOPT_FOLLOWLOCATION    => false,
                CURLOPT_POSTFIELDS        => $payload,
                CURLOPT_TIMEOUT_MS        => (int)($timeout * 1000),
                CURLOPT_HTTPHEADER        => [
                    'Content-Type: application/json',
                ],
            ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $response  = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error     = curl_error($curl);
        curl_close($curl);

        if (false === $response) {
            throw new Exception(sprintf(
                'Unable to connect to %s, reason: %s',
                $uri,
                $error
            ));
        }

        if ($http_code !== self::HTTP_OK && $http_code !== self::HTTP_NO_CONTENT) {
            throw new Exception(sprintf(
                'Unable to connect to %s, reason: %s',
                $uri,
                sprintf("Invalid status code: %d", $http_code)
            ));
        }

        return $response;
    }
}

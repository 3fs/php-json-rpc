<?php
namespace trifs\jsonrpc\tests\unit;

use trifs\jsonrpc\Server;

class ServerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException        \InvalidArgumentException
     * @expectedExceptionMessage $input has to be a string.
     * @expectedExceptionCode    0
     * @dataProvider             dpEmptyInvoker
     */
    public function testInvalidInput($invoker)
    {
        $server = new Server([], $invoker);
        $server->run();
    }

    /**
     * @dataProvider dpParseError
     */
    public function testParseError($input, $invoker)
    {
        $server = new Server($input, $invoker);
        $this->assertSame(
            json_encode([
                'jsonrpc' => '2.0',
                'id'      => null,
                'error'   => [
                    'code'    => -32700,
                    'message' => 'Parse error',
                ],
            ]),
            $server->run()
        );
    }

    /**
     * @dataProvider dpInvalidRequest
     */
    public function testInvalidRequest($input, $invoker)
    {
        $server   = new Server($input, $invoker);
        $response = [
                'jsonrpc' => '2.0',
                'id'      => null,
                'error'   => [
                    'code'    => -32600,
                    'message' => 'Invalid Request',
                ],
        ];
        // one ugly hack
        if ($input === '[1,2,3]') {
            $response = array_fill(0, 3, $response);
        }
        $this->assertSame(
            json_encode($response),
            $server->run()
        );
    }

    public function testInvalidMethod()
    {
        $server = new Server(
            '{"jsonrpc": "2.0", "method": "foobar", "id": "1"}',
            function () {
                throw new \Exception('The method does not exist / is not available.', Server::ERROR_METHOD_NOT_FOUND);
            }
        );
        $this->assertSame(
            json_encode([
                'jsonrpc' => '2.0',
                'id'      => '1',
                'error'   => [
                    'code'    => -32601,
                    'message' => 'The method does not exist / is not available.',
                ],
            ]),
            $server->run()
        );
    }

    public function testRequestWithParams()
    {
        $server = new Server(
            '{"jsonrpc": "2.0", "method": "subtract", "params": [42, 23], "id": 1}',
            function ($method, array $params = []) {
                return $params[0] - $params[1];
            }
        );
        $this->assertSame(
            json_encode([
                'jsonrpc' => '2.0',
                'id'      => 1,
                'result'  => 19,
            ]),
            $server->run()
        );
    }

    /**
     * @dataProvider dpNamedParams
     */
    public function testRequestWithNamedParams($input)
    {
        $server = new Server(
            $input,
            function ($method, array $params = []) {
                return $params['minuend'] - $params['subtrahend'];
            }
        );
        $this->assertSame(
            json_encode([
                'jsonrpc' => '2.0',
                'id'      => 3,
                'result'  => 19,
            ]),
            $server->run()
        );
    }

    public function testNotification()
    {
        $server = new Server(
            '{"jsonrpc": "2.0", "method": "update", "params": [1,2,3,4,5]}',
            function () {
            }
        );
        $this->assertNull($server->run());
    }

    public function testBatchRequest()
    {
        $server = new Server(
            '[
                {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
                {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]},
                {"jsonrpc": "2.0", "method": "subtract", "params": [42,23], "id": "2"},
                {"foo": "boo"},
                {"jsonrpc": "2.0", "method": "foo.get", "params": {"name": "myself"}, "id": "5"},
                {"jsonrpc": "2.0", "method": "get_data", "id": "9"}
            ]',
            function ($method, array $params = []) {
                switch ($method) {
                    case 'sum':
                        return array_sum($params);
                    case 'subtract':
                        return $params[0] - $params[1];
                    case 'get_data':
                        return ['hello', 5];
                    case 'notify_hello':
                        return true;
                }
                throw new \Exception(Server::MESSAGE_ERROR_METHOD_NOT_FOUND, Server::ERROR_METHOD_NOT_FOUND);
            }
        );

        $this->assertSame(
            json_encode([
                [
                    'jsonrpc' => '2.0',
                    'id'      => '1',
                    'result'  => 7,
                ],
                [
                    'jsonrpc' => '2.0',
                    'id'      => '2',
                    'result'  => 19,
                ],
                [
                    'jsonrpc' => '2.0',
                    'id'      => null,
                    'error'   => [
                        'code'    => -32600,
                        'message' => 'Invalid Request',
                    ],
                ],
                [
                    'jsonrpc' => '2.0',
                    'id'      => '5',
                    'error'   => [
                        'code'    => -32601,
                        'message' => 'Method not found',
                    ],
                ],
                [
                    'jsonrpc' => '2.0',
                    'id'      => '9',
                    'result'  => [
                        'hello',
                        5
                    ],
                ],
            ]),
            $server->run()
        );
    }

    public function testBatchNotifications()
    {
        $server = new Server(
            '[
                {"jsonrpc": "2.0", "method": "notify_sum", "params": [1,2,4]},
                {"jsonrpc": "2.0", "method": "notify_hello", "params": [7]}
            ]',
            function () {
            }
        );
        $this->assertNull($server->run());
    }

    public function dpNamedParams()
    {
        $json = [
            '{"jsonrpc": "2.0", "method": "subtract", "params": {"subtrahend": 23, "minuend": 42}, "id": 3}',
            '{"jsonrpc": "2.0", "method": "subtract", "params": {"minuend": 42, "subtrahend": 23}, "id": 3}',
        ];
        foreach ($json as $input) {
            yield [
                $input,
                function () {
                }
            ];
        }
    }

    public function dpParseError()
    {
        $json = [
            '{"jsonrpc": "2.0", "method": "foobar, "params": "bar", "baz]',
            '[]',
            '[
              {"jsonrpc": "2.0", "method": "sum", "params": [1,2,4], "id": "1"},
              {"jsonrpc": "2.0", "method"
            ]',
        ];
        foreach ($json as $input) {
            yield [
                $input,
                function () {
                }
            ];
        }
    }

    public function dpInvalidRequest()
    {
        $json = [
            '{"jsonrpc": "2.0", "method": 1, "params": "bar"}',
            '[1]',
            '[1,2,3]',
        ];
        foreach ($json as $input) {
            yield [
                $input,
                function () {
                }
            ];
        }
    }

    public function dpEmptyInvoker()
    {
        yield [
            function () {
            }
        ];
    }
}

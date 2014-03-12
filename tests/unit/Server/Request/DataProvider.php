<?php
namespace trifs\jsonrpc\tests\unit\Server\Request;

trait DataProvider
{

    public function dpInvalidRequest()
    {
        $json = [
            [],
            // version
            ['jsonrpc' => '1'],
            ['jsonrpc' => '2.0'],
            // ids
            ['jsonrpc' => '2.0', 'id' => []],
            ['jsonrpc' => '2.0', 'id' => 2.0],
            ['jsonrpc' => '2.0', 'id' => new \stdClass()],
            // methods
            ['jsonrpc' => '2.0', 'method' => []],
            ['jsonrpc' => '2.0', 'method' => 123],
            ['jsonrpc' => '2.0', 'method' => new \stdClass()],
            ['jsonrpc' => '2.0', 'method' => 'rpc.'],
            ['jsonrpc' => '2.0', 'method' => 'rpc.something'],
            // params
            ['jsonrpc' => '2.0', 'method' => 'valid-method', 'params' => 1],
            ['jsonrpc' => '2.0', 'method' => 'valid-method', 'params' => '1'],
            ['jsonrpc' => '2.0', 'method' => 'valid-method', 'params' => 1.337],
        ];
        foreach ($json as $input) {
            yield [$input];
        }
    }
}

<?php
namespace trifs\jsonrpc\tests\unit\Server\Request;

use trifs\jsonrpc\Server\Request\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{

    use DataProvider;

    public function testEmptyInitialisation()
    {
        $request = new Request([]);

        $this->assertNull($request->getId());
        $this->assertNull($request->getMethod());
        $this->assertSame([], $request->getParameters());
        $this->assertSame(
            [$request],
            $request->getRequests()
        );
    }

    public function testIsBatch()
    {
        $this->assertFalse((new Request([]))->isBatch());
    }

    public function testIsNotification()
    {
        $this->assertFalse((new Request([]))->isNotification());
    }

    /**
     * @dataProvider dpIds
     */
    public function testGetId($id)
    {
        $request = new Request(['id' => $id]);
        $this->assertSame($id, $request->getId());
    }

    /**
     * @dataProvider dpMethods
     */
    public function testGetMethod($method)
    {
        $request = new Request(['method' => $method]);
        $this->assertSame($method, $request->getMethod());
    }

    /**
     * @dataProvider dpParams
     */
    public function testGetParameters($parameter)
    {
        $request = new Request(['params' => $parameter]);
        $this->assertSame($parameter, $request->getParameters());
    }

    /**
     * @dataProvider             dpInvalidRequest
     * @expectedException        \Exception
     * @expectedExceptionCode    -32600
     * @expectedExceptionMessage Invalid Request
     */
    public function testValidateInvalidRequest(array $json)
    {
        $request = new Request($json);
        $request->validate();
    }

    public function testValidateValidRequest()
    {
        $request = new Request([
            'jsonrpc' => '2.0',
            'id'      => 123,
            'method'  => 'method-name',
        ]);
        $request->validate();
    }

    public function dpIds()
    {
        $ids = [
            'some-id',
            null,
            123,
            '123',
        ];
        foreach ($ids as $id) {
            yield [$id];
        }
    }

    public function dpMethods()
    {
        $methods = [
            'some-method',
            'rpc.method',
            123,
            '123',
        ];
        foreach ($methods as $method) {
            yield [$method];
        }
    }

    public function dpParams()
    {
        $params = [
            'some-params',
            [],
            [1, 2, 3],
            [1 => 'a', '2' => 'b', 3 => 'c'],
        ];
        foreach ($params as $param) {
            yield [$param];
        }
    }
}

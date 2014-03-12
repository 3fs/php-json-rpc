<?php
namespace trifs\jsonrpc\tests\unit\Server\Request;

use trifs\jsonrpc\Server\Request\Batch;
use trifs\jsonrpc\Server\Request\Notification;
use trifs\jsonrpc\Server\Request\Request;

class BatchTest extends \PHPUnit_Framework_TestCase
{

    use DataProvider;

    public function testEmptyInitialisation()
    {
        $request = new Batch([]);

        $this->assertNull($request->getId());
        $this->assertNull($request->getMethod());
        $this->assertSame([], $request->getParameters());
        $this->assertSame([], $request->getRequests());
    }

    public function testIsBatch()
    {
        $this->assertTrue((new Batch([]))->isBatch());
    }

    public function testIsNotification()
    {
        $this->assertFalse((new Batch([]))->isNotification());
    }

    /**
     * @dataProvider dpIds
     */
    public function testGetId($id)
    {
        $request = new Batch(['id' => $id]);
        $this->assertNull($request->getId());
    }

    /**
     * @dataProvider dpMethods
     */
    public function testGetMethod($method)
    {
        $request = new Batch(['method' => $method]);
        $this->assertNull($request->getMethod());
    }

    /**
     * @dataProvider dpParams
     */
    public function testGetParameters($parameter)
    {
        $request = new Batch(['params' => $parameter]);
        $this->assertSame([], $request->getParameters());
    }

    public function testGetRequests()
    {
        $json = [
            [ 'method' => 'notification-1', 'id' => null, ],
            [ 'method' => 'notification-2', 'id' => null, ],
            [ 'method' => 'notification-3', ],
            [ 'method' => 'notification-4', 'id' => null, 'params' => [], ],
            [ 'method' => 'notification-5', 'id' => null, 'params' => [1, 2, 3], ],
            [ 'method' => 'notification-6', 'params' => [], ],
            [ 'method' => 'notification-6', 'params' => [1, 2, 3], ],
            [ 'method' => 'request-1', 'id' => 1],
            [ 'method' => 'request-1', 'id' => '1'],
            [ 'method' => 'request-1', 'id' => 1, 'params' => []],
            [ 'method' => 'request-1', 'id' => '1', 'params' => [1, 2, 3]],
        ];
        $json = array_map(function ($json) {
            return ['jsonrpc' => '2.0'] + $json;
        }, $json);

        $request = new Batch($json);

        $this->assertNull($request->getId());
        $this->assertNull($request->getMethod());
        $this->assertSame([], $request->getParameters());
        $this->assertEquals(
            array_map(
                function ($json) {
                    if (empty($json['id'])) {
                        return new Notification($json);
                    }
                    return new Request($json);
                },
                $json
            ),
            $request->getRequests()
        );
    }

    /**
     * @dataProvider             dpInvalidRequest
     * @expectedException        \Exception
     * @expectedExceptionCode    -32600
     * @expectedExceptionMessage Invalid Request
     */
    public function testValidateInvalidBatch(array $json)
    {
        $request = new Batch($json);
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

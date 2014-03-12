<?php
namespace trifs\jsonrpc\tests\unit\Server\Request;

use trifs\jsonrpc\Server\Request\Notification;

class NotificationTest extends \PHPUnit_Framework_TestCase
{

    use DataProvider;

    public function testEmptyInitialisation()
    {
        $request = new Notification([]);

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
        $this->assertFalse((new Notification([]))->isBatch());
    }

    public function testIsNotification()
    {
        $this->assertTrue((new Notification([]))->isNotification());
    }

    /**
     * @dataProvider dpIds
     */
    public function testGetId($id)
    {
        $request = new Notification(['id' => $id]);
        $this->assertSame($id, $request->getId());
    }

    /**
     * @dataProvider dpMethods
     */
    public function testGetMethod($method)
    {
        $request = new Notification(['method' => $method]);
        $this->assertSame($method, $request->getMethod());
    }

    /**
     * @dataProvider dpParams
     */
    public function testGetParameters($parameter)
    {
        $request = new Notification(['params' => $parameter]);
        $this->assertSame($parameter, $request->getParameters());
    }

    /**
     * @dataProvider             dpInvalidRequest
     * @expectedException        \Exception
     * @expectedExceptionCode    -32600
     * @expectedExceptionMessage Invalid Request
     */
    public function testValidateInvalidNotification(array $json)
    {
        $request = new Notification($json);
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

<?php
namespace trifs\jsonrpc\tests\unit;

use trifs\jsonrpc\Client;
use trifs\jsonrpc\Client\Transporter\TransporterInterface;

class ClientTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage No requests have been set.
     * @expectedExceptionCode    0
     */
    public function testSendWithoutRequests()
    {
        $client = new Client('');
        $client->send();
    }

    /**
     * @return void
     */
    public function testEmptyEndpoint()
    {
        // default timeout
        $timeout = (float) ini_get('default_socket_timeout');

        // set up mock transporter to expect certain parameters
        $transporter = $this->getMock(TransporterInterface::class);
        $transporter->method('request')
            ->with(
                $this->equalTo(''),
                $this->callback(function ($obj) {
                    $payload = json_decode($obj);
                    $this->assertEquals($payload->jsonrpc, '2.0');
                    $this->assertEquals($payload->method, 'method');
                    return true;
                }),
                $this->equalTo(['timeout' => $timeout])
            )
            ->willReturn(null);

        $client = new Client('');
        $client->setTransporter($transporter);
        $result = $client->request('method')->send();

        $this->assertTrue($result);
    }

    /**
     * @return void
     */
    public function testNotificationWithError()
    {
        $uri = (string) rand();
        $timeout = rand();

        // set up mock transporter to expect certain parameters
        $transporter = $this->getMock(TransporterInterface::class);
        $transporter->method('request')
            ->with(
                $this->equalTo($uri),
                $this->callback(function ($obj) {
                    $payload = json_decode($obj);
                    $this->assertEquals($payload->jsonrpc, '2.0');
                    $this->assertEquals($payload->method, 'method');

                    // notification contains ID
                    $this->assertNotEmpty($payload->id);
                    return true;
                }),
                $this->equalTo(['timeout' => $timeout])
            )
            ->willReturn(json_encode(['error' => 'something_wrong']));

        $client = new Client($uri, ['timeout' => $timeout]);
        $client->setTransporter($transporter);
        $result = $client->request('method')->send();

        $this->assertEquals($result, ['error' => 'something_wrong']);
    }

    /**
     * @return void
     */
    public function testSetTimeout()
    {
        $client = new Client('');

        // default timeout from php.ini
        $timeout = (float) ini_get('default_socket_timeout');
        $this->assertSame($timeout, $client->getTimeout());

        $this->assertSame(10.5, $client->setTimeout(10.5)->getTimeout());
        $this->assertSame(10.5, $client->setTimeout('10.5')->getTimeout());
        $this->assertSame(10.0, $client->setTimeout(10)->getTimeout());
    }

    /**
     * @return void
     */
    public function testSetTimeoutInConstruct()
    {
        $client = new Client('', ['timeout' => 10.5]);
        $this->assertSame(10.5, $client->getTimeout());
    }
}

<?php
namespace trifs\jsonrpc\tests\unit;

use trifs\jsonrpc\Client;

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
     * @expectedException        \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage file_get_contents(): Filename cannot be empty
     * @expectedExceptionCode    2
     */
    public function testEmptyEndpoint()
    {
        $client = new Client('');
        $client->request('method')
            ->send();
    }

    /**
     * @return void
     */
    public function testSetTimeout()
    {
        $client = new Client('');
        $this->assertNull($client->getTimeout());

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

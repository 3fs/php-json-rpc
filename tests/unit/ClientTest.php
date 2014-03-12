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
}

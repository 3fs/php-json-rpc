<?php
namespace trifs\jsonrpc\Server\Request;

class Notification extends Request
{

    /**
     * Returns a boolean flag indicating whether a request is a notification or not.
     *
     * @return boolean
     */
    public function isNotification()
    {
        return true;
    }
}

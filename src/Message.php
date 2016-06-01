<?php // -->
/**
 * GoIP Client/Server Package based on
 * GoIP SMS Gateway Interface.
 *
 * (c) 2014-2016 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace GoIP;

/**
 * Message Class
 *
 * @package  GoIP
 * @author   Charles Zamora <czamora@openovate.com>
 * @standard PSR-2
 */
class Message {
    /**
     * ACK Response Message.
     *
     * @const string
     */
    const ACK_MESSAGE = 'reg:%s;status:%s';

    /**
     * Generate ACK response message.
     *
     * @param   int
     * @return  string
     */
    public function getAck($id, $status = 200)
    {
        // return ack response
        return sprintf(self::ACK_MESSAGE, $id, $status);
    }
}
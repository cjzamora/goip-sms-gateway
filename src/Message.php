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
class Message 
{
    /**
     * ACK Response message.
     *
     * @const string
     */
    const ACK_MESSAGE = 'reg:%s;status:%s;';

    /**
     * Bulk SMS Request message.
     *
     * @const string
     */
    const BULK_SMS_REQUEST = 'MSG %s %s %s\n';

    /**
     * Authentication Request message.
     *
     * @const string
     */
    const AUTHENTICATION_REQUEST = 'PASSWORD %s %s\n';

    /**
     * Submit Number Request message.
     *
     * @const string
     */
    const SUBMIT_NUMBER_REQUEST = 'SEND %s %s %s';

    /**
     * Submit End Request message.
     *
     * @const string
     */
    const END_REQUEST = 'DONE %s\n';

    /**
     * Get the request message constant
     * and return it's formatted value.
     *
     * @param   string
     * @param   [*mixed, ...]
     * @return  string
     */
    public function getConstant()
    {
        // get the arguments
        $args = func_get_args();

        // get the constant
        $const = array_shift($args);
        
        // get the message
        $message = constant('self::' . $const);  

        // set the parameters
        array_unshift($args, $message);

        // call local methods to format message
        return call_user_func_array('sprintf', $args);
    }
}
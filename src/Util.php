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
 * Utility Class
 *
 * @package  GoIP
 * @author   Charles Zamora <czamora@openovate.com>
 * @standard PSR-2
 */
class Util extends Base 
{
    /**
     * Parse buffer as an array.
     *
     * @param   string
     * @return  array
     */
    public static function parseArray($buffer)
    {
        // split the buffer
        $data   = explode(';', $buffer);
        // prepare for parsed data
        $parsed = array();

        // iterate on the buffer
        foreach($data as $value) {
            // split value
            $parts = explode(':', $value);

            // get the key
            $key = array_shift($parts);
            // join the value
            $val = implode(':', $parts);

            // if we don't have key
            if(strlen($key) == 0) {
                continue;
            }

            $parsed[$key] = $val;
        }

        return $parsed;
    }

    /**
     * Parse buffer as readable plain text.
     *
     * @param   string
     * @return  string
     */
    public static function parseString($buffer)
    {
        // parse the buffer
        $data = self::parseArray($buffer);
        // prepare the parsed data
        $parsed = '';

        // iterate on each data
        foreach($data as $key => $value) {
            // append parsed data
            $parsed .= $key . ' : ' . $value . PHP_EOL;
        }

        return $parsed;
    }

    /**
     * Check the buffer if it contains a
     * message.
     *
     * @param   string
     * @param   bool
     * @return  array
     */
    public static function getMessage($buffer)
    {
        // parse the buffer
        $data = self::parseArray($buffer);

        // if received key is set and msg
        if(isset($data['RECEIVE']) && isset($data['msg'])) {
            return $data;
        }

        // return empty array
        return array();
    }
}
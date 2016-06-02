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
 * Request Class
 *
 * @package  GoIP
 * @author   Charles Zamora <czamora@openovate.com>
 * @standard PSR-2
 */
class Request extends Base 
{
    /**
     * Max SMS Content Length
     *
     * @const int
     */
    const MAX_LENGTH = 3000;

    /**
     * Current socket that we will use.
     *
     * @var object
     */
    protected $socket = null;

    /**
     * Current host.
     *
     * @var string
     */
    protected $host = null;

    /**
     * Current port.
     *
     * @var string
     */
    protected $port = null;

    /**
     * Current password.
     *
     * @var string
     */
    protected $password = null;

    /**
     * Current buffer.
     *
     * @var array
     */
    protected $buffer = array();

    /**
     * Current send id.
     *
     * @var string
     */
    protected $sendId = null;

    /**
     * Debug flag.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Initialize request by setting
     * the newly created socket.
     *
     * @param   object
     * @return  $this
     */
    public function __construct($socket, $host, $port, $password)
    {
        // set the socket
        $this->socket   = $socket;
        // set the host
        $this->host     = $host;
        // set the port
        $this->port     = $port;
        // set the password
        $this->password = $password;
    }

    /**
     * Send bulk sms request.
     *
     * @param   int
     * @param   string
     * @return  bool
     */
    public function bulkSmsRequest($id, $content)
    {
        // cut the content
        $content = mb_strcut($content, 0, 3000);
        // get the current length
        $length  = strlen($content);

        // get the bulk sms request message
        $message = $this->message()->getConstant('BULK_SMS_REQUEST', $id, $length, $content);

        // send request
        if($this->send($message) < 0) {
            return false;
        }

        // get the response
        $response = $this->get('bulkSmsRequest', 1);

        // if we're good
        if($response[0] == 'PASSWORD') {
            // set the send id
            $this->sendId = $id;

            return true;
        }

        return false;
    }

    /**
     * Send authentication request.
     *
     * @param   string
     * @return  bool | string
     */
    public function authenticationRequest($password)
    {
        // get the authentication request message
        $message = $this->message()->getConstant('AUTHENTICATION_REQUEST', $this->sendId, $password);

        // send request
        if($this->send($message) < 0) {
            return false;
        }

        // get the response
        $response = $this->get('AuthenticationRequest', 1);

        // if we are good
        if($response[0] == 'SEND') {
            return true;
        }

        return false;
    }

    /**
     * Send submit number request.
     *
     * @param   string
     * @return  $this
     */
    public function submitNumberRequest($number)
    {
        // get the submit number request
        $message = $this->message()->getConstant('SUBMIT_NUMBER_REQUEST', $this->sendId, rand(1, 100), $number);

        // get the response until we got OK | ERROR
        foreach(range(1, 30) as $value) {
            // send request
            if($this->send($message) < 0) {
                return false;
            }

            // get the response
            $response = $this->get('SubmitNumberRequest', 1);

            // still waiting?
            if($response[0] == 'WAIT') {
                // let's wait
                sleep(1);

                continue;
            }

            // request OK?
            if($response[0] == 'OK') {
                return true;
            }

            // request ERROR?
            if($response[0] == 'ERROR') {
                return false;
            }
        }

        return false;
    }

    /**
     * Send end request.
     *
     * @return  bool
     */
    public function endRequest()
    {
        // get the end request
        $message = $this->message()->getConstant('END_REQUEST', $this->sendId);

        // send request
        if($this->send($message) < 0) {
            return false;
        }

        // get response
        $response = $this->get('END_REQUEST', 1);

        // if OK
        if($response[0] == 'OK'
        || $response[0] == 'DONE') {
            return true;
        }

        return false;
    }

    /**
     * Return the buffer.
     *
     * @param   string
     * @return  array
     */
    public function getBuffer($buffer = null)
    {
        // check if buffer key is set
        if(!is_null($buffer)) {
            // return the buffer key
            return isset($this->buffer[$buffer]) ? $this->buffer[$buffer] : array();
        }

        return $this->buffer;
    }

    /**
     * Send response to server.
     *
     * @param   string
     * @return  $this
     */
    public function send($message)
    {
        // if debug is set
        if($this->debug) {
            $this->debugMessage('Send', $message);
        }

        // send the message to the current socket
        return socket_sendto($this->socket, $message, strlen($message), 0, $this->host, $this->port);
    }

    /**
     * Get response from socket based
     * on the given message.
     *
     * @param   string
     * @param   int
     * @return  array
     */
    private function get($type, $max = 30)
    {
        // max retry times
        foreach(range(1, $max) as $value) {
            // get the response from the given host and port
            socket_recvfrom($this->socket, $buffer, 2048, 0, $this->host, $this->port);

            // if debug is set
            if($this->debug) {
                $this->debugMessage('Receive', $buffer);
            }

            // if buffer is not empty
            if(!empty($buffer)) {
                // set the buffer
                $this->buffer[$type] = explode(' ', $buffer);

                // return the buffer as array
                return $this->buffer[$type];
            }
        }

        // set error buffer
        return array(
            'ERROR',
            'Unable to connect to ' . $this->host . ':' . $this->port
        );
    }

    /**
     * Set debugging.
     *
     * @param   bool
     * @return  $this
     */
    public function setDebug($debug = false)
    {
        // set debug flag
        $this->debug = $debug;

        return $this;
    }

    /**
     * Log debug information.
     *
     * @param   string
     * @param   string
     * @return  $this
     */
    public function debugMessage($type, $message)
    {
        print $type . ': ' . $message;
        print PHP_EOL;

        return $this;
    }

    /**
     * Decorator for Message Class.
     *
     * @return GoIP\Message
     */
    public function message() {
        // return initialized message object
        return new Message();
    }
}
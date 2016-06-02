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
 * Client Class
 *
 * @package  GoIP
 * @author   Charles Zamora <czamora@openovate.com>
 * @standard PSR-2
 */
class Client extends Event 
{
    /**
     * Default socket.
     *
     * @var object
     */
    protected $socket = null;

    /**
     * Default host.
     *
     * @var string
     */
    protected $host = null;

    /**
     * Default port / channel.
     *
     * @var int
     */
    protected $port = null;

    /**
     * Default password.
     *
     * @var string
     */
    protected $password = null;

    /**
     * Current send id.
     *
     * @var int | null
     */
    protected $id = null;

    /**
     * Debug flag.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Initialize the client socket
     * given the host and the port,
     * default GoIP port for it's 
     * SMS interface is 9991 which
     * also serve as the GSM Module
     * channel.
     *
     * @param   string
     * @param   int
     * @return  $this
     */
    public function __construct($host, $port = 9991)
    {
        // set the host
        $this->host     = $host;
        // set the port
        $this->port     = $port;

        // initialize our socket
        // - ipv4 connection
        // - socket datagram type
        // - over UDP connection
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        // if socket failed to be created
        if($this->socket < 0) {
            // set event message
            $message = 'Failed to create socket: ' . socket_strerror($this->socket) . PHP_EOL;

            // throw an event
            $this->trigger('client-error', $message);

            // exit
            exit();
        }
    }

    /**
     * Set host.
     *
     * @param   string
     * @return  $this
     */
    public function setHost($host)
    {
        // set the host
        $this->host = $host;

        return $this;
    }

    /**
     * Set port.
     *
     * @param   int
     * @return  $this
     */
    public function setPort($port = 9991)
    {
        // set the port / channel
        $this->port = $port;

        return $this;
    }

    /**
     * Close the socket connection.
     *
     * @return $this
     */
    public function close()
    {
        // close the socket
        socket_close($this->socket);

        return $this;
    }

    /**
     * Set password.
     *
     * @param   string
     * @return  $this
     */
    public function setPassword($password)
    {
        // set the password
        $this->password = $password;

        return $this;
    }

    /**
     * Set send id.
     *
     * @param   int
     * @return  $this
     */
    public function setId($id)
    {
        // set the id
        $this->id = $id;

        return $this;
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
     * Send sms request.
     *
     * @param   string
     * @param   string
     * @return  $this
     */
    public function sendSms($content, $receiver)
    {
        // initialize request object
        $request = new Request(
            $this->socket, 
            $this->host, 
            $this->port,
            $this->password);

        // if debug
        if($this->debug) {
            // set request debugging
            $request->setDebug(true);
        }

        // is id set?
        if(!isset($this->id)) {
            // set a random number
            $this->id = rand(100, 1000);
        }

        // send bulk sms request
        foreach(range(1, 5) as $value) {
            $bulk = $request->bulkSmsRequest($this->id, $content);
        }

        // if bulk request failed
        if($bulk !== true) {
            throw new \Exception('An error occured in BulkSmsRequest.');
        }

        // send auth request
        $auth = $request->authenticationRequest($this->password);

        // if auth request failed
        if($auth !== true) {
            throw new \Exception('An error occured in AuthenticationRequest.');
        }

        // send submit number request
        $submit = $request->submitNumberRequest($receiver);

        // if submit number request failed
        if($submit !== true) {
            throw new \Exception('An error occured in SubmitNumberRequest.');
        }

        // send end request
        $end = $request->endRequest();

        // if end request is not done
        if($end !== true) {
            throw new \Exception('An error occured in EndRequest.');
        }

        // close socket
        $this->close();

        return true;
    }
}
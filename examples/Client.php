<?php // -->

// manual autoload
spl_autoload_register(function($class) {
    $class = str_replace('\\', '/', $class);
    $class = str_replace('GoIP/', '', $class);
    $class = dirname(__DIR__) . '/src/' . $class . '.php';

    require $class;
});

// require client class
require dirname(__DIR__) . '/src/Client.php';
// require request class
require dirname(__DIR__) . '/src/Request.php';

// initialize client
// - hostname to request to
// - port to request to, port
//   will actually serve as the
//   GSM Module channel, for example
//   first channel is 9991, if we have
//   8 channel GSM Modele the port range
//   will be 9991-9998 etc. 9991 will be
//   the default channel.
$client = new GoIP\Client('192.168.1.42');

// set the client password
$client->setPassword('admin');

// let's set debug
$client->setDebug(true);

try {
    $client
    // set the channel, we are going
    // to send an sms request to channel 1
    ->setPort(9991)
    // send an sms request
    ->sendSms('BAL', '8888');

    echo 'Message Successfully Sent!';
} catch(\Exception $e) {
    echo 'Message Sending Failed! => ' . $e->getMessage();
}
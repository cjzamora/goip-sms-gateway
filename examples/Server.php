<?php // -->

// manual autoload
spl_autoload_register(function($class) {
    $class = str_replace('\\', '/', $class);
    $class = str_replace('GoIP/', '', $class);
    $class = dirname(__DIR__) . '/src/' . $class . '.php';

    require $class;
});

// require server class
require dirname(__DIR__) . '/src/Server.php';

// initialize server
// - hostname to bind to
// - port to bind to
$server = new GoIP\Server('192.168.1.52', 44444);

$server
// set timeout before reading next data
->setReadTimeout(2)

// on connection bind
->on('bind', function($server) {
    echo 'Socket binded to: ' . $server->getHost() . ':' . $server->getPort() . PHP_EOL . PHP_EOL;
})

// on request data
->on('data', function($server, $buffer) {
    echo 'Server got buffer data from: ' . $server->getOrigin('host') . ':' . $server->getOrigin('port') . PHP_EOL;
    echo GoIP\Util::parseString($buffer);
    echo PHP_EOL;
})

// on keep-alive request ack
->on('ack', function($server) {
    echo 'Keep-Alive request acknowledged.' . PHP_EOL . PHP_EOL;
})

// on ack failed
->on('ack-fail', function() {
    echo 'Keep-Alive request acknowledgement failed.' . PHP_EOL . PHP_EOL;
})

// on message receive
->on('message', function($server, $buffer) {
    echo "\033[32mServer got a message from: " . $server->getOrigin('host') . ":" . $server->getOrigin('port') . " \033[0m" . PHP_EOL;
    echo "\033[32m" . GoIP\Util::parseString($buffer) . " \033[0m" ;
    echo PHP_EOL;

    // $server->end();
})

// on wait (waiting for valid data)
->on('wait', function($server) {
    echo 'Waiting for the client to send data.' . PHP_EOL . PHP_EOL;
})

// on server end
->on('end', function() {
    echo 'Server got exit event. Terminating.' . PHP_EOL . PHP_EOL;
})

// start receiving connection data
->loop();

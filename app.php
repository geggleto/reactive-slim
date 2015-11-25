
<?php
include "vendor/autoload.php";

use Zend\Diactoros\Stream;

//Our Slim App
$app = new Slim\App();

$app->get("/", function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) {
    return $response->write("Horray!");
});


//Our HTTP Server
$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);

$http = new React\Http\Server($socket);
$http->on('request', function (React\Http\Request $request,  React\Http\Response $response) use ($app) {

    $uri = $request->getUrl();
    $method = $request->getMethod();

    $body = new Stream('php://memory', "wb+");
    $body->write($request->getBody());
    $body->rewind();

    $psr7Request = new Zend\Diactoros\ServerRequest([],[],$uri, $method, $body, $request->getHeaders());

    /** @var $slimRespoonse \Slim\Http\Response */
    $slimRespoonse = $app($psr7Request, new \Slim\Http\Response());


    $response->writeHead(200, array('Content-Type' => 'text/plain'));
    $slimRespoonse->getBody()->rewind();
    $response->end($slimRespoonse->getBody()->getContents());
});

$socket->listen(1337);
$loop->run();
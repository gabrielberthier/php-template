<?php

namespace Tests\Application\Server;

use Core\Http\Exceptions\BadRequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Tests\Traits\App\InstanceManager;

const ENCODE = 'application/json';
const ENDPOINT = '/test';

test('should capture successful response', function () {
    $instanceApp = new InstanceManager();
    $app = $instanceApp->createAppInstance();
    $app->map(['GET'], ENDPOINT, function (ServerRequestInterface $req, ResponseInterface $response) {
        $json = json_encode(['res' => true]);
        $response->getBody()->write($json);

        return $response;
    });

    $request = $this->createRequest(
        'GET',
        ENDPOINT,
        [
            'HTTP_ACCEPT' => ENCODE,
            'Content-Type' => ENCODE,
        ],
    );
    $response = $app->handle($request);

    $body = json_decode($response->getBody()->__tostring(), associative: true);

    expect($response)->not->toBeNull();
    expect($body)->toBe(['res' => true]);
});

test('should capture 5XX response', function () {
    $instanceApp = new InstanceManager();
    $app = $instanceApp->createAppInstance();
    $app->map(['GET'], ENDPOINT, function (ServerRequestInterface $req) {
        throw new \Exception('ERROR');
    });

    $request = $this->createRequest(
        'GET',
        ENDPOINT,
        [
            'HTTP_ACCEPT' => ENCODE,
            'Content-Type' => ENCODE,
        ],
    );
    $response = $app->handle($request);

    $body = json_decode($response->getBody()->__tostring(), associative: true);

    expect($response)->not->toBeNull();
    expect($body)->toBe([
        'statusCode' => 500,
        'error' => [
            'type' => 'SERVER_ERROR',
            'description' => 'ERROR',
        ],
    ]);
});

test('should capture mapped error response', function () {
    $instanceApp = new InstanceManager();
    $app = $instanceApp->createAppInstance();
    $app->map(['GET'], ENDPOINT, function (ServerRequestInterface $req) {
        throw new BadRequestException($req);
    });

    $request = $this->createRequest(
        'GET',
        ENDPOINT,
        [
            'HTTP_ACCEPT' => ENCODE,
            'Content-Type' => ENCODE,
        ],
    );
    $response = $app->handle($request);

    $body = json_decode($response->getBody()->__tostring(), associative: true);

    expect($response)->not->toBeNull();
    expect($body)->toBe([
        'statusCode' => 400,
        'error' => [
            'type' => 'BAD_REQUEST',
            'description' => '',
        ],
    ]);
});

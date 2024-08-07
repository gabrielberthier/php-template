<?php

use App\Domain\Models\User;
use App\Domain\Repositories\UserRepository;
use Core\Http\Domain\ActionPayload;
use DI\Container;
use Tests\Traits\App\InstanceManager;
use Tests\Traits\App\RequestManager;


test('should call action successfully', function () {
    $instanceApp = new InstanceManager();
    $app = $instanceApp->createAppInstance();
    /** @var Container $container */
    $container = $instanceApp->getContainer();

    $user = new User(1, 'bill.gates', 'Bill', 'Gates');

    /** @var \Mockery\MockInterface|UserRepository */
    $userRepositoryProphecy = mock(UserRepository::class);
    $userRepositoryProphecy->shouldReceive('findAll')->once()->andReturn([$user]);

    $container->set(UserRepository::class, $userRepositoryProphecy);
    $request = new RequestManager();
    $request = $request->createRequest('GET', '/users');
    $response = $app->handle($request);

    $payload = (string) $response->getBody();
    $expectedPayload = new ActionPayload(200, [$user]);
    $serializedPayload = json_encode($expectedPayload);

    expect($payload)->toEqual($serializedPayload);
});

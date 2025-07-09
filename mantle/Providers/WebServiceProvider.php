<?php

declare(strict_types=1);

namespace Mantle\Providers;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Psr7\ServerRequest as GuzzleServerRequest;
use Mantle\Contracts\ServiceProviderInterface;
use Mantle\Aspects\Container;
use Mantle\Aspects\EventDispatcher;
use Mantle\Aspects\Logger;
use Mantle\Aspects\Request;
use Mantle\Aspects\Response;
use Mantle\Aspects\Router;
use Mantle\Events\EventDispatcher as EventsEventDispatcher;
use Mantle\Http\Middlewares\ExceptionHandlerMiddleware;
use Mantle\Routing\Handlers\MiddlewareDispatcher;
use Mantle\Routing\Handlers\MiddlewareDispatcherFactory;
use Mantle\Routing\UnmaskedRouteDiscovery;
use Mantle\Aspects\Debugger;
use Mantle\Containers\PhpDiContainer;
use Mantle\Debuggers\TracyDebugger;
use Mantle\Loggers\Monolog\Logger as MonologLogger;
use Mantle\Routing\Wrappers\FastRoute;

/**
 * Class WebServiceProvider
 *
 * Registers application service bindings in the dependency injection container.
 * This provider binds interfaces to their concrete implementations, enabling
 * dependency resolution throughout the application.
 */
final class WebServiceProvider implements ServiceProviderInterface // TODO: create a ConsoleServiceProvider
{
    /**
     * Register service bindings in the container.
     *
     * @return void
     */
    public function register(): void
    {
        Debugger::setInstance(new TracyDebugger());
        Debugger::init();

        Logger::setInstance(new MonologLogger());
        Container::setInstance(new PhpDiContainer());
        EventDispatcher::setInstance(new EventsEventDispatcher());
        Request::setInstance(GuzzleServerRequest::fromGlobals());
        Response::setInstance(new GuzzleResponse());
        Router::setInstance(
            new FastRoute(
                Container::getInstance(),
                EventDispatcher::getInstance(),
                new UnmaskedRouteDiscovery(),
                new MiddlewareDispatcherFactory(
                    MiddlewareDispatcher::class,
                    Container::getInstance()
                )
            )
        );

        Router::addMiddleware(ExceptionHandlerMiddleware::class);

        // Container::bind(SomethingInterface::class, SomeConcreteClass::class);

        EventDispatcher::dispatch('mantle.initialized');

        // TODO: discover and load plugin service providers and run register() method
        EventDispatcher::dispatch('plugins.initialized');
    }

    public function boot(): void
    {
        // TODO: run plugin service providers boot() method
        EventDispatcher::dispatch('plugins.booted');
        EventDispatcher::dispatch('mantle.booted');

        Router::routeDiscovery(
            rootNamespace: 'Mask\Http',
            rootPath: dirname(__FILE__, 3) . '/mask/Http',
            namespacePaths: [
                'App\Http' => dirname(__FILE__, 3) . '/app/Http',
            ]
        );

        $response = Router::handle(Request::getInstance());
        Response::send($response);

        EventDispatcher::dispatch('rogue.booted');
    }
}

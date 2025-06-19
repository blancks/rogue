<?php

declare(strict_types=1);

namespace Rogue\Mantle\Providers;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Psr7\ServerRequest as GuzzleServerRequest;
use Rogue\Mantle\Contracts\ServiceProviderInterface;
use Rogue\Mantle\Aspects\Container;
use Rogue\Mantle\Aspects\EventDispatcher;
use Rogue\Mantle\Aspects\Logger;
use Rogue\Mantle\Aspects\Request;
use Rogue\Mantle\Aspects\Response;
use Rogue\Mantle\Aspects\Router;
use Rogue\Mantle\Containers\DependencyInjectionContainer;
use Rogue\Mantle\Events\EventDispatcher as EventsEventDispatcher;
use Rogue\Mantle\Http\Middlewares\ExceptionHandlerMiddleware;
use Rogue\Mantle\Routing\Handlers\MiddlewareDispatcher;
use Rogue\Mantle\Routing\Handlers\MiddlewareDispatcherFactory;
use Rogue\Mantle\Routing\Router as RoutingRouter;
use Rogue\Mantle\Routing\UnmaskedRouteDiscovery;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Psr\Log\LoggerInterface;

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
        $this->tracySetUp();
        Logger::setInstance($this->getMonologInstance());
        Container::setInstance(new DependencyInjectionContainer());
        EventDispatcher::setInstance(new EventsEventDispatcher());
        Request::setInstance(GuzzleServerRequest::fromGlobals());
        Response::setInstance(new GuzzleResponse());
        Router::setInstance(
            new RoutingRouter(
                Container::getInstance(),
                EventDispatcher::getInstance(),
                new UnmaskedRouteDiscovery(),
                new MiddlewareDispatcherFactory(MiddlewareDispatcher::class)
            )
        );

        Router::addMiddleware(new ExceptionHandlerMiddleware());

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
            rootNamespace: 'Rogue\Mask\Http',
            rootPath: dirname(__FILE__, 3) . '/mask/Http',
            namespacePaths: [
                'Rogue\App\Http' => dirname(__FILE__, 3) . '/app/Http',
            ]
        );

        $response = Router::handle(Request::getInstance());
        Response::send($response);

        EventDispatcher::dispatch('rogue.booted');
    }

    private function tracySetUp(): void
    {
        if (!class_exists('\Tracy\Debugger')) {
            return;
        }

        \Tracy\Debugger::enable();
        \Tracy\Debugger::$showBar = false;
    }

    private function getMonologInstance(): LoggerInterface
    {
        $monolog = new MonologLogger('app');
        $monolog->pushHandler(new StreamHandler(logsPath('app.log'), Level::Debug));
        return $monolog;
    }
}

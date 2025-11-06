# Rogue

> [!IMPORTANT]
> I've recently discovered the existence of [TempestPHP](https://github.com/tempestphp) which share one of the development goal of this project which was the auto-discovery of the components.
> That said <ins>this project is officially archived</ins> as I will pursue my goals by learning and using Tempest instead. 


Rogue is a PHP framework designed to enable the creation of a main application, called the **Mask Application**, which serves as a base project. This architecture allows you to build core projects (such as a CMS) that can be easily extended with custom application logic, without modifying the original codebase.

The primary goal is to empower developers to extend or customize the Mask Application without interfering with its core logic. This separation simplifies updates and maintenance for both the original project and any derived third-party extensions.

---

## Example: Extending the Mask Application

Suppose you have a core CMS built as your Mask Application. You want to let third-party developers add custom routes and logic without touching the core codebase.

**1. Mask Application (core, untouched):**
```php
// mask/Http/Home/HomeController.php
namespace Mask\Http\Home;
use Mantle\Routing\Attributes\UGet;

class HomeController
{
    #[UGet('/landing')]
    public function index(): string {
        return 'Welcome to the Rogue Framework!';
    }

    #[UGet('/signup')]
    public function signup(): string {
        return 'Please sign up here!';
    }
}
```

**2. User Application (custom logic):**
```php
// app/Http/Home/HomeController.php
namespace App\Http\Home;
use Mantle\Routing\Attributes\Get;

class HomeController
{
    public function index(): string {
        return 'Welcome to my Project!';
    }

    #[Get('/items/{id}/types/{type}')]
    public function items(int $id, string $type): string {
        return "Your item {$id} of the type {$type}";
    }
}
```

**Result:**

* `/landing` - served by App `HomeController::index()`
* `/signup` - served by Mask `HomeController::signup()`
* `/items/{id}/types/{type}` - served by App `HomeController::items()`

This example demonstrates how existing logic can be reused while adding only needed custom application logic.

With this approach, Rogue automatically prioritizes the custom controller when a user visits `/landing`, allowing you to override or extend core functionality without modifying the Mask Application. The `/signup` route remains handled by the original Mask Application, while endpoints like `/items/123/types/foo` will be served by the custom application.

This ensures that customizations and core updates remain isolated while third-party logic can be added, replaced, or removed independently. This flexible separation streamlines both maintenance and extensibility for all parties.

> 🚨 **Note:**
> * For unmasked routing to work, the application controller inside the `App` namespace must use the same FQCN you want to extend or replace under `Mask`, except for the `App` segment of course.
> * Routes intended to be unmasked must use the appropriate `UnmaskedRoute` attribute (such as `UGet`, `UPost`, etc.) in the Mask controllers. This ensures Rogue can correctly substitute the custom logic in place of the core implementation.
> * If you want to build completely new endpoints, this requirement does not apply: you are free to define new controllers and routes as needed.

---

## Inversion of Control (IoC) Container & Autowiring

Rogue includes a powerful IoC (Inversion of Control) container that manages the instantiation and lifecycle of your application's classes. This container supports autowiring, which means it can automatically resolve and inject dependencies into your controllers, services, and other classes without requiring manual configuration.

**How it works:**
- When a class (such as a controller or service) is requested, Rogue inspects its constructor and automatically provides any required dependencies, as long as they are type-hinted and registered with the container.
- Dependencies can be other services, configuration objects, or any class that the container knows how to instantiate.
- This enables you to write clean, decoupled code and makes testing and extending your application much easier.

**Example:**
```php
class UserService {
    public function __construct(DatabaseConnection $db) {
        $this->db = $db;
    }
}

class HomeController {
    public function __construct(UserService $userService) {
        $this->userService = $userService;
    }
}
```
In this example, Rogue will automatically resolve and inject the `DatabaseConnection` into `UserService`, and then inject `UserService` into `HomeController` when it is needed for a route or service.

You can also bind custom implementations or singletons to the container for more advanced use cases.

## TODO

* [ ] .env
* [ ] Config Aspect
    * [ ] get config data from files
    * [ ] set config data for specific instance
* [ ] Router
    * [x] nikic/fast-route Implementation
    * [x] Check if 405 http status can be implemented
    * [ ] Check if 406 http status can be implemented
    * [x] Base Implementation (PSR-15)
    * [x] Global Middleware
    * [x] Route-Specific Middleware
* [ ] Router Middlewares
    * [x] Exception handling middleware
    * [ ] Authentication Middleware
    * [ ] Throttle Middleware
* [ ] Route Caching
    * [ ] Base Implementation
* [ ] Debugger Aspect (probably wont be needed: debugger will be disabled at all in production)
    * [x] Use a debugging tool (nette/tracy)
* [x] Logger Aspect
    * [x] Use a PSR-3 logger (monolog)
* [x] HTTP Exceptions
    * [x] BadRequestException - 400
    * [x] UnauthorizedException - 401
    * [x] ForbiddenException - 403
    * [x] NotFoundException - 404
    * [x] MethodNotAllowedException - 405
    * [x] ConflictException - 409
    * [x] GoneException - 410
    * [x] LockedException - 423
    * [x] TooManyRequestsException - 429
* [ ] Input Validation
    * [ ] Base Implementation
    * [ ] string (with min, max length)
    * [ ] integer (with min, max values)
    * [ ] in (list of allowed values)
    * [ ] regexp
    * [ ] array
    * [ ] email
* [ ] Templating Renderer
    * [ ] Base Implementation
    * [ ] Choose which template engine to use by default (probably going for: latte/latte)
    * [ ] Template Caching
* [ ] Output Resources
    * [ ] Base Implementation
    * [ ] JsonResource
* [ ] Database/ORM (likely Eloquent)
    * [ ] Migrations
* [ ] Plugins
    * [ ] ServiceProvider
* [ ] Custom Service Providers
    * [ ] App\Providers
    * [ ] Mask\Providers
* [ ] Improve Console (dagger) Commands
    * [ ] Helpers
        * [ ] ProgressBar
        * [ ] Loader
        * [ ] text coloring
        * [ ] user input
    * [ ] ConsoleServiceProvider
    * [ ] Console Router with PHP Attributes ( like http routing )
        * [ ] Command middlewares
    * [ ] Console Commands
        * [ ] Add command for caching web routes
        * [ ] Add command for listing web routes (discovered plus caching status)

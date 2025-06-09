<?php

namespace Tests\Feature;

use App\Core\Request;
use App\Core\Router;
use App\Controllers\HomeController;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /** @test */
    public function test_it_resolves_a_basic_get_route(): void
    {
        // Simulate a server request to the homepage
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $request = new Request();
        $router = new Router($request);

        // Define a route for the test
        $router->get('/', [HomeController::class, 'index']);

        // Expect the HomeController's index method to be called and output its message
        $this->expectOutputString("<h1>Welcome to the Shopping List!</h1><p>Our custom MVC is working.</p><p>Go to <a href='/items' style='color: blue; text-decoration: underline;'>/items</a> to see the list.</p>");

        $router->resolve();
    }

    /** @test */
    public function test_it_resolves_a_dynamic_route_with_parameters(): void
    {
        // Simulate a request to an edit page
        $_SERVER['REQUEST_URI'] = '/items/edit/123';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $request = new Request();
        $router = new Router($request);

        // Define a mock controller for the test
        $mockController = new class {
            public function edit(Request $request, int $id): void {
                echo "Editing item with ID: {$id}";
            }
        };

        $router->get('/items/edit/{id}', [get_class($mockController), 'edit']);

        $this->expectOutputString("Editing item with ID: 123");

        $router->resolve();
    }
}

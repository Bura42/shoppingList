<?php

namespace App\Controllers;

class HomeController
{
    /**
     * Renders the home page.
     */
    public function index(): void
    {
        echo "<h1>Welcome to the Shopping List!</h1>";
        echo "<p>Our custom MVC is working.</p>";
        echo "<p>Go to <a href='/items' style='color: blue; text-decoration: underline;'>/items</a> to see the list.</p>";
    }
}

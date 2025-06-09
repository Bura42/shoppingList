# Simple Shopping List

## Technology Stack

* **Backend:** PHP 8.3
* **Web Server:** Nginx
* **Database:** MySQL 8.0
* **Containerization:** Docker & Docker Compose
* **Dependency Management:** Composer
* **Frontend:** Bootstrap 5
* **Testing:** PHPUnit

## Setup and Installation

Follow these steps to get the application running on your local machine.

### Prerequisites

* Docker Desktop installed and running.
* A terminal or command-line interface.

### 1. Clone the Repository

```bash
git clone <your-repository-url>
cd <repository-folder>

2. Build and Run the Docker Containers
This command will build the custom PHP image and start the PHP, Nginx, and MySQL services in the background.

# First, build the images without cache to ensure a clean build
docker-compose build --no-cache

# Then, start the services
docker-compose up -d

3. Install Composer Dependencies
This command runs composer install inside the app container to install PHPUnit.

docker-compose run --rm app composer install

4. Run Database Migrations
This command executes a PHP script inside the app container to create the necessary items table in the database.

docker-compose exec app php bin/migrate.php

5. Access the Application
The application should now be running. You can access it in your web browser at:

http://localhost:8080/items

Running Tests
To run the PHPUnit test suite, execute the following command from your project root:

docker-compose exec app ./vendor/bin/phpunit

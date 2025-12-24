# Task Manager Demo App

This is a simple demo of a Project Task Manager, built with PHP (Laravel) for the backend and Livewire for the frontend.
This project include Unit Testing for both Backend functionality and Frontend Livewire functionality.

# Initial Setup
Use the green Code button at the top to select the Download Zip option. 

Unzip the downloaded file.

Open a terminal and cd into the folder where the files were unzipped.

Ensure your user has write permissions on this folder.

# Local Installation with Docker

To install and run the app using Docker:

sh deploy_docker.sh

or

bash deploy_docker.sh

This will automatically set up the environment, including PHP, MySQL, and NodeJS.

The app should be available at http://localhost:60

# Local Installation using Artisan Serve

If you prefer to run the app without Docker, make sure you have installed the following on your system:

PHP 8.x

Composer

MySQL

NodeJS + NPM

Database Setup

Open the .env.laravel file and check the database configuration:

DB_PORT=3306

DB_DATABASE=laravel

DB_USERNAME=sail

DB_PASSWORD=password


Create a MySQL database matching the configuration above. You can also modify these values as needed, but make sure the database exists.

Run Deployment Script

sh deploy_serve.sh

or

bash deploy_serve.sh

This will use php artisan serve

The app should be available at http://localhost:60

# Resources

The App was developed usin VS Code Version: 1.106.2
The suite is using Github Copilot.

For the drag and drop functionality we used the following JS Library:
https://sortablejs.github.io/Sortable

# HUD App - Backend REST API

A RESTful API built with **Laravel** serving as the backend for a custom Android-based Head-Up Display (HUD) application. This API functions as a community-driven platform, allowing users to store, share, and interact with custom HUD layouts.

## 🚀 Key Features

* **Layout Management:** Securely store, retrieve, and manage user-created HUD configurations formatted as JSON objects.
* **Media Handling:** Endpoints for uploading and serving images (e.g., layout screenshots/thumbnails).
* **Community & Social Ecosystem:** * Like and rate shared layouts.
    * Track download statistics.
    * Browse popular and highly-rated community designs.
* **Complex Database Architecture:** Features a well-structured relational database with multiple entities (Users, Layouts, Likes, Ratings, Downloads) managed via Laravel Eloquent ORM and database migrations.

## 🛠️ Tech Stack

* **Language:** PHP
* **Framework:** Laravel
* **Database:** MySQL
* **Architecture:** REST API

## ⚙️ Local Setup

To run this project locally, follow these standard Laravel installation steps:

1. Clone the repository:
   ```bash
   git clone [https://github.com/AdrianJurak/HUD_backend.git](https://github.com/AdrianJurak/HUD_backend.git)
   ```
2. Install dependencies:
    ```bash
   composer install
   ```
3. Set up your enviroment file:
    ```bash
   cp .env.example .env
   ```
4. Start the Docker containers via Sail
    ```bash
   ./vendor/bin/sail up -d
   ```
5. Generate the application key and run database migrations and seeders
    ```bash
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate:fresh --seed
   ```

# 🏎️ HUD App - Backend REST API

A robust RESTful API built with **Laravel**, serving as the backend for a custom ESP32-based Head-Up Display (HUD) telemetry application. This API functions as a community-driven platform, allowing users to securely store, share, and interact with custom HUD layouts and telemetry configurations.

## 🚀 Live Demo & Admin Panel

The application is fully containerized (Docker) and deployed on Render. You can explore the Filament-based Admin Dashboard here:

* **URL:** https://hud-backend-i7vx.onrender.com/admin
* **Login:** `admin@admin.com`
* **Password:** `password`

*(Note: The database is intentionally seeded with sample telemetry categories and HUD configurations for demonstration purposes).*

## ✨ Key Features

* **Admin Dashboard (Filament):** A fully-featured, responsive admin panel with statistical widgets, activity charts, and dark mode support.
* **Layout Management:** Securely store, retrieve, and manage user-created HUD configurations formatted as JSON objects.
* **Media Handling:** Endpoints for uploading and serving images (e.g., layout screenshots/thumbnails).
* **Community & Social Ecosystem:** * Like and rate shared layouts.
    * Track download statistics.
    * Browse popular and highly-rated community designs.
* **Relational Architecture:** A well-structured database featuring multiple entities (Users, Themes, Categories, Reviews, Downloads) managed via Laravel Eloquent ORM.

## 🛠️ Tech Stack

* **Language:** PHP 8.4
* **Framework:** Laravel 12
* **Admin Panel:** FilamentPHP
* **Database:** PostgreSQL (Production) / MySQL (Local environment)
* **Infrastructure:** Docker, Render (Cloud Hosting)

## 📄 API Documentation

A complete **Postman Collection** is included in this repository to quickly test all available REST endpoints.
* Import the `HUD_API_Collection.json` file located in the root directory into your Postman workspace.

## ⚙️ Local Setup

To run this project locally, follow these standard Laravel installation steps using Sail (Docker):

1. Clone the repository:
   ```bash
   git clone [https://github.com/AdrianJurak/HUD_backend.git](https://github.com/AdrianJurak/HUD_backend.git)
   ```
   
2. Install dependencies:
   ```bash
      composer install
   ```
3. Set up your environment file:
    ```bash
      cp .env.example .env
    ```
4. Start the Docker containers via Sail:
    ```bash
      ./vendor/bin/sail up -d
    ```
5. Generate the application key and run database migrations with seeders:
   ```bash
    ./vendor/bin/sail artisan key:generate
    ./vendor/bin/sail artisan migrate:fresh --seed
   ```

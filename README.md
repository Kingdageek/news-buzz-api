## News Buzz API

News Buzz is a news aggregator platform built on Laravel. This repo provides APIs that supports the platform.

There are three main entities:

-   Datasources: External APIs that news are fetched from (this is entirely hidden from users)
-   Sources: Actual News sources, some external APIs have several of these
-   Categories: News sources categorize their articles

### Current Features

-   Authentication: User login and registration
-   User Roles: Just two currently available (READER & ADMIN) (see class `App\Constants\UserRole`)
-   Preferences/Customization: User can choose which news sources and categories to see on their feed
-   Newsfeed: News aggregated from the available data sources depending on chosen preferences
-   Search & filter: News can be searched by search keyword and filtered using source, category, and date
-   News Sources and Categories from different datasources can be fetched
-   Datasources can be auto deactivated by admins
-   Datasources, News Sources and Categories can be auto updated by admins
-   Basic caching

### To Run

The app uses Laravel sail that provides an easy way to run docker apps, so, you have to have _Docker compose_ installed.
All typical laravel console commands can be run in sail using `sail artisan CMD-NAME`. The typical docker compose syntax still works too, in this case, you'd do: `docker compose exec CONTAINER-NAME php artisan CMD-NAME`.

**STEPS**

-   copy the `.env.example` file to a `.env` file in the same base directory:
    `cp .env.example .env`
-   Run sail: `sail up` or in detached mode with `sail up -d`
-   Generate app key: `sail artisan key:generate`
-   Generate JWT secret: `sail artisan jwt:secret`
-   Migrate the database: `sail artisan migrate`
-   Seed the DB with the dummy admin: `sail artisan db:seed` (this can be skipped actually but then you can't currently create admins via the register endpoint)
-   Get API keys from the external sources used (see the _EXTERNAL SOURCES_ section of the `.env.example` file to see the external sources used)
-   Initialize the main entities: `sail artisan app:update-main-entities`

If there are no errors, then you're ready to go :)
Explore the Routes! You should be able to fetch categories and sources now.

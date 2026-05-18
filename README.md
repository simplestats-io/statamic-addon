# Statamic Addon for SimpleStats.io

[![Latest Version on Packagist](https://img.shields.io/packagist/v/simplestats-io/statamic-addon.svg?style=flat-square)](https://packagist.org/packages/simplestats-io/statamic-addon)
[![Tests](https://github.com/simplestats-io/statamic-addon/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/simplestats-io/statamic-addon/actions/workflows/run-tests.yml)
[![Fix PHP code style issues](https://github.com/simplestats-io/statamic-addon/actions/workflows/fix-php-code-style-issues.yml/badge.svg?branch=main)](https://github.com/simplestats-io/statamic-addon/actions/workflows/fix-php-code-style-issues.yml)
[![License](https://img.shields.io/packagist/l/simplestats-io/statamic-addon.svg?style=flat-square)](https://packagist.org/packages/simplestats-io/statamic-addon)
[![Laravel Compatibility](https://badge.laravel.cloud/badge/simplestats-io/statamic-addon)](https://packagist.org/packages/simplestats-io/statamic-addon)

The official [Statamic v6](https://statamic.com) addon for [SimpleStats.io](https://simplestats.io). View your analytics directly inside the Statamic Control Panel: visitors, registrations, revenue, top referrers, top sources, and top countries.

![screenshot](https://simplestats.io/images/screenshot_statamic.jpg)

## Requirements

- PHP 8.2+
- Statamic 6+
- A SimpleStats.io account with an API token

## Installation

Install via Composer:

```bash
composer require simplestats-io/statamic-addon
```

Optionally publish the config file:

```bash
php artisan vendor:publish --tag="simplestats-config"
```

Add your API token to `.env`:

```env
SIMPLESTATS_API_TOKEN=your-api-token-here
```

That's it. A new **SimpleStats** entry appears under the **Tools** section of the Control Panel navigation. Open it to see your dashboard.

## Configuration

All configuration is optional. The addon works out of the box with just an API token.

### Config File

If you published the config, you can override the defaults via environment variables:

```php
// config/simplestats.php

return [

    /*
     |--------------------------------------------------------------------------
     | SimpleStats API Credentials
     |--------------------------------------------------------------------------
     |
     | Define your API credentials here. The API URL defaults to the hosted
     | SimpleStats instance. If you are self-hosting, change it to your own
     | URL. The API token is the same one used by the Laravel client package.
     |
     */

    'api_url' => env('SIMPLESTATS_API_URL', 'https://simplestats.io/api/v1'),

    'api_token' => env('SIMPLESTATS_API_TOKEN'),

    /*
     |--------------------------------------------------------------------------
     | Cache Duration
     |--------------------------------------------------------------------------
     |
     | API responses are cached to avoid unnecessary requests on every page
     | load. Multiple widgets sharing the same filters will reuse a single
     | cached response. Set to 0 to disable caching.
     |
     */

    'cache_ttl' => 60,

];
```

## Dashboard Widgets

The addon provides a dedicated dashboard page in the Control Panel:

| Widget | Description |
|--------|-------------|
| **Stats Overview** | KPI cards: Visitors, Registrations, CR, Net Revenue, ARPU, ARPV. |
| **Visitors & Registrations** | Line chart showing visitors and registrations over time. |
| **Revenue** | Line chart showing gross and net revenue over time. |
| **Top Referrers** | Table of top referrers with visitors, registrations, CR, DAU, and revenue. |
| **Top Sources** | Top traffic sources by UTM. |
| **Top Countries** | Top visitor countries. |
| **Entry Pages** | Top landing pages. |

A time range filter lets you switch between presets (Today, Last 7 Days, Last 30 Days, This Year, All Time, etc.).

## Self-Hosted

If you are running a self-hosted SimpleStats instance, point the addon to your own API via `.env`:

```env
SIMPLESTATS_API_URL=https://your-simplestats-instance.com/api/v1
SIMPLESTATS_API_TOKEN=your-api-token-here
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Zacharias Creutznacher](https://github.com/sairahcaz)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

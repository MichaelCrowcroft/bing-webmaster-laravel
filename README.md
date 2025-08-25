## Installation

You can install the package via composer:

```bash
composer require michaelcrowcroft/bing-webmaster-laravel
```

## Configuration

### Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="MichaelCrowcroft\\BingWebmaster\\BingWebmasterServiceProvider" --tag="bing-webmaster-config"
```

## Token Management

This package requires access tokens to be passed explicitly. This design supports multi-tenant applications where different users have different Bing Webmaster tokens.

### Setting Access Tokens

You must set the access token before making API calls:

```php
use BingWebmaster;

// Method 1: Set token on facade instance
BingWebmaster::setAccessToken($user->bing_access_token);

// Method 2: Create instance with token
$bing = new BingWebmaster($user->bing_access_token);

// Method 3: Create instance and set token
$bing = new BingWebmaster();
$bing->setAccessToken($user->bing_access_token);
```

## Usage

### Basic Setup

```php
use BingWebmaster;

class BingWebmasterController extends Controller
{
    public function dashboard(Request $request)
    {
        try {
            // Set the user's access token (required)
            BingWebmaster::setAccessToken($request->user()->bing_access_token);

            // Get user sites
            $sitesRequest = BingWebmaster::getUserSites();
            $sites = $sitesRequest->getFormattedSites();

            if (empty($sites)) {
                return view('bing.dashboard', [
                    'error' => 'No sites found in Bing Webmaster Tools.'
                ]);
            }

            $siteUrl = $sites[0]['url']; // Adjust based on actual response structure

            // Get rank and traffic statistics
            $trafficRequest = BingWebmaster::getRankAndTrafficStats($siteUrl, [
                'start_date' => now()->subDays(30)->format('Y-m-d'),
                'end_date' => now()->subDay()->format('Y-m-d'),
                'aggregation' => 'daily'
            ]);

            $trafficStats = $trafficRequest->getFormattedStats();

            return view('bing.dashboard', [
                'sites' => $sites,
                'trafficStats' => $trafficStats,
                'siteUrl' => $siteUrl
            ]);

        } catch (\Exception $e) {
            Log::error('Bing Webmaster API error', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);

            return view('bing.dashboard', [
                'error' => 'Failed to fetch data from Bing Webmaster. Please try again later.'
            ]);
        }
    }
}
```

### Using the Facade

```php
use BingWebmaster;

class BingService
{
    public function getSitePerformance(string $siteUrl, string $accessToken, int $days = 30): array
    {
        // Set the access token for this user's session
        BingWebmaster::setAccessToken($accessToken);

        $endDate = now()->subDay()->format('Y-m-d');
        $startDate = now()->subDays($days)->format('Y-m-d');

        return [
            'traffic' => BingWebmaster::getRankAndTrafficStats($siteUrl, [
                'start_date' => $startDate,
                'end_date' => $endDate
            ])->getFormattedStats(),

            'keywords' => BingWebmaster::getKeywordStats($siteUrl, [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'limit' => 50
            ])->getFormattedStats(),

            'pages' => BingWebmaster::getPageStats($siteUrl, [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'limit' => 50
            ])->getFormattedStats(),

            'queries' => BingWebmaster::getQueryStats($siteUrl, [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'limit' => 50
            ])->getFormattedStats(),

            'period' => "$startDate to $endDate",
        ];
    }
}
```

## API Endpoints

### Get User Sites

Get all sites accessible to the authenticated user:

```php
use BingWebmaster;

$sitesRequest = BingWebmaster::getUserSites();
$sites = $sitesRequest->getFormattedSites();

// Alternative: Execute directly
$sitesResponse = BingWebmaster::getUserSites()->getFormattedSites();
```

### Rank and Traffic Statistics

Get rank and traffic performance data:

```php
$siteUrl = 'https://www.example.com';

// Basic traffic stats
$trafficStats = BingWebmaster::getRankAndTrafficStats($siteUrl, [
    'start_date' => '2024-01-01',
    'end_date' => '2024-01-31',
    'aggregation' => 'daily'
])->getFormattedStats();

// Get summary only
$summary = BingWebmaster::getRankAndTrafficStats($siteUrl)->getSummary();

// Get daily data only
$dailyData = BingWebmaster::getRankAndTrafficStats($siteUrl)->getData();
```

### Keyword Statistics

Get keyword-level performance data:

```php
$keywordStats = BingWebmaster::getKeywordStats($siteUrl, [
    'start_date' => '2024-01-01',
    'end_date' => '2024-01-31',
    'limit' => 100
])->getFormattedStats();

// Get top keywords by clicks
$topKeywords = BingWebmaster::getKeywordStats($siteUrl)->getTopKeywordsByClicks(10);

// Get top keywords by impressions
$topImpressions = BingWebmaster::getKeywordStats($siteUrl)->getTopKeywordsByImpressions(10);
```

### Page Statistics

Get page-level performance data:

```php
$pageStats = BingWebmaster::getPageStats($siteUrl, [
    'start_date' => '2024-01-01',
    'end_date' => '2024-01-31',
    'limit' => 100
])->getFormattedStats();

// Get top pages by clicks
$topPages = BingWebmaster::getPageStats($siteUrl)->getTopPagesByClicks(10);

// Get top pages by impressions
$topImpressions = BingWebmaster::getPageStats($siteUrl)->getTopPagesByImpressions(10);

// Get best performing pages by position
$bestPosition = BingWebmaster::getPageStats($siteUrl)->getTopPagesByPosition(10);
```

### Query Statistics

Get search query performance data:

```php
$queryStats = BingWebmaster::getQueryStats($siteUrl, [
    'start_date' => '2024-01-01',
    'end_date' => '2024-01-31',
    'limit' => 100
])->getFormattedStats();

// Get top queries by clicks
$topQueries = BingWebmaster::getQueryStats($siteUrl)->getTopQueriesByClicks(10);

// Get top queries by impressions
$topImpressions = BingWebmaster::getQueryStats($siteUrl)->getTopQueriesByImpressions(10);
```

### URL Submission

Submit URLs for indexing:

```php
// Submit a single URL
$result = BingWebmaster::submitUrl($siteUrl, 'https://www.example.com/new-page')->submit();

// Check if submission was successful
$success = BingWebmaster::submitUrl($siteUrl, 'https://www.example.com/new-page')->wasSuccessful();
```

### Sitemap Submission

Submit sitemaps for crawling:

```php
// Submit a single sitemap
$result = BingWebmaster::submitSitemap($siteUrl, 'https://www.example.com/sitemap.xml')->submit();

// Submit multiple sitemaps
$sitemapUrls = [
    'https://www.example.com/sitemap.xml',
    'https://www.example.com/blog-sitemap.xml',
    'https://www.example.com/products-sitemap.xml'
];
$results = BingWebmaster::submitSitemap($siteUrl, '')->submitMultiple($siteUrl, $sitemapUrls);
```

## Laravel Commands

Create a command to generate Bing Webmaster reports:

```php
<?php

namespace App\Console\Commands;

use BingWebmaster;
use Illuminate\Console\Command;

class GenerateBingReport extends Command
{
    protected $signature = 'bing:report {site} {--days=30} {--token=}';
    protected $description = 'Generate Bing Webmaster report';

    public function handle()
    {
        $siteUrl = $this->argument('site');
        $days = (int) $this->option('days');
        $accessToken = $this->option('token') ?? config('bing-webmaster.access_token');

        if (!$accessToken) {
            $this->error('Access token is required. Use --token option or set BING_WEBMASTER_ACCESS_TOKEN in config.');
            return 1;
        }

        // Set the access token
        BingWebmaster::setAccessToken($accessToken);

        $this->info("Generating Bing Webmaster report for $siteUrl ($days days)...");

        $endDate = now()->subDay()->format('Y-m-d');
        $startDate = now()->subDays($days)->format('Y-m-d');

        // Get traffic stats
        $trafficStats = BingWebmaster::getRankAndTrafficStats($siteUrl, [
            'start_date' => $startDate,
            'end_date' => $endDate
        ])->getSummary();

        // Get keyword stats
        $keywordStats = BingWebmaster::getKeywordStats($siteUrl, [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'limit' => 20
        ])->getSummary();

        $this->info('=== Traffic Summary ===');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Clicks', $trafficStats['total_clicks']],
                ['Total Impressions', $trafficStats['total_impressions']],
                ['Average Position', number_format($trafficStats['average_position'], 1)],
                ['Average CTR', number_format($trafficStats['average_ctr'] * 100, 2) . '%'],
            ]
        );

        $this->info('=== Keyword Summary ===');
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Keywords', $keywordStats['total_keywords']],
                ['Total Clicks', $keywordStats['total_clicks']],
                ['Average CTR', number_format($keywordStats['average_ctr'] * 100, 2) . '%'],
            ]
        );

        $this->info('Report generated successfully!');
    }
}
```

## Token Management

This package assumes you're using Laravel Socialite or another OAuth solution to obtain and manage Bing Webmaster API tokens:

```php
// Set access token programmatically
BingWebmaster::setAccessToken($accessToken);

// Check if token is valid
if (BingWebmaster::isAccessTokenValid()) {
    // Token is valid, proceed with API calls
}

// Get current access token
$currentToken = BingWebmaster::getAccessToken();
```

### Laravel Socialite Integration Example

If you're using Laravel Socialite, you can integrate it like this:

```php
// In your controller
public function handleCallback(Request $request)
{
    $bingUser = Socialite::driver('bing')->user();

    // Store the access token
    $request->session()->put('bing_access_token', $bingUser->token);

    // Or save to database
    // auth()->user()->update(['bing_access_token' => $bingUser->token]);

    return redirect('/dashboard');
}

// Then use the token in your API calls
public function dashboard(Request $request)
{
    $token = $request->session()->get('bing_access_token');
    // Or from database: auth()->user()->bing_access_token;

    if ($token) {
        BingWebmaster::setAccessToken($token);
        // Make API calls...
    }
}
```

## Configuration Options

The package comes with a comprehensive configuration file:

```php
// config/bing-webmaster.php
return [
    'access_token' => null, // Not used - set tokens explicitly

    'timeout' => 30,
    'retry_attempts' => 3,

    'api' => [
        'base_url' => 'https://www.bing.com/webmaster/api.svc/json',
        'rank_and_traffic' => [
            'default_date_range' => [
                'start_date' => '-30 days',
                'end_date' => 'yesterday',
            ],
        ],
        'keyword_stats' => [
            'default_limit' => 1000,
        ],
        'page_stats' => [
            'default_limit' => 1000,
        ],
        'query_stats' => [
            'default_limit' => 1000,
        ],
    ],

    'cache' => [
        'enabled' => false,
        'ttl' => 3600,
        'prefix' => 'bing_webmaster_',
    ],

    'debug' => false,
    'rate_limiting' => [
        'enabled' => true,
        'max_requests_per_minute' => 60,
    ],
];
```

## Error Handling

The package uses standard exceptions. Make sure to handle them appropriately:

```php
try {
    $data = BingWebmaster::getRankAndTrafficStats($siteUrl)->getFormattedStats();
} catch (\Exception $e) {
    // Handle API error
    Log::error('Bing Webmaster API error: ' . $e->getMessage());
}
```

### Common Issues

**"No access token provided"**: Make sure to set an access token before making API calls:

```php
// ❌ This will fail
$sites = BingWebmaster::getUserSites()->getFormattedSites();

// ✅ Do this instead
BingWebmaster::setAccessToken($user->bing_access_token);
$sites = BingWebmaster::getUserSites()->getFormattedSites();
```

## Testing

Run the tests with:

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@michaelcrowcroft.com instead of using the issue tracker.

## Credits

- [Michael Crowcroft](https://github.com/michaelcrowcroft)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
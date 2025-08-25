<?php

namespace MichaelCrowcroft\BingWebmaster\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Get Page Statistics Request
 *
 * Retrieves page-level performance data including impressions,
 * clicks, average position, and CTR for individual pages.
 */
class GetPageStats extends Request
{
    /**
     * The HTTP method for this request
     */
    protected Method $method = Method::GET;

    /**
     * Constructor
     */
    public function __construct(
        protected string $siteUrl,
        protected array $options = []
    ) {
        $this->siteUrl = $siteUrl;
        $this->options = $options;
    }

    /**
     * The endpoint for this request
     */
    public function resolveEndpoint(): string
    {
        return '/GetPageStats';
    }

    /**
     * Default query parameters
     */
    public function defaultQuery(): array
    {
        $params = [
            'siteUrl' => $this->siteUrl,
        ];

        // Add date range if provided
        if (isset($this->options['start_date'])) {
            $params['startDate'] = $this->options['start_date'];
        }

        if (isset($this->options['end_date'])) {
            $params['endDate'] = $this->options['end_date'];
        }

        // Add limit if provided
        if (isset($this->options['limit'])) {
            $params['limit'] = $this->options['limit'];
        } else {
            $params['limit'] = config('bing-webmaster.api.page_stats.default_limit', 1000);
        }

        // Add offset for pagination if provided
        if (isset($this->options['offset'])) {
            $params['offset'] = $this->options['offset'];
        }

        return $params;
    }

    /**
     * Convert the response to a usable format
     */
    public function createDtoFromResponse(\Saloon\Http\Response $response): array
    {
        $data = $response->json();

        // Handle different response formats
        if (isset($data['d'])) {
            // OData format
            $result = $data['d'];
        } elseif (isset($data['value'])) {
            // Modern API format
            $result = $data['value'];
        } else {
            $result = $data ?? [];
        }

        // Format the data for easier consumption
        return $this->formatPageStats($result);
    }

    /**
     * Format the page statistics data
     */
    protected function formatPageStats(array $data): array
    {
        $formatted = [
            'summary' => [
                'total_pages' => count($data),
                'total_clicks' => 0,
                'total_impressions' => 0,
                'average_position' => 0,
                'average_ctr' => 0,
            ],
            'pages' => [],
        ];

        if (!empty($data)) {
            $totalPosition = 0;
            $totalCtr = 0;
            $positionCount = 0;
            $ctrCount = 0;

            foreach ($data as $item) {
                // Handle different data formats from Bing API
                $pageUrl = $item['PageUrl'] ?? $item['page_url'] ?? $item['url'] ?? '';
                $clicks = (int)($item['Clicks'] ?? $item['clicks'] ?? 0);
                $impressions = (int)($item['Impressions'] ?? $item['impressions'] ?? 0);
                $position = (float)($item['AveragePosition'] ?? $item['average_position'] ?? $item['position'] ?? 0);
                $ctr = (float)($item['CTR'] ?? $item['ctr'] ?? 0);

                $formattedPage = [
                    'page_url' => $pageUrl,
                    'clicks' => $clicks,
                    'impressions' => $impressions,
                    'average_position' => $position,
                    'ctr' => $ctr,
                ];

                $formatted['pages'][] = $formattedPage;

                // Update summary
                $formatted['summary']['total_clicks'] += $clicks;
                $formatted['summary']['total_impressions'] += $impressions;

                if ($position > 0) {
                    $totalPosition += $position;
                    $positionCount++;
                }

                if ($ctr > 0) {
                    $totalCtr += $ctr;
                    $ctrCount++;
                }
            }

            // Calculate averages
            if ($positionCount > 0) {
                $formatted['summary']['average_position'] = $totalPosition / $positionCount;
            }

            if ($ctrCount > 0) {
                $formatted['summary']['average_ctr'] = $totalCtr / $ctrCount;
            }
        }

        return $formatted;
    }

    /**
     * Get the formatted page statistics
     */
    public function getFormattedStats(): array
    {
        $response = $this->send();
        return $this->createDtoFromResponse($response);
    }

    /**
     * Get summary statistics only
     */
    public function getSummary(): array
    {
        $stats = $this->getFormattedStats();
        return $stats['summary'];
    }

    /**
     * Get page data only
     */
    public function getPages(): array
    {
        $stats = $this->getFormattedStats();
        return $stats['pages'];
    }

    /**
     * Get top performing pages by clicks
     */
    public function getTopPagesByClicks(int $limit = 10): array
    {
        $pages = $this->getPages();

        // Sort by clicks descending
        usort($pages, function($a, $b) {
            return $b['clicks'] <=> $a['clicks'];
        });

        return array_slice($pages, 0, $limit);
    }

    /**
     * Get top performing pages by impressions
     */
    public function getTopPagesByImpressions(int $limit = 10): array
    {
        $pages = $this->getPages();

        // Sort by impressions descending
        usort($pages, function($a, $b) {
            return $b['impressions'] <=> $a['impressions'];
        });

        return array_slice($pages, 0, $limit);
    }

    /**
     * Get pages with best average position
     */
    public function getTopPagesByPosition(int $limit = 10): array
    {
        $pages = $this->getPages();

        // Sort by position ascending (lower is better)
        usort($pages, function($a, $b) {
            return $a['average_position'] <=> $b['average_position'];
        });

        return array_slice($pages, 0, $limit);
    }
}

<?php

namespace MichaelCrowcroft\BingWebmaster\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Get Rank and Traffic Statistics Request
 *
 * Retrieves rank and traffic statistics for a specific site including
 * impressions, clicks, average position, and CTR over a specified date range.
 */
class GetRankAndTrafficStats extends Request
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
        return '/GetRankAndTrafficStats';
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

        // Add aggregation if provided (daily, weekly, monthly)
        if (isset($this->options['aggregation'])) {
            $params['aggregation'] = $this->options['aggregation'];
        }

        // Add limit if provided
        if (isset($this->options['limit'])) {
            $params['limit'] = $this->options['limit'];
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
        return $this->formatTrafficStats($result);
    }

    /**
     * Format the traffic statistics data
     */
    protected function formatTrafficStats(array $data): array
    {
        $formatted = [
            'summary' => [
                'total_clicks' => 0,
                'total_impressions' => 0,
                'average_position' => 0,
                'average_ctr' => 0,
            ],
            'data' => [],
        ];

        if (!empty($data)) {
            foreach ($data as $item) {
                // Handle different data formats from Bing API
                $formattedItem = [
                    'date' => $item['Date'] ?? $item['date'] ?? null,
                    'clicks' => (int)($item['Clicks'] ?? $item['clicks'] ?? 0),
                    'impressions' => (int)($item['Impressions'] ?? $item['impressions'] ?? 0),
                    'average_position' => (float)($item['AveragePosition'] ?? $item['average_position'] ?? 0),
                    'ctr' => (float)($item['CTR'] ?? $item['ctr'] ?? 0),
                ];

                $formatted['data'][] = $formattedItem;

                // Update summary
                $formatted['summary']['total_clicks'] += $formattedItem['clicks'];
                $formatted['summary']['total_impressions'] += $formattedItem['impressions'];
            }

            // Calculate averages
            $count = count($formatted['data']);
            if ($count > 0) {
                $formatted['summary']['average_position'] = array_sum(array_column($formatted['data'], 'average_position')) / $count;
                $formatted['summary']['average_ctr'] = array_sum(array_column($formatted['data'], 'ctr')) / $count;
            }
        }

        return $formatted;
    }

    /**
     * Get the formatted traffic statistics
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
     * Get daily data only
     */
    public function getData(): array
    {
        $stats = $this->getFormattedStats();
        return $stats['data'];
    }
}

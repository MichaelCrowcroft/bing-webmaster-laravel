<?php

namespace MichaelCrowcroft\BingWebmaster\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Submit Sitemap Request
 *
 * Submits a sitemap to Bing for crawling and indexing.
 */
class SubmitSitemap extends Request
{
    /**
     * The HTTP method for this request
     */
    protected Method $method = Method::POST;

    /**
     * Constructor
     */
    public function __construct(
        protected string $siteUrl,
        protected string $sitemapUrl
    ) {
        $this->siteUrl = $siteUrl;
        $this->sitemapUrl = $sitemapUrl;
    }

    /**
     * The endpoint for this request
     */
    public function resolveEndpoint(): string
    {
        return '/SubmitSitemap';
    }

    /**
     * Request body data
     */
    public function defaultBody(): array
    {
        return [
            'siteUrl' => $this->siteUrl,
            'sitemapUrl' => $this->sitemapUrl,
        ];
    }

    /**
     * Convert the response to a usable format
     */
    public function createDtoFromResponse(\Saloon\Http\Response $response): array
    {
        $data = $response->json();

        // Handle successful submission response
        if ($response->successful()) {
            return [
                'success' => true,
                'message' => $data['message'] ?? $data['Message'] ?? 'Sitemap submitted successfully',
                'site_url' => $this->siteUrl,
                'sitemap_url' => $this->sitemapUrl,
                'data' => $data,
            ];
        }

        // Handle error response
        return [
            'success' => false,
            'message' => $data['message'] ?? $data['Message'] ?? $data['error'] ?? 'Failed to submit sitemap',
            'site_url' => $this->siteUrl,
            'sitemap_url' => $this->sitemapUrl,
            'data' => $data,
        ];
    }

    /**
     * Submit the sitemap and get formatted response
     */
    public function submit(): array
    {
        $response = $this->send();
        return $this->createDtoFromResponse($response);
    }

    /**
     * Check if the submission was successful
     */
    public function wasSuccessful(): bool
    {
        $response = $this->send();
        return $response->successful();
    }

    /**
     * Get the site URL the sitemap is being submitted to
     */
    public function getSiteUrl(): string
    {
        return $this->siteUrl;
    }

    /**
     * Get the sitemap URL being submitted
     */
    public function getSitemapUrl(): string
    {
        return $this->sitemapUrl;
    }

    /**
     * Submit multiple sitemaps at once
     */
    public static function submitMultiple(string $siteUrl, array $sitemapUrls): array
    {
        $results = [];

        foreach ($sitemapUrls as $sitemapUrl) {
            $request = new self($siteUrl, $sitemapUrl);
            $results[] = $request->submit();
        }

        return $results;
    }
}

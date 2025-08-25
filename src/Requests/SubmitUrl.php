<?php

namespace MichaelCrowcroft\BingWebmaster\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Submit URL Request
 *
 * Submits a URL to Bing for indexing/crawling.
 */
class SubmitUrl extends Request
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
        protected string $urlToSubmit
    ) {
        $this->siteUrl = $siteUrl;
        $this->urlToSubmit = $urlToSubmit;
    }

    /**
     * The endpoint for this request
     */
    public function resolveEndpoint(): string
    {
        return '/SubmitUrl';
    }

    /**
     * Request body data
     */
    public function defaultBody(): array
    {
        return [
            'siteUrl' => $this->siteUrl,
            'url' => $this->urlToSubmit,
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
                'message' => $data['message'] ?? $data['Message'] ?? 'URL submitted successfully',
                'site_url' => $this->siteUrl,
                'submitted_url' => $this->urlToSubmit,
                'data' => $data,
            ];
        }

        // Handle error response
        return [
            'success' => false,
            'message' => $data['message'] ?? $data['Message'] ?? $data['error'] ?? 'Failed to submit URL',
            'site_url' => $this->siteUrl,
            'submitted_url' => $this->urlToSubmit,
            'data' => $data,
        ];
    }

    /**
     * Submit the URL and get formatted response
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
     * Get the site URL being submitted to
     */
    public function getSiteUrl(): string
    {
        return $this->siteUrl;
    }

    /**
     * Get the URL being submitted
     */
    public function getUrlToSubmit(): string
    {
        return $this->urlToSubmit;
    }
}

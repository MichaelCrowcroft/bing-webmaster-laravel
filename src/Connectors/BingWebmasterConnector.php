<?php

namespace MichaelCrowcroft\BingWebmaster\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Http\Request;
use Saloon\Http\Response;

/**
 * Bing Webmaster API Connector
 *
 * This connector handles authentication and communication with the Bing Webmaster API.
 * It uses Bearer token authentication.
 */
class BingWebmasterConnector extends Connector
{
    use AcceptsJson;

    /**
     * The base URL for the Bing Webmaster API
     */
    public function resolveBaseUrl(): string
    {
        return 'https://www.bing.com/webmaster/api.svc/json';
    }

    /**
     * Default headers for all requests
     */
    protected function defaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Set the OAuth access token for authentication
     */
    public function withToken(string $token): self
    {
        return $this->withTokenAuth($token, 'Bearer');
    }

    /**
     * Boot the connector with authentication
     */
    public function boot(\Saloon\Http\PendingRequest $pendingRequest): void
    {
        // Access tokens should be set explicitly via withToken() method
        // This allows for multi-tenant applications with different tokens per user
    }

    /**
     * Handle response processing
     */
    public function handleResponse(Response $response): Response
    {
        // Bing API sometimes returns responses in different formats
        // Handle any common response transformations here

        return $response;
    }
}

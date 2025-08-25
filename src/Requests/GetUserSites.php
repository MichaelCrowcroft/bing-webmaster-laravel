<?php

namespace MichaelCrowcroft\BingWebmaster\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Get User Sites Request
 *
 * Retrieves all sites that the authenticated user has access to
 * in their Bing Webmaster Tools account.
 */
class GetUserSites extends Request
{
    /**
     * The HTTP method for this request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for this request
     */
    public function resolveEndpoint(): string
    {
        return '/GetUserSites';
    }

    /**
     * Convert the response to a usable format
     */
    public function createDtoFromResponse(\Saloon\Http\Response $response): array
    {
        $data = $response->json();

        // Bing API typically returns data in a specific format
        // Handle different response structures
        if (isset($data['d'])) {
            // OData format
            return $data['d'];
        }

        if (isset($data['value'])) {
            // Modern API format
            return $data['value'];
        }

        // Direct array response
        return $data ?? [];
    }

    /**
     * Get the formatted sites data
     */
    public function getFormattedSites(): array
    {
        $response = $this->send();
        return $this->createDtoFromResponse($response);
    }
}

<?php

namespace MichaelCrowcroft\BingWebmaster;

use MichaelCrowcroft\BingWebmaster\Connectors\BingWebmasterConnector;
use MichaelCrowcroft\BingWebmaster\Requests\GetUserSites;
use MichaelCrowcroft\BingWebmaster\Requests\GetRankAndTrafficStats;
use MichaelCrowcroft\BingWebmaster\Requests\GetKeywordStats;
use MichaelCrowcroft\BingWebmaster\Requests\GetPageStats;
use MichaelCrowcroft\BingWebmaster\Requests\GetQueryStats;
use MichaelCrowcroft\BingWebmaster\Requests\SubmitUrl;
use MichaelCrowcroft\BingWebmaster\Requests\SubmitSitemap;

/**
 * Main Bing Webmaster API client
 *
 * This class provides access to all Bing Webmaster API endpoints
 * and handles access token management using Saloon.
 */
class BingWebmaster
{
    protected BingWebmasterConnector $connector;
    protected ?string $accessToken = null;

    /**
     * Create a new Bing Webmaster instance
     *
     * @param string|null $accessToken Access token (must be provided explicitly)
     * @param array $config Additional configuration options
     */
    public function __construct(?string $accessToken = null, array $config = [])
    {
        $this->accessToken = $accessToken;
        $this->initializeConnector($config);
    }

    /**
     * Initialize the Bing Webmaster connector
     *
     * @param array $config
     * @return void
     */
    protected function initializeConnector(array $config = []): void
    {
        $this->connector = new BingWebmasterConnector();

        if ($this->accessToken) {
            $this->connector->withToken($this->accessToken);
        }

        // Apply any additional configuration
        if (isset($config['timeout'])) {
            $this->connector->setTimeout($config['timeout']);
        }

        if (isset($config['retries'])) {
            $this->connector->setRetries($config['retries']);
        }
    }

    /**
     * Set the access token
     *
     * @param string $accessToken
     * @return self
     */
    public function setAccessToken(string $accessToken): self
    {
        $this->accessToken = $accessToken;
        $this->connector->withToken($accessToken);

        return $this;
    }

    /**
     * Get the current access token
     *
     * @return string|null
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * Check if the access token is valid
     *
     * @return bool
     */
    public function isAccessTokenValid(): bool
    {
        return !empty($this->accessToken);
    }

    /**
     * Get the Bing Webmaster connector instance
     *
     * @return BingWebmasterConnector
     */
    public function getConnector(): BingWebmasterConnector
    {
        return $this->connector;
    }

    /**
     * Get user sites
     *
     * @return GetUserSites
     */
    public function getUserSites(): GetUserSites
    {
        return new GetUserSites();
    }

    /**
     * Get rank and traffic statistics
     *
     * @param string $siteUrl The site URL
     * @param array $options Query options
     * @return GetRankAndTrafficStats
     */
    public function getRankAndTrafficStats(string $siteUrl, array $options = []): GetRankAndTrafficStats
    {
        return new GetRankAndTrafficStats($siteUrl, $options);
    }

    /**
     * Get keyword statistics
     *
     * @param string $siteUrl The site URL
     * @param array $options Query options
     * @return GetKeywordStats
     */
    public function getKeywordStats(string $siteUrl, array $options = []): GetKeywordStats
    {
        return new GetKeywordStats($siteUrl, $options);
    }

    /**
     * Get page statistics
     *
     * @param string $siteUrl The site URL
     * @param array $options Query options
     * @return GetPageStats
     */
    public function getPageStats(string $siteUrl, array $options = []): GetPageStats
    {
        return new GetPageStats($siteUrl, $options);
    }

    /**
     * Get query statistics
     *
     * @param string $siteUrl The site URL
     * @param array $options Query options
     * @return GetQueryStats
     */
    public function getQueryStats(string $siteUrl, array $options = []): GetQueryStats
    {
        return new GetQueryStats($siteUrl, $options);
    }

    /**
     * Submit a URL for indexing
     *
     * @param string $siteUrl The site URL
     * @param string $url The URL to submit
     * @return SubmitUrl
     */
    public function submitUrl(string $siteUrl, string $url): SubmitUrl
    {
        return new SubmitUrl($siteUrl, $url);
    }

    /**
     * Submit a sitemap
     *
     * @param string $siteUrl The site URL
     * @param string $sitemapUrl The sitemap URL
     * @return SubmitSitemap
     */
    public function submitSitemap(string $siteUrl, string $sitemapUrl): SubmitSitemap
    {
        return new SubmitSitemap($siteUrl, $sitemapUrl);
    }

    /**
     * Execute a request using the connector
     *
     * @param \Saloon\Http\Request $request
     * @return \Saloon\Http\Response
     */
    public function execute($request)
    {
        return $this->connector->send($request);
    }
}

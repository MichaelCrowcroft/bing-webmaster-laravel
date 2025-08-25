<?php

namespace MichaelCrowcroft\BingWebmaster\Tests;

use MichaelCrowcroft\BingWebmaster\BingWebmaster;
use MichaelCrowcroft\BingWebmaster\Facades\BingWebmaster as BingWebmasterFacade;
use MichaelCrowcroft\BingWebmaster\BingWebmasterServiceProvider;
use MichaelCrowcroft\BingWebmaster\Requests\GetUserSites;
use MichaelCrowcroft\BingWebmaster\Requests\GetRankAndTrafficStats;
use MichaelCrowcroft\BingWebmaster\Requests\GetKeywordStats;
use MichaelCrowcroft\BingWebmaster\Requests\GetPageStats;
use MichaelCrowcroft\BingWebmaster\Requests\GetQueryStats;
use MichaelCrowcroft\BingWebmaster\Requests\SubmitUrl;
use MichaelCrowcroft\BingWebmaster\Requests\SubmitSitemap;
use Orchestra\Testbench\TestCase;

class BingWebmasterTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            BingWebmasterServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'BingWebmaster' => BingWebmasterFacade::class,
        ];
    }

    public function test_bing_webmaster_can_be_instantiated()
    {
        $bing = new BingWebmaster();

        $this->assertInstanceOf(BingWebmaster::class, $bing);
        $this->assertNull($bing->getAccessToken()); // No token set by default
    }

    public function test_bing_webmaster_can_be_instantiated_with_token()
    {
        $accessToken = 'test-access-token';
        $bing = new BingWebmaster($accessToken);

        $this->assertInstanceOf(BingWebmaster::class, $bing);
        $this->assertEquals($accessToken, $bing->getAccessToken());
    }

    public function test_bing_webmaster_can_set_token_after_instantiation()
    {
        $bing = new BingWebmaster();
        $accessToken = 'test-access-token';

        $result = $bing->setAccessToken($accessToken);

        $this->assertSame($bing, $result); // Should return self for method chaining
        $this->assertEquals($accessToken, $bing->getAccessToken());
    }

    public function test_facade_returns_correct_instance()
    {
        $bing = BingWebmasterFacade::getFacadeRoot();

        $this->assertInstanceOf(BingWebmaster::class, $bing);
    }

    public function test_service_can_be_resolved_from_container()
    {
        $bing = $this->app->make(BingWebmaster::class);

        $this->assertInstanceOf(BingWebmaster::class, $bing);
    }

    public function test_request_classes_can_be_instantiated()
    {
        $bing = new BingWebmaster();
        $siteUrl = 'https://example.com';

        $this->assertInstanceOf(
            GetUserSites::class,
            $bing->getUserSites()
        );

        $this->assertInstanceOf(
            GetRankAndTrafficStats::class,
            $bing->getRankAndTrafficStats($siteUrl)
        );

        $this->assertInstanceOf(
            GetKeywordStats::class,
            $bing->getKeywordStats($siteUrl)
        );

        $this->assertInstanceOf(
            GetPageStats::class,
            $bing->getPageStats($siteUrl)
        );

        $this->assertInstanceOf(
            GetQueryStats::class,
            $bing->getQueryStats($siteUrl)
        );

        $this->assertInstanceOf(
            SubmitUrl::class,
            $bing->submitUrl($siteUrl, 'https://example.com/page')
        );

        $this->assertInstanceOf(
            SubmitSitemap::class,
            $bing->submitSitemap($siteUrl, 'https://example.com/sitemap.xml')
        );
    }

    public function test_facade_request_classes_can_be_accessed()
    {
        $siteUrl = 'https://example.com';

        $this->assertInstanceOf(
            GetUserSites::class,
            BingWebmasterFacade::getUserSites()
        );

        $this->assertInstanceOf(
            GetRankAndTrafficStats::class,
            BingWebmasterFacade::getRankAndTrafficStats($siteUrl)
        );

        $this->assertInstanceOf(
            GetKeywordStats::class,
            BingWebmasterFacade::getKeywordStats($siteUrl)
        );

        $this->assertInstanceOf(
            GetPageStats::class,
            BingWebmasterFacade::getPageStats($siteUrl)
        );

        $this->assertInstanceOf(
            GetQueryStats::class,
            BingWebmasterFacade::getQueryStats($siteUrl)
        );

        $this->assertInstanceOf(
            SubmitUrl::class,
            BingWebmasterFacade::submitUrl($siteUrl, 'https://example.com/page')
        );

        $this->assertInstanceOf(
            SubmitSitemap::class,
            BingWebmasterFacade::submitSitemap($siteUrl, 'https://example.com/sitemap.xml')
        );
    }

    public function test_submit_url_request_has_correct_properties()
    {
        $siteUrl = 'https://example.com';
        $urlToSubmit = 'https://example.com/page';
        $request = new SubmitUrl($siteUrl, $urlToSubmit);

        $this->assertEquals($siteUrl, $request->getSiteUrl());
        $this->assertEquals($urlToSubmit, $request->getUrlToSubmit());
    }

    public function test_submit_sitemap_request_has_correct_properties()
    {
        $siteUrl = 'https://example.com';
        $sitemapUrl = 'https://example.com/sitemap.xml';
        $request = new SubmitSitemap($siteUrl, $sitemapUrl);

        $this->assertEquals($siteUrl, $request->getSiteUrl());
        $this->assertEquals($sitemapUrl, $request->getSitemapUrl());
    }

    public function test_request_classes_have_correct_endpoints()
    {
        $siteUrl = 'https://example.com';

        $this->assertEquals('/GetUserSites', (new GetUserSites())->resolveEndpoint());
        $this->assertEquals('/GetRankAndTrafficStats', (new GetRankAndTrafficStats($siteUrl))->resolveEndpoint());
        $this->assertEquals('/GetKeywordStats', (new GetKeywordStats($siteUrl))->resolveEndpoint());
        $this->assertEquals('/GetPageStats', (new GetPageStats($siteUrl))->resolveEndpoint());
        $this->assertEquals('/GetQueryStats', (new GetQueryStats($siteUrl))->resolveEndpoint());
        $this->assertEquals('/SubmitUrl', (new SubmitUrl($siteUrl, 'https://example.com/page'))->resolveEndpoint());
        $this->assertEquals('/SubmitSitemap', (new SubmitSitemap($siteUrl, 'https://example.com/sitemap.xml'))->resolveEndpoint());
    }

    public function test_is_access_token_valid_returns_false_when_no_token()
    {
        $bing = new BingWebmaster();

        $this->assertFalse($bing->isAccessTokenValid());
    }

    public function test_is_access_token_valid_returns_true_when_token_set()
    {
        $bing = new BingWebmaster();
        $bing->setAccessToken('test-token');

        $this->assertTrue($bing->isAccessTokenValid());
    }

    public function test_facade_can_set_and_retrieve_token()
    {
        $accessToken = 'test-facade-token';

        // Set token via facade
        BingWebmasterFacade::setAccessToken($accessToken);

        // Get the underlying instance and check token
        $bing = BingWebmasterFacade::getFacadeRoot();
        $this->assertEquals($accessToken, $bing->getAccessToken());
    }
}

<?php

namespace Spiriit\ComposerWriteChangelogs\tests\Command;

use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\Util\WebhookCaller;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class WebhookCallerTest extends TestCase
{
    public static function webhookUrls(): \Generator
    {
        yield 'ok' => ['https://automate.spiriit.org/webhook-test/6aa90362-14ce-4bea-876e-59f3207abf1a'];
        yield 'ko' => ['https://automate.spiriit.org/webhook-test/ko'];
    }

    /**
     * @test
     *
     * @dataProvider webhookUrls
     */
    public function webhook_caller_test(string $webhookUrl): void
    {
        $stringData = '[{"operation":"update","package":"doctrine/annotations","action":"downgraded","phrasing":"downgraded from","versionFrom":"2.0.1","versionTo":"1.14.3","semver":"major","changesUrl":"https://github.com/doctrine/annotations/compare/2.0.1...1.14.3","releaseUrl":"https://github.com/doctrine/annotations/releases/tag/1.14.3"},{"operation":"update","package":"doctrine/annotations","action":"downgraded","phrasing":"downgraded from","versionFrom":"2.0.1","versionTo":"1.14.3","semver":"major","changesUrl":"https://github.com/doctrine/annotations/compare/2.0.1...1.14.3","releaseUrl":"https://github.com/doctrine/annotations/releases/tag/1.14.3"},{"operation":"update","package":"doctrine/annotations","action":"downgraded","phrasing":"downgraded from","versionFrom":"2.0.1","versionTo":"1.14.3","semver":"major","changesUrl":"https://github.com/doctrine/annotations/compare/2.0.1...1.14.3","releaseUrl":"https://github.com/doctrine/annotations/releases/tag/1.14.3"},{"operation":"update","package":"doctrine/annotations","action":"downgraded","phrasing":"downgraded from","versionFrom":"2.0.1","versionTo":"1.14.3","semver":"major","changesUrl":"https://github.com/doctrine/annotations/compare/2.0.1...1.14.3","releaseUrl":"https://github.com/doctrine/annotations/releases/tag/1.14.3"},{"operation":"update","package":"doctrine/annotations","action":"downgraded","phrasing":"downgraded from","versionFrom":"2.0.1","versionTo":"1.14.3","semver":"major","changesUrl":"https://github.com/doctrine/annotations/compare/2.0.1...1.14.3","releaseUrl":"https://github.com/doctrine/annotations/releases/tag/1.14.3"},{"operation":"update","package":"doctrine/annotations","action":"downgraded","phrasing":"downgraded from","versionFrom":"2.0.1","versionTo":"1.14.3","semver":"major","changesUrl":"https://github.com/doctrine/annotations/compare/2.0.1...1.14.3","releaseUrl":"https://github.com/doctrine/annotations/releases/tag/1.14.3"},{"operation":"update","package":"doctrine/annotations","action":"downgraded","phrasing":"downgraded from","versionFrom":"2.0.1","versionTo":"1.14.3","semver":"major","changesUrl":"https://github.com/doctrine/annotations/compare/2.0.1...1.14.3","releaseUrl":"https://github.com/doctrine/annotations/releases/tag/1.14.3"},{"operation":"update","package":"doctrine/annotations","action":"downgraded","phrasing":"downgraded from","versionFrom":"2.0.1","versionTo":"1.14.3","semver":"major","changesUrl":"https://github.com/doctrine/annotations/compare/2.0.1...1.14.3","releaseUrl":"https://github.com/doctrine/annotations/releases/tag/1.14.3"},{"operation":"update","package":"doctrine/annotations","action":"downgraded","phrasing":"downgraded from","versionFrom":"2.0.1","versionTo":"1.14.3","semver":"major","changesUrl":"https://github.com/doctrine/annotations/compare/2.0.1...1.14.3","releaseUrl":"https://github.com/doctrine/annotations/releases/tag/1.14.3"}]';

        $okResponse = new MockResponse('ok', ['http_code' => 200]);
        $koResponse = new MockResponse('ko', ['http_code' => 400]);

        $client = new MockHttpClient(str_ends_with($webhookUrl, 'ko')
            ? $koResponse
            : $okResponse
        );

        $caller = new WebhookCaller($stringData, $webhookUrl, $client);

        if(str_ends_with($webhookUrl, 'ko')){
            self::expectException(ClientException::class);
            $caller->callWebhook();
        }else
        {
            self::assertEquals('ok', $caller->callWebhook());
        }
    }
}
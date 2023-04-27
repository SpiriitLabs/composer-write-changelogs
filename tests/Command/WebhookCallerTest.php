<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\tests\Command;

use PHPUnit\Framework\TestCase;
use Spiriit\ComposerWriteChangelogs\Util\WebhookCaller;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class WebhookCallerTest extends TestCase
{
    public static function webhookUrls(): \Generator
    {
        yield 'ok' => [new MockResponse('ok', ['http_code' => 200])];

        yield 'ko' => [new MockResponse('ko', ['http_code' => 400])];
    }

    /**
     * @dataProvider webhookUrls
     *
     * @test
     */
    public function webhook_caller_test(ResponseInterface $response): void
    {
        $client = new MockHttpClient($response);

        $webhookCaller = new WebhookCaller('', '', $client);

        if (400 === $response->getStatusCode()) {
            self::expectException(ClientException::class);
            $webhookCaller->callWebhook();

            return;
        }

        $this->assertSame('ok', $webhookCaller->callWebhook());
    }
}

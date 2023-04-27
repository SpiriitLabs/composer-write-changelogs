<?php

/*
 * This file is part of the composer-write-changelogs project.
 *
 * (c) Dev Spiriit <dev@spiriit.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Spiriit\ComposerWriteChangelogs\Util;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebhookCaller
{
    private string $stringData;
    private HttpClientInterface $client;

    public function __construct(string $StringData, string $webhookURL, ?HttpClientInterface $client = null)
    {
        $this->stringData = $StringData;
        $this->client = $client ?: HttpClient::createForBaseUri($webhookURL);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function callWebhook(): string
    {
        $response = $this->client->request('POST', '', [
            'body' => $this->stringData,
            'headers' => [
                'Content-Type' => 'text/plain',
            ],
        ]);

        return $response->getContent();
    }
}

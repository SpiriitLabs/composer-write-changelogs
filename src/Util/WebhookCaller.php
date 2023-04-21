<?php

namespace Spiriit\ComposerWriteChangelogs\Util;

use JsonSchema\Uri\Retrievers\Curl;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebhookCaller
{
    private string $stringData;
    private string $webhookURL;
    private HttpClientInterface $client;

    /**
     * @param string $StringData
     * @param string $webhookURL
     */
    public function __construct(string $StringData, string $webhookURL, ?HttpClientInterface $client = null)
    {
        $this->stringData = $StringData;
        $this->webhookURL = $webhookURL;
        $this->client = $client ?: HttpClient::createForBaseUri($this->webhookURL);
    }

    public function callWebhook(): string
    {
        $response = $this->client->request('POST', "", [
            'body' => $this->stringData,
        ]);

        return $response->getContent();
    }
}
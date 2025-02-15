<?php

namespace Cognesy\Instructor\Clients\Anthropic;

use Cognesy\Instructor\ApiClient\ApiConnector;
use Override;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\HeaderAuthenticator;

class AnthropicConnector extends ApiConnector
{
    protected string $baseUrl = 'https://api.anthropic.com/v1';

    #[Override]
    protected function defaultAuth() : Authenticator {
        return new HeaderAuthenticator($this->apiKey, 'x-api-key');
    }

    #[Override]
    protected function defaultHeaders(): array {
        return [
            'content-type' => 'application/json',
            'accept' => 'application/json',
            'anthropic-version' => '2023-06-01',
        ];
    }
}
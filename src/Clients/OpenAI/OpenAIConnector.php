<?php
namespace Cognesy\Instructor\Clients\OpenAI;

use Cognesy\Instructor\ApiClient\ApiConnector;
use Override;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;

class OpenAIConnector extends ApiConnector
{
    protected string $baseUrl = 'https://api.openai.com/v1';
    protected string $organization;

    public function __construct(
        string $apiKey,
        string $baseUrl = '',
        string $organization = '',
        int    $connectTimeout = 3,
        int    $requestTimeout = 30,
        array  $metadata = [],
        string $senderClass = '',
    ) {
        parent::__construct($apiKey, $baseUrl, $connectTimeout, $requestTimeout, $metadata, $senderClass);
        $this->organization = $organization;
    }

    #[Override]
    protected function defaultAuth() : Authenticator {
        return new TokenAuthenticator($this->apiKey);
    }

    #[Override]
    protected function defaultHeaders(): array {
        $headers = [
            'content-type' => 'application/json',
            'accept' => 'application/json',
            'OpenAI-Organization' => $this->organization,
        ];
        return $headers;
    }
}

<?php
namespace Cognesy\Instructor\Clients\Azure;

use Cognesy\Instructor\ApiClient\ApiClient;
use Cognesy\Instructor\ApiClient\ApiConnector;
use Cognesy\Instructor\Clients\OpenAI\Traits\HandlesStreamData;
use Cognesy\Instructor\Enums\Mode;
use Cognesy\Instructor\Events\EventDispatcher;
use Override;

class AzureClient extends ApiClient
{
    use HandlesStreamData;

    public string $defaultModel = 'azure:gpt-3.5-turbo'; //'gpt-4-turbo-preview';
    public int $defaultMaxTokens = 256;

    public function __construct(
        protected string $apiKey = '',
        protected string $resourceName = '',
        protected string $deploymentId = '',
        protected string $apiVersion = '',
        protected string $baseUri = '',
        protected int $connectTimeout = 3,
        protected int $requestTimeout = 30,
        protected array $metadata = [],
        EventDispatcher $events = null,
        ApiConnector $connector = null,
    ) {
        parent::__construct($events);
        $this->withConnector($connector ?? new AzureConnector(
            apiKey: $apiKey,
            resourceName: $resourceName,
            deploymentId: $deploymentId,
            baseUrl: $baseUri,
            connectTimeout: $connectTimeout,
            requestTimeout: $requestTimeout,
            metadata: $metadata,
            senderClass: '',
        ));
        $this->queryParams = ['api-version' => $apiVersion];
    }

    #[Override]
    public function getModeRequestClass(Mode $mode) : string {
        return AzureApiRequest::class;
    }
}

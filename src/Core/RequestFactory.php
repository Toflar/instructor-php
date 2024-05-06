<?php

namespace Cognesy\Instructor\Core;

use Cognesy\Instructor\ApiClient\Factories\ApiClientFactory;
use Cognesy\Instructor\ApiClient\ModelFactory;
use Cognesy\Instructor\Data\Request;
use Cognesy\Instructor\Enums\Mode;

class RequestFactory
{
    public function __construct(
        protected ApiClientFactory $clientFactory,
        protected ResponseModelFactory $responseModelFactory,
        protected ModelFactory $modelFactory,
    ) {}

    public function create(
        string|array $messages,
        string|object|array $responseModel,
        string $model = '',
        int $maxRetries = 0,
        array $options = [],
        string $functionName = '',
        string $functionDescription = '',
        string $prompt = '',
        string $retryPrompt = '',
        Mode $mode = Mode::Tools,
    ) : Request {
        $request = new Request(
            $messages,
            $responseModel,
            $model,
            $maxRetries,
            $options,
            $functionName,
            $functionDescription,
            $prompt,
            $retryPrompt,
            $mode,
            $this->clientFactory->getDefault(),
            $this->modelFactory
        );
        return $request;
    }

    public function fromRequest(Request $request) : Request {
        // make sure the request has a client
        if ($request->client() === null) {
            $request->withClient(
                $this->clientFactory->getDefault()
            );
        }
        // make sure the request has a response model
        if ($request->responseModel() === null) {
            $request->withResponseModel(
                $this->responseModelFactory->fromAny(
                    $request->requestedSchema(),
                    $request->functionName(),
                    $request->functionDescription()
                )
            );
        }
        return $request;
    }
}

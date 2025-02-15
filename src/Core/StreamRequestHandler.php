<?php

namespace Cognesy\Instructor\Core;

use Cognesy\Instructor\ApiClient\ApiClient;
use Cognesy\Instructor\Contracts\CanGenerateResponse;
use Cognesy\Instructor\Contracts\CanHandleStreamRequest;
use Cognesy\Instructor\Core\StreamResponse\PartialsGenerator;
use Cognesy\Instructor\Data\Request;
use Cognesy\Instructor\Events\EventDispatcher;
use Cognesy\Instructor\Events\Request\NewValidationRecoveryAttempt;
use Cognesy\Instructor\Events\Request\RequestSentToLLM;
use Cognesy\Instructor\Events\Request\RequestToLLMFailed;
use Cognesy\Instructor\Events\Request\ResponseReceivedFromLLM;
use Cognesy\Instructor\Events\Request\ValidationRecoveryLimitReached;
use Exception;
use Generator;

class StreamRequestHandler implements CanHandleStreamRequest
{
    private int $retries = 0;
    private array $messages = [];

    public function __construct(
        private EventDispatcher $events,
        private CanGenerateResponse $responseGenerator,
        private PartialsGenerator $partialsGenerator,
    ) {}

    /**
     * Returns response object or generator wrapped in Result monad
     */
    public function respondTo(Request $request) : Generator {
        $responseModel = $request->responseModel();
        if ($responseModel === null) {
            throw new Exception("Request does not have a response model");
        }
        // try to respond to the request until success or max retries reached
        $this->retries = 0;
        $this->messages = $request->messages();
        while ($this->retries <= $request->maxRetries()) {
            // (0) process stream and return partial results...
            yield from $this->getStreamedResponses($request);

            // (1) ...then get API client response
            $apiResponse = $this->partialsGenerator->getCompleteResponse();
            $this->events->dispatch(new ResponseReceivedFromLLM($apiResponse));

            // (2) we have ApiResponse here - let's process it: deserialize, validate, transform
            $processingResult = $this->responseGenerator->makeResponse($apiResponse, $responseModel);
            if ($processingResult->isSuccess()) {
                // get final value
                $value = $processingResult->unwrap();
                // store response
                $request->addResponse($this->messages, $apiResponse, $this->partialsGenerator->partialResponses(), $value);
                // return final result
                yield $value;
                // we're done here - no need to retry
                return;
            }

            // (3) retry - we have not managed to deserialize, validate or transform the response
            $errors = $processingResult->error();
            // store failed response
            $request->addFailedResponse($this->messages, $apiResponse, $this->partialsGenerator->partialResponses(), [$errors]);
            $this->messages = $request->makeRetryMessages($this->messages, $apiResponse->content, [$errors]);
            $request->withMessages($this->messages);
            $this->retries++;
            if ($this->retries <= $request->maxRetries()) {
                $this->events->dispatch(new NewValidationRecoveryAttempt($this->retries, $errors));
            }
            // (3.1) reset partials generator
            $this->partialsGenerator->resetPartialResponse();
        }
        $this->events->dispatch(new ValidationRecoveryLimitReached($this->retries, [$errors]));
        throw new Exception("Validation recovery attempts limit reached after {$this->retries} attempts due to: ".implode(", ", $errors));
    }

    protected function getStreamedResponses(Request $request) : Generator {
        /** @var ApiClient $apiClient */
        $apiClient = $request->client();
        if ($apiClient === null) {
            throw new Exception("Request does not have an API client");
        }
        $apiRequest = $request->toApiRequest();
        try {
            $this->events->dispatch(new RequestSentToLLM($apiRequest));
            $stream = $apiClient->withApiRequest($apiRequest)->stream();
            yield from $this->partialsGenerator->getPartialResponses($stream, $request->responseModel());
        } catch(Exception $e) {
            $this->events->dispatch(new RequestToLLMFailed($apiClient->getApiRequest(), $e->getMessage()));
            throw $e;
        }
    }
}
<?php

namespace Cognesy\Instructor\ApiClient\Traits;

use Cognesy\Instructor\ApiClient\Requests\ApiRequest;
use Exception;

trait HandlesApiRequest
{
    use HandlesQueryParams;

    protected ApiRequest $request;

    public function withApiRequest(ApiRequest $request) : static {
        $this->request = $request;
        return $this;
    }

    public function getApiRequest() : ApiRequest {
        if (empty($this->request)) {
            throw new Exception('Request is not set');
        }
        if (!empty($this->queryParams)) {
            $this->request->query()->set($this->queryParams);
        }
        return $this->request;
    }

    protected function isStreamedRequest() : bool {
        return $this->request->isStreamed();
    }

    protected function withStreaming(bool $streaming) : void {
        $this->request->config()->add('stream', $streaming);
    }
}
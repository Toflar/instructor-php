<?php

namespace Tests;

use Cognesy\Instructor\LLMs\FunctionCall;
use Cognesy\Instructor\LLMs\LLMResponse;
use Cognesy\Instructor\LLMs\OpenAI\OpenAIFunctionCaller;
use Mockery;

class MockLLM
{
    static public function get(array $args) : OpenAIFunctionCaller {
        $mockLLM = Mockery::mock(OpenAIFunctionCaller::class);
        $list = [];
        foreach ($args as $arg) {
            $list[] = self::makeFunc($arg);
        }
        $mockLLM->shouldReceive('callFunction')->andReturnUsing(...$list);
        return $mockLLM;
    }

    static private function makeFunc(string $json) {
        return fn() => new LLMResponse(
            toolCalls: [
                new FunctionCall(
                    toolCallId: '1',
                    functionName: 'callFunction',
                    functionArguments: $json,
                ),
            ],
            finishReason: 'success',
            rawData: null,
        );
    }
}
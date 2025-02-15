<?php
namespace Cognesy\Instructor\Extras\Mixin;

use Cognesy\Instructor\Enums\Mode;
use Cognesy\Instructor\Instructor;

trait HandlesExtraction {
    public function extract(
        string|array $messages,
        string $model = '',
        int $maxRetries = 2,
        array $options = [],
        array $examples = [],
        string $prompt = '',
        string $retryPrompt = '',
        Mode $mode = Mode::Tools,
    ) : mixed {
        return $this->getInstructor()->respond(
            messages: $messages,
            responseModel: $this->getResponseModel(),
            model: $model,
            maxRetries: $maxRetries,
            options: $options,
            examples: $examples,
            toolName: $this->getToolName(),
            toolDescription: $this->getToolDescription(),
            prompt: $prompt,
            retryPrompt: $retryPrompt,
            mode: $mode,
        );
    }

    abstract protected function getInstructor() : Instructor;
    abstract protected function getResponseModel() : string|array|object;
    abstract protected function getToolName() : string;
    abstract protected function getToolDescription() : string;
}

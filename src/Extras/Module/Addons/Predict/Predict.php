<?php

namespace Cognesy\Instructor\Extras\Module\Addons\Predict;

use BackedEnum;
use Cognesy\Instructor\Data\Example;
use Cognesy\Instructor\Extras\Module\Core\DynamicModule;
use Cognesy\Instructor\Extras\Module\Signature\Contracts\HasSignature;
use Cognesy\Instructor\Extras\Module\Signature\Signature;
use Cognesy\Instructor\Extras\Module\Call\Contracts\CanBeProcessed;
use Cognesy\Instructor\Extras\Module\Call\Enums\CallStatus;
use Cognesy\Instructor\Extras\Module\Utils\InputOutputMapper;
use Cognesy\Instructor\Instructor;
use Cognesy\Instructor\Utils\Template;
use Exception;

class Predict extends DynamicModule
{
    private Instructor $instructor;
    protected string $prompt;
    protected string $defaultPrompt = 'Your job is to infer output argument values in input data based on specification: {signature} {description}';
    protected int $maxRetries = 3;

    protected string|Signature|HasSignature $defaultSignature;

    protected ?object $signatureCarrier;

    public function __construct(
        string|Signature|HasSignature $signature,
        Instructor $instructor,
    ) {
        if ($signature instanceof HasSignature) {
            $this->signatureCarrier = $signature;
        }
        $this->defaultSignature = match(true) {
            $signature instanceof HasSignature => $signature->signature(),
            default => $signature,
        };
        $this->instructor = $instructor;
    }

    public function signature(): string|Signature {
        return $this->defaultSignature;
    }

    public function process(CanBeProcessed $call) : mixed {
        try {
            $call->changeStatus(CallStatus::InProgress);
            $values = $call->data()->input()->getValues();
            $targetObject = $this->signatureCarrier ?? $call->outputRef();
            $result = $this->forward($values, $targetObject);
            $outputs = InputOutputMapper::toOutputs($result, $this->outputNames());
            $call->setOutputs($outputs);
            $call->changeStatus(CallStatus::Completed);
        } catch (Exception $e) {
            $call->addError($e->getMessage(), ['exception' => $e]);
            $call->changeStatus(CallStatus::Failed);
            throw $e;
        }
        return $result;
    }

    public function forward(array $args, object $targetObject): mixed {
        $input = match(true) {
            count($args) === 0 => throw new \Exception('Empty input'),
            count($args) === 1 => reset($args),
            default => match(true) {
                is_array($args[0]) => $args[0],
                is_string($args[0]) => $args[0],
                default => throw new Exception('Invalid input - should be string or messages array'),
            }
        };

        $response = $this->instructor->respond(
            messages: $this->toMessages($input),
            responseModel: $targetObject,
            model: 'gpt-4o', // TODO: needs to be configurable
            maxRetries: $this->maxRetries,
        );

        return $response;
    }

    public function prompt() : string {
        if (empty($this->prompt)) {
            $this->prompt = $this->renderPrompt($this->defaultPrompt);
        }
        return $this->prompt;
    }

    public function setPrompt(string $prompt) : void {
        $this->prompt = $prompt;
    }

    // INTERNAL ////////////////////////////////////////////////////////////////////////////////////

    private function toMessages(string|array|object $input) : array {
        $content = match(true) {
            is_string($input) => $input,
            $input instanceof Example => $input->input(),
            $input instanceof BackedEnum => $input->value,
            // ...how do we handle chat messages input?
            default => json_encode($input), // wrap in json
        };
        return [
            ['role' => 'user', 'content' => $this->prompt()],
            ['role' => 'assistant', 'content' => 'Provide input data.'],
            ['role' => 'user', 'content' => $content]
        ];
    }

    public function renderPrompt(string $template): string {
        return Template::render($template, [
            'signature' => $this->getSignature()->toSignatureString(),
            'description' => $this->getSignature()->toOutputSchema()->description(),
        ]);
    }
}

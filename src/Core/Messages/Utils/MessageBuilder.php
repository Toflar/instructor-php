<?php
namespace Cognesy\Instructor\Core\Messages\Utils;

use Cognesy\Instructor\Data\Example;
use Cognesy\Instructor\Data\ResponseModel;
use Cognesy\Instructor\Enums\Mode;

class MessageBuilder
{
    use MessageBuilder\HandlesApiProviders;
    use MessageBuilder\MakesMessages;

    private string $clientClass = '';
    private Mode $mode = Mode::Tools;
    private array $messages = [];
    private ?ResponseModel $responseModel = null;
    private ?string $dataAcknowledgedPrompt = null;
    private ?string $prompt = null;
    /** @var ?Example[] $examples */
    private ?array $examples = null;

    public function __construct() {}

    static public function requestBody(
        string $clientClass,
        Mode $mode = Mode::Tools,
        array $messages = [],
        ?ResponseModel $responseModel = null,
        ?string $dataAcknowledgedPrompt = null,
        ?string $prompt = null,
        ?array $examples = null,
    ) : array {
        $instance = new self();
        $instance->clientClass = $clientClass;
        $instance->mode = $mode;
        $instance->messages = $messages;
        $instance->responseModel = $responseModel;
        $instance->dataAcknowledgedPrompt = $dataAcknowledgedPrompt;
        $instance->prompt = $prompt;
        $instance->examples = $examples;
        return $instance->makeExtractionRequest();
    }

    // INTERNAL TOOLS ///////////////////////////////////////////////////////////////////

    private function makeExtractionRequest() : array {
        // get body creation method based on client
        $builder = $this->getBuilder($this->clientClass);
        // get the parts of body specific to the client
        $script = $this->makeMessages();
        $script->setContext(['json_schema' => $this->responseModel->toJsonSchema()]);
        $body = $builder($script);

        // filter out empty values
        $body = array_filter($body);
        return $body;
    }
}

<?php

namespace Cognesy\Instructor\Utils;

use Cognesy\Instructor\Core\Messages\Message;
use Cognesy\Instructor\Core\Messages\Messages;
use InvalidArgumentException;

class Template
{
    private array $values = [];
    private array $keys = [];

    public function __construct(
        array $context = []
    ) {
        if (empty($context)) {
            return;
        }
        $materializedContext = $this->materializeContext($context);
        $this->values = array_values($materializedContext);
        $this->keys = array_map(
            fn($key) => $this->varPattern($key),
            array_keys($materializedContext)
        );
    }

    public static function render(string $template, array $context) : string {
        return (new Template($context))->renderString($template);
    }

    public function renderString(string $template): string {
        // find all keys in the template
        $keys = $this->findVars($template);
        // find keys missing from $this->keys
        $missingKeys = array_diff($keys, $this->keys);
        // remove missing key strings from the template
        $template = str_replace($missingKeys, '', $template);
        return str_replace($this->keys, $this->values, $template);
    }

    public function renderArray(array $rows, string $field = 'content'): array {
        return array_map(
            fn($item) => $this->renderString($item[$field] ?? ''),
            $rows
        );
    }

    public function renderMessage(array|Message $message) : array {
        $rendered = match(true) {
            is_array($message) => ['role' => $message['role'], 'content' => $this->renderString($message['content'])],
            $message instanceof Message => ['role' => $message->role, 'content' => $this->renderString($message->content)],
            default => throw new InvalidArgumentException('Invalid message type'),
        };
        return $rendered;
    }

    public function renderMessages(array|Messages $messages) : array {
        return array_map(
            fn($message) => $this->renderMessage($message),
            is_array($messages) ? $messages : $messages->toArray()
        );
    }

    // OVERRIDEABLE //////////////////////////////////////////////////////////////

    protected function varPattern(string $key) : string {
        return '<|' . $key . '|>';
    }

    protected function findVars(string $template) : array {
        $matches = [];
        // replace {xxx} pattern with <|xxx|> pattern match
        preg_match_all('/<\|([^|]+)\|>/', $template, $matches);
        return $matches[0];
    }

    // INTERNAL //////////////////////////////////////////////////////////////////

    private function materializeContext(array $context) : array {
        $contextValues = [];
        foreach ($context as $key => $value) {
            $value = match (true) {
                is_scalar($value) => $value,
                is_array($value) => Json::encode($value),
                is_callable($value) => $value($key, $context),
                is_object($value) && method_exists($value, 'toString') => $value->toString(),
                is_object($value) && method_exists($value, 'toJson') => $value->toJson(),
                is_object($value) && method_exists($value, 'toArray') => Json::encode($value->toArray()),
                is_object($value) && method_exists($value, 'toSchema') => Json::encode($value->toSchema()),
                is_object($value) && method_exists($value, 'toOutputSchema') => Json::encode($value->toOutputSchema()),
                is_object($value) && property_exists($value, 'value') => $value->value(),
                is_object($value) => Json::encode($value),
                default => $value,
            };
            $contextValues[$key] = $value;
        }
        return $contextValues;
    }
}

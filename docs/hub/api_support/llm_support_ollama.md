# Support for local Ollama

You can use Instructor with local Ollama instance. Please note that, at least currently,
OS models do not perform on par with OpenAI (GPT-3.5 or GPT-4) model.

```php
<?php
$loader = require 'vendor/autoload.php';
$loader->add('Cognesy\\Instructor\\', __DIR__ . '../../src/');

use Cognesy\Instructor\Clients\Ollama\OllamaClient;
use Cognesy\Instructor\Enums\Mode;
use Cognesy\Instructor\Instructor;

enum UserType : string {
    case Guest = 'guest';
    case User = 'user';
    case Admin = 'admin';
}

class User {
    public int $age;
    public string $name;
    public string $username;
    public UserType $role;
    /** @var string[] */
    public array $hobbies;
}

// Create instance of Ollama client with default settings
$client = new OllamaClient();

/// Get Instructor with the default client component overridden with your own
$instructor = (new Instructor)->withClient($client)
    ->onEvent(\Cognesy\Instructor\Events\Request\RequestSentToLLM::class, function($event) {
        print("Request sent to LLM:\n\n");
        dump($event->request);
    })
    ->onEvent(\Cognesy\Instructor\Events\Request\ResponseReceivedFromLLM::class, function($event) {
    print("Received response from LLM:\n\n");
    dump($event->response);
});

$user = $instructor->respond(
    messages: "Jason (@jxnlco) is 25 years old and is the admin of this project. He likes playing football and reading books.",
    responseModel: User::class,
    model: 'llama2:latest',
    mode: Mode::Json,
);

print("Completed response model:\n\n");
dump($user);

assert(isset($user->name));
assert(isset($user->age));
?>
```

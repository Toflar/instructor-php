# Streaming

Instructor can process LLM's streamed responses to provide partial response model
updates that you can use to update the model with new data as the response is being
generated.

```php
<?php
$loader = require 'vendor/autoload.php';
$loader->add('Cognesy\\Instructor\\', __DIR__ . '../../src/');

use Cognesy\Instructor\Instructor;

class UserRole
{
    /** Monotonically increasing identifier */
    public int $id;
    public string $title = '';
}

class UserDetail
{
    public int $age;
    public string $name;
    public string $location;
    /** @var UserRole[] */
    public array $roles;
    /** @var string[] */
    public array $hobbies;
}

// This function will be called every time a new token is received
function partialUpdate($partial) {
    // Clear the screen and move the cursor to the top
    echo chr(27).chr(91).'H'.chr(27).chr(91).'J';

    // Print explanation
    echo "Waiting 250ms on every update received to make changes easier to observe...\n";

    // Display the partial object
    dump($partial);

    // Wait a bit before clearing the screen to make partial changes slower.
    // Don't use this in your application :)
    usleep(250000);
}
?>
```
Now we can use this data model to extract arbitrary properties from a text message.
As the tokens are streamed from LLM API, the `partialUpdate` function will be called
with partially updated object of type `UserDetail` that you can use, usually to update
the UI.

```php
<?php
$text = <<<TEXT
    Jason is 25 years old, he is an engineer and tech lead. He lives in
    San Francisco. He likes to play soccer and climb mountains.
    TEXT;

$stream = (new Instructor)->request(
    messages: $text,
    responseModel: UserDetail::class,
    options: ['stream' => true]
)->stream();

foreach ($stream->partials() as $partial) {
    partialUpdate($partial);
}

$user = $stream->getLastUpdate();

assert($user->name === 'Jason');
assert($user->age === 25);
?>
```

# Using attributes

Instructor supports `Description` and `Instructions` attributes to provide more
context to the language model or to provide additional instructions to the model.

```php
<?php
$loader = require 'vendor/autoload.php';
$loader->add('Cognesy\\Instructor\\', __DIR__ . '../../src/');

use Cognesy\Instructor\Instructor;
use Cognesy\Instructor\Schema\Attributes\Description;
use Cognesy\Instructor\Schema\Attributes\Instructions;

// Step 1: Define a class that represents the structure and semantics
// of the data you want to extract
#[Description("Information about user")]
class User {
    #[Description("User's age")]
    public int $age;
    #[Instructions("Make it ALL CAPS")]
    public string $name;
    #[Description("User's job")]
    #[Instructions("Ignore hobbies, identify profession")]
    public string $job;
}

// Step 2: Get the text (or chat messages) you want to extract data from
$text = "Jason is 25 years old, 10K runner, speaker and an engineer.";
print("Input text:\n");
print($text . "\n\n");

// Step 3: Extract structured data using default language model API (OpenAI)
print("Extracting structured data using LLM...\n\n");
$user = (new Instructor)->respond(
    messages: $text,
    responseModel: User::class,
);

// Step 4: Now you can use the extracted data in your application
print("Extracted data:\n");
dump($user);

assert(isset($user->name));
assert(isset($user->age));
assert(isset($user->job));
?>
```

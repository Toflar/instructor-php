<?php
namespace Cognesy\Instructor\Core\Messages\Traits\Section;

use Cognesy\Instructor\Core\Messages\Enums\MessageRole;

trait HandlesAccess
{
    public function name() : string {
        return $this->name;
    }

    public function firstRole() : MessageRole {
        return $this->messages->firstRole();
    }

    public function lastRole() : MessageRole {
        return $this->messages->lastRole();
    }

    public function isEmpty() : bool {
        return $this->messages->isEmpty();
    }
}
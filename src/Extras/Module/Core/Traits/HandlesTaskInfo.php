<?php

namespace Cognesy\Instructor\Extras\Module\Core\Traits;

use DateTime;
use DateTimeImmutable;

trait HandlesTaskInfo
{
    private string $id;
    private DateTimeImmutable $createdAt;
    private DateTime $updatedAt;

    public function id(): string {
        return $this->id;
    }

    public function name(): string {
        $className = $this->className();
        $signature = $this->getSignature()->toShortSignature();
        return "{$className}({$signature})";
    }

    public function createdAt(): DateTimeImmutable {
        return $this->createdAt;
    }

    public function updatedAt(): DateTime {
        return $this->updatedAt;
    }

    private function className() : string {
        $parts = explode('\\', static::class);
        return $parts[count($parts) - 1];
    }
}

<?php

namespace Cognesy\Instructor\ApiClient\Contracts;

use Cognesy\Instructor\ApiClient\Data\Responses\ApiResponse;
use Generator;

interface CanCallApi
{
    public function respond() : ApiResponse;
    public function stream() : Generator;
    public function streamAll() : array;
}
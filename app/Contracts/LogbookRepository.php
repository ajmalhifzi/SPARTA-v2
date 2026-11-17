<?php
namespace App\Contracts;
interface LogbookRepository
{
    /** Return only projects belonging to the verified actor. */
    public function assigned(array $actor): array;
    public function get(string $id, array $actor): array;
    public function save(string $id, array $actor, array $data): void;
}

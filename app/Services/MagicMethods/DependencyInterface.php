<?php

declare(strict_types=1);

namespace App\Services\MagicMethods;

interface DependencyInterface
{
    public function connect(): void;

    public function disconnect(): void;

    public function getProperty(string $name);

    public function setProperty(string $name, $value): void;

    public function issetProperty(string $name): bool;

    public function unsetProperty(string $name): void;

    public function delegatedMethod(): void;

    public function serialize(): void;

    public function unserialize(): void;
}

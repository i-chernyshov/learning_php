<?php

declare(strict_types=1);

namespace App\Services\MagicMethods;

use RuntimeException;

/**
 * @property mixed $something
 *
 * @method void delegatedMethod()
 * @method static void staticDelegatedMethod()
 */
class MagicMethods
{
    private static ?DependencyInterface $dependencyStatic = null;

    public function __construct(private DependencyInterface $dependency, private array $cacheProperties = [])
    {
        $this->dependency->connect();
        static::$dependencyStatic = $this->dependency;
    }

    public function __destruct()
    {
        $this->dependency->disconnect();
    }

    public function __get(string $name)
    {
        $this->dependency->getProperty($name);

        if (!isset($this->cacheProperties[$name])) {
            $this->cacheProperties[$name] = null;
        }

        return $this->cacheProperties[$name];
    }

    public function __set(string $name, $value): void
    {
        $this->dependency->setProperty($name, $value);

        $this->cacheProperties[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        $this->dependency->issetProperty($name);

        return isset($this->cacheProperties[$name]);
    }

    public function __unset(string $name): void
    {
        $this->dependency->unsetProperty($name);

        unset($this->cacheProperties[$name]);
    }

    public function __call(string $name, array $arguments)
    {
        return $this->dependency->$name(...$arguments);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        if (str_starts_with($name, $staticPrefix = 'static')) {
            $name = lcfirst(str_replace($staticPrefix, '', $name));
            return static::$dependencyStatic->$name(...$arguments);
        }

        throw new RuntimeException('undefined method __callStatic');
    }

    public function __clone(): void
    {
        $this->dependency = clone $this->dependency;
        $this->cacheProperties = [];
    }

    public function __invoke(): bool
    {
        return true;
    }

    public function __toString(): string
    {
        return __CLASS__;
    }

    public function __serialize(): array
    {
        $this->dependency->serialize();

        return [
            'serializeProperty' => true,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->dependency = static::$dependencyStatic;
        $this->cacheProperties = $data;

        $this->dependency->unserialize();
    }

    public function getDependency(): DependencyInterface
    {
        return $this->dependency;
    }

    public function getCacheProperties(): array
    {
        return $this->cacheProperties;
    }
}

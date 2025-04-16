<?php

namespace DoubleBreak\PhpBatcher;

use ArrayObject;
use RuntimeException;

class State
{
    private  ArrayObject $storage;
    public function __construct(
        array $parameters = [],
        private readonly  array $immutableKeys = []
    ) {
        $this->storage = new ArrayObject($parameters, ArrayObject::ARRAY_AS_PROPS);
        $initialParameters = array_merge(
            array_fill_keys($this->immutableKeys, null),
            $parameters
        );
        $this->storage->exchangeArray($initialParameters);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->storage->offsetExists($key) ? $this->storage[$key] : $default;
    }

    public function set(string $key, mixed $value): void
    {
        if (in_array($key, $this->immutableKeys, true)) {
            throw new RuntimeException("Cannot modify immutable key '$key'.");
        }
        $this->storage[$key] = $value;
    }

    public function has(string $key): bool
    {
        return $this->storage->offsetExists($key);
    }

    public function all(): array
    {
        return $this->storage->getArrayCopy();
    }

    public function add(array $parameters): void
    {
        foreach (array_keys($parameters) as $key) {
            if (in_array($key, $this->immutableKeys, true)) {
                throw new RuntimeException("Cannot modify immutable key '$key'.");
            }
        }
        $this->storage->exchangeArray([...$this->all(), ...$parameters]);
    }

    public function remove(string $key): void
    {
        if (in_array($key, $this->immutableKeys, true)) {
            throw new RuntimeException("Cannot modify immutable key '$key'.");
        }
        if ($this->has($key)) {
            unset($this->storage[$key]);
        }
    }

    public function with(array $parameters): self
    {
        $newParameters = [...$this->all(), ...$parameters];
        return new self(
            parameters: $newParameters,
            immutableKeys: $this->immutableKeys
        );
    }
}
<?php

namespace DoubleBreak\PhpBatcher\Tests;

use DoubleBreak\PhpBatcher\State;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class StateTest extends TestCase
{
    public function testGetReturnsDefaultIfKeyDoesNotExist(): void
    {
        $state = new State();
        $this->assertSame('default', $state->get('nonexistent', 'default'));
    }

    public function testGetReturnsValueIfKeyExists(): void
    {
        $state = new State(['key' => 'value']);
        $this->assertSame('value', $state->get('key'));
    }

    public function testSetAddsOrUpdatesValue(): void
    {
        $state = new State();
        $state->set('key', 'value');
        $this->assertSame('value', $state->get('key'));
    }

    public function testSetThrowsExceptionForImmutableKey(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Cannot modify immutable key 'immutableKey'.");

        $state = new State([], ['immutableKey']);
        $state->set('immutableKey', 'value');
    }

    public function testHasReturnsTrueIfKeyExists(): void
    {
        $state = new State(['key' => 'value']);
        $this->assertTrue($state->has('key'));
    }

    public function testHasReturnsFalseIfKeyDoesNotExist(): void
    {
        $state = new State();
        $this->assertFalse($state->has('nonexistent'));
    }

    public function testAllReturnsAllParameters(): void
    {
        $parameters = ['key1' => 'value1', 'key2' => 'value2'];
        $state = new State($parameters);
        $this->assertSame($parameters, $state->all());
    }

    public function testAddAddsParameters(): void
    {
        $state = new State(['key1' => 'value1']);
        $state->add(['key2' => 'value2']);
        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $state->all());
    }

    public function testAddThrowsExceptionForImmutableKey(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Cannot modify immutable key 'immutableKey'.");

        $state = new State([], ['immutableKey']);
        $state->add(['immutableKey' => 'value']);
    }

    public function testRemoveDeletesKey(): void
    {
        $state = new State(['key' => 'value']);
        $state->remove('key');
        $this->assertFalse($state->has('key'));
    }

    public function testRemoveThrowsExceptionForImmutableKey(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Cannot modify immutable key 'immutableKey'.");

        $state = new State(['immutableKey' => 'value'], ['immutableKey']);
        $state->remove('immutableKey');
    }

    public function testWithCreatesNewStateWithMergedParameters(): void
    {
        $state = new State(['key1' => 'value1']);
        $newState = $state->with(['key2' => 'value2']);

        $this->assertNotSame($state, $newState);
        $this->assertSame(['key1' => 'value1'], $state->all());
        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $newState->all());
    }

    public function testWithCreatesNewStateWithMergedParametersAndChangedImmutables(): void
    {
        $state = new State(['key1' => 'value1', 'immutableKey' => 'immutableValue'], ['immutableKey']);
        $state->set('key2', 'value2');
        $newState = $state->with(['key3' => 'value3', 'immutableKey' => 'newImmutableValue']);

        $this->assertNotSame($state, $newState, 'Not same instance');
        $this->assertSame(
            ['immutableKey' => 'immutableValue', 'key1' => 'value1', 'key2' => 'value2'],
            $state->all(),
            'Original state'
        );
        $this->assertSame(
            ['immutableKey' => 'newImmutableValue', 'key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3'],
            $newState->all(),
            'New state'
        );
    }
}
<?php

namespace DoubleBreak\PhpBatcher\Tests;

use DoubleBreak\PhpBatcher\Batcher\Generic;
use DoubleBreak\PhpBatcher\State;
use PHPUnit\Framework\TestCase;

class GenericBatcherTest extends TestCase
{
    public function testAddAddsItemsToBatch(): void
    {
        $batcher = new Generic(3);
        $batcher->add('item1');
        $batcher->add('item2');

        $reflection = new \ReflectionClass($batcher);
        $batchProperty = $reflection->getProperty('batch');
        $batchProperty->setAccessible(true);

        $this->assertSame(['item1', 'item2'], $batchProperty->getValue($batcher));
    }

    public function testFlushExecutesFlushCallback(): void
    {
        $callbackExecuted = false;
        $callback = function ($batch, $state) use (&$callbackExecuted) {
            $callbackExecuted = true;
            $this->assertSame(['item1', 'item2'], $batch);
            $this->assertInstanceOf(State::class, $state);
        };

        $batcher = new Generic(3, $callback);
        $batcher->add('item1');
        $batcher->add('item2');

        $this->assertFalse($callbackExecuted, 'Callback should not be executed before flush.');

        $batcher->flush();

        $this->assertTrue($callbackExecuted, 'Callback should be executed after flush.');
    }

    public function testFlushUpdatesStateCorrectly(): void
    {
        $batcher = new Generic(3);
        $batcher->add('item1');
        $batcher->add('item2');
        $batcher->flush();

        $reflection = new \ReflectionClass($batcher);
        $stateProperty = $reflection->getProperty('state');
        $stateProperty->setAccessible(true);

        /** @var State $state */
        $state = $stateProperty->getValue($batcher);
        $this->assertSame(Generic::ORDINALITY_LAST, $state->get('ordinality'));
        $this->assertSame(1, $state->get('batchNumber'));
        $this->assertSame(2, $state->get('totalProcessedItems'));
    }

    public function testExecuteFlushHandlesEmptyBatch(): void
    {
        $callbackExecuted = false;
        $callback = function ($batch, $state) use (&$callbackExecuted) {
            $callbackExecuted = true;
        };

        $batcher = new Generic(3, $callback);
        $batcher->flush();

        $this->assertFalse($callbackExecuted, 'Callback should not be executed for an empty batch.');
    }

    public function testAddTriggersFlushWhenBatchSizeExceeded(): void
    {
        $callbackExecuted = false;
        $callback = function ($batch, $state) use (&$callbackExecuted) {
            $callbackExecuted = true;
            $this->assertSame(['item1', 'item2', 'item3'], $batch, 'Batch should contain the first three items.');
        };

        $batcher = new Generic(3, $callback);

        // Add items up to the batch size
        $batcher->add('item1');
        $batcher->add('item2');
        $batcher->add('item3');

        // Assert that the callback has not been executed yet
        $this->assertFalse($callbackExecuted, 'Callback should not be executed when the batch is just filled.');

        // Add the overflow item, which should trigger the flush
        $batcher->add('item4');

        // Assert that the callback has been executed after the overflow item
        $this->assertTrue($callbackExecuted, 'Callback should be executed when the batch size is exceeded.');

        // Use reflection to check the current batch state
        $reflection = new \ReflectionClass($batcher);
        $batchProperty = $reflection->getProperty('batch');
        $batchProperty->setAccessible(true);

        $this->assertSame(['item4'], $batchProperty->getValue($batcher), 'Batch should contain only the overflow item after flush.');
    }
}

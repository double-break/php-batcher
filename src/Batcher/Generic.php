<?php

namespace DoubleBreak\PhpBatcher\Batcher;

use DoubleBreak\PhpBatcher\State;

class Generic
{

    const ORDINALITY_NONE = 0;
    const ORDINALITY_FIRST = 1;
    const ORDINALITY_LAST = 2;
    const ORDINALITY_MIDDLE = 3;

    private array $batch = [];
    private State $state;

    public function __construct(private $batchSize = 1000, private readonly ?\Closure $flushCallback = null)
    {
        $this->state = new State(
            parameters: ['ordinality' => self::ORDINALITY_NONE, 'batchNumber' => 0, 'totalProcessedItems' => 0],
            immutableKeys: ['ordinality', 'batchNumber', 'totalProcessedItems']
        );
    }

    public function add($item)
    {
        if (count($this->batch) >= $this->batchSize) {
            $this->executeFlush();
        }
        $this->batch[] = $item;
    }

    public function flush()
    {
        $this->state = $this->state->with(['ordinality' => self::ORDINALITY_LAST]);
        if (count($this->batch) > 0) {
            $this->executeFlush();
        }
    }

    private function executeFlush()
    {
        //We are making copy of the batch and empty the original array here
        //to guarantee the state will be clean no matter what is going on in the onFlush callback
        $batch = $this->batch;
        $this->batch = [];

        $newOrdinality = $this->state->get('ordinality');
        if ($this->state->get('ordinality') == self::ORDINALITY_NONE) {
            $newOrdinality =  self::ORDINALITY_FIRST;
        } elseif ($this->state->get('ordinality') == self::ORDINALITY_FIRST) {
            $newOrdinality =  self::ORDINALITY_MIDDLE;
        }

        $this->state = $this->state->with([
            'ordinality' => $newOrdinality,
            'batchNumber' => $this->state->get('batchNumber') + 1,
            'totalProcessedItems' => $this->state->get('totalProcessedItems') + count($batch)
        ]);


        if ($this->flushCallback) {
            call_user_func($this->flushCallback, $batch, $this->state);
        }
    }

    public function getState(): State
    {
        return $this->state;
    }
}
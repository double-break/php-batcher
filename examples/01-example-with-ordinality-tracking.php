<?php

use DoubleBreak\PhpBatcher\Batcher\Generic;

require_once __DIR__ . '/../vendor/autoload.php';


$batcher = new Generic(11, function($batch, $state) {
    echo "Batch number: " . $state->get('batchNumber') . "\n";
    echo "Total processed items: " . $state->get('totalProcessedItems') . "\n";
    echo "Batch size: " . count($batch) . "\n";
    echo "Items in batch: " . implode(", ", $batch) . "\n";
    echo "Is FIRST: " . ($state->get('ordinality') == Generic::ORDINALITY_FIRST ? 'true' : 'false') . "\n";
    echo "Is LAST: " . ($state->get('ordinality') == Generic::ORDINALITY_LAST ? 'true' : 'false') . "\n";
    echo "Is MIDDLE: " . ($state->get('ordinality') == Generic::ORDINALITY_MIDDLE ? 'true' : 'false') . "\n";
    echo "\n\n";
});

$numbers = range(1, 104);
foreach ($numbers as $number) {
    $batcher->add($number);
}
$batcher->flush();

/**
 OUTPUT (For each batch, the output will show something like this):
    Batch number: 1
    Total processed items: 11
    Batch size: 11
    Items in batch: 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11
    Is FIRST: true
    Is LAST: false
    Is MIDDLE: false

    ...
 */


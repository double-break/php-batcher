<?php

use DoubleBreak\PhpBatcher\Batcher\Generic;

require_once __DIR__ . '/../vendor/autoload.php';

$batcher = new Generic(5, function (array $batch) {
    // Process the batch
    echo "Processing batch: " . implode(", ", $batch) . PHP_EOL;
});

$batcher->add("Item 1");
$batcher->add("Item 2");
$batcher->add("Item 3");
$batcher->add("Item 4");
$batcher->add("Item 5"); // Important: WILL NOT trigger the flush callback
$batcher->add("Item 6"); // This triggers the automatic flush before adding the new item

$batcher->flush(); // Manually flush remaining items

/**
 Output:
    Processing batch: Item 1, Item 2, Item 3, Item 4, Item 5
    Processing batch: Item 6
 */
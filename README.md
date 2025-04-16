
# PHP Batcher

**PHP Batcher** is a simple library providing batching functionality for PHP projects.

## Features

- Efficient batching of items.
- Ordinality tracking (e.g., first, middle, last batch).
- Custom state management whiten the batch processing.

## Requirements

- PHP 8.4 or higher.

## Installation

Install the library using Composer:

```bash
composer require double-break/php-batcher
```

## Usage

### Basic Example

Here is an example of how to use the library:

```php
use DoubleBreak\PhpBatcher\Batcher\Generic;

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
```

### Example with Ordinality Tracking

This example demonstrates how to track the ordinality of batches (e.g., first, middle, last):

```php
<?php
use DoubleBreak\PhpBatcher\Batcher\Generic;

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
```

### Example with Custom State Management

This example shows how to manage custom state during batch processing:

```php
<?php
use DoubleBreak\PhpBatcher\Batcher\Generic;

function submit($data) {
    // Simulate a submission process
    echo "Submitting:" . PHP_EOL;
    print_r($data);
    echo PHP_EOL;

    // Simulates downstream logic managing tracking key creation or reuse
    return $data['isFirst'] ? uniqid() : $data['trackingKey'];
}

$batcher = new Generic(11, function($batch, $state) {
    $trackingKey = $state->get('myTrackingKey');

    $data = [
        'isFirst' => $state->get('ordinality') == Generic::ORDINALITY_FIRST,
        'isLast' => $state->get('ordinality') == Generic::ORDINALITY_LAST,
        'items' => $batch,
    ];

    if ($trackingKey !== null) {
        $data['trackingKey'] = $trackingKey;
    }

    $trackingKey = submit($data);

    $state->set('myTrackingKey', $trackingKey);
});

$numbers = range(1, 104);
foreach ($numbers as $number) {
    $batcher->add($number);
}
$batcher->flush();
```

## Development

### Running Tests

To run the tests, use PHPUnit:

```bash
composer install
vendor/bin/phpunit tests
```

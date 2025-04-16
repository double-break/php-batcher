<?php

use DoubleBreak\PhpBatcher\Batcher\Generic;

require_once __DIR__ . '/../vendor/autoload.php';


function submit($data) {
    // Simulate a submission process
    echo "Submitting:" . PHP_EOL;
    print_r(", ", $data) . PHP_EOL;


    //simulates downstream logic managing tracking key creation or reuse
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


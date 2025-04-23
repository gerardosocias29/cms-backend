<?php

require_once 'vendor/autoload.php';

use App\Modules\Queue\Queue;

$queue = new Queue();

// Enqueue some items
$queue->enqueue("Patient A");
$queue->enqueue("Patient B");
$queue->enqueue("Patient C");

echo "Queue size: " . $queue->size() . PHP_EOL; // Output: 3

// Dequeue an item
$firstPatient = $queue->dequeue();
echo "First patient: " . $firstPatient . PHP_EOL; // Output: Patient A
echo "Queue size: " . $queue->size() . PHP_EOL; // Output: 2

// Peek at the next item
$nextPatient = $queue->peek();
echo "Next patient: " . $nextPatient . PHP_EOL; // Output: Patient B

// Check if the queue is empty
echo "Is queue empty? " . ($queue->isEmpty() ? "Yes" : "No") . PHP_EOL; // Output: No

// Dequeue the remaining items
$queue->dequeue();
$queue->dequeue();

// Check if the queue is empty again
echo "Is queue empty? " . ($queue->isEmpty() ? "Yes" : "No") . PHP_EOL; // Output: Yes
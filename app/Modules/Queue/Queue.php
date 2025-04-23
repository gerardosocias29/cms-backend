<?php

namespace App\Modules\Queue;

class Queue
{
    private $queue = [];

    /**
     * Add an item to the end of the queue.
     *
     * @param mixed $item The item to enqueue.
     * @return void
     */
    public function enqueue($item)
    {
        $this->queue[] = $item;
    }

    /**
     * Remove and return the item at the front of the queue.
     *
     * @return mixed|null The item at the front of the queue, or null if the queue is empty.
     */
    public function dequeue()
    {
        if ($this->isEmpty()) {
            return null;
        }

        return array_shift($this->queue);
    }

    /**
     * Check if the queue is empty.
     *
     * @return bool True if the queue is empty, false otherwise.
     */
    public function isEmpty()
    {
        return empty($this->queue);
    }

    /**
     * Get the number of items in the queue.
     *
     * @return int The number of items in the queue.
     */
    public function size()
    {
        return count($this->queue);
    }

    /**
     * Peek at the item at the front of the queue without removing it.
     *
     * @return mixed|null The item at the front of the queue, or null if the queue is empty.
     */
    public function peek()
    {
        if ($this->isEmpty()) {
            return null;
        }

        return reset($this->queue);
    }
}
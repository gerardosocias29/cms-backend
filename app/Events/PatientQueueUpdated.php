<?php

namespace App\Events;

use App\Models\Patient;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatientQueueUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $nextDepartmentId;

    public function __construct(int $nextDepartmentId)
    {
      $this->nextDepartmentId = $nextDepartmentId;
    }

    public function broadcastAs()
    {
      return 'PatientQueueUpdated';
    }

    public function broadcastOn()
    {
      return new Channel('department_' . $this->nextDepartmentId);
    }
}
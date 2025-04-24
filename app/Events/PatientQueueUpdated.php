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

    public $patient;
    public $nextDepartmentId;

    /**
     * Create a new event instance.
     *
     * @param Patient $patient
     * @param int $nextDepartmentId
     */
    public function __construct(Patient $patient, int $nextDepartmentId)
    {
      $this->patient = $patient;
      $this->nextDepartmentId = $nextDepartmentId;
    }

    public function broadcastAs()
    {
      return 'PatientQueueUpdated';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
      return [
        new PrivateChannel('department_' . $this->nextDepartmentId),
      ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
      return [
        'patientId' => $this->patient->id,
        'patientName' => $this->patient->name,
        'nextDepartmentId' => $this->nextDepartmentId,
        'timestamp' => now()->toDateTimeString(),
      ];
    }
}
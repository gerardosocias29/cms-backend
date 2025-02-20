<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Queue;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class QueueController extends Controller
{
    public function index()
    {
        return response()->json(Queue::orderBy('number', 'asc')->get());
    }

    public function store(Request $request)
    {
        $lastQueue = Queue::where('department_id', $request->department_id)->orderBy('number', 'desc')->first();
        $nextNumber = $lastQueue ? $lastQueue->number + 1 : 1;
        
        $queue = Queue::create([
            'department_id' => $request->department_id,
            'priority' => $request->priority, // Regular, Senior/PWD, Priority
            'number' => $nextNumber,
            'status' => 'waiting'
        ]);
        
        // event(new QueueUpdated()); // Commented as requested
        
        return response()->json($queue);
    }

    public function callNext(Request $request)
    {
        $next = Queue::where('department_id', $request->department_id)
            ->where('status', 'waiting')
            ->orderByRaw("CASE priority WHEN 'Priority' THEN 1 WHEN 'Senior/PWD' THEN 2 ELSE 3 END")
            ->orderBy('number', 'asc')
            ->first();
        
        if ($next) {
            $next->update(['status' => 'called']);
            // event(new QueueUpdated()); // Commented as requested
            return response()->json($next);
        }
        return response()->json(['message' => 'No patients in queue'], 404);
    }

    public function printTicket(Request $request)
    {
        $connector = new WindowsPrintConnector("Thermal_Printer");
        $printer = new Printer($connector);
        $printer->text("Queue Ticket\n");
        $printer->text("Department ID: " . $request->department_id . "\n");
        $printer->text("Priority: " . $request->priority . "\n");
        $printer->text("Number: " . $request->number . "\n");
        $printer->text("Name: " . $request->patient_name . "\n");
        $printer->cut();
        $printer->close();
        return response()->json(['message' => 'Printed successfully']);
    }
}
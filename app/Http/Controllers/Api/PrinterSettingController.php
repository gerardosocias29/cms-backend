<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Printer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Import DB facade for transactions
use Illuminate\Support\Facades\Log; // Optional: for logging errors
use Illuminate\Validation\ValidationException; // For specific validation error handling

class PrinterSettingController extends Controller
{
    /**
     * Get the default printer setting.
     */
    public function getDefaultPrinter()
    {
        try {
            $defaultPrinter = Printer::where('is_default', true)->first();

            // Return the printer if found, or null with 200 OK if not found (frontend handles null)
            return response()->json($defaultPrinter, 200);

        } catch (\Exception $e) {
            Log::error("Error fetching default printer: " . $e->getMessage());
            return response()->json(['message' => 'Failed to fetch default printer setting.'], 500);
        }
    }

    /**
     * Set the default printer.
     */
    public function setDefaultPrinter(Request $request)
    {
        try {
            // Change validation to numeric for IDs from WebUSB
            $validated = $request->validate([
                'vendorId' => 'required|numeric',
                'productId' => 'required|numeric',
                'name' => 'required|string|max:255',
                'serialNumber' => 'nullable|string|max:255',
            ]);

            $vendorId = $validated['vendorId'];
            $productId = $validated['productId'];
            $name = $validated['name'];
            $serialNumber = $validated['serialNumber'] ?? null;

            $newDefault = null; // Variable to hold the printer being set as default

            DB::transaction(function () use ($vendorId, $productId, $name, $serialNumber, &$newDefault) {
                // 1. Unset current default
                Printer::where('is_default', true)->update(['is_default' => false]);

                // 2. Find or Create the target printer
                // Using updateOrCreate is slightly cleaner here
                $newDefault = Printer::updateOrCreate(
                    [
                        'vendor_id' => $vendorId,
                        'product_id' => $productId,
                        // If serial number should be part of the unique key, add it here:
                        // 'serial_number' => $serialNumber,
                    ],
                    [
                        'name' => $name,
                        'serial_number' => $serialNumber, // Ensure serial is saved/updated
                        'is_default' => true // Set this one as default
                    ]
                );
            });

            // Return the printer that was just set as default
            return response()->json($newDefault, 200);

        } catch (ValidationException $e) {
            // Return validation errors
            return response()->json(['message' => 'Validation failed.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Error setting default printer: " . $e->getMessage());
            return response()->json(['message' => 'Failed to set default printer.'], 500);
        }
    }
}
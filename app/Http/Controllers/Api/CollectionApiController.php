<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class CollectionApiController extends Controller
{
    /**
     * Receive and store collection data from external systems
     * 
     * POST /api/finance/collections/receive
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function receive(Request $request)
    {
        try {
            // Validate incoming data
            $validated = $request->validate([
                'customer_name' => 'required|string|max:255',
                'invoice_number' => 'required|string|unique:collections,invoice_number',
                'amount_due' => 'required|numeric|min:0.01',
                'amount_paid' => 'required|numeric|min:0',
                'status' => 'nullable|string|in:Pending,Partial,Paid,Overdue,Cancelled',
                'payment_date' => 'nullable|date_format:Y-m-d',
                'employee_id' => 'nullable|integer|exists:users,id',
                'remarks' => 'nullable|string',
                'external_id' => 'nullable|string|unique:collections,external_id',
            ]);

            // Set default status if not provided
            if (!isset($validated['status'])) {
                // Determine status based on payment
                if ($validated['amount_paid'] >= $validated['amount_due']) {
                    $validated['status'] = 'Paid';
                } elseif ($validated['amount_paid'] > 0) {
                    $validated['status'] = 'Partial';
                } else {
                    $validated['status'] = 'Pending';
                }
            }

            // Set default date if not provided
            if (!isset($validated['payment_date'])) {
                $validated['payment_date'] = now()->format('Y-m-d');
            }

            // Create collection record
            $collection = Collection::create($validated);

            Log::info('Collection received from external system', [
                'invoice_number' => $collection->invoice_number,
                'amount_paid' => $collection->amount_paid,
                'id' => $collection->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Collection received and stored successfully',
                'data' => [
                    'id' => $collection->id,
                    'invoice_number' => $collection->invoice_number,
                    'customer_name' => $collection->customer_name,
                    'amount_paid' => $collection->amount_paid,
                    'status' => $collection->status,
                    'created_at' => $collection->created_at
                ]
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Validation error in collection API', [
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error receiving collection', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing collection',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get collection status
     * 
     * GET /api/finance/collections/status/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function status($id)
    {
        try {
            $collection = Collection::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $collection->id,
                    'invoice_number' => $collection->invoice_number,
                    'customer_name' => $collection->customer_name,
                    'amount_due' => $collection->amount_due,
                    'amount_paid' => $collection->amount_paid,
                    'status' => $collection->status,
                    'payment_date' => $collection->payment_date,
                    'created_at' => $collection->created_at,
                    'updated_at' => $collection->updated_at
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error retrieving collection status', [
                'id' => $id,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving collection'
            ], 500);
        }
    }
}

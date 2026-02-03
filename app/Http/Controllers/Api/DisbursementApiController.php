<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Disbursement;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class DisbursementApiController extends Controller
{
    /**
     * Receive and store disbursement data from external systems
     * 
     * POST /api/finance/disbursements/receive
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function receive(Request $request)
    {
        try {
            // Validate incoming data
            $validated = $request->validate([
                'voucher_no' => 'required|string|unique:disbursements,voucher_no',
                'vendor' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'status' => 'nullable|string|in:Pending,Approved,Processed,Cancelled',
                'disbursement_date' => 'nullable|date_format:Y-m-d',
                'external_id' => 'nullable|string|unique:disbursements,external_id',
                'remarks' => 'nullable|string',
            ]);

            // Set default status if not provided
            if (!isset($validated['status'])) {
                $validated['status'] = 'Pending';
            }

            // Set default date if not provided
            if (!isset($validated['disbursement_date'])) {
                $validated['disbursement_date'] = now()->format('Y-m-d');
            }

            // Create disbursement record
            $disbursement = Disbursement::create($validated);

            Log::info('Disbursement received from external system', [
                'voucher_no' => $disbursement->voucher_no,
                'amount' => $disbursement->amount,
                'id' => $disbursement->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Disbursement received and stored successfully',
                'data' => [
                    'id' => $disbursement->id,
                    'voucher_no' => $disbursement->voucher_no,
                    'amount' => $disbursement->amount,
                    'status' => $disbursement->status,
                    'created_at' => $disbursement->created_at
                ]
            ], 201);

        } catch (ValidationException $e) {
            Log::warning('Validation error in disbursement API', [
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error receiving disbursement', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing disbursement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get disbursement status
     * 
     * GET /api/finance/disbursements/status/{id}
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function status($id)
    {
        try {
            $disbursement = Disbursement::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $disbursement->id,
                    'voucher_no' => $disbursement->voucher_no,
                    'vendor' => $disbursement->vendor,
                    'amount' => $disbursement->amount,
                    'status' => $disbursement->status,
                    'disbursement_date' => $disbursement->disbursement_date,
                    'created_at' => $disbursement->created_at,
                    'updated_at' => $disbursement->updated_at
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Disbursement not found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error retrieving disbursement status', [
                'id' => $id,
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving disbursement'
            ], 500);
        }
    }
}

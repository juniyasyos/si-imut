<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyReportResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DailyReportResponseController extends Controller
{
    /**
     * Delete a daily report response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $record = DailyReportResponse::findOrFail($id);

            // Authorization via policy
            $this->authorize('delete', $record);

            $record->delete();

            Log::info('Daily report response deleted', [
                'id' => $id,
                'user_id' => Auth::id(),
                'user_name' => Auth::user()?->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dihapus'
            ], 200);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk menghapus laporan ini'
            ], 403);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting daily report response', [
                'id' => $id,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus laporan'
            ], 500);
        }
    }
}

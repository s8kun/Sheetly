<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSheetRequest;
use App\Models\Sheet;
use App\Models\Subject;
use Cloudinary\Cloudinary;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class SheetController extends Controller
{
    /**
     * Retrieve all approved sheets across all subjects for public feed.
     *
     * @return JsonResponse Returns an array of approved sheets with associated subject data.
     */
    public function index(): JsonResponse
    {
        $sheets = Sheet::where('status', 'approved')
            ->with('subject')
            ->latest()
            ->get();

        return response()->json($sheets);
    }

    // عرض شيتات المستخدم الحالي
    public function mySheets(): JsonResponse
    {
        $sheets = Sheet::where('user_id', Auth::id())
            ->with('subject')
            ->latest()
            ->get();

        return response()->json($sheets);
    }

    // عرض الشيتات المعلقة (للأدمن فقط - Middleware محمي)
    public function pendingSheets(): JsonResponse
    {
        $sheets = Sheet::where('status', 'pending')
            ->with(['subject', 'user'])
            ->latest()
            ->get();

        return response()->json($sheets);
    }

    // عرض بيانات شيت واحد
    public function show(Sheet $sheet): JsonResponse
    {
        if ($sheet->status !== 'approved') {
            $user = Auth::user();
            if (! $user || ($user->role !== 'admin' && $user->id !== $sheet->user_id)) {
                abort(403, 'This sheet is pending approval.');
            }
        }

        return response()->json($sheet->load('subject'));
    }

    /**
     * Handle the upload and creation of a new Sheet.
     * 
     * Stores the physical file in Cloudinary organized by Subject Code and Type.
     * Automatically calculates the next chapter number if one is not provided for 'chapter' types.
     *
     * @param StoreSheetRequest $request Validated request containing the file, subject_id, and metadata.
     * @return JsonResponse Returns the created sheet model.
     * @throws \Exception If Cloudinary upload fails.
     */
    public function store(StoreSheetRequest $request): JsonResponse
    {
        $subject = Subject::findOrFail($request->subject_id);

        $chapterNumber = $request->chapter_number;

        if ($request->type === 'chapter' && ! $request->filled('chapter_number')) {
            $chapterNumber = Sheet::where('subject_id', $subject->id)
                ->where('type', 'chapter')
                ->max('chapter_number') + 1;
        }

        $cloudinary = app(Cloudinary::class);

        $folder = "Sheetly/{$subject->code}";

        switch ($request->type) {
            case 'chapter':
                $folder .= "/Chapters/Chapter-{$chapterNumber}";
                break;
            case 'midterm':
                $folder .= '/Midterms'.($chapterNumber ? "/Midterm-{$chapterNumber}" : '');
                break;
            case 'final':
                $folder .= '/Finals'.($chapterNumber ? "/Final-{$chapterNumber}" : '');
                break;
        }

        try {
            $upload = $cloudinary->uploadApi()->upload(
                $request->file('file')->getRealPath(),
                [
                    'folder' => $folder,
                    'resource_type' => 'raw',
                ]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Cloudinary Upload Failed: '.$e->getMessage());

            return response()->json([
                'message' => 'File upload failed. Please check the file size or try again later.',
                'error' => $e->getMessage(),
            ], 500);
        }

        $sheet = Sheet::create([
            'title' => $request->title,
            'subject_id' => $request->subject_id,
            'user_id' => Auth::id(),
            'type' => $request->type,
            'chapter_number' => $chapterNumber,
            'file_url' => $upload['secure_url'],
            'status' => Auth::user()->role === 'admin' ? 'approved' : 'pending',
        ]);

        return response()->json([
            'message' => 'Sheet uploaded successfully.',
            'data' => $sheet,
        ], 201);
    }

    /**
     * Retrieve the secure download URL for a file and increment its download counter.
     * Ensures only approved sheets or authorized users can download.
     *
     * @param Sheet $sheet The resolved Sheet model.
     * @return JsonResponse JSON object containing the `download_url`.
     */
    public function download(Sheet $sheet): JsonResponse
    {
        if ($sheet->status !== 'approved' && (! Auth::check() || (Auth::user()->role !== 'admin' && Auth::id() !== $sheet->user_id))) {
            abort(403);
        }

        $sheet->increment('downloads_count');

        return response()->json([
            'download_url' => $sheet->file_url,
        ]);
    }

    // موافقة (أدمن فقط)
    public function approve(Sheet $sheet): JsonResponse
    {
        $sheet->update(['status' => 'approved']);

        return response()->json(['message' => 'Sheet approved.']);
    }

    // رفض (أدمن فقط)
    public function reject(Sheet $sheet): JsonResponse
    {
        $sheet->update(['status' => 'rejected']);

        return response()->json(['message' => 'Sheet rejected.']);
    }

    // حذف (صاحب الشيت أو الأدمن)
    public function destroy(Sheet $sheet): JsonResponse
    {
        if (Auth::user()->role !== 'admin' && Auth::id() !== $sheet->user_id) {
            abort(403, 'You are not authorized to delete this sheet.');
        }

        $sheet->delete();

        return response()->json(['message' => 'Sheet deleted successfully.']);
    }
}

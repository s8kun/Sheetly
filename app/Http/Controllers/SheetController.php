<?php

namespace App\Http\Controllers;

use App\Models\Sheet;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use App\Enums\Status;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Cloudinary\Cloudinary;
use App\Http\Requests\StoreSheetRequest;

class SheetController extends Controller
{
    // عرض الشيتات المعتمدة (للاستخدام العام)
    public function index(): JsonResponse
    {
        $sheets = Sheet::where('status', "approved")
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
        if ($sheet->status !== "approved") {
            $user = Auth::user();
            if (!$user || ($user->role !== 'admin' && $user->id !== $sheet->user_id)) {
                abort(403, 'This sheet is pending approval.');
            }
        }

        return response()->json($sheet->load('subject'));
    }

    // رفع شيت
    public function store(StoreSheetRequest $request): JsonResponse
    {
        $subject = Subject::findOrFail($request->subject_id);
        $cloudinary = app(Cloudinary::class);
        
        $folder = "Sheetly/{$subject->code}";

        switch ($request->type) {
            case 'chapter':
                $folder .= "/Chapters/Chapter-{$request->chapter_number}";
                break;
            case 'midterm':
                $folder .= "/Midterms" . ($request->filled('chapter_number') ? "/Midterm-{$request->chapter_number}" : "");
                break;
            case 'final':
                $folder .= "/Finals" . ($request->filled('chapter_number') ? "/Final-{$request->chapter_number}" : "");
                break;
        }

        $upload = $cloudinary->uploadApi()->upload(
            $request->file('file')->getRealPath(),
            [
                'folder' => $folder,
                'resource_type' => 'raw'
            ]
        );

        $sheet = Sheet::create([
            'title' => $request->title,
            'subject_id' => $request->subject_id,
            'user_id' => Auth::id(),
            'type' => $request->type,
            'chapter_number' => $request->chapter_number,
            'file_url' => $upload['secure_url'],
            'status' => Auth::user()->role === 'admin' ? 'approved' : 'pending',
        ]);

        return response()->json([
            'message' => 'Sheet uploaded successfully.',
            'data' => $sheet
        ], 201);
    }

    // تحميل الشيت
    public function download(Sheet $sheet): JsonResponse
    {
        if ($sheet->status !== "approved" && (!Auth::check() || (Auth::user()->role !== 'admin' && Auth::id() !== $sheet->user_id))) {
            abort(403);
        }

        $sheet->increment('downloads_count');

        return response()->json([
            'download_url' => $sheet->file_url
        ]);
    }

    // موافقة (أدمن فقط)
    public function approve(Sheet $sheet): JsonResponse
    {
        $sheet->update(['status' => "approved"]);
        return response()->json(['message' => 'Sheet approved.']);
    }

    // رفض (أدمن فقط)
    public function reject(Sheet $sheet): JsonResponse
    {
        $sheet->update(['status' => "rejected"]);
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

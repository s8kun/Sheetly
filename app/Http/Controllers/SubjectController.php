<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SubjectController extends Controller
{
    // عرض كل المواد مع دعم البحث
    public function index(Request $request): JsonResponse
    {
        $query = Subject::query();

        if ($request->filled('search')) {
            $search = strtoupper($request->input('search'));

            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            })
                ->orderByRaw("CASE WHEN code LIKE '{$search}%' THEN 1 ELSE 2 END")
                ->orderBy('code');
        } else {
            $query->orderBy('code');
        }

        return response()->json($query->get());
    }

    // عرض تفاصيل المادة
    public function show(Subject $subject): JsonResponse
    {
        $chapters = $subject->sheets()
            ->where('status', 'approved')
            ->where('type', 'chapter')
            ->whereNotNull('chapter_number')
            ->select('chapter_number')
            ->distinct()
            ->orderBy('chapter_number')
            ->pluck('chapter_number');

        $midterms = $subject->sheets()
            ->where('status', 'approved')
            ->where('type', 'midterm')
            ->latest()
            ->get();

        $finals = $subject->sheets()
            ->where('status', 'approved')
            ->where('type', 'final')
            ->latest()
            ->get();

        return response()->json([
            'subject' => $subject,
            'chapters' => $chapters,
            'midterms' => $midterms,
            'finals' => $finals,
        ]);
    }

    // عرض شيتات شباتر مادة واحدة
    public function showChapter(Subject $subject, int $chapterNumber): JsonResponse
    {
        $sheets = $subject->sheets()
            ->where('status', 'approved')
            ->where('type', 'chapter')
            ->where('chapter_number', $chapterNumber)
            ->latest()
            ->get();

        return response()->json([
            'code' => $subject->code,
            'chapter_number' => $chapterNumber,
            'sheets' => $sheets,
        ]);
    }

    // إنشاء مادة (الأمان عبر Middleware و Request)
    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $subject = Subject::create($request->validated());

        return response()->json(['message' => 'Subject created.', 'data' => $subject], 201);
    }

    // تعديل مادة
    public function update(UpdateSubjectRequest $request, Subject $subject): JsonResponse
    {
        $subject->update($request->validated());

        return response()->json(['message' => 'Subject updated.', 'data' => $subject]);
    }

    // حذف مادة
    public function destroy(Subject $subject): JsonResponse
    {
        $subject->delete();

        return response()->json(['message' => 'Subject deleted.']);
    }
}

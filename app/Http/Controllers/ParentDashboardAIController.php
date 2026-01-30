<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ParentDashboardAIController extends Controller
{
    public function getStudentAiAssistanceDetails(int $studentId, string $year, string $examType): JsonResponse
    {
        $standardTerms = ['Term 1', 'Term 2', 'Term 3', 'All'];

        if (! in_array($examType, $standardTerms, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid examType. Use Term 1, Term 2, Term 3, or All.',
            ], 422);
        }

        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Gemini API key is not configured.',
            ], 500);
        }

        /** @var ParentReportController $parentReportController */
        $parentReportController = app(ParentReportController::class);

        // Reuse existing report endpoints to build a rich dataset for AI
        $performanceResponse = $parentReportController->getParentReport($studentId, $year, $examType);

        if ($performanceResponse->getStatusCode() === 404) {
            return response()->json([
                'success' => false,
                'message' => 'Student or marks not found for the given year / exam type.',
            ], 404);
        }

        $classAverageResponse   = $parentReportController->getStudentClassAverage($studentId, $year, $examType);
        $weakSubjectsResponse   = $parentReportController->getStudentWeekSubjectDetails($studentId, $year, $examType);
        $strongSubjectsResponse = $parentReportController->getStudentStrongSubjectDetails($studentId, $year, $examType);

        $dataset = [
            'studentPerformanceReport' => $performanceResponse->getData(true),
            'classAverage'             => $classAverageResponse->getData(true),
            'weakSubjects'             => $weakSubjectsResponse->getData(true),
            'strongSubjects'           => $strongSubjectsResponse->getData(true),
        ];

        $prompt = "You are an AI assistant helping parents understand a student's academic performance. " .
            "You will receive structured JSON data that includes the student's report for a specific year and exam type, " .
            "their average compared to the class, and their weak and strong subjects. " .
            "Analyse the data and then respond in a very short way.\n" .
            "Write only 5 to 6 of the most important sentences about this child, focusing on: overall performance, main strengths, main weaknesses, and clear next steps.\n" .
            "Do NOT use headings, bullet points, or any other markdown formatting.\n" .
            "Write in simple, parent-friendly language and stay focused only on this student.\n\n" .
            "Here is the JSON data for this student: " . json_encode($dataset, JSON_THROW_ON_ERROR);

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type'   => 'application/json',
                    'x-goog-api-key' => $apiKey,
                ])
                ->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-3-flash-preview:generateContent',
                    $payload
                );
        } catch (\Throwable $throwable) {
            Log::error('ParentDashboardAIController: Gemini API call failed', [
                'studentId' => $studentId,
                'year'      => $year,
                'examType'  => $examType,
                'error'     => $throwable->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to contact AI service.',
            ], 502);
        }

        if (! $response->successful()) {
            Log::warning('ParentDashboardAIController: Gemini API returned error', [
                'status'   => $response->status(),
                'body'     => $response->body(),
                'studentId' => $studentId,
                'year'     => $year,
                'examType' => $examType,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'AI service returned an error.',
            ], 502);
        }

        $body = $response->json();

        $suggestionText = null;
        if (isset($body['candidates'][0]['content']['parts'])) {
            $parts = $body['candidates'][0]['content']['parts'];
            $texts = [];
            foreach ($parts as $part) {
                if (isset($part['text'])) {
                    $texts[] = $part['text'];
                }
            }
            $suggestionText = trim(implode("\n\n", $texts));
        }

        return response()->json([
            'success'    => true,
            'studentId'  => $studentId,
            'year'       => $year,
            'examType'   => $examType,
            'suggestion' => $suggestionText,
            'raw'        => $body,
        ]);
    }
}

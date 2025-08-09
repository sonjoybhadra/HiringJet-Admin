<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResumeRequest;
use App\Services\DeepSeekResumeParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ResumeParserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param DeepSeekResumeParser $parser
     */
    public function __construct(
        protected DeepSeekResumeParser $parser
    ) {}

    /**
     * Parse a resume and optionally match it to a job description.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function parse(Request $request): JsonResponse
    {
        // Check if API is available before proceeding
        if (!$this->parser->isApiAvailable()) {
            Log::warning('Resume parsing attempted while API is unavailable');
            return response()->json([
                'success' => false,
                'message' => 'Resume parsing service is currently unavailable. Please try again later.',
                'error_code' => 'API_UNAVAILABLE',
            ], 503); // Service Unavailable
        }

        // Check if request has any data
        if (!$request->hasAny(['resume', 'job_description'])) {
            return response()->json([
                'success' => false,
                'message' => 'No data posted. Please provide resume data.',
                'error_code' => 'MISSING_DATA',
            ], 400);
        }

        try {
            // Validate the incoming request
            $validated = $request->validate([
                'resume' => 'required|file|mimes:pdf,doc,docx|max:10240', // 10MB limit
                'job_description' => 'sometimes|string|max:10000',
                'options' => 'sometimes|array',
            ]);

            // Start timer for performance monitoring
            $startTime = microtime(true);

            // Parse the resume with any provided options
            $options = $request->input('options', []);
            $parsedData = $this->parser->parseResume($validated['resume'], $options);

            // Record parsing time for monitoring
            $parsingTime = microtime(true) - $startTime;
            Log::info('Resume parsed successfully', [
                'file_name' => $validated['resume']->getClientOriginalName(),
                'parsing_time' => $parsingTime,
            ]);

            // Match to job description if provided
            if ($request->has('job_description')) {
                $matchStartTime = microtime(true);

                $matchingResult = $this->parser->matchToJobDescription(
                    $parsedData,
                    $validated['job_description'],
                    $request->input('matching_options', [])
                );

                // Add matching data to result
                $parsedData['matching'] = [
                    'score' => $matchingResult['score'] ?? null,
                    'insights' => $matchingResult['insights'] ?? [],
                    'processing_time' => microtime(true) - $matchStartTime,
                ];

                Log::info('Resume matched to job description', [
                    'matching_time' => microtime(true) - $matchStartTime,
                ]);
            }

            // Return the successful response
            return response()->json([
                'success' => true,
                'data' => $parsedData,
                'meta' => [
                    'processing_time' => microtime(true) - $startTime,
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);

        } catch (ValidationException $e) {
            // Handle validation errors specifically
            Log::notice('Resume parsing validation failed', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
                'error_code' => 'VALIDATION_ERROR',
            ], 422);

        } catch (\RuntimeException $e) {
            // Handle expected runtime exceptions from the parser
            Log::error('Resume parsing runtime exception', [
                'message' => $e->getMessage(),
                'file' => $request->hasFile('resume') ? $request->file('resume')->getClientOriginalName() : 'No file',
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'PARSING_ERROR',
            ], 500);

        } catch (\Exception $e) {
            // Handle unexpected exceptions
            Log::critical('Unexpected error in resume parsing', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while processing your request.',
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Get health status of the resume parsing service.
     *
     * @return JsonResponse
     */
    public function healthCheck(): JsonResponse
    {
        try {
            $isAvailable = $this->parser->isApiAvailable();

            return response()->json([
                'success' => true,
                'status' => $isAvailable ? 'available' : 'unavailable',
                'timestamp' => now()->toIso8601String(),
            ], $isAvailable ? 200 : 503);

        } catch (\Exception $e) {
            Log::error('Health check failed', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Failed to check service health',
                'timestamp' => now()->toIso8601String(),
            ], 500);
        }
    }

    /**
     * Get supported file types and limits.
     *
     * @return JsonResponse
     */
    public function supportedFileTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'supported_types' => [
                'pdf' => 'application/pdf',
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ],
            'limits' => [
                'max_file_size' => '10MB',
                'max_job_description_length' => 10000,
            ],
        ]);
    }
}

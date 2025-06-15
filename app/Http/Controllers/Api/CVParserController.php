<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CVParseRequest;
use App\Http\Resources\CVResource;
use App\Services\CVParserService;
use Illuminate\Http\JsonResponse;

class CVParserController extends Controller
{
    /**
     * @var CVParserService
     */
    protected CVParserService $cvParserService;

    /**
     * Inject the CVParserService.
     *
     * @param CVParserService $cvParserService
     */
    public function __construct(CVParserService $cvParserService)
    {
        $this->cvParserService = $cvParserService;
    }

    /**
     * Parse an uploaded CV file and return structured data.
     *
     * @param CVParseRequest $request
     * @return JsonResponse
     */
    public function parse(CVParseRequest $request): JsonResponse
    {
        $file = $request->file('cv_file');

        // Call the CVParserService
        $result = $this->cvParserService->parse($file);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to parse CV',
                'error' => $result['error'],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'CV parsed successfully',
            'data' => new CVResource($result['data']),
        ], 200);
    }

    /**
     * Return supported file formats.
     *
     * @return JsonResponse
     */
    public function supportedFormats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'formats' => [
                'pdf' => 'Adobe PDF Document',
                'doc' => 'Microsoft Word Document (Legacy)',
                'docx' => 'Microsoft Word Document',
                'txt' => 'Plain Text File',
            ],
        ]);
    }
}

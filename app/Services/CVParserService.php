<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\PdfToText\Pdf;
use PhpOffice\PhpWord\IOFactory as WordParser;
use PhpOffice\PhpWord\Element\Run;
use Exception;

class CVParserService
{
    protected $supportedFormats = ['pdf', 'doc', 'docx', 'txt', 'rtf'];

    // Improved name patterns with better accuracy
    protected $namePatterns = [
        // Full name at start of document (most common)
        '/^([A-Z][A-Z\s]{2,50})\s*$/m',  // ALL CAPS names
        '/^([A-Z][a-z]+(?:\s+[A-Z][a-z]*\.?)*\s+[A-Z][a-z]+)\s*$/m', // Proper case names
        '/^([A-Z][a-z]+(?:\s+[A-Z]\.)*\s+[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\s*$/m', // With middle initials
    ];

    // Common CV section headers - updated with more variations
    protected $sectionHeaders = [
        'experience' => [
            'work experience', 'experience', 'employment', 'professional experience',
            'career history', 'work experiences', 'employment history', 'job experience'
        ],
        'education' => [
            'education', 'educational background', 'academic background', 'tertiary education',
            'educational qualification', 'educational qualifications', 'academic qualification',
            'academic qualifications', 'qualifications'
        ],
        'skills' => [
            'skills', 'technical skills', 'competencies', 'core competencies', 'computer proficiency',
            'technical competencies', 'key skills', 'professional skills'
        ],
        'objective' => [
            'career objective', 'objective', 'summary', 'professional summary', 'carrer objective',
            'career goal', 'professional objective', 'career summary'
        ],
        'contact' => ['contact', 'contact information', 'personal information'],
        'certifications' => [
            'certifications', 'certificates', 'training', 'professional trainings',
            'seminars and training attended', 'professional training', 'seminars attended',
            'training attended', 'courses'
        ],
        'projects' => ['projects', 'key projects', 'project', 'major projects'],
        'languages' => ['languages', 'language skills', 'languages known', 'languages know'],
        'references' => ['references', 'character reference', 'character references'],
        'personal' => [
            'personal background', 'personal profile', 'personal details', 'personal information',
            'additional relevant information', 'strenghts', 'strengths'
        ]
    ];

    // Contact patterns
    protected $contactPatterns = [
        'email' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
        'phone' => '/(?:\+?\d{1,4}[-.\s]?)?\(?\d{1,4}\)?[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}/',
        'address' => '/(?:address|addr)\s*:?\s*([^\n]+)/i',
        'location' => '/(?:location|based in|from)\s*:?\s*([^\n,]+)/i'
    ];

    // Skills database for better detection
    protected $skillsKeywords = [
        'technical' => [
            'programming' => ['php', 'javascript', 'python', 'java', 'c++', 'c#', 'ruby', 'go'],
            'web' => ['html', 'css', 'react', 'vue', 'angular', 'node.js', 'laravel', 'django'],
            'database' => ['mysql', 'postgresql', 'mongodb', 'redis', 'sqlite'],
            'tools' => ['git', 'docker', 'kubernetes', 'jenkins', 'aws', 'azure'],
            'office' => ['ms office', 'excel', 'word', 'powerpoint', 'outlook']
        ],
        'soft' => [
            'communication', 'leadership', 'teamwork', 'problem solving', 'analytical',
            'time management', 'project management', 'customer service'
        ]
    ];

    public function parse(UploadedFile $file)
    {
        try {
            // Validate file
            if (!$file->isValid()) {
                throw new Exception("Invalid file upload");
            }

            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, $this->supportedFormats)) {
                throw new Exception("Unsupported file format. Please upload PDF, DOC, DOCX, TXT, or RTF files.");
            }

            // Check file size (max 10MB)
            if ($file->getSize() > 10 * 1024 * 1024) {
                throw new Exception("File size too large. Maximum allowed size is 10MB.");
            }

            // Extract text based on file type
            $text = $this->extractText($file, $extension);

            if (empty($text)) {
                throw new Exception("Could not extract text from the file. The file might be corrupted or image-based.");
            }

            // Clean the extracted text with robust encoding handling
            $cleanText = $this->cleanText($text);

            if (empty($cleanText)) {
                throw new Exception("Text extraction resulted in empty content after cleaning.");
            }

            // Parse the CV data
            $parsedData = $this->parseCV($cleanText);

            // Calculate confidence score
            $confidenceScore = $this->calculateConfidenceScore($parsedData, $cleanText);

            return [
                'success' => true,
                'message' => 'CV parsed successfully',
                'data' => array_merge($parsedData, [
                    'metadata' => [
                        'confidence_score' => $confidenceScore,
                        'parsed_at' => now()->toISOString(),
                        'file_size' => $file->getSize(),
                        'file_type' => $extension,
                        'text_length' => strlen($cleanText),
                        'original_filename' => $file->getClientOriginalName()
                    ]
                ])
            ];

        } catch (Exception $e) {
            Log::error('CV Parsing error: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName() ?? 'unknown',
                'size' => $file->getSize() ?? 0,
                'mime_type' => $file->getMimeType() ?? 'unknown',
                'error' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to parse CV: ' . $e->getMessage(),
                'data' => null,
                'debug' => [
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'original_name' => $file->getClientOriginalName()
                ]
            ];
        }
    }

    protected function extractText(UploadedFile $file, $extension)
    {
        $path = $file->getRealPath();
        $text = '';

        try {
            switch ($extension) {
                case 'pdf':
                    $text = $this->extractPdfText($path);
                    break;
                case 'doc':
                case 'docx':
                    $text = $this->extractWordText($path);
                    break;
                case 'txt':
                case 'rtf':
                    $text = $this->extractPlainText($path);
                    break;
            }
        } catch (Exception $e) {
            Log::warning("Text extraction failed for {$extension}", ['error' => $e->getMessage()]);
            throw new Exception("Could not extract text from {$extension} file: " . $e->getMessage());
        }

        if (empty(trim($text))) {
            throw new Exception("Extracted text is empty. The file might be image-based or corrupted.");
        }

        return $text;
    }

    protected function extractPdfText($path)
    {
        try {
            $text = Pdf::getText($path);

            // Additional PDF-specific encoding fixes
            $text = str_replace([
                chr(0xC2).chr(0xA0), // Non-breaking space
                chr(0xE2).chr(0x80).chr(0x93), // En dash
                chr(0xE2).chr(0x80).chr(0x94), // Em dash
            ], [' ', '-', '-'], $text);

            return $text;
        } catch (Exception $e) {
            throw new Exception("PDF text extraction failed: " . $e->getMessage());
        }
    }

    protected function extractWordText($path)
    {
        try {
            $phpWord = WordParser::load($path);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $elementText = $this->extractElementText($element);
                    if (!empty($elementText)) {
                        $text .= $elementText . "\n";
                    }
                }
            }

            return $text;
        } catch (Exception $e) {
            throw new Exception("Word document text extraction failed: " . $e->getMessage());
        }
    }

    protected function extractElementText($element)
    {
        $text = '';

        try {
            if (method_exists($element, 'getElements')) {
                foreach ($element->getElements() as $childElement) {
                    if ($childElement instanceof Run) {
                        $runText = $childElement->getText();
                        if (is_string($runText)) {
                            $text .= $runText . ' ';
                        }
                    } elseif (method_exists($childElement, 'getText')) {
                        $childText = $childElement->getText();
                        if (is_string($childText)) {
                            $text .= $childText . ' ';
                        }
                    }
                }
            } elseif (method_exists($element, 'getText')) {
                $elementText = $element->getText();
                if (is_string($elementText)) {
                    $text = $elementText;
                }
            }
        } catch (Exception $e) {
            // Skip problematic elements
            Log::debug('Skipped problematic element during text extraction', ['error' => $e->getMessage()]);
        }

        return trim($text);
    }

    protected function extractPlainText($path)
    {
        try {
            if (!is_readable($path)) {
                throw new Exception("File is not readable");
            }

            $text = file_get_contents($path);

            if ($text === false) {
                throw new Exception("Could not read file contents");
            }

            return $text;
        } catch (Exception $e) {
            throw new Exception("Plain text extraction failed: " . $e->getMessage());
        }
    }

    protected function cleanText($text)
    {
        try {
            // Handle various encoding issues
            $text = $this->fixEncoding($text);

            // Normalize line breaks
            $text = preg_replace('/\r\n|\r/', "\n", $text);

            // Remove control characters except tabs and newlines
            $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

            // Remove excessive whitespace
            $text = preg_replace('/[ \t]+/', ' ', $text);
            $text = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $text);

            // Fix common PDF extraction issues
            $text = preg_replace('/([a-z])([A-Z])/', '$1 $2', $text);

            // Remove any remaining malformed UTF-8 sequences
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

            return trim($text);

        } catch (Exception $e) {
            Log::warning('Text cleaning failed, using fallback method', ['error' => $e->getMessage()]);
            return $this->fallbackTextCleaning($text);
        }
    }

    protected function fixEncoding($text)
    {
        // Detect encoding with multiple fallbacks
        $encodings = ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'];
        $detectedEncoding = null;

        foreach ($encodings as $encoding) {
            if (mb_check_encoding($text, $encoding)) {
                $detectedEncoding = $encoding;
                break;
            }
        }

        if ($detectedEncoding && $detectedEncoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $detectedEncoding);
        }

        // Handle specific problematic characters
        $replacements = [
            // Common PDF extraction issues
            "\xC2\xA0" => ' ',  // Non-breaking space
            "\xE2\x80\x93" => '-', // En dash
            "\xE2\x80\x94" => '-', // Em dash
            "\xE2\x80\x98" => "'", // Left single quotation mark
            "\xE2\x80\x99" => "'", // Right single quotation mark
            "\xE2\x80\x9C" => '"', // Left double quotation mark
            "\xE2\x80\x9D" => '"', // Right double quotation mark
            "\xE2\x80\xA2" => '•', // Bullet
            "\xEF\xBF\xBD" => '',  // Replacement character (remove)
        ];

        $text = str_replace(array_keys($replacements), array_values($replacements), $text);

        // Remove any invalid UTF-8 sequences
        $text = filter_var($text, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

        // Final UTF-8 validation and cleaning
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }

        return $text;
    }

    protected function fallbackTextCleaning($text)
    {
        // Very basic fallback cleaning
        $text = preg_replace('/[^\x20-\x7E\n\r\t]/', '', $text); // Keep only ASCII printable + whitespace
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        return trim($text);
    }

    protected function parseCV($text)
    {
        $sections = $this->identifySections($text);

        return [
            'personal_information' => $this->extractPersonalInfo($text, $sections),
            'summary' => $this->extractSummary($text, $sections),
            'experience' => $this->extractExperience($text, $sections),
            'education' => $this->extractEducation($text, $sections),
            'skills' => $this->extractSkills($text, $sections),
            'languages' => $this->extractLanguages($text, $sections),
            'certifications' => $this->extractCertifications($text, $sections),
            'projects' => $this->extractProjects($text, $sections)
        ];
    }

    protected function identifySections($text)
    {
        $lines = explode("\n", $text);
        $sections = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if line is a section header (case-insensitive, exact matching)
            foreach ($this->sectionHeaders as $sectionType => $headers) {
                foreach ($headers as $header) {
                    // More precise matching - line should be primarily the header
                    $lineFormatted = strtolower(preg_replace('/[^a-z\s]/', '', $line));
                    $headerFormatted = strtolower(preg_replace('/[^a-z\s]/', '', $header));

                    if ($lineFormatted === $headerFormatted ||
                        (strpos($lineFormatted, $headerFormatted) !== false && strlen($line) < 80)) {

                        // Additional validation - shouldn't be part of a sentence
                        if (!preg_match('/[.,:;]/', $line) || strtoupper($line) === $line ||
                            preg_match('/^[A-Z][A-Z\s&]+$/', $line)) {
                            $sections[$sectionType] = [
                                'start' => $index,
                                'header' => $line,
                                'content' => []
                            ];
                            break 2;
                        }
                    }
                }
            }
        }

        // Determine section boundaries with better logic
        $sectionTypes = array_keys($sections);
        for ($i = 0; $i < count($sectionTypes); $i++) {
            $currentType = $sectionTypes[$i];
            $startLine = $sections[$currentType]['start'];
            $endLine = isset($sectionTypes[$i + 1]) ? $sections[$sectionTypes[$i + 1]]['start'] : count($lines);

            // Extract content, filtering out the header line and empty lines
            $contentLines = [];
            for ($j = $startLine + 1; $j < $endLine; $j++) {
                $contentLine = trim($lines[$j] ?? '');
                if (!empty($contentLine)) {
                    $contentLines[] = $contentLine;
                }
            }

            $sections[$currentType]['content'] = $contentLines;
        }

        return $sections;
    }

    protected function extractPersonalInfo($text, $sections)
    {
        $personalInfo = [
            'name' => $this->extractName($text),
            'email' => $this->extractEmail($text),
            'phone' => $this->extractPhone($text),
            'address' => $this->extractAddress($text),
            'location' => $this->extractLocation($text),
            'date_of_birth' => $this->extractDateOfBirth($text),
            'nationality' => $this->extractNationality($text),
            'linkedin' => $this->extractLinkedIn($text),
            'website' => $this->extractWebsite($text)
        ];

        return array_filter($personalInfo, function($value) {
            return !is_null($value) && $value !== '';
        });
    }

    protected function extractAddress($text)
    {
        // Look for address patterns - usually at the beginning of CV
        $lines = explode("\n", $text);

        // Check first few lines for address
        for ($i = 0; $i < min(5, count($lines)); $i++) {
            $line = trim($lines[$i]);

            // Address indicators
            if (preg_match('/(#\d+|apt\.?\s*\d+|building|street|road|avenue|block)/i', $line)) {
                return $line;
            }

            // UAE/Dubai address pattern
            if (preg_match('/dubai|uae|sharjah|abu dhabi|emirates/i', $line) && strlen($line) > 15) {
                return $line;
            }
        }

        // Look for explicit address patterns
        if (preg_match('/(?:address|addr)\s*:?\s*([^\n]+)/i', $text, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    protected function extractDateOfBirth($text)
    {
        // Look for date of birth patterns
        $patterns = [
            '/date\s*of\s*birth\s*:?\s*(\d{1,2}\/\d{1,2}\/\d{4})/i',
            '/(?:dob|d\.o\.b\.?)\s*:?\s*(\d{1,2}\/\d{1,2}\/\d{4})/i',
            '/born\s*:?\s*(\d{1,2}\/\d{1,2}\/\d{4})/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    protected function extractNationality($text)
    {
        // Look for nationality patterns
        if (preg_match('/nationality\s*:?\s*([A-Za-z]+)/i', $text, $matches)) {
            return ucfirst(strtolower($matches[1]));
        }

        return null;
    }

    protected function extractName($text)
    {
        $lines = explode("\n", $text);

        // Remove empty lines from the beginning
        $lines = array_values(array_filter($lines, function($line) {
            return !empty(trim($line));
        }));

        if (empty($lines)) {
            return null;
        }

        // Strategy 1: Check the very first line (most common for CVs)
        $firstLine = trim($lines[0]);

        // Handle case where name is at the end (like "JOYAL SANTHMAYOR")
        $lastLine = trim(end($lines));
        if ($this->isValidName($lastLine) && !$this->isNonNameLine($lastLine)) {
            return $this->cleanName($lastLine);
        }

        if ($this->isValidName($firstLine) && !$this->isNonNameLine($firstLine)) {
            return $this->cleanName($firstLine);
        }

        // Strategy 2: Look for name in personal profile section
        if (preg_match('/name\s*:?\s*([A-Z][A-Z\s]{5,50})/i', $text, $matches)) {
            $candidate = trim($matches[1]);
            if ($this->isValidName($candidate)) {
                return $this->cleanName($candidate);
            }
        }

        // Strategy 3: Check first 5 lines for a valid name
        for ($i = 0; $i < min(5, count($lines)); $i++) {
            $line = trim($lines[$i]);

            // Skip obvious non-name lines
            if ($this->isNonNameLine($line)) {
                continue;
            }

            if ($this->isValidName($line)) {
                return $this->cleanName($line);
            }
        }

        // Strategy 4: Advanced pattern matching as fallback
        foreach ($this->namePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $candidate = trim($matches[1]);
                if ($this->isValidName($candidate) && !$this->isNonNameLine($candidate)) {
                    return $this->cleanName($candidate);
                }
            }
        }

        return null;
    }

    protected function isNonNameLine($text)
    {
        $text = strtolower(trim($text));

        // Common non-name patterns at the start of CVs
        $nonNamePatterns = [
            'resume', 'cv', 'curriculum vitae', 'personal information',
            'contact information', 'profile', 'about me', 'summary',
            'objective', 'career objective', 'professional summary',
            'email', 'phone', 'mobile', 'address', 'location',
            'experience', 'education', 'skills', 'qualifications',
            'work experience', 'employment', 'job', 'position',
            // Job titles that might appear early
            'manager', 'director', 'coordinator', 'specialist',
            'analyst', 'consultant', 'representative', 'officer',
            'assistant', 'supervisor', 'lead', 'senior', 'junior'
        ];

        foreach ($nonNamePatterns as $pattern) {
            if (strpos($text, $pattern) !== false) {
                return true;
            }
        }

        // Check for email patterns
        if (strpos($text, '@') !== false) {
            return true;
        }

        // Check for phone patterns
        if (preg_match('/\d{3,}/', $text)) {
            return true;
        }

        // Check for address patterns
        if (preg_match('/\b(street|st|avenue|ave|road|rd|apt|apartment|suite|building)\b/i', $text)) {
            return true;
        }

        return false;
    }

    protected function isValidName($text)
    {
        if (empty($text)) {
            return false;
        }

        $text = trim($text);

        // Length check
        if (strlen($text) < 3 || strlen($text) > 80) {
            return false;
        }

        // Skip if it's obviously not a name
        if ($this->isNonNameLine($text)) {
            return false;
        }

        // Must not contain email, phone, or address indicators
        if (preg_match('/@|www\.|http|\.com|\.org|\.net|\d{3,}/', $text)) {
            return false;
        }

        // For ALL CAPS names (common in CVs)
        if (ctype_upper(str_replace([' ', '.', "'"], '', $text))) {
            $words = preg_split('/\s+/', trim($text));
            // Should have 2-5 words for a full name
            if (count($words) >= 2 && count($words) <= 5) {
                // Each word should be mostly alphabetic
                foreach ($words as $word) {
                    if (!preg_match('/^[A-Z][A-Z\'\.]*$/', $word)) {
                        return false;
                    }
                }
                return true;
            }
        }

        // For proper case names
        $words = preg_split('/\s+/', trim($text));
        if (count($words) >= 2 && count($words) <= 5) {
            foreach ($words as $word) {
                // Each word should start with capital and be mostly alphabetic
                if (!preg_match('/^[A-Z][a-z\'\.]*$/', $word) && !preg_match('/^[A-Z]\.?$/', $word)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    protected function cleanName($name)
    {
        if (!$name) return null;

        $name = trim($name);

        // Handle ALL CAPS names
        if (ctype_upper(str_replace([' ', '.', "'"], '', $name))) {
            // Convert to proper case
            $words = explode(' ', $name);
            $cleanWords = array_map(function($word) {
                if (strlen($word) === 1 || (strlen($word) === 2 && substr($word, -1) === '.')) {
                    return $word; // Keep initials as-is
                }
                return ucfirst(strtolower($word));
            }, $words);
            $name = implode(' ', $cleanWords);
        }

        // Clean up spacing
        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name);

        return $name;
    }

    // protected function extractEmail($text)
    // {
    //     if (preg_match($this->contactPatterns['email'], $text, $matches)) {
    //         return strtolower($matches[0]);
    //     }
    //     return null;
    // }

    protected function extractPhone($text)
    {
        $phonePatterns = [
            '/(?:mobile|cell|phone|contact)\s*(?:no\.?|number)?\s*:?\s*(\+?[\d\s\-\(\)\.]{8,20})/i',
            '/(\+971\s*\d{2,3}\s*\d{6,7})/i', // UAE format
            '/(\+\d{1,4}[-.\s]?\d{2,4}[-.\s]?\d{6,8})/i', // International format
            '/(\d{10,11})/i' // Simple 10-11 digit numbers
        ];

        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $phone = preg_replace('/[^\d+]/', '', $matches[1]);
                if (strlen($phone) >= 7 && strlen($phone) <= 15) {
                    return $matches[1]; // Return original format
                }
            }
        }

        return null;
    }

    protected function extractEmail($text)
    {
        // More comprehensive email patterns
        $emailPatterns = [
            '/(?:email|e-mail|mail)\s*(?:address)?\s*:?\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i',
            '/\b([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})\b/',
            '/([a-zA-Z0-9]+@[a-zA-Z0-9]+[a-zA-Z0-9.]*[a-zA-Z]{2,})/i' // For emails without dots before @
        ];
        if (preg_match($this->contactPatterns['email'], $text, $matches)) {
            return strtolower($matches[0]);
        }

        foreach ($emailPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $email = strtolower(trim($matches[1]));
                // Basic email validation
                if (strpos($email, '@') !== false && strpos($email, '.') !== false) {
                    return $email;
                }
            }
        }

        return null;
    }

    protected function extractAddress($text)
    {
        if (preg_match($this->contactPatterns['address'], $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    protected function extractLocation($text)
    {
        // Look for location patterns
        $locationPatterns = [
            '/(?:address|location):\s*([^,\n]+(?:,\s*[^,\n]+)*)/i',
            '/([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*,\s*[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/i' // City, Country
        ];

        foreach ($locationPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    protected function extractLinkedIn($text)
    {
        if (preg_match('/linkedin\.com\/in\/[a-zA-Z0-9_-]+/', $text, $matches)) {
            return 'https://' . $matches[0];
        }
        return null;
    }

    protected function extractWebsite($text)
    {
        if (preg_match('/(?:https?:\/\/)?(?:www\.)?[a-zA-Z0-9-]+\.[a-zA-Z]{2,}(?:\/[^\s]*)?/', $text, $matches)) {
            $url = $matches[0];
            if (!preg_match('/^https?:\/\//', $url)) {
                $url = 'https://' . $url;
            }
            // Exclude email domains and social media
            if (!preg_match('/(gmail|yahoo|hotmail|outlook|linkedin|facebook|twitter)\.com/', $url)) {
                return $url;
            }
        }
        return null;
    }

    protected function extractSummary($text, $sections)
    {
        if (isset($sections['objective'])) {
            $content = implode("\n", $sections['objective']['content']);
            return trim($content) ?: null;
        }
        return null;
    }

    protected function extractExperience($text, $sections)
    {
        if (!isset($sections['experience'])) {
            return [];
        }

        $content = implode("\n", $sections['experience']['content']);
        $experiences = [];

        // Better block splitting - look for company/organization patterns
        $blocks = $this->intelligentExperienceBlocks($content);

        foreach ($blocks as $block) {
            $experience = $this->parseExperienceBlockImproved($block);
            if (!empty($experience) && !empty($experience['title'])) {
                $experiences[] = $experience;
            }
        }

        return $experiences;
    }

    protected function intelligentExperienceBlocks($content)
    {
        $lines = explode("\n", $content);
        $blocks = [];
        $currentBlock = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Check if this line starts a new job entry
            if ($this->isNewJobEntry($line)) {
                // Save previous block
                if (!empty($currentBlock)) {
                    $blocks[] = implode("\n", $currentBlock);
                }
                // Start new block
                $currentBlock = [$line];
            } else {
                $currentBlock[] = $line;
            }
        }

        // Add the last block
        if (!empty($currentBlock)) {
            $blocks[] = implode("\n", $currentBlock);
        }

        return $blocks;
    }

    protected function isNewJobEntry($line)
    {
        // Check for organization names or job titles that indicate new entries
        $indicators = [
            // Company patterns
            '/\b(ltd|llc|inc|corp|corporation|company|bank|university|college|institute)\b/i',
            // Job title patterns followed by dates
            '/^[A-Z][A-Za-z\s&\/]+(manager|executive|analyst|specialist|officer|assistant|coordinator|director|representative|technician)\b/i',
            // Date patterns that often indicate job periods
            '/\b(january|february|march|april|may|june|july|august|september|october|november|december)\s+\d{4}/i',
            '/\d{1,2}(th|st|nd|rd)?\s+(january|february|march|april|may|june|july|august|september|october|november|december)\s+\d{4}/i'
        ];

        foreach ($indicators as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    protected function parseExperienceBlockImproved($block)
    {
        $lines = array_filter(explode("\n", $block), function($line) {
            return !empty(trim($line));
        });

        if (empty($lines)) return null;

        $experience = [
            'title' => '',
            'company' => '',
            'location' => '',
            'start_date' => '',
            'end_date' => '',
            'duration' => '',
            'description' => []
        ];

        $foundTitle = false;
        $foundCompany = false;
        $foundDates = false;

        foreach ($lines as $line) {
            $line = trim($line);

            // Extract various date formats
            if (!$foundDates) {
                // Format: "01-06-2019 / PRESENT"
                if (preg_match('/(\d{2}-\d{2}-\d{4})\s*\/\s*(present|current|\d{2}-\d{2}-\d{4})/i', $line, $matches)) {
                    $experience['start_date'] = $matches[1];
                    $experience['end_date'] = ucfirst(strtolower($matches[2]));
                    $foundDates = true;
                    continue;
                }
                // Format: "2016To 2017" or "2017 To 2018"
                if (preg_match('/(\d{4})\s*to\s*(\d{4})/i', $line, $matches)) {
                    $experience['start_date'] = $matches[1];
                    $experience['end_date'] = $matches[2];
                    $foundDates = true;
                    continue;
                }
                // Format: "06-5-17 / 03-16-18"
                if (preg_match('/(\d{1,2}-\d{1,2}-\d{2,4})\s*\/\s*(\d{1,2}-\d{1,2}-\d{2,4})/i', $line, $matches)) {
                    $experience['start_date'] = $matches[1];
                    $experience['end_date'] = $matches[2];
                    $foundDates = true;
                    continue;
                }
            }

            // Skip lines that are just dates
            if (preg_match('/^\d{2}-\d{2}-\d{4}|^\d{4}\s*to\s*\d{4}$/i', $line)) {
                continue;
            }

            // Extract job title (usually ALL CAPS or follows certain patterns)
            if (!$foundTitle && $this->looksLikeJobTitle($line)) {
                $experience['title'] = $line;
                $foundTitle = true;
                continue;
            }

            // Extract company name
            if (!$foundCompany) {
                // Check if it's a company (has company indicators or comes after title)
                if ($this->looksLikeCompany($line) || ($foundTitle && !preg_match('/^[•·\-\*➢]/', $line))) {
                    // Check if it includes location
                    if (preg_match('/^(.+?)\s+(Bacolod|Dubai|Philippines|UAE|India|Hyderabad|[A-Z][a-z]+,?\s*[A-Z][a-z]+)$/i', $line, $matches)) {
                        $experience['company'] = trim($matches[1]);
                        $experience['location'] = trim($matches[2]);
                    } else {
                        $experience['company'] = $line;
                    }
                    $foundCompany = true;
                    continue;
                }
            }

            // Extract location if not found yet
            if (empty($experience['location']) && preg_match('/^(Bacolod|Dubai|Philippines|UAE|India|Hyderabad),?\s*([A-Za-z\s]*)/i', $line)) {
                $experience['location'] = trim($line);
                continue;
            }

            // Everything else is description (bullet points or regular text)
            if ($foundTitle || $foundCompany) {
                if (preg_match('/^[•·\-\*➢]\s*(.+)/', $line, $matches)) {
                    $desc = trim($matches[1]);
                    if (strlen($desc) > 10) {
                        $experience['description'][] = $desc;
                    }
                } else if (strlen($line) > 15 && !$this->looksLikeJobTitle($line) && !$this->looksLikeCompany($line)) {
                    $experience['description'][] = $line;
                }
            }
        }

        // Clean and return
        return array_filter($experience, function($value) {
            return !empty($value);
        });
    }

    protected function looksLikeCompany($line)
    {
        $companyIndicators = [
            'ltd', 'llc', 'inc', 'corp', 'corporation', 'company', 'bank', 'university',
            'college', 'institute', 'solutions', 'technologies', 'systems', 'services',
            'electronics', 'telecommunications', 'ptv', 'store'
        ];

        $lineLower = strtolower($line);
        foreach ($companyIndicators as $indicator) {
            if (strpos($lineLower, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function parseExperienceBlock($block)
    {
        $lines = array_filter(explode("\n", $block), function($line) {
            return !empty(trim($line));
        });

        if (empty($lines)) return null;

        $experience = [
            'title' => '',
            'company' => '',
            'location' => '',
            'start_date' => '',
            'end_date' => '',
            'description' => [],
            'achievements' => []
        ];

        $dateFound = false;
        $titleFound = false;
        $companyFound = false;

        foreach ($lines as $index => $line) {
            $line = trim($line);

            // Extract dates first (priority)
            if (!$dateFound && preg_match('/(\d{2}-\d{2}-\d{4})\s*\/?\s*(\d{2}-\d{2}-\d{4}|present)/i', $line, $matches)) {
                $experience['start_date'] = $matches[1];
                $experience['end_date'] = strtolower($matches[2]) === 'present' ? 'Present' : $matches[2];
                $dateFound = true;
                continue;
            }

            // Skip date-only lines
            if (preg_match('/^\d{2}-\d{2}-\d{4}/', $line)) {
                continue;
            }

            // Extract job title (usually first non-date line in ALL CAPS or title case)
            if (!$titleFound && $this->looksLikeJobTitle($line)) {
                $experience['title'] = $line;
                $titleFound = true;
                continue;
            }

            // Extract company (usually after job title, often has location)
            if ($titleFound && !$companyFound && !preg_match('/^[•·\-\*]/', $line)) {
                // Check if this line contains location info
                if (preg_match('/^(.+?)\s+([A-Z][a-z]+,?\s*[A-Z][a-z]+)$/', $line, $matches)) {
                    $experience['company'] = trim($matches[1]);
                    $experience['location'] = trim($matches[2]);
                } else {
                    $experience['company'] = $line;
                }
                $companyFound = true;
                continue;
            }

            // Everything else is description/achievements
            if ($titleFound || $companyFound) {
                if (preg_match('/^[•·\-\*]/', $line)) {
                    $description = preg_replace('/^[•·\-\*]\s*/', '', $line);
                    $experience['description'][] = trim($description);
                }
            }
        }

        // Clean up empty values
        return array_filter($experience, function($value) {
            return !empty($value);
        });
    }

    protected function looksLikeJobTitle($line)
    {
        // Job titles are often in ALL CAPS or Title Case
        $line = trim($line);

        // Check for ALL CAPS job titles
        if (ctype_upper(str_replace([' ', '/', '-', '&'], '', $line))) {
            return true;
        }

        // Check for common job title words
        $jobTitleWords = [
            'manager', 'director', 'analyst', 'specialist', 'coordinator',
            'representative', 'agent', 'officer', 'assistant', 'supervisor',
            'lead', 'senior', 'junior', 'developer', 'engineer', 'consultant',
            'administrator', 'controller', 'executive', 'associate'
        ];

        $lineLower = strtolower($line);
        foreach ($jobTitleWords as $word) {
            if (strpos($lineLower, $word) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function extractEducation($text, $sections)
    {
        if (!isset($sections['education'])) {
            return [];
        }

        $content = implode("\n", $sections['education']['content']);
        $educations = [];

        // Better education parsing
        $lines = array_filter(explode("\n", $content), function($line) {
            return !empty(trim($line));
        });

        $currentEducation = null;

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip section headers
            if (preg_match('/^(education|qualification)/i', $line)) {
                continue;
            }

            // Look for degree patterns
            if ($this->isDegreeLine($line)) {
                // Save previous education
                if ($currentEducation && !empty($currentEducation['degree'])) {
                    $educations[] = $currentEducation;
                }

                // Start new education entry
                $currentEducation = [
                    'degree' => $line,
                    'institution' => '',
                    'year' => '',
                    'location' => '',
                    'grade' => ''
                ];
            }
            // Look for institution
            else if ($currentEducation && empty($currentEducation['institution']) && $this->isInstitutionLine($line)) {
                $currentEducation['institution'] = $line;
            }
            // Look for year/date
            else if ($currentEducation && preg_match('/\b(19|20)\d{2}\b/', $line, $matches)) {
                if (empty($currentEducation['year'])) {
                    $currentEducation['year'] = $matches[0];
                }
            }
            // Look for grade/percentage
            else if ($currentEducation && preg_match('/(\d+(?:\.\d+)?)\s*(%|cgpa|gpa)/i', $line, $matches)) {
                $currentEducation['grade'] = $matches[1] . $matches[2];
            }
            // Look for location
            else if ($currentEducation && empty($currentEducation['location']) && preg_match('/^[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*,?\s+[A-Z][a-z]+/i', $line)) {
                $currentEducation['location'] = $line;
            }
        }

        // Add the last education entry
        if ($currentEducation && !empty($currentEducation['degree'])) {
            $educations[] = $currentEducation;
        }

        return array_filter($educations, function($edu) {
            return !empty($edu['degree']) || !empty($edu['institution']);
        });
    }

    protected function isDegreeLine($line)
    {
        $degreePhrases = [
            'bachelor', 'master', 'phd', 'doctorate', 'diploma', 'certificate',
            'bsc', 'msc', 'mba', 'ba', 'bs', 'ma', 'md', 'jd', 'btech', 'mtech',
            'bca', 'mca', 'be', 'me', 'bcom', 'mcom', 'llb', 'llm', 'bfa', 'mfa',
            'bed', 'med', 'pre-university', 'secondary', 'sslc'
        ];

        $lineLower = strtolower($line);
        foreach ($degreePhrases as $phrase) {
            if (strpos($lineLower, $phrase) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function isInstitutionLine($line)
    {
        $institutionPhrases = [
            'university', 'college', 'institute', 'school', 'academy', 'polytechnic',
            'technological university', 'state university', 'business school',
            'management college', 'education board', 'examination board'
        ];

        $lineLower = strtolower($line);
        foreach ($institutionPhrases as $phrase) {
            if (strpos($lineLower, $phrase) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function extractSkills($text, $sections)
    {
        $skills = [];

        // Look in skills section first
        if (isset($sections['skills'])) {
            $content = implode("\n", $sections['skills']['content']);
            $skills = array_merge($skills, $this->parseSkillsFromContent($content));
        }

        // Also scan entire document for technical skills
        $skills = array_merge($skills, $this->scanForTechnicalSkills($text));

        // Remove duplicates and categorize
        return $this->categorizeSkills(array_unique($skills));
    }

    protected function parseSkillsFromContent($content)
    {
        $skills = [];
        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Split by common separators
            $items = preg_split('/[,;|•·\-]/', $line);
            foreach ($items as $item) {
                $skill = trim($item);
                if (strlen($skill) > 1 && strlen($skill) < 50) {
                    $skills[] = $skill;
                }
            }
        }

        return $skills;
    }

    protected function scanForTechnicalSkills($text)
    {
        $foundSkills = [];
        $text = strtolower($text);

        foreach ($this->skillsKeywords['technical'] as $category => $skills) {
            foreach ($skills as $skill) {
                if (strpos($text, strtolower($skill)) !== false) {
                    $foundSkills[] = $skill;
                }
            }
        }

        return $foundSkills;
    }

    protected function categorizeSkills($skills)
    {
        $categorized = [
            'technical' => [],
            'soft' => [],
            'general' => []
        ];

        foreach ($skills as $skill) {
            $skillLower = strtolower($skill);
            $category = 'general';

            // Check technical skills
            foreach ($this->skillsKeywords['technical'] as $techCategory => $techSkills) {
                foreach ($techSkills as $techSkill) {
                    if (stripos($skill, $techSkill) !== false) {
                        $category = 'technical';
                        break 2;
                    }
                }
            }

            // Check soft skills
            if ($category === 'general') {
                foreach ($this->skillsKeywords['soft'] as $softSkill) {
                    if (stripos($skill, $softSkill) !== false) {
                        $category = 'soft';
                        break;
                    }
                }
            }

            $categorized[$category][] = $skill;
        }

        // Remove empty categories and return as array
        return array_filter($categorized, function($categorySkills) {
            return !empty($categorySkills);
        });
    }

    protected function extractCertifications($text, $sections)
    {
        $certifications = [];

        // Method 1: Look in certifications section
        if (isset($sections['certifications'])) {
            $content = implode("\n", $sections['certifications']['content']);
            $certifications = array_merge($certifications, $this->parseCertificationsFromContent($content));
        }

        // Method 2: Look for certification patterns throughout document
        $certifications = array_merge($certifications, $this->searchForCertificationPatterns($text));

        // Remove duplicates and empty entries
        return array_values(array_unique(array_filter($certifications)));
    }

    protected function parseCertificationsFromContent($content)
    {
        $certifications = [];
        $lines = array_filter(explode("\n", $content), function($line) {
            return !empty(trim($line));
        });

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip section headers and decorative lines
            if (preg_match('/^(certifications?|training|seminars?|_{5,}|&\s*awards?|achievements?)/i', $line)) {
                continue;
            }

            // Skip personal profile information
            if (preg_match('/^(date\s*of\s*birth|marital\s*status|nationality|passport)/i', $line)) {
                continue;
            }

            // Skip single characters, dates, or very short lines
            if (strlen($line) < 10 || preg_match('/^[\d\s:\/\-,()]+$/', $line)) {
                continue;
            }

            // Clean bullet points
            $line = preg_replace('/^[•·\-\*\s➢]*/', '', $line);
            $line = trim($line);

            // Valid certification should be meaningful text
            if (strlen($line) >= 10 && strlen($line) < 200 && !preg_match('/^(best|top|awarded)/i', $line)) {
                $certifications[] = $line;
            }
        }

        return $certifications;
    }

    protected function searchForCertificationPatterns($text)
    {
        $certifications = [];

        // Common certification patterns
        $patterns = [
            '/(?:certification|certificate|certified)\s+(?:in|from|of)\s+([^\n\.,]+)/i',
            '/([A-Z]{2,10})\s+certification/i', // IRDA certification, AMFI certification
            '/certified\s+([^\n\.,]+)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[1] as $match) {
                    $cert = trim($match);
                    if (strlen($cert) > 3 && strlen($cert) < 100) {
                        $certifications[] = $cert;
                    }
                }
            }
        }

        return $certifications;
    }

    protected function extractProjects($text, $sections)
    {
        $projects = [];

        // Method 1: Look in projects section
        if (isset($sections['projects'])) {
            $content = implode("\n", $sections['projects']['content']);
            $projects = array_merge($projects, $this->parseProjectsFromContent($content));
        }

        // Method 2: Search for project patterns throughout document
        $projects = array_merge($projects, $this->searchForProjectPatterns($text));

        return array_values(array_filter($projects));
    }

    protected function parseProjectsFromContent($content)
    {
        $projects = [];
        $lines = array_filter(explode("\n", $content), function($line) {
            return !empty(trim($line));
        });

        $currentProject = null;
        $skipNext = false;

        foreach ($lines as $index => $line) {
            $line = trim($line);

            // Skip section headers and decorative lines
            if (preg_match('/^(projects?|_{5,}|certifications?)/i', $line) || $skipNext) {
                $skipNext = false;
                continue;
            }

            // Skip certification entries that got mixed in
            if (preg_match('/(certification|certificate)\s+(from|of)/i', $line)) {
                continue;
            }

            // Look for project type indicators
            if (preg_match('/^(internship|industrial|management|marketing|finance)\s+project/i', $line)) {
                // Save previous project
                if ($currentProject && !empty($currentProject['title'])) {
                    $projects[] = $currentProject;
                }

                // Start new project
                $currentProject = [
                    'title' => $line,
                    'description' => '',
                    'duration' => '',
                    'type' => ''
                ];

                // Extract duration if on same line
                if (preg_match('/(\d+\s+(?:months?|days?|weeks?|years?))/i', $line, $matches)) {
                    $currentProject['duration'] = $matches[1];
                }
            }
            // Look for project titles in quotes or after "Project on"
            else if (preg_match('/project\s+on\s+(?:the\s+)?title\s*[\'"]?([^\'"\n]+)[\'"]?/i', $line, $matches)) {
                if ($currentProject) {
                    $currentProject['title'] = trim($matches[1]);
                }
            }
            // Additional project description
            else if ($currentProject && strlen($line) > 20) {
                if (empty($currentProject['description'])) {
                    $currentProject['description'] = $line;
                } else {
                    $currentProject['description'] .= ' ' . $line;
                }
            }
            // Standalone project title (if reasonable length)
            else if (!$currentProject && strlen($line) > 15 && strlen($line) < 150 &&
                     !preg_match('/^(best|top|awarded|date|marital)/i', $line)) {
                $currentProject = [
                    'title' => $line,
                    'description' => '',
                    'duration' => '',
                    'type' => ''
                ];
            }
        }

        // Add the last project
        if ($currentProject && !empty($currentProject['title']) && strlen($currentProject['title']) > 10) {
            $projects[] = $currentProject;
        }

        return array_filter($projects, function($project) {
            return !empty($project['title']) && strlen($project['title']) > 10;
        });
    }

    protected function searchForProjectPatterns($text)
    {
        $projects = [];

        // Look for "Project on" or "Internship Project" patterns
        $patterns = [
            '/(?:internship\s+)?project\s+(?:on\s+(?:the\s+)?title\s+)?"?([^"\n]+)"?/i',
            '/(?:management|industrial|marketing|finance)\s+project[^\n]*?([^\n]+)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[1] as $match) {
                    $title = trim($match);
                    if (strlen($title) > 10 && strlen($title) < 200) {
                        $projects[] = [
                            'title' => $title,
                            'description' => '',
                            'duration' => '',
                            'technologies' => []
                        ];
                    }
                }
            }
        }

        return $projects;
    }

    protected function extractLanguages($text, $sections)
    {
        $languages = [];

        // Method 1: Look in languages section
        if (isset($sections['languages'])) {
            $content = implode("\n", $sections['languages']['content']);
            $languages = array_merge($languages, $this->parseLanguagesFromContent($content));
        }

        // Method 2: Look in personal section for languages
        if (isset($sections['personal'])) {
            $content = implode("\n", $sections['personal']['content']);
            $personalLanguages = $this->extractLanguagesFromPersonal($content);
            $languages = array_merge($languages, $personalLanguages);
        }

        // Method 3: Search entire document for language patterns
        $documentLanguages = $this->searchForLanguagePatterns($text);
        $languages = array_merge($languages, $documentLanguages);

        // Remove duplicates and clean
        return array_values(array_unique(array_filter($languages)));
    }

    protected function parseLanguagesFromContent($content)
    {
        $languages = [];
        $lines = array_filter(explode("\n", $content), function($line) {
            return !empty(trim($line));
        });

        foreach ($lines as $line) {
            $line = trim($line);

            // Pattern: "English, Hindi, Kannada, and Konkani"
            if (preg_match('/(?:languages?\s*known?\s*:?\s*|speak\s*:?\s*)(.+)/i', $line, $matches)) {
                $langString = $matches[1];
                $langs = preg_split('/[,;&]/', $langString);
                foreach ($langs as $lang) {
                    $lang = trim(str_replace(['and', 'or'], '', $lang));
                    if (strlen($lang) > 2 && $this->isValidLanguage($lang)) {
                        $languages[] = ucfirst(strtolower($lang));
                    }
                }
            }
        }

        return $languages;
    }

    protected function extractLanguagesFromPersonal($content)
    {
        $languages = [];

        // Look for "Languages Known" pattern
        if (preg_match('/languages?\s*known?\s*:?\s*([^\n]+)/i', $content, $matches)) {
            $langString = $matches[1];
            $langs = preg_split('/[,;&]/', $langString);
            foreach ($langs as $lang) {
                $lang = trim(str_replace(['and', 'or', '&'], '', $lang));
                if (strlen($lang) > 2 && $this->isValidLanguage($lang)) {
                    $languages[] = ucfirst(strtolower($lang));
                }
            }
        }

        return $languages;
    }

    protected function searchForLanguagePatterns($text)
    {
        $languages = [];
        $commonLanguages = [
            'english', 'hindi', 'arabic', 'urdu', 'tamil', 'malayalam', 'kannada', 'konkani',
            'spanish', 'french', 'german', 'chinese', 'japanese', 'korean', 'russian',
            'portuguese', 'italian', 'dutch', 'bengali', 'punjabi', 'gujarati', 'marathi'
        ];

        // Look for language patterns in text
        if (preg_match('/languages?\s*(?:known?|spoken?)?\s*:?\s*([^\n\.]+)/i', $text, $matches)) {
            $langString = strtolower($matches[1]);
            foreach ($commonLanguages as $lang) {
                if (strpos($langString, $lang) !== false) {
                    $languages[] = ucfirst($lang);
                }
            }
        }

        return array_unique($languages);
    }

    protected function isValidLanguage($lang)
    {
        $commonLanguages = [
            'english', 'hindi', 'arabic', 'urdu', 'tamil', 'malayalam', 'kannada', 'konkani',
            'spanish', 'french', 'german', 'chinese', 'japanese', 'korean', 'russian',
            'portuguese', 'italian', 'dutch', 'bengali', 'punjabi', 'gujarati', 'marathi'
        ];

        return in_array(strtolower($lang), $commonLanguages);
    }

    protected function extractAwards($text, $sections)
    {
        return [];
    }

    protected function extractReferences($text, $sections)
    {
        if (!isset($sections['references'])) {
            return [];
        }

        // Basic implementation
        return [];
    }

    protected function extractInterests($text, $sections)
    {
        return [];
    }

    protected function calculateConfidenceScore($data, $text)
    {
        $score = 0;
        $maxScore = 100;

        // Name extraction (25 points)
        if (!empty($data['personal_information']['name'])) {
            $score += 25;
        }

        // Contact info (25 points)
        if (!empty($data['personal_information']['email'])) {
            $score += 15;
        }
        if (!empty($data['personal_information']['phone'])) {
            $score += 10;
        }

        // Experience (25 points)
        if (!empty($data['experience'])) {
            $score += min(count($data['experience']) * 8, 25);
        }

        // Education (15 points)
        if (!empty($data['education'])) {
            $score += min(count($data['education']) * 8, 15);
        }

        // Skills (10 points)
        if (!empty($data['skills'])) {
            if (is_array($data['skills'])) {
                $totalSkills = 0;
                foreach ($data['skills'] as $category => $skills) {
                    if (is_array($skills)) {
                        $totalSkills += count($skills);
                    }
                }
                $score += min($totalSkills * 2, 10);
            }
        }

        return round(min($score, $maxScore), 2);
    }

    // Remove unused methods
    protected function extractAwards($text, $sections) { return []; }
    protected function extractReferences($text, $sections) { return []; }
    protected function extractInterests($text, $sections) { return []; }
}

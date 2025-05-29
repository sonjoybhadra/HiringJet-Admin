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
    protected $supportedFormats = ['pdf', 'doc', 'docx', 'txt'];

    // Enhanced name detection patterns
    protected $namePatterns = [
        // Direct name patterns
        '/^([A-Z][a-z]{1,20}(?:\s+[A-Z][a-z]{1,20}){1,3})\s*$/m',
        '/^\s*([A-Z][A-Z\s]{8,40})\s*$/m', // ALL CAPS names
        '/^([A-Z][a-z]+(?:\s+[A-Z]\.?){0,2}\s+[A-Z][a-z]+)\s*$/m', // With middle initials

        // Labeled patterns
        //'/(?:name|candidate|applicant|full\s*name)\s*:?\s*([A-Z][a-z]+(?:\s+[A-Z][a-z]+){1,3})/i',
        //'/(?:mr|ms|mrs|dr|prof)\.?\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+){1,3})/i',

        // Header patterns (often names are in headers)
        '/^\s*([A-Z][a-z]+\s+[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)\s*\n/m',
    ];

    // Common titles to remove
    protected $titles = ['mr', 'mrs', 'ms', 'miss', 'dr', 'prof', 'professor', 'sir', 'madam', 'er', 'ca', 'phd'];

    // Words that disqualify a name
    protected $nameBlacklist = [
        'resume', 'cv', 'curriculum', 'vitae', 'profile', 'contact', 'phone', 'email',
        'address', 'mobile', 'tel', 'fax', 'www', 'http', 'linkedin', 'github',
        'experience', 'education', 'skills', 'objective', 'summary', 'references',
        'confidential', 'personal', 'information', 'details', 'page', 'updated'
    ];

    // Enhanced regex patterns
    protected $patterns = [
        'email' => '/\b[a-zA-Z0-9](?:[a-zA-Z0-9._-]*[a-zA-Z0-9])?@[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?)*\b/',
        'phone' => '/(?:\+\d{1,4}[-.\s]?)?\(?(?:\d{1,4})\)?[-.\s]?\d{1,4}[-.\s]?\d{1,4}[-.\s]?\d{1,9}/',
        'linkedin' => '/(?:https?:\/\/)?(?:www\.)?linkedin\.com\/in\/[a-zA-Z0-9_-]+\/?/',
        'github' => '/(?:https?:\/\/)?(?:www\.)?github\.com\/[a-zA-Z0-9_-]+\/?/',
        'date_range' => '/(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|\d{1,2}\/|\d{4})\s*[-–—]\s*(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|\d{1,2}\/|\d{4}|Present|Current|Now)/i',
        'year' => '/\b(19|20)\d{2}\b/',
    ];

    // Smart section detection
    protected $sectionPatterns = [
        'personal' => [
            'patterns' => ['/^(?:personal|contact|profile|about|summary|bio)\s*(?:information|details|data)?/i'],
            'weight' => 2.0,
            'position_bonus' => 3.0
        ],
        'experience' => [
            'patterns' => ['/^(?:work|professional|employment|career)\s*(?:experience|history|background)?/i', '/^experience$/i'],
            'weight' => 1.8,
            'position_bonus' => 1.0
        ],
        'education' => [
            'patterns' => ['/^(?:education|academic|qualification|learning)/i', '/^(?:educational|academic)\s*(?:background|qualification|details)/i'],
            'weight' => 1.6,
            'position_bonus' => 1.0
        ],
        'skills' => [
            'patterns' => ['/^(?:skills|competencies|expertise|proficiencies|abilities|technical\s*skills)/i'],
            'weight' => 1.4,
            'position_bonus' => 1.0
        ],
        'projects' => [
            'patterns' => ['/^(?:projects|portfolio|key\s*projects|major\s*projects)/i'],
            'weight' => 1.2,
            'position_bonus' => 1.0
        ],
        'certifications' => [
            'patterns' => ['/^(?:certifications?|certificates?|credentials|licenses|training)/i'],
            'weight' => 1.2,
            'position_bonus' => 1.0
        ],
        'achievements' => [
            'patterns' => ['/^(?:achievements?|awards?|honors?|recognition|accomplishments)/i'],
            'weight' => 1.1,
            'position_bonus' => 1.0
        ],
        'languages' => [
            'patterns' => ['/^(?:languages?|language\s*(?:skills|proficiency))/i'],
            'weight' => 1.0,
            'position_bonus' => 1.0
        ]
    ];

    public function parse(UploadedFile $file)
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, $this->supportedFormats)) {
                throw new Exception("Unsupported file format. Please upload PDF, DOC, DOCX, or TXT files.");
            }

            // Extract text with formatting metadata
            $extractedData = $this->extractTextWithMetadata($file, $extension);
            $text = $extractedData['text'];
            $metadata = $extractedData['metadata'];

            // Preprocess text
            $cleanText = $this->intelligentTextCleaning($text);

            // Extract data using multiple strategies
            $data = $this->extractAllData($cleanText, $metadata);

            // Post-process and validate
            $data = $this->validateAndCleanData($data);

            return [
                'success' => true,
                'data' => $data,
                'confidence_score' => $this->calculateSmartConfidenceScore($data),
                'extraction_metadata' => [
                    'text_length' => strlen($cleanText),
                    'has_formatting' => !empty($metadata['formatting']),
                    'name_extraction_method' => $metadata['name_method'] ?? 'pattern_based',
                    'sections_detected' => count($data) - 1 // Excluding personal_information
                ]
            ];

        } catch (Exception $e) {
            Log::error('CV Parsing error: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'debug_info' => [
                    'file_size' => $file->getSize(),
                    'file_type' => $extension
                ]
            ];
        }
    }

    /**
     * Extract text with formatting and position metadata
     */
    protected function extractTextWithMetadata(UploadedFile $file, $extension)
    {
        $path = $file->getRealPath();
        $text = '';
        $metadata = ['formatting' => [], 'structure' => []];

        switch ($extension) {
            case 'pdf':
                $text = Pdf::getText($path);
                $metadata = $this->analyzePDFStructure($text);
                break;

            case 'doc':
            case 'docx':
                $result = $this->extractWordWithFormatting($path);
                $text = $result['text'];
                $metadata = $result['metadata'];
                break;

            case 'txt':
                $text = file_get_contents($path);
                $metadata = $this->analyzeTextStructure($text);
                break;
        }

        return ['text' => $text, 'metadata' => $metadata];
    }

    /**
     * Enhanced Word document extraction with formatting
     */
    protected function extractWordWithFormatting($path)
    {
        $phpWord = WordParser::load($path);
        $text = '';
        $metadata = ['formatting' => [], 'structure' => []];
        $lineNumber = 0;

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $lineText = '';
                $lineFormatting = [];

                if (method_exists($element, 'getElements')) {
                    foreach ($element->getElements() as $childElement) {
                        if ($childElement instanceof Run) {
                            $runText = $childElement->getText();
                            if (!empty($runText)) {
                                $lineText .= $runText;

                                $font = $childElement->getFont();
                                if ($font) {
                                    $formatting = [
                                        'text' => $runText,
                                        'bold' => $font->getBold() ?? false,
                                        'size' => $font->getSize() ?? 11,
                                        'line' => $lineNumber
                                    ];

                                    if ($formatting['bold'] || $formatting['size'] > 12) {
                                        $lineFormatting[] = $formatting;
                                    }
                                }
                            }
                        } elseif (method_exists($childElement, 'getText')) {
                            $lineText .= $childElement->getText();
                        }
                    }
                } elseif (method_exists($element, 'getText')) {
                    $lineText = $element->getText();
                }

                if (!empty($lineText)) {
                    $text .= $lineText . "\n";
                    if (!empty($lineFormatting)) {
                        $metadata['formatting'] = array_merge($metadata['formatting'], $lineFormatting);
                    }
                    $lineNumber++;
                }
            }
        }

        return ['text' => $text, 'metadata' => $metadata];
    }

    /**
     * Analyze PDF structure for formatting clues
     */
    protected function analyzePDFStructure($text)
    {
        $lines = explode("\n", $text);
        $formatting = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Detect potential headers (short lines, all caps, or likely names)
            if ($index < 10) { // Top 10 lines
                if (ctype_upper($line) ||
                    (strlen($line) < 60 && $this->couldBeName($line))) {
                    $formatting[] = [
                        'text' => $line,
                        'bold' => true,
                        'line' => $index,
                        'likely_header' => true,
                        'score' => $this->calculateNameScore($line)
                    ];
                }
            }
        }

        return ['formatting' => $formatting, 'structure' => ['total_lines' => count($lines)]];
    }

    /**
     * Analyze text structure
     */
    protected function analyzeTextStructure($text)
    {
        $lines = explode("\n", $text);
        $formatting = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if ($index < 8 && (ctype_upper($line) || $this->couldBeName($line))) {
                $formatting[] = [
                    'text' => $line,
                    'bold' => ctype_upper($line),
                    'line' => $index,
                    'score' => $this->calculateNameScore($line)
                ];
            }
        }

        return ['formatting' => $formatting, 'structure' => ['total_lines' => count($lines)]];
    }

    /**
     * Intelligent text cleaning
     */
    protected function intelligentTextCleaning($text)
    {
        // Fix encoding issues
        $text = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text));

        // Normalize line breaks
        $text = preg_replace('/\r\n|\r|\n/', "\n", $text);

        // Fix multiple spaces but preserve line structure
        $text = preg_replace('/[ \t]+/', ' ', $text);

        // Fix bullet points
        $text = str_replace(['•', '●', '◦', '▪', '▫'], '*', $text);

        // Fix dashes
        $text = str_replace(['–', '—', '―'], '-', $text);

        // Fix quotes
        //$text = str_replace(''', "'", $text);
        //$text = str_replace(''', "'", $text);
        $text = str_replace('"', '"', $text);
        $text = str_replace('"', '"', $text);

        // Remove excessive empty lines but keep paragraph structure
        $text = preg_replace('/\n\s*\n\s*\n/', "\n\n", $text);

        return trim($text);
    }

    /**
     * Extract all data using multiple strategies
     */
    protected function extractAllData($text, $metadata)
    {
        // First, identify sections
        $sections = $this->smartSectionDetection($text, $metadata);

        // Extract personal information with advanced name detection
        $personalInfo = $this->advancedPersonalInfoExtraction($text, $metadata, $sections);

        $data = ['personal_information' => $personalInfo];

        // Extract other sections
        foreach ($sections as $sectionType => $sectionData) {
            $methodName = 'extract' . ucfirst($sectionType);
            if (method_exists($this, $methodName)) {
                $sectionContent = $this->getSectionContent($text, $sectionData);
                $data[$sectionType] = $this->$methodName($sectionContent, $text);
            }
        }

        return $data;
    }

    /**
     * Advanced name extraction using multiple strategies with first sentence priority
     */
    protected function advancedPersonalInfoExtraction($text, $metadata, $sections)
    {
        $personalInfo = [];

        // Strategy 0: Check first sentence/line as highest priority
        $name = $this->extractNameFromFirstSentence($text);
        $method = 'first_sentence';

        // Strategy 1: Use formatting information (high priority)
        if (!$name) {
            $name = $this->extractNameFromFormatting($metadata);
            $method = 'formatting';
        }

        // Strategy 2: Pattern-based extraction
        if (!$name) {
            $name = $this->extractNameFromPatterns($text);
            $method = 'patterns';
        }

        // Strategy 3: Position-based extraction (first non-empty lines)
        if (!$name) {
            $name = $this->extractNameFromPosition($text);
            $method = 'position';
        }

        // Strategy 4: Context-based extraction
        if (!$name) {
            $name = $this->extractNameFromContext($text);
            $method = 'context';
        }

        // Strategy 5: Fallback - best guess from first few lines
        if (!$name) {
            $name = $this->extractNameFallback($text);
            $method = 'fallback';
        }

        $personalInfo['name'] = $name;
        $personalInfo['_name_extraction_method'] = $method;

        // Extract other personal information
        $personalInfo['email'] = $this->smartEmailExtraction($text);
        $personalInfo['phone'] = $this->smartPhoneExtraction($text);
        $personalInfo['linkedin'] = $this->extractSocialProfile($text, 'linkedin');
        $personalInfo['github'] = $this->extractSocialProfile($text, 'github');
        $personalInfo['location'] = $this->smartLocationExtraction($text);
        $personalInfo['address'] = $this->smartAddressExtraction($text);

        return array_filter($personalInfo, function($value, $key) {
            return $value !== null && $key !== '_name_extraction_method';
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Extract name from the very first sentence/line (highest priority)
     */
    protected function extractNameFromFirstSentence($text)
    {
        $lines = explode("\n", $text);

        // Check first 3 lines only
        for ($i = 0; $i < min(3, count($lines)); $i++) {
            $line = trim($lines[$i]);

            // Skip empty lines
            if (empty($line)) continue;

            // Check if this first meaningful line looks like a name
            if ($this->isFirstSentenceName($line)) {
                $score = $this->calculateNameScore($line);

                // Lower threshold for first sentence since position is important
                if ($score > 0.4) {
                    return $this->cleanName($line);
                }
            }
        }

        return null;
    }

    /**
     * Extract name from formatting information with first sentence priority
     */
    protected function extractNameFromFormatting($metadata)
    {
        if (empty($metadata['formatting'])) return null;

        $candidates = [];
        $firstSentenceCandidate = null;

        foreach ($metadata['formatting'] as $format) {
            if (($format['bold'] || ($format['size'] ?? 0) > 13) &&
                $format['line'] < 8) { // Only consider top 8 lines

                $text = trim($format['text']);

                // First check: Is this a meaningful sentence/name candidate?
                if (!$this->isMeaningfulNameCandidate($text)) {
                    continue;
                }

                // Priority check: Is this the first sentence/line (line 0 or 1)?
                if ($format['line'] <= 1 && $this->isFirstSentenceName($text)) {
                    $firstSentenceCandidate = [
                        'text' => $text,
                        'score' => $this->calculateNameScore($text),
                        'line' => $format['line'],
                        'formatting_confidence' => $this->getFormattingConfidence($format),
                        'is_first_sentence' => true
                    ];
                }

                // Second check: Calculate name score for other candidates
                $score = $this->calculateNameScore($text);
                if ($score > 0.6) {
                    $candidates[] = [
                        'text' => $text,
                        'score' => $score,
                        'line' => $format['line'],
                        'formatting_confidence' => $this->getFormattingConfidence($format),
                        'is_first_sentence' => false
                    ];
                }
            }
        }

        // Priority 1: Return first sentence if it's a good name candidate
        if ($firstSentenceCandidate && $firstSentenceCandidate['score'] > 0.5) {
            return $this->cleanName($firstSentenceCandidate['text']);
        }

        // Priority 2: Return best candidate from other formatted text
        if (!empty($candidates)) {
            // Sort by combined score (name score + formatting confidence + line position)
            usort($candidates, function($a, $b) {
                $scoreA = $a['score'] + $a['formatting_confidence'] - ($a['line'] * 0.1);
                $scoreB = $b['score'] + $b['formatting_confidence'] - ($b['line'] * 0.1);

                return $scoreB <=> $scoreA;
            });

            return $this->cleanName($candidates[0]['text']);
        }

        return null;
    }

    /**
     * Check if this looks like a first sentence name
     */
    protected function isFirstSentenceName($text)
    {
        $text = trim($text);

        // Should not be empty
        if (empty($text)) return false;

        // Should not be too long (names are typically under 50 characters)
        if (strlen($text) > 50) return false;

        // Should not contain common first-line non-name content
        $nonNameFirstLines = [
            'curriculum vitae', 'resume', 'cv', 'profile', 'personal profile',
            'professional profile', 'about me', 'summary', 'overview',
            'contact information', 'personal information', 'candidate profile'
        ];

        foreach ($nonNameFirstLines as $nonName) {
            if (stripos($text, $nonName) !== false) {
                return false;
            }
        }

        // Should not start with common prefixes that aren't names
        $nonNamePrefixes = [
            'updated', 'revised', 'version', 'draft', 'confidential',
            'personal', 'professional', 'curriculum', 'page'
        ];

        foreach ($nonNamePrefixes as $prefix) {
            if (stripos($text, $prefix) === 0) {
                return false;
            }
        }

        // Should look like a name pattern
        $words = preg_split('/\s+/', trim($text));
        $wordCount = count($words);

        // Names typically have 2-4 words
        if ($wordCount < 2 || $wordCount > 4) return false;

        // Each word should start with capital letter (proper name format)
        foreach ($words as $word) {
            $cleanWord = trim($word, '.,!?:;');
            if (empty($cleanWord)) continue;

            // Must start with capital letter
            if (!preg_match('/^[A-Z]/', $cleanWord)) return false;

            // Should be mostly alphabetic
            if (!preg_match('/^[A-Za-z\'\.]+$/', $cleanWord)) return false;
        }

        return true;
    }

    /**
     * Check if text is a meaningful name candidate (not just random formatted text)
     */
    protected function isMeaningfulNameCandidate($text)
    {
        if (empty($text) || strlen($text) < 3) return false;

        // Check if it's too long to be a name
        if (strlen($text) > 60) return false;

        // Check if it contains too many non-alphabetic characters
        $alphaCount = preg_match_all('/[a-zA-Z]/', $text);
        $totalLength = strlen($text);
        if ($alphaCount / $totalLength < 0.7) return false; // At least 70% alphabetic

        // Check against common CV section headers (these shouldn't be names)
        $sectionHeaders = [
            'resume', 'curriculum vitae', 'cv', 'profile', 'summary', 'objective',
            'experience', 'education', 'skills', 'employment', 'work history',
            'qualifications', 'achievements', 'projects', 'certifications',
            'contact information', 'personal details', 'references', 'languages'
        ];

        foreach ($sectionHeaders as $header) {
            if (stripos($text, $header) !== false) {
                return false;
            }
        }

        // Check if it contains email patterns (emails aren't names)
        if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text)) {
            return false;
        }

        // Check if it contains phone patterns (phones aren't names)
        if (preg_match('/[\d\-\+\(\)\s]{8,}/', $text)) {
            return false;
        }

        // Check if it contains website/URL patterns
        if (preg_match('/(?:www\.|http|\.com|\.org|\.net|linkedin|github)/i', $text)) {
            return false;
        }

        // Check if it's a date or contains years
        if (preg_match('/\b(19|20)\d{2}\b/', $text)) {
            return false;
        }

        // Check word structure - names typically have 1-4 words
        $words = preg_split('/\s+/', trim($text));
        $wordCount = count($words);

        if ($wordCount < 1 || $wordCount > 5) return false;

        // Each word should look like a name part
        foreach ($words as $word) {
            $word = trim($word, '.,!?:;');

            // Skip very short words (but allow initials like "A." or "Jr")
            if (strlen($word) < 1) continue;

            // Check if word looks name-like
            if (!preg_match('/^[A-Za-z]/', $word)) return false; // Must start with letter

            // Allow common name patterns: "John", "O'Connor", "Jr.", "III", etc.
            if (!preg_match('/^[A-Za-z\'\.]+[IVX]*$/', $word)) return false;
        }

        return true;
    }

    /**
     * Calculate formatting confidence based on formatting properties
     */
    protected function getFormattingConfidence($format)
    {
        $confidence = 0;

        // Bold text gets higher confidence
        if ($format['bold']) {
            $confidence += 0.3;
        }

        // Larger font size gets higher confidence
        $fontSize = $format['size'] ?? 11;
        if ($fontSize > 14) {
            $confidence += 0.4;
        } elseif ($fontSize > 12) {
            $confidence += 0.2;
        }

        // Earlier lines get higher confidence (names usually at top)
        $line = $format['line'] ?? 10;
        if ($line === 0) {
            $confidence += 0.3;
        } elseif ($line < 3) {
            $confidence += 0.2;
        } elseif ($line < 5) {
            $confidence += 0.1;
        }

        return min($confidence, 1.0); // Cap at 1.0
    }

    /**
     * Extract name using pattern matching
     */
    protected function extractNameFromPatterns($text)
    {
        foreach ($this->namePatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $candidate = trim($matches[1]);
                if ($this->calculateNameScore($candidate) > 0.7) {
                    return $this->cleanName($candidate);
                }
            }
        }

        return null;
    }

    /**
     * Extract name from position (first few lines)
     */
    protected function extractNameFromPosition($text)
    {
        $lines = explode("\n", $text);
        $candidates = [];

        for ($i = 0; $i < min(6, count($lines)); $i++) {
            $line = trim($lines[$i]);
            if (empty($line) || strlen($line) < 4) continue;

            $score = $this->calculateNameScore($line);
            if ($score > 0.5) {
                $candidates[] = [
                    'text' => $line,
                    'score' => $score,
                    'line' => $i
                ];
            }
        }

        if (!empty($candidates)) {
            usort($candidates, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            return $this->cleanName($candidates[0]['text']);
        }

        return null;
    }

    /**
     * Extract name from context
     */
    protected function extractNameFromContext($text)
    {
        // Look for patterns with context
        $contextPatterns = [
            '/(?:Hi|Hello|Dear|From|Name|Candidate|Applicant),?\s*([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/i',
            '/I\s+am\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/i',
            '/My\s+name\s+is\s+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/i',
        ];

        foreach ($contextPatterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $candidate = trim($matches[1]);
                if ($this->calculateNameScore($candidate) > 0.6) {
                    return $this->cleanName($candidate);
                }
            }
        }

        return null;
    }

    /**
     * Fallback name extraction
     */
    protected function extractNameFallback($text)
    {
        $lines = explode("\n", $text);

        for ($i = 0; $i < min(10, count($lines)); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            // Very basic name-like pattern
            if (preg_match('/^[A-Z][a-z]+\s+[A-Z][a-z]+/', $line) &&
                !$this->containsBlacklistedWords($line)) {
                return $this->cleanName($line);
            }
        }

        return null;
    }

    /**
     * Calculate name score based on various factors
     */
    protected function calculateNameScore($text)
    {
        $score = 0;
        $text = trim($text);

        if (empty($text)) return 0;

        $words = explode(' ', $text);
        $wordCount = count($words);

        // Word count scoring (2-4 words is ideal for names)
        if ($wordCount >= 2 && $wordCount <= 4) {
            $score += 0.3;
        } elseif ($wordCount == 1 || $wordCount > 4) {
            $score -= 0.2;
        }

        // Capitalization scoring
        $properlyCapitalized = true;
        foreach ($words as $word) {
            if (!preg_match('/^[A-Z][a-z]*$/', $word) && !preg_match('/^[A-Z]\.$/', $word)) {
                $properlyCapitalized = false;
                break;
            }
        }
        if ($properlyCapitalized) {
            $score += 0.4;
        }

        // Length scoring
        $length = strlen($text);
        if ($length >= 5 && $length <= 50) {
            $score += 0.2;
        }

        // Blacklist check
        if ($this->containsBlacklistedWords($text)) {
            $score -= 0.5;
        }

        // Common name patterns
        if (preg_match('/^[A-Z][a-z]+\s+[A-Z][a-z]+$/', $text)) {
            $score += 0.3; // First Last
        }
        if (preg_match('/^[A-Z][a-z]+\s+[A-Z]\.\s+[A-Z][a-z]+$/', $text)) {
            $score += 0.3; // First M. Last
        }

        // Penalize if it contains numbers or special characters
        if (preg_match('/[\d@#$%^&*()_+=\[\]{}|;:",.<>?\/\\\\]/', $text)) {
            $score -= 0.3;
        }

        return max(0, min(1, $score));
    }

    /**
     * Check if text could be a name
     */
    protected function couldBeName($text)
    {
        return $this->calculateNameScore($text) > 0.4;
    }

    /**
     * Check if text contains blacklisted words
     */
    protected function containsBlacklistedWords($text)
    {
        $text = strtolower($text);
        foreach ($this->nameBlacklist as $word) {
            if (strpos($text, $word) !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Clean and format name
     */
    protected function cleanName($name)
    {
        if (!$name) return null;

        // Remove titles
        $titlePattern = '/^(' . implode('|', $this->titles) . ')\.?\s+/i';
        $name = preg_replace($titlePattern, '', $name);

        // Clean up
        $name = preg_replace('/[^\w\s\.]/', '', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name);

        // Convert to proper case
        $words = explode(' ', $name);
        $words = array_map(function($word) {
            if (strlen($word) > 1) {
                return ucfirst(strtolower($word));
            }
            return strtoupper($word);
        }, $words);

        return implode(' ', $words);
    }

    /**
     * Smart email extraction
     */
    protected function smartEmailExtraction($text)
    {
        // Try multiple patterns
        $patterns = [
            $this->patterns['email'],
            '/(?:email|e-mail|mail)\s*:?\s*([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,})/i',
            '/([a-zA-Z0-9._%+-]+)\s*(?:at|@)\s*([a-zA-Z0-9.-]+)\s*(?:dot|\.)\s*([a-zA-Z]{2,})/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $email = isset($matches[1]) && isset($matches[2]) ?
                    $matches[1] . '@' . $matches[2] . '.' . $matches[3] :
                    $matches[0];

                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return strtolower($email);
                }
            }
        }

        return null;
    }

    /**
     * Smart phone extraction
     */
    protected function smartPhoneExtraction($text)
    {
        $patterns = [
            '/(?:phone|tel|mobile|cell|contact)\s*:?\s*(\+?\d{1,4}[-.\s]?\d{3,4}[-.\s]?\d{3,4}[-.\s]?\d{1,4})/i',
            '/(\+\d{1,4}[-.\s]?\d{3,4}[-.\s]?\d{3,4}[-.\s]?\d{1,4})/',
            '/\b(\d{10,15})\b/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $phone = preg_replace('/[^\d+]/', '', $matches[1]);
                if (strlen($phone) >= 10) {
                    return $phone;
                }
            }
        }

        return null;
    }

    /**
     * Extract social profiles
     */
    protected function extractSocialProfile($text, $platform)
    {
        if (isset($this->patterns[$platform])) {
            if (preg_match($this->patterns[$platform], $text, $matches)) {
                $url = $matches[0];
                if (!preg_match('/^https?:\/\//', $url)) {
                    $url = 'https://' . $url;
                }
                return $url;
            }
        }
        return null;
    }

    /**
     * Smart location extraction
     */
    protected function smartLocationExtraction($text)
    {
        $locations = [
            'cities' => ['Dubai', 'Abu Dhabi', 'Sharjah', 'Ajman', 'Ras Al Khaimah', 'Fujairah', 'Umm Al Quwain',
                        'Mumbai', 'Delhi', 'Bangalore', 'Chennai', 'Hyderabad', 'Pune', 'Kolkata',
                        'Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad',
                        'Dhaka', 'Chittagong', 'Manila', 'Cebu', 'Davao', 'Kathmandu'],
            'countries' => ['UAE', 'United Arab Emirates', 'India', 'Pakistan', 'Bangladesh',
                           'Philippines', 'Nepal', 'Sri Lanka', 'Egypt', 'Saudi Arabia']
        ];

        // Look for cities first (more specific)
        foreach ($locations['cities'] as $city) {
            if (stripos($text, $city) !== false) {
                return $city;
            }
        }

        // Then countries
        foreach ($locations['countries'] as $country) {
            if (stripos($text, $country) !== false) {
                return $country;
            }
        }

        return null;
    }

    /**
     * Smart address extraction
     */
    protected function smartAddressExtraction($text)
    {
        $patterns = [
            '/(?:address|location|residence|residing)\s*:?\s*([^,\n]+(?:,[^,\n]+)*)/i',
            '/([A-Za-z0-9\s,.-]+(?:Dubai|Abu Dhabi|Sharjah|UAE|India|Pakistan|Philippines|Egypt)[^,\n]*)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $address = trim($matches[1]);
                if (strlen($address) > 10 && strlen($address) < 200) {
                    return $address;
                }
            }
        }

        return null;
    }

    /**
     * Smart section detection
     */
    protected function smartSectionDetection($text, $metadata)
    {
        $lines = explode("\n", $text);
        $sections = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line) || strlen($line) < 3) continue;

            foreach ($this->sectionPatterns as $sectionType => $config) {
                foreach ($config['patterns'] as $pattern) {
                    if (preg_match($pattern, $line)) {
                        $score = $config['weight'];

                        // Position bonus (earlier sections get higher priority)
                        if ($index < count($lines) * 0.3) {
                            $score *= $config['position_bonus'];
                        }

                        // Check if this is the best match for this section type
                        if (!isset($sections[$sectionType]) || $sections[$sectionType]['score'] < $score) {
                            $sections[$sectionType] = [
                                'start_line' => $index,
                                'heading' => $line,
                                'score' => $score
                            ];
                        }
                        break;
                    }
                }
            }
        }

        // Calculate end lines for each section
        $sortedSections = [];
        foreach ($sections as $type => $data) {
            $sortedSections[] = ['type' => $type, 'start' => $data['start_line'], 'data' => $data];
        }

        usort($sortedSections, function($a, $b) {
            return $a['start'] - $b['start'];
        });

        for ($i = 0; $i < count($sortedSections); $i++) {
            $currentSection = $sortedSections[$i];
            $nextStart = isset($sortedSections[$i + 1]) ? $sortedSections[$i + 1]['start'] : count($lines);

            $sections[$currentSection['type']]['end_line'] = $nextStart - 1;
        }

        return $sections;
    }

    /**
     * Get section content
     */
    protected function getSectionContent($text, $sectionData)
    {
        $lines = explode("\n", $text);
        $start = $sectionData['start_line'] + 1; // Skip heading
        $end = $sectionData['end_line'] ?? count($lines) - 1;

        if ($start > $end || $start >= count($lines)) {
            return '';
        }

        $sectionLines = array_slice($lines, $start, $end - $start + 1);
        return implode("\n", array_filter($sectionLines, function($line) {
            return !empty(trim($line));
        }));
    }

    /**
     * Extract experience with better parsing
     */
    protected function extractExperience($sectionContent, $fullText)
    {
        if (empty($sectionContent)) return [];

        $experiences = [];
        $blocks = $this->splitIntoBlocks($sectionContent);

        foreach ($blocks as $block) {
            $experience = $this->parseExperienceBlock($block);
            if (!empty($experience)) {
                $experiences[] = $experience;
            }
        }

        return $experiences;
    }

    /**
     * Parse individual experience block
     */
    protected function parseExperienceBlock($block)
    {
        $lines = explode("\n", trim($block));
        $experience = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Extract dates
            if (preg_match('/(\d{4})\s*[-–—]\s*(\d{4}|present|current|now)/i', $line, $matches)) {
                $experience['start_date'] = $matches[1];
                $experience['end_date'] = strtolower($matches[2]) === 'present' ||
                                         strtolower($matches[2]) === 'current' ||
                                         strtolower($matches[2]) === 'now' ? 'Present' : $matches[2];
                continue;
            }

            // Extract job title (usually first meaningful line or after dates)
            if (empty($experience['title']) &&
                !preg_match('/\d{4}/', $line) &&
                strlen($line) > 5 &&
                strlen($line) < 100) {
                $experience['title'] = $line;
                continue;
            }

            // Extract company (look for common patterns)
            if (preg_match('/(?:at|@)\s+(.+)/i', $line, $matches)) {
                $experience['company'] = trim($matches[1]);
                continue;
            }

            // If line contains common company indicators
            if (preg_match('/(?:company|corp|ltd|llc|inc|pvt|private|limited)/i', $line) &&
                empty($experience['company'])) {
                $experience['company'] = $line;
                continue;
            }
        }

        // Clean up and validate
        if (isset($experience['title']) || isset($experience['company'])) {
            return array_filter($experience);
        }

        return [];
    }

    /**
     * Extract education with better parsing
     */
    protected function extractEducation($sectionContent, $fullText)
    {
        if (empty($sectionContent)) return [];

        $education = [];
        $blocks = $this->splitIntoBlocks($sectionContent);

        foreach ($blocks as $block) {
            $edu = $this->parseEducationBlock($block);
            if (!empty($edu)) {
                $education[] = $edu;
            }
        }

        return $education;
    }

    /**
     * Parse education block
     */
    protected function parseEducationBlock($block)
    {
        $lines = explode("\n", trim($block));
        $education = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Extract year/date
            if (preg_match('/(\d{4})\s*[-–—]\s*(\d{4}|present|current)/i', $line, $matches)) {
                $education['start_year'] = $matches[1];
                $education['end_year'] = strtolower($matches[2]) === 'present' ||
                                        strtolower($matches[2]) === 'current' ? 'Present' : $matches[2];
            } elseif (preg_match('/(\d{4})/', $line, $matches)) {
                $education['year'] = $matches[1];
            }

            // Extract degree
            if (preg_match('/\b(bachelor|master|phd|doctorate|diploma|certificate|bsc|msc|mba|ba|bs|ma|md|jd|btech|mtech|bca|mca)\b/i', $line, $matches)) {
                $education['degree'] = $line;
            }

            // Extract institution
            if (preg_match('/\b(university|college|institute|school|academy)\b/i', $line)) {
                $education['institution'] = $line;
            }
        }

        return array_filter($education);
    }

    /**
     * Extract skills with intelligence
     */
    protected function extractSkills($sectionContent, $fullText)
    {
        if (empty($sectionContent)) return [];

        $skills = [];

        // Technical skills database
        $techSkills = [
            'programming' => ['PHP', 'JavaScript', 'Python', 'Java', 'C++', 'C#', 'Ruby', 'Go', 'Rust', 'Swift', 'Kotlin', 'TypeScript'],
            'web' => ['HTML', 'CSS', 'React', 'Vue', 'Angular', 'Laravel', 'Django', 'Node.js', 'Express', 'Bootstrap', 'Tailwind'],
            'database' => ['MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'SQLite', 'Oracle', 'SQL Server', 'Firebase'],
            'cloud' => ['AWS', 'Azure', 'GCP', 'Docker', 'Kubernetes', 'Terraform', 'Jenkins'],
            'tools' => ['Git', 'GitHub', 'GitLab', 'Jira', 'Confluence', 'Slack', 'Postman', 'VS Code'],
            'frameworks' => ['Spring', 'Hibernate', 'Flask', 'FastAPI', 'Next.js', 'Nuxt.js', 'Svelte']
        ];

        // Extract from section content
        $lines = explode("\n", $sectionContent);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Split by common separators
            $skillItems = preg_split('/[,;|•·\n]/', $line);
            foreach ($skillItems as $skill) {
                $skill = trim($skill);
                if (strlen($skill) > 1 && strlen($skill) < 50) {
                    $skills[] = [
                        'name' => $skill,
                        'category' => $this->categorizeSkill($skill, $techSkills)
                    ];
                }
            }
        }

        // Also extract technical skills from full text
        foreach ($techSkills as $category => $skillList) {
            foreach ($skillList as $skill) {
                if (stripos($fullText, $skill) !== false) {
                    $skills[] = [
                        'name' => $skill,
                        'category' => $category
                    ];
                }
            }
        }

        // Remove duplicates
        $uniqueSkills = [];
        $seenSkills = [];

        foreach ($skills as $skill) {
            $key = strtolower($skill['name']);
            if (!in_array($key, $seenSkills)) {
                $uniqueSkills[] = $skill;
                $seenSkills[] = $key;
            }
        }

        return $uniqueSkills;
    }

    /**
     * Categorize skill
     */
    protected function categorizeSkill($skill, $techSkills)
    {
        foreach ($techSkills as $category => $skills) {
            if (in_array($skill, $skills)) {
                return $category;
            }
        }
        return 'general';
    }

    /**
     * Extract projects
     */
    protected function extractProjects($sectionContent, $fullText)
    {
        if (empty($sectionContent)) return [];

        $projects = [];
        $blocks = $this->splitIntoBlocks($sectionContent);

        foreach ($blocks as $block) {
            $project = $this->parseProjectBlock($block);
            if (!empty($project)) {
                $projects[] = $project;
            }
        }

        return $projects;
    }

    /**
     * Parse project block
     */
    protected function parseProjectBlock($block)
    {
        $lines = explode("\n", trim($block));
        $project = [];
        $description = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // First substantial line is usually the project name
            if ($index === 0 || (empty($project['name']) && strlen($line) < 100)) {
                $project['name'] = $line;
            } else {
                $description[] = $line;
            }
        }

        if (!empty($description)) {
            $project['description'] = implode(' ', $description);
        }

        // Extract technologies
        $project['technologies'] = $this->extractTechnologies($block);

        return array_filter($project);
    }

    /**
     * Extract technologies from text
     */
    protected function extractTechnologies($text)
    {
        $technologies = [];
        $techList = ['PHP', 'JavaScript', 'Python', 'Java', 'React', 'Vue', 'Angular', 'Laravel', 'Django', 'Node.js', 'MySQL', 'MongoDB', 'AWS', 'Docker'];

        foreach ($techList as $tech) {
            if (stripos($text, $tech) !== false) {
                $technologies[] = $tech;
            }
        }

        return $technologies;
    }

    /**
     * Extract certifications
     */
    protected function extractCertifications($sectionContent, $fullText)
    {
        if (empty($sectionContent)) return [];

        $certifications = [];
        $lines = explode("\n", $sectionContent);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strlen($line) < 5) continue;

            $cert = ['name' => $line];

            // Extract year
            if (preg_match('/(\d{4})/', $line, $matches)) {
                $cert['year'] = $matches[1];
            }

            // Extract issuer
            $issuers = ['Microsoft', 'Google', 'AWS', 'Cisco', 'Oracle', 'SAP', 'CompTIA', 'Adobe'];
            foreach ($issuers as $issuer) {
                if (stripos($line, $issuer) !== false) {
                    $cert['issuer'] = $issuer;
                    break;
                }
            }

            $certifications[] = $cert;
        }

        return $certifications;
    }

    /**
     * Extract achievements
     */
    protected function extractAchievements($sectionContent, $fullText)
    {
        if (empty($sectionContent)) return [];

        $achievements = [];
        $lines = explode("\n", $sectionContent);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strlen($line) < 10) continue;

            $achievement = ['title' => $line];

            // Extract year
            if (preg_match('/(\d{4})/', $line, $matches)) {
                $achievement['year'] = $matches[1];
                $achievement['title'] = trim(str_replace($matches[0], '', $line));
            }

            $achievements[] = $achievement;
        }

        return $achievements;
    }

    /**
     * Extract languages
     */
    protected function extractLanguages($sectionContent, $fullText)
    {
        if (empty($sectionContent)) return [];

        $languages = [];
        $commonLanguages = ['English', 'Arabic', 'Hindi', 'Urdu', 'Spanish', 'French', 'German', 'Chinese', 'Japanese', 'Russian'];
        $proficiencyLevels = ['Native', 'Fluent', 'Conversational', 'Basic', 'Intermediate', 'Advanced'];

        foreach ($commonLanguages as $language) {
            if (stripos($sectionContent, $language) !== false) {
                $lang = ['language' => $language];

                // Try to find proficiency
                foreach ($proficiencyLevels as $level) {
                    if (stripos($sectionContent, $level) !== false) {
                        $lang['proficiency'] = $level;
                        break;
                    }
                }

                $languages[] = $lang;
            }
        }

        return $languages;
    }

    /**
     * Split content into logical blocks
     */
    protected function splitIntoBlocks($content)
    {
        // Split by double newlines or lines that look like separators
        $blocks = preg_split('/\n\s*\n|\n\s*[-=*]{3,}\s*\n/', $content);

        return array_filter(array_map('trim', $blocks), function($block) {
            return !empty($block) && strlen($block) > 10;
        });
    }

    /**
     * Validate and clean extracted data
     */
    protected function validateAndCleanData($data)
    {
        // Validate email
        if (isset($data['personal_information']['email'])) {
            if (!filter_var($data['personal_information']['email'], FILTER_VALIDATE_EMAIL)) {
                unset($data['personal_information']['email']);
            }
        }

        // Validate phone
        if (isset($data['personal_information']['phone'])) {
            $phone = preg_replace('/[^\d+]/', '', $data['personal_information']['phone']);
            if (strlen($phone) < 10) {
                unset($data['personal_information']['phone']);
            }
        }

        // Remove empty sections
        foreach ($data as $key => $value) {
            if (empty($value) || (is_array($value) && count($value) === 0)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Calculate smart confidence score
     */
    protected function calculateSmartConfidenceScore($data)
    {
        $score = 0;
        $maxScore = 100;

        // Name (25 points)
        if (!empty($data['personal_information']['name'])) {
            $score += 25;
        }

        // Contact info (25 points)
        $contactScore = 0;
        if (!empty($data['personal_information']['email'])) $contactScore += 15;
        if (!empty($data['personal_information']['phone'])) $contactScore += 10;
        $score += min($contactScore, 25);

        // Professional sections (50 points)
        $professionalSections = ['experience', 'education', 'skills'];
        $professionalScore = 0;

        foreach ($professionalSections as $section) {
            if (!empty($data[$section])) {
                switch ($section) {
                    case 'experience':
                        $professionalScore += 20;
                        break;
                    case 'education':
                        $professionalScore += 20;
                        break;
                    case 'skills':
                        $professionalScore += 10;
                        break;
                }
            }
        }
        $score += min($professionalScore, 50);

        return round($score, 2);
    }
}

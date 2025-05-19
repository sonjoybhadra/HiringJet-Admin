<?php



namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Pdf;
use PhpOffice\PhpWord\IOFactory as WordParser;
use Exception;

class CVParserService
{
    protected $supportedFormats = ['pdf', 'doc', 'docx', 'txt'];

    /**
     * Section headings to look for in CVs
     */
    protected $sectionHeadings = [
        'personal_information' => [
            'personal information', 'personal details', 'personal profile', 'contact', 'contact information', 'about me'
        ],
        'summary' => [
            'summary','SUMMARY', 'professional summary','PROFESSIONAL SUMMARY', 'career objective', 'CAREER OBJECTIVE','objective','OBJECTIVE', 'profile', 'about me'
        ],
        'education' => [
            'education', 'educational background', 'academic background', 'qualifications', 'academic qualifications'
        ],
        'experience' => [
            'experience', 'work experience', 'employment history', 'professional experience', 'career history'
        ],
        'skills' => [
            'skills', 'technical skills', 'core competencies', 'key skills', 'proficiencies'
        ],
        'languages' => [
            'languages', 'language proficiency', 'language skills'
        ],
        'certifications' => [
            'certifications', 'certificates', 'professional certifications', 'qualifications'
        ],
        'projects' => [
            'projects', 'project experience', 'key projects'
        ],
        'publications' => [
            'publications', 'research publications', 'papers', 'articles'
        ],
        'awards' => [
            'awards', 'achievements', 'honors', 'recognition', 'accomplishments'
        ],
        'references' => [
            'references', 'professional references'
        ],
        'interests' => [
            'interests', 'hobbies', 'activities', 'personal interests'
        ]
    ];

    /**
     * Parse CV file and extract structured data
     *
     * @param UploadedFile $file
     * @return array
     */
    public function parse(UploadedFile $file)
    {
        try {
            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, $this->supportedFormats)) {
                throw new Exception("Unsupported file format. Please upload PDF, DOC, DOCX, or TXT files.");
            }

            $text = $this->extractText($file, $extension);
             //dd($text);
            // Store the text for debugging purposes
            Log::debug('Extracted CV text', ['text' => $text]);

            // Identify sections in the CV
            $sections = $this->identifySections($text);

            // Extract structured data from each section
            $data = $this->extractStructuredData($text, $sections);

            return [
                'success' => true,
                'data' => $data
            ];
        } catch (Exception $e) {
            Log::error('CV Parsing error: ' . $e->getMessage(), [
                'file' => $file->getClientOriginalName(),
                'exception' => $e
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Extract text from uploaded file based on file type
     *
     * @param UploadedFile $file
     * @param string $extension
     * @return string
     * @throws Exception
     */
    protected function extractText(UploadedFile $file, $extension)
    {
        $path = $file->getRealPath();

        switch ($extension) {
            case 'pdf':
                return $this->extractTextFromPdf($path);
            case 'doc':
            case 'docx':
                return $this->extractTextFromWord($path);
            case 'txt':
                return file_get_contents($path);
            default:
                throw new Exception("Unsupported file format");
        }
    }

    /**
     * Extract text from PDF file
     *
     * @param string $path
     * @return string
     */
    protected function extractTextFromPdf($path)
    {
        return Pdf::getText($path);
    }

    /**
     * Extract text from Word document
     *
     * @param string $path
     * @return string
     */
    protected function extractTextFromWord($path)
    {
        $phpWord = WordParser::load($path);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . ' ';
                }
            }
        }

        return $text;
    }

    /**
     * Identify sections in the CV text
     *
     * @param string $text
     * @return array
     */
    protected function identifySections($text)
    {
        // Normalize text: convert to lowercase and replace multiple spaces with a single space
        $normalizedText = strtolower(preg_replace('/\s+/', ' ', $text));

        // Break text into lines for better section identification
        $lines = preg_split('/\r\n|\r|\n/', $text);

        $sections = [];
        $currentSection = null;
        $sectionStart = 0;

        foreach ($lines as $index => $line) {
            $line = trim($line);

            // Skip empty lines
            if (empty($line)) {
                continue;
            }

            $normalizedLine = strtolower($line);

            // Check if line is a section heading
            $detectedSection = $this->detectSectionHeading($normalizedLine);

            if ($detectedSection) {
                // If we've already identified a section, record its end
                if ($currentSection) {
                    $sections[$currentSection]['end'] = $index - 1;
                }

                // Start a new section
                $currentSection = $detectedSection;
                $sections[$currentSection] = [
                    'start' => $index,
                    'end' => null, // Will be filled in later
                    'heading' => $line
                ];
                $sectionStart = $index;
            }
        }

        // Set end of last section
        if ($currentSection) {
            $sections[$currentSection]['end'] = count($lines) - 1;
        }

        return $sections;
    }

    /**
     * Detect if a line is a section heading
     *
     * @param string $line
     * @return string|null
     */
    protected function detectSectionHeading($line)
    {
        $line = strtolower(trim($line));

        // Remove common punctuation from the line
        $normalizedLine = preg_replace('/[:.]+$/', '', $line);

        foreach ($this->sectionHeadings as $section => $headings) {
            foreach ($headings as $heading) {
                // Check for exact match or match with common formatting
                if ($normalizedLine == $heading || $normalizedLine == strtoupper($heading) ||
                    $normalizedLine == ucfirst($heading) || $normalizedLine == ucwords($heading)) {
                    return $section;
                }
            }
        }

        return null;
    }

    /**
     * Extract structured data from CV text
     *
     * @param string $text
     * @param array $sections
     * @return array
     */
    protected function extractStructuredData($text, $sections)
    {
        // Normalize text for better parsing
        $normalizedText = $this->normalizeText($text);

        // Extract data from each section based on identified sections
        $data = [
            'personal_information' => $this->extractPersonalInformation($normalizedText, $sections['personal_information'] ?? null),
            'summary' => $this->extractSummary($normalizedText, $sections['summary'] ?? null),
            'education' => $this->extractEducation($normalizedText, $sections['education'] ?? null),
            'experience' => $this->extractExperience($normalizedText, $sections['experience'] ?? null),
            'skills' => $this->extractSkills($normalizedText, $sections['skills'] ?? null),
            'languages' => $this->extractLanguages($normalizedText, $sections['languages'] ?? null),
            'certifications' => $this->extractCertifications($normalizedText, $sections['certifications'] ?? null),
            'projects' => $this->extractProjects($normalizedText, $sections['projects'] ?? null),
            'publications' => $this->extractPublications($normalizedText, $sections['publications'] ?? null),
            'awards' => $this->extractAwards($normalizedText, $sections['awards'] ?? null),
            'references' => $this->extractReferences($normalizedText, $sections['references'] ?? null),
            'social_profiles' => $this->extractSocialProfiles($normalizedText),
            'interests' => $this->extractInterests($normalizedText, $sections['interests'] ?? null),
            'metadata' => [
                'confidence_score' => $this->calculateConfidenceScore($sections),
                'parsed_at' => now()->toIso8601String(),
            ]
        ];

        // Filter out empty sections
        return array_filter($data, function($value) {
            return !empty($value) || $value === 0;
        });
    }

    /**
     * Normalize text for better parsing
     *
     * @param string $text
     * @return string
     */
    protected function normalizeText($text)
    {
        // Replace multiple whitespace with a single space
        $text = preg_replace('/\s+/', ' ', $text);

        // Normalize line breaks
        $text = str_replace(["\r", "\n"], " ", $text);

        return trim($text);
    }

    /**
     * Get text from a specific section
     *
     * @param string $text
     * @param array|null $section
     * @return string|null
     */
    protected function getSectionText($text, $section = null)
    {
        if (!$section) {
            // If no section is provided, return the whole text
            return $text;
        }

        // Split text into lines
        $lines = preg_split('/\r\n|\r|\n/', $text);

        // Extract section lines
        $sectionLines = array_slice($lines, $section['start'] + 1, $section['end'] - $section['start']);

        // Join section lines
        return implode(' ', $sectionLines);
    }

    /**
     * Extract personal information (name, email, phone, location)
     *
     * @param string $text
     * @param array|null $section
     * @return array
     */
    protected function extractPersonalInformation($text, $section = null)
    {
        $sectionText = $section ? $this->getSectionText($text, $section) : $text;

        $personalInfo = [
            'name' => $this->extractName($text), // Look for name in the full text
            'email' => $this->extractEmail($sectionText),
            'phone' => $this->extractPhone($sectionText),
            'location' => $this->extractLocation($sectionText),
            'address' => $this->extractAddress($sectionText),
            'date_of_birth' => $this->extractDateOfBirth($sectionText),
            'nationality' => $this->extractNationality($sectionText),
            'linkedin' => $this->extractLinkedIn($sectionText),
            'website' => $this->extractWebsite($sectionText),
        ];

        return array_filter($personalInfo);
    }

    /**
     * Extract name from text
     *
     * @param string $text
     * @return string|null
     */
    protected function extractName($text)
    {
        // Multiple name extraction strategies
        $strategies = [
            // Strategy 1: Name at the very beginning (most common)
            '/^([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/m',
            // Strategy 2: All caps name at beginning
            '/^([A-Z\s]{3,50})/m',
            // Strategy 3: Name in header-like format
            '/([A-Z][a-z]+\s+[A-Z][a-z]+(?:\s+[A-Z][a-z]+)?)\s*$/m',
            // Strategy 4: Name with professional titles removed
            '/^(?:Mr\.?|Ms\.?|Mrs\.?|Dr\.?)?\s*([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/m'
        ];

        foreach ($strategies as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $name = trim($matches[1]);
                // Clean up the name
                $name = preg_replace('/[^\w\s]/', '', $name);
                $name = preg_replace('/\s+/', ' ', $name);

                // Validate name (2-4 words, reasonable length)
                if (str_word_count($name) >= 2 && str_word_count($name) <= 4 && strlen($name) <= 50) {
                    return $name;
                }
            }
        }

        return null;
    }


    /**
     * Extract email from text
     *
     * @param string $text
     * @return string|null
     */
 protected function extractEmail($text) {
    // Normalize text: remove extra spaces, common prefixes
    $text = preg_replace('/\s+/', ' ', trim($text));
    $text = preg_replace('/^(Email?\s*:?-?\s*)/i', '', $text);

    // Strategy 1: Standard email pattern
    if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
        return strtolower($matches[0]);
    }

    // Strategy 2: Missing @ symbol (jihadalabad999gmail.com)
    // This will catch your example!
    if (preg_match('/([a-zA-Z0-9._%+-]{3,})([a-zA-Z0-9.-]*\.[a-zA-Z]{2,})/', $text, $matches)) {
        $username = $matches[1];
        $domain = $matches[2];

        // Check if domain contains common email providers
        if (preg_match('/(gmail|yahoo|hotmail|outlook|email|mail)/i', $domain)) {
            return strtolower($username . '@' . $domain);
        }

        // Check if it's a valid domain structure
        if (preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $domain) && strlen($username) >= 3) {
            return strtolower($username . '@' . $domain);
        }
    }

    // Strategy 3: Alternative pattern for missing @ - more specific for your case
    if (preg_match('/([a-zA-Z0-9._%+-]+)(gmail|yahoo|hotmail|outlook)\.com/i', $text, $matches)) {
        return strtolower($matches[1] . '@' . $matches[2] . '.com');
    }

    // Strategy 4: Spaced email (user @ domain . com)
    if (preg_match('/([a-zA-Z0-9._%+-]+)\s*@?\s*([a-zA-Z0-9.-]+)\s*\.\s*([a-zA-Z]{2,})/', $text, $matches)) {
        $username = $matches[1];
        $domain = $matches[2];
        $tld = $matches[3];
        return strtolower($username . '@' . $domain . '.' . $tld);
    }

    // Strategy 5: Obfuscated emails (user[at]domain[dot]com)
    $obfuscated = preg_replace('/\[at\]|\\(at\\)|@/i', '@', $text);
    $obfuscated = preg_replace('/\[dot\]|\\(dot\\)|\s*\.\s*/', '.', $obfuscated);
    if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $obfuscated, $matches)) {
        return strtolower($matches[0]);
    }

    return null;
}

    /**
     * Extract phone number from text
     *
     * @param string $text
     * @return string|null
     */
    protected function extractPhone($text)
    {
        $patterns = [
            '/\+\d{1,3}[\s\-]?\d{1,4}[\s\-]?\d{1,4}[\s\-]?\d{1,9}/',
            '/\(\d{3}\)[\s\-]?\d{3}[\s\-]?\d{4}/',
            '/\d{3}[\s\-]?\d{3}[\s\-]?\d{4}/',
            '/\d{10,15}/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return preg_replace('/[^\d\+\(\)]/', '', $matches[0]);
            }
        }
        return null;
    }

    /**
     * Extract location from text
     *
     * @param string $text
     * @return string|null
     */
    protected function extractLocation($text)
    {
        // Look for city, state/province, country patterns
        $patterns = [
            '/(?:located in|location|based in|residing in)\s+([A-Za-z\s]+,\s+[A-Za-z\s]+)/',
            '/(?:City|Town|Location):\s+([A-Za-z\s]+)/',
            '/([A-Za-z\s]+,\s+[A-Za-z]{2,})/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract full address from text
     *
     * @param string $text
     * @return string|null
     */
    protected function extractAddress($text)
    {
        // Look for address patterns
        $patterns = [
            '/(?:Address|Location):\s+([^,]+,.+?(?=\s+Phone|\s+Email|$))/i',
            '/([0-9]+\s+[A-Za-z\s]+,\s+[A-Za-z\s]+,\s+[A-Za-z\s]+\s+[0-9]{5,})/i',
            '/([^,]+,[^,]+,[^,]+,[^,]+)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract date of birth from text
     *
     * @param string $text
     * @return string|null
     */
    protected function extractDateOfBirth($text)
    {
        // Look for date of birth patterns
        $patterns = [
            '/(?:Date of Birth|DOB|Birth Date):\s+((?:January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{1,2},\s+\d{4}|\d{1,2}\/\d{1,2}\/\d{4}|\d{1,2}-\d{1,2}-\d{4})/',
            '/(?:Born):\s+(\d{1,2}\/\d{1,2}\/\d{4}|\d{1,2}-\d{1,2}-\d{4})/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract nationality from text
     *
     * @param string $text
     * @return string|null
     */
    protected function extractNationality($text)
    {
        // Look for nationality patterns
        $patterns = [
            '/(?:Nationality|Citizenship):\s+([A-Za-z\s]+)/',
            '/(?:I am|I\'m)\s+([A-Za-z]+)\s+(?:citizen|national)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract LinkedIn URL from text
     *
     * @param string $text
     * @return string|null
     */
    protected function extractLinkedIn($text)
    {
        $patterns = [
            '/(?:linkedin\.com\/in\/[a-zA-Z0-9_-]+)/',
            '/(?:linkedin\.com\/pub\/[a-zA-Z0-9_-]+)/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $matches[0];
            }
        }

        return null;
    }

    /**
     * Extract website from text
     *
     * @param string $text
     * @return string|null
     */
    protected function extractWebsite($text)
    {
        // Look for website patterns, excluding LinkedIn, GitHub, etc.
        $patterns = [
            '/(?:Website|Web|Homepage|Personal site):\s+(https?:\/\/(?!(?:linkedin\.com|github\.com|twitter\.com|facebook\.com))[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,})/',
            '/(https?:\/\/(?!(?:linkedin\.com|github\.com|twitter\.com|facebook\.com))[a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z]{2,})/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return $matches[1] ?? $matches[0];
            }
        }

        return null;
    }

    /**
     * Extract professional summary/objective
     *
     * @param string $text
     * @param array|null $section
     * @return string|null
     */
    protected function extractSummary($text, $section = null)
    {
        if ($section) {
            $sectionText = $this->getSectionText($text, $section);
            return $sectionText ? trim($sectionText) : null;
        }

        // Fallback to pattern matching if no section identified
        $patterns = [
            '/(?:Summary|Profile|Objective|Professional Summary|About Me)(?::|\.|\s)\s*([^.]+(?:\.[^.]+){0,5})/',
            '/(?:Career Objective|Professional Objective)(?::|\.|\s)\s*([^.]+(?:\.[^.]+){0,5})/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                return trim($matches[1]);
            }
        }

        return null;
    }

    /**
     * Extract education information
     *
     * @param string $text
     * @param array|null $section
     * @return array
     */
    protected function extractEducation($text, $section = null)
    {
        $education = [];

        // Get section text if available
        $textToSearch = $section ? $this->getSectionText($text, $section) : $text;

        // Define common degree patterns
        $degreePatterns = 'Bachelor|Master|PhD|BSc|MSc|MBA|BA|BS|MA|MD|JD';

        // Break into lines for better parsing
        $lines = preg_split('/\r\n|\r|\n/', $textToSearch);
        $currentEducation = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            // Check if this line begins a new education entry
            if (preg_match('/(' . $degreePatterns . '|University|College|Institute|School)[^.]*/', $line)) {
                // If we were building an education entry, add it to the list
                if ($currentEducation) {
                    $education[] = $currentEducation;
                }

                // Extract degree
                preg_match('/(' . $degreePatterns . '[^,]*),?/', $line, $degreeMatch);
                $degree = $degreeMatch[1] ?? null;

                // Extract institution
                preg_match('/(?:at|from|in)\s+([^,]+)/', $line, $instMatch);
                $institution = $instMatch[1] ?? null;

                // If no institution found, look for typical institution names
                if (!$institution && preg_match('/(University|College|Institute|School)\s+of\s+[^,]+/', $line, $instMatch)) {
                    $institution = $instMatch[0];
                }

                // Extract years
                preg_match('/(\d{4})\s*-\s*(\d{4}|Present|Current)/', $line, $yearMatch);
                $startYear = $yearMatch[1] ?? null;
                $endYear = $yearMatch[2] ?? null;

                // Extract field of study
                preg_match('/in\s+([^,]+)/', $line, $fieldMatch);
                $fieldOfStudy = $fieldMatch[1] ?? null;

                // Create new education entry
                $currentEducation = [
                    'degree' => $degree,
                    'institution' => $institution,
                    'field_of_study' => $fieldOfStudy,
                    'start_year' => $startYear,
                    'end_year' => $endYear,
                    'details' => []
                ];
            } elseif ($currentEducation) {
                // Add details to current education entry
                $currentEducation['details'][] = $line;
            }
        }

        // Add the last education entry if there is one
        if ($currentEducation) {
            $education[] = $currentEducation;
        }

        // Post-process education entries
        foreach ($education as &$edu) {
            // Join details into a single string
            if (!empty($edu['details'])) {
                $edu['description'] = implode(' ', $edu['details']);
                unset($edu['details']);
            }

            // Clean up empty fields
            $edu = array_filter($edu);
        }

        // If no education found using detailed approach, try pattern matching
        if (empty($education)) {
            // Extract education blocks using simpler pattern
            preg_match_all('/(?:' . $degreePatterns . '|University|College|Institute|School)[^.]*(?:\d{4})/', $textToSearch, $matches);

            foreach ($matches[0] as $educationBlock) {
                // Extract degree
                preg_match('/(' . $degreePatterns . '[^,]*),?/', $educationBlock, $degreeMatch);
                $degree = $degreeMatch[1] ?? null;

                // Extract institution
                preg_match('/(?:at|from|in)\s+([^,]+)/', $educationBlock, $instMatch);
                $institution = $instMatch[1] ?? null;

                // Extract years
                preg_match('/(\d{4})\s*-\s*(\d{4}|Present|Current)/', $educationBlock, $yearMatch);
                $startYear = $yearMatch[1] ?? null;
                $endYear = $yearMatch[2] ?? null;

                // Extract field of study
                preg_match('/in\s+([^,]+)/', $educationBlock, $fieldMatch);
                $fieldOfStudy = $fieldMatch[1] ?? null;

                if ($degree || $institution) {
                    $education[] = array_filter([
                        'degree' => $degree,
                        'institution' => $institution,
                        'field_of_study' => $fieldOfStudy,
                        'start_year' => $startYear,
                        'end_year' => $endYear,
                    ]);
                }
            }
        }

        return $education;
    }

    /**
     * Extract work experience information
     *
     * @param string $text
     * @param array|null $section
     * @return array
     */
    protected function extractExperience($text, $section = null)
    {
        $experience = [];

        // Get section text if available
        $textToSearch = $section ? $this->getSectionText($text, $section) : $text;

        // Define common job title patterns
        $jobTitles = 'Engineer|Developer|Manager|Director|Analyst|Consultant|Designer|Specialist|Officer|Lead|Head|Chief|Coordinator|Administrator|Assistant|Supervisor|Executive';

        // Break into lines for better parsing
        $lines = preg_split('/\r\n|\r|\n/', $textToSearch);
        $currentExperience = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            // Check if this line begins a new experience entry
            if (preg_match('/(?:' . $jobTitles . '|Experience)[^.]*(?:\d{4})/', $line) ||
                preg_match('/(\d{4})\s*-\s*(\d{4}|Present|Current)/', $line)) {
                // If we were building an experience entry, add it to the list
                if ($currentExperience) {
                    $experience[] = $currentExperience;
                }

                // Extract title
                preg_match('/(' . $jobTitles . '[^,]*),?/', $line, $titleMatch);
                $title = $titleMatch[1] ?? null;

                // Extract company
                preg_match('/(?:at|for|with)\s+([^,]+)/', $line, $companyMatch);
                $company = $companyMatch[1] ?? null;

                // Look for company name in other formats
                if (!$company && preg_match('/([^,]+)(?:,|\s+-)/', $line, $compMatch)) {
                    $company = $compMatch[1];
                }

                // Extract years
                preg_match('/(\d{4})\s*-\s*(\d{4}|Present|Current)/', $line, $yearMatch);
                $startYear = $yearMatch[1] ?? null;
                $endYear = $yearMatch[2] ?? null;

                // Extract more specific dates if available
                preg_match('/(?:January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{4}\s*-\s*(?:January|February|March|April|May|June|July|August|September|October|November|December)\s+\d{4}|Present|Current/i', $line, $dateMatch);
                $dateRange = $dateMatch[0] ?? null;

                // Create new experience entry
                $currentExperience = [
                    'title' => $title,
                    'company' => $company,
                    'date_range' => $dateRange,
                    'start_year' => $startYear,
                    'end_year' => $endYear,
                    'details' => []
                ];
            } elseif ($currentExperience) {
                // Check if line is a bullet point
                if (preg_match('/^(?:\s*•|\s*-|\s*\*|\s*\d+\.)\s*(.+)$/', $line, $bulletMatch)) {
                    $currentExperience['details'][] = $bulletMatch[1];
                } else {
                    // Add line as a detail
                    $currentExperience['details'][] = $line;
                }
            }
        }

        // Add the last experience entry if there is one
        if ($currentExperience) {
            $experience[] = $currentExperience;
        }

        // Post-process experience entries
        foreach ($experience as &$exp) {
            // Join details into a single string
            if (!empty($exp['details'])) {
                $exp['description'] = implode(' ', $exp['details']);
                unset($exp['details']);
            }

            // Extract location if present
            if (isset($exp['description'])) {
                preg_match('/in\s+([^,\.]+)/', $exp['description'], $locMatch);
                $exp['location'] = $locMatch[1] ?? null;
            }

            // Clean up empty fields
            $exp = array_filter($exp);
        }

        return $experience;
    }

    /**
     * Extract job description from experience block
     *
     * @param string $experienceBlock
     * @return string|null
     */
    protected function extractJobDescription($experienceBlock)
    {
        // Extract description after date information
        preg_match('/\d{4}[^.]*\.\s+(.+)/', $experienceBlock, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Extract job location from experience block
     *
     * @param string $experienceBlock
     * @return string|null
     */
    protected function extractJobLocation($experienceBlock)
    {
        preg_match('/in\s+([^,]+)/', $experienceBlock, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Extract skills information
     *
     * @param string $text
     * @return array
     */
    protected function extractSkills($text)
    {
        $skills = [];

        // Common skill section identifiers
        $skillSectionPatterns = [
            '/Skills(?::|\.|\s)\s*([^.]+(?:\.[^.]+){0,10})/',
            '/Technical Skills(?::|\.|\s)\s*([^.]+(?:\.[^.]+){0,10})/',
            '/Core Competencies(?::|\.|\s)\s*([^.]+(?:\.[^.]+){0,10})/',
            '/Proficiencies(?::|\.|\s)\s*([^.]+(?:\.[^.]+){0,10})/'
        ];

        foreach ($skillSectionPatterns as $pattern) {
            preg_match($pattern, $text, $matches);
            if (!empty($matches[1])) {
                $skillText = $matches[1];
                // Split by commas, semicolons, or other separators
                $skillItems = preg_split('/[,;]/', $skillText);
                foreach ($skillItems as $skill) {
                    $skill = trim($skill);
                    if (!empty($skill)) {
                        $skills[] = $skill;
                    }
                }
                break;
            }
        }

        // Look for common programming languages, frameworks, tools
        $techSkills = ['PHP', 'JavaScript', 'Python', 'Java', 'C\+\+', 'Ruby', 'Swift',
                      'HTML', 'CSS', 'SQL', 'Laravel', 'React', 'Vue', 'Angular', 'Node\.js',
                      'AWS', 'Azure', 'Docker', 'Kubernetes', 'Git', 'Jira', 'Scrum', 'Agile'];

        foreach ($techSkills as $techSkill) {
            if (preg_match('/\b' . $techSkill . '\b/i', $text)) {
                $skills[] = preg_replace('/\\\\/', '', $techSkill); // Remove escape characters
            }
        }

        return array_unique($skills);
    }

    /**
     * Extract languages spoken
     *
     * @param string $text
     * @return array
     */
    protected function extractLanguages($text)
    {
        $languages = [];

        // Common languages
        $commonLanguages = [
            'English', 'Spanish', 'French', 'German', 'Chinese', 'Japanese', 'Arabic',
            'Russian', 'Portuguese', 'Italian', 'Dutch', 'Korean', 'Turkish', 'Swedish', 'Hindi'
        ];

        // Try to find language section
        preg_match('/Languages(?::|\.|\s)\s*([^.]+)/', $text, $matches);

        if (!empty($matches[1])) {
            $languageText = $matches[1];
            // Split by commas or other separators
            $languageItems = preg_split('/[,;]/', $languageText);
            foreach ($languageItems as $language) {
                $language = trim($language);
                if (!empty($language)) {
                    // Try to extract proficiency level
                    preg_match('/([A-Za-z]+)\s*(?:\(([A-Za-z\s]+)\)|([A-Za-z\s]+))/', $language, $langMatches);

                    if (!empty($langMatches)) {
                        $langName = trim($langMatches[1]);
                        $proficiency = trim($langMatches[2] ?? $langMatches[3] ?? '');

                        $languages[] = [
                            'language' => $langName,
                            'proficiency' => $proficiency
                        ];
                    }
                }
            }
        } else {
            // Look for common languages directly
            foreach ($commonLanguages as $language) {
                if (preg_match('/\b' . $language . '\b\s*(?:\(([A-Za-z\s]+)\)|([A-Za-z\s]+))/', $text, $matches)) {
                    $proficiency = trim($matches[1] ?? $matches[2] ?? '');
                    $languages[] = [
                        'language' => $language,
                        'proficiency' => $proficiency
                    ];
                } elseif (preg_match('/\b' . $language . '\b/', $text)) {
                    $languages[] = [
                        'language' => $language,
                        'proficiency' => null
                    ];
                }
            }
        }

        return $languages;
    }

    /**
     * Extract certifications
     *
     * @param string $text
     * @return array
     */
    protected function extractCertifications($text)
    {
        $certifications = [];

        // Look for certification section
        preg_match('/Certifications(?::|\.|\s)\s*([^.]+(?:\.[^.]+){0,5})/', $text, $matches);

        if (!empty($matches[1])) {
            $certText = $matches[1];
            // Split by periods or other separators
            $certItems = preg_split('/[,;.]/', $certText);
            foreach ($certItems as $cert) {
                $cert = trim($cert);
                if (!empty($cert)) {
                    // Extract date if available
                    preg_match('/(.+?)(?:\s+|\()(\d{4})(?:\)|)/', $cert, $certMatches);

                    if (!empty($certMatches)) {
                        $certifications[] = [
                            'name' => trim($certMatches[1]),
                            'year' => $certMatches[2] ?? null,
                            'issuer' => null // Hard to reliably extract
                        ];
                    } else {
                        $certifications[] = [
                            'name' => $cert,
                            'year' => null,
                            'issuer' => null
                        ];
                    }
                }
            }
        } else {
            // Look for common certification patterns
            $certPatterns = [
                'AWS Certified', 'Microsoft Certified', 'Google Certified', 'Cisco Certified',
                'PMP', 'CCNA', 'CISSP', 'CISA', 'CFA', 'CPA', 'Six Sigma'
            ];

            foreach ($certPatterns as $certPattern) {
                if (preg_match('/\b' . $certPattern . '[^,.]*/', $text, $matches)) {
                    $certifications[] = [
                        'name' => trim($matches[0]),
                        'year' => null,
                        'issuer' => null
                    ];
                }
            }
        }

        return $certifications;
    }

    /**
     * Extract projects
     *
     * @param string $text
     * @return array
     */
    protected function extractProjects($text)
    {
        $projects = [];

        // Look for projects section
        preg_match('/Projects(?::|\.|\s)\s*([^.]+(?:\.[^.]+){0,10})/', $text, $matches);

        if (!empty($matches[1])) {
            $projectText = $matches[1];
            // Split by project indicators
            $projectItems = preg_split('/(?:\d+\.|•|\*)/', $projectText);
            foreach ($projectItems as $project) {
                $project = trim($project);
                if (!empty($project)) {
                    // Extract project name (assumed to be at the beginning or in quotes/brackets)
                    preg_match('/^([^:]+):|"([^"]+)"|\'([^\']+)\'|\[([^\]]+)\]/', $project, $nameMatches);
                    $name = $nameMatches[1] ?? $nameMatches[2] ?? $nameMatches[3] ?? $nameMatches[4] ?? null;

                    // Extract description (everything else)
                    $description = $name ? str_replace($nameMatches[0], '', $project) : $project;

                    if ($name || !empty($description)) {
                        $projects[] = [
                            'name' => $name ? trim($name) : null,
                            'description' => trim($description),
                            'technologies' => $this->extractTechnologiesFromText($project)
                        ];
                    }
                }
            }
        }

        return $projects;
    }

    /**
     * Extract technologies from text
     *
     * @param string $text
     * @return array
     */
    protected function extractTechnologiesFromText($text)
    {
        $technologies = [];

        // Common technologies to look for
        $techPatterns = [
            'PHP', 'JavaScript', 'Python', 'Java', 'C\+\+', 'Ruby', 'Swift',
            'HTML', 'CSS', 'SQL', 'Laravel', 'React', 'Vue', 'Angular', 'Node\.js',
            'AWS', 'Azure', 'Docker', 'Kubernetes', 'Git', 'MongoDB', 'MySQL',
            'PostgreSQL', 'Redis', 'RabbitMQ', 'Elasticsearch'
        ];

        foreach ($techPatterns as $tech) {
            if (preg_match('/\b' . $tech . '\b/i', $text)) {
                $technologies[] = preg_replace('/\\\\/', '', $tech); // Remove escape characters
            }
        }

        // Look for technologies listed in parentheses
        preg_match('/\(([^)]+)\)/', $text, $matches);
        if (!empty($matches[1])) {
            $techList = explode(',', $matches[1]);
            foreach ($techList as $tech) {
                $tech = trim($tech);
                if (!empty($tech)) {
                    $technologies[] = $tech;
                }
            }
        }

        return array_unique($technologies);
    }

    /**
     * Extract publications
     *
     * @param string $text
     * @return array
     */
    protected function extractPublications($text)
    {
        $publications = [];

        // Look for publications section
        preg_match('/Publications(?::|\.|\s)\s*([^.]+(?:\.[^.]+){0,10})/', $text, $matches);

        if (!empty($matches[1])) {
            $pubText = $matches[1];
            // Split by publication indicators
            $pubItems = preg_split('/(?:\d+\.|•|\*)/', $pubText);
            foreach ($pubItems as $pub) {
                $pub = trim($pub);
                if (!empty($pub)) {
                    // Extract publication year
                    preg_match('/\((\d{4})\)/', $pub, $yearMatches);
                    $year = $yearMatches[1] ?? null;

                    // Extract publication title (assumed to be in quotes)
                    preg_match('/"([^"]+)"/', $pub, $titleMatches);
                    $title = $titleMatches[1] ?? null;

                    if (!$title) {
                        // Alternative: title might be the first part before any year
                        $title = $year ? trim(strstr($pub, '(' . $year . ')', true)) : $pub;
                    }

                    if (!empty($title)) {
                        $publications[] = [
                            'title' => $title,
                            'year' => $year,
                            'publisher' => null, // Hard to reliably extract
                        ];
                    }
                }
            }
        }

        return $publications;
    }

    /**
     * Extract awards and achievements
     *
     * @param string $text
     * @return array
     */
    protected function extractAwards($text)
    {
        $awards = [];

        // Look for awards section
        preg_match('/(?:Awards|Achievements|Honors|Recognition)(?::|\.|\s)\s*([^.]+(?:\.[^.]+){0,10})/', $text, $matches);

        if (!empty($matches[1])) {
            $awardText = $matches[1];
            // Split by award indicators
            $awardItems = preg_split('/(?:\d+\.|•|\*)/', $awardText);
            foreach ($awardItems as $award) {
                $award = trim($award);
                if (!empty($award)) {
                    // Extract award year
                    preg_match('/\((\d{4})\)/', $award, $yearMatches);
                    $year = $yearMatches[1] ?? null;

                    // Remove year information from title
                    $title = $year ? trim(str_replace('(' . $year . ')', '', $award)) : $award;

                    if (!empty($title)) {
                        $awards[] = [
                            'title' => $title,
                            'year' => $year,
                            'issuer' => null, // Hard to reliably extract
                        ];
                    }
                }
            }
        }

        return $awards;
    }

    /**
     * Extract references
     *
     * @param string $text
     * @return array
     */
    protected function extractReferences($text)
    {
        $references = [];

        // Look for references section
        preg_match('/References(?::|\.|\s)\s*([^.]+(?:\.[^.]+){0,10})/', $text, $matches);

        if (!empty($matches[1])) {
            $refText = $matches[1];
            // Split by reference indicators
            $refItems = preg_split('/(?:\d+\.|•|\*)/', $refText);
            foreach ($refItems as $ref) {
                $ref = trim($ref);
                if (!empty($ref)) {
                    // Extract name (assumes name is at beginning)
                    preg_match('/^([A-Z][a-z]+ [A-Z][a-z]+)/', $ref, $nameMatches);
                    $name = $nameMatches[1] ?? null;

                    // Extract title/company
                    preg_match('/(?:' . ($name ? preg_quote($name, '/') : '') . ',\s+)?([^,]+,[^,]+)/', $ref, $titleMatches);
                    $titleCompany = $titleMatches[1] ?? null;

                    // Extract contact info (email/phone)
                    $email = null;
                    $phone = null;

                    if (preg_match('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $ref, $emailMatches)) {
                        $email = $emailMatches[0];
                    }

                    if (preg_match('/(?:\+\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/', $ref, $phoneMatches)) {
                        $phone = $phoneMatches[0];
                    }

                    if ($name || $titleCompany || $email || $phone) {
                        $references[] = [
                            'name' => $name,
                            'title_company' => $titleCompany,
                            'email' => $email,
                            'phone' => $phone,
                        ];
                    }
                }
            }
        } elseif (strpos(strtolower($text), 'references available upon request') !== false) {
            $references[] = [
                'name' => 'Available upon request',
                'title_company' => null,
                'email' => null,
                'phone' => null,
            ];
        }

        return $references;
    }

    /**
     * Extract social profiles
     *
     * @param string $text
     * @return array
     */
    protected function extractSocialProfiles($text)
    {
        $profiles = [];

        // Common social media platforms
        $platforms = [
            'LinkedIn' => '/linkedin\.com\/in\/([a-zA-Z0-9_-]+)/',
            'GitHub' => '/github\.com\/([a-zA-Z0-9_-]+)/',
            'Twitter' => '/twitter\.com\/([a-zA-Z0-9_-]+)/',
            'Facebook' => '/facebook\.com\/([a-zA-Z0-9_-]+)/',
            'Instagram' => '/instagram\.com\/([a-zA-Z0-9_-]+)/',
            'Medium' => '/medium\.com\/@?([a-zA-Z0-9_-]+)/',
            'Stack Overflow' => '/stackoverflow\.com\/users\/\d+\/([a-zA-Z0-9_-]+)/',
        ];

        foreach ($platforms as $platform => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $profiles[] = [
                    'platform' => $platform,
                    'url' => $matches[0],
                    'username' => $matches[1] ?? null,
                ];
            }
        }

        return $profiles;
    }

    /**
     * Extract interests and hobbies
     *
     * @param string $text
     * @return array
     */
    protected function extractInterests($text)
    {
        $interests = [];

        // Look for interests section
        preg_match('/(?:Interests|Hobbies)(?::|\.|\s)\s*([^.]+)/', $text, $matches);

        if (!empty($matches[1])) {
            $interestText = $matches[1];
            // Split by commas or other separators
            $interestItems = preg_split('/[,;]/', $interestText);
            foreach ($interestItems as $interest) {
                $interest = trim($interest);
                if (!empty($interest)) {
                    $interests[] = $interest;
                }
            }
        }

        return $interests;
    }

    /**
     * Calculate confidence score for the parsing
     *
     * @return float
     */
    protected function calculateConfidenceScore()
    {
        // This would be based on the quantity and quality of extracted data
        // For now, return a placeholder value
        return 0.85;
    }
}

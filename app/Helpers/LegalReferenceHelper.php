<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class LegalReferenceHelper
{
    /**
     * Process legal text to make references clickable
     * 
     * @param string $text The text to process
     * @param int $categoryId The current category ID
     * @param string|null $currentSection The current section being viewed (for context)
     * @return string The processed text with clickable references
     */
    public static function processContent($text, $categoryId, $currentSection = null)
    {
        if (empty($text)) {
            return "";
        }

        // Extract base section from current_section
        $baseSection = '';
        $baseSubsection = '';
        if ($currentSection && preg_match('/^(\d+(?:\.\d+)?)(?:\((\d+)\))?/', $currentSection, $baseMatch)) {
            $baseSection = $baseMatch[1];
            $baseSubsection = $baseMatch[2] ?? '';
        }

        // PRIORITY 1: Process cross-act references first
        // Handle specific act references, making only the section part clickable
        $text = preg_replace_callback(
            '/\b(subsection|section|paragraph)\s+(\d+(?:\.\d+)?(?:\([^)]+\))*)\s*\(([^)]+(?:Act|Division Rules|Rules)[^)]*)\)/i',
            function ($matches) use ($categoryId) {
                $type = strtolower($matches[1]);
                $sectionId = $matches[2];
                $actName = trim($matches[3]);
                
                // Look up the correct category ID for this act
                $actCategoryId = self::findActCategoryId($actName, $categoryId);
                
                // Return with only the section reference clickable, act name as plain text
                return '<span class="ref cross-act-ref" data-section-id="' . htmlspecialchars($sectionId, ENT_QUOTES) . 
                    '" data-category-id="' . htmlspecialchars($actCategoryId, ENT_QUOTES) . 
                    '" data-act-name="' . htmlspecialchars($actName, ENT_QUOTES) . 
                    '">' . $type . ' ' . $sectionId . '</span>' . ' (' . htmlspecialchars($actName, ENT_QUOTES) . ')';
            },
            $text
        );

        // Pattern: Handle "paragraphs (d) and (d.1)" format
        $text = preg_replace_callback(
            '/\b(paragraph|paragraphs)\s+\(([a-z](?:\.\d+)?)\)(?:\s+and\s+\(([a-z](?:\.\d+)?)\))?/i',
            function ($matches) use ($baseSection, $baseSubsection, $categoryId) {
                $type = strtolower($matches[1]);
                $firstLetter = $matches[2];
                $secondLetter = isset($matches[3]) ? $matches[3] : '';
                
                if (empty($baseSection)) return $matches[0];
                
                // Build the section ID including subsection if available
                $sectionId = $baseSection;
                if (!empty($baseSubsection)) {
                    $sectionId .= '(' . $baseSubsection . ')';
                }
                
                $html = '<span class="ref" data-section-id="' . htmlspecialchars($sectionId . '(' . $firstLetter . ')', ENT_QUOTES) . 
                        '" data-category-id="' . htmlspecialchars($categoryId, ENT_QUOTES) . 
                        '">' . $type . ' (' . $firstLetter . ')</span>';
                
                if ($secondLetter) {
                    $html .= ' and <span class="ref" data-section-id="' . htmlspecialchars($sectionId . '(' . $secondLetter . ')', ENT_QUOTES) . 
                            '" data-category-id="' . htmlspecialchars($categoryId, ENT_QUOTES) . 
                            '">(' . $secondLetter . ')</span>';
                }
                
                return $html;
            },
            $text
        );

        // Pattern: Handle both "subsection (1) or (2)" and "subsection (1.1) or (1.2)" patterns
        $text = preg_replace_callback(
            '/\b(subsection|subsections?)\s+\((\d+(?:\.\d+)?)\)(?:\s+or\s+\((\d+(?:\.\d+)?)\))/i',
            function ($matches) use ($baseSection, $categoryId) {
                $type = strtolower($matches[1]);
                $firstNum = $matches[2];
                $secondNum = $matches[3];
                
                if (empty($baseSection)) return $matches[0];
                
                $html = '<span class="ref" data-section-id="' . htmlspecialchars($baseSection . '(' . $firstNum . ')', ENT_QUOTES) . 
                        '" data-category-id="' . htmlspecialchars($categoryId, ENT_QUOTES) . 
                        '">' . $type . ' (' . $firstNum . ')</span>';
                
                if ($secondNum) {
                    $html .= ' or <span class="ref" data-section-id="' . htmlspecialchars($baseSection . '(' . $secondNum . ')', ENT_QUOTES) . 
                            '" data-category-id="' . htmlspecialchars($categoryId, ENT_QUOTES) . 
                            '">(' . $secondNum . ')</span>';
                }
                
                return $html;
            },
            $text
        );

        // Handle "subsection 82(2), (3) or (4)" format
        $text = preg_replace_callback(
            '/\b(subsection|subsections?)\s+(\d+(?:\.\d+)?)\((\d+(?:\.\d+)?)\)(?:\s*,\s*\((\d+(?:\.\d+)?)\))*(?:\s+or\s+\((\d+(?:\.\d+)?)\))?/i',
            function ($matches) use ($categoryId) {
                $type = strtolower($matches[1]);
                $section = $matches[2];
                $firstNum = $matches[3];
                
                // Start with the first subsection
                $html = '<span class="ref" data-section-id="' . htmlspecialchars($section . '(' . $firstNum . ')', ENT_QUOTES) . 
                        '" data-category-id="' . htmlspecialchars($categoryId, ENT_QUOTES) . 
                        '">' . $type . ' ' . $section . '(' . $firstNum . ')</span>';
                
                // Handle comma-separated subsections
                if (preg_match_all('/,\s*\((\d+(?:\.\d+)?)\)/', $matches[0], $commaMatches)) {
                    foreach ($commaMatches[1] as $num) {
                        $html .= ', <span class="ref" data-section-id="' . htmlspecialchars($section . '(' . $num . ')', ENT_QUOTES) . 
                                '" data-category-id="' . htmlspecialchars($categoryId, ENT_QUOTES) . 
                                '">(' . $num . ')</span>';
                    }
                }
                
                // Handle the "or" subsection if it exists
                if (isset($matches[5])) {
                    $html .= ' or <span class="ref" data-section-id="' . htmlspecialchars($section . '(' . $matches[5] . ')', ENT_QUOTES) . 
                            '" data-category-id="' . htmlspecialchars($categoryId, ENT_QUOTES) . 
                            '">(' . $matches[5] . ')</span>';
                }
                
                return $html;
            },
            $text
        );

        // Handle "paragraph 36(1)(b) or (c)" format
        $text = preg_replace_callback(
            '/\b(paragraph|paragraphs)\s+(\d+(?:\.\d+)?)\((\d+)\)\(([a-z](?:\.\d+)?)\)(?:\s+or\s+\(([a-z](?:\.\d+)?)\))?/i',
            function ($matches) use ($categoryId) {
                $type = strtolower($matches[1]);
                $section = $matches[2];
                $subsection = $matches[3];
                $firstLetter = $matches[4];
                $secondLetter = isset($matches[5]) ? $matches[5] : '';
                
                // Build the first reference
                $html = '<span class="ref" data-section-id="' . htmlspecialchars($section . '(' . $subsection . ')(' . $firstLetter . ')', ENT_QUOTES) . 
                        '" data-category-id="' . htmlspecialchars($categoryId, ENT_QUOTES) . 
                        '">' . $type . ' ' . $section . '(' . $subsection . ')(' . $firstLetter . ')</span>';
                
                // Add second reference if it exists
                if ($secondLetter) {
                    $html .= ' or <span class="ref" data-section-id="' . htmlspecialchars($section . '(' . $subsection . ')(' . $secondLetter . ')', ENT_QUOTES) . 
                            '" data-category-id="' . htmlspecialchars($categoryId, ENT_QUOTES) . 
                            '">(' . $secondLetter . ')</span>';
                }
                
                return $html;
            },
            $text
        );

        // Handle simple "paragraph (a)" format with proper context
        $text = preg_replace_callback(
            '/\b(paragraph|paragraphs)\s+\(([a-z](?:\.\d+)?)\)(?!\s*\([a-z](?:\.\d+)?\))/i',
            function ($matches) use ($baseSection, $baseSubsection, $categoryId) {
                $type = strtolower($matches[1]);
                $letter = $matches[2];
                
                if (empty($baseSection)) return $matches[0];
                
                // Build the complete section ID using context
                $sectionId = $baseSection;
                
                // Add subsection if available
                if (!empty($baseSubsection)) {
                    $sectionId .= '(' . $baseSubsection . ')';
                }
                
                // Add the paragraph letter
                $sectionId .= '(' . $letter . ')';
                
                return '<span class="ref" data-section-id="' . htmlspecialchars($sectionId, ENT_QUOTES) . 
                    '" data-category-id="' . htmlspecialchars($categoryId, ENT_QUOTES) . 
                    '">' . $type . ' (' . $letter . ')</span>';
            },
            $text
        );

        // Handle "section 34, 35, 35.1 or 37" format
        $text = preg_replace_callback(
            '/\b(section|sections)\s+(\d+(?:\.\d+)?)(?:\s*,\s*(\d+(?:\.\d+)?))*(?:\s+or\s+(\d+(?:\.\d+)?))?/i',
            function ($matches) use ($categoryId) {
                $type = strtolower($matches[1]);
                $firstNum = $matches[2];
                $output = '';
                
                // Start with first number
                $output = '<span class="ref" data-section-id="' . htmlspecialchars($firstNum, ENT_QUOTES) . 
                        '" data-category-id="' . htmlspecialchars($categoryId, ENT_QUOTES) . 
                        '">' . $type . ' ' . $firstNum . '</span>';
                
                // Get all comma-separated numbers
                if (preg_match_all('/,\s*(\d+(?:\.\d+)?)/', $matches[0], $commaMatches)) {
                    foreach ($commaMatches[1] as $number) {
                        $output .= ', <span class="ref" data-section-id="' . htmlspecialchars($number, ENT_QUOTES) . 
                                '" data-category-id="' . htmlspecialchars($categoryId, ENT_QUOTES) . 
                                '">' . $number . '</span>';
                    }
                }
                
                // Add the "or" number if it exists
                if (!empty($matches[4])) {
                    $output .= ' or <span class="ref" data-section-id="' . htmlspecialchars($matches[4], ENT_QUOTES) . 
                            '" data-category-id="' . htmlspecialchars($categoryId, ENT_QUOTES) . 
                            '">' . $matches[4] . '</span>';
                }
                
                return $output;
            },
            $text
        );

        // Add any remaining patterns from the original function...
        
        return $text;
    }
    
    /**
     * Find the category ID for a specific act by name
     *
     * @param string $actName The name of the act
     * @param int $defaultCategoryId Default category ID to return if act not found
     * @return int The category ID for the act
     */
    private static function findActCategoryId($actName, $defaultCategoryId)
    {
        try {
            $result = DB::table('legal_tables_master')
                ->where('act_name_1', $actName)
                ->orWhere('act_name_2', $actName)
                ->orWhere('act_name_3', $actName)
                ->orWhere('act_name_1', 'like', "%$actName%")
                ->orWhere('act_name_2', 'like', "%$actName%")
                ->orWhere('act_name_3', 'like', "%$actName%")
                ->first();
                
            return $result ? $result->id : $defaultCategoryId;
        } catch (\Exception $e) {
            return $defaultCategoryId;
        }
    }
    
    /**
     * Parse section IDs for custom sorting
     *
     * @param string|null $id The section ID to parse
     * @return array Parsed components of the section ID
     */
    public static function parseSectionId($id)
    {
        if ($id === null || $id === "") {
            return [];
        }

        $parts = [];
        preg_match_all("/(\d+|\D+)/", $id, $matches);

        foreach ($matches[0] as $part) {
            if (is_numeric($part)) {
                $parts[] = intval($part);
            } else {
                $part = trim($part, "().");
                $parts[] = $part;
            }
        }

        return $parts;
    }

    /**
     * Compare section IDs for hierarchical sorting
     *
     * @param string $a First section ID
     * @param string $b Second section ID
     * @return int Comparison result (-1, 0, 1)
     */
    public static function compareSectionIds($a, $b)
    {
        $partsA = self::parseSectionId($a);
        $partsB = self::parseSectionId($b);

        $maxParts = max(count($partsA), count($partsB));

        for ($i = 0; $i < $maxParts; $i++) {
            if (!isset($partsA[$i])) {
                return -1;
            }
            if (!isset($partsB[$i])) {
                return 1;
            }

            $partA = $partsA[$i];
            $partB = $partsB[$i];

            if (is_numeric($partA) && is_numeric($partB)) {
                if ($partA !== $partB) {
                    return $partA - $partB;
                }
            } else {
                $cmp = strcmp((string) $partA, (string) $partB);
                if ($cmp !== 0) {
                    return $cmp;
                }
            }
        }

        return 0;
    }
}

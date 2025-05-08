<?php
define('BASE_PATH', dirname(__DIR__, 3));
require_once BASE_PATH . '/vendor/autoload.php';

use Smalot\PdfParser\Parser;

function processPdfSkills($pdfPath) {
    try {
        // Extract text from PDF
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();

        // Define desired skills
        $desiredSkills = ['PHP', 'JavaScript', 'SQL', 'HTML', 'CSS', 'Python'];
        $matchedSkills = [];

        // Case-insensitive matching
        foreach ($desiredSkills as $skill) {
            if (stripos($text, $skill) !== false) {
                $matchedSkills[] = $skill;
            }
        }

        return implode(', ', $matchedSkills); // e.g., "PHP, SQL"
    } catch (Exception $e) {
        return ''; // Return empty string on error
    }
}
?>
<?php
header('Content-Type: application/json');

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/php/logs/php_error_log');

function respond($status, $data = null, $message = null) {
    $response = ['status' => $status];
    if ($data !== null) $response['data'] = $data;
    if ($message !== null) $response['message'] = $message;
    echo json_encode($response);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
$text = isset($input['text']) ? trim($input['text']) : '';
$target_lang = isset($input['target_lang']) ? trim($input['target_lang']) : 'FR';
$source_lang = isset($input['source_lang']) ? trim($input['source_lang']) : 'auto';

if (empty($text)) {
    respond('error', null, 'Text is required');
}

// Prepare input for Python script
$input_data = json_encode([
    'text' => $text,
    'target_lang' => $target_lang,
    'source_lang' => $source_lang
]);

// Path to Python script (Windows path)
$python_script = 'C:\\xampp\\htdocs\\fpjw\\projet_web\\front_office\\front_office\\view\\translate.py';
$python_executable = 'python'; // Adjust if needed (e.g., 'python3' or full path like 'C:\\Python39\\python.exe')

// Escape command for Windows
$command = escapeshellcmd("$python_executable \"$python_script\"");
$descriptors = [
    0 => ['pipe', 'r'], // stdin
    1 => ['pipe', 'w'], // stdout
    2 => ['pipe', 'w']  // stderr
];

$process = proc_open($command, $descriptors, $pipes);

if (is_resource($process)) {
    fwrite($pipes[0], $input_data);
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $return_code = proc_close($process);

    if ($return_code !== 0 || !empty($error)) {
        error_log("Python error: $error, Return code: $return_code");
        respond('error', null, 'Python script error: ' . $error);
    }

    $result = json_decode($output, true);
    if ($result && $result['status'] === 'success') {
        respond('success', ['translated_text' => $result['translated_text']]);
    } else {
        error_log('Python script output: ' . $output);
        respond('error', null, $result['message'] ?? 'Translation failed');
    }
} else {
    error_log('Failed to execute Python script: ' . $command);
    respond('error', null, 'Failed to execute Python script');
}
?>
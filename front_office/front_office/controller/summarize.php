<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['text'])) {
    $text = $_POST['text'];
    $escapedText = escapeshellarg($text);

    $command = "echo $escapedText | python3 summarizer.py";
    $output = shell_exec($command);

    header('Content-Type: application/json');
    echo $output;
}
?>

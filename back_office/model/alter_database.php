<?php
// Database alter script to fix title column length and empty course ID issues

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aziz";

// Create PDO connection
try {
    $dsn = "mysql:host={$servername};dbname={$dbname};charset=utf8mb4";
    $conn = new PDO(
        $dsn,
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

// Start HTML output
echo "<div style='font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 30px; background-color: #f9f9f9; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);'>";
echo "<h1 style='color: #333; margin-top: 0;'>Database Structure Enhancement</h1>";
echo "<div style='background-color: #fff; padding: 20px; border-radius: 4px; margin-bottom: 20px;'>";

// 1. Alter the title column to increase its size
$alterTitleSql = "ALTER TABLE cours MODIFY title VARCHAR(100) NOT NULL";
$success1 = false;

try {
    $conn->exec($alterTitleSql);
    echo "<div style='background-color: #f0fff4; padding: 15px; border-left: 5px solid #28a745; margin-bottom: 15px;'>";
    echo "<h3 style='color: #28a745; margin-top: 0;'>✓ Title Field Enhanced</h3>";
    echo "<p>The course title column has been updated to allow up to 100 characters.</p>";
    echo "</div>";
    $success1 = true;
} catch (PDOException $e) {
    echo "<div style='background-color: #fff8f8; padding: 15px; border-left: 5px solid #dc3545; margin-bottom: 15px;'>";
    echo "<h3 style='color: #dc3545; margin-top: 0;'>✗ Title Field Update Failed</h3>";
    echo "<p>There was an error updating the course title column: " . $e->getMessage() . "</p>";
    echo "</div>";
}

// 2. Fix empty course IDs
echo "<h2>Checking for Courses with Empty IDs...</h2>";

// First check if there are courses with empty IDs
$checkEmptySql = "SELECT COUNT(*) as empty_count FROM cours WHERE course_id = '' OR course_id IS NULL";
$result = $conn->query($checkEmptySql);
$row = $result->fetch(PDO::FETCH_ASSOC);
$emptyCount = $row['empty_count'];

if ($emptyCount > 0) {
    echo "<div style='background-color: #fff5e6; padding: 15px; border-left: 5px solid #ff9800; margin-bottom: 15px;'>";
    echo "<h3 style='color: #ff9800; margin-top: 0;'>⚠ Found {$emptyCount} courses with empty IDs</h3>";
    echo "<p>Attempting to generate new IDs for these courses...</p>";
    echo "</div>";
    
    // Get the latest course ID
    $lastIdSql = "SELECT course_id FROM cours WHERE course_id LIKE 'CRS-%' ORDER BY CAST(SUBSTRING(course_id, 5) AS UNSIGNED) DESC LIMIT 1";
    $result = $conn->query($lastIdSql);
    
    $nextNum = 1;
    if ($result && $result->rowCount() > 0) {
        $row = $result->fetch(PDO::FETCH_ASSOC);
        $lastId = $row['course_id'];
        
        if (preg_match('/CRS-(\d+)/', $lastId, $matches)) {
            $nextNum = intval($matches[1]) + 1;
        }
    }
    
    // Get all courses with empty IDs
    $emptyCoursesSql = "SELECT * FROM cours WHERE course_id = '' OR course_id IS NULL";
    $result = $conn->query($emptyCoursesSql);
    $fixedCount = 0;
    
    if ($result && $result->rowCount() > 0) {
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $newId = 'CRS-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
            $nextNum++;
            
            // Update the course with a new ID
            $updateSql = "UPDATE cours SET course_id = :newId WHERE id = :id OR (course_id = '' AND title = :title)";
            $stmt = $conn->prepare($updateSql);
            
            // Check if we have an auto-increment column named 'id'
            if (isset($row['id'])) {
                $stmt->bindParam(':newId', $newId);
                $stmt->bindParam(':id', $row['id']);
                $stmt->bindParam(':title', $row['title']);
            } else {
                // If there's no 'id' column, use the title as a way to identify the record
                $updateSql = "UPDATE cours SET course_id = :newId WHERE course_id = '' AND title = :title";
                $stmt = $conn->prepare($updateSql);
                $stmt->bindParam(':newId', $newId);
                $stmt->bindParam(':title', $row['title']);
            }
            
            if ($stmt->execute()) {
                $fixedCount++;
            }
        }
        
        if ($fixedCount > 0) {
            echo "<div style='background-color: #f0fff4; padding: 15px; border-left: 5px solid #28a745; margin-bottom: 15px;'>";
            echo "<h3 style='color: #28a745; margin-top: 0;'>✓ Fixed {$fixedCount} courses with new IDs</h3>";
            echo "<p>Generated unique course IDs for previously empty entries.</p>";
            echo "</div>";
        } else {
            echo "<div style='background-color: #fff8f8; padding: 15px; border-left: 5px solid #dc3545; margin-bottom: 15px;'>";
            echo "<h3 style='color: #dc3545; margin-top: 0;'>✗ Failed to fix empty course IDs</h3>";
            echo "<p>Could not update the courses with empty IDs.</p>";
            echo "</div>";
        }
    }
} else {
    echo "<div style='background-color: #f0fff4; padding: 15px; border-left: 5px solid #28a745; margin-bottom: 15px;'>";
    echo "<h3 style='color: #28a745; margin-top: 0;'>✓ No Empty Course IDs</h3>";
    echo "<p>All courses have proper IDs assigned.</p>";
    echo "</div>";
}

// 3. Check if there are any empty titles and fix them
$checkEmptyTitleSql = "SELECT COUNT(*) as empty_title_count FROM cours WHERE title = '' OR title IS NULL";
$result = $conn->query($checkEmptyTitleSql);
$row = $result->fetch(PDO::FETCH_ASSOC);
$emptyTitleCount = $row['empty_title_count'];

if ($emptyTitleCount > 0) {
    echo "<div style='background-color: #fff5e6; padding: 15px; border-left: 5px solid #ff9800; margin-bottom: 15px;'>";
    echo "<h3 style='color: #ff9800; margin-top: 0;'>⚠ Found {$emptyTitleCount} courses with empty titles</h3>";
    echo "<p>Attempting to generate placeholder titles for these courses...</p>";
    echo "</div>";
    
    // Fix empty titles
    $updateEmptyTitleSql = "UPDATE cours SET title = CONCAT('Course ', course_id) WHERE title = '' OR title IS NULL";
    
    try {
        $conn->exec($updateEmptyTitleSql);
        echo "<div style='background-color: #f0fff4; padding: 15px; border-left: 5px solid #28a745; margin-bottom: 15px;'>";
        echo "<h3 style='color: #28a745; margin-top: 0;'>✓ Fixed {$emptyTitleCount} courses with placeholder titles</h3>";
        echo "<p>Generated placeholder titles for courses that had empty titles.</p>";
        echo "</div>";
    } catch (PDOException $e) {
        echo "<div style='background-color: #fff8f8; padding: 15px; border-left: 5px solid #dc3545; margin-bottom: 15px;'>";
        echo "<h3 style='color: #dc3545; margin-top: 0;'>✗ Failed to fix empty course titles</h3>";
        echo "<p>Error: " . $e->getMessage() . "</p>";
        echo "</div>";
    }
} else {
    echo "<div style='background-color: #f0fff4; padding: 15px; border-left: 5px solid #28a745; margin-bottom: 15px;'>";
    echo "<h3 style='color: #28a745; margin-top: 0;'>✓ No Empty Course Titles</h3>";
    echo "<p>All courses have proper titles assigned.</p>";
    echo "</div>";
}

echo "</div>";

// Closing message
echo "<div style='background-color: #e6f7ff; padding: 20px; border-left: 5px solid #1890ff; margin-top: 20px;'>";
echo "<h2 style='color: #1890ff; margin-top: 0;'>Database Enhancement Complete</h2>";
echo "<p>Your database structure has been enhanced to better support your courses management system.</p>";
echo "<ul>";
echo "<li>Course title field increased to 100 characters</li>";
echo "<li>Empty course IDs fixed with proper format (CRS-XXX)</li>";
echo "<li>Empty course titles updated with placeholder values</li>";
echo "</ul>";
echo "<p><a href='../view/courses.html' style='display: inline-block; background-color: #1890ff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin-top: 10px;'>Return to Courses</a></p>";
echo "</div>";

echo "</div>";

$conn = null;
?>

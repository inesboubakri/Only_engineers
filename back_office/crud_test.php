<?php
// CRUD Test page - for diagnosing issues with database operations
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Test Tool</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 0 auto; padding: 20px; }
        h1, h2 { color: #333; }
        .card { border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin-bottom: 20px; }
        .result { background-color: #f5f5f5; padding: 10px; border-radius: 4px; white-space: pre-wrap; }
        .success { color: green; }
        .error { color: red; }
        form { margin-bottom: 20px; }
        input, select, button { margin: 5px 0; padding: 8px; }
        .response { margin-top: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .action-btn { cursor: pointer; margin-right: 5px; }
    </style>
</head>
<body>
    <h1>Course CRUD Test Tool</h1>
    <p>Use this tool to test the database CRUD operations for courses</p>
    
    <div class="card">
        <h2>Database Connection Test</h2>
        <?php
        // Test database connection
        try {
            require_once '../model/config.php';
            
            if ($conn && ($conn instanceof PDO)) {
                echo "<p class='success'>✓ Database connection successful (PDO)</p>";
                
                // Test a simple query
                $stmt = $conn->query("SELECT 1");
                if ($stmt !== false) {
                    echo "<p class='success'>✓ Query test successful</p>";
                    
                    // Check if the courses table exists
                    $tableCheck = $conn->query("SHOW TABLES LIKE 'cours'");
                    if ($tableCheck && $tableCheck->rowCount() > 0) {
                        echo "<p class='success'>✓ Table 'cours' exists</p>";
                        
                        // Check table structure
                        $columns = $conn->query("DESCRIBE cours");
                        if ($columns) {
                            echo "<p class='success'>✓ Table structure check passed</p>";
                            echo "<h3>Table Structure:</h3>";
                            echo "<table>";
                            echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
                            while ($col = $columns->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
                                echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
                                echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
                                echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
                                echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
                                echo "</tr>";
                            }
                            echo "</table>";
                        } else {
                            echo "<p class='error'>✗ Could not check table structure</p>";
                        }
                    } else {
                        echo "<p class='error'>✗ Table 'cours' does not exist</p>";
                    }
                } else {
                    echo "<p class='error'>✗ Query test failed</p>";
                }
            } else {
                echo "<p class='error'>✗ Database connection failed or not PDO</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
        ?>
    </div>
    
    <div class="card">
        <h2>Create Course</h2>
        <form id="createForm">
            <div>
                <label for="courseId">Course ID:</label>
                <input type="text" id="courseId" name="courseId" placeholder="CRS-XXX (optional)">
            </div>
            <div>
                <label for="courseTitle">Title:</label>
                <input type="text" id="courseTitle" name="courseTitle" required>
            </div>
            <div>
                <label for="courseFees">Fees:</label>
                <input type="text" id="courseFees" name="courseFees" placeholder="0 or amount">
            </div>
            <div>
                <label for="courseLink">Course Link:</label>
                <input type="text" id="courseLink" name="courseLink" placeholder="https://...">
            </div>
            <div>
                <label for="courseCertification">Certification Link:</label>
                <input type="text" id="courseCertification" name="courseCertification" placeholder="https://...">
            </div>
            <div>
                <label for="courseStatus">Status:</label>
                <select id="courseStatus" name="courseStatus">
                    <option value="free">Free</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
            <div>
                <label for="courseIcon">Icon:</label>
                <input type="text" id="courseIcon" name="courseIcon" placeholder="Emoji or icon" value="📚">
            </div>
            <button type="submit">Create Course</button>
        </form>
        <div id="createResponse" class="response"></div>
    </div>
    
    <div class="card">
        <h2>Read Courses</h2>
        <button id="readBtn">Load Courses</button>
        <div id="readResponse" class="response">
            <table id="coursesTable" style="display: none;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Icon</th>
                        <th>Title</th>
                        <th>Fees</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <h2>Update Course</h2>
        <form id="updateForm">
            <div>
                <label for="updateCourseId">Course ID:</label>
                <select id="updateCourseId" name="courseId" required></select>
            </div>
            <div>
                <label for="updateCourseTitle">Title:</label>
                <input type="text" id="updateCourseTitle" name="courseTitle" required>
            </div>
            <div>
                <label for="updateCourseFees">Fees:</label>
                <input type="text" id="updateCourseFees" name="courseFees" placeholder="0 or amount">
            </div>
            <div>
                <label for="updateCourseLink">Course Link:</label>
                <input type="text" id="updateCourseLink" name="courseLink" placeholder="https://...">
            </div>
            <div>
                <label for="updateCourseCertification">Certification Link:</label>
                <input type="text" id="updateCourseCertification" name="courseCertification" placeholder="https://...">
            </div>
            <div>
                <label for="updateCourseStatus">Status:</label>
                <select id="updateCourseStatus" name="courseStatus">
                    <option value="free">Free</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
            <div>
                <label for="updateCourseIcon">Icon:</label>
                <input type="text" id="updateCourseIcon" name="courseIcon" placeholder="Emoji or icon" value="📚">
            </div>
            <button type="submit">Update Course</button>
        </form>
        <div id="updateResponse" class="response"></div>
    </div>
    
    <div class="card">
        <h2>Delete Course</h2>
        <form id="deleteForm">
            <div>
                <label for="deleteCourseId">Course ID:</label>
                <select id="deleteCourseId" name="courseId" required></select>
            </div>
            <button type="submit">Delete Course</button>
        </form>
        <div id="deleteResponse" class="response"></div>
    </div>
    
    <script>
        // Helper function to make CRUD requests
        async function makeCrudRequest(action, formData) {
            const form = new FormData();
            form.append('action', action);
            
            for (const [key, value] of Object.entries(formData)) {
                form.append(key, value);
            }
            
            try {
                const response = await fetch('../controllers/course_crud.php', {
                    method: 'POST',
                    body: form
                });
                
                return await response.json();
            } catch (error) {
                console.error('Error:', error);
                return { status: 'error', message: 'Network error: ' + error.message };
            }
        }
        
        // Create Course form submission
        document.getElementById('createForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                courseId: document.getElementById('courseId').value,
                courseTitle: document.getElementById('courseTitle').value,
                courseFees: document.getElementById('courseFees').value,
                courseLink: document.getElementById('courseLink').value,
                courseCertification: document.getElementById('courseCertification').value,
                courseStatus: document.getElementById('courseStatus').value,
                courseIcon: document.getElementById('courseIcon').value
            };
            
            const responseDiv = document.getElementById('createResponse');
            responseDiv.innerHTML = 'Processing...';
            
            const result = await makeCrudRequest('create', formData);
            
            responseDiv.innerHTML = `
                <div class="${result.status === 'success' ? 'success' : 'error'}">
                    <strong>${result.status === 'success' ? 'Success' : 'Error'}:</strong> ${result.message}
                </div>
                <pre>${JSON.stringify(result, null, 2)}</pre>
            `;
            
            if (result.status === 'success') {
                document.getElementById('createForm').reset();
                document.getElementById('readBtn').click(); // Refresh the course list
            }
        });
        
        // Read Courses button click
        document.getElementById('readBtn').addEventListener('click', async function() {
            const responseDiv = document.getElementById('readResponse');
            responseDiv.innerHTML = 'Loading courses...';
            
            const result = await makeCrudRequest('read', {});
            
            if (result.status === 'success') {
                const table = document.getElementById('coursesTable');
                const tbody = table.querySelector('tbody');
                tbody.innerHTML = '';
                
                if (result.data && result.data.length > 0) {
                    result.data.forEach(course => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${course.course_id}</td>
                            <td>${course.icon || '📚'}</td>
                            <td>${course.title}</td>
                            <td>${course.status === 'free' ? 'Free' : ('$' + parseFloat(course.fees).toFixed(2))}</td>
                            <td>${course.status}</td>
                            <td>
                                <button class="action-btn edit-btn" data-id="${course.course_id}">Edit</button>
                                <button class="action-btn delete-btn" data-id="${course.course_id}">Delete</button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    
                    table.style.display = 'table';
                    responseDiv.innerHTML = '';
                    responseDiv.appendChild(table);
                    
                    // Update course ID dropdowns for update/delete
                    updateCourseDropdowns(result.data);
                    
                    // Add event listeners to edit buttons
                    document.querySelectorAll('.edit-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const courseId = this.getAttribute('data-id');
                            populateUpdateForm(courseId, result.data);
                        });
                    });
                    
                    // Add event listeners to delete buttons
                    document.querySelectorAll('.delete-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const courseId = this.getAttribute('data-id');
                            if (confirm(`Are you sure you want to delete course ${courseId}?`)) {
                                document.getElementById('deleteCourseId').value = courseId;
                                document.getElementById('deleteForm').dispatchEvent(new Event('submit'));
                            }
                        });
                    });
                } else {
                    responseDiv.innerHTML = '<p>No courses found</p>';
                }
            } else {
                responseDiv.innerHTML = `
                    <div class="error">
                        <strong>Error:</strong> ${result.message}
                    </div>
                    <pre>${JSON.stringify(result, null, 2)}</pre>
                `;
            }
        });
        
        // Update Course form submission
        document.getElementById('updateForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                courseId: document.getElementById('updateCourseId').value,
                courseTitle: document.getElementById('updateCourseTitle').value,
                courseFees: document.getElementById('updateCourseFees').value,
                courseLink: document.getElementById('updateCourseLink').value,
                courseCertification: document.getElementById('updateCourseCertification').value,
                courseStatus: document.getElementById('updateCourseStatus').value,
                courseIcon: document.getElementById('updateCourseIcon').value
            };
            
            const responseDiv = document.getElementById('updateResponse');
            responseDiv.innerHTML = 'Processing...';
            
            const result = await makeCrudRequest('update', formData);
            
            responseDiv.innerHTML = `
                <div class="${result.status === 'success' ? 'success' : 'error'}">
                    <strong>${result.status === 'success' ? 'Success' : 'Error'}:</strong> ${result.message}
                </div>
                <pre>${JSON.stringify(result, null, 2)}</pre>
            `;
            
            if (result.status === 'success') {
                document.getElementById('readBtn').click(); // Refresh the course list
            }
        });
        
        // Delete Course form submission
        document.getElementById('deleteForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const courseId = document.getElementById('deleteCourseId').value;
            
            const responseDiv = document.getElementById('deleteResponse');
            responseDiv.innerHTML = 'Processing...';
            
            const result = await makeCrudRequest('delete', { courseId });
            
            responseDiv.innerHTML = `
                <div class="${result.status === 'success' ? 'success' : 'error'}">
                    <strong>${result.status === 'success' ? 'Success' : 'Error'}:</strong> ${result.message}
                </div>
                <pre>${JSON.stringify(result, null, 2)}</pre>
            `;
            
            if (result.status === 'success') {
                document.getElementById('readBtn').click(); // Refresh the course list
            }
        });
        
        // Populate course dropdowns for update and delete
        function updateCourseDropdowns(courses) {
            const updateSelect = document.getElementById('updateCourseId');
            const deleteSelect = document.getElementById('deleteCourseId');
            
            // Clear current options
            updateSelect.innerHTML = '';
            deleteSelect.innerHTML = '';
            
            // Add options for each course
            courses.forEach(course => {
                const updateOption = document.createElement('option');
                updateOption.value = course.course_id;
                updateOption.textContent = `${course.course_id} - ${course.title}`;
                updateSelect.appendChild(updateOption);
                
                const deleteOption = document.createElement('option');
                deleteOption.value = course.course_id;
                deleteOption.textContent = `${course.course_id} - ${course.title}`;
                deleteSelect.appendChild(deleteOption);
            });
        }
        
        // Populate update form with course data
        function populateUpdateForm(courseId, courses) {
            const course = courses.find(c => c.course_id === courseId);
            if (course) {
                document.getElementById('updateCourseId').value = course.course_id;
                document.getElementById('updateCourseTitle').value = course.title;
                document.getElementById('updateCourseFees').value = course.status === 'free' ? '0' : course.fees;
                document.getElementById('updateCourseLink').value = course.course_link;
                document.getElementById('updateCourseCertification').value = course.certification_link;
                document.getElementById('updateCourseStatus').value = course.status;
                document.getElementById('updateCourseIcon').value = course.icon || '📚';
                
                // Scroll to update form
                document.getElementById('updateForm').scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        // Load courses on page load
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('readBtn').click();
        });
    </script>
</body>
</html>
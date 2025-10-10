<!DOCTYPE html>
<html>
<head>
    <title>Debug AI Timesheets</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .btn { padding: 8px 15px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .result { margin: 10px 0; padding: 10px; background: #f8f9fa; border-radius: 3px; white-space: pre-wrap; }
        .error { background: #f8d7da; color: #721c24; }
        .success { background: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <h1>AI Timesheets Debug Tool</h1>
    
    <div class="test-section">
        <h3>Test 1: Check Database Tables</h3>
        <button class="btn" onclick="testTables()">Check Tables</button>
        <div id="tables-result" class="result"></div>
    </div>
    
    <div class="test-section">
        <h3>Test 2: Test Simple API (No JOIN)</h3>
        <button class="btn" onclick="testSimpleAPI()">Test Simple API</button>
        <div id="simple-api-result" class="result"></div>
    </div>
    
    <div class="test-section">
        <h3>Test 3: Test Original API (With JOIN)</h3>
        <button class="btn" onclick="testOriginalAPI()">Test Original API</button>
        <div id="original-api-result" class="result"></div>
    </div>
    
    <div class="test-section">
        <h3>Test 4: Test Frontend JavaScript</h3>
        <button class="btn" onclick="testFrontendJS()">Test Frontend Function</button>
        <div id="frontend-result" class="result"></div>
    </div>

    <script>
        function showResult(elementId, data, isError = false) {
            const element = document.getElementById(elementId);
            element.className = 'result ' + (isError ? 'error' : 'success');
            element.textContent = typeof data === 'object' ? JSON.stringify(data, null, 2) : data;
        }

        function testTables() {
            fetch('/test-no-join')
                .then(response => response.json())
                .then(data => showResult('tables-result', data))
                .catch(error => showResult('tables-result', 'Error: ' + error.message, true));
        }

        function testSimpleAPI() {
            fetch('/api/ai-timesheets/pending-simple')
                .then(response => response.json())
                .then(data => showResult('simple-api-result', data))
                .catch(error => showResult('simple-api-result', 'Error: ' + error.message, true));
        }

        function testOriginalAPI() {
            fetch('/api/ai-timesheets/pending')
                .then(response => response.json())
                .then(data => showResult('original-api-result', data))
                .catch(error => showResult('original-api-result', 'Error: ' + error.message, true));
        }

        function testFrontendJS() {
            // Simulate the frontend function
            fetch('/api/ai-timesheets/pending-simple', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showResult('frontend-result', {
                        message: 'Frontend test successful!',
                        timesheets_count: data.timesheets.length,
                        sample_timesheet: data.timesheets[0] || 'No timesheets found'
                    });
                } else {
                    showResult('frontend-result', 'Frontend test failed: ' + data.message, true);
                }
            })
            .catch(error => showResult('frontend-result', 'Frontend error: ' + error.message, true));
        }

        // Auto-run tests on page load
        window.onload = function() {
            testTables();
            setTimeout(testSimpleAPI, 1000);
            setTimeout(testOriginalAPI, 2000);
            setTimeout(testFrontendJS, 3000);
        };
    </script>
</body>
</html>

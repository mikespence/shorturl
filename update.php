<?php
// update.php – Front End Update Page
// The short code is provided via the URL query parameter.
$code = isset($_GET['code']) ? $_GET['code'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update URL</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="max-w-xl mx-auto p-4 mt-10 bg-white shadow rounded">
    <h1 class="text-2xl font-bold mb-4 text-center">Update Your URL</h1>
    <!-- Display current URL and visit count -->
    <div id="current-details" class="mb-4 text-center font-medium text-gray-700"></div>
    
    <!-- Form for updating: New URL field left blank -->
    <form id="update-form" class="space-y-4">
      <div>
        <label class="block text-gray-700">New URL:</label>
        <input type="url" name="new_url" class="w-full border rounded p-2" placeholder="Enter new URL" required>
      </div>
      <div>
        <label class="block text-gray-700">Email:</label>
        <input type="email" name="email" class="w-full border rounded p-2" placeholder="Enter your email" required>
      </div>
      <div>
        <label class="block text-gray-700">Passcode:</label>
        <input type="password" name="passcode" class="w-full border rounded p-2" placeholder="Enter your passcode" required>
      </div>
      <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded">Update URL</button>
    </form>
    <div id="result" class="mt-4"></div>
  </div>
  
  <script>
    // Inject the short code from PHP into JavaScript.
    const shortCode = "<?php echo htmlspecialchars($code); ?>";
    
    if (!shortCode) {
      document.getElementById("result").innerHTML = '<p class="text-red-500">No code provided in URL.</p>';
    } else {
      // Fetch the current URL details from process_update.php via GET.
      fetch(`/process_update.php?code=${shortCode}`)
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            document.getElementById("result").innerHTML = `<p class="text-red-500">${data.error}</p>`;
          } else {
            // Display the current URL and visit count.
            document.getElementById("current-details").innerHTML = 
              `<strong>Current URL:</strong> ${data.original_url}<br>
               <strong>Visits:</strong> ${data.visit_count}`;
            // Leave the "New URL" field blank for user input.
          }
        })
        .catch(error => {
          console.error("Error fetching current details:", error);
          document.getElementById("result").innerHTML = '<p class="text-red-500">Error fetching data.</p>';
        });
    }
    
    // Handle form submission via fetch POST request.
    document.getElementById("update-form").addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      
      fetch(`/process_update.php?code=${shortCode}`, {
        method: "POST",
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        const resultDiv = document.getElementById("result");
        if (data.error) {
          resultDiv.innerHTML = `<p class="text-red-500">${data.error}</p>`;
        } else if (data.message) {
          resultDiv.innerHTML = `<p class="text-green-600">${data.message}</p>`;
        }
      })
      .catch(error => {
        console.error("Error updating URL:", error);
        document.getElementById("result").innerHTML = '<p class="text-red-500">Error updating URL.</p>';
      });
    });
  </script>
</body>
</html>
<?php
// update.php – Front End Update Page
$code = isset($_GET['code']) ? $_GET['code'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Your URL</title>
  <!-- Google Font -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-500 to-purple-700 flex items-center justify-center">
  <div class="w-full max-w-lg mx-auto bg-white p-8 rounded-xl shadow-lg">
    <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Update Your URL</h1>
    
    <!-- Display current URL and visit count -->
    <div id="current-details" class="mb-4 text-center text-gray-700"></div>
    
    <!-- Update Form -->
    <form id="update-form" class="space-y-5">
      <div>
        <label for="new_url" class="block text-gray-700 font-semibold mb-1">New URL</label>
        <input type="url" id="new_url" name="new_url" placeholder="Enter your new URL" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>
      <div>
        <label for="email" class="block text-gray-700 font-semibold mb-1">Email</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>
      <div>
        <label for="passcode" class="block text-gray-700 font-semibold mb-1">Passcode</label>
        <input type="password" id="passcode" name="passcode" placeholder="Enter your passcode" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
      </div>
      <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-md transition duration-300">Update URL</button>
    </form>
    
    <!-- Result Message -->
    <div id="result" class="mt-6 text-center"></div>
    
    <!-- QR Code Container -->
    <div id="qr-code-container" class="mt-8 text-center"></div>
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
              `<p class="mb-1"><strong>Current URL:</strong> <span class="text-blue-600">${data.original_url}</span></p>
               <p class="text-sm text-gray-600"><strong>Visits:</strong> ${data.visit_count}</p>`;
            // Generate the short URL and corresponding QR code URL.
            const shortURL = window.location.protocol + '//' + window.location.host + '/' + shortCode;
            // You can either generate locally or use an external API; here's the external API version:
            const qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" + encodeURIComponent(shortURL) + "&size=300x300";
            document.getElementById("qr-code-container").innerHTML = `<img src="${qrCodeUrl}" alt="QR Code" class="mx-auto rounded-lg shadow-md">`;
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
<?php
// update.php – Front End Update Page (split-screen design)
$code = isset($_GET['code']) ? $_GET['code'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Your URL</title>
  <!-- Google Font: Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    /* For a subtle overlay on the left panel */
    .overlay {
      background: rgba(0, 0, 0, 0.4);
    }
  </style>
</head>
<body class="min-h-screen bg-gray-100">
  <!-- Container: Flex column on mobile, row on medium screens and up -->
  <div class="flex flex-col md:flex-row min-h-screen">
  
    <!-- Left Panel: Current details, QR code, short URL -->
    <div class="md:w-1/2 relative flex items-center justify-center p-8 text-white" style="background: url('https://source.unsplash.com/random/800x1200?technology') no-repeat center/cover;">
      <!-- Overlay -->
      <div class="absolute inset-0 overlay"></div>
      <!-- Content -->
      <div class="relative z-10 text-center space-y-6">
        <h1 class="text-4xl font-bold">Your Short URL</h1>
        <div id="current-details">
          <!-- Filled dynamically -->
          <p class="text-xl"><strong>Current URL:</strong> <span id="current-url" class="text-blue-300"></span></p>
          <p class="text-sm">Visits: <span id="visit-count" class="text-blue-300"></span></p>
        </div>
        <div id="copy-url-container">
          <!-- Copy-to-clipboard section -->
        </div>
        <div id="qr-code-container" class="mt-4">
          <!-- QR Code appears here -->
        </div>
      </div>
    </div>
    
    <!-- Right Panel: Update form -->
    <div class="md:w-1/2 flex items-center justify-center p-8 bg-white">
      <div class="w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Update Your URL</h2>
        <form id="update-form" class="space-y-5">
          <div>
            <label for="new_url" class="block text-gray-700 font-medium mb-1">New URL</label>
            <input type="url" id="new_url" name="new_url" placeholder="Enter your new URL" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-400">
          </div>
          <div>
            <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter your email" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-400">
          </div>
          <div>
            <label for="passcode" class="block text-gray-700 font-medium mb-1">Passcode</label>
            <input type="password" id="passcode" name="passcode" placeholder="Enter your passcode" required class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-indigo-400">
          </div>
          <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded transition duration-300">Update URL</button>
        </form>
        <div id="result" class="mt-6 text-center"></div>
      </div>
    </div>
    
  </div>
  
  <script>
    // Inject the short code from PHP into JavaScript.
    const shortCode = "<?php echo htmlspecialchars($code); ?>";
    const currentUrlSpan = document.getElementById("current-url");
    const visitCountSpan = document.getElementById("visit-count");
    const copyUrlContainer = document.getElementById("copy-url-container");
    const qrCodeContainer = document.getElementById("qr-code-container");
    const resultDiv = document.getElementById("result");
    const form = document.getElementById("update-form");
    
    if (!shortCode) {
      resultDiv.innerHTML = '<p class="text-red-500">No code provided in URL.</p>';
    } else {
      // Fetch current URL details (and visit count) from process_update.php via GET.
      fetch(`/process_update.php?code=${shortCode}`)
        .then(response => response.json())
        .then(data => {
          if (data.error) {
            resultDiv.innerHTML = `<p class="text-red-500">${data.error}</p>`;
          } else {
            currentUrlSpan.textContent = data.original_url;
            visitCountSpan.textContent = data.visit_count;
            
            // Build short URL and copy-to-clipboard section.
            const shortURL = window.location.protocol + '//' + window.location.host + '/' + shortCode;
            copyUrlContainer.innerHTML = `
              <div class="flex items-center justify-center space-x-2">
                <span>Short URL:</span>
                <input id="short-url" type="text" value="${shortURL}" readonly class="w-56 px-2 py-1 border border-gray-300 rounded focus:outline-none">
                <button id="copy-btn" class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded transition">Copy</button>
              </div>`;
            document.getElementById("copy-btn").addEventListener("click", function() {
              const copyText = document.getElementById("short-url");
              copyText.select();
              navigator.clipboard.writeText(copyText.value).then(() => {
                this.innerText = "Copied!";
                setTimeout(() => { this.innerText = "Copy"; }, 2000);
              });
            });
            
            // Generate QR code using external API.
            const qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" + encodeURIComponent(shortURL) + "&size=300x300";
            qrCodeContainer.innerHTML = `<img src="${qrCodeUrl}" alt="QR Code" class="mx-auto rounded-md shadow-md transition-transform duration-300 hover:scale-105">`;
          }
        })
        .catch(error => {
          console.error("Error fetching details:", error);
          resultDiv.innerHTML = '<p class="text-red-500">Error fetching data.</p>';
        });
    }
    
    // Handle form submission via fetch POST.
    form.addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new FormData(form);
      
      fetch(`/process_update.php?code=${shortCode}`, {
        method: "POST",
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.error) {
          resultDiv.innerHTML = `<p class="text-red-500">${data.error}</p>`;
        } else if (data.message) {
          resultDiv.innerHTML = `<p class="text-green-600">${data.message}</p>`;
        }
      })
      .catch(error => {
        console.error("Error updating URL:", error);
        resultDiv.innerHTML = '<p class="text-red-500">Error updating URL.</p>';
      });
    });
  </script>
</body>
</html>
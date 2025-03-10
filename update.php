<?php
// update.php – Front End Update Page (Split-Screen Purple + White Layout)
$code = isset($_GET['code']) ? $_GET['code'] : '';
// update.php
require 'header.php';
printHeader("Update Your URL");

// Then your split-screen layout code here...
?>

<!-- Your existing update page code (the left purple panel showing the short URL, etc. and the right panel with the form) -->

  <!-- Container: Splits into two panels on md+ screens -->
  <div class="flex flex-col md:flex-row min-h-screen">

    <!-- Left Panel (Purple Background) -->
    <div class="md:w-1/2 bg-gradient-to-br from-purple-600 to-purple-900 text-white flex flex-col items-center justify-center p-8">
      <!-- Title -->
      <h1 class="text-3xl font-bold mb-6">Your Short URL</h1>
      
      <!-- Current URL Details -->
      <div id="current-details" class="text-center space-y-2 mb-6">
        <!-- Filled dynamically: 
             1) Current URL 
             2) Visits -->
      </div>

      <!-- Copy-to-Clipboard Container -->
      <div id="copy-url-container" class="mb-6">
        <!-- Filled dynamically: 
             Short URL + Copy Button -->
      </div>

      <!-- QR Code Container -->
      <div id="qr-code-container">
        <!-- Filled dynamically: QR Code -->
      </div>

      <!-- Spinner for loading data -->
      <div id="spinner" class="mt-6 hidden">
        <div class="spinner"></div>
      </div>
    </div>

    <!-- Right Panel (White Background) -->
    <div class="md:w-1/2 bg-white flex items-center justify-center p-8">
      <div class="w-full max-w-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Update Your URL</h2>
        
        <!-- Update Form -->
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
          <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded transition duration-300">
            Update URL
          </button>
        </form>

        <!-- Result / Error Messages -->
        <div id="result" class="mt-6 text-center"></div>
      </div>
    </div>

  </div>
  
  <script>
    const shortCode = "<?php echo htmlspecialchars($code); ?>";
    const currentDetailsDiv = document.getElementById("current-details");
    const copyUrlContainer = document.getElementById("copy-url-container");
    const qrCodeContainer = document.getElementById("qr-code-container");
    const resultDiv = document.getElementById("result");
    const spinner = document.getElementById("spinner");
    const form = document.getElementById("update-form");

    if (!shortCode) {
      resultDiv.innerHTML = '<p class="text-red-500">No code provided in URL.</p>';
    } else {
      // Show spinner while fetching
      spinner.classList.remove("hidden");

      // Fetch current URL details (and visit count) from process_update.php via GET
      fetch(`/process_update.php?code=${shortCode}`)
        .then(response => response.json())
        .then(data => {
          spinner.classList.add("hidden");
          if (data.error) {
            resultDiv.innerHTML = `<p class="text-red-500">${data.error}</p>`;
          } else {
            // Display current URL and visit count
            currentDetailsDiv.innerHTML = `
              <p class="text-lg">
                <strong>Current URL:</strong> 
                <a href="${data.original_url}" target="_blank" class="text-lime-300 underline">
                  ${data.original_url}
                </a>
              </p>
              <p class="text-sm">
                Visits: <span class="text-lime-300">${data.visit_count}</span>
              </p>
            `;

            // Create short URL and a copy-to-clipboard input
            const shortURL = window.location.protocol + '//' + window.location.host + '/' + shortCode;
            copyUrlContainer.innerHTML = `
              <div class="flex items-center justify-center space-x-2">
                <span class="font-medium">Short URL:</span>
                <input id="short-url" type="text" value="${shortURL}" readonly 
                       class="w-56 px-2 py-1 border border-gray-300 rounded text-gray-800 focus:outline-none">
                <button id="copy-btn" 
                        class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded transition">
                  Copy
                </button>
              </div>
            `;
            document.getElementById("copy-btn").addEventListener("click", function() {
              const copyText = document.getElementById("short-url");
              copyText.select();
              navigator.clipboard.writeText(copyText.value).then(() => {
                this.innerText = "Copied!";
                setTimeout(() => { this.innerText = "Copy"; }, 2000);
              });
            });

            // Generate QR Code using external API
            const qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" + encodeURIComponent(shortURL) + "&size=300x300";
            qrCodeContainer.innerHTML = `
              <img src="${qrCodeUrl}" alt="QR Code" 
                   class="mx-auto rounded-md shadow-md transition-transform duration-300 hover:scale-105">
            `;
          }
        })
        .catch(error => {
          spinner.classList.add("hidden");
          console.error("Error fetching details:", error);
          resultDiv.innerHTML = '<p class="text-red-500">Error fetching data.</p>';
        });
    }

    // Handle form submission via POST
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

<?php
printFooter();
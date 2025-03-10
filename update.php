<?php
// update.php – QR on the left, details on the right
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
    body {
      font-family: 'Inter', sans-serif;
    }
    .spinner {
      border: 4px solid rgba(0, 0, 0, 0.1);
      width: 48px;
      height: 48px;
      border-radius: 50%;
      border-left-color: #10B981; /* Green-500 */
      animation: spin 1s linear infinite;
      margin: auto;
    }
    @keyframes spin {
      to { transform: rotate(360deg); }
    }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-green-200 to-green-50 flex flex-col items-center justify-center">

  <!-- Top navigation (optional) -->
  <header class="absolute top-0 w-full flex justify-end items-center p-6">
    <div class="space-x-6 text-gray-700 text-xl">
      <a href="#" class="hover:underline">Login</a>
      <a href="#" class="hover:underline">Sign up</a>
    </div>
  </header>

  <!-- Main Container -->
  <div class="w-full max-w-5xl px-6 flex flex-col items-center">
    <!-- Heading -->
    <h1 class="text-7xl font-bold text-green-700 mb-12 text-center">NiceLink</h1>
    
    <!-- White Card: QR on left, details on right -->
    <div class="w-full max-w-3xl bg-white bg-opacity-80 rounded-3xl p-8 mb-12 shadow-lg">
      <div id="spinner" class="hidden mb-6 flex justify-center">
        <div class="spinner"></div>
      </div>
      <div class="flex flex-col md:flex-row items-center md:items-start justify-center space-y-6 md:space-y-0 md:space-x-8">
        
        <!-- QR Code on Left -->
        <div id="qr-code-container" class="flex justify-center md:w-1/3">
          <!-- Filled dynamically -->
        </div>
        
        <!-- Details on Right -->
        <div class="md:w-2/3 space-y-4 text-center" id="details-right">
          <div id="current-details" class="space-y-2">
            <!-- Current URL, visits -->
          </div>
          <div id="copy-url-container">
            <!-- Short URL + copy button -->
          </div>
          <div id="details-error" class="text-red-500"></div>
        </div>
      </div>
    </div>

    <!-- Update Form (scaled up for bigger fields) -->
    <div class="transform scale-110 w-full max-w-3xl">
      <form id="update-form" class="space-y-10">
        <!-- Large row for New URL -->
        <div class="w-full flex items-center relative">
          <input 
            type="url" 
            name="new_url" 
            placeholder="Enter your new URL" 
            required
            class="w-full rounded-full border-4 border-green-500 px-8 py-5 text-3xl focus:outline-none focus:ring-4 focus:ring-green-400 transition"
          />
        </div>

        <!-- Side-by-Side Email & Passcode -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full">
          <!-- Email Field -->
          <div class="flex flex-col">
            <label for="email" class="text-gray-700 font-semibold text-2xl mb-3">Email</label>
            <input 
              type="email" 
              id="email" 
              name="email" 
              placeholder="Enter your email" 
              required
              class="rounded-full border-4 border-green-300 px-6 py-4 text-2xl focus:outline-none focus:ring-4 focus:ring-green-400 transition"
            />
          </div>
          <!-- Passcode Field -->
          <div class="flex flex-col">
            <label for="passcode" class="text-gray-700 font-semibold text-2xl mb-3">Passcode</label>
            <input 
              type="password" 
              id="passcode" 
              name="passcode" 
              placeholder="Enter your passcode"
              required
              class="rounded-full border-4 border-green-300 px-6 py-4 text-2xl focus:outline-none focus:ring-4 focus:ring-green-400 transition"
            />
          </div>
        </div>

        <!-- Update Button -->
        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-5 rounded-full text-3xl transition">
          Update
        </button>
      </form>
      <div id="result" class="mt-6 text-center"></div>
    </div>

  </div>

  <script>
    const shortCode = "<?php echo htmlspecialchars($code); ?>";
    const spinner = document.getElementById("spinner");
    const currentDetailsDiv = document.getElementById("current-details");
    const copyUrlContainer = document.getElementById("copy-url-container");
    const qrCodeContainer = document.getElementById("qr-code-container");
    const detailsErrorDiv = document.getElementById("details-error");
    const updateForm = document.getElementById("update-form");
    const resultDiv = document.getElementById("result");

    if (!shortCode) {
      detailsErrorDiv.innerText = "No code provided in URL.";
    } else {
      spinner.classList.remove("hidden");
      fetch(`/process_update.php?code=${shortCode}`)
        .then(response => response.json())
        .then(data => {
          spinner.classList.add("hidden");
          if (data.error) {
            detailsErrorDiv.innerText = data.error;
          } else {
            // Current URL & Visits
            currentDetailsDiv.innerHTML = `
              <p class="text-2xl"><strong>Current URL:</strong>
                <a href="${data.original_url}" target="_blank" class="text-green-600 underline">${data.original_url}</a>
              </p>
              <p class="text-xl">Visits: <span class="text-green-600">${data.visit_count}</span></p>
            `;
            // Short URL + copy
            const shortURL = window.location.protocol + '//' + window.location.host + '/' + shortCode;
            copyUrlContainer.innerHTML = `
              <div class="flex items-center justify-center space-x-4">
                <span class="font-medium text-xl">Short URL:</span>
                <input id="short-url" type="text" value="${shortURL}" readonly
                       class="w-64 px-3 py-2 border border-gray-300 rounded focus:outline-none text-gray-800 text-xl" />
                <button id="copy-btn"
                        class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded text-xl transition">
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
            // QR Code
            qrCodeContainer.innerHTML = `
              <img src="${data.qr_code}" alt="QR Code"
                   class="mx-auto rounded-md shadow-md transition-transform duration-300 hover:scale-105" />
            `;
          }
        })
        .catch(error => {
          spinner.classList.add("hidden");
          detailsErrorDiv.innerText = "Error fetching data.";
          console.error(error);
        });
    }

    // Handle Update Submission
    updateForm.addEventListener("submit", function(e) {
      e.preventDefault();
      const formData = new FormData(updateForm);
      fetch(`/process_update.php?code=${shortCode}`, {
        method: "POST",
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.error) {
          resultDiv.innerHTML = `<p class="text-red-500">${data.error}</p>`;
        } else if (data.message) {
          resultDiv.innerHTML = `<p class="text-green-700 text-2xl">${data.message}</p>`;
        }
      })
      .catch(error => {
        console.error(error);
        resultDiv.innerHTML = '<p class="text-red-500">Error updating URL.</p>';
      });
    });
  </script>

</body>
</html>
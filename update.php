<?php
// update.php
// We'll assume there's a "code" parameter in the URL, e.g. update.php?code=ABC123
$short_code = isset($_GET['code']) ? $_GET['code'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ShortQR</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    body {
      font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      background: linear-gradient(135deg, #d4f2cc, #e8fce9);
    }
  </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center">
  <div class="max-w-3xl w-full bg-white rounded-xl shadow-lg p-8">
    <h1 class="text-4xl font-bold text-center text-green-700 mb-8">ShortQR</h1>

    <!-- This is where we display the info loaded via JS -->
    <div id="info-container" class="mb-6">
      <!-- We will populate this dynamically with the data from process_update.php?code=SHORTCODE -->
      <p id="loading-msg" class="text-center text-gray-600">Loading info...</p>
    </div>

    <!-- Short URL + Copy Button -->
  </div>

  <!-- JavaScript to load info from process_update.php and populate the page -->
  <script>
    const shortCode = "<?= htmlspecialchars($short_code) ?>";
    if (!shortCode) {
      document.getElementById('loading-msg').textContent = "No code provided.";
    } else {
      // Fetch the data from process_update.php?code=SHORTCODE (GET request)
      fetch(`/process_update.php?code=${shortCode}`)
        .then(response => response.json())
        .then(data => {
          const infoContainer = document.getElementById('info-container');
          const loadingMsg = document.getElementById('loading-msg');
          loadingMsg.remove(); // Remove the "Loading info..." message
          console.log(data);
          if (data.error) {
            infoContainer.innerHTML = `<p class="text-red-500 text-center">${data.error}</p>`;
            return;
          }

          // data.original_url, data.visit_count, data.qr_code
          // Create a nice layout for the QR code + details
          infoContainer.innerHTML = `
            <div class=" items-center md:items-start md:space-x-6 space-y-6 md:space-y-0 justify-center">
              <!-- QR Code + Download -->
              <div class="flex flex-col items-center space-y-3 mb-5">
                <img src="${data.qr_code}" alt="QR Code" class="w-40 h-40 rounded-md shadow-md" />
                <a href="${data.qr_code}" download="${shortCode}.png" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded transition text-lg">
                  <i class="fa-solid fa-download mr-2"></i>Download QR
                </a>
              </div>
              <!-- Details -->
              <div class="space-y-2 text-center md:text-left">
                <p class="text-lg text-lg truncate w-full block">
                  <span class="font-semibold">Redirecting to: </span>
                  <a href="${data.original_url}" target="_blank" class="text-green-600 underline">${data.original_url}</a>
                </p>
                <p class="text-lg"><span class="font-semibold">Visits:</span> <span class="text-green-600 font-semibold">${data.visit_count}</span></p>
                <p class="text-lg font-semibold">Short URL</p>
                <div class="flex items-center space-x-2">
                  <input
                    id="short-url"
                    type="text"
                    value="${data.short_url}"
                    readonly
                    class="w-full py-2 border-none border-gray-300 rounded focus:outline-none text-gray-800 text-lg"
                  />
                  <button
                    id="copy-btn"
                    class="inline-flex items-center px-4 py-1 bg-green-600 hover:bg-green-700 text-white rounded transition text-lg"
                  >
                    <i class="fa-regular fa-copy mr-2"></i>Copy
                  </button>
                </div>
              </div>
            </div>
          `;

          // Also populate the short URL in the input for copying
          const shortUrlSection = document.getElementById('short-url-section');
          const shortUrlInput = document.getElementById('short-url');
          // We'll guess the short URL is your domain + shortCode
          // Or you can parse data if you prefer
          const shortUrl = data.short_url;
          shortUrlInput.value = shortUrl;
        })
        .catch(err => {
          document.getElementById('info-container').innerHTML = `<p class="text-red-500 text-center">Error loading info.</p>`;
          console.error(err);
        });
    }

    // Handle the copy button
    document.addEventListener("click", function(e) {
      const btn = e.target.closest("#copy-btn");
      if (btn) {
        const copyText = document.getElementById("short-url");
        navigator.clipboard.writeText(copyText.value).then(() => {
          btn.innerHTML = '<i class="fa-regular fa-copy mr-2"></i>Copied!';
          setTimeout(() => { btn.innerHTML = '<i class="fa-regular fa-copy mr-2"></i>Copy'; }, 2000);
        }).catch(err => {
          console.error('Failed to copy:', err);
        });
      }
    });
  </script>
</body>
</html>
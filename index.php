<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>URL Shortener SPA</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="max-w-xl mx-auto p-4 mt-10 bg-white shadow rounded">
    <h1 class="text-2xl font-bold mb-4 text-center">URL Shortener</h1>
    <form id="shorten-form" class="space-y-4">
      <div>
        <label class="block text-gray-700">Original URL:</label>
        <input type="url" name="original_url" class="w-full border rounded p-2" required>
      </div>
      <div>
        <label class="block text-gray-700">Email (for updates):</label>
        <input type="email" name="email" class="w-full border rounded p-2" required>
      </div>
      <div>
        <label class="block text-gray-700">Custom Short Code (optional):</label>
        <input type="text" name="custom_code" class="w-full border rounded p-2">
      </div>
      <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded">Shorten URL</button>
    </form>
    <div id="result" class="mt-4"></div>
  </div>
  
  <script>
    document.getElementById("shorten-form").addEventListener("submit", function(e) {
      e.preventDefault();
      
      const form = e.target;
      const formData = new FormData(form);
      
      fetch("process.php", {
        method: "POST",
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        const resultDiv = document.getElementById("result");
        if (data.error) {
          resultDiv.innerHTML = `<p class="text-red-500">${data.error}</p>`;
        } else {
          // Hide the form once the link has been created
          form.style.display = "none";
          resultDiv.innerHTML = `
            <p class="mb-4 text-center">
              <a href="${data.short_url}" class="text-blue-500 underline">${data.short_url}</a>
            </p>
            <img src="${data.qr_code_url}" alt="QR Code" class="mx-auto mb-4">
            <p class="text-gray-600 text-center">Your URL has been shortened!</p>
            <p class="text-gray-500 text-center">Update your URL later at <a href="${data.update_url}" class="underline">${data.update_url}</a></p>
            <button id="new-link" class="mt-4 w-full bg-green-500 text-white p-2 rounded">Create Another Link</button>
          `;
          
          // Set up the "Create Another Link" button to show the form again
          document.getElementById("new-link").addEventListener("click", function() {
            // Clear the result div
            resultDiv.innerHTML = "";
            // Show and reset the form
            form.style.display = "block";
            form.reset();
          });
        }
      })
      .catch(err => {
        console.error(err);
        document.getElementById("result").innerHTML = `<p class="text-red-500">An error occurred.</p>`;
      });
    });
  </script>
</body>
</html>
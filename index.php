<?php
// index.php – The landing page for creating a short URL.
require 'header.php'; // Load our shared header
printHeader("Create a Short URL"); // Start the HTML, pass the page title

// We’ll do a split-screen: left side is a purple gradient with some branding/info,
// right side is the form for creating the short URL.
?>
<div class="flex flex-col md:flex-row min-h-screen">

  <!-- Left Panel (Purple) -->
  <!-- Left Panel (Purple Gradient) -->
  <div class="md:w-1/2 bg-gradient-to-br from-purple-600 to-purple-900 text-white flex flex-col items-center justify-center p-8">
    <div class="max-w-md text-center space-y-6">
      <h1 class="text-4xl font-bold">Welcome to NiceLink</h1>
      <p class="text-lg text-purple-100">
        Create short, memorable links with a custom code, track visits, and manage updates easily.
      </p>
      <!-- Optional brand graphic or illustration -->
      <img src="https://source.unsplash.com/400x300/?url,shortener" alt="Brand" class="mx-auto rounded shadow-md" />
    </div>
  </div>

  <!-- Right Panel (White) -->
  <div class="md:w-1/2 bg-white flex items-center justify-center p-8">
    <div class="w-full max-w-md">
      <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center">Create a Short URL</h2>
      <form id="shorten-form" class="space-y-4">
        <div>
          <label for="original_url" class="block text-gray-700 text-lg font-semibold mb-2">Long URL</label>
          <input type="url" id="original_url" name="original_url" placeholder="https://MyVeryLongLink.com/this/that/the-other" required
                 class="w-full px-5 py-4 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" />
        </div>
        <div>
          <label for="email" class="block text-gray-700 text-lg font-semibold mb-2">Email (so you can make changes)</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required
                 class="w-full px-5 py-4 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" />
        </div>
        <div>
          <label for="custom_code" class="block text-gray-700 text-lg font-semibold mb-2">Custom Short Code (optional)</label>
          <input type="text" id="custom_code" name="custom_code" placeholder="e.g. mylink"
                 class="w-full px-5 py-4 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all" />
        </div>
        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-full transition duration-300 text-xl">
          Create Short URL
        </button>
      </form>
      <div id="result" class="mt-4"></div>
      <!-- If you want to display any success/error message, you can do so below:
           (In this example, process.php will redirect or return JSON, so you might handle that differently) -->
    </div>
  </div>

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
        console.log(data);
        // Hide the form once the link has been created
        form.style.display = "none";
        resultDiv.innerHTML = `
          <p class="mb-4 text-center">
            <a href="${data.short_url}" class="text-blue-500 underline">${data.short_url}</a>
          </p>
          <img src="${data.qr_code}" alt="QR Code" class="mx-auto mb-4">
          <p class="text-gray-600 text-center">Your URL has been shortened!</p>
          <p class="text-gray-500 text-center">Update your URL later at <a href="${data.update_url}" class="underline">${data.update_url}</a></p>
          <button id="new-link" class="mt-4 w-full bg-green-500 text-white py-4 rounded-full transition duration-300 text-xl">Create Another Link</button>
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
<?php
printFooter(); // Close out the HTML from footer.php
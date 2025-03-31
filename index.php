<?php
// index.php – Green Color Scheme with Bigger Form Fields
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create a Short URL</title>
  <!-- Google Font: Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-green-200 to-green-50 flex flex-col items-center justify-center">

  <!-- Main Container -->
  <div class="w-full max-w-5xl px-6 flex flex-col items-center">
    <!-- Branding or Big Logo -->
    <h1 class="text-7xl font-bold text-green-700 mb-16 text-center">ShortQR</h1>
    
    <!-- Form Container (scaled up 10%) -->
    <div class="transform scale-110">
      <form id="shorten-form" class="space-y-4">
        <!-- Big URL Input with Large Button -->
        <div class="w-full flex items-center relative">
          <input 
            type="url" 
            name="original_url" 
            placeholder="Enter your link here" 
            required
            class="w-full rounded-full border-4 border-green-500 px-8 py-6 text-3xl focus:outline-none focus:ring-4 focus:ring-green-400 transition"
          />
          <button 
            type="submit" 
            class="absolute right-0 mr-2 bg-green-600 hover:bg-green-700 text-white font-bold px-12 py-5 rounded-full text-3xl transition duration-300"
          >
            Create
          </button>
        </div>
        
        <!-- Side-by-Side Email & Custom Code Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 w-full">
          <!-- Email Field -->
          <div class="flex flex-col">
            <label for="email" class="text-gray-700 font-semibold text-2xl mb-3">Email (to make changes)</label>
            <input 
              type="email" 
              id="email" 
              name="email" 
              placeholder="Enter your email" 
              required
              class="rounded-full border-4 border-gray-300 px-6 py-4 text-2xl focus:outline-none focus:ring-4 focus:ring-green-400 transition"
            />
          </div>
          <!-- Custom Short Code Field with Prefix -->
          <div class="flex flex-col">
            <label for="custom_code" class="text-gray-700 font-semibold text-2xl mb-3">Custom Short Code (optional)</label>
            <div class="flex items-center rounded-full border-4 border-gray-300 px-6 py-4 focus-within:ring-4 focus-within:ring-green-400 transition">
              <span class="text-gray-600 text-2xl">shortQR.app/</span>
              <input 
                type="text" 
                id="custom_code" 
                name="custom_code" 
                placeholder="mylink"
                class="ml-4 flex-1 bg-transparent focus:outline-none text-2xl text-gray-800"
              />
            </div>
          </div>
        </div>
      </form>
      <div id="result" class="mt-4"></div>
    </div>

    <!-- SEO Copy Section -->
    <section class="mt-16 text-center max-w-3xl">
      <h2 class="text-4xl font-bold text-green-700 mb-4">The Best Free URL Shortener</h2>
      <p class="text-xl text-gray-700">
        ShortQR offers an easy, fast, and reliable way to shorten your URLs. Whether you're sharing on social media, in emails, or on your website, our service lets you create custom short links that are both memorable and trackable. Enjoy real-time analytics and the freedom to update your destination anytime. Try ShortQR today and transform your long URLs into powerful, branded links!
      </p>
    </section>

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
</body>
</html>
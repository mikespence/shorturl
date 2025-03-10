<?php
// index.php – Larger input, side-by-side email & custom code fields
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
<body class="bg-white min-h-screen flex flex-col items-center justify-center">

  <!-- Top navigation / branding (optional) -->
  <header class="absolute top-0 w-full flex justify-end items-center p-4">
    <div class="space-x-4 text-gray-700">
      <a href="#" class="hover:underline">Login</a>
      <a href="#" class="hover:underline">Sign up</a>
    </div>
  </header>

  <!-- Main Container -->
  <div class="w-full max-w-4xl px-4 flex flex-col items-center">

    <!-- Branding or Big Logo -->
    <h1 class="text-6xl font-bold text-indigo-700 mb-12 text-center">NiceLink</h1>
    
    <!-- Form for creating a short URL -->
    <form action="process.php" method="POST" class="w-full flex flex-col items-center space-y-8">
      
      <!-- Large URL Input -->
      <div class="w-full flex items-center relative">
        <input 
          type="url" 
          name="original_url" 
          placeholder="Enter link here" 
          required
          class="w-full rounded-full border-2 border-indigo-500 px-6 py-5 text-2xl focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
        />
        <button 
          type="submit" 
          class="absolute right-0 mr-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-10 py-4 rounded-full text-2xl transition duration-300"
        >
          Shorten
        </button>
      </div>
      
      <!-- Side-by-side Email & Custom Code -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 w-full">
        <!-- Email Field -->
        <div class="flex flex-col">
          <label for="email" class="text-gray-700 font-semibold mb-2 text-lg">Email (for updates)</label>
          <input 
            type="email" 
            id="email" 
            name="email" 
            placeholder="Enter your email" 
            required
            class="rounded-full border border-gray-300 px-4 py-3 text-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
          />
        </div>
        
        <!-- Custom Short Code Field with prefix -->
        <div class="flex flex-col">
          <label for="custom_code" class="text-gray-700 font-semibold mb-2 text-lg">Custom Short Code (optional)</label>
          <!-- A container that includes the prefix and the text input -->
          <div class="flex items-center rounded-full border border-gray-300 px-4 py-3 focus-within:ring-2 focus-within:ring-indigo-400 transition">
            <span class="text-gray-600">nicelink.co.uk/</span>
            <input 
              type="text" 
              id="custom_code" 
              name="custom_code" 
              placeholder="e.g. mylink"
              class="ml-2 flex-1 focus:outline-none text-lg"
            />
          </div>
        </div>
      </div>
    </form>

    <!-- Optional Info / Features -->
    <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
      <div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Custom URL</h3>
        <p class="text-gray-600">
          Create a unique and meaningful short link.  
          Specify the alias to reflect your brand or content.
        </p>
      </div>
      <div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Analytics</h3>
        <p class="text-gray-600">
          Track how many times your link was visited.  
          Gain insights into user engagement.
        </p>
      </div>
      <div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">Easy Updates</h3>
        <p class="text-gray-600">
          Update your short URL anytime.  
          Just use your email and passcode to change the destination.
        </p>
      </div>
    </div>

  </div>

</body>
</html>
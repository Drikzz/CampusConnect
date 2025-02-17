<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <title>Campus Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
    <link
      href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css"
      rel="stylesheet"
    />
    <style>
      .active {
        background-color: #e54646; /* indigo-600 */
        color: white;
        transform: translateY(-0.25rem); /* -translate-y-1 */
      }
      .sidebar div:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        left: 100%;
        margin-left: 10px;
        padding: 5px 10px;
        background-color: #e54646;
        color: white;
        border-radius: 5px;
        white-space: nowrap;
        z-index: 50;
      }
      .sidebar {
        z-index: 50;
        position: sticky;
        top: 0;
        height: 100vh;
      }
      .navbar {
        position: sticky;
        top: 0;
        z-index: 40;
      }
    </style>
  </head>
  <body class="bg-gray-100 min-h-screen text-gray-800">
    <div class="flex">
      <div class="bg-white rounded-lg p-5 flex flex-col items-center gap-4 shadow-md sidebar">
        <div class="w-11 h-11 rounded-lg flex items-center justify-center text-gray-500 cursor-pointer transition-all duration-200 relative hover:bg-red-100 hover:text-red-600 transform hover:-translate-y-1" data-tooltip="Dashboard" onclick="setActive(this); window.location.href='{{ route('admin-dashboard2') }}'">
          <i class="bx bx-home"></i>
        </div>
        <div class="w-11 h-11 rounded-lg flex items-center justify-center text-gray-500 cursor-pointer transition-all duration-200 relative hover:bg-red-100 hover:text-red-600 transform hover:-translate-y-1" data-tooltip="Users" onclick="setActive(this); window.location.href='{{ route('admin-userManagement') }}'">
          <i class="bx bx-user"></i>
        </div>
        <div class="w-11 h-11 rounded-lg flex items-center justify-center text-gray-500 cursor-pointer transition-all duration-200 relative hover:bg-red-100 hover:text-red-600 transform hover:-translate-y-1" data-tooltip="Products" onclick="setActive(this); window.location.href='{{ route('admin-productManagement') }}'">
          <i class="bx bx-package"></i>
        </div>
        <div class="w-11 h-11 rounded-lg flex items-center justify-center text-gray-500 cursor-pointer transition-all duration-200 relative hover:bg-red-100 hover:text-red-600 transform hover:-translate-y-1" data-tooltip="Wallet" onclick="setActive(this)">
          <i class="bx bx-credit-card"></i>
        </div>
        <!-- <div class="w-11 h-11 rounded-lg flex items-center justify-center text-gray-500 cursor-pointer transition-all duration-200 relative hover:bg-red-100 hover:text-red-600 transform hover:-translate-y-1" data-tooltip="Analytics" onclick="setActive(this)">
          <i class="bx bx-bar-chart"></i>
        </div> -->
        <div class="w-11 h-11 rounded-lg flex items-center justify-center text-gray-500 cursor-pointer transition-all duration-200 relative hover:bg-red-100 hover:text-red-600 transform hover:-translate-y-1" data-tooltip="Transactions" onclick="setActive(this); window.location.href='{{ route('admin.transactions') }}'">
          <i class="bx bx-transfer"></i>
        </div>
        <div class="w-11 h-11 rounded-lg flex items-center justify-center text-gray-500 cursor-pointer transition-all duration-200 relative hover:bg-red-100 hover:text-red-600 transform hover:-translate-y-1" data-tooltip="Settings" onclick="setActive(this)">
          <i class="bx bx-cog"></i>
        </div>
       <!-- <div class="w-11 h-11 rounded-lg flex items-center justify-center text-gray-500 cursor-pointer transition-all duration-200 relative hover:bg-red-100 hover:text-red-600 transform hover:-translate-y-1" data-tooltip="Help" onclick="setActive(this)">
          <i class="bx bx-help-circle"></i>
        </div>-->
        <div class="w-11 h-11 rounded-lg flex items-center justify-center text-gray-500 cursor-pointer transition-all duration-200 relative hover:bg-red-100 hover:text-red-600 transform hover:-translate-y-1" data-tooltip="Logout" onclick="setActive(this)">
          <i class="bx bx-log-in-circle"></i>
        </div>
      </div>
      <div class="flex-1 p-6 z-0">
        <div class="flex flex-col gap-4">
          <div class="bg-white p-4 rounded-lg flex justify-between items-center shadow-md navbar">
            <h1 class="text-2xl font-semibold bg-clip-text text-transparent bg-gradient-to-r from-red-600 to-red-400">Dashboard</h1>
          </div>
          {{ $slot }}
        </div>
      </div>
    </div>
    <script>
      function setActive(element) {
        const buttons = document.querySelectorAll('.sidebar div');
        buttons.forEach(button => button.classList.remove('active'));
        element.classList.add('active');
      }

      // Set the default active button
      document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        const buttons = document.querySelectorAll('.sidebar div');
        buttons.forEach(button => {
          const route = button.getAttribute('onclick').match(/window\.location\.href='([^']+)'/);
          if (route && currentPath === route[1]) {
            button.classList.add('active');
          }
        });
      });
    </script>
  </body>
</html>
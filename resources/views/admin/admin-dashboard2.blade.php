@php
  use App\Models\Product;
  use App\Models\User;
  use App\Models\Order;

  $listedProducts = Product::count();
  $totalUsers = User::count();
  $totalOrders = Order::count();

  $dailyTransactions = Order::whereDate('created_at', today())->count();
  $weeklyTransactions = Order::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
  $monthlyTransactions = Order::whereMonth('created_at', now()->month)->count();

  $dailyRegistrations = User::whereDate('created_at', today())->count();
  $weeklyRegistrations = User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count();
  $monthlyRegistrations = User::whereMonth('created_at', now()->month)->count();

  $products = Product::paginate(5);
@endphp

<x-adminLayout2>

  <div class="bg-white p-4 rounded-lg flex justify-between items-center shadow-md navbar">
    <h1 class="text-2xl font-semibold bg-clip-text text-transparent bg-gradient-to-r from-red-600 to-red-400">DASHBOARD</h1>
  </div>

  <div class="flex gap-4">
    <div class="bg-white p-2 rounded-lg shadow-md transition-all duration-300 relative overflow-hidden hover:transform hover:-translate-y-1 hover:shadow-lg flex-1">
      <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-600 to-red-400"></div>
      <h3 class="text-gray-500 text-sm mb-2 font-medium">Listed Products</h3>
      <div class="text-lg font-semibold text-gray-800 flex items-baseline gap-2">
        {{ number_format($listedProducts) }}
        <span class="text-sm text-green-500 font-medium p-1 bg-green-100 rounded">+45%</span>
      </div>
    </div>
    <div class="bg-white p-2 rounded-lg shadow-md transition-all duration-300 relative overflow-hidden hover:transform hover:-translate-y-1 hover:shadow-lg flex-1">
      <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-600 to-red-400"></div>
      <h3 class="text-gray-500 text-sm mb-2 font-medium">Total Users</h3>
      <div class="text-lg font-semibold text-gray-800 flex items-baseline gap-2">
        {{ number_format($totalUsers) }}
        <span class="text-sm text-green-500 font-medium p-1 bg-green-100 rounded">+10%</span>
      </div>
    </div>
    <div class="bg-white p-2 rounded-lg shadow-md transition-all duration-300 relative overflow-hidden hover:transform hover:-translate-y-1 hover:shadow-lg flex-1">
      <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-600 to-red-400"></div>
      <h3 class="text-gray-500 text-sm mb-2 font-medium">Total Orders</h3>
      <div class="text-lg font-semibold text-gray-800 flex items-baseline gap-2">
        {{ number_format($totalOrders) }}
        <span class="text-sm text-green-500 font-medium p-1 bg-green-100 rounded">+45%</span>
      </div>
    </div>
    <div class="bg-white p-2 rounded-lg shadow-md transition-all duration-300 relative overflow-hidden hover:transform hover:-translate-y-1 hover:shadow-lg flex-1">
      <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-600 to-red-400"></div>
      <h3 class="text-gray-500 text-sm mb-2 font-medium">Blank Card</h3>
      <div class="text-lg font-semibold text-gray-800 flex items-baseline gap-2">
        <!-- Leave this card blank for now -->
      </div>
    </div>
  </div>

  <div class="flex gap-4">
    <div class="relative flex flex-col rounded-xl bg-white bg-clip-border text-gray-700 shadow-md flex-1">
      <div class="relative mx-4 mt-4 flex flex-col gap-4 overflow-hidden rounded-none bg-transparent bg-clip-border text-gray-700 shadow-none md:flex-row md:items-center justify-between">
        <div>
          <h6 class="block font-sans text-base font-semibold leading-relaxed tracking-normal text-blue-gray-900 antialiased">Transaction Overview</h6>
        </div>
        <div class="relative">
          <button class="text-red-500 hover:text-red-700 focus:outline-none" onclick="toggleDropdown('dropdown1')">
            <i class="bx bx-dots-horizontal-rounded text-xl"></i>
          </button>
          <div id="dropdown1" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" onclick="updateChart('daily', 'transactions')">Daily</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" onclick="updateChart('weekly', 'transactions')">Weekly</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" onclick="updateChart('monthly', 'transactions')">Monthly</a>
          </div>
        </div>
      </div>
      <div class="pt-6 px-2 pb-0">
        <div id="line-chart"></div>
      </div>
    </div>
    <div class="relative flex flex-col rounded-xl bg-white bg-clip-border text-gray-700 shadow-md flex-1">
      <div class="relative mx-4 mt-4 flex flex-col gap-4 overflow-hidden rounded-none bg-transparent bg-clip-border text-gray-700 shadow-none md:flex-row md:items-center justify-between">
        <div>
          <h6 class="block font-sans text-base font-semibold leading-relaxed tracking-normal text-blue-gray-900 antialiased">User Registrations</h6>
        </div>
        <div class="relative">
          <button class="text-red-500 hover:text-red-700 focus:outline-none" onclick="toggleDropdown('dropdown2')">
            <i class="bx bx-dots-horizontal-rounded text-xl"></i>
          </button>
          <div id="dropdown2" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" onclick="updateChart('daily', 'registrations')">Daily</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" onclick="updateChart('weekly', 'registrations')">Weekly</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" onclick="updateChart('monthly', 'registrations')">Monthly</a>
          </div>
        </div>
      </div>
      <div class="pt-6 px-2 pb-0">
        <div id="bar-chart"></div>
      </div>
    </div>
  </div>

  <div class="flex gap-4">
    <div class="relative flex flex-col rounded-xl bg-white bg-clip-border text-gray-700 shadow-md flex-1">
      <div class="relative mx-4 mt-4 flex flex-col gap-4 overflow-hidden rounded-none bg-transparent bg-clip-border text-gray-700 shadow-none md:flex-row md:items-center justify-between">
        <div>
          <h6 class="block font-sans text-base font-semibold leading-relaxed tracking-normal text-blue-gray-900 antialiased">Products</h6>
        </div>
      </div>
      <div class="pt-6 px-2 pb-0">
        <table class="min-w-full bg-white">
          <thead>
            <tr>
              <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Product</th>
              <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Category</th>
              <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock</th>
              <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Price</th>
              <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Discount</th>
              <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Discounted Price</th>
              <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Image</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($products as $product)
            <tr>
              <td class="py-2 px-4 border-b border-gray-200">{{ $product->name }}</td>
              <td class="py-2 px-4 border-b border-gray-200">{{ $product->category->name }}</td>
              <td class="py-2 px-4 border-b border-gray-200">{{ $product->stock }}</td>
              <td class="py-2 px-4 border-b border-gray-200">₱{{ number_format($product->price, 2) }}</td>
              <td class="py-2 px-4 border-b border-gray-200">{{ $product->discount }}%</td>
              <td class="py-2 px-4 border-b border-gray-200">₱{{ number_format($product->discounted_price, 2) }}</td>
              <td class="py-2 px-4 border-b border-gray-200">
                <img src="{{ asset('storage/' . ($product->images[0] ?? 'default-image.jpg')) }}" class="w-16 h-16 object-cover">
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        <div class="mt-4">
          {{ $products->links() }}
        </div>        
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script>
    function toggleDropdown(id) {
      const dropdown = document.getElementById(id);
      dropdown.classList.toggle('hidden');
    }

    document.addEventListener('click', function(event) {
      const dropdowns = ['dropdown1', 'dropdown2'];
      dropdowns.forEach(id => {
        const dropdown = document.getElementById(id);
        const button = dropdown.previousElementSibling;
        if (!button.contains(event.target) && !dropdown.contains(event.target)) {
          dropdown.classList.add('hidden');
        }
      });
    });

    const lineChart = new ApexCharts(document.querySelector("#line-chart"), {
      series: [{
        name: "Transactions",
        data: {!! json_encode([$dailyTransactions, $weeklyTransactions, $monthlyTransactions]) !!},
      }],
      chart: {
        type: "line",
        height: 240,
        toolbar: { show: false },
      },
      title: { show: false },
      dataLabels: { enabled: false },
      colors: ["#e54646"],
      stroke: { lineCap: "round", curve: "smooth" },
      markers: { size: 0 },
      xaxis: {
        axisTicks: { show: false },
        axisBorder: { show: false },
        labels: { style: { colors: "#616161", fontSize: "12px" } },
        categories: ["Daily", "Weekly", "Monthly"],
      },
      yaxis: {
        labels: { style: { colors: "#616161", fontSize: "12px" } },
      },
      grid: {
        show: true,
        borderColor: "#dddddd",
        strokeDashArray: 5,
        xaxis: { lines: { show: true } },
        padding: { top: 5, right: 20 },
      },
      fill: { opacity: 0.8 },
      tooltip: { theme: "dark" },
    });
    lineChart.render();

    const barChart = new ApexCharts(document.querySelector("#bar-chart"), {
      series: [{
        name: "User Registrations",
        data: {!! json_encode([$dailyRegistrations, $weeklyRegistrations, $monthlyRegistrations]) !!},
      }],
      chart: {
        type: "bar",
        height: 240,
        toolbar: { show: false },
      },
      title: { show: false },
      dataLabels: { enabled: false },
      colors: ["#e54646"],
      plotOptions: { bar: { columnWidth: "40%", borderRadius: 2 } },
      xaxis: {
        axisTicks: { show: false },
        axisBorder: { show: false },
        labels: { style: { colors: "#616161", fontSize: "12px" } },
        categories: ["Daily", "Weekly", "Monthly"],
      },
      yaxis: {
        labels: { style: { colors: "#616161", fontSize: "12px" } },
      },
      grid: {
        show: true,
        borderColor: "#dddddd",
        strokeDashArray: 5,
        xaxis: { lines: { show: true } },
        padding: { top: 5, right: 20 },
      },
      fill: { opacity: 0.8 },
      tooltip: { theme: "dark" },
    });
    barChart.render();
  </script>
</x-adminLayout2>

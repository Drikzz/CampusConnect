<x-adminLayout2>
    <body class="bg-gray-100 p-6">
        <div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-lg">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Fund Management</h2>
            </div>

            <!-- Cards for Fund Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-4">
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center w-full justify-between">
                    <div class="p-3 rounded-full bg-red-100 text-red-500 mr-4">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="flex-grow">
                        <h3 class="text-lg font-semibold">Total users refills</h3>
                        <p class="text-2xl">10,000</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center w-full justify-between">
                    <div class="p-3 rounded-full bg-yellow-100 text-yellow-500 mr-4">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <div class="flex-grow">
                        <h3 class="text-lg font-semibold">no.of user refilled</h3>
                        <p class="text-2xl">$2,000</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center w-full justify-between">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="flex-grow">
                        <h3 class="text-lg font-semibold">Commision based on transactions</h3>
                        <p class="text-2xl">5,000</p>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-md flex items-center w-full justify-between">
                    <div class="p-3 rounded-full bg-green-100 text-green-500 mr-4">
                        <i class="fas fa-piggy-bank"></i>
                    </div>
                    <div class="flex-grow">
                        <h3 class="text-lg font-semibold">Saved Funds</h3>
                        <p class="text-2xl">3,000</p>
                    </div>
                </div>
            </div>

            <!-- Fund Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border rounded-lg shadow-md">
                    <thead>
                        <tr class="bg-gray-200 text-left text-sm font-semibold">
                            <th class="p-3">User Id</th>
                            <th class="p-3">User name</th>
                            <th class="p-3">Amount refilled</th>
                            <th class="p-3">Wmsu Email</th>
                            <th class="p-3">Date</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-t">
                            <td class="p-3">1</td>
                            <td class="p-3">Ken</td>
                            <td class="p-3">1,000</td>
                            <td class="p-3">Eh202201108@wmsu.edu.ph</td>
                            <td class="p-3">January 1, 2023</td>
                            <td class="p-3 flex space-x-2">
                                <button class="bg-red-500 text-white px-4 py-2 rounded border border-red-500">View</button>
                                <button class="bg-red-500 text-white px-4 py-2 rounded border border-red-500">Approve</button>
                            </td>
                        </tr>
                        <tr class="border-t">
                            <td class="p-3">2</td>
                            <td class="p-3">Grant</td>
                            <td class="p-3">$3,000</td>
                            <td class="p-3">Approved</td>
                            <td class="p-3">February 15, 2023</td>
                            <td class="p-3 flex space-x-2">
                                <button class="bg-red-500 text-white px-4 py-2 rounded border border-red-500">View</button>
                                <button class="bg-red-500 text-white px-4 py-2 rounded border border-red-500">Reject</button>
                            </td>
                        </tr>
                        <!-- Add more rows as needed -->
                    </tbody>
                </table>
            </div>
        </div>
    </body>
</x-adminLayout2>
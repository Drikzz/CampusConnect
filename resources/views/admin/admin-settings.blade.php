<x-adminLayout2>
  <div class="bg-white p-4 rounded-lg shadow-md">
    <h1 class="text-2xl font-semibold bg-clip-text text-transparent bg-gradient-to-r from-red-600 to-red-400">Settings</h1>
    <form action="{{ route('admin.settings') }}" method="POST" class="mt-4">
      @csrf
      <div class="mb-4">
        <label for="commission_rate" class="block text-gray-700 font-medium mb-2">Commission Rate (%)</label>
        {{-- <input type="number" name="commission_rate" id="commission_rate" class="w-full p-2 border border-gray-300 rounded-lg" value="{{ old('commission_rate', $commissionRate) }}" required> --}}
      </div>
      <div class="flex justify-end">
        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition-all duration-200">Save</button>
      </div>
    </form>
  </div>
</x-adminLayout2>
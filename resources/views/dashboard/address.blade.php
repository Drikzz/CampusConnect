@extends('dashboard.dashboard')

@section('dashboard-content')
    <div class="max-w-2xl mx-auto">
        <h2 class="text-2xl font-bold mb-6">Shipping Address</h2>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="address_line1" class="block text-sm font-medium text-gray-700">Address Line 1</label>
                    <input type="text" name="address_line1" id="address_line1"
                        value="{{ old('address_line1', $user->address_line1) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                </div>

                <div>
                    <label for="address_line2" class="block text-sm font-medium text-gray-700">Address Line 2
                        (Optional)</label>
                    <input type="text" name="address_line2" id="address_line2"
                        value="{{ old('address_line2', $user->address_line2) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" name="city" id="city" value="{{ old('city', $user->city) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                    </div>

                    <div>
                        <label for="postal_code" class="block text-sm font-medium text-gray-700">Postal Code</label>
                        <input type="text" name="postal_code" id="postal_code"
                            value="{{ old('postal_code', $user->postal_code) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-color focus:ring-primary-color">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-primary-color text-white px-4 py-2 rounded-md hover:bg-primary-color/90">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection

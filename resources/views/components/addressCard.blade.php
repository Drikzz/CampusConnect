{{-- Address Tab Content --}}
<div class="max-w-3xl mx-auto py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="border-b border-gray-200 pb-4 mb-6">
            <h3 class="text-xl font-semibold text-gray-800">Delivery Address</h3>
            <p class="text-sm text-gray-500">Update your delivery information for purchases.</p>
        </div>

        <form action="" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 gap-6">
                {{-- Address Line 1 --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1</label>
                    <input type="text" name="address_line1" value="{{ auth()->user()->address_line1 ?? '' }}"
                        placeholder="House/Unit Number, Building, Street Name"
                        class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black transition-all">
                </div>

                {{-- Address Line 2 --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2 (Optional)</label>
                    <input type="text" name="address_line2" value="{{ auth()->user()->address_line2 ?? '' }}"
                        placeholder="Apartment, Suite, Unit, Building, Floor, etc."
                        class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black transition-all">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- City/Municipality --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City/Municipality</label>
                        <input type="text" name="city" value="{{ auth()->user()->city ?? '' }}"
                            placeholder="City/Municipality"
                            class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black transition-all">
                    </div>

                    {{-- Province --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                        <input type="text" name="province" value="{{ auth()->user()->province ?? '' }}"
                            placeholder="Province"
                            class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black transition-all">
                    </div>
                </div>

                {{-- Delivery Contact Number --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Contact Number</label>
                    <input type="tel" name="delivery_phone" value="{{ auth()->user()->delivery_phone ?? '' }}"
                        placeholder="Contact number for delivery"
                        class="w-full rounded-lg border-gray-300 focus:border-black focus:ring-black transition-all">
                    <p class="mt-1 text-sm text-gray-500">This number will be used by delivery personnel.</p>
                </div>
            </div>

            {{-- Save Button --}}
            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-6 py-2 bg-primary-color text-white rounded-lg hover:bg-primary-color/90 transition">
                    Save Address
                </button>
            </div>
        </form>
    </div>
</div>

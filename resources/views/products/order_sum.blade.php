<x-layout>
    <form id="checkout-form" action="{{ route('checkout.process') }}" method="POST">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="sub_total" id="form-total" value="{{ $product->discounted_price }}">
        <input type="hidden" name="quantity" id="form-quantity" value="{{ old('quantity', 1) }}">

        <div class="min-h-screen bg-gray-50 pb-24 pt-12 md:pb-20 md:pt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                    {{-- Left Column - Order Summary --}}
                    <div class="md:col-span-1 bg-white p-6 rounded-lg shadow-sm h-fit">
                        <div>
                            <h2 class="text-xl md:text-2xl font-Satoshi-bold mb-6">Order Summary</h2>

                            {{-- Adjust product card layout --}}
                            <div class="flex flex-col gap-4 mb-6">
                                {{-- Product Card --}}
                                <div
                                    class="flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 border rounded-lg space-y-4 sm:space-y-0">
                                    {{-- Product Image and Name --}}
                                    <div class="flex items-center gap-4 w-full sm:w-auto">
                                        <img src="{{ asset('storage/' . $product->images[0]) }}"
                                            alt="{{ $product->name }}"
                                            class="w-16 h-16 object-cover rounded-md flex-shrink-0">
                                        <h3 class="font-Satoshi-bold">{{ Str::ucfirst($product->name) }}</h3>
                                    </div>
                                    <div
                                        class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
                                        {{-- Quantity Controls --}}
                                        <div class="relative flex items-center max-w-[8rem]">
                                            <button type="button" id="decrement-button"
                                                class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-s-lg p-3 h-11 focus:ring-gray-100 focus:ring-2 focus:outline-none"
                                                onclick="event.preventDefault()">
                                                <svg class="w-3 h-3 text-gray-900" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 18 2">
                                                    <path stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2" d="M1 1h16" />
                                                </svg>
                                            </button>

                                            <input type="text" id="quantity" value="{{ old('quantity', 1) }}"
                                                class="bg-gray-50 border-x-0 border-gray-300 h-11 text-center text-gray-900 text-sm focus:ring-0 focus:outline-none block w-full py-2.5"
                                                readonly data-max-stock="{{ $product->stock }}"
                                                data-original-price="{{ $product->price }}"
                                                data-discounted-price="{{ $product->discounted_price }}">
                                            <button type="button" id="increment-button"
                                                class="bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded-e-lg p-3 h-11 focus:ring-gray-100 focus:ring-2 focus:outline-none"
                                                onclick="event.preventDefault()">
                                                <svg class="w-3 h-3 text-gray-900" aria-hidden="true"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 18 18">
                                                    <path stroke="currentColor" stroke-linecap="round"
                                                        stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16" />
                                                </svg>
                                            </button>
                                        </div>

                                        {{-- Subtotal --}}
                                        <div class="text-right ml-4">
                                            <p class="font-Satoshi-bold whitespace-nowrap" id="subtotal">
                                                ₱{{ number_format($product->discounted_price) }}</p>
                                        </div>
                                    </div>
                                </div>

                                @if ($product->has_variants)
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium mb-2">Size/Variant</label>
                                        <select name="variant" class="w-full md:w-48 rounded-md border-gray-200">
                                            @foreach ($product->variants as $variant)
                                                <option value="{{ $variant->id }}">{{ $variant->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            </div>

                            {{-- Seller Information --}}
                            <div class="border-t border-gray-100 py-4 mb-6">
                                <h4 class="font-Satoshi-bold mb-2">Seller Information</h4>
                                <div class="flex items-center gap-3">
                                    <img src="{{ asset('storage/' . $product->seller->profile_picture) }}"
                                        class="w-8 h-8 rounded-full object-cover">
                                    <div>
                                        <p class="font-medium">{{ $product->seller->first_name }}</p>
                                        <p class="text-sm text-gray-500">
                                            {{ $product->seller->location ?? 'Location N/A' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- Delivery Estimate --}}
                            <div class="bg-gray-50 p-4 rounded-md mb-6">
                                <h4 class="font-medium mb-2">Estimated Delivery</h4>
                                <p class="text-sm text-gray-600">{{ now()->addDays(3)->format('M d') }} -
                                    {{ now()->addDays(7)->format('M d') }}</p>
                            </div>
                        </div>

                        {{-- Price Breakdown --}}
                        <div class="space-y-3 border-t border-b py-4">
                            <div class="flex justify-between">
                                <span class="font-Satoshi">Original Price</span>
                                <span class="text-black font-Satoshi"
                                    id="original-price">₱{{ number_format($product->price) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-Satoshi">Discount ({{ $product->discount }}%)</span>
                                <span class="text-red-500 font-Satoshi"
                                    id="discount-amount">-₱{{ number_format($product->price - $product->discounted_price) }}</span>
                            </div>
                            <div class="flex justify-between font-Satoshi-bold text-lg">
                                <span class="font-Satoshi-bold">Total</span>
                                <span class="font-Satoshi-bold"
                                    id="total">₱{{ number_format($product->discounted_price) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column - Checkout Form --}}
                    <div class="md:col-span-1 bg-white rounded-lg shadow-sm flex flex-col">
                        <div class="bg-white p-6 border-b">
                            <h2 class="text-2xl font-Satoshi-bold">Checkout Details</h2>
                        </div>

                        <div class="p-6 overflow-y-auto flex-1">
                            <div class="space-y-6">
                                {{-- Contact Information Section --}}
                                <div>
                                    <div
                                        class="flex items-center justify-between w-full py-5 font-medium border-b border-gray-200">
                                        <span class="font-Satoshi-bold text-black">Contact Information</span>
                                    </div>
                                    <div class="py-5 border-b border-gray-200 space-y-4">
                                        <div class="space-y-2">
                                            <label for="wmsu_email"
                                                class="block text-sm font-medium text-gray-900">WMSU
                                                Email</label>
                                            <input type="email" id="wmsu_email" name="wmsu_email"
                                                value="{{ Auth::user()->wmsu_email }}"
                                                class="w-full px-4 py-2 border rounded-md bg-gray-100" readonly>
                                        </div>
                                        <div class="space-y-2">
                                            <label for="phone"
                                                class="block text-sm font-medium text-gray-900">Phone
                                                number</label>
                                            <input type="tel" id="phone" name="phone"
                                                value="{{ old('phone', Auth::user()->phone) }}"
                                                placeholder="Enter your phone number"
                                                class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-black focus:outline-none @error('phone') border-red-500 @enderror">
                                            @error('phone')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Meetup Details Section --}}
                                <div>
                                    <div
                                        class="flex items-center justify-between w-full py-5 font-medium border-b border-gray-200">
                                        <span class="font-Satoshi-bold text-black">Meetup Schedule</span>
                                    </div>
                                    <div class="py-5 border-b border-gray-200 space-y-4">
                                        @forelse ($product->seller->meetupLocations()->where('is_active', true)->get() as $location)
                                            <div class="border rounded-lg p-4 space-y-4">
                                                <div class="font-medium text-gray-900">{{ $location->full_name }}
                                                </div>
                                                <div class="text-sm text-gray-600">{{ $location->description }}</div>

                                                {{-- Available Schedules --}}
                                                <div class="space-y-2">
                                                    @php
                                                        $availableDays = json_decode($location->available_days);
                                                        $timeFrom = \Carbon\Carbon::parse(
                                                            $location->available_from,
                                                        )->format('g:i A');
                                                        $timeUntil = \Carbon\Carbon::parse(
                                                            $location->available_until,
                                                        )->format('g:i A');
                                                        $schedules = [];

                                                        foreach ($availableDays as $day) {
                                                            $dayName = match ((int) $day) {
                                                                0 => 'Sunday',
                                                                1 => 'Monday',
                                                                2 => 'Tuesday',
                                                                3 => 'Wednesday',
                                                                4 => 'Thursday',
                                                                5 => 'Friday',
                                                                6 => 'Saturday',
                                                                default => 'Unknown',
                                                            };

                                                            $schedules[] = [
                                                                'day_num' => $day,
                                                                'day_name' => $dayName,
                                                                'time_range' => "$timeFrom - $timeUntil",
                                                            ];
                                                        }
                                                    @endphp

                                                    @foreach ($schedules as $schedule)
                                                        <label
                                                            class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer">
                                                            <input type="radio" name="meetup_schedule"
                                                                value="{{ $location->id }}_{{ $schedule['day_num'] }}"
                                                                class="form-radio text-black mr-3">
                                                            <div>
                                                                <span
                                                                    class="font-medium">{{ $schedule['day_name'] }}</span>
                                                                <span
                                                                    class="text-gray-600 text-sm ml-2">{{ $schedule['time_range'] }}</span>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>

                                                {{-- Location Details --}}
                                                <div class="text-sm text-gray-600 mt-2">
                                                    <p>{{ $location->custom_location }}</p>
                                                    @if ($location->latitude && $location->longitude)
                                                        <a href="https://maps.google.com/?q={{ $location->latitude }},{{ $location->longitude }}"
                                                            target="_blank"
                                                            class="inline-flex items-center text-primary-color hover:text-primary-color/80 mt-2">
                                                            <svg class="w-4 h-4 mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            </svg>
                                                            View on Maps
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-4 text-gray-500">
                                                No meetup schedules available
                                            </div>
                                        @endforelse

                                        <p class="text-xs text-gray-500 mt-4">
                                            * Limited to {{ $location->max_daily_meetups ?? 5 }} meetups per day
                                        </p>
                                    </div>
                                </div>

                                {{-- Payment Method Section --}}
                                <div class="py-5 space-y-4">
                                    <h3 class="font-Satoshi-bold">Payment Method</h3>
                                    <div class="space-y-2">
                                        <label class="flex items-center space-x-3">
                                            <input type="radio" name="payment_method" value="cash"
                                                {{ old('payment_method', 'cash') == 'cash' ? 'checked' : '' }}
                                                class="form-radio text-black">
                                            <span>Cash on Meetup</span>
                                        </label>
                                        <label class="flex items-center space-x-3">
                                            <input type="radio" name="payment_method" value="gcash"
                                                {{ old('payment_method') == 'gcash' ? 'checked' : '' }}
                                                class="form-radio text-black">
                                            <span>GCash</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Order Button --}}
                        <div class="p-6 border-t bg-white">
                            <button type="submit"
                                class="w-full bg-black text-white py-3 rounded-full font-Satoshi-bold hover:bg-gray-800 transition-colors">
                                Place Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const meetupLocations = document.querySelectorAll('input[name="meetup_location_id"]');

            meetupLocations.forEach(radio => {
                radio.addEventListener('change', function() {
                    // You could add additional functionality here if needed
                    // For example, updating a summary section or handling location selection
                });
            });
        });
    </script>

</x-layout>

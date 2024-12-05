<x-layout>
    <form id="checkout-form" action="{{ route('checkout.process') }}" method="POST">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        <input type="hidden" name="sub_total" id="form-total" value="{{ $product->discounted_price }}">
        <input type="hidden" name="delivery_estimate"
            value="{{ now()->addDays(3)->format('M d') }} - {{ now()->addDays(7)->format('M d') }}">
        <input type="hidden" name="quantity" id="form-quantity" value="{{ old('quantity', 1) }}">

        <div class="min-h-screen bg-gray-50 pb-24 pt-12 md:pb-20 md:pt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Change grid-cols-3 to grid-cols-2 and adjust column spans --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                    {{-- Left Column - Order Summary (now 1/2 width) --}}
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
                                        <img src="{{ $product->image }}" alt="{{ $product->name }}"
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
                                                ₱{{ number_format($product->discounted_price, 2) }}</p>
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
                                    <img src="{{ Storage::url($product->seller->profile_picture) ?? 'default.jpg' }}"
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
                                    id="original-price">₱{{ number_format($product->price, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-Satoshi">Discount ({{ $product->discount }}%)</span>
                                <span class="text-red-500 font-Satoshi"
                                    id="discount-amount">-₱{{ number_format($product->price - $product->discounted_price, 2) }}</span>
                            </div>
                            <div class="flex justify-between font-Satoshi-bold text-lg">
                                <span class="font-Satoshi-bold">Total</span>
                                <span class="font-Satoshi-bold"
                                    id="total">₱{{ number_format($product->discounted_price, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column - Checkout Form (now 1/2 width) --}}
                    <div class="md:col-span-1 bg-white rounded-lg shadow-sm">
                        <div class="sticky top-0 bg-white p-6 border-b z-10">
                            <h2 class="text-2xl font-Satoshi-bold">Checkout Details</h2>
                        </div>

                        <div class="p-6 overflow-y-auto max-h-[calc(100vh-200px)]">
                            <div class="space-y-6 flex-grow">
                                {{-- Contact Information Section --}}
                                <div>
                                    <div
                                        class="flex items-center justify-between w-full py-5 font-medium border-b border-gray-200">
                                        <span class="font-Satoshi-bold text-black">Contact Information</span>
                                    </div>
                                    <div class="py-5 border-b border-gray-200 space-y-4">
                                        <div class="space-y-2">
                                            <label for="email"
                                                class="block text-sm font-medium text-gray-900">Email
                                                address: (Optional) </label>
                                            <input type="email" id="email" name="email"
                                                value="{{ old('email') }}" placeholder="Enter your email address"
                                                class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-black focus:outline-none @error('email') border-red-500 @enderror">
                                            @error('email')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="space-y-2">
                                            <label for="phone"
                                                class="block text-sm font-medium text-gray-900">Phone
                                                number</label>
                                            <input type="tel" id="phone" name="phone"
                                                value="{{ old('phone', Auth::user()->phone ?? '') }}"
                                                placeholder="Enter your phone number"
                                                class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-black focus:outline-none @error('phone') border-red-500 @enderror">
                                            @error('phone')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- Shipping Information Section --}}
                                <div>
                                    <div
                                        class="flex items-center justify-between w-full py-5 font-medium border-b border-gray-200">
                                        <span class="font-Satoshi-bold text-black">Shipping Address</span>
                                    </div>
                                    <div class="py-5 border-b border-gray-200 space-y-4">
                                        <div class="space-y-2">
                                            <label for="address"
                                                class="block text-sm font-medium text-gray-900">Street
                                                address</label>
                                            <input type="text" id="address" name="address"
                                                value="{{ old('address', auth()->user()->address ?? '') }}"
                                                placeholder="Enter your street address"
                                                class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-black focus:outline-none @error('address') border-red-500 @enderror">
                                            @error('address')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="space-y-2">
                                            <label for="city"
                                                class="block text-sm font-medium text-gray-900">City</label>
                                            <input type="text" id="city" name="city"
                                                value="{{ old('city', auth()->user()->city ?? '') }}"
                                                placeholder="Enter your city"
                                                class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-black focus:outline-none @error('city') border-red-500 @enderror">
                                            @error('city')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="space-y-2">
                                            <label for="postal_code"
                                                class="block text-sm font-medium text-gray-900">Postal code</label>
                                            <input type="text" id="postal_code" name="postal_code"
                                                value="{{ old('postal_code', auth()->user()->postal_code ?? '') }}"
                                                placeholder="Enter your postal code"
                                                class="w-full px-4 py-2 border rounded-md focus:ring-2 focus:ring-black focus:outline-none @error('postal_code') border-red-500 @enderror">
                                            @error('postal_code')
                                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                            @enderror
                                        </div>
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
                                            <span>Cash on Delivery</span>
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

                            {{-- Sticky Order Button --}}
                            <div class="sticky bottom-0 bg-white p-6 border-t mt-auto">
                                <button type="submit"
                                    class="w-full bg-black text-white py-3 rounded-full font-Satoshi-bold hover:bg-gray-800 transition-colors">
                                    Place Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</x-layout>

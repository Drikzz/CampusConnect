@extends('dashboard.dashboard')

@section('dashboard-content')
    <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-bold mb-6">Become a Seller</h2>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <div class="prose max-w-none">
                <h3 class="text-xl font-semibold mb-4">Why Sell on Campus Connect?</h3>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Reach thousands of students and staff members</li>
                    <li>Easy-to-use platform for listing and managing products</li>
                    <li>Secure payment processing</li>
                    <li>Support for both selling and trading items</li>
                </ul>

                <h3 class="text-xl font-semibold mt-6 mb-4">How It Works</h3>
                <ol class="list-decimal pl-5 space-y-2">
                    <li>Review and accept our seller terms and conditions</li>
                    <li>Complete your seller profile</li>
                    <li>List your first product</li>
                    <li>Start selling!</li>
                </ol>

                <div class="mt-8 text-center">
                    <a href="{{ route('dashboard.seller.terms') }}"
                        class="inline-block bg-primary-color text-white px-6 py-3 rounded-md hover:bg-primary-color/90">
                        Continue to Seller Terms
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

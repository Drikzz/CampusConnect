<x-layout>
    <div class="w-full mt-10 mb-28 px-16">
        <h1 class="text-4xl font-bold mb-6 text-center text-blue-800">Terms and Conditions</h1>

        <div class="mt-8">
            <h2 class="text-2xl font-semibold mb-2 text-blue-700">Acceptance of Terms</h2>
            <p class="text-gray-700 leading-relaxed">By using Campus Connect as a seller, you agree to these terms. We
                may update these
                terms from time to time, and by continuing to use the platform, you accept the changes.</p>
        </div>

        <div class="mt-8">
            <h2 class="text-2xl font-semibold mb-2 text-blue-700">Seller Responsibilities</h2>
            <ul class="list-disc list-inside text-gray-700 space-y-2">
                <li>Accurate Listings - Provide clear and truthful details about your items, including
                    descriptions and prices.</li>
                <li>Confirmation of Sale - If an item is sold, you must confirm it immediately. Failure to
                    do so will result in a penalty (see Section 5).</li>
                <li>Communication - Respond to buyers promptly to ensure smooth transactions.</li>
            </ul>
        </div>

        <div class="mt-8">
            <h2 class="text-2xl font-semibold mb-2 text-blue-700">Commissions</h2>
            <p class="text-gray-700 leading-relaxed">Campus Connect takes a percentage from each sale, based on the item
                price:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2">
                <li>₱1 to ₱500: 2%</li>
                <li>₱501 to ₱1,000: 5%</li>
                <li>₱1,001 to ₱10,000: 8%</li>
                <li>₱10,001 to ₱20,000: 12%</li>
                <li>₱20,001 and above: 15%</li>
            </ul>
            <p class="text-gray-700 leading-relaxed">This commission is automatically deducted from your earnings.</p>
        </div>

        <div class="mt-8">
            <h2 class="text-2xl font-semibold mb-2 text-blue-700">Penalties for Non-Confirmation</h2>
            <p class="text-gray-700 leading-relaxed">If you do not confirm that an item is sold after a buyer checks
                out:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2">
                <li>You will get a warning.</li>
                <li>Half of the transaction amount will be deducted from your wallet.</li>
                <li>The regular commission will still apply.</li>
            </ul>
        </div>

        <div class="mt-8">
            <h2 class="text-2xl font-semibold mb-2 text-blue-700">Wallet and Earnings</h2>
            <p class="text-gray-700 leading-relaxed">Your earnings from sales will go into your Campus Connect wallet.
                After commission
                and penalties, you can cash out your balance.</p>
        </div>

        <div class="mt-8">
            <h2 class="text-2xl font-semibold mb-2 text-blue-700">Prohibited Actions</h2>
            <p class="text-gray-700 leading-relaxed">Sellers must not:</p>
            <ul class="list-disc list-inside text-gray-700 space-y-2">
                <li>List fake or stolen items.</li>
                <li>Provide false information about items.</li>
                <li>Cancel sales unfairly or mislead buyers.</li>
            </ul>
            <p class="text-gray-700 leading-relaxed">Breaking these rules can lead to account suspension.</p>
        </div>

        <div class="mt-8">
            <h2 class="text-2xl font-semibold mb-2 text-blue-700">Account Termination</h2>
            <p class="text-gray-700 leading-relaxed">Campus Connect can suspend or close your account if you break any
                rules. Any
                remaining balance in your wallet may be withheld if your account is terminated.</p>
        </div>

        <form action="{{ route('dashboard.terms') }}" method="POST" class="mt-8 bg-gray-100 p-6 rounded-lg shadow-md">
            @csrf
            <div class="flex items-center">
                <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" id="acceptTerms" name="acceptTerms"
                    required>
                <label class="ml-2 text-gray-700" for="acceptTerms">By proceeding, you acknowledge that you have read
                    and accepted the Campus Connect Terms and Conditions.</label>
            </div>
            <button type="submit"
                class="mt-4 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                id="submitBtn">Submit</button>
        </form>
    </div>
</x-layout>

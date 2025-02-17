<div class="sticky top-0 w-full bg-white shadow-md z-50">
    <div class="max-w-6xl mx-auto px-8 py-6">
        <div class="mb-6">
            <div class="relative mb-8">
                <div class="w-full bg-gray-100 rounded-full h-2 mb-4">
                    <div class="bg-primary-color h-2 rounded-full transition-all duration-500"
                        style="width: {{ ($currentStep / (isset($showSellerSetup) ? 3 : 3)) * 100 }}%">
                    </div>
                </div>

                <div class="flex justify-between">
                    <div class="flex flex-col items-center">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center 
                            {{ $currentStep >= 1 ? 'bg-primary-color text-white' : 'bg-gray-200' }}">
                            <i class="fas fa-user"></i>
                        </div>
                        <span
                            class="text-sm mt-2 {{ $currentStep == 1 ? 'text-primary-color font-medium' : 'text-gray-500' }}">
                            Personal Info
                        </span>
                    </div>

                    <div class="flex flex-col items-center">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center 
                            {{ $currentStep >= 2 ? 'bg-primary-color text-white' : 'bg-gray-200' }}">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <span
                            class="text-sm mt-2 {{ $currentStep == 2 ? 'text-primary-color font-medium' : 'text-gray-500' }}">
                            Account Info
                        </span>
                    </div>

                    <div class="flex flex-col items-center">
                        <div
                            class="w-8 h-8 rounded-full flex items-center justify-center 
                            {{ $currentStep >= 3 ? 'bg-primary-color text-white' : 'bg-gray-200' }}">
                            <i class="fas fa-check"></i>
                        </div>
                        <span
                            class="text-sm mt-2 {{ $currentStep == 3 ? 'text-primary-color font-medium' : 'text-gray-500' }}">
                            Confirmation
                        </span>
                    </div>

                    @if (isset($showSellerSetup) && $showSellerSetup)
                        <div class="flex flex-col items-center">
                            <div
                                class="w-8 h-8 rounded-full flex items-center justify-center 
                                {{ $currentStep >= 4 ? 'bg-primary-color text-white' : 'bg-gray-200' }}">
                                <i class="fas fa-store"></i>
                            </div>
                            <span
                                class="text-sm mt-2 {{ $currentStep == 4 ? 'text-primary-color font-medium' : 'text-gray-500' }}">
                                Seller Setup
                            </span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .feather {
        height: 100%;
        width: 100%;
        stroke-width: 1.5;
    }
</style>

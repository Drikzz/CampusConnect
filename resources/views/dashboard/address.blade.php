@extends('dashboard.dashboard')

@section('dashboard-content')
    <div class="max-w-4xl mx-auto">
        <!-- Header section -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Meetup Locations</h2>
            <button type="button" data-modal-target="add-meetup-modal" data-modal-toggle="add-meetup-modal"
                class="bg-primary-color text-white px-4 py-2 rounded-md hover:bg-primary-color/90">
                Add Meetup Location
            </button>
        </div>

        <!-- Meetup locations list -->
        @if ($meetupLocations->isEmpty())
            <div class="text-center py-8 bg-gray-50 rounded-lg">
                <p class="text-gray-600">No meetup locations. Add one to get started.</p>
            </div>
        @else
            <div class="grid gap-6">
                @foreach ($meetupLocations as $meetupLocation)
                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-semibold text-lg">{{ $meetupLocation->full_name }}</h3>
                                <p class="text-gray-600">{{ $meetupLocation->phone }}</p>
                                <p class="mt-2">{{ $meetupLocation->location->name }}</p>
                                @if ($meetupLocation->custom_location)
                                    <p class="text-sm text-gray-500">{{ $meetupLocation->custom_location }}</p>
                                @endif
                                @if ($meetupLocation->is_default)
                                    <span
                                        class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Default Location
                                    </span>
                                @endif
                                {{-- @if ($meetupLocation->latitude && $meetupLocation->longitude)
                                    <p class="text-sm text-gray-500 mt-2">
                                        Location: {{ $meetupLocation->latitude }}, {{ $meetupLocation->longitude }}
                                    </p>
                                @endif --}}
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="editLocation({{ $meetupLocation->id }})"
                                    class="text-gray-600 hover:text-gray-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button type="button" onclick="deleteLocation({{ $meetupLocation->id }}, this)"
                                    class="text-red-600 hover:text-red-900" data-location-id="{{ $meetupLocation->id }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Add Meetup Location Modal -->
    <div id="add-meetup-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 flex items-center justify-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>
        <div class="relative p-4 w-full max-w-2xl max-h-full z-10">
            <div class="relative bg-white rounded-lg shadow">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Add Meetup Location
                    </h3>
                    <button type="button"
                        class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center"
                        data-modal-hide="add-meetup-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                    </button>
                </div>
                <form action="{{ route('meetup-locations.store') }}" method="POST" class="p-4 md:p-5">
                    @csrf
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">

                    <div class="grid gap-4 mb-4">
                        <!-- Full Name Field -->
                        <div>
                            <label for="full_name" class="block mb-2 text-sm font-medium text-gray-900">Full Name</label>
                            <div class="relative">
                                <input type="text" name="full_name" id="full_name"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                    placeholder="Select or enter your full name" required readonly>
                                <button type="button" id="showUserNames"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div id="namesDropdown"
                                    class="hidden absolute z-10 w-full mt-1 bg-white rounded-lg shadow-lg border border-gray-200">
                                    <ul class="py-1">
                                        <li>
                                            <button type="button"
                                                class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 name-option"
                                                data-name="{{ $user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name }}">
                                                {{ $user->first_name . ' ' . ($user->middle_name ? $user->middle_name . ' ' : '') . $user->last_name }}
                                            </button>
                                        </li>
                                        <li>
                                            <button type="button"
                                                class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 name-option"
                                                data-name="{{ $user->last_name . ', ' . $user->first_name . ' ' . ($user->middle_name ? $user->middle_name : '') }}">
                                                {{ $user->last_name . ', ' . $user->first_name . ' ' . ($user->middle_name ? $user->middle_name : '') }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Phone Number Field -->
                        <div>
                            <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">Phone
                                Number</label>
                            <div class="relative">
                                <input type="text" name="phone" id="phone"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                    placeholder="Select or enter phone number" required readonly>
                                <button type="button" id="showPhoneNumbers"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-gray-700">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div id="phoneDropdown"
                                    class="hidden absolute z-10 w-full mt-1 bg-white rounded-lg shadow-lg border border-gray-200">
                                    <ul class="py-1">
                                        <li>
                                            <button type="button"
                                                class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 phone-option"
                                                data-phone="{{ $user->phone }}">
                                                {{ $user->phone }}
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Meetup z Field -->
                        <div>
                            <label for="location_id" class="block mb-2 text-sm font-medium text-gray-900">Meetup
                                Location</label>
                            <select name="location_id" id="location"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                required>
                                <option value="">Select a location</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" data-lat="{{ $location->latitude }}"
                                        data-lng="{{ $location->longitude }}">
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Map Container -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Pin Location on Map
                            </label>
                            <div id="map" class="h-64 rounded-lg border border-gray-300"></div>
                            <p class="mt-2 text-sm text-gray-500">
                                Click on the map to set the exact meetup location
                            </p>
                        </div>
                    </div>

                    <!-- Default Location Checkbox -->
                    <div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_default" class="sr-only peer" value="1">
                            <div
                                class="w-11 h-6 bg-gray-200 rounded-full peer 
                                peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300
                                dark:bg-gray-700 peer-checked:after:translate-x-full 
                                peer-checked:after:border-white after:content-[''] 
                                after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:border-gray-300 after:border 
                                after:rounded-full after:h-5 after:w-5 after:transition-all
                                dark:border-gray-600 peer-checked:bg-primary-color">
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-900">Set as default location</span>
                        </label>
                    </div>

                    <!-- Form Buttons -->
                    <div class="flex items-center space-x-4 mt-4">
                        <button type="submit"
                            class="text-white bg-primary-color hover:bg-primary-color/90 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Save
                        </button>
                        <button type="button" data-modal-hide="add-meetup-modal"
                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Meetup Location Modal -->
    <div id="edit-meetup-modal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 flex items-center justify-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>
        <div class="relative p-4 w-full max-w-2xl max-h-full z-10">
            <div class="relative bg-white rounded-lg shadow">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Edit Meetup Location
                    </h3>
                    <button type="button"
                        class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center"
                        data-modal-hide="edit-meetup-modal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                    </button>
                </div>
                <form id="edit-form" method="POST" class="p-4 md:p-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">

                    <div class="grid gap-4 mb-4">
                        <!-- Full Name Field -->
                        <div>
                            <label for="full_name" class="block mb-2 text-sm font-medium text-gray-900">Full Name</label>
                            <div class="relative">
                                <input type="text" name="full_name" id="full_name"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                    placeholder="Enter your full name" required>
                            </div>
                        </div>

                        <!-- Phone Number Field -->
                        <div>
                            <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">Phone
                                Number</label>
                            <div class="relative">
                                <input type="text" name="phone" id="phone"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                    placeholder="Enter phone number" required>
                            </div>
                        </div>

                        <!-- Meetup z Field -->
                        <div>
                            <label for="location_id" class="block mb-2 text-sm font-medium text-gray-900">Meetup
                                Location</label>
                            <select name="location_id" id="location"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                required>
                                <option value="">Select a location</option>
                                @foreach ($locations as $location)
                                    <option value="{{ $location->id }}" data-lat="{{ $location->latitude }}"
                                        data-lng="{{ $location->longitude }}">
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Map Container -->
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-900">
                                Pin Location on Map
                            </label>
                            <div id="map" class="h-64 rounded-lg border border-gray-300"></div>
                            <p class="mt-2 text-sm text-gray-500">
                                Click on the map to set the exact meetup location
                            </p>
                        </div>
                    </div>

                    <!-- Default Location Checkbox -->
                    <div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_default" class="sr-only peer" value="1">
                            <div
                                class="w-11 h-6 bg-gray-200 rounded-full peer 
                                peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary-300
                                dark:bg-gray-700 peer-checked:after:translate-x-full 
                                peer-checked:after:border-white after:content-[''] 
                                after:absolute after:top-[2px] after:left-[2px]
                                after:bg-white after:border-gray-300 after:border 
                                after:rounded-full after:h-5 after:w-5 after:transition-all
                                dark:border-gray-600 peer-checked:bg-primary-color">
                            </div>
                            <span class="ml-3 text-sm font-medium text-gray-900">Set as default location</span>
                        </label>
                    </div>

                    <!-- Form Buttons -->
                    <div class="flex items-center space-x-4 mt-4">
                        <button type="submit"
                            class="text-white bg-primary-color hover:bg-primary-color/90 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Save
                        </button>
                        <button type="button" data-modal-hide="edit-meetup-modal"
                            class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Replace the existing deleteLocation function with this exact version
        function deleteLocation(productId, buttonElement) {
            if (!confirm('Are you sure you want to delete this meetup location?')) return;

            fetch(`/dashboard/meetup-locations/${productId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const locationCard = buttonElement.closest('.bg-white');
                        locationCard.remove();

                        // Check if there are no more locations
                        const remainingLocations = document.querySelectorAll('.grid.gap-6 > div').length;
                        if (remainingLocations === 0) {
                            const container = document.querySelector('.grid.gap-6').parentElement;
                            container.innerHTML = `
                            <div class="text-center py-8 bg-gray-50 rounded-lg">
                                <p class="text-gray-600">No meetup locations. Add one to get started.</p>
                            </div>
                        `;
                        }
                    } else {
                        alert(data.message || 'Failed to delete location');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting location');
                });
        }

        // Add notification helper function
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded shadow-lg z-50 animate-fade-in-out ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white`;
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Add this CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInOut {
                0% { opacity: 0; transform: translateY(-20px); }
                10% { opacity: 1; transform: translateY(0); }
                90% { opacity: 1; transform: translateY(0); }
                100% { opacity: 0; transform: translateY(-20px); }
            }
            .animate-fade-in-out {
                animation: fadeInOut 3s ease-in-out;
            }
        `;
        document.head.appendChild(style);

        // Define deleteLocation in the global scope
        window.deleteLocation = function(id) {
            if (confirm('Are you sure you want to delete this meetup location?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/dashboard/meetup-locations/${id}`;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Modal elements
            const addModal = document.getElementById('add-meetup-modal');
            const editModal = document.getElementById('edit-meetup-modal');
            const addModalToggles = document.querySelectorAll('[data-modal-toggle="add-meetup-modal"]');
            const modalHides = document.querySelectorAll('[data-modal-hide]');

            // Form elements
            const fullNameInput = document.getElementById('full_name');
            const phoneInput = document.getElementById('phone');
            const showUserNamesBtn = document.getElementById('showUserNames');
            const showPhoneNumbersBtn = document.getElementById('showPhoneNumbers');
            const namesDropdown = document.getElementById('namesDropdown');
            const phoneDropdown = document.getElementById('phoneDropdown');
            const nameOptions = document.querySelectorAll('.name-option');
            const phoneOptions = document.querySelectorAll('.phone-option');
            const locationSelect = document.getElementById('location');
            const latitudeInput = document.getElementById('latitude');
            const longitudeInput = document.getElementById('longitude');

            // Initialize maps for both modals
            let addMap = null;
            let editMap = null;
            let addMarker = null;
            let editMarker = null;
            const wmsuCenter = [6.913622766161386, 122.06137404543367];

            function initMap(container, mapRef, markerRef) {
                if (!mapRef) {
                    mapRef = L.map(container).setView(wmsuCenter, 17);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(mapRef);

                    mapRef.on('click', function(e) {
                        if (markerRef) {
                            mapRef.removeLayer(markerRef);
                        }
                        markerRef = L.marker(e.latlng).addTo(mapRef);
                        container.closest('form').querySelector('input[name="latitude"]').value = e.latlng
                            .lat;
                        container.closest('form').querySelector('input[name="longitude"]').value = e.latlng
                            .lng;
                    });
                }
                return {
                    map: mapRef,
                    marker: markerRef
                };
            }

            // Toggle modal functionality with separate map handling
            function toggleModal(modal) {
                const isEdit = modal.id === 'edit-meetup-modal';
                const mapContainer = modal.querySelector('#map');

                modal.classList.toggle('hidden');
                document.body.classList.toggle('overflow-hidden');

                if (!modal.classList.contains('hidden')) {
                    setTimeout(() => {
                        if (isEdit) {
                            const result = initMap(mapContainer, editMap, editMarker);
                            editMap = result.map;
                            editMarker = result.marker;
                            editMap.invalidateSize();
                        } else {
                            const result = initMap(mapContainer, addMap, addMarker);
                            addMap = result.map;
                            addMarker = result.marker;
                            addMap.invalidateSize();
                        }
                    }, 100);
                }
            }

            addModalToggles.forEach(btn => {
                btn.addEventListener('click', () => toggleModal(addModal));
            });

            modalHides.forEach(btn => {
                btn.addEventListener('click', () => {
                    const modalId = btn.getAttribute('data-modal-hide');
                    toggleModal(document.getElementById(modalId));
                });
            });

            // Full Name Dropdown functionality
            showUserNamesBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                namesDropdown.classList.toggle('hidden');
                phoneDropdown.classList.add('hidden');
            });

            // Phone Number Dropdown functionality
            showPhoneNumbersBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                phoneDropdown.classList.toggle('hidden');
                namesDropdown.classList.add('hidden');
            });

            nameOptions.forEach(option => {
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    fullNameInput.value = this.getAttribute('data-name');
                    namesDropdown.classList.add('hidden');
                });
            });

            phoneOptions.forEach(option => {
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    phoneInput.value = this.getAttribute('data-phone');
                    phoneDropdown.classList.add('hidden');
                });
            });

            // Location Select functionality
            locationSelect.addEventListener('change', function() {
                // Get the selected option's text
                const selectedLocation = this.options[this.selectedIndex].text;

                // Set map view based on selected location
                if (this.value) {
                    const locations = {
                        'WMSU Canteen': [6.912892502432668, 122.06177697832719],
                        'WMSU Library': [6.912629451896356, 122.06052675192008],
                        'CTE Park': [6.912622978832384, 122.06106584283167],
                        'WMSU CLAW Canteen': [6.913461503920047, 122.06087352835287],
                        'Open Field': [6.9132072117384675, 122.06128139366133],
                        'Open Stage': [6.913503361915724, 122.06129880528563],
                        'Covered Court': [6.913837227944562, 122.06159582603337],
                        'Campus B mini-canteen': [6.912457153491456, 122.0635060541529],
                        'Campus B garments canteen': [6.913245294685882, 122.06340725691392],
                        'CAIS Seating Area': [6.912221735743497, 122.06342468448292],
                        'Campus A Gate': [6.912719986426812, 122.06175988294497],
                        'Campus B Gate': [6.913349602798584, 122.0625666213794],
                        // Add coordinates for other locations as needed
                    };

                    if (locations[selectedLocation]) {
                        if (marker) {
                            map.removeLayer(marker);
                        }
                        map.setView(locations[selectedLocation], 18);
                        marker = L.marker(locations[selectedLocation]).addTo(map);
                        latitudeInput.value = locations[selectedLocation][0];
                        longitudeInput.value = locations[selectedLocation][1];
                    }
                }
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!namesDropdown.contains(e.target) && !showUserNamesBtn.contains(e.target)) {
                    namesDropdown.classList.add('hidden');
                }
                if (!phoneDropdown.contains(e.target) && !showPhoneNumbersBtn.contains(e.target)) {
                    phoneDropdown.classList.add('hidden');
                }
            });

            // Close modal when clicking outside
            addModal.addEventListener('click', function(e) {
                if (e.target === addModal) {
                    toggleModal(addModal);
                }
            });

            editModal.addEventListener('click', function(e) {
                if (e.target === editModal) {
                    toggleModal(editModal);
                }
            });

            // Edit location function
            window.editLocation = function(id) {
                const meetupLocation = @json($meetupLocations);
                const location = meetupLocation.find(loc => loc.id === id);

                if (location) {
                    const form = document.getElementById('edit-form');
                    form.action = `/dashboard/meetup-locations/${id}`;

                    // Populate form fields
                    form.querySelector('#full_name').value = location.full_name;
                    form.querySelector('#phone').value = location.phone;
                    form.querySelector('#location').value = location.location_id;

                    // Set default checkbox
                    form.querySelector('input[name="is_default"]').checked = location.is_default;

                    // Show edit modal
                    toggleModal(editModal);

                    // Update map after modal is visible
                    setTimeout(() => {
                        if (editMap) {
                            editMap.invalidateSize();
                            if (editMarker) editMap.removeLayer(editMarker);
                            if (location.latitude && location.longitude) {
                                editMarker = L.marker([location.latitude, location.longitude]).addTo(
                                    editMap);
                                editMap.setView([location.latitude, location.longitude], 18);
                            }
                        }
                    }, 100);
                }
            };

            // Reset form when opening modal for new location
            addModalToggles.forEach(btn => {
                btn.addEventListener('click', function() {
                    const form = document.getElementById('meetup-form');
                    const modalTitle = document.getElementById('modal-title');

                    // Reset form for adding
                    form.reset();
                    form.action = "{{ route('meetup-locations.store') }}";
                    form.querySelector('input[name="_method"]').value = 'POST';
                    modalTitle.textContent = 'Add Meetup Location';

                    // Reset map
                    if (map) {
                        if (marker) map.removeLayer(marker);
                        map.setView(wmsuCenter, 17);
                    }
                });
            });

            // Update location select handler for both modals
            function setupLocationSelectHandler(modal, mapInstance, markerRef) {
                const locationSelect = modal.querySelector('select[name="location_id"]');
                locationSelect.addEventListener('change', function() {
                    // Get the selected option's text
                    const selectedLocation = this.options[this.selectedIndex].text;

                    // Set map view based on selected location
                    if (this.value) {
                        const locations = {
                            'WMSU Canteen': [6.912892502432668, 122.06177697832719],
                            'WMSU Library': [6.912629451896356, 122.06052675192008],
                            'CTE Park': [6.912622978832384, 122.06106584283167],
                            'WMSU CLAW Canteen': [6.913461503920047, 122.06087352835287],
                            'Open Field': [6.9132072117384675, 122.06128139366133],
                            'Open Stage': [6.913503361915724, 122.06129880528563],
                            'Covered Court': [6.913837227944562, 122.06159582603337],
                            'Campus B mini-canteen': [6.912457153491456, 122.0635060541529],
                            'Campus B garments canteen': [6.913245294685882, 122.06340725691392],
                            'CAIS Seating Area': [6.912221735743497, 122.06342468448292],
                            'Campus A Gate': [6.912719986426812, 122.06175988294497],
                            'Campus B Gate': [6.913349602798584, 122.0625666213794],
                        };

                        if (locations[selectedLocation] && mapInstance) {
                            if (markerRef) {
                                mapInstance.removeLayer(markerRef);
                            }
                            mapInstance.setView(locations[selectedLocation], 18);
                            markerRef = L.marker(locations[selectedLocation]).addTo(mapInstance);

                            // Update hidden inputs
                            modal.querySelector('input[name="latitude"]').value = locations[
                                selectedLocation][0];
                            modal.querySelector('input[name="longitude"]').value = locations[
                                selectedLocation][1];
                        }
                    }
                });
            }

            // Setup location handlers for both modals
            addModal && setupLocationSelectHandler(addModal, addMap, addMarker);
            editModal && setupLocationSelectHandler(editModal, editMap, editMarker);

            // Update initMap function to return the marker reference
            function initMap(container, mapRef, markerRef) {
                if (!mapRef) {
                    mapRef = L.map(container).setView(wmsuCenter, 17);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(mapRef);

                    mapRef.on('click', function(e) {
                        if (markerRef) {
                            mapRef.removeLayer(markerRef);
                        }
                        markerRef = L.marker(e.latlng).addTo(mapRef);
                        container.closest('form').querySelector('input[name="latitude"]').value = e.latlng
                            .lat;
                        container.closest('form').querySelector('input[name="longitude"]').value = e.latlng
                            .lng;
                    });
                }
                return {
                    map: mapRef,
                    marker: markerRef
                };
            }

            // Update toggleModal to setup location handler after map initialization
            function toggleModal(modal) {
                const isEdit = modal.id === 'edit-meetup-modal';
                const mapContainer = modal.querySelector('#map');

                modal.classList.toggle('hidden');
                document.body.classList.toggle('overflow-hidden');

                if (!modal.classList.contains('hidden')) {
                    setTimeout(() => {
                        if (isEdit) {
                            const result = initMap(mapContainer, editMap, editMarker);
                            editMap = result.map;
                            editMarker = result.marker;
                            editMap.invalidateSize();
                            setupLocationSelectHandler(modal, editMap, editMarker);
                        } else {
                            const result = initMap(mapContainer, addMap, addMarker);
                            addMap = result.map;
                            addMarker = result.marker;
                            addMap.invalidateSize();
                            setupLocationSelectHandler(modal, addMap, addMarker);
                        }
                    }, 100);
                }
            }

            // Update location select handler
            document.getElementById('location').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value !== 'other' && selectedOption.value !== '') {
                    const lat = selectedOption.dataset.lat;
                    const lng = selectedOption.dataset.lng;

                    if (marker) map.removeLayer(marker);
                    marker = L.marker([lat, lng]).addTo(map);
                    map.setView([lat, lng], 18);

                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lng;
                }
            });
        });
    </script>

@endsection

@push('scripts')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('styles')
    <style>
        /* Add styles for switch transition */
        .peer:checked~div {
            background-color: var(--primary-color);
        }

        .peer:checked~div:after {
            transform: translateX(100%);
            border-color: white;
        }
    </style>
@endpush

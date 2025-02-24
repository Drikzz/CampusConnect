@extends('dashboard.dashboard')

@section('dashboard-content')
    <div class="max-w-4xl mx-auto">
        <!-- Header section -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold">Meetup Locations & Schedule</h2>
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
                        <!-- Location Info -->
                        <div class="flex justify-between items-start mb-4">
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
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="editLocation({{ $meetupLocation->id }})"
                                    class="text-gray-600 hover:text-gray-900">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button type="button" onclick="deleteMeetupLocation({{ $meetupLocation->id }}, this)"
                                    class="text-red-600 hover:text-red-900" data-location-id="{{ $meetupLocation->id }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Schedule Info -->
                        <div class="mt-4 border-t pt-4">
                            <h4 class="font-medium text-gray-900 mb-2">Available Schedule</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Available Days -->
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Available Days:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach (json_decode($meetupLocation->available_days) ?? [] as $day)
                                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">
                                                {{ $day }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Operating Hours -->
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Operating Hours:</p>
                                    @if ($meetupLocation->available_from && $meetupLocation->available_until)
                                        <p class="text-sm">
                                            {{ \Carbon\Carbon::parse($meetupLocation->available_from)->format('g:i A') }}
                                            -
                                            {{ \Carbon\Carbon::parse($meetupLocation->available_until)->format('g:i A') }}
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-500 italic">Not set</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Max Meetups -->
                            <div class="mt-2">
                                <p class="text-sm text-gray-600">
                                    Maximum meetups per day:
                                    <span class="font-medium text-gray-900">
                                        {{ $meetupLocation->max_daily_meetups }}
                                    </span>
                                </p>
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
            <div class="relative bg-white rounded-lg shadow max-h-[90vh] overflow-y-auto">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                    <h3 class="text-xl font-semibold text-gray-900">
                        Add Meetup Location
                    </h3>
                    <button type="button" data-modal-hide="add-meetup-modal"
                        class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                    </button>
                </div>

                <!-- Modal body -->
                <form action="{{ route('meetup-locations.store') }}" method="POST" class="p-4 md:p-5" id="addMeetupForm">
                    @csrf
                    <input type="hidden" name="_method" value="POST">
                    <input type="hidden" name="latitude" id="add-latitude">
                    <input type="hidden" name="longitude" id="add-longitude">

                    <div class="grid gap-4 mb-4">
                        <!-- Basic Information Section -->
                        <div class="border-b pb-4">
                            <h4 class="font-medium text-gray-900 mb-3">Basic Information</h4>
                            <!-- Full Name Field -->
                            <div>
                                <label for="full_name" class="block mb-2 text-sm font-medium text-gray-900">Full
                                    Name</label>
                                <div class="relative">
                                    <input type="text" name="full_name" id="full_name"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                        value="{{ $user->first_name . ' ' . $user->last_name }}" required>
                                </div>
                            </div>

                            <!-- Phone Number Field -->
                            <div>
                                <label for="phone" class="block mb-2 text-sm font-medium text-gray-900">Phone
                                    Number</label>
                                <div class="relative">
                                    <input type="text" name="phone" id="phone"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                        value="{{ $user->phone }}" required>
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

                        <!-- Schedule Section -->
                        <div class="border-b pb-4">
                            <h4 class="font-medium text-gray-900 mb-3">Availability Schedule</h4>

                            <!-- Available Days -->
                            <div class="mb-4">
                                <label class="block mb-2 text-sm font-medium text-gray-900">Available Days</label>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="available_days[]" value="{{ $day }}"
                                                class="rounded border-gray-300 text-primary-color focus:ring-primary-color">
                                            <span class="ml-2 text-sm">{{ $day }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Operating Hours -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="available_from" class="block mb-2 text-sm font-medium text-gray-900">
                                        Available From
                                    </label>
                                    <div class="relative">
                                        <input type="time" name="available_from" id="available_from"
                                            class="bg-gray-50 border leading-none border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                            required>
                                        <div class="absolute inset-y-0 end-0 flex items-center pe-3.5 pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-500" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="available_until" class="block mb-2 text-sm font-medium text-gray-900">
                                        Available Until
                                    </label>
                                    <div class="relative">
                                        <input type="time" name="available_until" id="available_until"
                                            class="bg-gray-50 border leading-none border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                            required>
                                        <div class="absolute inset-y-0 end-0 flex items-center pe-3.5 pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-500" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Max Daily Meetups -->
                            <div>
                                <label for="max_daily_meetups" class="block mb-2 text-sm font-medium text-gray-900">
                                    Maximum Meetups Per Day
                                </label>
                                <input type="number" name="max_daily_meetups" id="max_daily_meetups"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                    min="1" max="50" value="5">
                                <p class="mt-1 text-sm text-gray-500">
                                    Recommended: 5 meetups per day to balance availability with your study schedule
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
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Meetup Location Modal -->
    <div id="edit-meetup-modal" tabindex="-1" role="dialog"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 flex items-center justify-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" aria-hidden="true"></div>
        <div class="relative p-4 w-full max-w-2xl max-h-full z-10">
            <div class="relative bg-white rounded-lg shadow max-h-[90vh] overflow-y-auto">
                <!-- Modal header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                    <h3 class="text-xl font-semibold text-gray-900">Edit Meetup Location</h3>
                    <button type="button" onclick="closeEditModal()"
                        class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 14 14">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                        </svg>
                        <span class="sr-only">Close modal</span>
                    </button>
                </div>

                <!-- Modal form -->
                <form id="editMeetupForm" method="POST" class="p-4 md:p-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="latitude" id="edit-latitude">
                    <input type="hidden" name="longitude" id="edit-longitude">

                    <div class="grid gap-4 mb-4">
                        <!-- Basic Information Section -->
                        <div class="border-b pb-4">
                            <h4 class="font-medium text-gray-900 mb-3">Basic Information</h4>
                            <!-- Full Name Field -->
                            <div>
                                <label for="edit-full_name" class="block mb-2 text-sm font-medium text-gray-900">Full
                                    Name</label>
                                <div class="relative">
                                    <input type="text" name="full_name" id="edit-full_name"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                        required>
                                </div>
                            </div>

                            <!-- Phone Number Field -->
                            <div>
                                <label for="edit-phone" class="block mb-2 text-sm font-medium text-gray-900">Phone
                                    Number</label>
                                <div class="relative">
                                    <input type="text" name="phone" id="edit-phone"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                        required>
                                </div>
                            </div>

                            <!-- Meetup z Field -->
                            <div>
                                <label for="edit-location_id" class="block mb-2 text-sm font-medium text-gray-900">Meetup
                                    Location</label>
                                <select name="location_id" id="edit-location"
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
                                <div id="edit-map" class="h-64 rounded-lg border border-gray-300"></div>
                                <p class="mt-2 text-sm text-gray-500">
                                    Click on the map to set the exact meetup location
                                </p>
                            </div>
                        </div>

                        <!-- Schedule Section -->
                        <div class="border-b pb-4">
                            <h4 class="font-medium text-gray-900 mb-3">Availability Schedule</h4>

                            <!-- Available Days -->
                            <div class="mb-4">
                                <label class="block mb-2 text-sm font-medium text-gray-900">Available Days</label>
                                <div class="grid grid-cols-3 gap-2">
                                    @foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                                        <label class="inline-flex items-center">
                                            <input type="checkbox" name="available_days[]" value="{{ $day }}"
                                                class="rounded border-gray-300 text-primary-color focus:ring-primary-color">
                                            <span class="ml-2 text-sm">{{ $day }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Operating Hours -->
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label for="edit-available_from" class="block mb-2 text-sm font-medium text-gray-900">
                                        Available From
                                    </label>
                                    <div class="relative">
                                        <input type="time" name="available_from" id="edit-available_from"
                                            class="bg-gray-50 border leading-none border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                            required>
                                        <div class="absolute inset-y-0 end-0 flex items-center pe-3.5 pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-500" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label for="edit-available_until"
                                        class="block mb-2 text-sm font-medium text-gray-900">
                                        Available Until
                                    </label>
                                    <div class="relative">
                                        <input type="time" name="available_until" id="edit-available_until"
                                            class="bg-gray-50 border leading-none border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                            required>
                                        <div class="absolute inset-y-0 end-0 flex items-center pe-3.5 pointer-events-none">
                                            <svg class="w-4 h-4 text-gray-500" aria-hidden="true"
                                                xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                                viewBox="0 0 24 24">
                                                <path fill-rule="evenodd"
                                                    d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Max Daily Meetups -->
                            <div>
                                <label for="edit-max_daily_meetups" class="block mb-2 text-sm font-medium text-gray-900">
                                    Maximum Meetups Per Day
                                </label>
                                <input type="number" name="max_daily_meetups" id="edit-max_daily_meetups"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5"
                                    min="1" max="50" value="5">
                                <p class="mt-1 text-sm text-gray-500">
                                    Recommended: 5 meetups per day to balance availability with your study schedule
                                </p>
                            </div>
                        </div>

                        <!-- Default Location Checkbox -->
                        <div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_default" id="edit-is_default" class="sr-only peer"
                                    value="1">
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
                                Save Changes
                            </button>
                            <button type="button" onclick="closeEditModal()"
                                class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900">
                                Cancel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize maps
            let addMap = null,
                editMap = null;
            let addMarker = null,
                editMarker = null;
            const wmsuCenter = [6.913622766161386, 122.06137404543367];

            // Initialize add map
            function initAddMap() {
                if (!addMap) {
                    addMap = L.map('map').setView(wmsuCenter, 17);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(addMap);

                    addMap.on('click', function(e) {
                        if (addMarker) addMap.removeLayer(addMarker);
                        addMarker = L.marker(e.latlng).addTo(addMap);
                        document.getElementById('add-latitude').value = e.latlng.lat;
                        document.getElementById('add-longitude').value = e.latlng.lng;
                    });
                }
                setTimeout(() => addMap.invalidateSize(), 100);
            }

            // Initialize edit map
            function initEditMap() {
                if (!editMap) {
                    editMap = L.map('edit-map').setView(wmsuCenter, 17);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(editMap);

                    editMap.on('click', function(e) {
                        if (editMarker) editMap.removeLayer(editMarker);
                        editMarker = L.marker(e.latlng).addTo(editMap);
                        document.getElementById('edit-latitude').value = e.latlng.lat;
                        document.getElementById('edit-longitude').value = e.latlng.lng;
                    });
                }
                setTimeout(() => editMap.invalidateSize(), 100);
            }

            // Modal handling functions
            function closeEditModal() {
                document.getElementById('edit-meetup-modal').classList.add('hidden');
                document.getElementById('editMeetupForm').reset();
            }

            window.closeEditModal = closeEditModal;

            // Edit location function
            window.editLocation = function(locationId) {
                const location = @json($meetupLocations).find(loc => loc.id === locationId);
                if (!location) return;

                // Show modal and initialize map
                document.getElementById('edit-meetup-modal').classList.remove('hidden');

                setTimeout(() => {
                    initEditMap();
                    if (editMarker) editMap.removeLayer(editMarker);
                    editMarker = L.marker([location.latitude, location.longitude]).addTo(editMap);
                    editMap.setView([location.latitude, location.longitude], 18);
                }, 100);

                // Set form action
                const form = document.getElementById('editMeetupForm');
                form.action = `/dashboard/meetup-locations/${locationId}`;

                // Fill form fields
                document.getElementById('edit-full_name').value = location.full_name;
                document.getElementById('edit-phone').value = location.phone;
                document.getElementById('edit-location').value = location.location_id;
                document.getElementById('edit-latitude').value = location.latitude;
                document.getElementById('edit-longitude').value = location.longitude;
                document.getElementById('edit-available_from').value = location.available_from.slice(0, 5);
                document.getElementById('edit-available_until').value = location.available_until.slice(0, 5);
                document.getElementById('edit-max_daily_meetups').value = location.max_daily_meetups;
                document.getElementById('edit-is_default').checked = location.is_default;

                // Handle available days
                const availableDays = JSON.parse(location.available_days || '[]');
                form.querySelectorAll('input[name="available_days[]"]').forEach(checkbox => {
                    checkbox.checked = availableDays.includes(checkbox.value);
                });
            };

            // Delete function
            window.deleteMeetupLocation = function(locationId, buttonElement) {
                if (!confirm('Are you sure you want to delete this meetup location?')) return;

                fetch(`/dashboard/meetup-locations/${locationId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const locationCard = buttonElement.closest('.bg-white');
                            locationCard.remove();

                            const remainingLocations = document.querySelectorAll('.grid.gap-6 > div')
                                .length;
                            if (remainingLocations === 0) {
                                const container = document.querySelector('.grid.gap-6').parentElement;
                                container.innerHTML = `
                                <div class="text-center py-8 bg-gray-50 rounded-lg">
                                    <p class="text-gray-600">No meetup locations. Add one to get started.</p>
                                </div>
                            `;
                            }
                            showNotification('Location deleted successfully', 'success');
                        } else {
                            showNotification(data.message || 'Failed to delete location', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Error deleting location', 'error');
                    });
            };

            // Click outside to close modals
            document.getElementById('edit-meetup-modal').addEventListener('click', function(e) {
                if (e.target === this) closeEditModal();
            });

            // Initialize add map when add modal is opened
            document.querySelector('[data-modal-toggle="add-meetup-modal"]').addEventListener('click', () => {
                setTimeout(initAddMap, 100);
            });

            // Update location select handler for add form
            document.querySelector('#location').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value && addMap) {
                    const lat = parseFloat(selectedOption.dataset.lat);
                    const lng = parseFloat(selectedOption.dataset.lng);

                    // Remove existing marker
                    if (addMarker) {
                        addMap.removeLayer(addMarker);
                    }

                    // Add new marker and update form values
                    addMarker = L.marker([lat, lng]).addTo(addMap);
                    addMap.setView([lat, lng], 18);

                    document.getElementById('add-latitude').value = lat;
                    document.getElementById('add-longitude').value = lng;
                }
            });

            // Update location select handler for edit form
            document.querySelector('#edit-location').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value && editMap) {
                    const lat = parseFloat(selectedOption.dataset.lat);
                    const lng = parseFloat(selectedOption.dataset.lng);

                    // Remove existing marker
                    if (editMarker) {
                        editMap.removeLayer(editMarker);
                    }

                    // Add new marker and update form values
                    editMarker = L.marker([lat, lng]).addTo(editMap);
                    editMap.setView([lat, lng], 18);

                    document.getElementById('edit-latitude').value = lat;
                    document.getElementById('edit-longitude').value = lng;
                }
            });

            // Initialize add map
            function initAddMap() {
                if (!addMap) {
                    addMap = L.map('map').setView(wmsuCenter, 17);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(addMap);

                    // Add click handler
                    addMap.on('click', function(e) {
                        if (addMarker) {
                            addMap.removeLayer(addMarker);
                        }
                        addMarker = L.marker(e.latlng).addTo(addMap);
                        document.getElementById('add-latitude').value = e.latlng.lat;
                        document.getElementById('add-longitude').value = e.latlng.lng;
                    });

                    // Set initial marker if location is selected
                    const locationSelect = document.querySelector('#location');
                    if (locationSelect.value) {
                        const selectedOption = locationSelect.options[locationSelect.selectedIndex];
                        const lat = parseFloat(selectedOption.dataset.lat);
                        const lng = parseFloat(selectedOption.dataset.lng);
                        if (lat && lng) {
                            addMarker = L.marker([lat, lng]).addTo(addMap);
                            addMap.setView([lat, lng], 18);
                            document.getElementById('add-latitude').value = lat;
                            document.getElementById('add-longitude').value = lng;
                        }
                    }
                }
                addMap.invalidateSize();
            }
        });
    </script>

    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    @endpush

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add form submission handler
            document.getElementById('addMeetupForm').addEventListener('submit', function(e) {
                e.preventDefault();
                handleFormSubmission(this);
            });

            // Edit form submission handler
            document.getElementById('editMeetupForm').addEventListener('submit', function(e) {
                e.preventDefault();
                handleFormSubmission(this);
            });

            // Generic form submission handler for both add and edit
            function handleFormSubmission(form) {
                const formData = new FormData(form);

                fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.needs_confirmation) {
                            if (confirm(data.message)) {
                                // Add confirmation flag and resubmit
                                formData.append('confirmed_default_change', '1');
                                fetch(form.action, {
                                        method: 'POST',
                                        body: formData,
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json',
                                        },
                                        credentials: 'same-origin'
                                    })
                                    .then(response => response.json())
                                    .then(result => {
                                        if (result.success) {
                                            window.location.href = result.redirect;
                                        } else {
                                            alert(result.message || 'An error occurred');
                                        }
                                    });
                            }
                        } else if (data.success) {
                            window.location.href = data.redirect;
                        } else {
                            alert(data.message || 'An error occurred');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while processing the request');
                    });
            }
        });
    </script>

@endsection

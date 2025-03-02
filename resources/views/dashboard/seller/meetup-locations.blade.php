@extends('dashboard.dashboard')

@section('dashboard-content')
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">Manage Meetup Locations</h2>
            <button id="addLocationBtn" class="bg-primary-color text-white px-4 py-2 rounded-lg">
                Add New Location
            </button>
        </div>

        <!-- Locations List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($meetupLocations as $location)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-medium">{{ $location->name }}</div>
                                @if ($location->landmark)
                                    <div class="text-sm text-gray-500">Landmark: {{ $location->landmark }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $location->address }}</td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $location->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $location->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button class="edit-location text-blue-600 hover:text-blue-800"
                                    data-id="{{ $location->id }}">
                                    Edit
                                </button>
                                <button class="delete-location text-red-600 hover:text-red-800"
                                    data-id="{{ $location->id }}">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Location Modal -->
    <div id="locationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modalTitle">Add New Location</h3>
                <form id="locationForm" class="mt-4 text-left">
                    @csrf
                    <input type="hidden" name="location_id" id="location_id">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" id="location_name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Address</label>
                            <input type="text" name="address" id="location_address"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Landmark (Optional)</label>
                            <input type="text" name="landmark" id="location_landmark"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                            <textarea name="description" id="location_description" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end space-x-2">
                        <button type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 border rounded-md"
                            onclick="closeModal()">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary-color hover:bg-primary-color/90 rounded-md">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Add your JavaScript for managing the modal and form submissions here
        // Include CRUD operations for meetup locations
    </script>
@endsection

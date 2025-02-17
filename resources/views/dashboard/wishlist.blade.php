@extends('dashboard.dashboard')

@section('dashboard-content')
    <h2 class="text-2xl font-bold mb-6">My Wishlist</h2>

    @if ($wishlists->isEmpty())
        <div class="text-center py-8">
            <p class="text-gray-500">Your wishlist is empty</p>
            <a href="{{ route('products') }}" class="text-primary-color hover:underline mt-2 inline-block">
                Browse Products
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($wishlists as $wishlist)
                <div class="bg-white p-4 rounded-lg shadow flex items-center gap-4">
                    <img src="{{ asset('storage/' . $wishlist->product->image) }}" alt="{{ $wishlist->product->name }}"
                        class="w-20 h-20 object-cover rounded">

                    <div class="flex-1">
                        <h3 class="font-semibold">{{ $wishlist->product->name }}</h3>
                        <p class="text-primary-color">₱{{ number_format($wishlist->product->price, 2) }}</p>

                        <div class="flex gap-2 mt-2">
                            <a href="{{ route('prod.details', $wishlist->product->id) }}"
                                class="text-sm text-primary-color hover:underline">
                                View Product
                            </a>
                            <form action="{{ route('wishlist.remove', $wishlist->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-500 hover:underline">
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $wishlists->links() }}
        </div>
    @endif
@endsection

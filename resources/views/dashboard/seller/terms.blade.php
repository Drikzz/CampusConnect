@extends('dashboard.dashboard')

@section('dashboard-content')
    {{-- Use the existing terms content but wrapped in dashboard layout --}}
    <div class="max-w-4xl mx-auto">
        {{-- Copy content from existing terms.blade.php but update the form action --}}
        <form action="{{ route('dashboard.seller.terms.accept') }}" method="POST">
            // ...existing terms content...
        </form>
    </div>
@endsection

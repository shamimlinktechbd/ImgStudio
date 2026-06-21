@extends('layouts.app')

@section('content')
    <div class="section-head">
        <h1>All images</h1>
        <form class="search-form" method="GET" action="{{ route('admin.images') }}">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search file or category">
            <button class="button small" type="submit">Search</button>
        </form>
    </div>

    <section class="image-grid">
        @forelse($images as $image)
            <article class="image-card">
                <a href="{{ route('images.show', $image) }}">
                    <img src="{{ asset('storage/' . $image->displayPath()) }}" alt="{{ $image->original_name }}">
                </a>
                <div class="card-body">
                    <strong>{{ $image->original_name }}</strong>
                    <span>{{ optional($image->user)->email ?: 'Guest' }} · {{ $image->category ?: 'No category' }}</span>
                    <div class="action-row">
                        <a class="button secondary small" href="{{ route('images.download', $image) }}">Download</a>
                        <form method="POST" action="{{ route('images.destroy', $image) }}">
                            @csrf
                            @method('DELETE')
                            <button class="button danger small" type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <p class="muted">No images found.</p>
        @endforelse
    </section>

    {{ $images->links() }}
@endsection

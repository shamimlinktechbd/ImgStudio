@extends('layouts.app')

@section('content')
    <div class="section-head">
        <h1>Image history</h1>
        <a class="button small" href="{{ route('images.index') }}">New upload</a>
    </div>

    <section class="image-grid">
        @forelse($images as $image)
            <article class="image-card">
                <a href="{{ route('images.show', $image) }}">
                    <img src="{{ asset('storage/' . $image->displayPath()) }}" alt="{{ $image->original_name }}">
                </a>
                <div class="card-body">
                    <strong>{{ $image->original_name }}</strong>
                    <span>{{ ucfirst($image->last_action) }} · {{ $image->created_at->diffForHumans() }}</span>
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
            <p class="muted">History empty.</p>
        @endforelse
    </section>

    {{ $images->links() }}
@endsection

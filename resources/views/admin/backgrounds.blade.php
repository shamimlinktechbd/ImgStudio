@extends('layouts.app')

@section('content')
    <div class="section-head">
        <h1>Background library</h1>
        <a class="button small" href="{{ route('admin.dashboard') }}">Dashboard</a>
    </div>

    <section class="workspace-grid">
        <div class="panel">
            <h2>Upload background</h2>
            <form class="stack-form" method="POST" action="{{ route('admin.backgrounds.store') }}" enctype="multipart/form-data">
                @csrf
                <label>
                    <span>Name</span>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                </label>
                <label>
                    <span>Category</span>
                    <input type="text" name="category" value="{{ old('category') }}" placeholder="Studio, Nature, Office">
                </label>
                <label>
                    <span>Background image</span>
                    <input type="file" name="background" accept="image/png,image/jpeg,image/webp" required>
                </label>
                <button class="button" type="submit">Upload background</button>
            </form>
        </div>

        <aside class="panel">
            <h2>How users use these</h2>
            <p>Image editor page-e user AI background remove kore ekhane uploaded background select korte parbe.</p>
        </aside>
    </section>

    <section class="image-grid spaced">
        @forelse($backgrounds as $background)
            <article class="image-card">
                <img src="{{ asset('storage/' . $background->path) }}" alt="{{ $background->name }}">
                <div class="card-body">
                    <strong>{{ $background->name }}</strong>
                    <span>{{ $background->category ?: 'No category' }} · {{ $background->width }} x {{ $background->height }}</span>
                    <form method="POST" action="{{ route('admin.backgrounds.destroy', $background) }}">
                        @csrf
                        @method('DELETE')
                        <button class="button danger small" type="submit">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="muted">No backgrounds uploaded yet.</p>
        @endforelse
    </section>

    {{ $backgrounds->links() }}
@endsection

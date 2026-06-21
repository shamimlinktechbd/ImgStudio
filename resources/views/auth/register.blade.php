@extends('layouts.app')

@section('content')
    <section class="auth-panel">
        <h1>Register</h1>
        <form class="stack-form" method="POST" action="{{ route('register.store') }}">
            @csrf
            <label>
                <span>Name</span>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus>
            </label>
            <label>
                <span>Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>
            <label>
                <span>Password</span>
                <input type="password" name="password" required>
            </label>
            <label>
                <span>Confirm password</span>
                <input type="password" name="password_confirmation" required>
            </label>
            <button class="button" type="submit">Create account</button>
        </form>
    </section>
@endsection

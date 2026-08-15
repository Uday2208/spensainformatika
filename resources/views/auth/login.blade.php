@extends('layouts.public')
@section('title', 'Login - Sistem Akademik')
@section('content')
<div class="min-h-[80vh] flex items-center justify-center bg-slate-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-2xl shadow-xl border border-slate-100">
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-slate-900">
                Masuk Sistem
            </h2>
            <p class="mt-2 text-center text-sm text-slate-600">
                Gunakan username dan password Anda
            </p>
        </div>
        <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
            @csrf
            
            @if ($errors->any())
                <div class="bg-red-50 text-red-500 p-3 rounded-md text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="username" class="sr-only">Username</label>
                    <input id="username" name="username" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-slate-300 placeholder-slate-500 text-slate-900 rounded-t-md focus:outline-none focus:ring-brand focus:border-brand focus:z-10 sm:text-sm" placeholder="Username / Nomer Induk">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-3 border border-slate-300 placeholder-slate-500 text-slate-900 rounded-b-md focus:outline-none focus:ring-brand focus:border-brand focus:z-10 sm:text-sm" placeholder="Password">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-brand hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand transition shadow-lg shadow-blue-500/30">
                    Sign in
                </button>
            </div>
            
            <div class="text-center mt-4">
                <a href="{{ url('/') }}" class="text-sm font-medium text-brand hover:text-blue-500">
                    &larr; Kembali ke Beranda
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.turtube')

@section('title', 'Canlı Yayın Oluştur')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="rounded-3xl border border-gray-800 bg-gray-900 p-6 sm:p-8">
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-400">Creator araçları</p>
        <h1 class="mt-2 text-3xl font-bold text-white">Canlı yayın planla</h1>
        <p class="mt-3 text-gray-400">Yayını oluşturduktan sonra yayın anahtarını yayın yazılımına ekleyebilirsin.</p>

        <form method="POST" action="{{ route('live.store') }}" class="mt-8 space-y-6">
            @csrf
            <div>
                <label class="mb-2 block font-medium text-white">Yayın başlığı</label>
                <input name="title" value="{{ old('title') }}" required class="w-full rounded-xl border border-gray-700 bg-gray-950 px-4 py-3 text-white focus:border-red-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-2 block font-medium text-white">Açıklama</label>
                <textarea name="description" rows="5" class="w-full rounded-xl border border-gray-700 bg-gray-950 px-4 py-3 text-white focus:border-red-500 focus:outline-none">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="mb-2 block font-medium text-white">Planlanan başlangıç</label>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="w-full rounded-xl border border-gray-700 bg-gray-950 px-4 py-3 text-white focus:border-red-500 focus:outline-none">
            </div>
            <div>
                <label class="mb-2 block font-medium text-white">Oynatıcı URL’si <span class="text-sm font-normal text-gray-500">(opsiyonel)</span></label>
                <input type="url" name="stream_url" value="{{ old('stream_url') }}" placeholder="https://..." class="w-full rounded-xl border border-gray-700 bg-gray-950 px-4 py-3 text-white focus:border-red-500 focus:outline-none">
            </div>
            <button class="rounded-xl bg-red-600 px-6 py-3 font-semibold text-white transition hover:bg-red-700">Yayını oluştur</button>
        </form>
    </div>
</div>
@endsection

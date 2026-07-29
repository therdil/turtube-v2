@if (session('success'))
    <div class="mb-6 flex items-start justify-between gap-4 rounded-xl border border-green-700/70 bg-green-900/30 px-5 py-4 text-green-200" role="status">
        <span>{{ session('success') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-red-700/70 bg-red-900/30 px-5 py-4 text-red-200" role="alert">
        <p class="font-semibold">Lütfen aşağıdaki alanları kontrol et.</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

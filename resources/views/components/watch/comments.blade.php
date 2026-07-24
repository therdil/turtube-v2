@if (session('success'))
    <div class="mb-6 rounded-xl border border-green-700 bg-green-900/30 p-4 text-green-300">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-xl border border-red-700 bg-red-900/30 p-4 text-red-300">
        <ul class="list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mt-8">

    <h2 class="mb-6 text-2xl font-bold text-white">
        Yorumlar ({{ $video->comments->count() }})
    </h2>

    @auth

        <form
            method="POST"
            action="{{ route('comments.store', $video) }}"
            class="mb-8">

            @csrf

            <textarea
                name="comment"
                rows="4"
                maxlength="2000"
                class="w-full rounded-xl border border-gray-700 bg-gray-900 p-4 text-white placeholder-gray-500 focus:border-red-500 focus:outline-none"
                placeholder="Yorumunu yaz..."
                required>{{ old('comment') }}</textarea>

            <div class="mt-4 flex justify-end">

                <x-ui.button type="submit">
                    Yorumu Gönder
                </x-ui.button>

            </div>

        </form>

    @else

        <div class="mb-8 rounded-xl border border-yellow-700 bg-yellow-900/20 p-4 text-yellow-300">
            Yorum yapabilmek için giriş yapmalısın.
        </div>

    @endauth

    <div class="space-y-6">

        @forelse ($video->comments as $comment)

            <x-ui.card>

                <div class="flex items-start gap-4">

                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-600 font-bold text-white">
                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                    </div>

                    <div class="flex-1">

                        <div class="flex items-center justify-between gap-4">

                            <div class="flex items-center gap-3">

                                <span class="font-semibold text-white">
                                    {{ $comment->user->name }}
                                </span>

                                <span class="text-sm text-gray-500">
                                    {{ $comment->created_at->diffForHumans() }}
                                </span>

                            </div>

                            @auth
                                @if ($comment->user_id === auth()->id())

                                    <form
                                        method="POST"
                                        action="{{ route('comments.destroy', $comment) }}"
                                        onsubmit="return confirm('Bu yorumu silmek istediğine emin misin?')">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="rounded-lg bg-red-600 px-3 py-1 text-sm font-medium text-white transition hover:bg-red-700">
                                            Sil
                                        </button>

                                    </form>

                                @endif
                            @endauth

                        </div>

                        <p class="mt-3 whitespace-pre-line text-gray-300">
                            {{ $comment->comment }}
                        </p>

                    </div>

                </div>

            </x-ui.card>

        @empty

            <x-ui.card>

                <p class="text-gray-400">
                    Henüz yorum yapılmamış. İlk yorumu sen yap!
                </p>

            </x-ui.card>

        @endforelse

    </div>

</div>
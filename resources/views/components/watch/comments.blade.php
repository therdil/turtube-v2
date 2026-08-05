<div class="mt-8">
    <h2 class="mb-6 text-2xl font-bold text-white">Yorumlar ({{ $video->comments->count() }})</h2>

    @auth
        <form method="POST" action="{{ route('comments.store', $video) }}" class="mb-8 rounded-2xl border border-gray-800 bg-gray-900 p-4">
            @csrf
            <textarea id="new-comment" name="comment" rows="4" maxlength="2000" class="w-full rounded-xl border border-gray-700 bg-gray-950 p-4 text-white placeholder-gray-500 focus:border-red-500 focus:outline-none" placeholder="Yorumunu yaz... Emoji kullanabilirsin 😀" required>{{ old('comment') }}</textarea>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                <div class="flex gap-1" aria-label="Emoji seç">
                    @foreach (['😀', '❤️', '👏', '🔥', '🎉'] as $emoji)
                        <button type="button" data-insert-emoji="{{ $emoji }}" data-emoji-target="new-comment" class="rounded-lg px-2 py-1 hover:bg-gray-800">{{ $emoji }}</button>
                    @endforeach
                </div>
                <x-ui.button type="submit">Yorumu Gönder</x-ui.button>
            </div>
        </form>
    @else
        <div class="mb-8 rounded-xl border border-yellow-700 bg-yellow-900/20 p-4 text-yellow-300">Yorum yapabilmek için giriş yapmalısın.</div>
    @endauth

    <div class="space-y-5">
        @forelse ($video->comments as $comment)
            <x-ui.card>
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-600 font-bold text-white">{{ strtoupper(substr($comment->user->name, 0, 1)) }}</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="font-semibold text-white">{{ $comment->user->name }}</span>
                                @if ($comment->is_pinned)<span class="rounded-full bg-amber-400/15 px-2 py-1 text-xs font-semibold text-amber-300">Sabit yorum</span>@endif
                                <span class="text-sm text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            @auth
                                <div class="flex flex-wrap gap-2">
                                    @if ($video->user_id === auth()->id() || auth()->user()->is_admin)
                                        <form method="POST" action="{{ route('comments.pin', [$video, $comment]) }}">@csrf @method('PATCH')<button class="rounded-lg border border-amber-500/50 px-3 py-1 text-sm font-medium text-amber-200 hover:bg-amber-500 hover:text-gray-950">{{ $comment->is_pinned ? 'Sabiti kaldır' : 'Sabitle' }}</button></form>
                                    @endif
                                    @if ($comment->user_id === auth()->id() || $video->user_id === auth()->id() || auth()->user()->is_admin)
                                        <form method="POST" action="{{ route('comments.destroy', $comment) }}" onsubmit="return confirm('Bu yorumu silmek istediğine emin misin?')">@csrf @method('DELETE')<button class="rounded-lg bg-red-600 px-3 py-1 text-sm font-medium text-white hover:bg-red-700">Sil</button></form>
                                    @endif
                                </div>
                            @endauth
                        </div>

                        <p class="mt-3 whitespace-pre-line text-gray-300">{{ $comment->comment }}</p>

                        @auth
                            <div class="mt-4 flex flex-wrap gap-3">
                                <details>
                                    <summary class="cursor-pointer text-sm font-semibold text-gray-400 hover:text-white">Yanıtla</summary>
                                    <form method="POST" action="{{ route('comments.store', $video) }}" class="mt-3 rounded-xl bg-gray-950 p-3">
                                        @csrf
                                        <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                                        <textarea id="reply-{{ $comment->id }}" name="comment" rows="3" maxlength="2000" required class="w-full rounded-lg border border-gray-700 bg-gray-900 p-3 text-sm text-white" placeholder="Yanıtını yaz..."></textarea>
                                        <div class="mt-2 flex items-center justify-between"><div>@foreach (['😀', '❤️', '👏', '🔥'] as $emoji)<button type="button" data-insert-emoji="{{ $emoji }}" data-emoji-target="reply-{{ $comment->id }}" class="px-1 hover:scale-110">{{ $emoji }}</button>@endforeach</div><button class="rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">Yanıtla</button></div>
                                    </form>
                                </details>
                                @if ($comment->user_id === auth()->id() || auth()->user()->is_admin)
                                    <details>
                                        <summary class="cursor-pointer text-sm font-semibold text-gray-400 hover:text-white">Düzenle</summary>
                                        <form method="POST" action="{{ route('comments.update', $comment) }}" class="mt-3 rounded-xl bg-gray-950 p-3">@csrf @method('PATCH')<textarea name="comment" rows="3" maxlength="2000" required class="w-full rounded-lg border border-gray-700 bg-gray-900 p-3 text-sm text-white">{{ $comment->comment }}</textarea><button class="mt-2 rounded-lg bg-gray-700 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-600">Kaydet</button></form>
                                    </details>
                                @endif
                            </div>
                        @endauth

                        @if ($comment->replies->isNotEmpty())
                            <div class="mt-5 space-y-4 border-l-2 border-gray-800 pl-4">
                                @foreach ($comment->replies as $reply)
                                    <div class="flex gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-700 text-xs font-bold text-white">{{ strtoupper(substr($reply->user->name, 0, 1)) }}</div>
                                        <div class="min-w-0 flex-1 rounded-xl bg-gray-950 p-3">
                                            <div class="flex items-center justify-between gap-3"><p class="font-semibold text-white">{{ $reply->user->name }} <span class="ml-2 text-xs font-normal text-gray-500">{{ $reply->created_at->diffForHumans() }}</span></p>@auth @if ($reply->user_id === auth()->id() || $video->user_id === auth()->id() || auth()->user()->is_admin)<form method="POST" action="{{ route('comments.destroy', $reply) }}">@csrf @method('DELETE')<button class="text-xs text-red-300 hover:text-red-200">Sil</button></form>@endif @endauth</div>
                                            <p class="mt-2 whitespace-pre-line text-sm text-gray-300">{{ $reply->comment }}</p>
                                            @auth
                                                @if ($reply->user_id === auth()->id() || auth()->user()->is_admin)
                                                    <details class="mt-2"><summary class="cursor-pointer text-xs font-semibold text-gray-500 hover:text-white">Düzenle</summary><form method="POST" action="{{ route('comments.update', $reply) }}" class="mt-2">@csrf @method('PATCH')<textarea name="comment" rows="2" maxlength="2000" required class="w-full rounded-lg border border-gray-700 bg-gray-900 p-2 text-sm text-white">{{ $reply->comment }}</textarea><button class="mt-2 rounded bg-gray-700 px-2 py-1 text-xs text-white">Kaydet</button></form></details>
                                                @endif
                                            @endauth
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>
        @empty
            <x-ui.card><p class="text-gray-400">Henüz yorum yapılmamış. İlk yorumu sen yap!</p></x-ui.card>
        @endforelse
    </div>
</div>

@auth
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-insert-emoji]').forEach((button) => button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.emojiTarget);
        if (!input) return;
        const start = input.selectionStart ?? input.value.length;
        const end = input.selectionEnd ?? input.value.length;
        input.value = `${input.value.slice(0, start)}${button.dataset.insertEmoji}${input.value.slice(end)}`;
        input.focus();
        input.setSelectionRange(start + button.dataset.insertEmoji.length, start + button.dataset.insertEmoji.length);
    }));
});
</script>
@endauth

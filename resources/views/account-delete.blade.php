@extends('layouts.turtube')

@section('title', 'Hesap Silme | TurTube')

@section('meta_description', 'TurTube hesabınızı nasıl silebileceğinizi öğrenin.')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="rounded-3xl border border-gray-800 bg-gray-900/70 p-6 shadow-xl sm:p-8 lg:p-10">

        <div class="mb-8 border-b border-gray-800 pb-8">
            <p class="mb-2 text-sm font-medium text-red-400">TurTube</p>

            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                Hesap Silme
            </h1>

            <p class="mt-4 text-gray-400">
                TurTube hesabınızı ve hesabınızla ilişkili verileri silmek için aşağıdaki adımları
                takip edebilirsiniz.
            </p>
        </div>

        <div class="space-y-8 text-sm leading-7 text-gray-300">

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">
                    TurTube hesabınızı silme
                </h2>

                <ol class="list-decimal space-y-3 pl-6">
                    <li>TurTube hesabınızla giriş yapın.</li>
                    <li>Profil sayfanızı açın.</li>
                    <li>Hesap ayarları bölümündeki hesap silme alanına gidin.</li>
                    <li>Hesabınızı silme işlemini başlatın.</li>
                    <li>Güvenlik amacıyla mevcut hesabınızın şifresini girerek işlemi onaylayın.</li>
                </ol>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">
                    Hesap silindiğinde
                </h2>

                <p>
                    Hesabınız silindiğinde hesabınıza bağlı profil ve hesap bilgileri ile
                    TurTube hesabınız üzerinden yönetilen kullanıcı verileri silme işlemine
                    tabi tutulur.
                </p>

                <p class="mt-3">
                    Kullanıcı tarafından yüklenen video, Shorts, thumbnail ve diğer içeriklerin
                    hesabın silinmesiyle birlikte nasıl ele alınacağı, yürürlükteki sistem
                    kuralları ve yasal saklama yükümlülükleri dikkate alınarak uygulanır.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">
                    Veri saklama
                </h2>

                <p>
                    Güvenlik, yasal yükümlülükler, uyuşmazlıkların çözümü, kötüye kullanımı
                    önleme veya teknik yedekleme gibi nedenlerle bazı veriler belirli bir süre
                    saklanabilir. Gerekli saklama süresi sona erdiğinde ilgili veriler silinir
                    veya anonim hale getirilir.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">
                    Gizlilik Politikası
                </h2>

                <p>
                    Kişisel verilerin işlenmesi hakkında daha fazla bilgi için
                    <a
                        href="{{ url('/privacy') }}"
                        class="font-medium text-red-400 hover:text-red-300">
                        TurTube Gizlilik Politikası
                    </a>
                    sayfasını inceleyebilirsiniz.
                </p>
            </section>

            <section class="rounded-2xl border border-gray-700 bg-gray-950/60 p-5">
                <h2 class="mb-3 text-lg font-semibold text-white">
                    Hesap silme işlemini başlat
                </h2>

                @auth
                    <p class="mb-4">
                        Giriş yapmış durumdasınız. Hesap silme işlemini profil sayfanızdan
                        başlatabilirsiniz.
                    </p>

                    <a
                        href="{{ route('profile.edit') }}"
                        class="inline-flex items-center rounded-xl bg-red-600 px-5 py-3 font-medium text-white transition hover:bg-red-500">
                        Profil ve hesap ayarlarına git
                    </a>
                @else
                    <p class="mb-4">
                        Hesabınızı silmek için önce TurTube hesabınızla giriş yapmanız gerekir.
                    </p>

                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center rounded-xl bg-red-600 px-5 py-3 font-medium text-white transition hover:bg-red-500">
                        Giriş yap
                    </a>
                @endauth
            </section>

        </div>
    </div>
</div>
@endsection

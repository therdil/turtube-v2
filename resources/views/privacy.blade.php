@extends('layouts.turtube')

@section('title', 'Gizlilik Politikası | TurTube')

@section('meta_description', 'TurTube Gizlilik Politikası. Kişisel verilerin, hesap bilgilerinin ve kullanıcı içeriklerinin nasıl işlendiği hakkında bilgi.')

@section('content')
<div class="mx-auto max-w-4xl">
    <div class="rounded-3xl border border-gray-800 bg-gray-900/70 p-6 shadow-xl sm:p-8 lg:p-10">

        <div class="mb-8 border-b border-gray-800 pb-8">
            <p class="mb-2 text-sm font-medium text-red-400">TurTube</p>
            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                Gizlilik Politikası
            </h1>
            <p class="mt-4 text-sm text-gray-400">
                Son güncelleme: 20 Ağustos 2026
            </p>
        </div>

        <div class="space-y-8 text-sm leading-7 text-gray-300">

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">1. Genel Bilgilendirme</h2>
                <p>
                    Bu Gizlilik Politikası, TurTube web sitesi ve TurTube Android uygulaması
                    üzerinden sunulan video paylaşım ve izleme hizmetlerinde kişisel bilgilerin
                    nasıl işlendiğini açıklamak amacıyla hazırlanmıştır.
                </p>
                <p class="mt-3">
                    TurTube; kullanıcıların hesap oluşturmasına, video ve Shorts yüklemesine,
                    video izlemesine, kanalları takip etmesine, yorum ve beğeni gibi etkileşimlerde
                    bulunmasına ve platformun diğer özelliklerinden yararlanmasına olanak sağlar.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">2. Toplanan Bilgiler</h2>
                <p>TurTube'un kullandığınız özelliklere bağlı olarak aşağıdaki bilgiler işlenebilir:</p>

                <ul class="mt-3 list-disc space-y-2 pl-6">
                    <li>Hesap oluştururken sağlanan ad, kullanıcı adı ve e-posta gibi hesap bilgileri.</li>
                    <li>Profil, kanal ve kullanıcı tarafından sağlanan diğer bilgiler.</li>
                    <li>Yüklenen videolar, Shorts, thumbnail görselleri, açıklamalar ve etiketler gibi kullanıcı içerikleri.</li>
                    <li>Yorumlar, beğeniler, favoriler, oynatma listeleri, izleme geçmişi ve benzeri kullanıcı etkileşimleri.</li>
                    <li>Premium üyelik ve abonelik durumuna ilişkin bilgiler.</li>
                    <li>Uygulamanın ve web sitesinin çalışması için gerekli teknik bilgiler ve hata kayıtları.</li>
                    <li>Bildirim tercihleri ve hesap güvenliğiyle ilgili bilgiler.</li>
                </ul>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">3. Bilgilerin Kullanım Amaçları</h2>
                <p>Toplanan bilgiler aşağıdaki amaçlarla kullanılabilir:</p>

                <ul class="mt-3 list-disc space-y-2 pl-6">
                    <li>TurTube hesabınızı oluşturmak ve hesabınızı yönetmek.</li>
                    <li>Video, Shorts, kanal ve diğer platform özelliklerini sunmak.</li>
                    <li>Videoların yüklenmesini, işlenmesini, saklanmasını ve oynatılmasını sağlamak.</li>
                    <li>Kullanıcı etkileşimlerini ve kişiselleştirilmiş platform özelliklerini sunmak.</li>
                    <li>Güvenlik, kötüye kullanım önleme ve içerik moderasyonu işlemlerini yürütmek.</li>
                    <li>Hizmetin performansını ve güvenilirliğini geliştirmek.</li>
                    <li>Gerekli bildirimleri ve hizmetle ilgili iletişimleri göndermek.</li>
                    <li>Premium abonelik ve ilgili özellikleri yönetmek.</li>
                    <li>Yasal yükümlülüklere uymak ve yetkili taleplere cevap vermek.</li>
                </ul>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">4. Kullanıcı İçerikleri</h2>
                <p>
                    TurTube'a yüklediğiniz video, Shorts, thumbnail, açıklama, yorum ve benzeri
                    içerikler hizmetin sunulabilmesi amacıyla işlenebilir ve seçtiğiniz görünürlük
                    ayarlarına bağlı olarak diğer kullanıcılarla paylaşılabilir.
                </p>
                <p class="mt-3">
                    Kullanıcılar yükledikleri içeriklerin gerekli haklarına sahip olmaktan ve
                    platform kurallarına uygun içerik sağlamaktan sorumludur.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">5. Video ve Medya Depolama</h2>
                <p>
                    TurTube'a yüklenen medya dosyaları, hizmetin sunulması amacıyla bulut depolama
                    altyapısında saklanabilir. Medya yükleme işlemlerinde Cloudflare R2 altyapısı
                    kullanılabilir.
                </p>
                <p class="mt-3">
                    Video dosyalarının işlenmesi, dönüştürülmesi ve oynatılabilir hale getirilmesi
                    için TurTube'un sunucu tarafındaki medya işleme sistemleri kullanılabilir.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">6. Çerezler ve Benzer Teknolojiler</h2>
                <p>
                    TurTube, oturum yönetimi, güvenlik, tercihlerin hatırlanması ve hizmetin
                    çalışması için gerekli çerezleri ve benzer teknolojileri kullanabilir.
                </p>
                <p class="mt-3">
                    Web sitesinin bazı özellikleri çerezlerin veya tarayıcıdaki yerel depolama
                    mekanizmalarının kullanılmasına bağlı olabilir.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">7. Reklamlar</h2>
                <p>
                    TurTube ücretsiz hizmetlerin sürdürülebilmesi amacıyla reklam gösterebilir.
                    Reklam hizmetleri kullanıma sunulduğunda, ilgili reklam sağlayıcılarının
                    kendi gizlilik politikaları ve veri işleme kuralları da geçerli olabilir.
                </p>
                <p class="mt-3">
                    TurTube Premium kapsamında reklamların kaldırılması gibi ücretli özellikler
                    sunulabilir. Premium aboneliğin aktif olduğu dönemde hangi özelliklerin
                    sunulacağı ilgili abonelik koşullarında belirtilir.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">8. Üçüncü Taraf Hizmetleri</h2>
                <p>
                    TurTube; barındırma, medya depolama, ödeme, bildirim, reklam, analiz,
                    güvenlik veya benzeri teknik hizmetler için üçüncü taraf hizmet sağlayıcıları
                    kullanabilir.
                </p>
                <p class="mt-3">
                    Bu hizmet sağlayıcıları yalnızca ilgili hizmetin sağlanması için gerekli
                    kapsamda veri işleyebilir ve kendi koşullarına ve gizlilik politikalarına
                    tabi olabilir.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">9. Veri Güvenliği</h2>
                <p>
                    TurTube, kişisel bilgilerin ve kullanıcı hesaplarının yetkisiz erişime,
                    değiştirilmeye veya kötüye kullanıma karşı korunması için makul teknik ve
                    idari güvenlik önlemleri uygulamayı amaçlar.
                </p>
                <p class="mt-3">
                    Bununla birlikte internet üzerinden gerçekleştirilen hiçbir veri aktarımının
                    veya elektronik depolama yönteminin mutlak biçimde güvenli olduğu garanti
                    edilemez.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">10. Verilerin Saklanması</h2>
                <p>
                    Bilgiler, hesabın ve ilgili hizmetlerin sağlanması için gerekli olduğu sürece
                    veya yürürlükteki mevzuatın gerektirdiği süre boyunca saklanabilir.
                </p>
                <p class="mt-3">
                    Kullanıcı hesabının silinmesi veya belirli içeriklerin kaldırılması halinde,
                    bazı bilgilerin yasal yükümlülükler, güvenlik, dolandırıcılığı önleme,
                    uyuşmazlıkların çözümü veya teknik yedekleme süreçleri nedeniyle belirli
                    bir süre daha tutulması gerekebilir.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">11. Kullanıcı Hakları</h2>
                <p>
                    Yürürlükteki mevzuat kapsamında kullanıcıların kişisel verilerine ilişkin
                    çeşitli hakları bulunabilir. Bu haklar arasında bilgi talep etme, verilerin
                    düzeltilmesini isteme, belirli koşullarda silinmesini talep etme ve veri
                    işleme faaliyetlerine ilişkin bilgi alma gibi haklar bulunabilir.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">12. Hesap ve İçerik Silme</h2>
                <p>
                    Kullanıcılar, TurTube hesabı veya kullanıcı içerikleriyle ilgili silme
                    işlemleri için platformda sunulan ilgili hesap ve içerik yönetimi
                    özelliklerini kullanabilir.
                </p>
                <p class="mt-3">
                    Hesap veya içerik silme talepleri, yürürlükteki yasal yükümlülükler ve
                    güvenlik gereklilikleri dikkate alınarak işleme alınır.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">13. Çocukların Gizliliği</h2>
                <p>
                    TurTube, çocuklara yönelik özel bir hizmet olarak tasarlanmamıştır.
                    Çocukların kişisel bilgilerinin korunmasına ilişkin yürürlükteki kurallara
                    uygun hareket edilmesi amaçlanır.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">14. Politika Değişiklikleri</h2>
                <p>
                    Bu Gizlilik Politikası, hizmetlerdeki değişiklikler, yeni özellikler,
                    yasal gereklilikler veya güvenlik ihtiyaçları nedeniyle güncellenebilir.
                    Güncel politika her zaman bu sayfada yayınlanır.
                </p>
            </section>

            <section>
                <h2 class="mb-3 text-xl font-semibold text-white">15. İletişim</h2>
                <p>
                    Gizlilik ve kişisel verilerle ilgili sorularınız veya talepleriniz için
                    TurTube üzerinden sunulan güncel iletişim kanallarını kullanabilirsiniz.
                </p>
            </section>

        </div>
    </div>
</div>
@endsection

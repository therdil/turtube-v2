# TurTube Mobile API v1

Bu doküman, Android ve ileride iOS istemcilerinin mevcut Laravel uygulamasına
güvenli biçimde bağlanması için eklenen JSON API'yi açıklar. Web rotaları ve
Blade akışları bundan bağımsızdır; değiştirilmemiştir.

## Temel adres ve sürümleme

Canlı ortam temel adresi:

```text
https://turtube.com/api/v1
```

Yerelde bu adres `http://127.0.0.1:8000/api/v1` olur. API sürümü URL içinde
taşınır; sonraki kırıcı değişiklikler `/api/v2` altında yapılmalıdır.

Tüm yanıtlar JSON'dur. Sayfalı koleksiyonlar Laravel'in standart biçimindedir:

```json
{
  "data": [],
  "links": {"first": "...", "last": "...", "prev": null, "next": null},
  "meta": {"current_page": 1, "per_page": 20, "total": 0}
}
```

`limit` parametresi varsayılan olarak `20`, en fazla `50` olabilir. `page`
Laravel sayfalama parametresidir.

## Ortak video ve kanal alanları

Video listeleri ve detayları aşağıdaki temel alanları döndürür:

```json
{
  "id": 42,
  "title": "Video başlığı",
  "description": "...",
  "thumbnail_url": "https://turtube.com/storage/thumbnails/example.jpg",
  "preview_url": null,
  "video_url": "https://turtube.com/storage/videos/example.mp4",
  "playback_sources": [{"label": "1080p", "url": "https://..."}],
  "duration": 125,
  "views": 1200,
  "likes_count": 10,
  "comments_count": 3,
  "published_at": "2026-08-11T12:00:00.000000Z",
  "is_short": false,
  "is_premium": false,
  "processing_status": "completed",
  "category": {"id": 1, "name": "Eğitim", "slug": "egitim"},
  "channel": {"id": 1, "username": "kanal-handle", "display_name": "Kanal"}
}
```

`video_url` ve `playback_sources[].url` mobil istemciler için doğrudan,
tam URL olarak gönderilir. Uygulamanın mevcut medya işleme süreci HLS manifest
üretmediği için bu sürümde HLS endpoint'i yoktur; istemci MP4 URL'lerini
oynatmalıdır. Canlı ortamda `APP_URL` HTTPS adresi (`https://turtube.com`)
olmalıdır.

Kanalın URL parametresi mevcut uygulamadaki `users.name` değeridir (`username`
alanı), e-posta değildir.

## Public endpoint'ler

| Endpoint | Açıklama | Parametreler |
| --- | --- | --- |
| `GET /videos` | Yayındaki normal, premium olmayan videolar | `page`, `limit`, `category` (id veya slug), `search`, `sort=newest|views` |
| `GET /videos/{id}` | Bir normal video detayı | — |
| `GET /shorts` | Yayındaki premium olmayan Shorts içerikleri | `page`, `limit`, `category`, `search`, `sort=newest|views` |
| `GET /categories` | Kategori listesi | — |
| `GET /search?q=...` | Video ve Shorts araması | Zorunlu `q`; isteğe bağlı `page`, `limit`, `category`, `sort=relevance|newest|views` |
| `GET /channels/{username}` | Kanal bilgisi ve içeriği | `content=videos|shorts`, `search`, `page`, `limit` |

İlk v1 sürümünde public medya endpoint'leri yalnızca herkese açık,
premium olmayan içeriği verir. Premium medya erişimi, üyelik kontrolü ve
imzalı/korumalı medya akışı sonraki authenticated içerik sürümünde eklenmelidir.

## Kimlik doğrulama (Laravel Sanctum token)

Mobil istemci cookie/CSRF akışı kullanmaz. Giriş veya kayıt sonrasında dönen
token, sonraki isteklerde şu başlıkla gönderilir:

```http
Authorization: Bearer <token>
Accept: application/json
```

| Endpoint | Gövde | Açıklama |
| --- | --- | --- |
| `POST /auth/login` | `email`, `password`, isteğe bağlı `device_name` | Token ve hesap özeti döner. |
| `POST /auth/register` | `name`, `email`, `password`, `password_confirmation`, isteğe bağlı `device_name` | Hesap oluşturur, token döner. |
| `POST /auth/forgot-password` | `email` | Kimlik doğrulama gerektirmez; genel bir yanıt döner ve varsa reset bağlantısını e-posta ile gönderir. |
| `GET /auth/me` | Bearer token | Oturum sahibinin hesap özeti döner. |
| `POST /auth/logout` | Bearer token | Sadece mevcut cihaz tokenını iptal eder; `204 No Content`. |

`/auth/me` ve giriş/kayıt yanıtlarındaki kullanıcı nesnesi hesap sahibine ait
e-posta ve premium erişim bilgisini içerir. Public kanal yanıtları e-posta,
parola, yönetici, ban veya token bilgilerini hiçbir zaman içermez.

### Şifre sıfırlama

`POST /api/v1/auth/forgot-password` yalnızca reset e-postası gönderme isteğini başlatır:

```json
{
  "email": "user@example.com"
}
```

Başarılı yanıt her durumda aynıdır; kullanıcı hesabının varlığı ifşa edilmez:

```json
{
  "message": "Bu e-posta adresi kayıtlıysa, şifre sıfırlama bağlantısı gönderilmiştir."
}
```

Endpoint kimlik doğrulama gerektirmez ve e-posta/IP bazında dakikada beş istekle sınırlıdır. Reset tokenı ve bağlantısı yalnızca Laravel tarafından e-postayla üretilir; sıfırlama işlemi mevcut web ekranında tamamlanır.

## Hatalar ve sınırlar

| Kod | Biçim / anlam |
| --- | --- |
| `401` | Kimlik doğrulama gerekli veya token geçersiz. |
| `403` | Banlı hesap ya da erişilemeyen premium içerik. |
| `404` | Yayında olmayan, yanlış türde veya bulunamayan kaynak. |
| `422` | Doğrulama hatası: `message` ve alan bazlı `errors` döner. |
| `429` | Çok fazla istek. `Retry-After` başlığına uyulmalıdır. |
| `503` | Platform bakım modunda. JSON `message` döner. |

Genel API sınırı dakika başına 120 istektir. Arama mevcut `search` limitiyle,
giriş/kayıt ise mevcut `login`/`registration` limitleriyle ayrıca korunur.
İstemci `429` aldığında kısa gecikme ve kontrollü yeniden deneme uygulamalıdır.

Native Android/iOS istemcilerinde CORS gerekmez. İleride browser tabanlı bir
istemci eklenirse izin verilecek origin'ler tek tek `config/cors.php` üzerinden
tanımlanmalı; wildcard (`*`) kullanılmamalıdır.

## İstemci entegrasyon notları

- Tokenı Android Keystore/iOS Keychain gibi güvenli depoda saklayın; loglara
  veya URL'lere yazmayın.
- Her istekte `Accept: application/json` gönderin.
- `thumbnail_url`, `preview_url` ve video URL'leri `null` olabilir. İstemci
  boş durum için güvenli placeholder göstermelidir.
- `processing_status` tamamlanmadan video dosyası kullanılamayabilir.
- Video izleme ilerlemesi, beğeni, yorum, abonelik, yükleme ve Studio/Admin
  işlemleri bu ilk read/auth API kapsamına dahil edilmemiştir; bunlar yetki
  politikalarıyla ayrı sürümlerde eklenmelidir.

## Yayına alma notu

Sanctum paketi `personal_access_tokens` migration'ını ekler. Canlıya çıkarken,
normal deploy penceresinde ve yedek sonrası yalnızca sunucuda çalıştırılmalıdır:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

Bu dokümanın oluşturulması sırasında canlı sunucuda migration, yapılandırma
veya başka bir işlem çalıştırılmamıştır.

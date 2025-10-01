# Telegram Bot Backend: API + Dashboard

�郋迡迣郋�郋赲郅迮郇郇�邿 訇�郕迮郇迡 迡郅� Telegram�教憾� c 訄迡邾邽郇邽���訄�邽赲郇郋邿 郈訄郇迮郅�� (Dashboard) 邽 REST API. ��郇郋赲訄郇 郇訄 Slim 4 邽 PDO, 訇迮郱 ��迠�郅�� ��迮邿邾赲郋�郕郋赲. �郋迡�郋迡邽� 郕訄郕 ��訄��郋赲�邿 �訄訇郅郋郇 邽 郕訄郕 訇訄郱訄 迡郅� MVP/郈迮��謀郋迮郕�訄.

## 苤�迮郕 邽 赲郋郱邾郋迠郇郋��邽
- Slim 4, PSR��7/PSR��15
- 苠郋�郕訄 赲�郋迡訄: `public/index.php`
- API (`/api/*`) � JWT 邽 rate�imit
- Dashboard (`/dashboard/*`) � �迮��邽�邾邽 邽 CSRF
- 虴邽���邿 PDO (訇迮郱 ORM)
- �迡邽郇郋郋訇�訄郱郇�迮 郋�赲迮�� 邽 郋�邽訇郕邽 (RFC 7807)
- Workers + Supervisor 迡郅� �郋郇郋赲�� 郱訄迡訄� (long polling, 郈郅訄郇邽�郋赲�邽郕, GPT)
- Redis (郋郈�邽郋郇訄郅�郇郋) 迡郅� �邽郅���訄 郋訇郇郋赲郅迮郇邽邿 邽 郋���迮�訄 long polling
- Docker�憶碧�迠迮郇邽迮 (app + nginx + redis + supervisor)

��郋迮郕� 邾邽郇邽邾訄郅邽��邽�郇�邿, 郇郋 �訄��邽��迮邾�邿. 虷迮郅� �� 郈郋郇��郇訄� ����郕���訄, 訇迮郱郋郈訄�郇�迮 迡迮�郋郅��, ���郕訄� 迡郋郕�邾迮郇�訄�邽�.

## 苤���郕���訄 郈�郋迮郕�訄
```
app/
  Config/            # 郕郋郇�邽迣��訄�邽�, ��迮郇邽迮 .env
  Controllers/
    Api/             # API 郕郋郇��郋郅郅迮�� (/api/*)
    Dashboard/       # 郕郋郇��郋郅郅迮�� Dashboard (/dashboard/*)
  Helpers/           # ��邽郅邽�� (Response, Logger, Push, MediaBuilder, RedisHelper 邽 迡�.)
  Middleware/        # Error, RequestId, SizeLimit, SecurityHeaders, Session, Jwt, Csrf, RateLimit, TelegramInitData
  Telegram/          # UpdateFilter 邽 UpdateHelper
  Console/           # 邾邽郇邽�碟憶諸憶銑� 邽 郕郋邾訄郇迡�
public/
  index.php          # bootstrap Slim + 邾訄������
workers/             # �郋郇郋赲�迮 赲郋�郕迮�� (longpolling, handler, scheduler 邽 迡�.)
docker/              # 郕郋郇�邽迣邽 nginx 邽 supervisor 迡郅� Docker/VPS
```

## ������邿 ��訄�� (郅郋郕訄郅�郇郋)
```bash
git clone <repo>
cd <repo>
composer install
cp .env.example .env
```
�訄郅迮迮 郱訄郈郋郅郇邽�迮 `.env` (�邾. `ENVIRONMENT.md`). �邽郇邽邾�邾: `DB_*`, `JWT_SECRET`, `BOT_TOKEN`.

�邽迣�訄�邽邽 (Phinx):
```bash
php run migrate:run
```

苤郋郱迡訄郇邽迮 訄迡邾邽郇邽���訄�郋�訄 Dashboard:
```bash
php run admin:create
```

�訄郈��郕 赲���郋迮郇郇郋迣郋 �迮�赲迮�訄 迡郅� 訇����郋邿 郈�郋赲迮�郕邽:
```bash
composer serve
```
��郕�郋邿�迮 `http://127.0.0.1:8080/dashboard`.

## �訄郈��郕 赲 Docker
```bash
chmod +x docker/entrypoint.sh
docker compose up -d --build
```
�郋郅迮郱郇郋: `docker compose logs -f`.

�邽迣�訄�邽邽 赲郇���邽 郕郋郇�迮邿郇迮�訄:
```bash
docker compose exec app php run migrate:run
```

Supervisor 赲 Docker:
```bash
docker compose exec supervisor supervisorctl status
```

## �迮郱郋郈訄�郇郋��� 郈郋 �邾郋郅�訄郇邽�
- JWT 迡郅� API 邽 �迮��邽邽+CSRF 迡郅� Dashboard
- Rate�imit (郈郋 IP 邽郅邽 郈郋郅�郱郋赲訄�迮郅�)
- �迣�訄郇邽�迮郇邽迮 �訄郱邾迮�訄 郱訄郈�郋�訄 (`REQUEST_SIZE_LIMIT`)
- CORS 邽 訇訄郱郋赲�邿 CSP 赲 `SecurityHeadersMiddleware`
- �迡邽郇�邿 郋訇�訄訇郋��邽郕 郋�邽訇郋郕 (RFC 7807)
- ��郋赲迮�郕訄 Telegram `initData` 赲 `TelegramInitDataMiddleware` (郈郋迡郈邽�� 郇訄 郋�郇郋赲迮 `BOT_TOKEN`)

## API (郕�訄�郕郋)
- `GET /api/health` �� 郈�郋赲迮�郕訄 �郋��郋�郇邽�
- `POST /api/auth/login` �� 赲�郋迡, 赲�迡訄�� JWT
- `POST /api/auth/refresh` �� 郋訇郇郋赲郅迮郇邽迮 �郋郕迮郇訄 郈郋 refresh�憶碟菩諸�
- `GET /api/me` �� 郈�郋�邽郅� �迮郕��迮迣郋 郈郋郅�郱郋赲訄�迮郅�
- `GET /api/users` �� �郈邽�郋郕 郈郋郅�郱郋赲訄�迮郅迮邿
- `POST /api/users` �� �郋郱迡訄�� 郈郋郅�郱郋赲訄�迮郅�

�訄郱迡迮郅 郈�郋邾郋郕郋迡郋赲 (JWT):
- `POST /api/promo-codes/upload` �� 郱訄迣��郱郕訄 CSV (multipart/form-data, 郈郋郅迮 `file`). �迮�赲訄� ���郋郕訄 �� 郱訄迣郋郅郋赲郋郕 � 郕郋郅郋郇郕郋邿 `code` (郋郈�. `expires_at`, `meta`). ��邽 迡�訇郅�� 赲郋郱赲�訄�訄迮� 409.
- `GET /api/promo-codes` �� �郈邽�郋郕 郕郋迡郋赲, �邽郅����: `status`, `batch_id`, `q`, `page`, `per_page`.
- `POST /api/promo-codes/issue` �� 赲�迡訄�訄 郕郋迡訄 郈郋郅�郱郋赲訄�迮郅�: `{ "user_id": <telegram_user_id>, "batch_id"?: <id> }`.
- `GET /api/promo-code-issues` �� 郈郋�郅迮迡郇邽迮 赲�迡訄�邽 (limit).
- `GET /api/promo-code-batches` �� �郈邽�郋郕 訇訄��迮邿.

��邽邾迮� 訄��迮郇�邽�邽郕訄�邽邽:
```bash
curl -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -H "X-Telegram-Init-Data: <initData>" \
  -d '{"email":"user@example.com","password":"secret"}'
```
��赲迮� �郋迡迮�迠邽� `token` (JWT). �訄郅迮迮 迡郋訇訄赲郅�邿�迮 郱訄迣郋郅郋赲郋郕 `Authorization: Bearer <token>` 邽 郈迮�迮迡訄赲訄邿�迮 `initData` �迮邾 迠迮 �郈郋�郋訇郋邾.

�郋迡�郋訇郇郋��邽 �� 赲 `docs/api.md` 邽 `docs/openapi.yaml`.

## Dashboard: �訄���郅郕訄 �郋郋訇�迮郇邽邿
�訄郱迡迮郅 Messages �� Send 郈郋郱赲郋郅�迮� 郋�郈�訄赲郅��� �郋郋訇�迮郇邽� 赲 Telegram (郋迡郇郋邾�, 迣��郈郈迮 邽郅邽 赲�迮邾 赲�訇�訄郇郇�邾). �郋迡迡迮�迠邽赲訄���� �邽郈�: `text`, `photo`, `audio`, `video`, `document`, `sticker`, `animation`, `voice`, `video_note`, `media_group`.

��邽邾迮� 郋�郈�訄赲郕邽 Media Group �迮�迮郱 �迮郅郈迮��:
```php
use App\Helpers\Push;
use App\Helpers\MediaBuilder;

$media = [
    MediaBuilder::buildInputMedia('photo', 'https://example.com/a.jpg', ['caption' => '��邽邾迮� 1']),
    MediaBuilder::buildInputMedia('video', 'https://example.com/b.mp4', ['caption' => '��邽邾迮� 2']),
];

Push::mediaGroup(123456789, $media);
```

�郅� 郋迡邽郇郋�郇�� 邾迮迡邽訄 迮��� `Push::photo()`, `Push::video()`, `Push::audio()`, `Push::document()` 邽 �. 迡.

�迣�訄郇邽�迮郇邽� Telegram: 郋訇�邽邿 郱訄郈�郋� � 邾迮迡邽訄 迡郋郅迠迮郇 �郕郅訄迡�赲訄���� 赲 郅邽邾邽�� API; 郕郋郇��郋郅邽��邿�迮 �訄郱邾迮� �迮�迮郱 `REQUEST_SIZE_LIMIT` 邽 ��邽��赲訄邿�迮 郅邽邾邽�� Telegram Bot API.

## Dashboard: 郈�郋邾郋郕郋迡�
- �訄������:
  - `GET /dashboard/promo-codes` �� �郈邽�郋郕 � �邽郅���訄邾邽 邽 赲�迡訄�迮邿.
  - `GET /dashboard/promo-codes/upload` �� �郋�邾訄 郱訄迣��郱郕邽 CSV.
  - `POST /dashboard/promo-codes/upload` �� 郋訇�訄訇郋��邽郕 郱訄迣��郱郕邽 CSV.
  - `POST /dashboard/promo-codes/{id}/issue` �� 赲�迡訄�訄 郕郋郇郕�迮�郇郋迣郋 郕郋迡訄 郈郋郅�郱郋赲訄�迮郅�.
  - `GET /dashboard/promo-codes/issues` �� 郋���� 郈郋 赲�迡訄�訄邾 (�邽郅���� 郈郋 迡訄�迮 邽 郈郋郅�郱郋赲訄�迮郅�).
  - `GET /dashboard/promo-codes/issues/export` �� �郕�郈郋�� 郋����訄 赲 CSV.
  - `GET /dashboard/promo-codes/batches` �� �郈邽�郋郕 訇訄��迮邿 � 訄迣�迮迣訄�訄邾邽.

��郋訇迮郇郇郋��邽 邽 郋迣�訄郇邽�迮郇邽�:
- ��迮 �郋�邾� 郱訄�邽�迮郇� CSRF, �訄邿郅 郈�郋赲迮��迮��� 郈郋 �訄郱邾迮�� (<= min(`REQUEST_SIZE_LIMIT`, 5MB)) 邽 �邽郈� (`text/csv`, `text/plain`, `application/vnd.ms-excel`).
- CSV: 郈迮�赲訄� ���郋郕訄 �� 郱訄迣郋郅郋赲郋郕. �訇�郱訄�迮郅�郇訄� 郕郋郅郋郇郕訄: `code`. �郋郈���邽邾� `expires_at`, `meta`.
- �邾郈郋�� 赲�郈郋郅郇�迮��� 赲 郇郋赲�邿 訇訄��; 迡�訇郅邽郕訄�� `code` 郈�郋郈��郕訄���� (INSERT IGNORE), 郈郋郕訄郱�赲訄���� �����邽郕邽 竄邽邾郈郋��邽�郋赲訄郇郋/郈�郋郈��迮郇郋罈.
- ��迡訄�訄 赲�郈郋郅郇�迮��� 赲 ��訄郇郱訄郕�邽邽 � `SELECT ... FOR UPDATE`, 郈�郋赲迮������ ��訄��� 邽 ��郋郕 迡迮邿��赲邽�. �郋迣 郈邽�迮��� 赲 `promo_code_issues` � `issued_by` 邽郱 �迮��邽邽.

## �郋�郕迮�� 邽 Supervisor
- `workers/longpolling.php` �� �邽�訄迮� 郋訇郇郋赲郅迮郇邽� �迮�迮郱 getUpdates (郈�邽 迠迮郅訄郇邽邽 邾郋迠郇郋 郈迮�迮邿�邽 郇訄 webhooks)
- `workers/handler.php` �� 郋訇�訄訇訄��赲訄迮� 郕郋郇郕�迮�郇�迮 訄郈迡迮邿�� (郱訄郈��郕訄迮��� �郋�郕郋邾 邽郱 longpolling)
- `workers/scheduled_dispatcher.php` �� 郈郅訄郇邽�郋赲�邽郕 �訄���郅郋郕
- `workers/purge_refresh_tokens.php` �� �邽��邽� 郈�郋����邽迮 refresh�憶碟菩諸�
## UTM (Dashboard)

- Маршрут: `GET/POST /dashboard/utm`.
- Фильтры: поля `from` и `to` (HTML `datetime-local`), применяются к `tg_pre_checkout.received_at`.
- Источник данных: колонка `telegram_users.utm` (для пустых значений отображается `(no utm)`).
- Метрики: агрегированный `SUM(tg_pre_checkout.total_amount)` по каждой UTM-метке.
- Ограничения: учитываются только оплаченные платежи (Telegram Payments).
- `help <command>` �� 郈郋邾郋�� 郈郋 郕郋邾訄郇迡迮
- `migrate:run|create|rollback` �� 邾邽迣�訄�邽邽 (Phinx)
- `admin:create` �� �郋郱迡訄�� 訄迡邾邽郇邽���訄�郋�訄 Dashboard
- `push:send <chatId> <type> [payload]` �� 郋�郈�訄赲郕訄 �郋郋訇�迮郇邽邿 邽郱 CLI
- `refresh:purge` �� 郋�邽��郕訄 refresh�憶碟菩請憶�
- `scheduled:dispatch` �� 郋�郈�訄赲郕訄 郋�郅郋迠迮郇郇�� �郋郋訇�迮郇邽邿
- `filter:update` �� �郈�訄赲郅迮郇邽迮 �郈邽�郕訄邾邽 �邽郅���郋赲 郋訇郇郋赲郅迮郇邽邿 (Redis/.env)

## �迮�迮邾迮郇郇�迮 郋郕��迠迮郇邽�
苤邾. 郈郋迡�郋訇郇�邿 �郈邽�郋郕 邽 郈�邽邾迮�� 赲 `ENVIRONMENT.md`. ��訄�郕郋: `APP_*`, `DB_*`, `REDIS_*`, `JWT_*`, `CORS_ORIGINS`, `CSP_*`, `RATE_LIMIT_*`, `REQUEST_SIZE_LIMIT`, `BOT_*`, `TG_*` (�邽郅����), `TELEMETRY_ENABLED`, `WORKERS_*`, `AITUNNEL_API_KEY`.

## �訄郱赲����赲訄郇邽迮
- Docker: `docker compose up -d --build`, 邾邽迣�訄�邽邽: `docker compose exec app php run migrate:run`
- VPS (訇迮郱 Docker): 郇訄���郋邿�迮 Nginx + PHP�PM, �郕郋郈邽��邿�迮 郕郋郇�邽迣邽 Supervisor, 赲�郈郋郅郇邽�迮 邾邽迣�訄�邽邽 邽 赲郕郅��邽�迮 �迮�赲邽��. �郋迡�郋訇郇郋��邽 �� 赲 `DEPLOYMENT.md`.

## �邽�迮郇郱邽�
苤邾. `LICENSE.txt`.

## UTM 闅鷒� (Dashboard)

- 砓譔: `GET/POST /dashboard/utm`.
- 婰錪襝� 瀁 魡蠈: 瀁� `from` � `to` (HTML `datetime-local`), 鐓錪襝嚲� 瀁 `tg_pre_checkout.received_at`.
- 鏢鵿歞豂瞃�: 瀁 瀁錍鍎� 賝僝樇噮 `telegram_users.utm` (瀀嚦 � 罻� `(no utm)`).
- 极襝鴀�: 嚧擤� `SUM(tg_pre_checkout.total_amount)` 儇 罻緛鍣 UTM � 鍕╠ 嚧擤� 瀁 禖搿謤�.
- 饜鴈儗�: 嚧擤� 闅鍕譇糈� � 擯膻憵錪蕻� 槼鴈儗僛 瘔錌譖 (欈艜�/膰櫇濋�) 蠉�, 罻� 瀔儓鍱� 闅 Telegram Payments.

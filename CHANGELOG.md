# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 4.3.0 — 2026-06-18

Webhook delivery signing was dropped backend-side: deliveries are no
longer HMAC-signed, the `secret` field is gone from the webhook create
response, and `POST /v1/webhooks/{id}/rotate-secret` was deleted. The
webhook resource keeps CRUD only.

### Removed

- `Blueticks\Webhooks\verify()` helper (and its `files` autoload entry).
- `Blueticks\Errors\WebhookVerificationError`.
- `Blueticks\Types\WebhookCreateResult` and `Blueticks\Types\WebhookEvent`.
- `webhooks->rotateSecret()` — the `rotate-secret` endpoint no longer exists.

### Changed

- `webhooks->create()` now returns `Blueticks\Types\Webhook` (no `secret`)
  instead of the former `WebhookCreateResult`.

## 4.2.0 — 2026-05-22

OpenAPI parity pass. The SDK now matches `backend/openapi.json`
operation-for-operation; an engineless drift check
(`.github/workflows/sdk-spec-drift.yml`) gates future regressions. The
`/v1/*` surface is pre-release — none of these changes affect production
callers yet.

### Changed

- `messages->send()` now takes the discriminated body shape matching the
  backend's strict `anyOf` (BE#50):
  ```php
  $client->messages->send(['type' => 'text', 'to' => '+1...', 'text' => 'hi']);
  $client->messages->send(['type' => 'media', 'to' => '+1...', 'media' => ['url' => '...', 'kind' => 'image']]);
  $client->messages->send(['type' => 'poll', 'to' => '+1...', 'poll' => ['question' => '...', 'options' => [...]]]);
  ```
- Single-item GETs now use `->retrieve($id)` instead of `->get($id)`:
  `audiences`, `campaigns`, `chats`, `groups`, `webhooks`, `messages`,
  `scheduledMessages`. Also `engines->status()` → `engines->retrieve()`.
- `newsletters->create()` returns the typed `Newsletter` DTO (8 fields).

### Added

- `newsletters->list($params)` — `GET /v1/newsletters` (cursor-paginated → `Page<Newsletter>`)
- `newsletters->retrieve(string $id): Newsletter` — `GET /v1/newsletters/{id}`
- `ping->retrieve(): Ping` — typed DTO (`account_id`, `key_prefix`, `scopes`)
- `Message` now exposes `key`, `type`, `media_kind`, `poll_question`, `link_preview`

### Removed

- `engines->me()`, `engines->logout()`, `engines->reload()`
- `contacts->getProfilePicture()`

### Fixed

- `groups->list()` was documented at `dev.blueticks.co` but absent from the
  SDK for ~9 days — now present.

## 4.0.1 — 2026-04-30

### BREAKING (completes 4.0.0)
- `Blueticks\Types\Page` properties migrated camelCase → snake_case to
  match the rest of the 4.0.0 DTO migration: `$page->hasMore` →
  `$page->has_more`, `$page->nextCursor` → `$page->next_cursor`. 4.0.0
  shipped with these still camelCase by oversight; this completes the
  contract so every DTO property in the SDK uniformly mirrors the wire
  format.

## 4.0.0 — 2026-04-30

### BREAKING
- `Blueticks\Resources\ChatsResource` methods now return typed DTOs instead of
  raw `array<string, mixed>`:
  - `list()` → `Page<Chat>`
  - `get()` → `Chat`
  - `listParticipants()` → `Page<Participant>`
  - `listMessages()` → `Page<ChatMessage>`
  - `getMessage()` → `ChatMessage`
  - `getMedia()` → `ChatMedia`
  Methods without DTOs (`markRead`, `open`, `react`, `loadOlderMessages`,
  `getMessageAck`, `getMediaUrl`, `batchMessageAcks`) continue to return raw
  associative arrays.
- DTO property names migrated camelCase → snake_case across `Audience`,
  `Webhook`, `WebhookCreateResult`, `WebhookEvent`, `Contact`, `Campaign`,
  `Message`, and `AppendContactsResult` to match the wire format and the
  precedent set by `Account` / the chat DTOs. Example:
  `$audience->createdAt` → `$audience->created_at`;
  `$message->mediaUrl` → `$message->media_url`;
  `$campaign->audienceId` → `$campaign->audience_id`.

### Added
- None beyond what is covered in BREAKING.

## 3.3.0 — 2026-04-30

### Added
- Chat DTO scaffolding: `Blueticks\Types\Chat`, `Participant`,
  `ChatMessage`, `ChatMedia` are now exported from `src/Types/`. Each has
  a `fromArray(array $raw): self` factory that validates the response
  shape and throws `Blueticks\Errors\ValidationError` on missing/wrong
  fields. Mirror the Python SDK's `blueticks.types.chats` types.

### Note
- `$client->chats->*` resource methods still return raw associative
  arrays in 3.x. Callers who want typed objects can wrap manually:
  `Chat::fromArray($client->chats->get($id))`. A future 4.0.0 will
  switch the resource methods themselves to typed returns (breaking
  change).

## 3.2.0 — 2026-04-30

### Note
- `$client->chats->getMedia()` response now carries two extra fields
  from the underlying API:
  - `original_quality`: `bool|null` — false when WA returned a preview
    JPEG instead of the original sender uploaded (#113 — only affects
    own-sent newsletter media). null/absent on the genuine original.
  - `media_unavailable`: `?string` — reason bytes couldn't be retrieved
    (`expired`, `fetching`, `error`, `no_media`).
- The chat resources still return raw associative arrays (no DTOs yet),
  so the new fields flow through transparently. Typed DTOs are planned
  for a later release.

## 3.1.0 — 2026-04-29

### Added
- `$client->chats->listMessages()` opts now accept `message_types` —
  `list<string>` of allowed message kinds (e.g. `['document']` for PDFs,
  `['image']` for photos). System events (`gp2`, `revoked`,
  `newsletter_notification`) are excluded by default unless explicitly listed.
  Forwarded to the server as comma-separated form per OpenAPI
  `style: form, explode: false`.

### Note
- Chat resources still return raw associative arrays (no DTOs yet for
  `Chat`/`ChatMessage`/`ChatMedia`); the new `caption` and `filename`
  fields flow through unchanged. Typed DTOs are planned for a later release.

## 1.1.0 — 2026-04-23

### Added
- `$client->messages->send()` and `->get()`
- `$client->webhooks->{create,list,get,update,delete,rotateSecret}()`
- `$client->audiences->{create,list,get,update,delete,appendContacts,updateContact,deleteContact}()`
- `$client->campaigns->{create,list,get,pause,resume,cancel}()`
- `Blueticks\Webhooks\verify()` helper
- `Blueticks\Errors\WebhookVerificationError`

## [1.0.0] — 2026-04-23

### Added

- Initial release.
- `Blueticks\Blueticks` client class with `apiKey` + `baseUrl` constructor options
  (env fallbacks: `BLUETICKS_API_KEY`, `BLUETICKS_BASE_URL`).
- `->ping()` helper and `->account->retrieve()` resource.
- PSR-18 HTTP transport via `php-http/discovery`; retries with exponential
  backoff on 429/502/503/504 and network errors.
- Typed error hierarchy (`AuthenticationError`, `PermissionDeniedError`,
  `NotFoundError`, `BadRequestError`, `RateLimitError`, `APIError`,
  `APIConnectionError`, `ValidationError`) under `Blueticks\Errors\`.
- Readonly DTOs (`Blueticks\Types\Ping`, `Blueticks\Types\Account`) with
  `fromArray()` factories that validate response shape.

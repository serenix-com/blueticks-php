# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

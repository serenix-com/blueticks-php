# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

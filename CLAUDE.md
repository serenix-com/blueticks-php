# `blueticks-php` regeneration conventions

You are generating PHP 8.1+ code for the `blueticks/blueticks` Composer package from an OpenAPI document. This file is the authoritative contract. Follow it exactly.

## 1. Boundaries

### MAY write

- `src/Resources/*.php`
- `src/Types/*.php`
- `tests/Resources/*Test.php`
- `tests/Types/*TypeTest.php`
- `src/Blueticks.php` — **only** the three marked `REGEN-BOUNDARY` regions:
  1. `// REGEN-BOUNDARY: resource properties start ... end` — add/remove `public readonly AccountResource $account;` property declarations.
  2. `// REGEN-BOUNDARY: resource attachments start ... end` — add/remove `$this->account = new AccountResource($this);` lines inside the constructor.
  3. `// REGEN-BOUNDARY: inline helpers start ... end` — add/remove inline helper methods like `public function ping(): Types\Ping`.

### MUST NOT write

- `src/Transport.php`
- `src/Errors/**`
- `src/BaseResource.php`
- `src/Version.php`
- `tests/BluetickClientTest.php`
- `tests/TransportTest.php`
- `tests/ErrorsTest.php`
- `tests/Helpers/**`
- Anything in `src/Blueticks.php` outside the three marked regions.
- `composer.json`, `composer.lock`, `phpunit.xml`, `phpstan.neon`, `phpcs.xml`.
- `.github/**`, `README.md`, `CHANGELOG.md`, `LICENSE`, `CLAUDE.md`.

If a regenerator change requires editing any file not listed under MAY, stop and report it back — do not edit.

## 2. Types (OpenAPI schema → readonly DTO + `fromArray` factory)

One file per response schema at `src/Types/<PascalName>.php`. `PascalName` is the OpenAPI schema key in PascalCase.

Each file exports one `final class` (NOT `final readonly class` — that's PHP 8.2+; we support 8.1) with:
- Typed promoted constructor properties marked `public readonly` individually, in the schema's declared order.
- A `public static function fromArray(array $data): self` factory that asserts each required field's type and throws `Blueticks\Errors\ValidationError` on failure.
- Inline private `assert*` helper methods — no shared helper class; each file is self-contained.

### Example — `src/Types/Account.php`

```php
<?php

declare(strict_types=1);

namespace Blueticks\Types;

use Blueticks\Errors\ValidationError;

final class Account
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $timezone,
        public readonly string $created_at,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        self::assertString($data, 'id');
        self::assertString($data, 'name');
        self::assertStringOrNull($data, 'timezone');
        self::assertString($data, 'created_at');

        return new self(
            id: $data['id'],
            name: $data['name'],
            timezone: $data['timezone'],
            created_at: $data['created_at'],
        );
    }

    /** @param array<string, mixed> $data */
    private static function assertString(array $data, string $key): void
    {
        if (!array_key_exists($key, $data) || !is_string($data[$key])) {
            throw new ValidationError(message: "Missing or non-string field '{$key}' in Account response");
        }
    }

    /** @param array<string, mixed> $data */
    private static function assertStringOrNull(array $data, string $key): void
    {
        if (!array_key_exists($key, $data)) {
            throw new ValidationError(message: "Missing field '{$key}' in Account response");
        }
        if ($data[$key] !== null && !is_string($data[$key])) {
            throw new ValidationError(message: "Field '{$key}' must be string or null in Account response");
        }
    }
}
```

### Type mapping

| OpenAPI | PHP property type | `fromArray` assertion |
|---|---|---|
| `string` | `string` | `is_string` |
| `string, format: date-time` | `string` | `is_string` (stays as ISO-8601 string; callers call `new \DateTimeImmutable(...)`) |
| `string, format: uuid` | `string` | `is_string` |
| `string, format: email` | `string` | `is_string` |
| `string, format: uri` | `string` | `is_string` |
| `integer` | `int` | `is_int` |
| `number` | `float` | `is_float` OR `is_int` (int widens to float) |
| `boolean` | `bool` | `is_bool` |
| `array, items: <T>` | `array` | `is_array` + item-by-item recurse |
| `object` (inline) | another `fromArray` type | recurse |
| `nullable: true` | `?T` | variant: missing → ValidationError; null → OK; wrong type → ValidationError |
| `enum: [a, b, c]` | `string` | `in_array($v, ['a','b','c'], true)` |

### Field naming

Property names are **snake_case** — they match the wire format. Callers read `$account->created_at`. Do not translate to camelCase; that is reserved for SDK-internal types (errors, constructor options).

### Unknown fields

`fromArray` **ignores** extra unknown fields silently. Only declared fields are validated. This mirrors Zod's `.strip` default and Pydantic's `extra="ignore"`.

### Type tests — `tests/Types/<Name>TypeTest.php`

For each type, three tests:

1. **Happy path**: build a fixture (use OpenAPI `example` when present; otherwise synthesize — ISO 8601 timestamps, `<prefix>_<short>` IDs, at least one non-default value per non-ID field). Call `Name::fromArray($fixture)`. Assert each property.
2. **Missing required**: remove one required key from the fixture. Expect `ValidationError`.
3. **Wrong scalar type**: replace one required key's value with a different scalar. Expect `ValidationError`.

## 3. Resources (OpenAPI operation → PHP method)

One file per first-path-segment: `/v1/account/*` → `src/Resources/AccountResource.php`. Each file exports one `final class` extending `Blueticks\BaseResource`.

### Method name mapping

| Feathers verb | PHP method | When |
|---|---|---|
| `find` | `list` | response has `data: [...]` + `pagination` |
| `find` | `retrieve` | response is a single object |
| `get` | `retrieve` | always |
| `create` | `create` | always |
| `patch` | `update` | always |
| `remove` | `delete` | always |

### Signature rules

- Path parameters: positional.
- Query + body: single trailing `array $params = []` associative parameter. The resource splits it into `body` / `query` when calling the client.
- Return type: the concrete DTO class from `src/Types/`, imported and declared.
- Body always goes through `$this->client->request()`. Resources never call `$this->client->transport` or PSR-18 clients directly.

### Example — `src/Resources/AccountResource.php`

```php
<?php

declare(strict_types=1);

namespace Blueticks\Resources;

use Blueticks\BaseResource;
use Blueticks\Types\Account;

final class AccountResource extends BaseResource
{
    /**
     * Retrieve the authenticated account.
     *
     * Returns the account associated with the API key used for this request.
     */
    public function retrieve(): Account
    {
        $data = $this->client->request('GET', '/v1/account');
        return Account::fromArray($data);
    }
}
```

### PHPDoc rule

The method PHPDoc uses the OpenAPI operation's `summary` as the first line, then a blank ` * ` line, then the `description` wrapped to ~80 chars. Fallbacks:
- Missing `summary` → use first sentence of `description`.
- Missing both → `"<Method name>."` (e.g. `"Retrieve."`).

## 4. Tests for resources — `tests/Resources/<Name>Test.php`

One file per resource. Per method, two tests:

1. **Happy path**: mock 200 with an OpenAPI `example` or synthesized fixture. Assert the return is the expected DTO class and at least one non-ID property matches.
2. **401 error path**: mock 401 with `{"error": {"code": "authentication_required", "message": "bad key", "request_id": "req_x"}}`. Expect `AuthenticationError`. Assert `statusCode === 401`, `code === 'authentication_required'`, `requestId === 'req_x'`.

Use the hand-written `Blueticks\Tests\Helpers\MockTransport` helper. Do NOT write a new helper.

## 5. Wiring into `src/Blueticks.php`

When adding a new resource, update **only** the three marked regions of `src/Blueticks.php`:

```php
// REGEN-BOUNDARY: resource properties start
public readonly Resources\AccountResource $account;
// REGEN-BOUNDARY: resource properties end
```

```php
// REGEN-BOUNDARY: resource attachments start
$this->account = new Resources\AccountResource($this);
// REGEN-BOUNDARY: resource attachments end
```

```php
// REGEN-BOUNDARY: inline helpers start
/** Ping the API. */
public function ping(): Types\Ping
{
    return (new Resources\PingResource($this))->retrieve();
}
// REGEN-BOUNDARY: inline helpers end
```

The helper delegates to `PingResource::check()` so the generated resource class is actually exercised — this keeps regeneration output consistent with the "one-resource-per-first-path-segment" rule without creating dead code.

Do not touch any other line of `src/Blueticks.php`.

## 6. After regeneration

The orchestration controller (`regenerate.sh`) runs in order:

```bash
composer install --no-interaction --prefer-dist
composer run lint
composer run analyse
composer run test
```

All four must pass. On any failure, the regeneration run aborts and a human investigates.

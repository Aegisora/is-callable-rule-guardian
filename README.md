# Aegisora Is Callable Rule Guardian

[![Latest Version](https://img.shields.io/packagist/v/aegisora/is-callable-rule-guardian?style=flat-square)](https://packagist.org/packages/aegisora/is-callable-rule-guardian)
[![Total Downloads](https://img.shields.io/packagist/dt/aegisora/is-callable-rule-guardian?style=flat-square)](https://packagist.org/packages/aegisora/is-callable-rule-guardian)
![Code Coverage Badge](./badge.svg)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
![PHPStan Badge](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg?style=flat)

Is Callable Rule Guardian provides a simple shortcut for callable validation using `aegisora/guardian` and `aegisora/is-callable-rule`.

It is designed for cases where you want to quickly check whether a value **is callable**, without manually building an `IsCallableRule` and a validation pipeline by hand.

This package is built on top of:

* [aegisora/guardian](https://github.com/Aegisora/guardian)
* [aegisora/is-callable-rule](https://github.com/Aegisora/is-callable-rule)

---

## ✨ Features

* 🔹 Simple shortcut API for `IsCallableRule`
* 🔹 Validates that a value is callable via `check()`
* 🔹 Works with closures, invokable objects, function names and array callables
* 🔹 Uses `aegisora/guardian` internally
* 🔹 Uses `aegisora/is-callable-rule` internally
* 🔹 Supports a custom validation exception
* 🔹 Keeps rule execution errors separated from validation errors
* 🔹 Fully compatible with the Aegisora ecosystem
* 🔹 Ready to use out of the box

---

## 📦 Installation

```bash
composer require aegisora/is-callable-rule-guardian
```

---

## 🚀 Core Concept

This package wraps the common callable validation flow:

```php
$guardian->check(
    $value,
    IsCallableRule::create(),
    new ValueIsNotCallableException()
);
```

into a dedicated shortcut class:

```php
$isCallableRuleGuardian->check($value, new ValueIsNotCallableException());
```

Instead of manually creating an `IsCallableRule` and passing it to `Guardian`, you can use `IsCallableRuleGuardian` directly.

---

## 🏗️ Basic Usage

```php
use Aegisora\Guardian\Guardian;
use Aegisora\Guardian\Exceptions\GuardianValidationException;
use Aegisora\RuleGuardians\IsCallableRule\IsCallableRuleGuardian;

$guardian = new Guardian();

$isCallableRuleGuardian = new IsCallableRuleGuardian($guardian);

try {
    $isCallableRuleGuardian->check($value);
    // $value is callable
} catch (GuardianValidationException $exception) {
    // $value is not callable
}
```

`check()` **passes when** `$value` is callable, and **fails otherwise**.

---

## ✅ How the callable check works

A value is considered callable when it can be invoked as a function:

```php
$isCallableRuleGuardian->check(static fn (): string => 'ok'); // passes (closure)
$isCallableRuleGuardian->check('trim');                       // passes (function name)
$isCallableRuleGuardian->check([SomeClass::class, 'method']); // passes (static array callable)
$isCallableRuleGuardian->check([new SomeClass(), 'method']);  // passes (instance array callable)

$isCallableRuleGuardian->check(1);                            // fails (int)
$isCallableRuleGuardian->check('fooo');                       // fails (non-callable string)
$isCallableRuleGuardian->check([]);                           // fails (array)
$isCallableRuleGuardian->check(new stdClass());               // fails (non-invokable object)
$isCallableRuleGuardian->check(['UnknownClass', 'method']);   // fails (invalid array callable)
```

An object is also callable when it implements the `__invoke()` magic method.

---

## 🧩 Usage with Custom Exception

You may provide your own exception for validation failure. It must be the **last** argument.

```php
use Aegisora\Guardian\Guardian;
use Aegisora\RuleGuardians\IsCallableRule\IsCallableRuleGuardian;
use App\Exceptions\ValueIsNotCallableException;

$guardian = new Guardian();

$isCallableRuleGuardian = new IsCallableRuleGuardian($guardian);

$isCallableRuleGuardian->check(
    $value,
    new ValueIsNotCallableException()
);
```

If the value is not callable, the provided exception will be thrown instead of `GuardianValidationException`.

This is useful when validation errors should have domain-specific meaning.

---

## 🧪 Example in Application Service

```php
use Aegisora\RuleGuardians\IsCallableRule\IsCallableRuleGuardian;
use App\Exceptions\InvalidHandlerException;

final class HandlerRegistry
{
    private IsCallableRuleGuardian $isCallableRuleGuardian;

    public function __construct(
        IsCallableRuleGuardian $isCallableRuleGuardian
    ) {
        $this->isCallableRuleGuardian = $isCallableRuleGuardian;
    }

    /**
     * @param mixed $handler
     */
    public function register(string $name, $handler): void
    {
        $this->isCallableRuleGuardian->check(
            $handler,
            new InvalidHandlerException()
        );

        // business logic for registering a callable handler
    }
}
```

---

## 🚨 Exceptions

The package raises validation-related exceptions, all delegated to `Guardian` (the outcome of running the rule):

### `GuardianValidationException`

Thrown when validation fails and no custom exception is provided.

The rule code for a failed callable check is `is_callable_rule`.

```php
use Aegisora\Guardian\Exceptions\GuardianValidationException;

try {
    $isCallableRuleGuardian->check($value);
} catch (GuardianValidationException $exception) {
    echo $exception->getRuleCode(); // "is_callable_rule"
}
```

### Custom exception

When a custom exception is passed as the last argument, it is thrown instead of `GuardianValidationException` on validation failure.

```php
use App\Exceptions\ValueIsNotCallableException;

try {
    $isCallableRuleGuardian->check($value, new ValueIsNotCallableException());
} catch (ValueIsNotCallableException $exception) {
    // domain-specific handling
}
```

### `GuardianExecutingRuleException`

Thrown when the underlying rule fails to execute (raises a `RuleException` during validation), as opposed to simply reporting an invalid result.

The callable check accepts any value type and reports non-callable values as an invalid result, so this exception is not triggered by the input itself — it is surfaced only if `Guardian` fails to execute the rule.

```php
use Aegisora\Guardian\Exceptions\GuardianExecutingRuleException;

try {
    $isCallableRuleGuardian->check($value);
} catch (GuardianExecutingRuleException $exception) {
    // the rule could not be executed
}
```

---

## 🧩 API

### `IsCallableRuleGuardian::check()`

```php
/**
 * @param mixed $value
 * @throws GuardianExecutingRuleException
 * @throws GuardianValidationException
 * @throws \Throwable
 */
public function check($value, ?\Throwable $exception = null): void
```

Validates that `$value` is **callable**.

Arguments:

* `$value` — the value to validate
* `$exception` — an optional custom `\Throwable` to be thrown on validation failure

The method returns `void`. It communicates results through exceptions only — it returns nothing on success and throws on failure:

* `GuardianValidationException` — the callable check failed and no custom exception was provided
* the provided custom exception — the check failed and a custom exception was passed
* `GuardianExecutingRuleException` — the rule could not be executed

---

## 🏛️ Architecture

This package is a small shortcut layer over the Aegisora validation pipeline.

Flow:

1. `IsCallableRuleGuardian::check()` is called with a value and an optional exception
2. An `IsCallableRule` is created (`create()`)
3. `Guardian` executes the rule against the value
4. If the check passes, execution continues normally
5. If the check fails, the custom exception or `GuardianValidationException` is thrown
6. If the rule could not be executed, `GuardianExecutingRuleException` is thrown

Internal flow:

```
value → IsCallableRuleGuardian → Guardian → IsCallableRule → Result → Exception
```

---

## 🔗 Related Packages

* [aegisora/guardian](https://github.com/Aegisora/guardian) — validation execution orchestrator
* [aegisora/is-callable-rule](https://github.com/Aegisora/is-callable-rule) — is callable rule
* [aegisora/rule-contract](https://github.com/Aegisora/rule-contract) — base rule contract and validation result architecture

---

## ⚖️ License

This package is open-source and licensed under the MIT License. See the LICENSE for details.

---

## 🌱 Contributing

Contributions are welcome and greatly appreciated!. See the CONTRIBUTING for details.

---

## 🌟 Support

If you find this project useful, please consider giving it a star on GitHub!

It helps the project grow and motivates further development.

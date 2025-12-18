# 🤝 Contributing to maatify/data-adapters

Thank you for considering contributing to **maatify/data-adapters**.

This library is part of the **Maatify.dev Ecosystem** and follows a **strict architectural philosophy**:
it provides **DI-first infrastructure adapters only**, with **no abstraction, no magic, and no runtime behavior**.

This document explains how to report issues, propose changes, and submit pull requests
in a way that respects the project’s scope and guarantees long-term stability.

---

## 🧭 Project Scope (Important)

Before contributing, please understand what this project **is** and **is not**.

### ✔️ In Scope

* Infrastructure adapters (MySQL, Redis, MongoDB)
* Dependency Injection boundaries
* Explicit driver ownership
* Factories for explicit construction
* Documentation, examples, and tests
* Static analysis improvements

### ❌ Out of Scope

* Repositories or query abstractions
* Unified APIs across drivers
* Configuration loading or `.env` handling
* Auto-detection or runtime switching
* Failover, retry, pooling, or telemetry
* Framework integrations (Laravel, Symfony, etc.)

Any contribution that violates this scope **will not be accepted**.

---

## 🐛 Reporting Issues

If you encounter a bug or unexpected behavior:

1. Search existing issues first
2. If not found, open a new issue including:

   * A clear and descriptive title
   * Steps to reproduce
   * Expected vs actual behavior
   * PHP version and operating system
   * Driver type involved (PDO, DBAL, Redis, MongoDB)

👉 Open an issue:
[https://github.com/Maatify/data-adapters/issues](https://github.com/Maatify/data-adapters/issues)

---

## 🌟 Feature Requests

Feature requests are welcome **only if they align with the project scope**.

Good examples:

* Supporting a new infrastructure driver
* Improving adapter type safety
* Improving factory ergonomics without adding magic
* Documentation or example improvements

When submitting a request:

1. Explain the problem it solves
2. Describe the proposed API explicitly
3. Avoid suggestions involving auto-configuration or abstraction

---

## 🔧 Development Setup

Clone the repository:

```bash
git clone https://github.com/Maatify/data-adapters.git
cd data-adapters
composer install
```

> ⚠️ This library does **not** use environment variables or `.env` files.

Run tests:

```bash
vendor/bin/phpunit
```

Run static analysis:

```bash
composer run analyse
```

Run formatting:

```bash
composer run format
```

---

## 🧪 Testing Guidelines

* All new behavior **must** be covered by tests
* Existing tests **must pass**
* Tests must be deterministic
* No real databases or services in tests
* Prefer mocks and test doubles

Test structure:

```
tests/
 ├─ Adapters/
 ├─ Factories/
 ├─ Contracts/
 └─ TestDoubles/
```

---

## 🧱 Coding Standards

This project enforces:

* **PHP >= 8.4**
* **PSR-12**
* `declare(strict_types=1)` everywhere
* Explicit typing (no mixed APIs)
* No global state
* All classes must be `final` unless explicitly justified
* Dependency Injection only (no service locators)

---

## 🚀 Submitting Pull Requests

### ✔️ PR Checklist

* [ ] Change aligns with documented scope
* [ ] Code is fully typed and strict
* [ ] No API changes without discussion
* [ ] Tests added or updated
* [ ] All tests and static analysis pass
* [ ] Documentation updated if applicable
* [ ] Commits are clean and meaningful

### ✔️ PR Flow

1. Fork the repository

2. Create a branch:

   ```bash
   git checkout -b feature/my-change
   ```

3. Commit your changes

4. Push and open a Pull Request

5. Request review from project maintainers

---

## 🧩 Commit Message Format

Recommended:

```
feat: add mongo database adapter
fix: correct redis adapter type hint
docs: improve usage examples
test: add adapter factory coverage
```

Avoid vague messages like `update` or `fix stuff`.

---

## 📦 Branch Naming Convention

```
feature/<description>
bugfix/<issue-id>
docs/<description>
test/<description>
```

---

## 🔐 Security Issues

If you discover a security vulnerability, **do not open a public issue**.

Report it privately to:

📧 **[security@maatify.dev](mailto:security@maatify.dev)**

Please see `SECURITY.md` for the full policy.

---

## ❤️ Thank You

Your contribution helps keep the Maatify ecosystem clean, predictable,
and professional.

---

© 2025 Maatify.dev
Engineered by **Mohamed Abdulalim ([@megyptm](https://github.com/megyptm))**
[https://www.maatify.dev](https://www.maatify.dev)

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Unified Ecosystem for Modern PHP Libraries</sub>
</p>
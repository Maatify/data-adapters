[![Maatify Data Adapters](https://img.shields.io/badge/Maatify-Data%20Adapters-blue?style=for-the-badge)](README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

# Security Policy

This document describes how security issues are handled
for the `maatify/data-adapters` package.

---

## 📌 Scope of This Policy

This security policy applies **only** to:

- The `maatify/data-adapters` source code
- Adapter implementations
- Factories
- Documentation included in this repository

It does **not** cover:
- Underlying database drivers (PDO, Redis, MongoDB, etc.)
- PHP runtime or extensions
- Framework integrations
- Application-level security concerns

---

## 🧠 Security Model

`maatify/data-adapters` is a **passive infrastructure package**.

Important characteristics:

- No network access
- No environment variable reading
- No credential handling
- No connection management
- No data processing logic
- No serialization logic

As a result:
- The attack surface is intentionally minimal
- Most security risks originate **outside** this package

---

## 🚫 What This Package Does NOT Do

For clarity, this package does **not**:

- Store secrets
- Load credentials
- Handle authentication or authorization
- Execute queries
- Validate or sanitize data
- Manage encryption
- Open or close connections

Security responsibilities remain with:
- The application
- The bootstrap layer
- The infrastructure environment

---

## 🧪 Dependencies & Supply Chain

This package depends on:
- PHP itself
- Optional third-party drivers (PDO, Redis, Predis, MongoDB, DBAL)

Security of those dependencies is:
- The responsibility of their respective maintainers
- The responsibility of the application to keep them updated

No optional dependency is loaded unless explicitly used.

---

## 🧯 Reporting a Vulnerability

If you discover a security issue **directly related** to this package:

### Please DO NOT:
- Open a public GitHub issue
- Discuss the vulnerability publicly

### Please DO:
- Report it privately via GitHub Security Advisories
- Or contact the maintainer directly if advisories are unavailable

When reporting, include:
- A clear description of the issue
- A minimal reproduction if possible
- The affected version(s)
- Why the issue impacts this package specifically

---

## ⏱️ Response Timeline

Security reports are handled with priority.

Typical response process:
1. Acknowledge receipt
2. Assess scope and impact
3. Prepare a fix if applicable
4. Release a patched version
5. Publish an advisory if required

Not all reports result in a security release.
Issues outside this package’s scope may be closed without action.

---

## 🧩 Supported Versions

Only actively maintained versions receive security updates.

At the time of writing:
- **v2.x** → Supported
- **v1.x** → Unsupported / Legacy

Users are strongly encouraged to upgrade.

---

## ⚠️ Usage Disclaimer

Because this package:
- Wraps low-level drivers
- Exposes them directly via `getDriver()`

Security depends heavily on:
- How drivers are configured
- How adapters are used
- How applications handle errors and data

Misuse of adapters may introduce vulnerabilities
that are **not** the responsibility of this package.

---

## 🔒 Final Note

This package prioritizes:
- Explicitness
- Determinism
- Transparency

Security through obscurity is explicitly rejected.

---

## 📎 Related Documents

- [`README.md`](README.md)
- [`docs/01-scope.md`](docs/01-scope.md)
- [`docs/04-misuse-traps.md`](docs/04-misuse-traps.md)

---

© Maatify.dev — Infrastructure-first, explicit-by-design PHP libraries

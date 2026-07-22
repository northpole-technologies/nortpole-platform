# Northpole Architecture Handbook

Version: 1.0

Status: Living Document

---

# Purpose

This handbook defines the architectural principles that govern the Northpole Platform.

Its purpose is to ensure every feature, module and architectural decision follows the same long-term vision.

If a future decision conflicts with this document, the decision should be questioned before implementation.

---

# Vision

Northpole is not a Laravel application.

Northpole is an application platform.

Laravel is the engine.

Northpole is the operating system built on top of it.

---

# Core Principles

## 1. The Core Owns the Platform

The Northpole Core is responsible for:

- Module discovery
- Module registration
- Authentication
- Permissions
- Licensing
- Marketplace
- Billing
- Notifications
- Platform configuration
- Shared services

The Core must never contain business logic belonging to an application.

---

## 2. Modules Own Business Logic

Every business application lives inside its own module.

Examples:

- SantaBuddy
- Home Doctor
- FrontGuard
- BalanceMe
- CV Station

A module owns:

- Routes
- Controllers
- Services
- Views
- Assets
- Config
- Database
- Migrations
- Language files

A module should be installable without modifying the Northpole Core.

---

## 3. Self-Contained Modules

Every module should be capable of being:

- Installed
- Updated
- Disabled
- Removed

without affecting other modules.

No module may directly depend on another module unless that dependency is declared.

---

## 4. Loose Coupling

Modules communicate through contracts, events or services.

Never through direct internal class references where avoidable.

The Core should know that a module exists.

The Core should not know how a module works.

---

## 5. Single Responsibility

Each class should have one clear responsibility.

Examples:

ModuleManager discovers modules.

ModuleInstaller installs modules.

LicenceManager validates licences.

UserManager manages users.

Avoid "God Classes".

---

## 6. Convention Over Configuration

Developers should rarely need documentation to know where something belongs.

Every module follows the same structure.

```
Module
│
├── config
├── database
├── resources
├── routes
├── src
└── tests
```

Consistency is more valuable than cleverness.

---

## 7. Backwards Compatibility

Breaking existing modules should be avoided wherever practical.

When breaking changes are unavoidable:

- document them
- version them
- provide migration guidance

---

## 8. Security First

Every feature should assume hostile input.

Validate.

Sanitise.

Authorise.

Audit.

Security is never optional.

---

## 9. Testing

Every new feature should include automated tests where practical.

Platform tests verify platform behaviour.

Module tests verify module behaviour.

No release should knowingly reduce test coverage.

---

## 10. Documentation

Every significant architectural decision should be documented.

Documentation is part of the product.

Code without documentation is considered incomplete.

---

# Development Workflow

Every feature should follow the same cycle.

Plan

↓

Build

↓

Test

↓

Review

↓

Refine

↓

Release

---

# Versioning

Northpole follows Semantic Versioning.

MAJOR.MINOR.PATCH

Examples

1.0.0

1.1.0

1.1.3

Breaking changes increase MAJOR.

New features increase MINOR.

Bug fixes increase PATCH.

---

# Module Requirements

Every module must contain:

- module.json
- Service Provider
- Routes
- Tests

Recommended:

- Config
- Views
- Assets
- Migrations
- Documentation

---

# Marketplace Standards

Marketplace modules should:

- include documentation
- include screenshots
- include tests
- declare dependencies
- declare minimum Northpole version
- include licence information

---

# Coding Standards

- PSR-12
- Laravel best practices
- SOLID principles
- Dependency Injection
- Constructor injection preferred
- No duplicated logic
- British English for documentation

---

# Decision Rule

Whenever a design decision is unclear ask:

"Does this make Northpole easier to extend five years from now?"

If the answer is no,

reconsider the design.

---

# Northpole Philosophy

Build once.

Install anywhere.

Extend safely.

Own the platform.

Never the complexity.
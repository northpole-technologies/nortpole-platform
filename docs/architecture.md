# NorthPole Architecture

## Introduction

NorthPole is a modular application platform built around a deterministic Runtime.

Rather than treating modules as passive packages, NorthPole treats every module as an active participant in a well-defined lifecycle.

The platform is designed around these architectural principles:

- Modularity
- Predictability
- Tenant isolation
- Explicit contracts
- Test-first development
- Long-term maintainability

Every Runtime component exists to support one or more of these principles.

---

## Architectural Goals

NorthPole is designed to address common problems found in large business applications:

- Tightly coupled codebases
- Unpredictable startup behaviour
- Duplicated business logic
- Difficult module upgrades
- Hidden dependencies
- Inconsistent extension mechanisms

The Runtime provides a structured approach that allows independent modules to collaborate while remaining loosely coupled.

---

## High-Level Architecture

The platform is composed of five primary layers:

1. Application
2. Runtime
3. Modules
4. Infrastructure
5. Laravel Framework

Each layer has clearly defined responsibilities.

### Application

The application layer exposes the platform through HTTP APIs, console commands and future user interfaces.

It coordinates incoming requests but should not contain module business logic.

### Runtime

The Runtime discovers modules, validates their manifests, resolves dependencies, builds registries and executes ordered Runtime stages.

It provides the shared contracts through which modules communicate.

### Modules

Modules contain independent business capabilities such as:

- CRM
- Inventory
- HomeDoctor
- SantaBuddy

Each module owns its domain logic, routes, migrations, commands, queries, events and tests.

### Infrastructure

Infrastructure provides shared technical services such as persistence, queues, caching, notifications and external integrations.

### Laravel Framework

Laravel provides the underlying application container, routing, middleware, database access, console tooling and testing foundation.

---

## Runtime Responsibilities

The Runtime is the heart of NorthPole.

Its responsibilities include:

- Discovering modules
- Loading and validating manifests
- Resolving dependencies
- Registering Runtime components
- Executing lifecycle stages
- Managing module lifecycle operations
- Exposing Runtime metadata
- Providing diagnostics

The Runtime coordinates business modules but does not own their business logic.

---

## Runtime Registries

NorthPole uses specialised registries rather than repeatedly scanning the application.

Current registries include:

- Stage Registry
- Command Registry
- Query Registry
- Event Subscriber Registry
- Configuration Registry
- Navigation Registry
- Permission Registry
- Notification Registry
- Scheduled Job Registry

Registries provide deterministic lookups, explicit registration and inspectable Runtime metadata.

---

## Runtime Stages

Runtime work is organised into ordered stages.

The default stages are:

1. Configuration
2. Service Providers
3. Routes
4. Views
5. Navigation
6. Event Subscribers
7. Command Handlers
8. Query Handlers
9. Migrations
10. Permissions

Each stage has a focused responsibility and participates in the Runtime boot pipeline.

---

## Command Bus

Commands represent requests to change application state.

Examples include:

- CreateCustomer
- InstallModule
- EnableModule

Commands are dispatched through the Command Bus and resolved through the Command Registry.

Each command has exactly one registered handler.

---

## Query Bus

Queries retrieve information without modifying application state.

Examples include:

- FindCustomer
- ListMarketplaceModules
- RetrieveRuntimeMetadata

Queries are dispatched through the Query Bus and resolved through the Query Registry.

Each query has exactly one registered handler.

---

## Event Bus

Events communicate that something has already happened.

Examples include:

- CustomerCreated
- ModuleInstalled
- ModuleEnabled

Multiple subscribers may react to the same event without creating direct dependencies between modules.

---

## Module Lifecycle

The Runtime manages tenant-aware module lifecycle operations:

- Install
- Enable
- Disable
- Uninstall

Lifecycle operations pass through validation and dedicated lifecycle stages before state changes are applied.

---

## Multi-Tenancy

Tenant isolation is a core architectural requirement.

Requests resolve an active tenant before protected Runtime operations are performed.

Tenant-aware behaviour includes:

- Module installations
- Commands
- Queries
- Events
- Database records
- Permissions
- Runtime APIs

Tenant resolution follows a fail-closed approach.

When a valid tenant cannot be established, protected operations must not continue.

---

## Runtime Metadata

NorthPole exposes Runtime metadata for diagnostics and future developer tooling.

Metadata includes:

- Registered Runtime stages
- Registered command handlers
- Registered query handlers
- Registered event subscribers
- Active Runtime registries
- Discovered modules
- Runtime version information

The metadata layer allows developers to inspect the Runtime without relying on hidden behaviour.

---

## Testing Philosophy

NorthPole follows a strict test-first development methodology.

Automated tests protect:

- Runtime stage ordering
- Registry population
- Command dispatch
- Query dispatch
- Event registration
- Event publication
- Module lifecycle behaviour
- Tenant isolation
- Runtime APIs

Tests verify observable contracts and prevent architectural drift.

---

## Design Principles

Architectural decisions should reinforce the following principles:

- Keep modules independent.
- Prefer explicit contracts.
- Avoid hidden behaviour.
- Preserve tenant boundaries.
- Design extension points deliberately.
- Maintain backwards compatibility where practical.
- Keep business logic outside the Runtime.
- Protect behaviour with automated tests.

---

## Future Growth

The Runtime has been designed to evolve without requiring changes to existing modules.

Future capabilities may include:

- Marketplace distribution
- Module version compatibility
- Runtime health monitoring
- Visual Runtime inspector
- Background workflow orchestration
- Distributed Runtime services
- AI-assisted module generation

The architectural foundation remains stable while allowing the platform to expand.

---

## Summary

NorthPole provides a deterministic Runtime for building modular, tenant-aware business applications.

Its ordered stages, specialised registries, communication buses and lifecycle contracts provide a stable, predictable and highly extensible foundation for enterprise software.

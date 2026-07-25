# Runtime Overview

## Purpose

The NorthPole Runtime is the heart of the platform.

It is responsible for discovering modules, validating manifests, resolving dependencies and coordinating the deterministic boot process.

The Runtime separates platform infrastructure from business functionality, allowing modules to remain independent while providing a consistent startup lifecycle.

---

# Responsibilities

The Runtime is responsible for:

- Discovering installed modules
- Reading module manifests
- Validating module configuration
- Resolving module dependencies
- Executing the Boot Pipeline
- Managing Runtime registries
- Providing diagnostics and inspection

The Runtime does not contain business logic.

Business functionality belongs inside modules.

---

# Runtime Architecture

```text
PlatformServiceProvider
        │
        ▼
Runtime
        │
        ├── Module Discovery
        ├── Manifest Validation
        ├── Dependency Resolution
        ├── Boot Pipeline
        ├── Runtime Registries
        └── Diagnostics
@'
# Module Development

## Purpose

Modules are the primary building blocks of a NorthPole application.

Every piece of business functionality should live inside a module. The Runtime provides the infrastructure; modules provide the business capabilities.

A module should be self-contained, testable and independently maintainable.

---

# Module Structure

A typical module is organised as follows.

```text
modules/

CRM/
├── Commands/
├── Queries/
├── Events/
├── Listeners/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Database/
│   ├── Migrations/
│   ├── Factories/
│   └── Seeders/
├── Models/
├── Providers/
├── Resources/
│   ├── Views/
│   ├── Lang/
│   └── Assets/
├── Routes/
├── Config/
├── Tests/
├── module.json
└── README.md
@'
# Command Bus

## Purpose

The Command Bus is responsible for executing actions within the platform.

A Command represents an instruction to perform work. Unlike a Query, a Command changes the state of the application.

Each Command is handled by exactly one Command Handler.

---

# What is a Command?

A Command is an object that represents an intention.

Examples include:

- CreateCustomer
- UpdateInvoice
- DeleteProduct
- AssignRole
- SendWelcomeEmail

Commands contain the data required to perform the action but do not contain business logic.

Business logic belongs inside the Command Handler.

---

# Architecture

```text
Module
   │
   ▼
CreateCustomer Command
   │
   ▼
Command Bus
   │
   ▼
Command Registry
   │
   ▼
CreateCustomerHandler
   │
   ▼
Business Logic Executes
@'
# Query Bus

## Purpose

The Query Bus is responsible for retrieving information from the platform.

A Query requests data but must never modify the application's state.

Each Query is processed by exactly one Query Handler.

---

# What is a Query?

A Query represents a request for information.

Examples include:

- FindCustomer
- GetInvoice
- ListProducts
- SearchOrders
- GetDashboardStatistics

Queries contain the information required to retrieve data but do not perform the retrieval themselves.

The retrieval logic belongs inside the Query Handler.

---

# Architecture

```text
Module
   │
   ▼
FindCustomer Query
   │
   ▼
Query Bus
   │
   ▼
Query Registry
   │
   ▼
FindCustomerHandler
   │
   ▼
Customer Returned
@'
# Event Bus

## Purpose

The Event Bus enables modules to communicate without direct dependencies.

An Event announces that something has happened. Other modules may choose to respond by registering Event Subscribers.

The module that raises the Event does not know who is listening.

This keeps modules loosely coupled and independently maintainable.

---

# What is an Event?

An Event represents something that has already happened.

Examples include:

- CustomerCreated
- InvoicePaid
- ProductDeleted
- UserRegistered
- ModuleInstalled

Events describe facts.

They are not instructions.

---

# Architecture

```text
CRM Module
     │
     ▼
CustomerCreated Event
     │
     ▼
Event Bus
     │
     ▼
Event Registry
     │
     ├──────────────► Loyalty Module
     │                    Award Points
     │
     ├──────────────► Email Module
     │                    Send Welcome Email
     │
     └──────────────► Audit Module
                          Record Activity
@'
# Capability System

## Purpose

The Capability Registry provides a central catalogue of the features made available by installed modules.

A capability describes what a module can do rather than how it does it.

This allows the Runtime and other modules to discover platform functionality without depending on implementation details.

---

# What is a Capability?

A capability is a declaration that a module provides a specific feature.

Examples include:

- Customer Management
- Email Notifications
- Payment Processing
- Reporting
- File Storage
- Audit Logging

Capabilities are registered during Runtime startup.

---

# Architecture

```text
CRM Module
      │
      ▼
Declares Capability
      │
      ▼
Capability Registry
      │
      ▼
Runtime
      │
      ▼
Platform Services

@'
# Navigation Registry

## Purpose

The Navigation Registry provides a central location for collecting navigation items contributed by modules.

Rather than each module modifying application menus directly, modules declare their navigation in their manifest. The Runtime discovers these declarations and builds the application's navigation structure during startup.

This approach keeps modules independent while providing a consistent user experience.

---

# What is Navigation?

Navigation defines how users move through the application.

Examples include:

- Dashboard
- Customers
- Orders
- Reports
- Settings

Each navigation item is owned by the module that provides the corresponding functionality.

---

# Architecture

```text
CRM Module
      │
      ▼
Navigation Declaration
      │
      ▼
Navigation Registry
      │
      ▼
Runtime
      │
      ▼
Application Menu

@'
# Permission System

## Purpose

The Permission Registry provides a central catalogue of all permissions contributed by installed modules.

Rather than scattering permissions throughout the application, every module declares its own permissions and the Runtime discovers them during startup.

This creates a consistent and discoverable security model.

---

# What is a Permission?

A permission represents an action that a user may be authorised to perform.

Examples include:

- crm.customers.view
- crm.customers.create
- crm.customers.update
- crm.customers.delete
- reports.view
- settings.manage

Permissions are descriptive and independent of any specific authentication system.

---

# Architecture

```text
CRM Module
      │
      ▼
Permission Declaration
      │
      ▼
Permission Registry
      │
      ▼
Runtime
      │
      ▼
Authorisation Layer

@'
# Runtime Lifecycle

## Purpose

The Runtime Lifecycle defines the deterministic sequence used to initialise the NorthPole Platform.

Every application follows exactly the same startup process, ensuring that modules are discovered, validated and registered in a predictable order.

This deterministic approach improves reliability, simplifies debugging and makes the platform easier to understand.

---

# Lifecycle Overview

Every application startup follows this sequence.

```text
Application Starts
        │
        ▼
Platform Service Provider
        │
        ▼
Runtime Initialised
        │
        ▼
Module Discovery
        │
        ▼
Manifest Validation
        │
        ▼
Dependency Resolution
        │
        ▼
Boot Pipeline
        │
        ▼
Runtime Registries Populated
        │
        ▼
Application Ready

@'
# Engineering Handbook

## Purpose

This handbook defines the engineering standards used throughout the NorthPole Platform.

Its purpose is to ensure that every contribution follows the same architectural principles, coding standards and development workflow.

Consistency is considered a platform feature.

---

# Core Principles

Every contribution to NorthPole should reinforce these principles.

## Architecture Before Implementation

Understand the architecture before writing code.

Good architecture reduces future complexity.

---

## The Runtime Owns Infrastructure

Infrastructure belongs inside the Runtime.

Modules contain business functionality.

Keep these responsibilities separate.

---

## Modules Are Independent

Modules should be self-contained.

Communication between modules should occur through:

- Commands
- Queries
- Events

Avoid direct implementation dependencies.

---

## Deterministic by Design

The platform should behave predictably.

Every application should start using the same Runtime lifecycle.

Every Runtime contribution should be registered consistently.

---

## Explicit Over Implicit

Avoid hidden behaviour.

If something happens automatically, it should be obvious where and why it happens.

---

## Documentation Is Code

Documentation is part of the implementation.

Whenever behaviour changes, the documentation should be updated in the same pull request.

Out-of-date documentation is considered a defect.

---

## Tests Are Executable Documentation

Tests explain expected behaviour.

Every Runtime component should have meaningful automated tests.

When behaviour changes, tests should change first.

---

# Development Workflow

Every feature should follow this sequence.

```text
Understand
      │
      ▼
Design
      │
      ▼
Write Tests
      │
      ▼
Implement
      │
      ▼
Refactor
      │
      ▼
Run Test Suite
      │
      ▼
Update Documentation

@'
# NorthPole Roadmap

## Vision

NorthPole is being built as a modular application platform that enables organisations to compose business applications from independent, discoverable modules.

The long-term vision is to provide an ecosystem where modules can be developed, installed, upgraded and managed with minimal friction while maintaining a consistent developer experience.

---

# Guiding Principles

The roadmap is driven by principles rather than fixed dates.

Every enhancement should improve one or more of the following:

- Maintainability
- Extensibility
- Discoverability
- Reliability
- Developer Experience

---

# Current Status

The Runtime foundation is complete.

Core platform capabilities include:

- Runtime
- Module Discovery
- Manifest Validation
- Dependency Resolution
- Boot Pipeline
- Command Bus
- Query Bus
- Event Bus
- Navigation Registry
- Permission Registry
- Capability Registry
- Runtime Diagnostics

These components provide the foundation upon which future platform features will be built.

---

# Near-Term Goals

## Module Marketplace

Provide a central catalogue of available modules.

Possible features include:

- Browse modules
- Search modules
- Install modules
- Upgrade modules
- Version compatibility
- Publisher information

---

## Module Lifecycle

Extend module management with support for:

- Install
- Enable
- Disable
- Upgrade
- Uninstall
- Health checks

---

## Configuration Management

Provide a consistent approach for module configuration.

Potential capabilities include:

- Configuration validation
- Secure settings
- Environment overrides
- Configuration diagnostics

---

## Background Processing

Expand support for asynchronous operations.

Examples include:

- Queue integration
- Scheduled tasks
- Background workers
- Retry policies

---

# Long-Term Goals

## Developer SDK

Provide tooling to simplify module development.

Possible additions include:

- Code generators
- Validation tools
- Testing utilities
- Development templates
- Diagnostics

---

## Observability

Improve Runtime visibility through:

- Performance metrics
- Startup timing
- Health monitoring
- Structured logging
- Runtime telemetry

---

## Marketplace Ecosystem

Support a broader ecosystem of third-party modules.

Potential features include:

- Digital signatures
- Compatibility verification
- Ratings and reviews
- Commercial licensing
- Automatic updates

---

## Multi-Organisation Features

Continue expanding organisational capabilities while preserving module isolation and Runtime consistency.

---

# Design Philosophy

The roadmap is intentionally evolutionary.

NorthPole favours small, well-tested improvements over large disruptive redesigns.

Architectural stability is considered more valuable than rapid feature growth.

---

# Contributing

Contributions should align with the engineering principles documented in the Engineering Handbook.

Every new feature should:

- Solve a real problem.
- Maintain architectural consistency.
- Include automated tests.
- Include updated documentation where appropriate.

---

# Looking Ahead

NorthPole has been designed with longevity in mind.

As new capabilities are introduced, the platform should remain:

- Predictable
- Modular
- Testable
- Discoverable
- Easy to maintain

The objective is not simply to build more software, but to build software that remains understandable and dependable for years to come.

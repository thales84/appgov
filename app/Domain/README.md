# Domain modules

AppGov follows a modular-monolith architecture. A module is created when its
first use case is implemented; empty namespace trees are intentionally avoided.

Planned owners:

- `Identity` — users, citizen profiles and identity assurance.
- `Organizations` — public bodies, units, territories and assignments.
- `Catalog` — services, procedure versions, requirements and fees.
- `Applications` — applications, steps, events and workflow.
- `Documents` — private files, reviews and generated documents.
- `Payments` — invoices, transactions, reconciliation and refunds.
- `Appointments` — locations, slots and reservations.
- `Examinations` — sessions, attempts and validated results.
- `Decisions` — administrative decisions and reviews.
- `Issuance` — title production, movements and delivery.
- `Notifications` — user-facing messages and delivery attempts.
- `Reporting` — read models and operational metrics.
- `Audit` — immutable security and business audit trail.

Controllers never query another module's tables directly. Cross-module
collaboration uses an application action, an explicit contract or a domain
event.

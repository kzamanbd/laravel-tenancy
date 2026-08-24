# Documentation

Architecture and operating guide for **UpFront**.

UpFront is a multi-tenant status-page SaaS. One **organization** pays; it owns one
or more **tenants**; each tenant is exactly one **status page**. Admins manage the
page in a Laravel/Inertia workspace; the public page is a flat file served from
object storage so it stays up when this application does not.

## Map

| Doc | Answers |
|---|---|
| [01 — Architecture](01-architecture.md) | What the system is, its two planes, request paths, and the rules the design obeys |
| [02 — Tenancy & isolation](02-tenancy-and-isolation.md) | How one tenant's data is kept out of another's, and why it is the database that does it |
| [03 — Data model](03-data-model.md) | Every table, relationship, and enum |
| [04 — Publish pipeline](04-publish-pipeline.md) | How a database change becomes a static page on a CDN |
| [05 — Custom domains & TLS](05-custom-domains-tls.md) | Domain verification, on-demand certificates, and the phishing gate |
| [06 — Notifications](06-notifications.md) | Subscriber lifecycle, fan-out, rate limiting, bounces |
| [07 — Roles & permissions](07-roles-and-permissions.md) | Who may do what, and the three layers that enforce it |
| [08 — User guides](08-user-guides.md) | Step-by-step operation for every level of user, visitor through platform operator |
| [09 — Operations](09-operations.md) | Setup, environment, queues, deploy topology, runbooks, known gaps |

## Reading order

- **New engineer:** 01 → 02 → 03, then whichever subsystem you are touching.
- **Reviewing security:** 02 → 07 → 05.
- **Running it in production:** 09 → 04 → 05.
- **Answering "what can this user do?":** 07 → 08.

## Source of truth

These documents describe the code in this repository. Where a rule matters
(isolation, zero-query reads, certificate issuance) the code carries the same
reasoning in a docblock and a test asserts it. If a doc and the code disagree,
the code and its tests win — and the doc is a bug.

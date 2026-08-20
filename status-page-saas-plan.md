# Status Page as a Service — SaaS Plan

A multi-tenant status page platform. This document covers positioning, scope,
pricing, architecture, tenancy model, build sequence, go-to-market, unit
economics, and risks.

---

## 1. The Wedge

The market is crowded — Atlassian Statuspage, Better Stack, Instatus, Status.io,
plus free self-hosted Cachet. Undercutting on price is a losing game; Instatus
already lives at $20. Two openings are real:

### 1.1 Status pages that update themselves

The actual failure mode of status pages isn't the page — it's that during an
incident nobody has time to write updates, so the page sits green while Twitter
burns.

Auto-open incidents from monitoring signals, draft the customer-facing update
from the alert payload plus the Slack thread, and require one click to publish.

> **Pitch:** *"Your page stays honest without a human remembering to be honest."*

### 1.2 Agency / MSP white-label

One agency manages 40 client products, each needing a page. Nested tenancy,
reseller billing, one dashboard. Competitors treat this as an afterthought or
charge per-page.

This is also the version that makes the multi-tenancy genuinely hard — which is
the point if this doubles as a portfolio piece.

**Recommendation:** lead with 1.1, but build 1.2 into the data model from day one.

---

## 2. Ideal Customer Profile

| Segment | Fit | Why |
|---|---|---|
| Seed–Series A B2B SaaS, 5–50 eng | **Primary** | Feel the pain, have paying customers asking, hate the $99 Statuspage bill |
| Dev agencies / MSPs | **Secondary** | Multi-page need, sticky, higher ACV |
| API-first / infra companies | Good | Status page is a sales asset for them |
| Enterprise | **Ignore** | SSO, SOC2, procurement, 9-month cycles |
| Hobbyists | Free tier only | Never convert, but they write blog posts |

---

## 3. Scope

### 3.1 MVP — must ship or don't ship

- Components with status levels
- Incidents with a timeline of updates
- Maintenance windows (scheduled + auto-transition)
- Public page on `tenant.yourapp.com`
- Custom domain + automatic SSL
- Email subscribers with confirm / unsubscribe
- Slack + generic webhook notifications
- Uptime history strip (90 days)
- Basic theming (logo, colors, custom CSS)
- REST API, including a `POST /incidents` a script can hit

### 3.2 V1 — what people pay more for

- Built-in uptime monitoring (HTTP, TCP, keyword, cron heartbeat)
- Auto-incident creation from monitor failures
- AI-drafted incident updates
- Component groups and dependencies
- Audience-specific / private pages (magic link)
- SMS, Discord, Microsoft Teams
- Embeddable status widget and badge
- Status-change history export

### 3.3 V2 — moat

- Agency workspace with sub-tenants and reseller billing
- SSO for private pages
- Postmortem templates and publishing
- SLA reporting per component
- Zendesk / Intercom / Datadog / PagerDuty integrations
- Multi-language pages

### 3.4 Explicitly not building

Full APM, log aggregation, on-call rotation. Integrate with PagerDuty — don't
fight it.

---

## 4. Pricing

| Tier | Price | Contents |
|---|---|---|
| Free | $0 | 1 page, 5 components, subdomain only, 50 email subs, "powered by" badge |
| Starter | $19/mo | Custom domain, 25 components, 1k subs, 20 monitors, integrations, no badge |
| Growth | $59/mo | 5k subs, 100 monitors, private pages, auto-incidents + AI drafts, SMS metered |
| Agency | $149/mo | 10 sub-tenants (+$12 each), full white-label incl. sender domain, API, reseller billing |
| Enterprise | Custom | SSO, SLA, DPA, audit log |

- Annual billing at 2 months free.
- **Meter SMS separately.** It is the one line item that can invert your margin.
  Sell credits; do not bundle generously.

---

## 5. Architecture

### 5.1 The dominating constraint

**Your status page must survive your own outage.**

If the public page renders from the same database and region as your admin app,
your first real incident kills your credibility permanently. Design for this on
day one.

| Path | Design | Can it fail? |
|---|---|---|
| Write (admin app) | Normal stack — Postgres, API, dashboard | Yes, acceptably |
| Publish step | Every state change renders static JSON + HTML to object storage | — |
| Read (public page) | Pure CDN. No database call. | Must not |
| Break-glass admin | Minimal, separately hosted, writes directly to object storage | — |

Put the object storage / CDN on a **different provider and region** than the app.
The break-glass admin is a feature, not paranoia — it is marketing copy.

### 5.2 Notification fanout

Route through a queue (SQS / BullMQ) with **per-tenant rate limits**. An incident
at a tenant with 40k subscribers must not starve every other tenant's delivery.

---

## 6. Multi-Tenancy Decisions

### 6.1 Isolation model

Shared Postgres with **Row-Level Security**.

RLS fails *closed* if you forget a `WHERE tenant_id = ...`; app-level filtering
fails *open*. For a product where a bug means Tenant A reads Tenant B's
unpublished incident, that asymmetry decides it.

### 6.2 Hierarchy — model the agency case now

```
organizations          (billing entity, plan, may be an agency)
  └── workspaces       (a tenant; one status page each)
       └── components
       └── incidents
       └── subscribers
       └── monitors

memberships            (user × org|workspace × role)
```

Ship agency features in V2 if you like, but retrofitting a level of hierarchy
into a live billing system is miserable. An unused level now costs one join.

### 6.3 Tenant resolution

```
Host header
  → custom_domains lookup (cached)
  → fall back to subdomain
  → workspace ID into request context
  → workspace ID into the Postgres session var that RLS reads
```

### 6.4 Custom domains and SSL

The hardest infra piece, and the best moat against a weekend clone.

| Option | Trade-off |
|---|---|
| Caddy + on-demand TLS with an `ask` endpoint validating against your DB | Simplest, self-hosted, no per-hostname fee |
| Cloudflare for SaaS | Fastest to ship, per-hostname cost |

Verify ownership via CNAME **before** issuing. Handle the renewal-failure path —
expired certs on customer domains generate support tickets forever.

### 6.5 The leak test

An automated suite that authenticates as Tenant A and attempts every read and
write against Tenant B's IDs, run in CI.

**If you build nothing else on this list, build this.**

---

## 7. Build Sequence

| Phase | Weeks | Output |
|---|---|---|
| 0 | 1 | Schema + RLS + tenant context + leak test suite |
| 1 | 2–3 | Components, incidents, maintenance, admin UI |
| 2 | 2 | Static publish pipeline + CDN read path + theming |
| 3 | 2 | Custom domains, automated SSL, verification flow |
| 4 | 2 | Email subscribers, queue-based fanout, Slack/webhook |
| 5 | 1–2 | Stripe, plans, quota enforcement, usage metering |
| 6 | 2 | Monitoring engine + auto-incidents |
| 7 | ongoing | Super-admin console, impersonation, tenant provisioning |

Roughly **3 months solo** to a chargeable product, **5 months** to something
competitive.

---

## 8. Go-to-Market

Low ACV means sales calls are unaffordable. Everything must be self-serve and
inbound.

- **SEO is the whole channel.** Target "statuspage alternative", "statuspage
  pricing", "free status page", "status page for [framework]". Comparison pages
  convert absurdly well here because people arrive already annoyed at a bill.
- **Free tier as distribution.** Every free page carries your badge on a domain
  that gets traffic *during outages* — the moment of maximum attention. This is
  the single best growth loop available to this product.
- **Migration tooling.** A one-click Statuspage importer (their API exposes
  components and incident history) removes the only real switching cost.
- **Open-source the page renderer**, keep the platform closed. Buys credibility
  and backlinks in a developer market.
- **Integration marketplaces.** Slack, Vercel, Datadog, Zapier listings are free
  and durable lead sources.

---

## 9. Unit Economics

Infra per Starter tenant — CDN + storage + DB share + email — lands around
**$0.40–1.50/mo**, so roughly **93% gross margin**. SMS breaks this if bundled;
keep it metered.

Realistic SMB SaaS churn is 4–6% monthly. At $19 that puts LTV near **$300–400**,
meaning CAC must stay under about **$100** — which rules out paid acquisition
almost entirely and confirms the SEO/PLG-only strategy above.

---

## 10. Risks

| Risk | Detail | Mitigation |
|---|---|---|
| Commoditized core | The basic product is a weekend build | Defensibility lives in custom domains, deliverability, integrations, trust — budget there |
| Deliverability | Time-critical mail on shared reputation; one abusive tenant degrades everyone | Double opt-in from day one, IP pools segmented by tier |
| Trust paradox | Nobody buys a status page from a vendor whose own page has been down | Uptime bar is higher than your customers' — see §5.1 |
| Free-tier abuse | Custom domains + SSL on free accounts is a phishing vector | Domain verification and rate limits *before* issuance |

**Kill criteria:** if 90 days after launch you can't reach 20 paying customers
through organic channels, the wedge isn't sharp enough. Pivot fully into the
agency / white-label niche, where ACV justifies actually talking to people.

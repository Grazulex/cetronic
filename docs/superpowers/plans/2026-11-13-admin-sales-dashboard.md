# Admin sales dashboard (Part 2) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the admin dashboard and its three widgets by one page that shows the sales of the month, compared at the same date with the previous month (and, for revenue, the same month last year).

**Architecture:** A pure period object computes the compared date ranges; `SalesStatsService` (already the source of the "Statistiques ventes" page) gains two queries (daily revenue, new customers); a custom Filament page `App\Filament\Pages\Dashboard` renders the figures in a Blade view in the Part 1 "Boutique" look, with a cumulative chart as a non-discovered `ChartWidget` in `App\Livewire\Admin` (same pattern as `SalesChart`).

**Tech Stack:** Laravel 10, Filament 3.3, Livewire 3, Chart.js through Filament `ChartWidget`, Pest.

**Spec:** `docs/superpowers/specs/2026-11-13-admin-redesign-design.md` (Part 2). Builds on Part 1 (`docs/superpowers/plans/2026-11-13-admin-boutique-look.md`, branch `feat/admin-boutique-redesign`).

## Global Constraints

- Prod runs PHP 8.3: no 8.4 syntax; after Pint, `git diff | grep -E 'mb_(l|r)?trim\('` must be empty.
- No database change, no new business feature beyond the spec.
- Sales rules = `SalesStatsService`: CA HT = Σ `price_paid × quantity` (stored in cents, euros at the edges), excl. VAT and shipping; every non-deleted order counts whatever its status; date = order `created_at`; day-granular bounds (`from` start of day, `to` end of day).
- A dashboard figure must equal the "Statistiques ventes" figure for the same period (reuse the service, never re-implement the sum).
- **Admins only** see figures, orders and the "à traiter" strip. Agents land on the dashboard and see a short welcome text, no figure.
- UI in French, Part 1 tokens (cream `#faf6f0` cards, `#eee5da` borders, serif title, black accents).
- Commits GPG-signed, Conventional Commits in English, on branch `feat/admin-boutique-redesign` (or a branch cut from it). Never push.

## Review Focus

1. 31st of a month → previous month has fewer days: compared range must clamp (31 March → 1–28/29 February), never spill into March. → Task 1 test.
2. A month with no sale at all (new month on the 1st, or a quiet past month) → page shows 0 / "—" for changes, no division by zero, chart empty but rendered. → Task 3 test.
3. Deleted orders → excluded everywhere (figures, top lists, latest orders). → Task 2 and 3 tests.
4. An agent opening `/admin` → no figure, no order, no customer name in the HTML. → Task 3 test.
5. Navigating to a future month or a garbage `?mois=` value → falls back to the current month. → Task 1 test.

---

### Task 1: The compared period

**Files:**
- Create: `app/DataObjects/Stats/DashboardPeriod.php`
- Test: `tests/Unit/Stats/DashboardPeriodTest.php` (register `uses(Tests\TestCase::class)->in('Unit/Stats')` in `tests/Pest.php` if Carbon test-now needs the app)

**Interfaces:**
- Produces: `final readonly class DashboardPeriod` with
  - `public static function for(?string $month, Carbon $now): self` — `$month` is `'Y-m'`; null, unparsable, or after `$now`'s month → current month.
  - `public Carbon $from, $to` (current range), `$previousFrom, $previousTo`, `$lastYearFrom, $lastYearTo`
  - `public bool $isCurrentMonth`
  - `public function label(): string` → `'Novembre 2026'`
  - `public function rangeLabel(): string` → `'Du 1er au 13 novembre · comparé au 1er–13 octobre'` (current) or `'Novembre 2026 · comparé à octobre 2026'` (past)
  - `public function previousMonth(): string` (`'Y-m'`), `public function nextMonth(): ?string` (null when current)
  - `public function filters(): SalesFilters`, `previousFilters()`, `lastYearFilters()`

Rules: current month → `from` = 1st, `to` = today; previous = 1st of previous month → same day number, clamped to that month's last day; last year = same rule one year back. Past month → whole month vs whole previous month (and whole same month last year).

- [ ] **Step 1: Failing tests** — `DashboardPeriod::for(null, Carbon::parse('2026-11-13'))`: from 2026-11-01, to 2026-11-13, previous 2026-10-01→2026-10-13, last year 2025-11-01→2025-11-13, `label()` `'Novembre 2026'`, `isCurrentMonth` true, `nextMonth()` null; `for(null, 2026-03-31)`: previous 2026-02-01→2026-02-28; `for('2026-09', 2026-11-13)`: 2026-09-01→2026-09-30 vs 2026-08-01→2026-08-31, `nextMonth()` `'2026-10'`; `for('2027-01', …)` and `for('nope', …)` → current month.
- [ ] **Step 2: Run** `php artisan test --compact tests/Unit/Stats/DashboardPeriodTest.php` → FAIL (class missing).
- [ ] **Step 3: Implement.** Month names in French via `Carbon::locale('fr')->translatedFormat('F Y')`, capitalised with `Str::ucfirst`.
- [ ] **Step 4: Run** → PASS. **Step 5: Commit** `feat: compute the dashboard comparison period`.

### Task 2: Two new sales queries

**Files:**
- Modify: `app/Services/SalesStatsService.php`
- Test: `tests/Feature/Stats/SalesStatsServiceTest.php` (append)

**Interfaces:**
- Produces:
  - `public function dailyCumulative(SalesFilters $filters): array` → `array<int, array{day: int, revenue: float}>` one entry per day from `from` to `to` (both required), cumulative euros, days without sale carry the previous total.
  - `public function newCustomers(SalesFilters $filters): int` → customers whose **first ever** non-deleted order falls in `[from, to]` and who ordered in the period.
  - Uses existing `summary()`, `breakdownQuery(…, SalesGroupByEnum::BRAND|ITEM)` for the rest.

- [ ] **Step 1: Failing tests** — sales on 2026-11-02 (100 €) and 2026-11-05 (50 €), one deleted order on 2026-11-03: `dailyCumulative(from 11-01 to 11-06)` = days 1..6 → `[0, 100, 100, 100, 150, 150]`; customer A first order 2026-10-10 and again 2026-11-02, customer B first order 2026-11-05 → `newCustomers(Nov)` = 1; a customer whose only order is deleted is not new.
- [ ] **Step 2: Run** → FAIL. **Step 3: Implement** (day expression like `monthExpression()`, sqlite `strftime('%d', …)` / MySQL `DAY(...)`; reuse `baseQuery()`). **Step 4: Run** → PASS (whole `SalesStatsServiceTest`). **Step 5: Commit** `feat: add daily cumulative revenue and new customers to sales stats`.

### Task 3: The dashboard page

**Files:**
- Create: `app/Filament/Pages/Dashboard.php` (extends `Filament\Pages\Dashboard`, `$view = 'filament.pages.dashboard'`, `#[Url(as: 'mois')] public ?string $month = null`), `resources/views/filament/pages/dashboard.blade.php`, `app/Livewire/Admin/CumulativeRevenueChart.php` (`ChartWidget`, line chart, two datasets "mois en cours" black / "mois précédent" dashed `#cfc4b6`, `public string $month`, `abort_unless` admin like `SalesChart`, not lazy)
- Modify: `app/Providers/Filament/AdminPanelProvider.php` (`->pages([\App\Filament\Pages\Dashboard::class])` instead of Filament's)
- Delete: `app/Filament/Widgets/StatsOverview.php`, `LatestCartsOpen.php`, `LatestCustomersToActived.php` (and their views if any)
- Test: `tests/Feature/Admin/DashboardTest.php`

Content (spec Part 2, admins only), top to bottom:
- Title = `label()`, subtitle = `rangeLabel()`; links "‹ {previous month}" and, for a past month, "{next month} ›" / "Ce mois" (`?mois=`).
- "À traiter" strip: "N clients à activer →" linking to `UserResource::getUrl('index', ['activeTab' => 'a-activer'])`; hidden when N = 0 (N = customers with `is_actif` false, not deleted).
- Four cards: **CA HT** (+ change vs previous, + "vs {mois} {année-1}"), **Commandes**, **Panier moyen HT**, **Clients qui ont commandé** (+ "dont N nouveaux"). Change = `(current − previous) / previous`, shown "▲ 12 %" green / "▼ 3 %" red; previous = 0 → "—".
- Cumulative chart (Task 2 `dailyCumulative` for current and previous range, x = day of month).
- **Top marques du mois** (top 5 by CA HT, with a bar proportional to the first) and **Top produits du mois** (top 5 by quantity, "reference · marque", "N pcs").
- **Dernières commandes** (5 most recent non-deleted orders of the period, newest first: "#reference · client", date, total HT, link to the order edit page).
- Agents: `getHeading()` "Bienvenue" and a one-line text; no query runs.

- [ ] **Step 1: Failing tests** (`Carbon::setTestNow('2026-11-13 10:00')`):
  - admin sees "Novembre 2026", "Du 1er au 13 novembre", the CA HT that `SalesStatsService::summary()` returns for 1–13 Nov (formatted `48 320,00 €` style via `Number::currency(…, 'EUR', 'fr')`), "▲ 50 %" for 150 € vs 100 €, and "dont 1 nouveau";
  - an order on 14 Oct is not counted in the October comparison;
  - top brand and top product names appear in order; latest orders link to `OrderResource::getUrl('edit', …)`; a deleted order appears nowhere;
  - "2 clients à activer" links to the users list tab; with 0 the strip is absent;
  - `?mois=2026-09` shows "Septembre 2026" and "comparé à août 2026"; `?mois=2030-01` shows "Novembre 2026";
  - a month without sales renders with "0,00 €" and "—";
  - `loginAsAgent()` → `/admin` OK, sees "Bienvenue", does not see any customer name, reference or "€";
  - old widgets are gone: `class_exists(App\Filament\Widgets\StatsOverview::class)` false.
- [ ] **Step 2: Run** → FAIL. **Step 3: Implement.** **Step 4: Run** `tests/Feature/Admin` and `tests/Feature/Stats` → PASS; `AdminPagesRenderTest` green; agent navigation test from Part 1 still green.
- [ ] **Step 5: Screenshot** `http://cetronic.test/admin` and compare with `.superpowers/brainstorm/24463-1794539777/content/tableau-de-bord.html`.
- [ ] **Step 6: Commit** `feat: show the sales of the month on the admin dashboard`.

### Task 4: Final check

- [ ] `php artisan test --compact` all green; `vendor/bin/phpstan analyse --memory-limit=1G` OK; `mb_trim` grep empty; `npm run build` OK.
- [ ] Cross-check on local data: the dashboard CA HT for the current month equals "Statistiques ventes" with period "Ce mois-ci".

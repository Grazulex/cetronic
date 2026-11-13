# Admin redesign "Boutique" — design

Date: 2026-11-13 · Status: draft for approval (builder check done)

## Intent

The owner finds the Filament admin "ugly and simplistic": colours, logo,
font, menu, lists and forms too big, and dashboards that serve nothing.
Now that the admin runs on Filament 3, give it a look that matches the
Cetronic brand, make lists and forms denser, and replace the dashboard by
one that answers the first question of the day: **how are this month's
sales going?** Daily users: Geoffrey and the Cetronic staff (French
speakers).

## Goals

1. **"Boutique" look** (picked among three mockups): the public site's
   identity in the admin. Cream sidebar, black accents, serif page titles,
   sans-serif body, the anthracite Cetronic logo.
2. **Menu in French, reordered**, one distinct icon per entry.
3. **Filament's own UI in French** (buttons, filters, pagination…).
4. **Dense lists and forms** (validated mockup): rows about half today's
   height, only useful columns by default, French labels; product form
   regrouped into fewer tabs with a side panel for publication switches.
5. **New dashboard: sales of the month**, compared at the same date.

## Non-goals

- No dark mode (stays off).
- No change to the public site's look or behaviour.
- No database change, no new business feature.
- Secondary resources (logs, metas translations, PDF catalog, customer
  types, pubs) only get the theme, French labels and icons — no
  reorganisation.

## Anti-goals (a failure even if it "works")

- The public site's CSS changes after the build tooling update.
- An e-mail sent to a customer changes language because the admin now
  runs in French.
- An existing admin action, filter, bulk action or page disappears or
  stops working.
- A dashboard figure that disagrees with the "Statistiques ventes" page
  for the same period.

## Constraints

- Filament 3.3 / Livewire 3, Laravel 10. Prod runs PHP 8.3 (no 8.4
  syntax; after Pint, no `mb_trim`).
- Deploy: Forge runs `npm ci || npm install` then `npm run build` on every
  deploy, so a Vite-compiled theme ships without changing the Forge script.
- Front tooling today: Vite 3, laravel-vite-plugin 0.5, Tailwind ^3.1,
  `@tailwindcss/forms`. Filament's theme needs Tailwind ≥ 3.4,
  `@tailwindcss/typography` and `tailwindcss/nesting`.
- Sales figures follow the rules already used by `SalesStatsService` and
  sent to the client: CA HT = Σ paid price × quantity, excluding VAT and
  shipping; every non-deleted order counts whatever its status; date =
  order date.
- Tests: `tests/Feature/Admin/AdminPagesRenderTest.php` (every admin page
  renders) must stay green; full suite and PHPStan green.

## Approaches

1. **Custom Filament theme compiled by Vite (picked).** Filament's
   official theming: a `theme.css` with Filament's Tailwind preset, wired
   with `->viteTheme()`, plus panel settings (logo, font, colours, locale),
   French labels and a new dashboard. Full control, survives Filament
   updates, and fixes the Tailwind utilities missing in our custom views.
   Cost: Tailwind 3.1 → 3.4 and two plugins, shared with the front build.
2. Plain CSS override loaded without compilation: quicker start, no
   tooling change, but overrides Filament's styles one by one, breaks on
   updates, and leaves the missing utilities unsolved.
3. Ready-made third-party theme: few exist for Filament 3, generic, would
   not give the validated Boutique look, external dependency.

## Delivery

Two independently deployable parts, in this order:

1. **Look & feel**: theme, logo, fonts, colours, French UI, menu, dense
   lists and forms.
2. **Dashboard**: sales of the month.

## Part 1 — Look & feel (decided)

**Visual tokens** (from the validated mockup):
- Sidebar background cream `#f6efe6`; surfaces/cards `#faf6f0`; borders
  `#eee5da` / `#e3dbd0`; text `#111`; muted text `#8a8178`.
- Primary = black (`#111`) for buttons and the active menu entry; success
  green `#2f6b45`; danger `#9b3b2f`.
- Page titles in a serif face; body in a clean sans-serif.
- Logo: `public/logo.svg` (anthracite) in the sidebar/top bar and on the
  login page.

**Menu** (French, in this order, distinct icons):
- Tableau de bord
- Ventes: Commandes, Paniers, Statistiques ventes
- Catalogue: Produits, Marques, Catégories, Pubs, Traductions des métas,
  Journal de publication
- Clients: Clients, Types de client, Import remises marque
- Contenu: Textes du site, Messages (site announcements), Catalogues PDF
- Imports: Import CSV, Import photos

**French UI**: the admin panel runs in French (Filament ships French
translations); resources get French model labels.

**Lists** (all resources get the density; the four main ones get the
rest):
- Rows about half today's height (theme CSS).
- Produits, Commandes, Clients, Paniers: French column headers; only
  useful columns visible by default, the others (created/updated dates…)
  stay available as toggleable hidden columns; quick filters as list tabs
  (pills). Products: Tous · En ligne · Hors ligne · En promo · Sans photo;
  thumbnail column when the product has a picture.
- Every existing filter, action and bulk action stays.

**Forms**:
- French labels everywhere on the four main resources.
- Product form: tabs Général · Prix · Photos · Caractéristiques · Ventes
  (today: Brand & category, Master data, Activations, Pictures, Prices,
  Ventes). Fields on three columns; a side panel holds the publication
  switches (en ligne, nouveauté, best-seller) and a short summary (price,
  units sold, photos, last change). Every existing field stays.

## Part 2 — Dashboard (decided)

Replaces the current dashboard and its three widgets.

- **Header**: month name ("Novembre 2026"), period line "Du 1er au 13
  novembre · comparé au 1er–13 octobre", navigation to previous months.
- **"À traiter" strip**: customers waiting for activation, linking to the
  filtered customer list. Hidden when zero. (No "new messages": `Message`
  is a site announcement written by the admin, not an inbox, and contact
  forms are e-mailed, not stored.)
- **Four key figures**, each with its change vs the previous month at the
  same date: CA HT (also vs the same month last year), Commandes, Panier
  moyen HT, Clients qui ont commandé (with "dont N nouveaux").
- **Cumulative CA HT chart**, day by day, this month vs previous month.
- **Top brands of the month** (CA HT) and **top products of the month**
  (units), top 4–5 each.
- **Latest orders** (4–5), linking to the order.
- For a past month, "at the same date" means the whole month vs the whole
  previous month.
- **Access: admins only**, like today's widgets (owner's decision). Agents
  keep a landing page without company figures (empty or a short welcome);
  no sales figure or order is exposed to them. Menu entries an agent
  cannot open stay hidden, as today.

## Left to the builder

- Exact serif and sans-serif fonts (must load in prod; Google Fonts
  allowed), exact icons, exact Tailwind/Filament classes and CSS.
- How to switch the panel to French (middleware or panel config) — must
  respect the anti-goal on customer e-mail language; check
  `UserObserver` jobs (`SendMailActifUser`, `SendMailNewUser`) and order
  mails triggered from the admin.
- Which product columns map to "En ligne / En promo / Sans photo" in the
  data model.
- Reusing `SalesStatsService` for the dashboard (preferred) vs new queries.
- Proving the front CSS is unchanged after the Tailwind update (e.g. diff
  of the built `front.css` before/after).

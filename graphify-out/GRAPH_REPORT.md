# Graph Report - mahan-Portofilo-resume  (2026-11-24)

## Corpus Check
- 69 files · ~312,390 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 42 file(s) not represented in the graph (top: .css 13, .woff 9, .woff2 9)

## Summary
- 796 nodes · 1758 edges · 92 communities (27 shown, 65 thin omitted)
- Extraction: 84% EXTRACTED · 16% INFERRED · 0% AMBIGUOUS · INFERRED: 277 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `a88d2108`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- plugins.min.js
- jquery-3.4.1.min.js
- Bt
- ._queueCallback
- i
- r
- J
- gr
- ut
- At
- Ct
- core.php
- me
- helpers.php
- seo.php
- Ae
- راهنمای نصب روی هاست cPanel
- Vt
- common.js
- t
- setting
- Xt
- render_field
- enhance.js
- Ue
- gs
- ne
- on
- seed.php
- jalali_date
- resources.php
- 2026_10_25_002_categories.php
- {closure#1}
- {closure#1}
- {closure#1}
- {closure#1}
- {closure#1}
- {closure#1}
- {closure#1}
- {closure#1}
- {closure#1}
- {closure#1}
- {closure#1}
- {closure#1}
- {closure#1}
- {closure#1}
- zu
- ja
- bu
- Do
- ei
- Wi
- hi
- CLAUDE.md
- .claude/CLAUDE.md
- _n
- wn

## God Nodes (most connected - your core abstractions)
1. `Bt` - 37 edges
2. `o()` - 32 edges
3. `i()` - 32 edges
4. `a()` - 26 edges
5. `J` - 26 edges
6. `r()` - 25 edges
7. `E()` - 25 edges
8. `ut` - 25 edges
9. `n()` - 24 edges
10. `o()` - 24 edges

## Surprising Connections (you probably didn't know these)
- `{closure#1}()` --calls--> `row()`  [INFERRED]
  sitemap.php → app/helpers.php
- `{closure#1}()` --calls--> `row()`  [INFERRED]
  admin/index.php → app/helpers.php
- `redirect_to_canonical_host()` --calls--> `setting()`  [INFERRED]
  app/seo.php → app/helpers.php
- `sitemap_entry()` --calls--> `absolute_url()`  [INFERRED]
  sitemap.php → app/helpers.php
- `sitemap_entry()` --calls--> `url_for()`  [INFERRED]
  sitemap.php → app/seo.php

## Import Cycles
- None detected.

## Communities (92 total, 65 thin omitted)

### Community 0 - "plugins.min.js"
Cohesion: 0.08
Nodes (51): M(), a(), b(), t(), c(), d(), E(), en() (+43 more)

### Community 2 - "jquery-3.4.1.min.js"
Cohesion: 0.07
Nodes (41): A(), Ae(), b(), be(), bt(), ce(), ct(), d() (+33 more)

### Community 3 - "Bt"
Cohesion: 0.09
Nodes (3): xt(), Bt, Ft

### Community 4 - "._queueCallback"
Cohesion: 0.09
Nodes (4): et(), Gt, st, U

### Community 5 - "i"
Cohesion: 0.17
Nodes (30): a(), c(), ce(), e(), ee(), f(), h(), he() (+22 more)

### Community 6 - "r"
Cohesion: 0.11
Nodes (29): _a(), ao(), ba(), bo(), co(), da(), eo(), fa() (+21 more)

### Community 7 - "J"
Cohesion: 0.16
Nodes (3): X(), Y(), J

### Community 8 - "gr"
Cohesion: 0.18
Nodes (26): Ar(), Bt(), dr(), _e(), Et(), gr(), hr(), Ir() (+18 more)

### Community 12 - "core.php"
Cohesion: 0.14
Nodes (14): {closure#1}(), csrf_field(), csrf_token(), current_admin(), login_failed(), login_locked(), login_succeeded(), redirect() (+6 more)

### Community 13 - "me"
Cohesion: 0.16
Nodes (18): be(), cr(), d(), De(), Dt(), fr(), g(), ge() (+10 more)

### Community 14 - "helpers.php"
Cohesion: 0.16
Nodes (6): db(), db_connect(), latest_posts(), project_category_names(), related_projects(), rows()

### Community 15 - "seo.php"
Cohesion: 0.18
Nodes (11): absolute_url(), jalali_text_to_iso(), jalali_to_gregorian(), redirect_to_canonical_host(), schema_breadcrumb(), schema_page(), schema_site_nodes(), site_url() (+3 more)

### Community 16 - "Ae"
Cohesion: 0.15
Nodes (15): Ae(), al(), cl(), el(), Es(), hl(), il(), i (+7 more)

### Community 17 - "راهنمای نصب روی هاست cPanel"
Cohesion: 0.14
Nodes (12): اجرای محلی (اختیاری), به‌روزرسانی سایت بعد از هر Push, راهنمای نصب روی هاست cPanel, سئو و معرفی سایت به گوگل, نکات مهم, پیش‌نیازها, ۱. ساخت دیتابیس در cPanel, ۲. انتخاب نسخه PHP (+4 more)

### Community 19 - "common.js"
Cohesion: 0.23
Nodes (4): formError(), formSuccess(), submitForm(), submitMSG()

### Community 20 - "t"
Cohesion: 0.18
Nodes (11): As(), b(), Jt(), k(), ko(), ks(), _o(), _s() (+3 more)

### Community 21 - "setting"
Cohesion: 0.39
Nodes (6): setting(), settings(), auto_migrate(), migration_names(), migrations_pending(), run_migrations()

### Community 22 - "Xt"
Cohesion: 0.31
Nodes (8): Gt(), Nt(), Qe(), qt(), se(), Vt(), Xt(), Zt()

### Community 23 - "render_field"
Cohesion: 0.36
Nodes (7): store_upload(), field_options(), read_field(), render_field(), asset(), e(), s()

### Community 24 - "enhance.js"
Cohesion: 0.57
Nodes (6): finish(), frame(), playBar(), render(), show(), startReveal()

### Community 25 - "Ue"
Cohesion: 0.29
Nodes (4): Fe(), Oe(), pe(), Ue()

### Community 26 - "gs"
Cohesion: 0.33
Nodes (6): Cn(), gs(), In(), sn(), un(), zs()

### Community 27 - "ne"
Cohesion: 0.33
Nodes (6): Di(), Ji(), ne(), Qi(), _t(), Yi()

### Community 28 - "on"
Cohesion: 0.40
Nodes (4): an(), Ct(), en(), on()

### Community 30 - "jalali_date"
Cohesion: 0.67
Nodes (3): field_default(), gregorian_to_jalali(), jalali_date()

### Community 50 - "zu"
Cohesion: 0.67
Nodes (3): Iu(), Su(), zu()

### Community 51 - "ja"
Cohesion: 0.67
Nodes (3): ja(), oo(), ro()

## Knowledge Gaps
- **65 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `E()` connect `plugins.min.js` to `Ct`, `._queueCallback`, `i`?**
  _High betweenness centrality (0.138) - this node is a cross-community bridge._
- **Are the 24 inferred relationships involving `o()` (e.g. with `ce()` and `De()`) actually correct?**
  _`o()` has 24 INFERRED edges - model-reasoned connections that need verification._
- **Should `plugins.min.js` be split into smaller, more focused modules?**
  _Cohesion score 0.08045977011494253 - nodes in this community are weakly interconnected._
- **Why does `i()` connect `i` to `plugins.min.js`, `mapbox-gl.js`, `jquery-3.4.1.min.js`, `r`, `J`, `me`, `Ae`, `t`, `ne`, `on`?**
  _High betweenness centrality (0.107) - this node is a cross-community bridge._
- **Are the 19 inferred relationships involving `i()` (e.g. with `A()` and `M()`) actually correct?**
  _`i()` has 19 INFERRED edges - model-reasoned connections that need verification._
- **Should `mapbox-gl.js` be split into smaller, more focused modules?**
  _Cohesion score 0.03278688524590164 - nodes in this community are weakly interconnected._
- **Why does `Z()` connect `plugins.min.js` to `jquery-3.4.1.min.js`, `i`, `J`?**
  _High betweenness centrality (0.060) - this node is a cross-community bridge._
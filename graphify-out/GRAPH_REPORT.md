# Graph Report - mahan-Portofilo-resume  (2026-11-27)

## Corpus Check
- 80 files · ~325,169 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 43 file(s) not represented in the graph (top: .css 14, .woff 9, .woff2 9)

## Summary
- 877 nodes · 1899 edges · 103 communities (28 shown, 75 thin omitted)
- Extraction: 84% EXTRACTED · 16% INFERRED · 0% AMBIGUOUS · INFERRED: 303 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `c588f60e`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- plugins.min.js
- jquery-3.4.1.min.js
- Bt
- Gt
- i
- r
- J
- gr
- ut
- At
- Ct
- core.php
- app/theme.php
- helpers.php
- seo.php
- Ae
- راهنمای نصب روی هاست cPanel
- Vt
- common.js
- t
- setting
- Xt
- me
- enhance.js
- Ue
- gs
- ne
- on
- seed.php
- e
- resources.php
- 2026_10_25_002_categories.php
- admin.js
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
- U
- track_visit
- stats.php
- {closure#1}
- {closure#1}
- {closure#1}
- deployment

## God Nodes (most connected - your core abstractions)
1. `Bt` - 37 edges
2. `i()` - 32 edges
3. `o()` - 32 edges
4. `J` - 26 edges
5. `a()` - 26 edges
6. `ut` - 25 edges
7. `E()` - 25 edges
8. `r()` - 25 edges
9. `h()` - 24 edges
10. `n()` - 24 edges

## Surprising Connections (you probably didn't know these)
- `{closure#2}()` --calls--> `rows()`  [INFERRED]
  admin/stats.php → app/helpers.php
- `{closure#1}()` --calls--> `row()`  [INFERRED]
  sitemap.php → app/helpers.php
- `theme_font_info()` --calls--> `theme_font_path()`  [INFERRED]
  admin/theme.php → app/theme.php
- `{closure#1}()` --calls--> `row()`  [INFERRED]
  admin/index.php → app/helpers.php
- `{closure#1}()` --calls--> `row()`  [INFERRED]
  admin/stats.php → app/helpers.php

## Import Cycles
- None detected.

## Communities (103 total, 75 thin omitted)

### Community 0 - "plugins.min.js"
Cohesion: 0.07
Nodes (52): M(), a(), b(), t(), c(), d(), E(), en() (+44 more)

### Community 2 - "jquery-3.4.1.min.js"
Cohesion: 0.07
Nodes (42): A(), Ae(), b(), be(), bt(), ce(), ct(), d() (+34 more)

### Community 3 - "Bt"
Cohesion: 0.09
Nodes (3): xt(), Bt, Ft

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
Nodes (14): {closure#1}(), {closure#1}(), csrf_field(), csrf_token(), current_admin(), login_failed(), login_locked(), login_succeeded() (+6 more)

### Community 13 - "app/theme.php"
Cohesion: 0.15
Nodes (27): {closure#3}(), theme_build_css(), theme_clear_favicon_files(), theme_color(), theme_color_label(), theme_declarations(), theme_defaults(), theme_effective() (+19 more)

### Community 14 - "helpers.php"
Cohesion: 0.16
Nodes (8): db(), db_connect(), latest_posts(), project_category_names(), related_projects(), rows(), settings(), theme_reset_colors()

### Community 15 - "seo.php"
Cohesion: 0.16
Nodes (12): absolute_url(), phone_international(), jalali_text_to_iso(), jalali_to_gregorian(), redirect_to_canonical_host(), schema_breadcrumb(), schema_page(), schema_site_nodes() (+4 more)

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
Cohesion: 0.26
Nodes (10): save_setting(), setting(), auto_migrate(), migration_names(), migrations_pending(), run_migrations(), theme_favicon_tags(), theme_install_font() (+2 more)

### Community 22 - "Xt"
Cohesion: 0.31
Nodes (8): Gt(), Nt(), Qe(), qt(), se(), Vt(), Xt(), Zt()

### Community 23 - "me"
Cohesion: 0.16
Nodes (18): be(), cr(), d(), De(), Dt(), fr(), g(), ge() (+10 more)

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

### Community 30 - "e"
Cohesion: 0.15
Nodes (13): theme_field_html(), theme_font_info(), store_upload(), field_default(), field_options(), read_field(), render_field(), asset() (+5 more)

### Community 34 - "admin.js"
Cohesion: 0.42
Nodes (6): changed(), counts(), globalValue(), normalize(), paint(), refresh()

### Community 50 - "zu"
Cohesion: 0.67
Nodes (3): Iu(), Su(), zu()

### Community 51 - "ja"
Cohesion: 0.67
Nodes (3): ja(), oo(), ro()

### Community 93 - "track_visit"
Cohesion: 0.31
Nodes (9): analytics_browser(), analytics_device(), analytics_is_bot(), analytics_os(), analytics_referrer(), track_visit(), views_text(), client_ip() (+1 more)

## Knowledge Gaps
- **75 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `E()` connect `plugins.min.js` to `Ct`, `i`?**
  _High betweenness centrality (0.114) - this node is a cross-community bridge._
- **Are the 19 inferred relationships involving `i()` (e.g. with `A()` and `M()`) actually correct?**
  _`i()` has 19 INFERRED edges - model-reasoned connections that need verification._
- **Should `plugins.min.js` be split into smaller, more focused modules?**
  _Cohesion score 0.06529602132114982 - nodes in this community are weakly interconnected._
- **Why does `i()` connect `i` to `plugins.min.js`, `mapbox-gl.js`, `jquery-3.4.1.min.js`, `r`, `J`, `Ae`, `t`, `me`, `ne`, `on`?**
  _High betweenness centrality (0.088) - this node is a cross-community bridge._
- **Are the 24 inferred relationships involving `o()` (e.g. with `ce()` and `De()`) actually correct?**
  _`o()` has 24 INFERRED edges - model-reasoned connections that need verification._
- **Should `mapbox-gl.js` be split into smaller, more focused modules?**
  _Cohesion score 0.03278688524590164 - nodes in this community are weakly interconnected._
- **Why does `Z()` connect `plugins.min.js` to `jquery-3.4.1.min.js`, `i`, `J`?**
  _High betweenness centrality (0.050) - this node is a cross-community bridge._
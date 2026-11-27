# Graph Report - mahan-Portofilo-resume  (2026-11-28)

## Corpus Check
- 84 files · ~328,283 words
- Verdict: corpus is large enough that graph structure adds value.
- Unclassified: 43 file(s) not represented in the graph (top: .css 14, .woff 9, .woff2 9)

## Summary
- 914 nodes · 1983 edges · 93 communities (26 shown, 67 thin omitted)
- Extraction: 84% EXTRACTED · 16% INFERRED · 0% AMBIGUOUS · INFERRED: 324 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `0893861a`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- plugins.min.js
- mapbox-gl.js
- jquery-3.4.1.min.js
- Bt
- Ct
- i
- r
- J
- gr
- ut
- At
- schedule.php
- core.php
- app/theme.php
- helpers.php
- seo.php
- Ae
- راهنمای نصب روی هاست cPanel
- Vt
- common.js
- publish.js
- setting
- Gt
- Xt
- enhance.js
- Ue
- U
- ne
- 2026_11_27_003_php2shell_article_images.php
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
- {closure#1}
- CLAUDE.md
- .claude/CLAUDE.md
- track_visit
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
- `{closure#1}()` --calls--> `row()`  [INFERRED]
  admin/stats.php → app/helpers.php
- `{closure#1}()` --calls--> `row()`  [INFERRED]
  sitemap.php → app/helpers.php
- `theme_font_info()` --calls--> `theme_font_path()`  [INFERRED]
  admin/theme.php → app/theme.php
- `{closure#1}()` --calls--> `row()`  [INFERRED]
  admin/index.php → app/helpers.php
- `{closure#2}()` --calls--> `rows()`  [INFERRED]
  admin/stats.php → app/helpers.php

## Import Cycles
- None detected.

## Communities (93 total, 67 thin omitted)

### Community 0 - "plugins.min.js"
Cohesion: 0.08
Nodes (51): M(), a(), b(), t(), c(), d(), E(), en() (+43 more)

### Community 1 - "mapbox-gl.js"
Cohesion: 0.03
Nodes (34): an(), Ar(), As(), bu(), Cn(), Ct(), Do(), _e() (+26 more)

### Community 2 - "jquery-3.4.1.min.js"
Cohesion: 0.07
Nodes (41): A(), Ae(), b(), be(), bt(), ce(), ct(), d() (+33 more)

### Community 3 - "Bt"
Cohesion: 0.09
Nodes (3): xt(), Bt, Ft

### Community 5 - "i"
Cohesion: 0.14
Nodes (39): b(), c(), ce(), d(), De(), e(), ee(), f() (+31 more)

### Community 6 - "r"
Cohesion: 0.09
Nodes (35): a(), _a(), ao(), ba(), bo(), co(), da(), eo() (+27 more)

### Community 7 - "J"
Cohesion: 0.16
Nodes (3): X(), Y(), J

### Community 8 - "gr"
Cohesion: 0.23
Nodes (22): Bt(), dr(), Et(), gr(), hr(), Ir(), jr(), Mt() (+14 more)

### Community 10 - "At"
Cohesion: 0.11
Nodes (3): et(), At, st

### Community 11 - "schedule.php"
Cohesion: 0.19
Nodes (20): field_default(), render_schedule(), views_text(), fa_digits(), gregorian_to_jalali(), jalali_date(), jalali_month_names(), schedule_check_allowed() (+12 more)

### Community 12 - "core.php"
Cohesion: 0.14
Nodes (14): {closure#1}(), csrf_field(), csrf_token(), current_admin(), login_failed(), login_locked(), login_succeeded(), redirect() (+6 more)

### Community 13 - "app/theme.php"
Cohesion: 0.15
Nodes (27): {closure#3}(), theme_build_css(), theme_clear_favicon_files(), theme_color(), theme_color_label(), theme_declarations(), theme_defaults(), theme_effective() (+19 more)

### Community 14 - "helpers.php"
Cohesion: 0.10
Nodes (8): {closure#1}(), {closure#2}(), db(), db_connect(), latest_posts(), project_category_names(), related_projects(), rows()

### Community 15 - "seo.php"
Cohesion: 0.18
Nodes (10): absolute_url(), phone_international(), redirect_to_canonical_host(), schema_breadcrumb(), schema_page(), schema_site_nodes(), site_url(), url_for() (+2 more)

### Community 16 - "Ae"
Cohesion: 0.15
Nodes (15): Ae(), al(), cl(), el(), Es(), hl(), il(), i (+7 more)

### Community 17 - "راهنمای نصب روی هاست cPanel"
Cohesion: 0.14
Nodes (12): اجرای محلی (اختیاری), به‌روزرسانی سایت بعد از هر Push, راهنمای نصب روی هاست cPanel, سئو و معرفی سایت به گوگل, نکات مهم, پیش‌نیازها, ۱. ساخت دیتابیس در cPanel, ۲. انتخاب نسخه PHP (+4 more)

### Community 19 - "common.js"
Cohesion: 0.23
Nodes (4): formError(), formSuccess(), submitForm(), submitMSG()

### Community 20 - "publish.js"
Cohesion: 0.41
Nodes (12): choose(), makeWheel(), monthDays(), paint(), refresh(), scrollWheel(), selectedText(), setDayCount() (+4 more)

### Community 21 - "setting"
Cohesion: 0.22
Nodes (12): save_setting(), setting(), settings(), auto_migrate(), migration_names(), migrations_pending(), run_migrations(), theme_favicon_tags() (+4 more)

### Community 23 - "Xt"
Cohesion: 0.13
Nodes (18): be(), cr(), Dt(), fr(), ge(), Gt(), Jt(), Kt() (+10 more)

### Community 24 - "enhance.js"
Cohesion: 0.57
Nodes (6): finish(), frame(), playBar(), render(), show(), startReveal()

### Community 25 - "Ue"
Cohesion: 0.29
Nodes (4): Fe(), Oe(), pe(), Ue()

### Community 27 - "ne"
Cohesion: 0.33
Nodes (6): Di(), Ji(), ne(), Qi(), _t(), Yi()

### Community 30 - "e"
Cohesion: 0.19
Nodes (11): theme_field_html(), theme_font_info(), store_upload(), {closure#1}(), field_options(), read_field(), render_field(), asset() (+3 more)

### Community 34 - "admin.js"
Cohesion: 0.42
Nodes (6): changed(), counts(), globalValue(), normalize(), paint(), refresh()

### Community 93 - "track_visit"
Cohesion: 0.43
Nodes (7): analytics_browser(), analytics_device(), analytics_is_bot(), analytics_os(), analytics_referrer(), track_visit(), client_ip()

## Knowledge Gaps
- **67 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `E()` connect `plugins.min.js` to `At`, `Ct`, `i`, `r`?**
  _High betweenness centrality (0.105) - this node is a cross-community bridge._
- **Are the 19 inferred relationships involving `i()` (e.g. with `A()` and `M()`) actually correct?**
  _`i()` has 19 INFERRED edges - model-reasoned connections that need verification._
- **Should `plugins.min.js` be split into smaller, more focused modules?**
  _Cohesion score 0.0794148380355277 - nodes in this community are weakly interconnected._
- **Why does `i()` connect `i` to `plugins.min.js`, `mapbox-gl.js`, `jquery-3.4.1.min.js`, `r`, `J`, `Ae`, `Xt`, `ne`?**
  _High betweenness centrality (0.081) - this node is a cross-community bridge._
- **Are the 24 inferred relationships involving `o()` (e.g. with `ce()` and `De()`) actually correct?**
  _`o()` has 24 INFERRED edges - model-reasoned connections that need verification._
- **Should `mapbox-gl.js` be split into smaller, more focused modules?**
  _Cohesion score 0.025457605722701452 - nodes in this community are weakly interconnected._
- **Why does `Z()` connect `plugins.min.js` to `jquery-3.4.1.min.js`, `i`, `J`?**
  _High betweenness centrality (0.046) - this node is a cross-community bridge._
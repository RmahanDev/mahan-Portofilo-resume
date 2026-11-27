## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Rules:
- When graphify-out/graph.json exists, run `graphify query "<question>"` before your first grep, search or source-file read for a codebase question, and put this rule in every subagent prompt that explores code. Read raw files only for lines the graph or an earlier read already located. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. When the answer is under a Markdown heading, `graphify section "<heading>"` prints that section's text, so the .md need not be opened. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).
- A committed graphify-out/graph.json is rewritten whole on every build, so a large diff to it in a pull request is expected. Do not flag it as churn or suggest skipping the graph rebuild or its hooks.

## Article images

Every image in `assets/images/news/` must follow one style: the site theme and the images that already belong to the articles. Before you make or replace an article image, look at the existing ones (for example `claude-api-vs-subscription-*.jpg`, `wordpress-website-design-*.jpg`, `wordpress-vs-laravel-*.jpg`) and copy their look.

- Colours: the site theme (`app/theme.php`). Background `#0a0a0a` with a warm centre glow around `#443221`; dark cards `#1e1d22`; gold accent `#ffdb6e` to `#ffbc5e`; coral `#ff8064` for a second path or a warning; greys `#56565d` and `#a7a7ad` for placeholder bars.
- Elements: dark rounded cards with a glowing outline, line icons with round caps, glowing paths, four-point sparkles in gold or coral, a faint grid (about 3.5% white), dashed orbit rings, and a dark vignette at the edges.
- Sizes: cover 900x600, header 1600x1067, inline figures 1200x700. Save as progressive JPEG, quality 92.
- Spacing: keep about 5% of the width free at the edges, and use the same gaps between cards as the existing images.
- No text, letters or digits in any image. The one exception is the WordPress mark, which is a logo and not typed text. The caption and the alt text carry the explanation.
- Logos keep their shape. The WordPress mark is a gold disc with the W cut out.
- When an image is replaced, keep its file name so the article references still work.

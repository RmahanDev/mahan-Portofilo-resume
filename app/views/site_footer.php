<?php
/**
 * Site footer: contact details and social links, visible on every page without opening anything.
 * Included by layout_bottom.php right after </main>, so this <footer> is the page's contentinfo landmark.
 *
 * @var array $socials profile links from layout_top.php
 */
$footerSocials = $socials ?? [];
?>
<footer class="site-footer">
    <div class="container">
        <div class="box-outer site-footer__panel">
            <h2 class="title title--h3 site-footer__title">راه‌های ارتباطی</h2>

            <address class="site-footer__address">
                <ul class="details-info site-footer__list">
<?php require __DIR__ . '/contact_items.php'; ?>
                </ul>
            </address>
<?php if ($footerSocials): ?>

            <div class="separation"></div>

            <ul class="site-footer__social">
<?php foreach ($footerSocials as $footerSocial): $footerLabel = social_label($footerSocial); ?>
                <li>
                    <a class="social__link" href="<?= e($footerSocial['url']) ?>" rel="me" title="<?= e($footerLabel) ?>" aria-label="<?= e($footerLabel) ?>">
                        <i class="<?= e($footerSocial['icon']) ?>" aria-hidden="true"></i>
                    </a>
                </li>
<?php endforeach; ?>
            </ul>
<?php endif; ?>
        </div>
    </div>
</footer>

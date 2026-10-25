<?php
/**
 * A row of linked cards (related projects or posts).
 * Expects $linkHeading, $linkCards = [['url' => ..., 'title' => ..., 'image' => ..., 'label' => ...], ...] and $prefix.
 */
if (empty($linkCards)) {
    return;
}
?>
                            <section class="link-cards">
                                <h2 class="title title--h2"><?= e($linkHeading) ?></h2>
                                <div class="link-cards__grid">
<?php foreach ($linkCards as $c): ?>
                                    <a class="link-card box" href="<?= e($c['url']) ?>">
                                        <span class="link-card__media"><img class="link-card__image cover lazyload" src="<?= asset($c['image'], $prefix) ?>" alt="<?= e($c['title']) ?>" loading="lazy" decoding="async" /></span>
                                        <span class="link-card__body">
<?php if ($c['label'] !== ''): ?>
                                            <span class="link-card__label"><?= e($c['label']) ?></span>
<?php endif; ?>
                                            <span class="link-card__title"><?= e($c['title']) ?></span>
                                            <span class="link-card__more"><?= e($linkMore ?? 'مشاهده') ?> ←</span>
                                        </span>
                                    </a>
<?php endforeach; ?>
                                </div>
                            </section>

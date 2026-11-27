<?php
/**
 * Contact rows shared by the site footer and the contact page: email, phone, availability and location.
 * Each row is an <li class="details-info__item"> inside a .details-info list, and a row is left out
 * when its setting is empty. Values come from the admin settings, so the footer always matches them.
 */
$contactRows = [];

$contactEmail = trim(setting('contact_email'));
if ($contactEmail !== '') {
    $contactRows[] = [
        'icon'  => 'icon-envelope',
        'label' => 'ایمیل',
        'text'  => $contactEmail,
        'href'  => filter_var($contactEmail, FILTER_VALIDATE_EMAIL) ? 'mailto:' . $contactEmail : '',
    ];
}

$contactPhone = trim(setting('contact_phone'));
if ($contactPhone !== '') {
    $contactTel = phone_international($contactPhone);
    $contactRows[] = [
        'icon'  => 'icon-phone',
        'label' => 'تلفن',
        'text'  => $contactPhone,
        'href'  => $contactTel !== '' ? 'tel:' . $contactTel : '',
    ];
}

$contactStatus = trim(setting('work_status'));
if ($contactStatus !== '') {
    $contactRows[] = ['icon' => 'icon-calendar', 'label' => 'وضعیت همکاری', 'text' => $contactStatus, 'href' => ''];
}

$contactPlace = trim(setting('location'));
if ($contactPlace !== '') {
    $contactRows[] = ['icon' => 'icon-location', 'label' => 'موقعیت مکانی', 'text' => $contactPlace, 'href' => ''];
}

foreach ($contactRows as $contactRow):
?>
                                    <li class="details-info__item">
                                        <span class="box icon-box" aria-hidden="true"><i class="font-icon <?= e($contactRow['icon']) ?>"></i></span>
                                        <div class="contacts-block__info">
                                            <span class="overhead"><?= e($contactRow['label']) ?></span>
<?php if ($contactRow['href'] !== ''): ?>
                                            <a class="text-overflow" href="<?= e($contactRow['href']) ?>"><?= e($contactRow['text']) ?></a>
<?php else: ?>
                                            <span class="text-overflow"><?= e($contactRow['text']) ?></span>
<?php endif; ?>
                                        </div>
                                    </li>
<?php endforeach; ?>

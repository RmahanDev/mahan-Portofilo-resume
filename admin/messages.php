<?php
require dirname(__DIR__) . '/app/admin/core.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_post();
    $id = (int) ($_POST['id'] ?? 0);
    switch ($_POST['do'] ?? '') {
        case 'delete':
            q('DELETE FROM messages WHERE id = ?', [$id]);
            flash('success', 'پیام حذف شد.');
            redirect('messages.php');
        case 'unread':
            q('UPDATE messages SET is_read = 0 WHERE id = ?', [$id]);
            redirect('messages.php');
        case 'read_all':
            q('UPDATE messages SET is_read = 1');
            redirect('messages.php');
    }
    redirect('messages.php');
}

$menu = 'messages';
$id = (int) ($_GET['id'] ?? 0);
$message = $id ? row('SELECT * FROM messages WHERE id = ?', [$id]) : null;

if ($message) {
    q('UPDATE messages SET is_read = 1 WHERE id = ?', [$id]);
    $pageTitle = 'پیام: ' . $message['subject'];
} else {
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 20;
    $total = (int) row('SELECT COUNT(*) c FROM messages')['c'];
    $pages = max(1, (int) ceil($total / $perPage));
    $list = rows('SELECT * FROM messages ORDER BY id DESC LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage));
    $pageTitle = 'پیام‌های فرم تماس';
}

require dirname(__DIR__) . '/app/admin/layout_top.php';
?>
<?php if ($message): ?>
                        <div class="admin-toolbar">
                            <a class="admin-link" href="messages.php"><i class="feathericon-arrow-right"></i> بازگشت به پیام‌ها</a>
                        </div>
                        <div class="box box-inner admin-message">
                            <ul class="details-info details-info--inline">
                                <li class="details-info__item">
                                    <span class="box icon-box"><i class="font-icon feathericon-user"></i></span>
                                    <div class="details-info__info"><span class="overhead">فرستنده</span><?= e($message['name']) ?></div>
                                </li>
                                <li class="details-info__item">
                                    <span class="box icon-box"><i class="font-icon icon-envelope"></i></span>
                                    <div class="details-info__info"><span class="overhead">ایمیل</span><a href="mailto:<?= e($message['email']) ?>?subject=<?= e(rawurlencode('Re: ' . $message['subject'])) ?>"><?= e($message['email']) ?></a></div>
                                </li>
                                <li class="details-info__item">
                                    <span class="box icon-box"><i class="font-icon icon-phone"></i></span>
                                    <div class="details-info__info"><span class="overhead">موبایل</span><?= e($message['phone'] ?: '-') ?></div>
                                </li>
                                <li class="details-info__item">
                                    <span class="box icon-box"><i class="font-icon icon-calendar"></i></span>
                                    <div class="details-info__info"><span class="overhead">زمان</span><?= e(jalali_date(strtotime($message['created_at']))) ?> - <?= e(date('H:i', strtotime($message['created_at']))) ?></div>
                                </li>
                            </ul>
                            <div class="admin-message__body"><?= nl2br(e($message['message'])) ?></div>
                            <form method="post" class="admin-actions">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= (int) $message['id'] ?>">
                                <a class="btn btn--small" href="mailto:<?= e($message['email']) ?>?subject=<?= e(rawurlencode('Re: ' . $message['subject'])) ?>"><i class="feathericon-corner-up-left"></i> پاسخ با ایمیل</a>
                                <button class="btn btn--small" name="do" value="unread"><i class="feathericon-mail"></i> علامت نخوانده</button>
                                <button class="btn btn--small admin-btn-danger" name="do" value="delete" data-confirm="این پیام حذف شود؟"><i class="feathericon-trash-2"></i> حذف</button>
                            </form>
                        </div>
<?php else: ?>
<?php if (!$list): ?>
                        <div class="box box-inner admin-empty">هنوز پیامی از فرم تماس نرسیده است.</div>
<?php else: ?>
                        <form method="post" class="admin-toolbar">
                            <?= csrf_field() ?>
                            <button class="btn btn--small" name="do" value="read_all"><i class="feathericon-check"></i> همه خوانده شد</button>
                        </form>
                        <div class="admin-table-wrap box box-inner">
                            <table class="admin-table">
                                <thead><tr><th>فرستنده</th><th>موضوع</th><th>ایمیل</th><th>زمان</th></tr></thead>
                                <tbody>
<?php foreach ($list as $m): ?>
                                    <tr class="<?= $m['is_read'] ? '' : 'is-unread' ?>">
                                        <td><a href="messages.php?id=<?= (int) $m['id'] ?>"><?= e($m['name']) ?></a></td>
                                        <td><a href="messages.php?id=<?= (int) $m['id'] ?>"><?= e(mb_strimwidth($m['subject'], 0, 60, '…')) ?></a></td>
                                        <td dir="ltr"><?= e($m['email']) ?></td>
                                        <td><?= e(jalali_date(strtotime($m['created_at']))) ?></td>
                                    </tr>
<?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
<?php if ($pages > 1): ?>
                        <div class="admin-pager">
<?php for ($i = 1; $i <= $pages; $i++): ?>
                            <a class="<?= $i === $page ? 'is-active' : '' ?>" href="messages.php?page=<?= $i ?>"><?= $i ?></a>
<?php endfor; ?>
                        </div>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<?php require dirname(__DIR__) . '/app/admin/layout_bottom.php';

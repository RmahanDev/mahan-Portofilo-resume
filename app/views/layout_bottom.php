<?php
/**
 * Closes the content column opened by layout_top.php and loads the page scripts.
 *
 * @var string $prefix
 * @var string $beforeTop   markup placed right after </main> (PhotoSwipe)
 * @var string $afterMain   markup placed after the back-to-top button (review popups)
 * @var string $extraScripts markup placed after common.js
 */
$p = $prefix;
?>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?= $beforeTop ?? '' ?>

    <div class="back-to-top"></div>
<?= $afterMain ?? '' ?>

    <script src="<?= $p ?>assets/js/jquery-3.4.1.min.js"></script>
    <script src="<?= $p ?>assets/js/plugins.min.js"></script>
    <script src="<?= $p ?>assets/js/common.js"></script>
<?= $extraScripts ?? '' ?>
</body>

</html>

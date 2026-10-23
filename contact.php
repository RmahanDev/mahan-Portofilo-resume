<?php
require __DIR__ . '/app/bootstrap.php';

$prefix = '';
$active = 'contact';
$title = setting('seo_contact_title');
$description = setting('seo_contact_description');

$mapConfig = json_encode([
    'lat'   => (float) setting('map_lat', '35.828474'),
    'lng'   => (float) setting('map_lng', '50.969361'),
    'zoom'  => (float) setting('map_zoom', '13'),
    'popup' => setting('map_popup'),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);

$extraHead = <<<HTML
    <link href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" rel="stylesheet" />
    <script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js"></script>

    <script src="assets/js/mapbox-gl.js"></script>
    <link href="assets/styles/mapbox-gl.css" rel="stylesheet" />

    <style>
        #phoneContact::placeholder {
            direction: rtl;
            text-align: right;
        }
        #validator-contact.validation-success { color: var(--color-accent); }
        #validator-contact.validation-danger { color: var(--color-error); }
        .contact-form__hp { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); opacity: 0; pointer-events: none; }
    </style>
    <script>window.MAP_CONFIG = {$mapConfig};</script>
HTML;

$extraScripts = <<<'HTML'
    <script src="assets/js/mapbox.init.js"></script>
    <script>
        // Messages are stored in the database and read from the admin panel.
        $(function () {
            var $form = $('#contact-form');
            var $msg = $('#validator-contact');
            var $btn = $form.find('button[type="submit"]');

            // common.js binds a demo AJAX handler; replace it with ours.
            $form.off('submit').on('submit', function (e) {
                e.preventDefault();
                $btn.prop('disabled', true);
                $.post('contact-send', $form.serialize())
                    .done(function (res) {
                        $msg.removeClass().addClass('validation-success').text(res.message);
                        $form[0].reset();
                    })
                    .fail(function (xhr) {
                        var text = (xhr.responseJSON && xhr.responseJSON.message) || 'ارسال پیام انجام نشد، لطفا دوباره تلاش کنید.';
                        $msg.removeClass().addClass('validation-danger').text(text);
                    })
                    .always(function () { $btn.prop('disabled', false); });
            });
        });
    </script>
HTML;

require __DIR__ . '/app/views/layout_top.php';
?>

                        <div class="pb-0 pb-sm-2">
                            <h1 class="title title--h1 title__separate"><?= s('contact_title') ?></h1>
                        </div>

                        <div class="map" id="map"></div>

                        <h2 class="title title--h2"><?= s('contact_form_title') ?></h2>

                        <form id="contact-form" class="contact-form" data-toggle="validator">
                            <div class="contact-form__hp" aria-hidden="true">
                                <input type="text" name="website" tabindex="-1" autocomplete="off">
                                <input type="hidden" name="ts" value="<?= time() ?>">
                            </div>
                            <div class="row">
                                <div class="form-group col-12 col-md-6">
                                    <input type="text" class="input form-control" id="nameContact" name="nameContact"
                                        placeholder="نام و نام خانوادگی" required="required" autocomplete="on"
                                        oninvalid="setCustomValidity('لطفا نام و نام خانوادگی خود را وارد کنید.')"
                                        onkeyup="setCustomValidity('')">
                                    <div class="help-block with-errors"></div>
                                </div>

                                <div class="form-group col-12 col-md-6">
                                    <input type="email" class="input form-control" id="emailContact" name="emailContact"
                                        placeholder="ایمیل" required="required" autocomplete="on"
                                        oninvalid="setCustomValidity('ایمیل نادرست است.')"
                                        onkeyup="setCustomValidity('')">
                                    <div class="help-block with-errors"></div>
                                </div>

                                <div class="form-group col-12 col-md-12">
                                    <input type="text" class="input form-control" id="phoneContact" name="phoneContact"
                                        placeholder="شماره موبایل" autocomplete="on">
                                </div>

                                <div class="form-group col-12 col-md-12">
                                    <input type="text" class="input form-control" id="subjectContact"
                                        name="subjectContact" placeholder="موضوع پیام" required="required"
                                        autocomplete="on" oninvalid="setCustomValidity('لطفا موضوع پیام را وارد کنید.')"
                                        onkeyup="setCustomValidity('')">
                                    <div class="help-block with-errors"></div>
                                </div>

                                <div class="form-group col-12 col-md-12">
                                    <textarea class="textarea form-control" id="messageContact" name="messageContact"
                                        placeholder="پیام..." rows="4" required="required"
                                        oninvalid="setCustomValidity('لطفا پیام خود را وارد کنید.')"
                                        onkeyup="setCustomValidity('')"></textarea>
                                    <div class="help-block with-errors"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 col-md-6 order-2 order-md-1 text-center text-md-start">
                                    <div id="validator-contact" class="hidden"></div>
                                </div>

                                <div class="col-12 col-md-6 order-1 order-md-2 text-end">
                                    <button type="submit" class="btn"><i class="font-icon icon-send"></i> ارسال
                                        پیام</button>
                                </div>
                            </div>
                        </form>
<?php require __DIR__ . '/app/views/layout_bottom.php';

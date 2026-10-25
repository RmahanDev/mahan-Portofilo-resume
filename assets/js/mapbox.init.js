// مختصات و متن نقشه از پنل ادمین (contact.php) می‌آید؛ مقادیر پیش‌فرض همان مقادیر قبلی است
var mapCfg = Object.assign({ lat: 35.828474, lng: 50.969361, zoom: 13, popup: 'Alborz Province, Karaj, South Kuye Karmandan, Iran' }, window.MAP_CONFIG || {});


// اگر کتابخانهٔ نقشه (maplibre) بارگذاری نشد، نقشهٔ رایگان OpenStreetMap به‌صورت iframe نمایش داده می‌شود
if (typeof maplibregl === 'undefined') {
    var d = 0.012;
    var bbox = [mapCfg.lng - d * 2, mapCfg.lat - d, mapCfg.lng + d * 2, mapCfg.lat + d].join('%2C');
    var frame = document.createElement('iframe');
    frame.src = 'https://www.openstreetmap.org/export/embed.html?bbox=' + bbox + '&layer=mapnik&marker=' + mapCfg.lat + '%2C' + mapCfg.lng;
    frame.title = 'نقشهٔ موقعیت';
    frame.loading = 'lazy';
    frame.style.cssText = 'width:100%;height:100%;border:0;filter:invert(1) hue-rotate(180deg) brightness(.92) contrast(.88) saturate(.55)';
    document.getElementById('map').appendChild(frame);
} else {
    // تایل‌های رایگان OpenStreetMap (بدون کلید API). ظاهر تیره با فیلتر CSS روی بوم نقشه ساخته می‌شود (enhance.css).
    // تایل‌های قبلی carto دیگر بدون کلید کار نمی‌کردند و روی نقشه «API KEY REQUIRED» می‌نوشتند.
    var map = new maplibregl.Map({
        container: 'map',
        style: {
            'version': 8,
            'sources': {
                'raster-tiles': {
                    'type': 'raster',
                    'tiles': [
                        'https://tile.openstreetmap.org/{z}/{x}/{y}.png'
                    ],
                    'tileSize': 256,
                    'maxzoom': 19,
                    'attribution': '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
                }
            },
            'layers': [
                {
                    'id': 'simple-tiles',
                    'type': 'raster',
                    'source': 'raster-tiles',
                    'minzoom': 0,
                    'maxzoom': 19
                }
            ]
        },
        center: [mapCfg.lng, mapCfg.lat], // (طول، عرض)
        zoom: mapCfg.zoom
    });

    // حل مشکل لود نشدن کامل نقشه در تب‌های بوت‌استرپ با تاخیر ۱۰۰ میلی‌ثانیه‌ای
    map.on('load', function () {
        map.resize();
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(){
            setTimeout(function() {
                map.resize();
            }, 100);
        });
    });

    // ساخت Popup روی مارکر
    var popup = new maplibregl.Popup({ offset: 40 }).setText(mapCfg.popup);

    // لینک «مسیریابی با Google Maps» از همین مختصات ساخته می‌شود
    $('#map-google').attr('href', 'https://www.google.com/maps/search/?api=1&query=' + mapCfg.lat + ',' + mapCfg.lng);

    // ساخت همان المنت قبلی با کلاس و آیدی شما برای حفظ استایل طلایی
    var el = document.createElement('div');
    el.id = 'marker';

    // قرار دادن مارکر سفارشی شما روی نقشه
    new maplibregl.Marker(el)
        .setLngLat([mapCfg.lng, mapCfg.lat])
        .setPopup(popup)
        .addTo(map);

    // دکمه‌های مثبت و منفی زوم نقشه
    map.addControl(new maplibregl.NavigationControl(), 'bottom-right');

    var el = document.createElement('div');
    el.id = 'marker';
}

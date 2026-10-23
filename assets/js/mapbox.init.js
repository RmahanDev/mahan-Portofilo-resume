// مختصات و متن نقشه از پنل ادمین (contact.php) می‌آید؛ مقادیر پیش‌فرض همان مقادیر قبلی است
var mapCfg = Object.assign({ lat: 35.828474, lng: 50.969361, zoom: 13, popup: 'Alborz Province, Karaj, South Kuye Karmandan, Iran' }, window.MAP_CONFIG || {});

// ایجاد نقشه با استفاده از استایل تاریک و بدون نیاز به هیچ توکنی
var map = new maplibregl.Map({
    container: 'map',
    style: {
        'version': 8,
        'sources': {
            'raster-tiles': {
                'type': 'raster',
                'tiles': [
                    'https://basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
                ],
                'tileSize': 256,
                'attribution': '&copy; OpenStreetMap contributors'
            }
        },
        'layers': [
            {
                'id': 'simple-tiles',
                'type': 'raster',
                'source': 'raster-tiles',
                'minzoom': 0,
                'maxzoom': 18
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
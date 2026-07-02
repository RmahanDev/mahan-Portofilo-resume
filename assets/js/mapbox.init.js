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
    center: [50.969361, 35.828474], // مختصات اصلاح شده کرج (طول، عرض)
    zoom: 13
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
var popup = new maplibregl.Popup({ offset: 40 }).setText(
    'Alborz Province, Karaj, South Kuye Karmandan, Iran'
);

// ساخت همان المنت قبلی با کلاس و آیدی شما برای حفظ استایل طلایی
var el = document.createElement('div');
el.id = 'marker';

// قرار دادن مارکر سفارشی شما روی نقشه
new maplibregl.Marker(el)
    .setLngLat([50.969361, 35.828474])
    .setPopup(popup)
    .addTo(map);

// دکمه‌های مثبت و منفی زوم نقشه
map.addControl(new maplibregl.NavigationControl(), 'bottom-right');

var el = document.createElement('div');
el.id = 'marker';

// ۱. خواندن فایل SVG و تزریق به مارکر
fetch('../images/icon-map-pic.svg')
    .then(response => response.text())
    .then(svgText => {
        el.innerHTML = svgText; // آیکون داخل دایو قرار می‌گیرد
        
        // ۲. پیدا کردن تگ path یا کل svg و تغییر رنگ با JS
        var svgElement = el.querySelector('svg');
        if (svgElement) {
            svgElement.style.fill = '#f4c430'; // رنگ طلایی دلخواه شما
            svgElement.style.width = '100%';
            svgElement.style.height = '100%';
        }
    });

// ۳. اضافه کردن به نقشه (مانند قبل)
new maplibregl.Marker(el)
    .setLngLat([50.969361, 35.828474])
    .addTo(map);

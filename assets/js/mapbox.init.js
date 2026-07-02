// تعریف مختصات
var lat = 35.828474;
var lng = 50.969361;

// ایجاد نقشه
var map = L.map('map').setView([lat, lng], 13);

// اضافه کردن لایه تاریک (CartoDB Dark Matter)
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// اضافه کردن مارکر
var marker = L.marker([lat, lng]).addTo(map);
marker.bindPopup("<b>من اینجا هستم!</b><br>Karaj, Alborz Province").openPopup();

// حل مشکل رندر در تب‌های بوت‌استرپ
$('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(){
    map.invalidateSize();
});

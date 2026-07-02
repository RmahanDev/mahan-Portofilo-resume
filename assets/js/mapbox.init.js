mapboxgl.accessToken = 'pk.eyJ1IjoiYXJ0dGVtcGxhdGUiLCJhIjoiY2s0M3I5ZHgzMGEzNDNucXM1cDd0dzl3cSJ9.a2wjLlxz8LzWj9nIoGsshw';
var map = new mapboxgl.Map({
    container: 'map',
    style: 'mapbox://styles/mapbox/dark-v10',
    center: [50.969361, 35.828474], // lng, lat
    zoom: 13
});

map.on('load', function () {
    map.resize();
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(){
        map.resize();
    });
});

var popup = new mapboxgl.Popup({ offset: 40 }).setText(
    'Alborz Province, Karaj, South Kuye Karmandan, Iran'
);

var el = document.createElement('div');
el.id = 'marker';

new mapboxgl.Marker(el)
    .setLngLat([50.969361, 35.828474]) // lng, lat
    .setPopup(popup)
    .addTo(map);

map.addControl(new mapboxgl.NavigationControl(), 'bottom-right');
$(document).ready(function() {
    if (typeof google != 'undefined' && $('#map-canvas').length > 0) {
       map = new google.maps.Map($('#map-canvas')[0],{
            zoom: radius,
            scrollwheel: false,
            center: new google.maps.LatLng(centroid.latitude,centroid.longitude)
       });
       count = 0;
       $.each(points, function(index, point) {
          new google.maps.Marker({
                position: new google.maps.LatLng(point.latitude, point.longitude),
                map: map,
                label: String.fromCharCode(65+count++)
          });
       });
    }
    $(".fancybox").fancybox();
});
function updateMap() {
        if (typeof google != 'undefined' && $('#map-canvas').length > 0) {
           if (typeof map == 'undefined') {
                map = new google.maps.Map($('#map-canvas')[0],{
                     zoom: radius,
                     scrollwheel: false,
                     center: new google.maps.LatLng(centroid.latitude,centroid.longitude)
                });
                markers = [];
           }
           $.each(markers, function(index, marker) {
               marker.setMap(null);
           });
           count = 0;
           $.each(points, function(index, point) {
              markers[markers.length+1] = new google.maps.Marker({
                    position: new google.maps.LatLng(point.latitude, point.longitude),
                    map: map,
                    label: String.fromCharCode(65+count++)
              });
           });
        }
        $(".fancybox").fancybox();
}
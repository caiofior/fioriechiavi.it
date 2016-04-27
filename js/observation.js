function updateMap() {
        $(document).ready(function() {
        if (typeof google != 'undefined' && $('#map-canvas').length > 0) {  
        latLong = new google.maps.LatLng(centroid.latitude,centroid.longitude);
        if (typeof map == 'undefined' ) {
            map = new google.maps.Map($('#map-canvas')[0],{
                        zoom: radius,
                        scrollwheel: false,
                        center: latLong
                    });
            markers=[];
        }
        
        $.each(markers, function(index, marker) {
            marker.setMap(null);
        });
        count = 0;
        $.each(points, function(index, point) {
            markers[markers.length] = new google.maps.Marker({
                 position: new google.maps.LatLng(point.latitude, point.longitude),
                  map: map,
                  label: String.fromCharCode(65+count++)
            });
         });
         map.setZoom(radius);
         map.setCenter(latLong);
        }
        
        $(".fancybox").fancybox();
        });
}
$("#observationContent").on("click","#paginationContainer a.pageSelector",function(e){
    $.ajax({
        url: $(this).attr("href"),
        data: {"xhr":1},
        method:"post",
        async : false,
        success : function (data) {
                $("#observationContent").html(data);
        }
    }); 
    e.preventDefault();
});
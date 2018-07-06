function updateMap() {
        $(document).ready(function() {
        if ($('#map-canvas').length > 0) {

         mapboxgl.accessToken = mapBoxToken;
         var map = new mapboxgl.Map({
            container: 'map-canvas',
            style: 'mapbox://styles/mapbox/outdoors-v9',
            center: [centroid.longitude,centroid.latitude],
            zoom: radius
         });
         var count = 0;
         $.each(points, function(index, point) {
            var el = $("<div>"+ String.fromCharCode(65+count++)+"</div>");
            new mapboxgl.Marker(el[0])
           .setLngLat([point.longitude,point.latitude])
           .addTo(map);




          });
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

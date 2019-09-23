$(document).ready(function() {
      $( "#taxasearch" ).autocomplete({
      source: "?action=taxasearch",
      select: function( e, ui ) {
         window.location.href = ui.item.value;
         e.preventDefault();
      },
      change: function (e, ui) {
          if (!ui.item)
              $(this).val("");
      }
    });
    $(".more_info").click (function (e){
       $(this).parent().siblings("div").show();
       e.preventDefault();
    });
    $("#signalObservationButton").click(function (e){
       $("#signalObservation").load(window.location.href.replace("#","")+"&action=signalObservation",function(e ){
           $("#signalObservationButton").hide();
       });
       e.preventDefault();
   });
   if($(".github").css("display") != "none") {
    if (typeof searchTerm != 'undefined' && searchTerm != "") {
      var images =[];
      $.getJSON( "https://www.googleapis.com/customsearch/v1?q="+searchTerm+"&cx="+cx+"&key="+key+"&num=7", function( data ) {
        $.each(data["items"],function (key,value) {
           if (
	          typeof value["pagemap"] == "object" &&
	   	  typeof value["pagemap"]["cse_thumbnail"] == "object") {
         images.push(value);
	      $("#imageSnipets").append(
	      "<div><a href=\""+value["link"]+"\" target=\"_blank\"><img src=\""+
	      value["pagemap"]["cse_thumbnail"][0]["src"]+
	      "\" alt=\""+value["title"]+"\"/><br/><span>"+value["title"]+"</span></a></div>"
	      );
	   }
        });
        $.ajax({
           type: "POST",
           url:window.location.href+"&action=saveGoogleSearch",
           data:JSON.stringify(images),
           contentType: "application/json; charset=utf-8",
           dataType: "json"
        });
     });
   }
   if ($('#map-canvas').length > 0) {

      mapboxgl.accessToken = mapBoxToken;
      var map = new mapboxgl.Map({
         container: 'map-canvas',
         style: 'mapbox://styles/mapbox/outdoors-v9',
         center: [longitude,latitude],
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
   }
});

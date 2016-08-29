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
      $.getJSON( "https://www.googleapis.com/customsearch/v1?q="+searchTerm+"&cx="+cx+"&key="+key+"&num=7", function( data ) {
        $.each(data["items"],function (key,value) {
           if (   
	          typeof value["pagemap"] == "object" &&
	   	  typeof value["pagemap"]["cse_thumbnail"] == "object") {
	      $("#imageSnipets").append(
	      "<a href=\""+value["link"]+"\" class=\"externalLink\"><img src=\""+
	      value["pagemap"]["cse_thumbnail"][0]["src"]+
	      "\" alt=\""+value["title"]+"\"/><div>"+value["title"]+"</div></a>"
	      );
	   }
        });
     });
   }
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
   }
});
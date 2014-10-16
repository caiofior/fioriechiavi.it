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
    $.getJSON( "https://www.googleapis.com/customsearch/v1?q="+searchTerm+"&cx="+cx+"&key="+key+"&num=7", function( data ) {
      $.each(data["items"],function (key,value) {
         if (   
	        typeof value["pagemap"] == "object" &&
	 	typeof value["pagemap"]["cse_thumbnail"] == "object") {
	    $("#imageSnipets").append(
	    "<img src=\""+
	    value["pagemap"]["cse_thumbnail"][0]["src"]+
	    "\"/>"
	    );
	 }
      });
   });
});
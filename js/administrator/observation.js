$(document).ready(function() {
    ct = $("#observation").dataTable({
        "oLanguage":  {
            "sUrl": "js/common/datatables/lang/it.json"
         },
        "bStateSave" : true,
        "aaSorting": [[ 1, "desc" ]],
        "bJQueryUI": true,
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "#",
         "fnServerParams": function ( aoData ) {
            aoData.push({ "name": "task", "value": "observation" });  
         },
        "aoColumnDefs":  getDatatableMetadata(this),
        "drawCallback": function( ) {
            $(".actions.delete").click(function (e) {
               e.preventDefault();
               $(this).dialog({
                  buttons: {
                     "Confermi la cancellazione dell'osservazione?": function() {
                        $.ajax({
                           url: $(this).attr("href"),
                           async : false
                           });
                        ct.dataTable().fnDraw();
                        $( this ).dialog( "close" );
                     }
                  }
               });
            });
        }
    });
    tinymce.init({
      selector: "textarea"
    });
    if (typeof google != 'undefined') {
        latLong = new google.maps.LatLng(latitude, longitude);
        map = new google.maps.Map($('#map-canvas')[0],{
            zoom: 12,
            scrollwheel: false,
            center: latLong
        });
        marker = new google.maps.Marker({
              position: latLong,
              map: map,
	      draggable:true
        });
	$("#update_position").show().click(function(e){
	  $("#latitude").attr("readonly",false).val(marker.getPosition().lat());
	  $("#longitude").attr("readonly",false).val(marker.getPosition().lng());
	});
    }
    $.datepicker.setDefaults($.datepicker.regional["it"]);
    $("#update_datetime").click(function(e){
        $("#datetime").attr("readonly",false).datepicker({ "dateFormat": "yy-mm-dd"});
    });
    $("#show_metadata").click(function(e){
        $("#metadata").toggle();
        e.preventDefault();
    });
});
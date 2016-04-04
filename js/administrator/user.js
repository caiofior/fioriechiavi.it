$(document).ready(function() {
    us = $("#users").dataTable({
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
            aoData.push({ "name": "task", "value": "user" });  
         },
        "aoColumnDefs":  getDatatableMetadata(this) ,
        "drawCallback": function( ) {
            $("td.isactive input").click(function (e) {
               aPos = us.fnGetPosition( $(this).parent()[0] );
               aData = us.fnGetData( aPos[0] );
               $.ajax({
                  data: {
                    "action":"isactive",
                    "id":aData[0],
                    "checked":($(this).is(':checked') ? 1 : 0)
                  },
                  async : false
               });
               us.dataTable().fnDraw();
            });
            $(".actions.delete").click(function (e) {
               e.preventDefault();
               $(this).dialog({
                  buttons: {
                     "Confermi la cancellazione dell'utente?": function() {
                        $.ajax({
                           url: $(this).attr("href"),
                           async : false
                           });
                        us.dataTable().fnDraw();
                        $( this ).dialog( "close" );
                     }
                  }
               });
            });
        }
    });
});
$.datepicker.setDefaults($.datepicker.regional["it"]);
$("#expire").datepicker({ "dateFormat": "yy-mm-dd"});
$("#taxa_id").autocomplete({
   source: function (request, response) {
      $.getJSON("#", {
         task: "user",
         action:"taxalist",
         term:$( "#taxa_id" ).val(),
         taxa_list: $( "#taxa_id" ).parents("form").find("#taxa_list input[name=taxa_list\\[\\]]").serialize(),
      }, 
      response);
   },
   select: function(e, ui ) {
       $("#taxa_list").append("<div>"+ui.item.label+" <a class='remove_taxa' href='#'>X</a><input type='hidden' name='taxa_list[]' value='"+ui.item.value+"'/></div>");
       $("#taxa_id").val("");
       removeTaxa ();
       e.preventDefault();
   }
});
removeTaxa ();
function removeTaxa () {
    $(".remove_taxa").click(function (e) {
        p =  $(this).parent("div");
        $(this).dialog({
           buttons: {
              "Confermi la cancellazione del taxa?": function() {
                    $(this).dialog('close');
                    p.remove();
              }
           }
        });
        e.preventDefault();
    });
}
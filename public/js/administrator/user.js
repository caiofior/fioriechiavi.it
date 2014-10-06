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
                    "user_id":aData[0],
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
                        ct.dataTable().fnDraw();
                        $( this ).dialog( "close" );
                     }
                  }
               });
            });
        }
    });
});
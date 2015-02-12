$(document).ready(function() {
    ct = $("#contactT").dataTable({
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
            aoData.push({ "name": "task", "value": "contact" });  
         },
        "aoColumnDefs":  getDatatableMetadata(this),
        "drawCallback": function( ) {
         $(".actions.delete").click(function (e) {
            e.preventDefault();
            $(this).dialog({
               buttons: {
                  "Confermi la cancellazione del messaggio?": function() {
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
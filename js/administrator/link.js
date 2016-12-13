$(document).ready(function() {
    rg = $("#link").dataTable({
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
            aoData.push({ "name": "task", "value": "link" });  
         },
        "aoColumnDefs":  getDatatableMetadata(this),
        "drawCallback": function( ) {
            $(".actions.delete").click(function (e) {
               e.preventDefault();
               $(this).dialog({
                  buttons: {
                     "Confermi la cancellazione del link?": function() {
                        $.ajax({
                           url: $(this).attr("href"),
                           async : false
                           });
                        rg.dataTable().fnDraw();
                        $( this ).dialog( "close" );
                     }
                  }
               });
         });
        }
    });
});

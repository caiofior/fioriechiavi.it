$(document).ready(function() {
    rg = $("#log").dataTable({
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
            aoData.push({ "name": "task", "value": "log" });  
         },
         "drawCallback": function( ) {
            $("a.blank").attr('target','_blank');    
         },
        "aoColumnDefs":  getDatatableMetadata(this)
    });
});
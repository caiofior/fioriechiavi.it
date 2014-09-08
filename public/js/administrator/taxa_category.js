$(document).ready(function() {
    $("#categoryTaxa").dataTable({
        "oLanguage":  {
                "sUrl": "js/DataTables/lang/it.json"
         },
        "bStateSave" : true,
        "aaSorting": [[ 1, "desc" ]],
        "bJQueryUI": true,
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "#",
         "fnServerParams": function ( aoData ) {
            aoData.push({ "name": "task", "value": "taxa_category" });  
         },
        "aoColumnDefs":  getDatatableMetadata(this)
    });
    $("#addCategoryTaxa").click(function() {
       return false;
    });
});
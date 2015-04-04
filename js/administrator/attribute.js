$(document).ready(function() {
    rg = $("#attribute").dataTable({
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
            aoData.push({ "name": "task", "value": "attribute" });  
         },
        "aoColumnDefs":  getDatatableMetadata(this),
        "drawCallback": function( ) {
            $(".actions.delete").click(function (e) {
               e.preventDefault();
               $(this).dialog({
                  buttons: {
                     "Confermi la cancellazione dell'attributo?": function() {
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
    vl = $("#values").dataTable({
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
            aoData.push({ "name": "task", "value": "attribute" },{ "name": "action", "value": "value" },{ "name": "id", "value": $("#id").val() });  
         },
        "aoColumnDefs":  getDatatableMetadata(this),
        "drawCallback": function( ) {
            $(".actions.delete").click(function (e) {
               e.preventDefault();
               $(this).dialog({
                  buttons: {
                     "Confermi la cancellazione del valore?": function() {
                        $.ajax({
                           url: $(this).attr("href"),
                           async : false
                           });
                        vl.dataTable().fnDraw();
                        $( this ).dialog( "close" );
                     }
                  }
               });
         });
         $(".actions.edit").click(function (e) {
            oldVal=$(this).parent("td").prev().text();
            $(this).parent("td").prev().editable('?task=attribute&action=jeditable&id='+$('#id').val()+"&old_val="+oldVal, {
                  "onblur": "submit",
                  "indicator" : "Salvataggio in corso...",
                  "tooltip"   : "Click per modificare...",
                  "placeholder" : "Clicca per modificare"
             });
             $(this).parent("td").prev().focus();
            e.preventDefault();
         });
        }
    });
});
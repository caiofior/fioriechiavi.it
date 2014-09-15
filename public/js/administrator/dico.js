$(document).ready(function() {
    di = $("#dico").dataTable({
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
            aoData.push({ "name": "task", "value": "dico" });  
         },
        "aoColumnDefs":  getDatatableMetadata(this),
        "drawCallback": function( ) {
            $(".actions.delete").click(function (e) {
               e.preventDefault();
               $(".actions.delete").dialog({
                  buttons: {
                     "Confermi la cancellazione della categoria del taxa?": function() {
                        $.ajax({
                           url: $(this).attr("href"),
                           async : false
                           });
                        di.dataTable().fnDraw();
                     }
                  }
               });
            });
        }
    });
    $('.editable').editable('?task=dico&action=jeditable&id_dico='+$('#id').val(), {
         "indicator" : "Salvataggio in corso...",
         "tooltip"   : "Click per modificare...",
         "placeholder" : "Clicca per modificare"
    });
});
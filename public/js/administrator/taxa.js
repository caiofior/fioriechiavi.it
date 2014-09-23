$(document).ready(function() {
    ct = $("#taxa").dataTable({
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
            aoData.push({ "name": "task", "value": "taxa" });  
         },
        "aoColumnDefs":  getDatatableMetadata(this),
        "drawCallback": function( ) {
            $(".actions.delete").click(function (e) {
               e.preventDefault();
               $(".actions.delete").dialog({
                  buttons: {
                     "Confermi la cancellazione del taxa?": function() {
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
    $( "#taxa_kind_id_name" ).autocomplete({
      source: "?task=taxa&action=taxakindlist",
      select: function( e, ui ) {
         $("#taxa_kind_id").val(ui.item.value);
         $( "#taxa_kind_id_name" ).val(ui.item.label);
         e.preventDefault();
      },
      change: function (e, ui) {
          if (!ui.item)
              $(this).val("");
      }
    });
});
$(document).ready(function() {
    ct = $("#contentT").dataTable({
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
            aoData.push({ "name": "task", "value": "content" });  
         },
        "aoColumnDefs":  getDatatableMetadata(this),
        "drawCallback": function( ) {
         $('.editable').editable('?task=content&action=jeditable&id='+$('#id').val(), {
               "indicator" : "Salvataggio in corso...",
               "tooltip"   : "Click per modificare...",
               "placeholder" : "Clicca per modificare",
               "submitdata" : function () {
                  aPos = ct.fnGetPosition( this );
                  aData = ct.fnGetData( aPos[0] );
                  return {id:aData[0]};
               }
          });
         $(".actions.delete").click(function (e) {
            e.preventDefault();
            $(this).dialog({
               buttons: {
                  "Confermi la cancellazione del contenuto?": function() {
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
      force_br_newlines : false,
      force_p_newlines : false,
      forced_root_block : "",
      entity_encoding : "raw",  
      plugins: "link",
      selector: "textarea"
    });
});
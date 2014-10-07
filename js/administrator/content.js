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
        }
    });
    tinymce.init({
       plugins: "link",
      selector: "textarea"
    });
});
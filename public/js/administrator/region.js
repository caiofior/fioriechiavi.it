$(document).ready(function() {
    rg = $("#region").dataTable({
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
            aoData.push({ "name": "task", "value": "region" });  
         },
        "aoColumnDefs":  getDatatableMetadata(this),
        "drawCallback": function( ) {
            $(".actions.delete").click(function (e) {
               e.preventDefault();
               $(".actions.delete").dialog({
                  buttons: {
                     "Confermi la cancellazione della regione?": function() {
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
         $('.editable').editable('?task=region&action=jeditable&id='+$('#id').val(), {
               "indicator" : "Salvataggio in corso...",
               "tooltip"   : "Click per modificare...",
               "placeholder" : "Clicca per modificare",
               "submitdata" : function () {
                  aPos = rg.fnGetPosition( this );
                  aData = rg.fnGetData( aPos[0] );
                  return {id:aData[0]};
               }
          });
        }
    });
    $("#addRegion").click(function (e) {
       $("#addRegionForm").show();
       e.preventDefault();
    });

});
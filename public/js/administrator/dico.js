$(document).ready(function() {
    di = $("#dico").dataTable({
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
            aoData.push({ "name": "task", "value": "dico" });  
         },
        "aoColumnDefs":  getDatatableMetadata(this),
        "drawCallback": function( ) {
            $(".actions.delete").click(function (e) {
               e.preventDefault();
               $(".actions.delete").dialog({
                  buttons: {
                     "Confermi la cancellazione della chiave dicotomica?": function() {
                        $.ajax({
                           url: $(this).attr("href"),
                           async : false
                           });
                        di.dataTable().fnDraw();
                        $( this ).dialog( "close" );
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
    $(".addTaxaButton").click(function() {
      $(this).hide().siblings(".addTaxa").show();
    });
    $(".restoreTaxaButton").click(function () {
       $(this).parent("form").hide().siblings(".addTaxaButton").show();      
    });
    $("input:submit").unbind("click");
   $( ".taxaName" ).autocomplete({
    source: "?task=dico&action=taxalist"
   });
});
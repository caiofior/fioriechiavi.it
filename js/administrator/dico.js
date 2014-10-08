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
               $(this).dialog({
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
         "onblur": "submit",
         "indicator" : "Salvataggio in corso...",
         "tooltip"   : "Click per modificare...",
         "placeholder" : "Clicca per modificare"
    });
    $(".addTaxaButton").click(function(e) {
      $(this).hide().siblings(".addTaxa").show();
      e.preventDefault();
    });
    $(".restoreTaxaButton").click(function (e) {
       $(this).parent("form").hide().siblings(".addTaxaButton").show();
       e.preventDefault();
    });
    $("input:submit").unbind("click");
   $( ".taxaName" ).autocomplete({
    source: "?task=dico&action=taxalist",
    select: function( e, ui ) {
         $.ajax({
            url: "?task=dico&action=createtaxaassociation",
            data: {
               "id":$('#id').val(),
               "id_dico":$(this).siblings("input[name='children_dico_item_id']").val(),
               "taxa_id":ui.item.value
            },
            async : false
          });
         window.location.reload();
      }
   });
    $( ".update" ).click(function (e){
       window.location.reload();
       e.preventDefault();
    });
    $(".deleteTaxaItem").click(function (e){
        $(this).dialog({
           buttons: {
              "Confermi la cancellazione della voce?": function() {
              }
           }
        });
    });
    $( ".editDicoItem" ).click(function (e){
        $(this).prev().trigger("click");
       e.preventDefault();
    });
    $(".hideMissing").click (function (e){
       $(".editable.missing").parent("div").toggle();
       e.preventDefault();
    });
    $(".downloadButton").click (function (e){
       $("form.downloadForm").toggle();
       e.preventDefault();
    });
});
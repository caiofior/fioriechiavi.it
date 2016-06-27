$(document).ready(function() {
    ct = $("#categoryTaxa").dataTable({
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
            aoData.push({ "name": "task", "value": "taxa_category" });  
         },
        "aoColumnDefs":  getDatatableMetadata(this),
        "drawCallback": function( ) {
            $(".actions.delete").click(function (e) {
               e.preventDefault();
               $(this).dialog({
                  buttons: {
                     "Confermi la cancellazione del raggruppamento sistematico?": function() {
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
      selector: "textarea"
    });
    $("#sortCategoryTaxaList" ).sortable({
       change: function( event, ui ) {
          $("#saved").hide();
          $("#saveCategoryTaxaOrder").show();
       }
    });
    $("#saveCategoryTaxaOrder").click(function (e) {
      e.preventDefault();
      $.ajax({
         url: $(this).attr("href"),
         type:"POST",
         data: $.extend({},$("#sortCategoryTaxaList" ).sortable("toArray")),
         async : false,
         success : function (data, textStatus, jqXHR ) {
            $("#saved").show();
            $("#saveCategoryTaxaOrder").hide();
            window.location.reload();
         }  
      });
   });
});
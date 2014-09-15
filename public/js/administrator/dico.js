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
      $("#addDico").click(function (e) {
      e.preventDefault();
        $("#addDico").dialog({
           buttons: {
              "Confermi la creazione della chiave radice?": function() {
                 $.ajax({
                    url: $(this).attr("href"),
                    async : false
                    });
                 di.dataTable().fnDraw();
              }
           }
        });
     });
    tinymce.init({
      selector: "textarea"
    });
    $("#sortCategoryTaxaList" ).sortable();
    $("#saveCategoryTaxaOrder").click(function (e) {
       e.preventDefault();
       $.ajax({
        url: $(this).attr("href"),
        type:"POST",
        data: $.extend({},$("#sortCategoryTaxaList" ).sortable("toArray")),
        async : false
       });
    });
});
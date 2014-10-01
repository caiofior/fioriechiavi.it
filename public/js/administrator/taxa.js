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
               $(this).dialog({
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
    $("#attribute_name, #attribute_value").change(function (e){
       $("#attribute_error").hide();
       if ($("#attribute_name").val() != "" && $("#attribute_value").val() != "") {
          $.ajax({
            url: $("form").attr("href"),
            data: {
               "attribute_name":$("#attribute_name").val(),
               "attribute_value":$("#attribute_value").val()
            },
            async : false
          });
          updateAttributes();
          $("#attribute_name").val("");
          $("#attribute_value").val("");
       } else {
          $("#attribute_error").show();
       }
       
    }); 

   $( "#attribute_name" ).autocomplete({
      source: "?task=taxa&action=taxaattributelist&exclude_taxa_id="+$("#id").val()
   });
   function updateAttributes() {
      $("#attribute_list").load("?task=taxa&action=reloadattribute&id="+$("#id").val());
      deleteAttribute ();
   }
   function deleteAttribute () {
      $(".attribute.actions.delete").click(function (e) {
      e.preventDefault();
      $(this).dialog({
         buttons: {
            "Confermi la cancellazione dell'attributo?": function() {
               $.ajax({
                  url: $(this).attr("href"),
                  async : false
               });
               updateAttributes()
               $( this ).dialog( "close" );
            }
         }
      });
      });
      $('.editable').editable('?task=taxa&action=jeditable&taxa_id='+$('#id').val(), {
         "indicator" : "Salvataggio in corso...",
         "tooltip"   : "Click per modificare...",
         "placeholder" : "Clicca per modificare"
      });
   }
   deleteAttribute ();
});
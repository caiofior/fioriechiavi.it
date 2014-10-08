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
      $('.editable').editable("?task=taxa&action=jeditable&taxa_id="+$("#id").val(), {
         "indicator" : "Salvataggio in corso...",
         "tooltip"   : "Click per modificare...",
         "placeholder" : "Clicca per modificare"
      });
   }
   deleteAttribute ();
   up = $("#uploader").plupload({
        // General settings
        runtimes : 'html5,flash,silverlight,html4',
        url : "administrator.php?task=taxa&action=imageupload&taxa_id="+$("#id").val(),
 
        // Maximum file size
        max_file_size : '2mb',
 
        chunk_size: '1mb',
 
        // Specify what files to browse for
        filters : [
            {title : "Image files", extensions : "jpg,gif,png"}
        ],
        
        init : {
            FilesAdded: function(up, files) {
              up.start();
            },
            UploadComplete: function(up, files) {
               updateImages();
                up.splice();
            },
          },
 
        // Rename files by clicking on their titles
        rename: true,
         
        // Sort files
        sortable: true,
 
        // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
        dragdrop: true,
 
        // Views to activate
        views: {
            list: true,
            thumbs: true, // Show thumbs
            active: 'thumbs'
        },
 
        // Flash settings
        flash_swf_url : 'js/plupload/js/Moxie.swf',
     
        // Silverlight settings
        silverlight_xap_url : 'js/plupload/js/Moxie.xap'
    });
    function updateImages() {
      $("#image_list").load("?task=taxa&action=reloadimage&id="+$("#id").val());
      deleteImage ();
    }
    function deleteImage () {
      $(".image.actions.delete").click(function (e) {
      e.preventDefault();
      $(this).dialog({
         buttons: {
            "Confermi la cancellazione dell'immagine?": function() {
               $.ajax({
                  url: $(this).attr("href"),
                  async : false
               });
               updateImages()
               $( this ).dialog( "close" );
            }
         }
      });
      });
   }
   deleteImage ();
});
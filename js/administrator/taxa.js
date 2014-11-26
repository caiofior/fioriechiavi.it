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
         plugins: ["table","code"],
         tools: ["inserttable","code"],
         selector: "textarea:not(.notEditable)"
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
    $("a.loadMarkup").click(function (e){
       $("div.mce-tinymce").hide();
       $("#description_markup_container").show();
    });
    $("a.confirmLoad").click(function (e){
         $.ajax({
            url: "#",
            data: {
               "task":"taxa",
               "action":"parse_markup",
               "description_markup":$("#description_markup").val()
            },
            success : function (data, textStatus, jqXHR ) {
               tinyMCE.get("description").setContent(data);
               $("a.cancelLoad").click();
            }    
         });
    });
    $("a.cancelLoad").click(function (e){
         $("div.mce-tinymce").show();
         $("#description_markup_container").hide();
    });
    $("a.selectAllRegions").click(function (e) {
       if($("#regions option:selected").length == 0) {
          $("#regions option").prop("selected",true);
       } else {
          $("#regions option").prop("selected",false);
       }
       e.preventDefault();
    });
    $("#attribute_name, #attribute_value").change(function (e){
       $("#attribute_error").hide();
       if ($("#attribute_name").val() != "" && $("#attribute_value").val() != "") {
          el = $("#attribute_template div").clone(true);
          el.find("[name='attribute_name_list[]']").val($("#attribute_name").val());
          el.find("[name='attribute_value_list[]']").val($("#attribute_value").val());
          el.show();
          $("#attribute_list").append(el);
          $("#attribute_name").val("");
          $("#attribute_value").val("");
          $("#attribute_name").focus();
       } else {
          $("#attribute_error").show();
       }
       
    }); 
   $( "#attribute_name" ).autocomplete({
      source: function (request, response) {
         $.getJSON("#", {
            task: "taxa",
            action:"taxaattributelist",
            term:$( "#attribute_name" ).val(),
            attribute_name_list: $( "#attribute_name" ).parents("form").find("#attribute_list input[name=attribute_name_list\\[\\]]").serialize(),

         }, 
         response);
      }
   });
   $(".attribute.actions.delete").click(function (e) {
      el =  $(this).parent("div.attContainer");
      $(this).dialog({
         buttons: {
            "Confermi la cancellazione dell' attributo?": function() {
               $(this).dialog('destroy');
               el.remove();
            }
         }
      });
      e.preventDefault();
   });
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
                $.each(files,function (id,value) {
                  el = $("#image_template div").clone(true);
                  el.find("img").attr("src","tmp/"+value["name"]);
                  el.find("[name='image_name_list[]']").val(value["name"]);
                  el.show();
                  $("#image_list").append(el);
                });
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
    function deleteImage () {
      $(".image.actions.delete").click(function (e) {
      el =  $(this).parent("div.imgContainer");
      e.preventDefault();
      $(this).dialog({
         buttons: {
            "Confermi la cancellazione dell'immagine?": function() {
               $(this).dialog('destroy');
               el.remove();
            }
         }
      });
      });
   }
   deleteImage ();
});
$(document).ready(function() {
    $.fn.dataTable.ext.errMode = 'none';
    ct = $("#dictionary").dataTable({
        "oLanguage":  {
                "sUrl": "js/common/datatables/lang/it.json"
         },
        "bStateSave" : true,
        "aaSorting": [[ 0, "desc" ]],
        "bJQueryUI": true,
        "bProcessing": true,
        "bServerSide": true,
        "sAjaxSource": "#",
         "fnServerParams": function ( aoData ) {
            aoData.push({ "name": "task", "value": "dictionary" });  
         },
        "aoColumnDefs":  getDatatableMetadata(this),
        "drawCallback": function( ) {
            $(".actions.delete").click(function (e) {
               e.preventDefault();
               $(this).dialog({
                  buttons: {
                     "Confermi la cancellazione del termine?": function() {
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
            $("a.blank").attr('target','_blank');
        }
    });
    tinymce.init({
        //language : 'it', 
        //language_url : '/languages/it.js',
        force_br_newlines : false,
        force_p_newlines : false,
        forced_root_block : "",
        entity_encoding : "raw",
        plugins: ["table","code"],
        tools: ["inserttable","code"],
        selector: "textarea:not(.notEditable)",
        setup: function(editor) {
            editor.on('change', function(e) {
                $("#"+e.target.id).trigger("change");
            });
    }

    });
   
   up = $("#uploader").plupload({
        // General settings
        runtimes : 'html5,flash,silverlight,html4',
        url : "administrator.php?task=taxa&action=imageupload&taxa_id="+$("#id").val(),
 
        // Maximum file size
        max_file_size : '500kb',
 
        chunk_size: '10kb',
	max_retries: 10,
 
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
                  el.find("[name='image_name_list[]']").val(value["name"]).trigger("change");
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
   $(".reindexAJAX").click(function (e){
        $(".reindexAjaxLoader").show();
        $.get(
                $(this).attr("href"),
                function(data){
                    $(".reindexAjaxLoader").hide();
                }
              );
        e.preventDefault();
    });
});
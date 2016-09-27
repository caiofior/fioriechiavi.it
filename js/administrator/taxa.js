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
    $(".deleteAddDico").click(function (e){
        e.preventDefault();
        $(this).dialog({
           buttons: {
              "Confermi la cancellazione della chiave aggiuntiva ?": function() {
                   window.location =  $(this).attr("href");
              }
           }
        });
        
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
    $("a.add_dico_button").click(function(e) {
       $("#add_add_dico").toggle();
       e.preventDefault(); 
    });
    $("#attribute_name, #attribute_value").change(function (e){
       $("#attribute_missing_name").hide();
       $("#attribute_error").hide();
       if ($("#attribute_name").val() != "" && $("#attribute_value").val() != "") {
          el = $("#attribute_template div").clone(false);
          el.find("[name='attribute_name_list[]']").val($("#attribute_name").val());
          el.find("[name='attribute_value_list[]']").val($("#attribute_value").val());
          el.show();
          $("#attribute_list").append(el);
          addAutocomplete(el.find("[name='attribute_value_list[]']"));
          $("#attribute_name").val("");
          $("#attribute_value").val("");
          $("#attribute_name").focus();
       } else {
          $("#attribute_error").show();
       }
       
    }); 
   $( "#attribute_name" ).autocomplete({
      source: function (request, response) {
         $("#attribute_missing_name").hide();
         $.getJSON("#", {
            task: "taxa",
            action:"taxaattributelist",
            term:$( "#attribute_name" ).val(),
            attribute_name_list: $( "#attribute_name" ).parents("form").find("#attribute_list input[name=attribute_name_list\\[\\]]").serialize(),

         }, 
         response);
      }
   });
   $( "#attribute_value" ).autocomplete({
      source: function (request, response) {
         $("#attribute_missing_name").hide();
         $.getJSON("#", {
            task: "taxa",
            action:"taxaattributelistvalue",
            name:$( "#attribute_name" ).val(),
            term:$( "#attribute_value" ).val()
         }, 
         response);
      },
      search: function( event, ui ) {
         if ($( "#attribute_name" ).val() == "") {
            $("#attribute_missing_name").show();
            event.preventDefault();
         } else {
             
            $("#attribute_missing_name").hide();   
         }
      },
      change: function( event, ui ) {
         $("#attribute_value").trigger("change");
      }
    });
    function addAutocomplete (el) {
        
    try{
     el.autocomplete( "destroy" );
    } catch (e) {}
    el.autocomplete({
      source: function (request, response) {
         $.getJSON("#", {
            task: "taxa",
            action:"taxaattributelistvalue",
            name:$(this.element).parents("span").siblings(".prevAttibuteName").val(),
            term:$(this.element).val()
         }, 
         response);
      }
    });
    $(".attribute.actions.delete").unbind("click").click(function (e) {
      el =  $(this).closest("div.attContainer");
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
   }
   
   addAutocomplete($(".prevAttibuteValue"));
   
   
   $("input, textarea, select").change(function (e){
      $(".saved").hide();
      $(".tosave").show();
   });
   $(".update_col_id").click(function(e){
      $(this).siblings(".ajaxLoader").show();
      $("#col_id_list").load("?task=taxa&action=get_col_id_list&taxa_name="+$("#name").val().replace(/ /g, '+'),function(e){
          $("a.blank").attr('target','_blank');
          $("#col_id_list").show();
          $(".selected_col_id").click(function(e){
              $("#col_id_list").hide();
              $("#col_id").val($(this).text());
              e.preventDefault();
          });
          $(this).siblings(".ajaxLoader").hide();
      }); 
      e.preventDefault();
   });
   $(".update_eol_id").click(function(e){
      $(this).siblings(".ajaxLoader").show();
      $("#eol_id_list").load("?task=taxa&action=get_eol_id_list&taxa_name="+$("#name").val().replace(/ /g, '+'),function(e){
          $("a.blank").attr('target','_blank');
          $("#eol_id_list").show();
          $(".selected_eol_id").click(function(e){
              $("#eol_id_list").hide();
              $("#eol_id").val($(this).text());
              e.preventDefault();
          });
          $(this).siblings(".ajaxLoader").hide();
      }); 
      e.preventDefault();
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
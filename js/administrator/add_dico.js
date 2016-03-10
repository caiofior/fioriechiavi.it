$(document).ready(function() {
    $('.editable').editable('?task=add_dico&action=jeditable&id_dico='+$('#id').val(), {
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
    source: "?task=add_dico&action=taxalist",
    select: function( e, ui ) {
         if (e.ctrlKey == true) {
             $(this).val(ui.item.label);
             e.preventDefault();
         } else {
            $.ajax({
               url: "?task=add_dico&action=createtaxaassociation",
               data: {
                  "id":$('#id').val(),
                  "id_dico":$(this).siblings("input[name='children_add_dico_item_id']").val(),
                  "taxa_id":ui.item.value
               },
               async : false
             });
            window.location.reload();
         }
      }
   });
    $( ".update" ).click(function (e){
       window.location.reload();
       e.preventDefault();
    });
    $(".deleteTaxaAssociation").click(function (e){
        e.preventDefault();
        $(this).dialog({
           buttons: {
              "Confermi la cancellazione dell'associazione con il taxa?": function() {
                   window.location =  $(this).attr("href");
              }
           }
        });
        
    });
    $(".deleteButton").click(function (e){
        e.preventDefault();
        $(this).dialog({
           buttons: {
              "Confermi la cancellazione di tutte le voci della chiave?": function() {
                   window.location =  $(this).attr("href");
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
    $(".uploadButton").click (function (e){
       $("form.uploadForm").toggle();
       e.preventDefault();
    });
    up = new plupload.Uploader({
        browse_button: 'pickfiles',
        container: $("#uploader")[0],
        // General settings
        runtimes : 'html5,flash,silverlight,html4',
        url : "administrator.php?task=add_dico&action=upload&taxa_id="+$("#id").val(),
        multi_selection : false,
        chunk_size: '1mb',
 
        // Specify what files to browse for
        filters : [
            {title : "CSV files", extensions : "txt,csv"}
        ],
        
        init : {
            FilesAdded: function(up, files) {
              $("#uploadFormatChoose").hide();
              if ($("#upload_format").val() =="") {
                 $("#uploadFormatChoose").show();
              } else {
                 up.start();
              }
            },
            UploadComplete: function(up, files) {
              $("#filename").val(files[0]['name']);
              $("form.uploadForm").submit();
            },
          },
 
        // Rename files by clicking on their titles
        rename: false,
         
        // Sort files
        sortable: false,
 
        // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
        dragdrop: false,
 
        // Views to activate
        views: {
            list: true,
            thumbs: false, // Show thumbs
            active: 'list'
        },
 
        // Flash settings
        flash_swf_url : 'js/plupload/js/Moxie.swf',
     
        // Silverlight settings
        silverlight_xap_url : 'js/plupload/js/Moxie.xap'
    });
    up.init();
});
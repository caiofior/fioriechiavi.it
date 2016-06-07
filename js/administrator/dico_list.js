$(document).ready(function() {
    $(".addDico").click(function(e) {
      $("#dico_error").hide();
      e.preventDefault();
      if ($(".addText").val() == "") {
          $("#dico_error").show();          
      } else {
          $(this).parent("form").trigger("submit");
      }
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
         if (e.ctrlKey == true) {
             $(this).val(ui.item.label);
             e.preventDefault();
         } else {
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
    $(".uploadImage").each(function (id,button) {
        new plupload.Uploader({
        browse_button: button,
        // General settings
        runtimes : 'html5,flash,silverlight,html4',
        url : "administrator.php?task=dico&action=uploadImage",
        multi_selection : false,
        chunk_size: '1mb',
        // Specify what files to browse for
        filters : [
            {title : "Immagini", extensions : "jpg,gif,png"}
        ],
        init : {
            FilesAdded: function(up, files) {
                 up.start();
            },
            UploadComplete: function(up, files) {
                $(button).siblings("input").val(files[0]['name']);
                $(button).append("<img src='tmp/"+files[0]['name']+"'>");
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
            list: false,
            thumbs: false, // Show thumbs
            active: 'list'
        },
 
        // Flash settings
        flash_swf_url : 'js/plupload/js/Moxie.swf',
     
        // Silverlight settings
        silverlight_xap_url : 'js/plupload/js/Moxie.xap'
    }).init();
    });
});
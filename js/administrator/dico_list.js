$(document).ready(function() {
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
            $(this).siblings("input[name=taxa_id\\[\\]]").val(ui.item.value);
            $("input[name=submit]").click();
         }
      }
   });
    $(".deleteTaxaAssociation").click(function (e){
        $(this).siblings("input[name=taxa_id\\[\\]]").val("");
        $("input[name=submit]").click();
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
    $(".createTaxa").click(function(e) {
      window.location = $(this).data("url")+"&name="+$(this).siblings("input[name=name\\[\\]]").val();
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
                $(button).siblings("input[name=photo_name\\[\\]], input[name=addPhotoId]").val(files[0]['name']);
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
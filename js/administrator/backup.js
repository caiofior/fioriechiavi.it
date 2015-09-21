    $(".resetTaxa").click(function (e){
        e.preventDefault();
        $(this).dialog({
           buttons: {
              "Confermi la cancellazione di tutti i taxa?": function() {
                   window.location =  $(this).attr("href");
              }
           }
        });
        
    });
    $(".resetUtenti").click(function (e){
        e.preventDefault();
        $(this).dialog({
           buttons: {
              "Confermi la cancellazione di tutti gli utenti?": function() {
                   window.location =  $(this).attr("href");
              }
           }
        });
        
    });
    $(".reindexAJAX").click(function (e){
        $(".reindexAjaxLoader").show();
        $.get(
                $(this).attr("href"),
                function(data){
                    $("#reindexLog").html(data);
                    $(".reindexAjaxLoader").hide();
                }
              );
        e.preventDefault();
    });
up = new plupload.Uploader({
        browse_button: 'pickfiles',
        container: $("#uploader")[0],
        // General settings
        runtimes : 'html5,flash,silverlight,html4',
        url : "administrator.php?task=backup&action=restore",
        multi_selection : false,
        // Maximum file size
        max_file_size : '2mb',
 
        chunk_size: '1mb',
 
        // Specify what files to browse for
        filters : [
            {title : "TXT files", extensions : "txt,csv,sql"}
        ],
        
        init : {
            FilesAdded: function(up, files) {
                up.start();
            }
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
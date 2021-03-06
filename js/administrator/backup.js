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
        $.ajax({
                url : $(this).attr("href"),
                success: function(data, textStatus, jqXHR ){
                    $("#reindexLog").html(data);
                    $(".reindexAjaxLoader").hide();
                },
                error : function( jqXHR,textStatus,errorThrown) {
                    $("#reindexLog").html("Errore "+textStatus);
                    $(".reindexAjaxLoader").hide();
                }
            });
        e.preventDefault();
    });
up = new plupload.Uploader({
        browse_button: 'pickfiles',
        container: $("#uploader")[0],
        // General settings
        runtimes : 'html5,flash,silverlight,html4',
        url : "administrator.php?task=backup&action=restore",
        multi_selection : false,
        chunk_size: '1mb',
        init : {
            FilesAdded: function(up, files) {
                $(".restoreAjaxLoader").show();
                up.start();
            },
            UploadComplete: function(up, files) {
                $(".restoreAjaxLoader").hide();
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
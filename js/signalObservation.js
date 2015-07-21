if(geoPosition.init()){  // Geolocation Initialisation
        geoPosition.getCurrentPosition(success_callback,error_callback,{enableHighAccuracy:true});
}else{
                // You cannot use Geolocation in this device
}
geoPositionSimulator.init(); 
function success_callback(p){
    $(".errorMessage").hide();
    $("#latitude").val(p.coords.latitude);
    $("#longitude").val(p.coords.longitude);
}
function error_callback(p){
        // p.message : error message
}
$(document).ready(function() {
    $(".moreImage").click(function(e) {
        el = $(".imageContainer input:first").clone(false);
        el.val("");
        $(".imageContainer").append(el);
        e.preventDefault();
    });
    $("input:submit").unbind("click").click(function (e) {
       $(".notValid").removeClass("notValid");
       $(".errorMessage").remove();
       valid = true;
       form = $(this).parents("form").first();
       if ($("#title").val() == "") {
            $("#title").addClass("notValid").focus();
            $(form).find("input:submit").after("<div class=\"errorMessage\"><span>Il titolo è obbligatorio</span></div>");    
            valid = false;
       }
       if ($("#description").val() == "") {
            $("#description").addClass("notValid").focus();
            $(form).find("input:submit").after("<div class=\"errorMessage\"><span>La descrizione è obbligatoria</span></div>");    
            valid = false;
       }
       if ($("#latitude").val() == "" || $("#longitude").val() == "") {
            $(form).find("input:submit").after("<div class=\"errorMessage\"><span>La posizione è obbligatoria</span></div>");
            valid = false;
       }
       nFiles = 0;
       $("input:file").each(function (id,input) {
           if ($(input).val() != "")
               nFiles++;
       });
       if (nFiles <1) {
            $(form).find("input:submit").after("<div class=\"errorMessage\"><span>Caricare almeno una foto</span></div>");
            valid = false;
       }
       if (valid == false) {
           e.preventDefault();
       }
    });
});
  (function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/it_IT/all.js#xfbml=1&appId="+appId;
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
  window.fbAsyncInit = function() {
    FB.init({
      appId      : appId,
      cookie     : true,
      xfbml      : true,
      version    : 'v2.2'
    });
  }
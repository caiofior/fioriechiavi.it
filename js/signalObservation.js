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
});
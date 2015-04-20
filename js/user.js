  function statusChangeCallback(response) {
    $("#fbstatus").empty();
    $.ajax($("form").first().attr("action")+"?task=facebook&action=login", {
        async:false,
        cache: true,
        method:"post",
	dataType:"json",
        data:response,
        success: function (data) {
            if (data.status == true) {
                url = document.referrer;
                if (url != '') {
                    window.location.href = url;
                }
                location.reload(true);
                
            } else {
                $("#fbstatus").html("<div class=\"validMessage\"><span>"+data.message+"</span></div>");
            }
        },
        error: function (jqXHR, textStatus,errorThrown) {
            console.error(textStatus+" "+errorThrown);
         }
    });  
  }
  function checkLoginState() {
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
  }
  $("#subscribe_form, #lost_password_form").hide();
  $("#lost_password_link").click(function(e) {$("#lost_password_form").toggle();e.preventDefault();});
  $("#subscribe_link").click(function(e) {$("#subscribe_form").toggle();e.preventDefault();});

  function statusChangeCallback(response) {
    $("#fbstatus").empty();
    $.ajax($("form").first().attr("action")+"?task=facebook&action=login", {
        async:false,
        cache: true,
        method:"post",
        data:response,
        success: function (data) {
            if (data.status == true) {
                location.reeload(true);
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
  FB.getLoginStatus(function(response) {
    statusChangeCallback(response);
  });


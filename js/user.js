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
  $("input:submit[name='login']").unbind("click").click(function (e) {
   $("#ajaxLoader").show();
   $(".notValid").removeClass("notValid");
   $(".errorMessage").remove();
   status = false;
   form = $(this).parents("form").first();
   data = form.serializeArray();
   data.push({"name":"xhrValidate" ,"value": "1"});
   request = $.ajax({
      url: form.attr("action"),
      type: "POST",
      data: data,
      async: false,
      dataType: "json",
      success: function (data) {
          objectData = Object.keys(data);
          if (objectData.length == 1 && objectData[0] == "validMessage") {
             message = data.validMessage;
             if (message == true)
                status = true;
             else {
               $(form).find("input:submit").after("<div class=\"validMessage\"><span>"+data.validMessage+"</span></div>");
            }
          }
          else {
            $.each(data, function (elementId, content) {
               try {
                  el = $("#"+elementId);
               } catch (err) {}
               if (el.length > 0 ) {
                  el.addClass("notValid").focus().after("<div class=\"errorMessage\"><span>"+content+"</span></div>");
               }
            });
          }
          $("#ajaxLoader").hide();
      },
      error: function (jqXHR, textStatus,errorThrown) {
         $("#ajaxLoader").hide();
         console.error(textStatus+" "+errorThrown);
      }
   });
   if (status == "true" ) {
       url = $("#return_url").val();
       if (typeof url != 'undefined' && url != '') {
           window.location.href = url;
       } else {
           window.location.reload(true);
       }
   }
   e.preventDefault();
   
  });
  $("#subscribe_form, #lost_password_form").hide();
  $("#lost_password_link").click(function(e) {$("#lost_password_form").toggle();e.preventDefault();});
  $("#subscribe_link").click(function(e) {$("#subscribe_form").toggle();e.preventDefault();});
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
      version    : 'v2.9'
    });
  }
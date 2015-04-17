$("#searchContent").on("click","#paginationContainer a.pageSelector",function(e){
    $("#start").val($(this).data("page")).trigger("change",{dontReset:true});
    e.preventDefault();
});
$("#searchForm input").on("change keyup",function (e,data) {
    if (
            typeof data !== 'object' ||
            !data.hasOwnProperty("dontReset") || 
            data.dontReset !== true
            ) {
        $("#start").val(0);
    }
    $.ajax({
        url: $(this).parents("form").first().attr("action")+"?action=search",
        data: $(this).parents("form").first().serializeArray(),
        method:"post",
        async : false,
        success : function (data) {
            $("#searchContent").html(data);
        }
    });  
});
jQuery(document).ready(function() {
    $ = jQuery;
    console.log("sd");
    
    fillSelects();

    $(".add-more").on("click", function(){
        var template = $("#template").clone();
        template.removeAttr("id");

        $("#link-container").append(template);
    })
    $(document).on("click", ".delete-item",  function(e){
        console.log("delete");
        
        e.preventDefault();
        var form = $(this).parents("form");
        var id = form.attr("data-record-id");
        if(id){
            var data = {
                action: 'delete',
                id: id
            }

            $.ajax({
                method: "post",
                url: ajaxurl,
                data: data,
                dataType: "json",
                beforeSend: function( xhr ) {
                }
              })
            .done(function( data ) {
                if(data.status == 'ok') {
                    console.log('OOOOOOOOOOOooo');
                    
                    form.parents(".link-block").parent().remove();
                }
                
                
                
                
            });
        }else{
            form.parents(".link-block").parent().remove();
        }
    })
    $(document).on("submit", "form",  function(e){
        e.preventDefault()
        var form = $(this);
        var href = $(this).find("[name='link-href']").val();
        var postId = $(this).find("select[name=post_id]").val();
        console.log(href, "href");
        console.log(postId, "post)id");
        var data = {
            action: 'add',
            href: href,
            post_id: postId,
        }
        if($(this).attr("data-record-id")){
            data["record-id"] = $(this).attr("data-record-id");
        }

        $.ajax({
            method: "post",
            url: ajaxurl,
            data: data,
            dataType: 'json',
            beforeSend: function( xhr ) {
            }
          })
            .done(function( data ) {
                if(parseInt(data.record_id) > 0){
                    form.attr("data-record-id", data.record_id);
                }
               
                form.find("select").attr("data-post", postId);
                if(data.status == 'ok'){
                    alert("Done");
                }else{
                    alert("Error");
                }
                
            });
        
    })
    function fillSelects(){
        $.each($('.link-block'), function(){
            if($(this).parents("#template").length == 0){
                var select = $(this).find("select");
                var pId = select.attr("data-post");
                select.val(pId);
            }
        })
    }
})



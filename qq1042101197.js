function set_select_tree_ajax(current_box,next_box,ajax_url){
    current_box.children('select').on('change',function(){
        var jq=$(this),
            post={
                'father':jq.val(),
                'key':jq.attr('name')
            };
        $.ajax({
            url : ajax_url,
            type : "POST",
            data:post,
            success : function(re) {
                if(re==0){
                    alert('获取列表失败');
                }else{
                    current_box.nextAll('.select_box').children('select').html("<option value=''>请选择</option>");
                    
                    var result=eval("("+re+")"),list=result['list'],info=result['info'];
                    var jq_sel=next_box.children('select');
                    var jq_span=next_box.children('span').text('选择'+info['tag']);
                    for(var pr in list){
                        jq_sel.append("<option value='"+pr+"'>"+list[pr]+"</option>");
                    }
                }
            }
        });
    });
}
function init_input_file(img_dialog){
    if(img_dialog!==false){
        if(typeof(img_dialog)=='undefined'){
            img_dialog=$(window.top.document.getElementById('view_org'));
        }
        $('img.prev').parent().each(function(){
            $(this).delegate('img.prev','click',function(){
                img_dialog.show().find('img').attr('src',$(this).attr('src'));
                img_dialog.css({"top":($(window.top).height()-img_dialog.height())/2,"left":($(window.top).width()-img_dialog.width())/2});
            });
        });
    }
	$(':file').each(function(){
		var ifile=this;
		$("<input type='button' value='浏览…' class='choose_file' />").click(function(){
			$(ifile).click();
		}).insertBefore($(this));
		$("<input type='button' class='cancel_upload' value='取消' />").hide().click(function(){
			$(this).hide().siblings(':file').nextAll('img').remove();
		}).insertAfter($(this));
		$(this).change(function(){
			$(this).nextAll('img').remove();
			$.each(this.files,function(i,v){
                $('<img class="prev"/>').attr("src",window.URL.createObjectURL(v)).insertAfter($(ifile));
			});
			$(this).siblings('.cancel_upload').show();
		});
	});

}
function btn_start_count(btn,tag,timer,time){
	if(tag){
		clearInterval(timer);
		if(typeof(time)=='undefined'){time=3;}
		if(btn.tagName.toLowerCase()=='input'){
			
		}else{
			var text_span=btn.children('span');
			text_span.text("("+time+")");
			timer=setInterval(function(){
				if(time<=0){
					clearInterval(timer);
					text_span.text('');
					gcd_btns.removeAttr('disabled');
					time=5;
					return false;
				}else{
					text_span.text("("+(--time)+")");
				}
			},1000);
		}
		
		
	}
}

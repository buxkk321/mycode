/**
 * Created by Administrator on 14-9-16.
 */
(function( $ ){
    /**
     * 面包屑工具
     * @param options
     * @returns {fn}
     */
    $.fn.ibread=function(options){
    	var ibread=this,
    	settings={
    		'tree':[],
    		'box':function(){},
    		'delimiter':' >> '
    	};
        if ( options ) {
            $.extend( settings, options );
        }
        if(settings['tree'].length>0){
        	ibread.html('');
        	$.each(settings['tree'],function(i,v){
        		settings['box'](i,v).appendTo(ibread);
			});
        }
        return this;
    };
    /**
     * 单个按钮倒计时工具
     * @param options
     * @returns {*|each|Array|each|each|each}
     */
    $.fn.icountbtn=function(options){
        var settings={
            'time':3,
            'speed':1000,
            'beforeAction':function(){

            }
        };
        if ( options ) {
            $.extend( settings, options );
        }
        return this.each(function(){
            var btn=$(this).data('counting',false);
            if(!btn.data('counting')){
                btn.data('counting',true).attr('disable','disable');
                clearInterval(btn.data('timer'));
                settings['beforeAction']();
                var t=settings['time'];
                if(btn.is('input')){
                    var org_text=btn.val();
                    btn.val(org_text +'('+t+')').data(
                        'timer',
                        setInterval(function(){
                            if(t<=0){
                                clearInterval(btn.data('timer'));
                                btn.val(org_text).removeAttr('disabled').data('counting',false);
                                t=settings['time'];
                                return false;
                            }else{
                                btn.val(org_text+'('+(--t)+')');
                            }
                        },settings['speed'])
                    );
                }else{
                    var text_span=$('<span>('+t+')</span>');
                    btn.append(text_span).data(
                        'timer',
                        setInterval(function(){
                            if(t<=0){
                                clearInterval(btn.data('timer'));
                                text_span.remove();
                                btn.removeAttr('disabled').data('counting',false);
                                t=settings['time'];
                                return false;
                            }else{
                                text_span.text("("+(--t)+")");
                            }
                        },settings['speed'])
                    );
                }
            }
        });
    };
    /**
     * 上传文件的表单控件扩展工具
     * @returns {*|each|Array|each|each|each}
     */
	$.fn.ifile=function(){
    	
    	return this.each(function(){
    		var ifile=$(this).hide(),
    		btn_cancel=$("<input type='button' class='cancel_upload' value='取消' />").click(function(){
    			$(this).hide();
    			ifile.nextAll('img').remove();
    		}).hide();
    		
    		$("<input type='button' value='浏览…' class='choose_file' />").click(function(){
    			ifile.click();
    		}).insertBefore(ifile);
    		
    		ifile.change(function(){
    			ifile.nextAll('img').remove();
    			btn_cancel.show();
    			$.each(this.files,function(i,v){
    				$('<img class="prev"/>').attr("src",window.URL.createObjectURL(v)).insertAfter(ifile);
    			});
    		}).after(btn_cancel);
        });
    };
    /**
     * 点击图片弹出原图
     */
    $.fn.img_dialog=function(){
//    	if(typeof(img_dialog)=='undefined'){
//    		img_dialog=$(window.top.document.getElementById('view_org'));
//    	}
//    	this.parent().each(function(){
//    		$(this).delegate('img.prev','click',function(){
//    			img_dialog.show().find('img').attr('src',$(this).attr('src'));
//    			img_dialog.css({"top":($(window.top).height()-img_dialog.height())/2,"left":($(window.top).width()-img_dialog.width())/2});
//    		});
//    	});
    };
})( jQuery );
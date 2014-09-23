/**
 * Created by Administrator on 14-9-16.
 */
(function( $ ){
    /**
     * 滚动菜单
     * @param options
     * @returns {*|each|Array|each|each|each}
     */
    $.fn.slide_menu=function(options){
        var settings={
            'direct':1,/*TODO:上下方向或左右方向的滚动*/
            'container':{},/*当前组菜单所在容器,jq对象*/
            'show_speed':400,/*显示速度*/
            'hide_speed':200,/*隐藏速度*/
            'default_open':null/*默认打开的元素*/
        };

        if ( options ) {
            $.extend( settings, options );
        }

        this.each(function(){
            var menu=$(this).data('is_open',false).click(function(){
                if(!menu.data('is_open')){
                    menu.next().stop(true).slideDown(settings.show_speed);
                }else{
                    menu.next().stop(true).slideUp(settings.hide_speed);
                }
                settings.container.find('a').removeClass('on');
                menu.data('is_open',!menu.data('is_open')).addClass('on');
            });
        });
        if(typeof(settings.default_open)=='number'){
            this.eq(settings.default_open).click();
        }
        return this;
    };
    /**
     * 面包屑工具
     * @param options
     * @returns {fn}
     */
    $.fn.ibread=function(options){
    	var ibread=this,
    	settings={
    		'tree':[],
    		'container':function(){},
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
            var btn=$(this).data('counting',false).click(function(){
                if(!btn.data('counting')){
                    var t=settings.time,org_text,do_count,do_reset,btn_init;
                    if(btn.is('input')){
                        btn_init=function(){
                            org_text=btn.val();
                            btn.val(org_text +'('+t+')');
                        };
                        do_count=function(){
                            btn.val(org_text+'('+(--t)+')');
                        };
                        do_reset=function(){
                            btn.val(org_text);
                        };
                    }else{
                        btn_init=function(){
                            org_text=$('<span>('+t+')</span>');
                            btn.append(org_text);
                        };
                        do_count=function(){
                            org_text.text("("+(--t)+")");
                        };
                        do_reset=function(){
                            org_text.remove();
                        };
                    }

                    btn.data('counting',true).attr('disable','disable');
                    clearInterval(btn.data('timer'));
                    settings.beforeAction();
                    btn_init();
                    btn.data('timer',setInterval(function(){
                            if(t<=0){
                                clearInterval(btn.data('timer'));
                                do_reset();
                                btn.removeAttr('disabled').data('counting',false);
                                t=settings.time;
                                return false;
                            }else{
                                do_count();
                            }
                        },settings.speed)
                    );
                }
            });
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
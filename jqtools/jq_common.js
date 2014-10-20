/**
 * Created by Administrator on 14-9-16.
 */
(function( $ ){
    /**
     * 下拉菜单动画效果
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
     * 生成二级下拉菜单
     * @param options
     */
    $.fn.imenu_second=function(options){
        var settings={
            'data_input':{},
            'site_root':'',
            'class_menu_head':'menu_head l1 f14',/*一级菜单的样式*/
            'class_menu_sub':'l2 f14',/*二级菜单的样式*/
            'target_frame':''
        };

        if ( options ) {
            $.extend( settings, options );
        }

        var a_group=[],container=this;
        $.each(settings.data_input,function(ke,vo){
            vo['url']=!vo['url']?'javascript:void(0);':settings.site_root+vo['url'];
            if(Number(vo['type'])==2){/*目录类型菜单直接加到容器中*/
                $('<li></li>').append(
                    $('<a></a>')
                    .attr({'href':vo['url'],'title':vo['tip'],'target':settings.target_frame})
                    .addClass(settings.class_menu_head+' menu_'+vo['id'])
                    .text(vo['title'])
                    .add('<dl></dl>')
                ).appendTo(container);
            }else{
                a_group.push(ke);
            }
        });
        var len=a_group.length;
        for (var i=0 ; i < len ; i++ ) {
            var data=settings.data_input[a_group[i]],
                obj_dl=$('a.menu_'+data['pid']).next('dl'),
                obj_a=$('<a></a>')
                    .attr({'href':data['url'],'title':data['tip'],'target':settings.target_frame})
                    .addClass(settings.class_menu_sub+' menu_'+data['id'])
                    .text(data['title']);
            if(obj_dl.length>0){
                $('<dd></dd>').append(obj_a).appendTo(obj_dl);
            }else{
                /*如果一个在容器中的menu既不是目录菜单,pid也没有对应的菜单元素,则直接追加到尾部*/
                $('<li></li>').append(obj_a).appendTo(container);
            }
        }
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
            'beforeAction':function(){/*在开始计时前的可选自定义函数*/

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
	$.fn.ifile=function(options){
	   var settings={
	            'img_class':'prev'/*预览图的class*/
	            }
	        
	        if ( options ) {
	            $.extend( settings, options );
	        }
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
    				$('<img class="'+settings.img_class+'"/>').attr("src",window.URL.createObjectURL(v)).insertAfter(ifile);
    			});
    		}).after(btn_cancel);
        });
    };
})( jQuery );
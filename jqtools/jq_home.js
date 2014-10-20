/**
 * Created by Administrator on 14-9-16.
 */
(function( $ ){
    /**
     * 图片轮播工具,支持手机触屏,该方法应用于存放滑动中的元素的容器对象
     * @param options
     * @returns {fn}
     */
    $.fn.slideBox = function(options) {
        var jq=this,
            settings={
            first_run:true,
            mouse_drag:false,/*是否允许鼠标拖拽*/
            inertial:0,/*惯性*/
            auto_vector:true,/*是否自动判断方向*/
            start_index:0,
            box_width:1920,
            once_time:2000,
            interval:4000,
            onBefore:function(){
            },
            onAfter:function(){

            },
            click_group:{},/*页脚或其他触发banner滚动的jq对象集合*/
            move_group:{}/*banner元素jq对象集合*/
        };
        if ( options ) {
            $.extend( settings, options );
        }
        jq.index=jq.old=settings.start_index;
        jq.append(settings.move_group.eq(0).clone());/*添加起始位置的滑块*/
        jq.dragstart=jq.isdrag=jq.ismoving=false;

        /**
         * 固定开始位和结束位的动画,不添加新元素,速度可调
         * 动画通过操纵容器的第一个子元素的左边距实现
         * @param vec 方向,1表示右,-1表示左
         * @param time 用时,默认为settings.once_time
         */
        jq.banner_move=function(vec,time){
            jq.ismoving=true;
            if(typeof(time)=='undefined'){time=settings.once_time;}
            if(vec>0){/*右滑动*/
                jq.children().eq(0).animate({'margin-left':0},time,function(){
                    jq.old=jq.index;
                    jq.children().eq(1).remove();
                    jq.ismoving=false;
                    settings.onAfter(jq.index);
                    jq.animate();
                });
            }else{
                jq.children().eq(0).animate({'margin-left':-settings.box_width},time,function(){
                    jq.old=jq.index;
                    jq.children().eq(0).remove();
                    jq.ismoving=false;
                    settings.onAfter(jq.index);
                    jq.animate();
                });
            }
        };
        /**
         * 用于确定动画开始前的数据,会添加新元素
         * @param index 目标的数字位置
         */
        jq.update=function(index){
            if(typeof(index)!='undefined'){
                jq.index=index;
            }else{
                jq.index=jq.old+1>settings.move_group.length- 1?0:jq.old+1;
            }
            jq.children().stop(true);
            jq.ismoving=true;
            settings.onBefore(jq.index);
            if(jq.old<jq.index){/*目标在old的右边*/
                jq.append(settings.move_group.eq(jq.index).clone());
                jq.banner_move(-1);
            }else{/*目标在old的右边*/
                jq.prepend(settings.move_group.eq(jq.index).clone().css('margin-left',-settings.box_width));
                jq.banner_move(1);
            }
        };
        /**
         * 设置循环执行
         * @param index 可选
         */
        jq.animate=function(index){
            jq.children().stop(true)
            clearInterval(jq.timer);
            if(typeof(index)!='undefined'){
                jq.update(index);
            }
            jq.timer=setInterval(function(){jq.update();},settings.interval);
        };
        jq.animate();/*启动*/

        if(settings.click_group.click){
            settings.click_group.click(function(){
                var index = $(this).index();
                if(jq.ismoving==false && index!=jq.old){
                    settings.click_group.removeClass('on').eq(index).addClass('on');
                    jq.animate(index);
                }
            });
        }
        var x_start=0,x_fix=0,x_result,mouse_x= 0;
        /**
         * banner移动到边缘时的切换处理,
         * @param vec
         */
        jq.banner_shift=function(vec,nostyle){
            jq.old=jq.index;
            if(vec>0){/*向右的移动,新的index在左边*/
                jq.index=jq.old-vec<0?settings.move_group.length- 1:jq.old-vec;
                jq.children().eq(1).remove();/*移除右边的banner*/
                jq.prepend(settings.move_group.eq(jq.index).clone());/*在左边加入一个banner*/
                if(nostyle!==true){jq.children().eq(0).css('margin-left',-settings.box_width);}

            }else{/*与上面相反*/
                jq.index=jq.old-vec>settings.move_group.length- 1?0:jq.old-vec;
                jq.children().eq(0).remove();
                jq.append(settings.move_group.eq(jq.index).clone());
            }
            jq.old=jq.index;
        };

        if("ontouchstart" in document){/*绑定touch相关事件*/
            jq.on({'touchstart':function(e){
                e.preventDefault();
                x_start=e.originalEvent.targetTouches[0].pageX;/*记录手指按下位置*/
                x_fix=parseFloat(jq.children().stop(true).eq(0).css('margin-left'));/*记录靠左的banner的左边距,并停止动画*/
                jq.ismoving=false;
                jq.isdrag=true;
                clearInterval(jq.timer);
                if(jq.children().size()<2){
                    jq.index=jq.old+1>settings.move_group.length- 1?0:jq.old+1;
                    jq.append(settings.move_group.eq(jq.index).clone());
                }
            },'touchmove':function(e){
                if(jq.isdrag){
                    if(settings.auto_vector || settings.inertial>0) mouse_x=x_fix+e.originalEvent.targetTouches[0].pageX-x_start-x_result;
                    x_result=x_fix+e.originalEvent.targetTouches[0].pageX-x_start;/*计算最终左边距*/
                    if(x_result>0){/*中线位移超过左边缘*/
                        jq.banner_shift(1,true);
                        x_result-=settings.box_width;
                        x_fix-=settings.box_width;
                    }else if(x_result<-settings.box_width){/*中线位移超过右边缘*/
                        jq.banner_shift(-1);
                        x_result+=settings.box_width;
                        x_fix+=settings.box_width;
                    }
                    jq.children().eq(0).css('margin-left',x_result);
                }
            },'touchend':function(e){
                jq.isdrag=false;
                if(settings.inertial>0){/*TODO:如果惯性大于0,设置相应的持续滚动*/
                    var shift;
                }else{
                    var scale=Math.abs(parseFloat(jq.children().eq(0).css('margin-left'))/settings.box_width);/*左边距与banner宽之比的绝对值*/
                    if(settings.auto_vector){
                        if(mouse_x>0){
                            jq.banner_move(1,settings.once_time*scale);
                        }else{
                            jq.banner_move(-1,settings.once_time*(1-scale));
                        }
                    }else{
                        if(scale>1/2){/*margin-left超过宽度的一半,向左复位*/
                            jq.banner_move(-1,settings.once_time*(1-scale));
                        }else{/*与上面相反,向右复位*/
                            jq.banner_move(1,settings.once_time*scale);
                        }
                    }
                }
            }});
        }else if(("ondragstart" in document) && settings.mouse_drag){
            jq.on('dragstart',function(e){
                e.preventDefault();
            }).on('mousemove',function(e){
                if(jq.isdrag){
                    x_result=x_fix+e.clientX-x_start;/*计算最终左边距*/
                    if(x_result>0){/*中线位移超过左边缘*/
                        jq.banner_shift(1,true);
                        x_result-=settings.box_width;
                        x_fix-=settings.box_width;
                    }else if(x_result<-settings.box_width){/*中线位移超过右边缘*/
                        jq.banner_shift(-1);
                        x_result+=settings.box_width;
                        x_fix+=settings.box_width;
                    }
                    jq.children().eq(0).css('margin-left',x_result);
                }else{
                    mouse_x=e.clientX;
                }
            });
            $(document).on('keydown',function(e){
                switch (e.keyCode){
                    case 16:
                        x_start=mouse_x;/*记录手指按下位置*/
                        if(jq.ismoving) x_fix=parseFloat(jq.children().stop(true).eq(0).css('margin-left'));/*记录靠左的banner的左边距,并停止动画*/
                        jq.css('cursor','pointer');
                        jq.isdrag=true;
                        jq.ismoving=false;
                        clearInterval(jq.timer);
                        if(jq.children().size()<2){
                            jq.index=jq.old+1>settings.move_group.length- 1?0:jq.old+1;
                            jq.append(settings.move_group.eq(jq.index).clone());
                        }
                        break;
                }
            }).on('keyup',function(e){
                switch (e.keyCode){
                    case 16:
                        jq.isdrag=false;
                        jq.css('cursor','normal');
                        var scale=Math.abs(parseFloat(jq.children().stop(true).eq(0).css('margin-left'))/settings.box_width);
                        if(scale>1/2){/*偏移不到宽度的一半,向左复位*/
                            jq.banner_move(-1,settings.once_time*(1-scale));
                        }else{/*偏移超过宽度的一半,向右复位*/
                            jq.banner_move(1,settings.once_time*scale);
                        }
                        break;
                }
            });
        }
        return this;
    };
})( jQuery );
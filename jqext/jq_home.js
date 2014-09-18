/**
 * Created by Administrator on 14-9-16.
 */
(function( $ ){
    $.fn.slideBox = function(options) {
        var jq=this;
        var settings={
            first_run:true,
            start_index:0,
            box_width:1920,
            once_time:2000,
            interval:4000,
            onBefore:function(){
            },
            onAfter:function(){
            },
            click_group:{},
            move_group:{}

        };
        this.now=settings.start_index;
        this.ismoving=false;
        if ( options ) {
            $.extend( settings, options );
        }
        this.update=function(index){
            settings.onBefore();
            jq.ismoving=true;
            if(typeof(index)!='undefined' ){
                jq.index=index;
            }else{
                jq.index=jq.now+1>settings.move_group.length- 1?0:jq.now+1;
            }
            settings.move_group.stop(true).eq(jq.index).width(settings.box_width);
            if(jq.index<jq.now){
                settings.move_group.eq(jq.index).css('margin-left',-settings.box_width).animate({'margin-left':0},'slow',function(){
                    settings.move_group.eq(jq.now).width(0);
                    jq.now=jq.index;
                    jq.ismoving=false;
                    settings.onAfter();
                });
            }else{
                settings.move_group.eq(jq.now).animate({'margin-left':-settings.box_width},settings.once_time,function(){
                    $(this).css({'margin-left':0,"width":0});
                    jq.now=jq.index;
                    jq.ismoving=false;
                    settings.onAfter();
                });
            }
        };
        this.animate=function(index){
            clearInterval(jq.timer);
            if(typeof(index)!='undefined'){
                jq.update(index);
            }
            jq.timer=setInterval(
                function(){
                    jq.update();
                },
                settings.interval
            );
        };
        settings.click_group.click(function(){
            var index = $(this).index();
            if(jq.ismoving==false && index!=jq.now){
                settings.click_group.removeClass('on').eq(index).addClass('on');
                jq.animate(index);
            }
        });
        return this;
    };
})( jQuery );
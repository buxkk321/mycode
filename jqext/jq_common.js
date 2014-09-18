/**
 * Created by Administrator on 14-9-16.
 */
(function( $ ){
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
     *
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
})( jQuery );
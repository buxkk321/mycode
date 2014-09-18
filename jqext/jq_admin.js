/**
 * Created by Administrator on 14-9-16.
 */
(function( $ ){
    $.fn.itextinput=function(options){

        return this.each(function() {
            var obj=$(this);

            obj.data({'default_text':obj.val(),'isset':false}).focus(function(){
                if(!obj.data('isset')){
                    obj.val('');
                }
            }).blur(function(){
                if(obj.val()===''){
                    obj.data('isset',false).val(obj.data('default_text'));
                }else{
                    obj.data('isset',true);
                }
            });
        });
    };
    $.fn.ipassword = function(){
//        var settings={
//
//        };
//
//        if ( options ) {
//            $.extend( settings, options );
//        }
        return this.each(function() {
            var pwd_text=$(this),
                pwd_true=$('<input type="password" name="password" class="text" style="display: none" />');

            pwd_true.blur(function(){
                if(pwd_true.val()==''){
                    pwd_true.hide();
                    pwd_text.show();
                }
            }).insertAfter(
                pwd_text.focus(function(){
                    pwd_text.hide();
                    pwd_true.show().focus();
                })
            );
        });
    };
    $.fn.idelbtn=function(options){
        var settings={
            'container':'',
            'url':'',
            'type':'post',
            'confirm':true,
            'decide':function(re){
                if(re['status']==1){
                    settings['success'](re);
                }else{
                    settings['error'](re);
                }
            },
            'success':function(re){},
            'error':function(re){}
        };

        if ( options ) {
            $.extend( settings, options );
        }

        return this.click(function(){
            if ( settings['confirm'] && !confirm('确定要删除吗?')) {
                    return false;
            }else{
                $.ajax({
                    url : settings['url'],
                    type :settings['type'],
                    data:settings['box'].serialize(),
                    success : function(re) {
                        if(settings['decide'](re)){
                            settings['success']();
                        }else{
                            settings['error']();
                        };
                    }
                });
            }
        });
    };
})( jQuery );
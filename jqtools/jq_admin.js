/**
 * Created by Administrator on 14-9-16.
 */
(function( $ ){
    /**
     * 文本输入框默认值效果
     * @param options
     * @returns {*|each|Array|each|each|each}
     */
    $.fn.itextinput=function(){
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
    /**
     * 密码输入框的默认值效果
     * @returns {*|each|Array|each|each|each}
     */
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
    /**
     * ajax请求的删除按钮
     * @param options
     * @returns {*|click|click|click|click|click}
     */
    $.fn.idelbtn=function(options){
        var settings={
            'container':'',
            'url':'',
            'type':'post',
            'confirm':true,
            'decide':function(re){
                if(re['status']==1){
                    return true;
                }else{
                    return false;
                }
            },
            'success':function(re){
                self.location.href=re['url'];
            },
            'error':function(re){
                alert('删除失败:'+re['msg']);
            }
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
                    data:settings['container'].serialize(),
                    dataType:'json',
                    success : function(re) {
                        if(settings['decide'](re)){
                            settings['success'](re);
                        }else{
                            settings['error'](re);
                        };
                    }
                });
            }
        });
    };
})( jQuery );
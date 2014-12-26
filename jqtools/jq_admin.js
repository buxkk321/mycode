/**
 * Created by Administrator on 14-9-16.
 */
(function( $ ){
    /**
     * 分类树结构
     * @param data
     * @param options
     * @returns {fn}
     */
	$.fn.treelist=function(data,options){
        var settings={
            'name_value':{},
            'node':'<dl></dl>',
            'child':'<dd></dd>',
            'content':function(vo){
                return '<dt></dt>';
            }
        },obj=this;
        if ( options ) $.extend( settings, options );
        var len=data.length;
        for(var i=0;i<len;i++){
            obj.append(
                $(settings.node).attr({'id':'node_'+data[i]['id']}).append(settings.content(data[i])).append(settings.child)
            );
        }
        for(var i=0;i<len;i++){
            var p=$('#node_'+data[i]['pid']);
            if(data[i]['pid']>0 && p.length>0){
                p.children().last().append($('#node_'+data[i]['id']));
            }
        }
        return this;
	}
    /**
     * 文本输入框默认值效果
     * @param options
     *  name_value
     *  default_text
     *  优先级说明:$(this).val()>name_value>default_text
     * @returns {*|each|Array|each|each|each}
     */
    $.fn.iplaceholder=function(options){
        var settings={
            'name_value':{},
            'default_text':'请输入...'
        };
        if ( options ) $.extend( settings, options );

        return this.each(function() {
            var obj=$(this),value=obj.val();
            if(!value) value=settings['name_value'][obj.attr('name')];
            if(!value) value=settings.default_text;

            switch(obj.attr('type')){
                case 'text':
                    obj.data({'def_text':value,'isset':false}).focus(function(){
                        if(!obj.data('isset')) obj.val('') ;
                    }).blur(function(){
                        if(obj.val()===''){
                            obj.data('isset',false).val(obj.data('def_text'));
                        }else{
                            obj.data('isset',true);
                        }
                    });
                    break;
                case 'password':
                    var pwd_text=$('<input type="text" value="'+value+'" class="'+obj.attr('class')+'"/>');
                    obj.hide().blur(function(){
                        if(obj.val()==''){
                            obj.hide();
                            pwd_text.show();
                        }
                    }).after(
                        pwd_text.focus(function(){
                            pwd_text.hide();
                            obj.show().focus();
                        })
                    );
                    break;
                default :
                    break;
            }

        });
    };

    $.fn.idefault=function(options){
        var settings={
            'select':{'selector1':'value1','selector2':'value2'},
            'checkbox':{},
            'radio':{},
            'input':{},
            'textarea':{},

        },obj=this;
        if ( options ) $.extend( settings, options );
        $.each(settings.select,function(k,v){
            var temp=obj.find(k);
            temp.get(0).selectedIndex=temp.children('option[value="'+v+'"]').index();
            temp.change();
        });
        $.each(settings.checkbox,function(k,v){

        });
        $.each(settings.radio,function(k,v){

        });
        return this;
    };

    /**
     * ajax请求的删除按钮
     * @param options
     * @returns {*|click|click|click|click|click}
     */
    $.fn.idelbtn=function(options){
        var settings={
            'url':'',
            'type':'post',
            'confirm':true,
            'getquery':function(){},
            'decide':function(re){
                if(re['status']==1){return true;}else{return false;}
            },
            'success':function(re){
                self.location.href=re['url'];
            },
            'error':function(re){
                alert('删除失败:'+re['msg']);
            }
        };
        if ( options ) $.extend( settings, options );

        return this.click(function(){
            if ( settings.confirm && !confirm('确定要删除吗?')) {
                    return false;
            }else{
                $.ajax({
                    url : settings.url,
                    type :settings.type,
                    data:settings.getquery(),
                    dataType:'json',
                    success : function(re) {
                        if(settings.decide(re)){
                            settings.success(re);
                        }else{
                            settings.error(re);
                        };
                    }
                });
            }
        });
    };

})( jQuery );
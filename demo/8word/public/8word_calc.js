function arr_flip(arr){
    var re={};
    for(var i in arr){
        re[arr[i]]=i;
    }
    return re;
}

function fix_num(b,length){
    if(b<1) b+=length;
    if(b>length) b-=length;
    return b;
}

var 五行={'土':0,'金':1,'水':2,'木':3,'火':4};
var 五行关系=['同我','我生','我克','克我','生我'];
var 五行关系2=['','生','克','克','生'];



var 天干五行={
    '甲':['阳','木'],
    '乙':['阴','木'],
    '丙':['阳','火'],
    '丁':['阴','火'],
    '戊':['阳','土'],
    '己':['阴','土'],
    '庚':['阳','金'],
    '辛':['阴','金'],
    '壬':['阳','水'],
    '癸':['阴','水']
};
var 天干相冲={
    '甲庚':1,
    '乙辛':1,
    '丙壬':1,
    '丁癸':1
};
var 天干相合={
    '甲己':'土',
    '乙庚':'金',
    '丙辛':'水',
    '丁壬':'木',
    '戊癸':'火'
};
var 天干十神={
    '同我':['比肩','劫财'],
    '我生':['食神','伤官'],
    '我克':['偏财','正财'],
    '克我':['七杀','正官'],
    '生我':['偏印','正印']
};
var 地支五行={
    '子':['阳','水',1],
    '丑':['阴','土',2],
    '寅':['阳','木',3],
    '卯':['阴','木',4],
    '辰':['阳','土',5],
    '巳':['阴','火',6],
    '午':['阳','火',7],
    '未':['阴','土',8],
    '申':['阳','金',9],
    '酉':['阴','金',10],
    '戌':['阳','土',11],
    '亥':['阴','水',12]
};
var 地支相冲={
    '子午':1,
    '卯酉':1,
    '寅申':1,
    '巳亥':1,
    '辰戌':1,
    '丑未':1
};
var 地支相合={
    '六合':{
        '子丑':'土',
        '寅亥':'木',
        '卯戌':'火',
        '辰酉':'金',
        '巳申':'水',
        '午未':'土'
    },
    '三合':{
        '寅午戌':'火',
        '申子辰':'水',
        '亥卯未':'木',
        '巳酉丑':'金'
    },
    '半合':{
		'寅午':['戌','火'],
		'午戌':['寅','火'],
		'申子':['辰','水'],
		'子辰':['申','水'],
		'亥卯':['未','木'],
		'卯未':['亥','木'],
		'巳酉':['丑','金'],
		'酉丑':['巳','金']
    }

};
var 地支相刑={
    '寅巳':'恃势之刑',
    '巳申':'恃势之刑',
    '申寅':'恃势之刑',
    '未丑':'无恩之刑',
    '丑戌':'无恩之刑',
    '戌未':'无恩之刑',
    '子卯':'无礼之刑'
};
var 地支藏干={
    '子':['癸'],
    '丑':['己','癸','辛'],//
    '寅':['甲','丙','戊'],
    '卯':['乙'],
    '辰':['戊','乙','癸'],//
    '巳':['丙','庚','戊'],
    '午':['丁','己'],
    '未':['己','丁','乙'],//
    '申':['庚','壬','戊'],
    '酉':['辛'],
    '戌':['戊','辛','丁'],//
    '亥':['壬','甲']
};
function get_5e_relation(t,s){
    var diff=五行[t]-五行[s];
    if(diff<0) diff=5+diff;
    return diff;
}
function get_stem_word(index){
    var c=1;
    for(var i in 天干五行){
        if(c==index) return i;
        c++
    }
}
function get_branche_word(index){
    var c=1;
    for(var i in 地支五行){
        if(c==index) return i;
        c++
    }
}
function get_stems_5e(s){
    var self_info=天干五行[s];
    if(!self_info) return '';
    return self_info[0]+self_info[1];
}
function get_branches_5e(s){
    var self_info=地支五行[s];
    return 地支五行[s][0]+地支五行[s][1];
}
function get_stems_10G(t,s){
    var self_info=天干五行[s];
    var target_info=天干五行[t];

    var diff=get_5e_relation(target_info[1],self_info[1]);
    var relation=五行关系[Math.abs(diff)];
    var type=self_info[0]==target_info[0]?0:1;

//            var notes='自己:'+self_info[0]+'('+五行[self_info[0]]+') '+
//                    '当前:'+target_info[0]+'('+五行[target_info[0]]+') '+
//                    '结果:'+relation+'('+diff+')';
    var notes='';

    return 天干十神[relation][type]+notes;
}

function get_stems_relation(s1,s2){
    var re='';
    var asc=s1+s2,desc=s2+s1;

    var 冲=天干相冲[asc] || 天干相冲[desc];

    if(冲){
        re+='<div>---相冲---</div>';
    }

    var 合=天干相合[asc] || 天干相合[desc];
    if(合){
        re+='<div>---相合,化'+合+'---</div>';
    }


    var s1_info=天干五行[s1];
    var s2_info=天干五行[s2];

    var diff=get_5e_relation(s2_info[1],s1_info[1]);
    if(diff>0){
        var relation=五行关系2[diff];
        if(diff>2){
            re+='<div>←'+relation+'</div>';
        }else{
            re+='<div>'+relation+'→</div>';
        }
    }
    return re;
}


function check_word_sh(word1){
    var re=[];
    $.each(地支相合.三合,function(shstr,v){
        var get_item=[];
        var chku={};
        $.each(['年','月','日','时'],function(k2,v2){
            var 地支=word1[v2+'支'];
            if(shstr.indexOf(地支)>-1){/*找到符合的*/
                get_item.push(v2);
                chku[地支]=1;
            }else{/*未找到*/
            }
        });
        var cc=0;
        for(var c in chku) cc++;
        if(cc>2){
            re.push([get_item,v]);
        }
    });
    return re;
}

function get_branches_relation(b1,b2){
    var re='';
    var asc=b1+b2,desc=b2+b1;

    var 冲=地支相冲[asc] || 地支相冲[desc];
    if(冲){
        re+='<div>---相冲---</div>';
    }

    var 刑=地支相刑[asc] || 地支相刑[desc];
    if(刑){
        re+='<div>---'+刑+'---</div>';
    }else if(b1==b2){
		re+='<div>---自刑---</div>';
	}

    var 六合=地支相合.六合[asc] || 地支相合.六合[desc];
    if(六合){
        re+='<div>---六合,化'+六合+'---</div>';
    }
	
	var 半合=地支相合.半合[asc] || 地支相合.半合[desc];
    if(半合){
        re+='<div>-半合,缺'+半合[0]+',化'+半合[1]+'-</div>';
    }
	
	var b1_info=地支五行[b1];
    var b2_info=地支五行[b2];
	var diff=get_5e_relation(b2_info[1],b1_info[1]);
    if(diff>0){
        var relation=五行关系2[diff];
        if(diff>2){
            re+='<div>←'+relation+'</div>';
        }else{
            re+='<div>'+relation+'→</div>';
        }

    }
    return re;
}
function get_cross_relation(w1,w2){

}
function get_relation(w1,w2){
    var type1=!天干五行[w1];
    var type2=!天干五行[w2];
    if(type1==type2){
        if(type1){
            return get_branches_relation(w1,w2);
        }else{
            return get_stems_relation(w1,w2);
        }
    }else{
        return get_cross_relation(w1,w2);
    }
}
function get_lunar_month_node(year,cb){

}
var lunar_month_start_point_list={

};
var sb_calc={
    start_date:'2000/01/01',
    start_stem_num:5,
    start_branche_num:7,
    lunar_month_num:{
        '正':1,
        '一':1,
        '二':2,
        '三':3,
        '四':4,
        '五':5,
        '六':6,
        '七':7,
        '八':8,
        '九':9,
        '十':10,
        '冬':11,
        '腊':12
    },
    get_date_arr:function(str){
        /*获取阳历年月日时的数值*/
        if(typeof(str)=='string'){
            var date_arr=str.split(' ');
            var delimiter_year='/';
            date_arr[0]=date_arr[0].split(delimiter_year);
            date_arr[1]=date_arr[1].split(':');

            var timestamp=new Date(str);/*TODO::处理精确到分的节气分界点*/
            timestamp=timestamp.getTime();
            return [date_arr[0][0],date_arr[0][1],date_arr[0][2],date_arr[1][0],timestamp,str];
        }else if(typeof(str)=='number'){
            var  re=[];
            var time= new Date(str);
            re.push(time.getFullYear());
            re.push(time.getMonth()+1);
            re.push(time.getDate());
            re.push(time.getHours());
            re.push(time.getMinutes());
            re.push(time.getSeconds());

            return [re[0],re[1],re[2],re[3],str,re[0]+'/'+re[1]+'/'+re[2]+' '+re[3]+':'+re[4]+':'+re[5]];
        }

        return str;


    },
    get_lunar_month:function(str,cb){
        var date_arr=sb_calc.get_date_arr(str);
        var year=date_arr[0];

        var next=function(err,split_info){
            if(err){
                cb(err);
                return ;
            }
            var month=parseInt(date_arr[1]);
            var month_jieqi_info=split_info.month_node[month];

            var catcht;
            for(var x in month_jieqi_info){
                var luna_info=month_jieqi_info[x];/*此处格式为{name:xxx,time:xxx}*/
                var _time=new Date(luna_info.time);

                if(date_arr[4]>=_time.getTime()){
                    catcht=luna_info;
                    console.log('进入节气:',luna_info);
                }
            }
            if(catcht){
                console.log('当前时间:',date_arr[5],'  进入节气:'+catcht.name+';分界时间:'+catcht.time);

                catcht=month;

            }else{
                /*本月没有匹配到的节气起始点,取前一个月的数据*/
                console.log('当前时间:',date_arr[5],' 未进入本月节气:'+luna_info.name+';分界时间:'+luna_info.time,',计算取前一个月:',month-1);

                catcht=month-1;
                //if(catcht==0) catcht=12;
            }
            cb('',catcht);
        };
        if(lunar_month_start_point_list[year]){
            next('',lunar_month_start_point_list[year]);
        }else{
            $.ajax({
                url:"public/month_jieqi_"+year+".json",
                dataType: "json",
                success: function(re){
                    lunar_month_start_point_list[year]=re;
                    next('',lunar_month_start_point_list[year]);
                },
                error:function(){
                    next('ajax fail');
                }
            });
        }
    },

    get_stem_word:function(){

    },
    get_date_info:function(str,cb){
        var date_arr=sb_calc.get_date_arr(str);

        var timestamp=date_arr[4]/1000;/*当前时间戳的秒数*/
        var timestamp_fix=new Date(sb_calc.start_date);/*开始计算的日期*/
        timestamp_fix=timestamp_fix.getTime()/1000;/*开始时间的秒数*/

        var timestamp_diff=timestamp-timestamp_fix;/*当前时间距离开始时间的秒数*/
        var day_count=parseInt(timestamp_diff/(3600*24));/*当前时间距离开始时间的天数*/
        /*日天干*/
        var day_stem=day_count%10-sb_calc.start_stem_num;
        day_stem=fix_num(day_stem,10);
        /*日地支*/
        var day_branche=day_count%12-sb_calc.start_branche_num+2;
        day_branche=fix_num(day_branche,12);

        /*时地支*/
        var hour_branche=date_arr[3]>=23?1:Math.ceil(date_arr[3]/2)+1;
        /*时天干*/
        var hour_stem_fix=(day_stem>5?day_stem-5:day_stem)*2;
        if(hour_stem_fix>10) hour_stem_fix-=10;
        var hour_stem=hour_stem_fix+hour_branche-2;
        hour_stem=fix_num(hour_stem,10);

        /*年天干*/
        var year_stem=date_arr[0]%10-3;
        year_stem=fix_num(year_stem,10);
        /*年地支*/
        var year_branche=date_arr[0]%12-3;
        year_branche=fix_num(year_branche,12);

        /*获取农历月数*/
        sb_calc.get_lunar_month(date_arr,function(err,lunar_month){
            if(err){
                cb(err);
                return;
            }

            /*月天干*/
            var month_stem_fix=(year_stem>5?year_stem-5:year_stem)*2-1;
            if(month_stem_fix>10) month_stem_fix-=10;
            var month_stem=month_stem_fix-(-lunar_month);
            month_stem=fix_num(month_stem,10);

            /*月地支*/
            var month_branche=fix_num(lunar_month-11,12);

            var re=[];
            re[0]=get_stem_word(year_stem);
            re[1]=get_branche_word(year_branche);

            re[2]=get_stem_word(month_stem);
            re[3]=get_branche_word(month_branche);

            re[4]=get_stem_word(day_stem);
            re[5]=get_branche_word(day_branche);

            re[6]=get_stem_word(hour_stem);
            re[7]=get_branche_word(hour_branche);


            var 八字={};
            $.each(['年','月','日','时'],function(k,v){
                八字[v+'干']=re[k*2];
                八字[v+'支']=re[k*2+1];
            });

            cb('',八字);
        });



    },
    parse_all:function(八字,show_5e){
        var zzz= $.extend({},八字);



        $.each(['年','月','日','时'],function(k,v){
            var 天干=zzz[v+'干'];
            var st五行=天干五行[天干][1];
            zzz[v+'干五行']=st五行;

            var 地支=zzz[v+'支'];
            var br五行=地支五行[地支][1];
            zzz[v+'支五行']=br五行;


            var 天干强弱={生我:[],克我:[],我生:[],我克:[],本气通根:[]};
            var 干支关系=[];
            var diff=get_5e_relation(st五行,br五行);
            if(diff>0){
                干支关系.push(五行关系2[diff]);
                干支关系.push(diff>2);
                天干强弱[diff>2?('我'+干支关系[0]):(干支关系[0]+'我')].push(v+'支');
            }
            zzz[v+'干支']=干支关系;
            zzz[v+'干强弱']=天干强弱;

            var cg=地支藏干[地支];
            $.each(['本','中','余'],function(k2,v2) {
                zzz[v+'支'+v2+'气']=cg[k2] || '';
                zzz[v+'支'+v2+'气五行']=get_stems_5e(cg[k2]);
                var tg=[];
                $.each(['年','月','日','时'],function(kk,checkv){
                    var 天干=zzz[checkv+'干'];
                    if(cg[k2]==天干){
                        tg.push(v);
                    }
                });
                zzz[v+'支'+v2+'气透干']=tg;
            });
        });
        $.each(['年','月','日','时'],function(k,v){
            var 天干=zzz[v+'干'];
            $.each(['年','月','日','时'],function(kk,checkv){
                var 地支本气=zzz[checkv+'支本气'];
                if(天干==地支本气){
                    zzz[v+'干强弱'].本气通根.push(checkv);
                }
            });
        });


        zzz.地支三合=check_word_sh(zzz);
        return zzz;
    }
};
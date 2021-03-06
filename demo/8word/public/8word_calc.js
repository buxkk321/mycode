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
function get_5e_relation(now,target){
    var diff=五行[now]-五行[target];
    if(diff<0) diff=5+diff;
    return [diff,五行关系2[diff],diff>2];
}


var 天干五行={
    '甲':['阳','木',1],
    '乙':['阴','木',2],
    '丙':['阳','火',3],
    '丁':['阴','火',4],
    '戊':['阳','土',5],
    '己':['阴','土',6],
    '庚':['阳','金',7],
    '辛':['阴','金',8],
    '壬':['阳','水',9],
    '癸':['阴','水',10]
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
var 十二长生天干基数={
    '甲':1,
    '乙':8,
    '丙':10,
    '丁':9,
    '戊':10,
    '己':9,
    '庚':7,
    '辛':12,
    '壬':4,
    '癸':3
};
var 十二长生={
	'1':['长生',1.1],
	'2':['沐浴',0.96],
	'3':['冠带',1.1],
	'4':['临官',1.2],
	'5':['帝旺',1.2],
	'6':['衰',1.03],
	'7':['病',1],
	'8':['死',0.93],
	'9':['墓',0.86],
	'10':['绝',0.93],
	'11':['胎',1],
	'12':['养',1.03]
};
function get_12changsheng(st,br){
	var yy=天干五行[st][0]=='阳'?1:-1;
	var cs_val=十二长生天干基数[st]+yy*地支五行[br][2];
	if(cs_val>12) cs_val-=12;
	if(cs_val<1) cs_val+=12;
	return 十二长生[cs_val];
}



var 天干十神={
    '同我':['比肩','劫财'],
    '我生':['食神','伤官'],
    '我克':['偏财','正财'],
    '克我':['七杀','正官'],
    '生我':['偏印','正印']
};
var 十神评分={
	'比肩':1,
	'劫财':1,
    '食神':1,
	'伤官':1,
    '偏财':1,
	'正财':1,
    '七杀':1,
	'正官':1,
    '偏印':1,
	'正印':1
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
		'寅午':['火','戌'],
		'午戌':['火','寅'],
		'申子':['水','辰'],
		'子辰':['水','申'],
		'亥卯':['木','未'],
		'卯未':['木','亥'],
		'巳酉':['金','丑'],
		'酉丑':['金','巳']
    },
    '暗拱':{/*三合缺一*/
        '寅戌':['火','午'],
        '申辰':['水','子'],
        '亥未':['木','卯'],
        '巳丑':['金','酉']
    }

};

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
            re.push([v,get_item]);
        }
    });
    return re;
}

var 地支三会={
    '寅卯辰':['木','东方'],
    '巳午未':['火','南方'],
    '申酉戌':['金','西方'],
    '亥子丑':['水','北方']
};
var 地支暗会={/*三会缺一*/
    '寅辰':['卯','木','东方'],
    '巳未':['午','火','南方'],
    '申戌':['酉','金','西方'],
    '亥丑':['子','水','北方']
};

function check_word_sanhui(word1){
    var check_str1=word1.年支+word1.月支+word1.日支;
    var check_str2=word1.月支+word1.日支+word1.时支;

    var re=地支三会[check_str1] || 地支三会[check_str2];
    return re;
}


var 地支相刑={
    '寅巳':'恃势之刑',
    '巳申':'恃势之刑',
    '申寅':'恃势之刑',
    '未丑':'无恩之刑',
    '丑戌':'无恩之刑',
    '戌未':'无恩之刑',
    '子卯':'无礼之刑'
};
/*TODO::*/
var 地支三刑={
    '寅巳申':'',
    '未丑戌':''
};
var 地支相害={
    '子未':'子未',
    '丑午':'丑午',
    '寅巳':'寅巳',
    '卯辰':'卯辰',
    '申亥':'申亥',
    '酉戌':'酉戌'
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

var 地支组合={
	'子子':{},
	'子丑':{},
	'子寅':{},
	'子卯':{},
	'子辰':{},
	'子巳':{},
	'子午':{},
	'子未':{},
	'子申':{},
	'子酉':{},
	'子戌':{},
	'子亥':{},
	
	'丑丑':{},
	'丑寅':{},
	'丑卯':{},
	'丑辰':{},
	'丑巳':{},
	'丑午':{},
	'丑未':{},
	'丑申':{},
	'丑酉':{},
	'丑戌':{},
	'丑亥':{},
	
	'寅寅':{},
	'寅卯':{},
	'寅辰':{},
	'寅巳':{},
	'寅午':{},
	'寅未':{},
	'寅申':{},
	'寅酉':{},
	'寅戌':{},
	'寅亥':{},
	
	'卯卯':{},
	'卯辰':{},
	'卯巳':{},
	'卯午':{},
	'卯未':{},
	'卯申':{},
	'卯酉':{},
	'卯戌':{},
	'卯亥':{},
	
	'辰辰':{},
	'辰巳':{},
	'辰午':{},
	'辰未':{},
	'辰申':{},
	'辰酉':{},
	'辰戌':{},
	'辰亥':{},
	
	'巳巳':{},
	'巳午':{},
	'巳未':{},
	'巳申':{},
	'巳酉':{},
	'巳戌':{},
	'巳亥':{},
	
	'午午':{},
	'午未':{},
	'午申':{},
	'午酉':{},
	'午戌':{},
	'午亥':{},
	
	'未未':{},
	'未申':{},
	'未酉':{},
	'未戌':{},
	'未亥':{},
	 
	'申申':{},
	'申酉':{},
	'申戌':{},
	'申亥':{},
	
	'酉酉':{},
	'酉戌':{},
	'酉亥':{},
	
	'戌戌':{},
	'戌亥':{},
	
	'亥亥':{},
}
var 天干组合={
	'甲甲':{},
    '甲乙':{},
    '甲丙':{},
    '甲丁':{},
    '甲戊':{},
    '甲己':{},
    '甲庚':{},
    '甲辛':{},
    '甲壬':{},
    '甲癸':{},
	
    '乙乙':{},
    '乙丙':{},
    '乙丁':{},
    '乙戊':{},
    '乙己':{},
    '乙庚':{},
    '乙辛':{},
    '乙壬':{},
    '乙癸':{},
	
    '丙丙':{},
    '丙丁':{},
    '丙戊':{},
    '丙己':{},
    '丙庚':{},
    '丙辛':{},
    '丙壬':{},
    '丙癸':{},
	
    '丁丁':{},
    '丁戊':{},
    '丁己':{},
    '丁庚':{},
    '丁辛':{},
    '丁壬':{},
    '丁癸':{},
	
    '戊戊':{},
    '戊己':{},
    '戊庚':{},
    '戊辛':{},
    '戊壬':{},
    '戊癸':{},
	
    '己己':{},
    '己庚':{},
    '己辛':{},
    '己壬':{},
    '己癸':{},
	
    '庚庚':{},
    '庚辛':{},
    '庚壬':{},
    '庚癸':{},
	
    '辛辛':{},
    '辛壬':{},
    '辛癸':{},
	
    '壬壬':{},
    '壬癸':{},
	
	'癸癸':{},
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
	if(!t) return '';
	
    var self_info=天干五行[s];
    var target_info=天干五行[t];
	
    var diff=get_5e_relation(target_info[1],self_info[1]);
    var relation=五行关系[Math.abs(diff[0])];
    var type=self_info[0]==target_info[0]?0:1;

	var notes='';
//            notes='自己:'+self_info[0]+'('+五行[self_info[0]]+') '+
//                    '当前:'+target_info[0]+'('+五行[target_info[0]]+') '+
//                    '结果:'+relation+'('+diff+')';
    

    return 天干十神[relation][type]+notes;
}
function get_stems_relation(s1,s2){

    var re={};
    var asc=s1+s2,desc=s2+s1;

    re.冲=天干相冲[asc] || 天干相冲[desc];

    re.合=天干相合[asc] || 天干相合[desc];

    var s1_info=天干五行[s1];
    var s2_info=天干五行[s2];
	 
    re.五行关系=get_5e_relation(s1_info[1],s2_info[1]);

    return re;
}
function get_stems_relation2(s1,s2){
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
    if(diff[0]>0){
        if(diff[2]){
            re+='<div>←'+diff[1]+'</div>';
        }else{
            re+='<div>'+diff[1]+'→</div>';
        }
    }
    return re;
}

function get_branches_relation(b1,b2){
    var re={};
    var asc=b1+b2,desc=b2+b1;

    re.冲=地支相冲[asc] || 地支相冲[desc];

    re.刑=地支相刑[asc] || 地支相刑[desc];
    if(b1==b2){
        re.刑='自刑';
    }
    re.害=地支相害[asc] || 地支相害[desc];

    re.六合=地支相合.六合[asc] || 地支相合.六合[desc];

	re.半合=地支相合.半合[asc] || 地支相合.半合[desc];

    var b1_info=地支五行[b1];
    var b2_info=地支五行[b2];
    re.五行关系=get_5e_relation(b1_info[1],b2_info[1]);

    return re;
}

function get_branches_relation2(b1,b2){
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
        re+='<div>-半合,缺'+半合[1]+',化'+半合[0]+'-</div>';
    }

    var b1_info=地支五行[b1];
    var b2_info=地支五行[b2];
    var diff=get_5e_relation(b2_info[1],b1_info[1]);
    if(diff[0]>0){
        if(diff[2]){
            re+='<div>←'+diff[1]+'</div>';
        }else{
            re+='<div>'+diff[1]+'→</div>';
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
                    //console.log('进入节气:',luna_info);
                }
            }
            if(catcht){
                //console.log('当前时间:',date_arr[5],'  进入节气:'+catcht.name+';分界时间:'+catcht.time);
                catcht=month;
            }else{
                /*本月没有匹配到的节气起始点,取前一个月的数据*/
                //console.log('当前时间:',date_arr[5],' 未进入本月节气:'+luna_info.name+';分界时间:'+luna_info.time,',计算取前一个月:',month-1);
                catcht=month-1;
                //if(catcht==0) catcht=12;
            }
            cb('',catcht);
        };
        if(lunar_month_start_point_list[year]){
            next('',lunar_month_start_point_list[year]);
        }else{
            $.ajax({
                url:"../public/month_jieqi_"+year+".json",
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
    parse_all:function(八字,cfg){
        var zzz= $.extend({},八字);
        if(!cfg) cfg={};

        var t_arr=['年','月','日','时'];
        zzz.地支六合=[];
        zzz.地支半合=[];

        zzz.天干相合=[];

		
		
		
        $.each(t_arr,function(k,v){
            var prev_z=t_arr[k-1];/*前一个*/
            var next_z=t_arr[k+1];/*后一个*/

            var 天干=zzz[v+'干'];
            zzz[v+'干五行']=天干五行[天干];
			

            var diff;
            var 天干强弱={生我:[],克我:[],我生:[],我克:[],本气通根:[],冲:[]};
            /*和前一个天干比较*/
            if(prev_z){
                diff=get_stems_relation(天干,zzz[prev_z+'干']);
                if(diff.冲) 天干强弱.冲.push(diff.冲);

                diff=diff.五行关系;
                if(diff[0]>0){
                    天干强弱[diff[2]?('我'+diff[1]):(diff[1]+'我')].push(prev_z+'干');
                }
            }
            /*和后一个天干比较*/
            if(next_z){
                diff=get_stems_relation(天干,zzz[next_z+'干']);
                if(diff.冲) 天干强弱.冲.push(diff.冲);

                if(diff.合){
                    zzz.天干相合.push([diff.合,v+next_z]);
                }

                zzz[v+'干'+next_z+'干']=diff;
                diff=diff.五行关系;
                if(diff[0]>0){
                    天干强弱[diff[2]?('我'+diff[1]):(diff[1]+'我')].push(next_z+'干');
                }
            }


            var 地支=zzz[v+'支'];
			zzz[v+'支五行']=地支五行[地支];
            
            

            var 地支强弱={生我:[],克我:[],我生:[],我克:[],刑:[],冲:[],害:[]};
            /*和前一个地支比较*/
            if(prev_z){
                diff=get_branches_relation(地支,zzz[prev_z+'支']);
                if(diff.刑) 地支强弱.刑.push(diff.刑);
                if(diff.冲) 地支强弱.冲.push(diff.冲);
                if(diff.害) 地支强弱.害.push(diff.害);

                diff=diff.五行关系;
                if(diff[0]>0){
                    地支强弱[diff[2]?('我'+diff[1]):(diff[1]+'我')].push(prev_z+'支');
                }
            }
            /*和后一个地支比较*/
            if(next_z){
                diff=get_branches_relation(地支,zzz[next_z+'支']);
                if(diff.刑) 地支强弱.刑.push(diff.刑);
                if(diff.冲) 地支强弱.冲.push(diff.冲);
                if(diff.害) 地支强弱.害.push(diff.害);

                if(diff.六合){
                    zzz.地支六合.push([diff.六合,v+next_z]);
                }
                if(diff.半合){
                    zzz.地支半合.push([diff.半合,v+next_z]);
                }

                zzz[v+'支'+next_z+'支']=diff;
                diff=diff.五行关系;
                if(diff[0]>0){
                    地支强弱[diff[2]?('我'+diff[1]):(diff[1]+'我')].push(next_z+'支');
                }
            }



			
			
            var 干支关系=[];
			var st五行=天干五行[天干][1];
			var br五行=地支五行[地支][1];
			
            diff=get_5e_relation(st五行,br五行);
            if(diff[0]>0){
                干支关系.push(diff[1]);
                干支关系.push(diff[2]);
                天干强弱[diff[2]?('我'+diff[1]):(diff[1]+'我')].push(v+'支');
            }
            diff=get_5e_relation(br五行,st五行);
            if(diff[0]>0){
                地支强弱[diff[2]?('我'+diff[1]):(diff[1]+'我')].push(v+'支');
            }

			if(zzz['大运干']){
				/*和大运柱比较,大运柱均匀作用于每一柱*/
				zzz['大运'+v+'干关系']=get_stems_relation(天干,zzz['大运干']);
				zzz['大运'+v+'支关系']=get_branches_relation(地支,zzz['大运支']);
			}
          


            zzz[v+'干支关系']=干支关系;
            zzz[v+'干强弱']=天干强弱;
            zzz[v+'支强弱']=地支强弱;



            var cg=地支藏干[地支];
            $.each(['本','中','余'],function(k2,v2) {
                zzz[v+'支'+v2+'气']=cg[k2] || '';
                zzz[v+'支'+v2+'气五行']=天干五行[cg[k2]];
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
 
		zzz.日主={
			'强弱':[
				get_12changsheng(zzz.日干,zzz.月支),
				{
					
				}
			],
			'十神':{
				年干:get_stems_10G(zzz.年干,zzz.日干),
				年支本气:get_stems_10G(zzz.年支本气,zzz.日干),
				年支中气:get_stems_10G(zzz.年支中气,zzz.日干),
				年支余气:get_stems_10G(zzz.年支余气,zzz.日干),
				
				月干:get_stems_10G(zzz.月干,zzz.日干),
				月支本气:get_stems_10G(zzz.月支本气,zzz.日干),
				月支中气:get_stems_10G(zzz.月支中气,zzz.日干),
				月支余气:get_stems_10G(zzz.月支余气,zzz.日干),
				
				日支本气:get_stems_10G(zzz.日支本气,zzz.日干),
				日支中气:get_stems_10G(zzz.日支中气,zzz.日干),
				日支余气:get_stems_10G(zzz.日支余气,zzz.日干),
				
				时干:get_stems_10G(zzz.时干,zzz.日干),
				时支本气:get_stems_10G(zzz.时支本气,zzz.日干),
				时支中气:get_stems_10G(zzz.时支中气,zzz.日干),
				时支余气:get_stems_10G(zzz.时支余气,zzz.日干)
			}
		};
		
		
        /**/
        $.each(t_arr,function(k,v){
            //TODO::计算大运的影响，大运占4成
            var 天干=zzz[v+'干'];
            $.each(t_arr,function(kk,checkv){
                var 地支本气=zzz[checkv+'支本气'];
                if(天干==地支本气){
                    zzz[v+'干强弱'].本气通根.push(checkv);
                }
            });
        });


        /*最后的统计*/
        var score=cfg.score || {};

        var hj={'土':0,'金':0,'水':0,'木':0,'火':0};
        $.each(t_arr,function(k,v){
            var next_z=t_arr[k+1];/*后一个*/
 
            $.each(['干','支'],function(kk,vv){
                var org=1;
                var qr=zzz[v+vv+'强弱'];
					
                org+=qr.克我.length*score.kewo;/*TODO::天干同性相克力度更大,地支克天干力度很小*/
                org+=qr.我生.length*score.wosheng;/*TODO::天干异性相生力度更大*/
                org+=qr.生我.length*score.shengwo;
                if(vv=='干'){/*天干特有规则*/

                    /*伤官0.3*/
                    /*食神0.2*/

                    org+=qr.冲.length*score.chongtg;

                    org+=qr.本气通根.length*score.bqtg;

                }else{/*地支特有规则*/
                    org+=qr.冲.length*score.chongdz;

                    org+=qr.刑.length*score.xing;
                    org+=qr.害.length*score.hai;
                    /*TODO::三会的力量大于一切*/
                }
                var wx=zzz[v+vv+'五行'][1];
                hj[wx]+=org;
            });
        });

        $.each(zzz.地支六合,function(k,v){
            hj[v[0]]+=parseFloat(score.lh);
        });
        $.each(zzz.地支半合,function(k,v){
            hj[v[0][0]]+=parseFloat(score.banhe);
        });

        zzz.地支三合=check_word_sh(zzz);
        $.each(zzz.地支三合,function(k,v){
			hj[v[0]]+=parseFloat(score.sanhe);
		});

		//四土齐全??
        zzz.地支三会=check_word_sanhui(zzz);
        if(zzz.地支三会){
            hj[zzz.地支三会[0]]+=parseFloat(score.sanhui);
        }

        $.each(zzz.天干相合,function(k,v){
            hj[v[0]]+=parseFloat(score.tgh);
        });
        $.each(hj,function(k,v){
            hj[k]=parseFloat(v.toFixed(5));
        });
        zzz.五行统计=hj;
        return zzz;
    }
};
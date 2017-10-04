var fs=require('fs');
var path=require('path');
/*类似php的str_pad*/
exports.str_pad=function(input,pad_length,pad_string,pad_type){
    input=String(input);
    var len = input.length;
    while(len < pad_length) {
        if(pad_type){
            input += pad_string;
        }else{
            input = pad_string + input;
        }
        len++;
    }
    return input;
};
/*按顺序截取对象的一部分*/
exports.slice_obj=function(obj,length){
    var xc= 0,re={};
    if(length<0){
        for(var x in obj) xc++;
        //console.log('截取列表长度:',xc);
        xc+=length;
        if(xc>0){
            //console.log('截取列表长度 超出:',xc);
        }
        for(var x in obj){
            if(xc>0){
                xc--;
            }else{
                re[x]=obj[x];
            }
        }
    }else{
        //console.log('取出截取列表 前:',length,' 个');
        for(var x in obj){
            if(xc<length){
                xc++;
                re[x]=obj[x];
            }else{
                break;
            }
        }
    }
    //xc=0;for(var x in obj){xc++;}console.log('截取后 列表长度:',xc);
    return re;
};
exports.format_date=function(stamp,lv,return_arr){
    var time,re=[];
    if(stamp<0){
        time= new Date();
    }else{
        time= new Date(stamp);
    }

    if(!lv) lv=6;
    if(lv>=1) re.push(time.getFullYear());
    if(lv>=2) re.push(exports.str_pad(time.getMonth()+1,2,'0'));
    if(lv>=3) re.push(exports.str_pad(time.getDate(),2,'0'));
    if(lv>=4) re.push(exports.str_pad(time.getHours(),2,'0'));
    if(lv>=5) re.push(exports.str_pad(time.getMinutes(),2,'0'));
    if(lv>=6) re.push(exports.str_pad(time.getSeconds(),2,'0'));

    if(!return_arr){
        var str='';
        if(typeof(return_arr)!='object') return_arr=['','-','-',' ',':',':'];
        for(k in re){
            if(lv>k) str+=return_arr[k]+re[k];
        }
        re=str;
    }
    return re;
};
var dclog_style={
    'bold'          : '\x1B[1m',
    'italic'        : '\x1B[3m',
    'underline'     : '\x1B[4m',
    'inverse'       : '\x1B[7m',
    'strikethrough' : '\x1B[9m',
    'white'         : '\x1B[37m',
    'grey'          : '\x1B[90m',/*灰*/
    'black'         : '\x1B[30m',
    'blue'          : '\x1B[34m',/*蓝*/
    'cyan'          : '\x1B[36m',/*青*/
    'green'         : '\x1B[32m',/*绿*/
    'magenta'       : '\x1B[35m',/*品红*/
    'red'           : '\x1B[31m',/*红*/
    'yellow'        : '\x1B[33m',/*黄*/
    'whiteBG'       : '\x1B[47m',
    'greyBG'        : '\x1B[49;5;8m',
    'blackBG'       : '\x1B[40m',
    'blueBG'        : '\x1B[44m',
    'cyanBG'        : '\x1B[46m',
    'greenBG'       : '\x1B[42m',
    'magentaBG'     : '\x1B[45m',
    'redBG'         : '\x1B[41m',
    'yellowBG'      : '\x1B[43m'
};
exports.dlog=function(){
    var arg=[exports.format_date(-1)+' ---'];
    for(var i in arguments){
        arg.push(arguments[i]);
    }
    console.log.apply(this,arg);
};
exports.clog=function(color){
    var arg=[dclog_style[color] || dclog_style.white];
    for(var i in arguments){
        if(i>0) arg.push(arguments[i]);
    }
    arg.push('\x1B[37m');
    console.log.apply(this,arg);
};
exports.dclog=function(color){
    var arg=[color,exports.format_date(-1)+' ---'];
    for(var i in arguments){
        if(i>0) arg.push(arguments[i]);
    }
    exports.clog.apply(this,arg);
};
var exec_delay={
    do:function(name,cb,timeout){
        if(!timeout) timeout=1;
        var that=exec_delay;
        if(that.task_now[name]){
            //dclog('blue','task:'+name+' now is exists!');
            //that.task_next[name]=function(){
            //    delete that.task_next[name];
            //    //dclog('blue','task:'+name+' next start');
            //    cb();
            //}
        }else{ /*首次申请删除任务*/
            //dclog('blue','create task:'+name+' ! , exec timeout:',timeout);
            that.task_now[name]=1;
            setTimeout(function(){
                //dclog('blue','task:'+name+' exec start');
                delete that.task_now[name];
                cb();
                //if(typeof(that.task_next[name])=='function'){
                //    that.task_next[name]();
                //}
            },timeout);
        }
    },
    task_now:{},
    task_next:{}
};
exports.exec_delay=exec_delay.do;
exports.isEmpty=function(val){
    switch(typeof(val)){
        case 'number':
            if(isNaN(val)){
                return true;
            }else{
                return val==0;
            }
            break;
        case 'string':
            return val=='';
            break;
        case 'object':
            if(!val) return true;
            for(var name in val){
                return false;
            }
            return true;
            break;
        case 'undefined':
            return true;
            break;
        default :/*剩余的function和bool都认为是非空*/
            return false;
    }
};
/*安全获取对象属性*/
exports.getValue = function(obj, properties){
    if(typeof properties === 'string' || typeof properties === 'number'){
        properties = [properties];
    }
    var tmpobj = obj;
    if(properties && obj && typeof(obj)=='object'){
        for(k in properties){
            tmpobj = tmpobj[properties[k]];
            if(!(typeof(tmpobj)=='object' && tmpobj)){
                return tmpobj;
            }
        }
        return tmpobj;
    }else{
        return obj;
    }
};
/*TODO::将任意数据输出成完整的字符串，相当于php的var_dump*/
exports.dump = function(data){
    var re='';
    for(var i in data){
        if(typeof(data[i])=='object'){
            re+=exports.dump(data[i]);
        }
        re+=i;
    }
    return re;
};


exports.str_split=function(str, length){
    str=String(str);
    var str_arr = [];
    var start = 0;
    while(start <= str.length){
        var end = start + length;
        str_arr.push(str.slice(start, end));
        start = end;
    }
    return str_arr;
};
exports.flog_base=function(path,msg,print,extra){
    msg=exports.format_date(-1)+'---'+msg;
    print=exports.toParseInt(print);
    if(!path) path='';
    if(print>=0){
        fs.appendFile(
            exports.mkdir_by_date(path,1)+'.log',msg+'\n',
            function (err) {if (err) console.log('+++++++tools error+++++++',err);}
        );
    }
    if(print!=0){
        if(print==2 || print==-2){
            console.log(msg,extra);
        }else{
            console.log(msg);
        }
    }
};
exports.json_re=function(re,return_str){
    if(!re) re={};
    if(typeof(re)=='string') re={msg:re};
    re.status=exports.toParseInt(re.status);
    return return_str?JSON.stringify(re):re;
};
/*创建多层目录，返回路径，结尾不含'/' */
exports.mkdir_deep=function(input_path){
    input_path=path.normalize(input_path);
    input_path=input_path.split(path.sep);

	var path_now='';
	var count=0;
	for(var i in input_path){
		if(input_path[i]=='.' || input_path[i]==''){
			
		}else{
			if(count>0){
				path_now+='/'+input_path[i];
			}else{
				path_now+=input_path[i];
			}
			count++;
			if(input_path[i]=='..'){
			}else{
				if (!fs.existsSync(path_now)){
					fs.mkdirSync(path_now);
				}
			}
			
		}
	}
	return path_now;
};
exports.mkdir_by_date=function(start_path,deep,h){

    var Da=new Date();
    var path=exports.mkdir_deep(start_path)+'/Y'+Da.getFullYear();
    if (!fs.existsSync(path)){
        fs.mkdirSync(path);
    }
    deep=exports.toParseInt(deep);
    if(deep<=1){
        var month=String(Da.getMonth()+1);
        if(month.length<2) month='0'+month;
        path+='/'+month+Da.getDate();
        if(deep<=0){
            if (!fs.existsSync(path)){
                fs.mkdirSync(path);
            }
            if (h){
                var time=new Date();
                path+='/'+exports.str_pad(time.getHours(),2,'0');
            }
        }
    }
    return path;
};
/*清空目录文件*/
exports.cleandir=function(dir){
    try{
        var files = fs.readdirSync(dir);//读取该文件夹
        files.forEach(function(file){
            var stats = fs.statSync(dir+'/'+file);
            if(!stats.isDirectory()){
                fs.unlinkSync(dir+'/'+file);
            }
        });
    } catch (x) {
        return x;
    }
    return false;
};
exports.toParseInt=function(str){
    str=String(str);
    return parseInt(str.indexOf('-')==0?str:'0'+str);
};
exports.toParseFloat=function(str){
    str=String(str);
    return parseFloat(str.indexOf('-')==0?str:'0'+str);
};

var initTime = setTimeout(function(){
}, 100);
var _init_sys_time = initTime._idleStart;
var _init_time = Date.now();
clearTimeout(initTime);

exports.finishTimeout=function(t){
    t._onTimeout();
    clearTimeout(t);
};
exports.pauseTimeout=function(t){
    if(t._surplusTime === undefined){
        var cb=t._onTimeout;
        var surplusTime= (t._idleStart + t._idleTimeout - _init_sys_time) - (Date.now() - _init_time);
        if(surplusTime<0){
            surplusTime = 0;
        }
        clearTimeout(t);
        t._onTimeout=cb;
        t._surplusTime=surplusTime;
    }
};

exports.continueTimeout=function(t){
    return setTimeout(t._onTimeout, t._surplusTime);
};

var querystring = require('querystring');
var http = require('http');
var https = require('https');

exports.http_get=function(url,send_data,callback,err){
    var res_data='';
    if(!url){
        console.log("http_get error: url is empty");
        callback(res_data,"http_get error: url is empty");
        return ;
    }
    var fix=url.indexOf('?')<0?'?':'&';
	fix=url+(send_data?fix+querystring.stringify(send_data):'');
	
	function parse_data(res){
		res.on('data',function(d){
			res_data += d;
		}).on('end', function(a){
			callback(res_data);
		});
	} 
	if(url.indexOf('https')==0){
		var req = https.get(fix,parse_data).on('error', function(e) {
			//TODO::查询接口出错
			console.log("https_get error: " + e.message);
			callback(res_data,e.message);
		});
	}else{
		var req = http.get(fix,parse_data).on('error', function(e) {
			//TODO::查询接口出错
			console.log("http_get error: " + e.message);
			callback(res_data,e.message);
		});
	}
    
    req.end();
};


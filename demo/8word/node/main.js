/*---nodejs模块-----*/
var fs=require('fs');
var path=require("path");
var http= require('http');
var url= require('url');
var querystring = require('querystring');
var child_process = require('child_process');
var net= require('net');


var temp_path=process.cwd();/*临时文件目录*/
var temp_dir=path.dirname(temp_path);/*程序根目录*/
var exec_path=path.dirname(process.execPath);/*安装文件所在目录*/


/*---第三方-----*/

var ts=require('./tools.js');
function dlog(){
    var arg=[ts.format_date(-1)+' ---'];
    for(var i in arguments){
        arg.push(arguments[i]);
    }
    console.log.apply(this,arg);
}
var dclog_style={
    'bold'          : '\x1B[1m',
    'italic'        : '\x1B[3m',
    'underline'     : '\x1B[4m',
    'inverse'       : '\x1B[7m',
    'strikethrough' : '\x1B[9m',
    'white'         : '\x1B[37m',
    'grey'          : '\x1B[90m',
    'black'         : '\x1B[30m',
    'blue'          : '\x1B[34m',
    'cyan'          : '\x1B[36m',
    'green'         : '\x1B[32m',
    'magenta'       : '\x1B[35m',
    'red'           : '\x1B[31m',
    'yellow'        : '\x1B[33m',
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
function dclog(color){
    var arg=[dclog_style[color] || dclog_style.white,ts.format_date(-1)+' ---'];
    for(var i in arguments){
        if(i>0) arg.push(arguments[i]);
    }
    arg.push('\x1B[37m');
    console.log.apply(this,arg);
}
var exec_delay={
    exec:function(name,cb,timeout){
        var that=this;
        if(!timeout) timeout=1;
        if(that.task_now[name]){
            //dclog('blue','exec_delay task now is exists!');
            that.task_next[name]=function(){
                delete that.task_next[name];
                //dclog('blue','same task_next exec start');
                cb();
            }
        }else{ /*首次申请删除任务*/
            //dclog('blue','new exec_delay task!');
            that.task_now[name]=1;
            cb();
            setTimeout(function(){
                //dclog('blue','same task exec finish');
                if(typeof(that.task_next[name])=='function'){
                    that.task_next[name]();
                }
                that.task_now[name]=0;
            },timeout);

        }
    },
    task_now:{},
    task_next:{}
};

function array_select(keys,input){
    if(typeof(keys)=='string') keys=keys.split(',');
    var re={};
    for(var k in keys){
        var v=keys[k];
        if(typeof(v)=='string'){
            v=v.split('|');
        }
        re[v[1] || v[0]]=input[v[0]];
    }
    return re;
}
function str2json(str){
    var re;
    try{
        re=JSON.parse(str);
        return re;
    } catch (x) {
        re=eval('('+str+')');
        if(re){
            return re;
        }else{
            return false;
        }
    }
}
function inArray(item,arr){
    for(var k in arr){
        if(arr[k] == item){
            return true;
        }
    }
    return false;
}

function res_success(res,data){
    if(res.finished == false) {
        res.setHeader('Content-Type','application/json');
        res.write(JSON.stringify({status: 1, info: data}));
    }
}
function res_error(res,msg){
    if(res.finished == false){
        res.setHeader('Content-Type','application/json');
        res.write(JSON.stringify({status:0,info:msg}));
    }
}

/*http服务器*/
var http_serv={
    PORT:10080,
    server:'',
    restart:function(){
        var that=this;

        var create=function(){
            that.server=http.createServer(function(req, res) {
                if(!req.url){
                    res.end();
                    return;
                }
                if (req.method.toLowerCase() == 'post'){
                    //if(typeof(http_serv.post_res[req.url])=='function'){
                    //    http_serv.post_res[req.url](req,res);
                    //}
                    res.end();
                }else{
                    var $_GET=url.parse(req.url,true);
                    //console.log($_GET);
                    var path_info=$_GET.pathname.split('/');
                    //console.log('get is',$_GET.query,'pathinfo is',path_info);
                    if(typeof(that.get_res[path_info[1]])=='function'){
                        that.get_res[path_info[1]](req,res,$_GET.query,path_info);
                    }else{
                        res.end();
                    }
                }
            }).listen(http_serv.PORT,function(){
                dlog("http_server listening " + http_serv.PORT);
            });
        };

        if(that.server){
            that.server.close(function(){
                ts.dclog('cyan','http_server restart');
                setTimeout(create,1000)
            });
        }else{
            create();
        }


    },
    get_res:{
        'public':function(req,res){
            var file=temp_path+req.url;
            //dlog('req public file:',file);
            if(fs.existsSync(file) && !fs.statSync(file).isDirectory()){
                res.write(fs.readFileSync(file));
            }
            res.end();
        },
        'index':function(req,res){
            var file_content=fs.readFileSync(temp_path+'/view/index.html');
            res.write(file_content);
            res.end();
        },
        'api':function(req,res,$_GET,path_info){
            var func=path_info[2];
            if(typeof(http_serv.api[func])=='function'){
                http_serv.api[func](req,res,$_GET);
            }else{
                res.end();
            }
        }
    },
    api:{
    }
};
http_serv.restart();


//
//child_process.exec(temp_path+'/index.url',{
//        env: process.env
//    },
//    function (err, stdout, stderr) {
//        if (err) {
//            dlog('init open index error',err);
//        }
//    }
//);



var jieqi_data_api={
    url:'http://api.jisuapi.com/jieqi/query',
    appkey:'9aa28a8bc82fab59',
    query:function(query_data,cb){
        var that=this;
        query_data.appkey=that.appkey;
        ts.http_get(
            that.url,
            query_data,
            function(res_data){
                var err='';
                try{
                    res_data=JSON.parse(res_data);
                } catch (x) {
                    err='返回json数据格式错误:';
                }
                if(err){
                    dlog('jieqi_data_api_url error:',err,res_data,that.url,query_data);
                }else{
                    if(res_data.status=='0'){
                        cb('',res_data.result);
                    }else{
                        cb(res_data.msg);
                    }
                }
            }
        );
    }
};


/*临时编写的获取农历数据的代码*/
var loopg={
    interval:100,
    start:function(year){
        var da={};

        var task_pool={};
        var end=function(data){
            var month_node={};

            for(var x in data){
                var v=data[x];
                var _time= v.time.split('-');
                var m=parseInt(_time[1]);
                if(!month_node[m]){
                    month_node[m]=[]
                }
                if(v.jieqiid%2==1){
                    month_node[m].push({
                        name: v.name,
                        time: v.time
                    });
                }
            }
            var wdata={month_node:month_node};

            fs.writeFileSync(temp_path+'/public/month_jieqi_'+year+'.json',JSON.stringify(wdata));
            console.log('end!!!',data);

        };

        var send_data={year:year};
        jieqi_data_api.query(send_data,function(err,re){
            if(err){

            }else{
                //console.log('res!!!',re);
            }
            end(re.list);
        });
    }
};
//loopg.start(2018);

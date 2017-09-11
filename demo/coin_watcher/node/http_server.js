/*---nodejs模块-----*/
var fs=require('fs');
var path=require("path");
var http= require('http');
var url= require('url');
var querystring = require('querystring');

var temp_path=process.cwd();/*临时文件目录*/
var temp_dir=path.dirname(temp_path);/*程序根目录*/

/*---第三方-----*/

var ts=require('./tools.js');
var dlog=ts.dlog;
var dclog=ts.dclog;

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
var match_data_rule= {
    time:/--------(.+?)--------/i,
    buy:/buy\:(.+?)\;/i,
    avg:/~~~\:(.+?)\~~~;/i,
    sell:/sell\:(.+?)\;/i,
    buy_all:/buy_all\:(.+?)\;/i,
    buy_avg:/buy_avg\:(.+?)\;/i,
    sell_all:/sell_all\:(.+?)\;/i,
    sell_avg:/sell_avg\:(.+?)\;/i,
    high:/high\:(.+?)\;/i,
    low:/low\:(.+?)\;/i
};
var coin_data_cache={};
/*http服务器*/
var http_serv={
    PORT:10080,
    server:'',
    restart:function(){
        var that=http_serv;
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
                        that.get_res[path_info[1]](req,res,$_GET.query || {},path_info);
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
        get_latest_data:function(req,res,$_GET){
            var data={},coin=$_GET.coin;
            if(coin){
                var time_now=new Date().getTime();
                var last_get_file='';

                function _get_file_content(timestamp){
                    var ct_arr='';
                    var format_time=ts.format_date(timestamp,0,1);
                    var year=format_time[0]+'';
                    var month=format_time[1]+'';
                    var day=format_time[2]+'';
                    var hour=format_time[3]+'';
                    var f=path.join(temp_path,'arrange_data',coin,year,month+'_'+day,hour+'.log');
                  
					if(last_get_file!=f){
                        last_get_file=f;
						if(fs.existsSync(f)){
							//console.log(coin,'read file:',f,timestamp);
                            ct_arr=fs.readFileSync(f,'utf-8');
                            ct_arr=ct_arr?ct_arr.split(';;;'):[];
                        }
                    }else{
					}
                    return ct_arr || [];
                }
                var content=_get_file_content(time_now);
                //console.log(coin,'content 1 length:',content.length);
                if($_GET.is_init>0){
                    var content_prev=_get_file_content(time_now-1500*1000);
                    //console.log(coin,'content_prev 1 length:',content_prev.length);
                    if(content_prev.length==0){
                        content_prev=_get_file_content(time_now-3000*1000);
                        //console.log(coin,'content_prev 2 length:',content_prev.length);
                    }
                    content=content_prev.concat(content);
                    //console.log(coin,'content 2 length:',content.length);
                    if(content.length<500){
                        content_prev=_get_file_content(time_now-4500*1000);
                        content=content_prev.concat(content);
                        //console.log(coin,'content extra length:',content.length);
                    }
                }

                if(content.length>0){
                    var tmp_list={};
                    for(var x in content){
                        var time,tmp_data={};
                        var child_str=content[x];
                        var errf=1;
                        for(var rule_name in match_data_rule){
                            var reg=match_data_rule[rule_name];
                            var check_data=child_str.match(reg);
                            if(check_data){
                                if(rule_name=='time'){
                                    time=check_data[1];
                                }else{
                                    tmp_data[rule_name]=check_data[1];
                                }
                                errf=0;
                            }
                        }
                        if(!time || errf) continue;/*没有时间或没有匹配内容,则跳过此次循环*/
                        tmp_list[time]=tmp_data;
                        //console.log(coin,'parse time:',time);
                    }
					
                    /*使用缓存*/
					var xx=0;
					//xx=0;for(var x in coin_data_cache) xx++;console.log('之前缓存长度:',xx);
                    for(var time in tmp_list){
                        if(!coin_data_cache[time]){
                            coin_data_cache[time]=tmp_list[time];
                            data[time]=tmp_list[time];/*将缓存中还没有的输出*/
                        }
                    }
					//xx=0;for(var x in coin_data_cache) xx++;console.log('现在缓存长度:',xx);
                    coin_data_cache=ts.slice_obj(coin_data_cache,-500);/*仅缓存500条*/
					//xx=0;for(var x in coin_data_cache) xx++;console.log('剪切后缓存长度:',xx);
                    if($_GET.is_init>0){
                        data=coin_data_cache;/*初始化时将缓存数据全部返回*/
                    }else{
                        //console.log('peace data:',data);
                    }
					//xx=0;for(var x in data) xx++;console.log('输出数据长度',xx);
                }

            }
            res_success(res,data);
            res.end();
        }
    }
};


exports.restart=http_serv.restart;
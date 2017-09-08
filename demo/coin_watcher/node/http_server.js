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
            var data={};
            if($_GET.coin){
                var time_now=new Date().getTime();
                var last_get_file='';
                var content='';
                function _get_file_content(timestamp){
                    var format_time=ts.format_date(timestamp,0,1);
                    var year=format_time[0]+'';
                    var month=format_time[1]+'';
                    var day=format_time[2]+'';
                    var hour=format_time[3]+'';
                    var f=path.join(temp_path,'arrange_data',$_GET.coin,year,month+'_'+day,hour+'.log');
                  
					if(last_get_file!=f){
                        last_get_file=f;
						if(fs.existsSync(f)){
							//console.log($_GET.coin,'read file:',f);
                            content=fs.readFileSync(f,'utf-8')+content;
                        }
                    }else{
					}
                }
                _get_file_content(time_now);
                _get_file_content(time_now-3000*1000);
                _get_file_content(time_now-6000*1000);
				if(content.length<60000){
					_get_file_content(time_now-9000*1000);
				} 
				
                if(content){
                    content=content.split(';;;');
					var xc=0;
                    for(var x in content){
                        var time,tmp_data={};
                        var child_str=content[x];

                        var errf=1;
                        for(var data_key in match_data_rule){
                            var kw=match_data_rule[data_key];
                            var check_data=child_str.match(kw);
                            if(check_data){
                                tmp_data[data_key]=check_data[1];
                                if(data_key=='time') time=check_data[1];
                                errf=0;
                            }
                        }
                        if(!time || errf) continue;
                        data[time]=tmp_data;
						//console.log($_GET.coin,'parse time:',time);
                    }
					for(var x in data){
						xc++;
                    }
					//console.log($_GET.coin,'parse data count:',xc);
					if(xc>200){
						xc-=200;
						for(var x in data){
							delete data[x];
							xc--;
							if(xc<=0) break; 
						}
					}
					xc=0;
					for(var x in data){
						xc++;
                    }
                    //console.log($_GET.coin,'parse data count:',xc);
                }

            }
            res_success(res,data);
            res.end();
        }
    }
};


exports.restart=http_serv.restart;
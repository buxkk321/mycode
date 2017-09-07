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


exports.restart=http_serv.restart;
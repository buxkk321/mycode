/*---nodejs模块-----*/
var fs=require('fs');
var path=require("path");
var http= require('http');
var https = require('https');
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


var watcher=require('./watcher.js');

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
    PORT:10084,
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
        'btc_viewer':function(req,res){
            var file_content=fs.readFileSync(temp_path+'/view/btc_viewer.html');
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
        get_order_book:function(req,res,$_GET){
            var data={},
                coin=$_GET.coin || 'btc',
                plt=$_GET.plt;
			
			function cb(msg,err){
				if(err){
					res_error(res,err);
				}else{
					res_success(res,msg);
				}
				res.end();
			}
			
			if(!coin || !plt){
				cb();
				return;
			}
			
			
			var query_url="";
			var query_data = {};
            function end(res_data,err){
                if(!err){
                    try{
                        res_data=JSON.parse(res_data);
                    } catch (x) {
                        err='返回json数据格式错误:';
                    }
                }
                if(err){
                    dlog('get_order_book error:',err,res_data,query_url,query_data);
                    cb(false,err);
                }else{
                    //dlog('get_order_book success, query data :',query_data);
                    if(typeof(cb)=='function'){
                        cb(res_data);
                    }
                }
            }
			switch(plt){
                case 'bitmex':
                    query_url='https://www.bitmex.com/api/v1/orderBook/L2';
                    query_data = {
                        symbol:'XBT',
                        depth:150
                    };
                    break;
                case 'bitfinex':
                    query_url='https://api.bitfinex.com/v1/book/'+'BTCUSD';
                    query_data = {
                        limit_bids:150,
                        limit_asks:150
                    };
                    break;
                case 'bittrex':
                    query_url='https://bittrex.com/api/v1.1/public/getorderbook';
                    query_data = {
                        market:'USDT-BTC',
                        type:'both'
                    };
                    break;
                case 'bitstamp':
                    query_url='https://www.bitstamp.net/api/v2/order_book/'+'btcusd'+'/';
                    break;
                case 'kraken_usd':
                    query_url='https://api.kraken.com/0/public/Depth';
                    query_data = {
                        pair:'XBTUSD',
                        count:50
                    };
                    break;
                case 'gdax':
                    query_url='https://api.gdax.com/products/'+'BTC-USD'+'/book?level=2';
                    var options = {
                        hostname:'api.gdax.com',
                        path:'products/'+'BTC-USD'+'/book?level=2',
                        method: 'GET',
                        headers: { 'User-Agent': 'Mozilla/5.0' }
                    };
                    var res_data='';
                    var req1 = https.request(options,function(res1){
                        res1.on('data',function(d){
                            res_data += d;
                        }).on('end', function(a){
                            end(res_data);
                        });
                    }).on('error', function(e) {
                        //TODO::查询接口出错
                        console.log("https_get error: " + e.message);
                        end(res_data,e.message);
                    });
                    req1.end();
                    return;
                    break;
                case 'bithumb':
                    break;

                case 'coincheck':
                    query_url='https://coincheck.com/api/order_books/';
                    break;
                case 'bitflyer':
                    query_url='https://api.bitflyer.jp/v1/getboard';
                    query_data = {
                        product_code: 'BTC_JPY'
                    };
                    break;
                case 'zaif':
                    query_url='https://api.zaif.jp/fapi/1/depth/1/btc_jpy';

                    break;
			}
			ts.http_get(
				query_url,
				query_data,
                end
			);
		}
    }
};


http_serv.restart();
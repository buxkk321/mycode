/*---nodejs模块-----*/
var fs=require('fs');
var path=require("path");
var crypto = require('crypto');

function md5(text) {
    return crypto.createHash('md5').update(text).digest('hex');
}

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

var my_key='';
var signature='';


var get_ticker={};
get_ticker.url='http://www.jubi.com/api/v1/ticker/';
get_ticker.query=function(coin,cb){
    var that=this;
    var query_data = {
        coin:coin
    };

    ts.http_get(
        that.url,
        query_data,
        function(res_data,err){
            if(!err){
                try{
                    res_data=JSON.parse(res_data);
                } catch (x) {
                    err='返回json数据格式错误:';
                }
            }
            if(err){
                dlog('get_data_m error:',err,res_data,that.url,query_data);
                cb(false,err);
            }else{
                if(typeof(cb)=='function'){
                    cb(res_data);
                }
            }
        }
    );
};

var get_orders={};
get_orders.url='http://www.jubi.com/api/v1/orders/';
get_orders.query=function(coin,cb){
    var that=this;
    var query_data = {
        coin:coin
    };

    ts.http_get(
        that.url,
        query_data,
        function(res_data,err){
            if(!err){
                try{
                    res_data=JSON.parse(res_data);
                } catch (x) {
                    err='返回json数据格式错误:';
                }
            }
            if(err){
                dlog('get_data_m error:',err,res_data,that.url,query_data);
                cb(false,err);
            }else{
                if(typeof(cb)=='function'){
                    cb(res_data);
                }
            }
        }
    );
};

var analyzer={
    current_100:{}
};
analyzer.loop=function(coin,delay1,delay2){
    get_ticker.query(coin,function(re1,err1){
        //re1:获取交易价格信息
        setTimeout(function(){
            get_orders.query(coin,function(re2,err2){
                //re2:获取最近100单交易信息
                if(re1 && re2){
                    console.log(" \n\r ");
                    var ana={buy:[],sell:[]};
                    var c100=analyzer.current_100;
                    var xc_c=0;
                    for(var xc in c100){
                        xc_c++;
                    }

                    if(xc_c>200){
                        xc_c-=200;
                        for(var xc in c100){
                            delete c100[xc];
                            xc_c--;
                            if(xc_c<=200) break;
                        }

                    }
                    for(var x in re2){
                        var one=re2[x];
                        var date=one.date;
                        if(c100[date]) continue;
                        c100[date]=one;

                        var all=one.amount*one.price;
                        if(!ana[one.type]){
                            ana[one.type]=[];
                        }
                        one.all=all;
                        ana[one.type].push(one);


                    }
                    ana.buy.sort(function(a,b){
                        return a.price-b.price;
                    });
                    ana.sell.sort(function(a,b){
                        return b.price-a.price;
                    });
                    var sell_all=0;
                    var sell_avg=0;
                    for(var x in ana.sell){
                        var one=ana.sell[x];
                        sell_all-=-one.all;
                        sell_avg-=-one.amount;
                    }
                    sell_avg=sell_avg==0?0:sell_all/sell_avg;

                    var buy_all=0;
                    var buy_avg=0;
                    for(var x in ana.buy){
                        var one=ana.buy[x];
                        buy_all-=-one.all;
                        buy_avg-=-one.amount;
                    }
                    buy_avg=buy_avg==0?0:buy_all/buy_avg;

                    dlog(
                        (xc_c==0?"初始化 --- ":'')+
                        "in:"+re1.buy+';~~~:'+(re1.buy/2+re1.sell/2).toFixed(5)+'~~~ out:'+re1.sell,
                        " \n\r",
                        (buy_all>0?'\x1B[32m':''),' in_all:'+parseInt(buy_all)+', in_avg:'+buy_avg.toFixed(5)+' ; ','\x1B[37m',
                        (sell_all>0?'\x1B[33m':''),'out_all:'+parseInt(sell_all)+', out_avg:'+sell_avg.toFixed(5),'\x1B[37m'
                    );

                    var format_time=ts.format_date(one.date*1000,0,1);
                    var year=format_time[0]+'';
                    var month=format_time[1]+'';
                    var day=format_time[2]+'';
                    var hour=format_time[3]+'';

                    /*拼接保存的文本*/
                    format_time=year+'-'+month+'-'+day+' '+format_time[3]+':'+format_time[4]+':'+format_time[5];
                    var s=(xc_c==0?"初始化 --- \n":'')+";;;--------"+format_time+"--------\n"+
                        "in:"+re1.buy+';~~~:'+(re1.buy/2+re1.sell/2).toFixed(5)+'~~~ out:'+re1.sell+';'+
                        "\n"+
                        'in_all:'+parseInt(buy_all)+'; in_avg:'+buy_avg.toFixed(5)+'; '+
                        'out_all:'+parseInt(sell_all)+'; out_avg:'+sell_avg.toFixed(5)+';'+
                        "\n";
                    /*需要创建的文件路径*/
                    var f=path.join(temp_path,'arrange_data',coin,year,month+'_'+day,hour+'.log');
                    ts.mkdir_deep(path.dirname(f));/*创建文件夹*/
                    (function(nfile_name,save_text){
                        /*TODO::如果文件过大，则自动进行分段保存*/
                        fs.appendFile(nfile_name,save_text,'utf8',function(err){
                            if(err) {
                                console.log(err);
                                /*TODO::error log*/
                            }else{
                                //console.log('转储到:',nfile_name,'成功');
                            }
                            //task2.f(nfile_name);
                        });
                    })(f,s);
                }else{
                    console.log('http_get error',err1 || err2);
                }

                setTimeout(function(){
                    analyzer.loop(coin,delay1,delay2);
                },delay2);

            });
        },delay1);
    });
};

exports.start=analyzer.loop;
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
    ch_od:{},
    interval_timer:'',
    last_query_orders_time:0,
    last_query_orders_time2:0
};


analyzer.loop=function(coin,delay_orders){
    get_ticker.query(coin,function(re1,err1){
        //re1:交易价格信息
        if(!re1){
            ts.dclog('magenta','http_get get_ticker error',err1);
            setTimeout(function(){
                analyzer.loop(coin,delay_orders);
            },delay_orders);
            return ;
        }
        setTimeout(function(){
            get_orders.query(coin,function(re2,err2){
                //re2:最近100单交易信息
                if(re2){
                    var ch_od=analyzer.ch_od;
                    for(var x in re2){
                        var one=re2[x];
                        if(ch_od[one.tid]) continue;/*缓存中已有,跳过*/
                        one.all=one.amount*one.price;
                        ch_od[one.tid]=one;
                    }
                    ch_od=ts.slice_obj(ch_od,-179);/*仅缓存179条*/


                    var time_now=new Date().getTime();
                    if(!analyzer.last_query_orders_time) analyzer.last_query_orders_time=time_now-delay_orders;
                    //console.log('上次计算时间:',ts.format_date(analyzer.last_query_orders_time),' --- 现在时间:',ts.format_date(time_now));

                    /*分别统计买入卖出所有数据*/
                    var collect={buy:[],sell:[]};
                    for(var x in ch_od){
                        var one=ch_od[x];
                        var date=one.date*1000;
                        if(date>analyzer.last_query_orders_time && date<=time_now){
                            /*只计算上次计算到当前时间之间的数据*/
                            if(!collect[one.type]) collect[one.type]=[];
                            collect[one.type].push(one);
                        }
                    }


                    /*计算卖出总量和平均卖价*/
                    var sell_all=0;
                    var sell_avg=0;
                    for(var x in collect.sell){
                        var one=collect.sell[x];
                        sell_all-=-one.all;
                        sell_avg-=-one.amount;
                    }
                    sell_avg=sell_avg==0?0:sell_all/sell_avg;
                    /*计算买入总量和平均买价*/
                    var buy_all=0;
                    var buy_avg=0;
                    for(var x in collect.buy){
                        var one=collect.buy[x];
                        buy_all-=-one.all;
                        buy_avg-=-one.amount;
                    }
                    buy_avg=buy_avg==0?0:buy_all/buy_avg;

                    dlog(
                        "in:"+re1.buy+';~~~:'+(re1.buy/2+re1.sell/2).toFixed(5)+'~~~ out:'+re1.sell,
                        " \n\r",
                        (buy_all>0?'\x1B[32m':''),' in_avg:'+buy_avg.toFixed(5),' in_all:'+parseInt(buy_all),' \x1B[37m',
                        (sell_all>0?'\x1B[33m':''),'out_all:'+parseInt(sell_all),' out_avg:'+sell_avg.toFixed(5),' \x1B[37m'
                    );

                    var format_time=ts.format_date(time_now,0,1);
                    var year=format_time[0]+'';
                    var month=format_time[1]+'';
                    var day=format_time[2]+'';
                    var hour=format_time[3]+'';

                    /*拼接保存的文本*/
                    format_time=year+'-'+month+'-'+day+' '+format_time[3]+':'+format_time[4]+':'+format_time[5];
                    var save_text=";;;--------"+format_time+"--------\n"+
                        "buy:"+re1.buy+';~~~:'+(re1.buy/2+re1.sell/2).toFixed(5)+'~~~ sell:'+re1.sell+';'+
                        "\n"+
                        'buy_avg:'+buy_avg.toFixed(5)+'; buy_all:'+parseInt(buy_all)+'; '+
                        'sell_all:'+parseInt(sell_all)+'; sell_avg:'+sell_avg.toFixed(5)+';'+
                        "\nhigh:"+re1.high+';low:'+re1.low+';'+
                        "\n";
                    /*需要创建的文件路径*/
                    var nfile_name=path.join(temp_path,'arrange_data',coin,year,month+'_'+day,hour+'.log');
                    ts.mkdir_deep(path.dirname(nfile_name));/*创建文件夹*/
                    (function(f,s){
                        fs.appendFile(f,s,'utf8',function(err){
                            if(err) {
                                console.log(err);
                                /*TODO::error log*/
                            }else{
                                //console.log('转储到:',nfile_name,'成功');
                            }
                            //task2.f(nfile_name);
                        });
                    })(nfile_name,save_text);

                    analyzer.last_query_orders_time=time_now;
                }else{
                    ts.dclog('magenta','http_get get_orders error',err2);
                }
                setTimeout(function(){
                    analyzer.loop(coin,delay_orders);
                },delay_orders);
            });
        },500);
    });
};
analyzer.start=function(coin,delay_orders){
    var that=analyzer;
    that.loop(coin,delay_orders);
    //
    //that.interval_timer=setInterval(function(){
    //
    //},delay1-(-delay2));
};

exports.start=analyzer.start;
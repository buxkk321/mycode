var watcher=require('./watcher.js');
watcher.start('xrp',10000);

var http_server=require('./http_server.js');
http_server.restart();
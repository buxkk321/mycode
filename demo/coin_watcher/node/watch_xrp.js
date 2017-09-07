var watcher=require('./watcher.js');
watcher.start('xrp',3000,7000);

var http_server=require('./http_server.js');
http_server.restart();
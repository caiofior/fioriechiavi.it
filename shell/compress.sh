#!/bin/sh
java -jar /home/caiofior/bin/compiler.jar --js /home/caiofior/public_html/florae.it/js/common/common.js | gzip > /home/caiofior/public_html/florae.it/js/common/common.min.js.gz
java -jar /home/caiofior/bin/compiler.jar --js /home/caiofior/public_html/florae.it/js/index.js | gzip > /home/caiofior/public_html/florae.it/js/index.min.js.gz
java -jar /home/caiofior/bin/compiler.jar --js /home/caiofior/public_html/florae.it/js/search.js | gzip > /home/caiofior/public_html/florae.it/js/search.min.js.gz
java -jar /home/caiofior/bin/compiler.jar --js /home/caiofior/public_html/florae.it/js/user.js | gzip > /home/caiofior/public_html/florae.it/js/user.min.js.gz
java -jar /home/caiofior/bin/compiler.jar --js /home/caiofior/public_html/florae.it/js/signalObservation.js | gzip > /home/caiofior/public_html/florae.it/js/signalObservation.min.js.gz
java -jar /home/caiofior/bin/compiler.jar --js /home/caiofior/public_html/florae.it/js/observation.js | gzip > /home/caiofior/public_html/florae.it/js/observation.min.js.gz
yui-compressor /home/caiofior/public_html/florae.it/template/leaf/css/style-cleaned.css | gzip > /home/caiofior/public_html/florae.it/template/leaf/css/style-cleaned.css.gz
yui-compressor /home/caiofior/public_html/florae.it/template/leaf/css/bootstrap.css | gzip > /home/caiofior/public_html/florae.it/template/leaf/css/bootstrap.css.gz
yui-compressor /home/caiofior/public_html/florae.it/template/plantbook/css/style-cleaned.css | gzip > /home/caiofior/public_html/florae.it/template/plantbook/css/style-cleaned.css.gz
yui-compressor /home/caiofior/public_html/florae.it/template/plantbook/css/bootstrap.css | gzip > /home/caiofior/public_html/florae.it/template/plantbook/css/bootstrap.css.gz
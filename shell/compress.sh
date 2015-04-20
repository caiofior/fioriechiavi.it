#!/bin/sh
java -jar /home/caiofior/bin/compiler.jar --js /home/caiofior/Pubblici/fioriechiavi.it/js/common/common.js | gzip > /home/caiofior/Pubblici/fioriechiavi.it/js/common/common.min.js.gz
java -jar /home/caiofior/bin/compiler.jar --js /home/caiofior/Pubblici/fioriechiavi.it/js/index.js | gzip > /home/caiofior/Pubblici/fioriechiavi.it/js/index.min.js.gz
java -jar /home/caiofior/bin/compiler.jar --js /home/caiofior/Pubblici/fioriechiavi.it/js/search.js | gzip > /home/caiofior/Pubblici/fioriechiavi.it/js/search.min.js.gz
java -jar /home/caiofior/bin/compiler.jar --js /home/caiofior/Pubblici/fioriechiavi.it/js/user.js | gzip > /home/caiofior/Pubblici/fioriechiavi.it/js/user.min.js.gz
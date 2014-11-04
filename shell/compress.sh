#!/bin/sh
java -jar /home/caiofior/bin/compiler.jar --js /home/caiofior/Pubblici/fioriechiavi.it/js/common/common.js | gzip > /home/caiofior/Pubblici/fioriechiavi.it/js/common/common.min.js.gz
java -jar /home/caiofior/bin/compiler.jar --js /home/caiofior/Pubblici/fioriechiavi.it/js/general.js | gzip > /home/caiofior/Pubblici/fioriechiavi.it/js/general.min.js.gz
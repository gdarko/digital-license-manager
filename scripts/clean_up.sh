#!/bin/bash

BASEDIR=$(dirname "$0")
cd $BASEDIR

if [[ ! -f "../vendor/tecnickcom/tcpdf/fonts/.reduced" ]]; then
    rm -rf _tmp
    mkdir _tmp
    cp -r ../vendor/tecnickcom/tcpdf/fonts _tmp/fonts
    rm -rf ../vendor/tecnickcom/tcpdf/fonts/*
    cp _tmp/fonts/courier.* ../vendor/tecnickcom/tcpdf/fonts
    cp _tmp/fonts/helvetica.* ../vendor/tecnickcom/tcpdf/fonts
    rm -rf _tmp
    touch ../vendor/tecnickcom/tcpdf/fonts/.reduced
    echo 'Clean up done.';
else
    echo "Clean up not needed."
fi

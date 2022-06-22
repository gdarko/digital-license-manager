#!/bin/bash

BASEDIR=$(dirname "$0")
cd $BASEDIR

fonts_count=$(ls -l ../vendor/tecnickcom/tcpdf/fonts/ | wc -l)

echo "Found $fonts_count fonts. Cleaning up..."

if [ $fonts_count -gt 3 ]; then
    rm -rf _tmp
    mkdir _tmp
    cp -r ../vendor/tecnickcom/tcpdf/fonts _tmp/fonts
    rm -rf ../vendor/tecnickcom/tcpdf/fonts/*
    cp _tmp/fonts/courier.* ../vendor/tecnickcom/tcpdf/fonts
    cp _tmp/fonts/helvetica.* ../vendor/tecnickcom/tcpdf/fonts
    rm -rf ../vendor/tecnickcom/tcpdf/examples
    rm -rf ../vendor/spipu/html2pdf/examples
    rm -rf _tmp
    echo 'Clean up done.';
else
    echo "Clean up not needed."
fi

#!/bin/bash

SCRIPT_DIR="$( cd -- "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"
PLUGIN_DIR="$( cd -- "$(dirname "$SCRIPT_DIR")" >/dev/null 2>&1 ; pwd -P )"
PLUGINS_ROOT_DIR="$( cd -- "$(dirname "$PLUGIN_DIR")" >/dev/null 2>&1 ; pwd -P )"
TMPDIR="$PLUGIN_DIR/_tmp"

cd $PLUGIN_DIR

fonts_count=$(ls -l "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts/" | wc -l)

echo "Found $fonts_count fonts. Cleaning up..."

if [ $fonts_count -gt 3 ]; then
    $(rm -rf $TMPDIR)
    $(mkdir $TMPDIR)
    $(cp -r "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts" "$TMPDIR/fonts")
    $(rm -rf "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(mkdir -p  "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    # Basic fonts
    $(cp "$TMPDIR/fonts/courier".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/helvetica".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/times".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    # Unicode fonts (multi-script support)
    $(cp "$TMPDIR/fonts/dejavusans".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/dejavuserif".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/freesans".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/freeserif".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    # Arabic fonts
    $(cp "$TMPDIR/fonts/aealarabiya".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/aefurat".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    # CJK fonts
    $(cp "$TMPDIR/fonts/cid0cs".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/cid0ct".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/cid0jp".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/cid0kr".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/stsongstdlight".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/msungstdlight".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/kozminproregular".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/kozgopromedium".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    $(cp "$TMPDIR/fonts/hysmyeongjostdmedium".* "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/fonts")
    # Examples
    $(rm -rf "$PLUGIN_DIR/vendor/tecnickcom/tcpdf/examples")
    $(rm -rf "$PLUGIN_DIR/vendor/spipu/html2pdf/examples")
    $(rm -rf "$TMPDIR")
    echo 'Clean up done.';
else
    echo "Clean up not needed."
fi

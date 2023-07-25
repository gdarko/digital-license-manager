/**
 * Copyright (C) 2020-2023 Darko Gjorgjijoski <https://darkog.com>
 * All Rights Reserved.
 * Licensed under GPLv3.
 */

window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {

    /**
     * Utilities class
     * @constructor
     */
    window.DLM.Utils = function () {
    }

    /**
     * Find a previous element
     * @param el
     * @param selector
     * @returns {undefined|Element}
     */
    window.DLM.Utils.Prev = function (el, selector) {
        if (selector) {
            const prev = el.previousElementSibling;
            if (prev && prev.matches(selector)) {
                return prev;
            }
            return undefined;
        } else {
            return el.previousElementSibling;
        }
    }

});

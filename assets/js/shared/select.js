/**
 * Copyright (C) 2020-2023 Darko Gjorgjijoski <https://darkog.com>
 * All Rights Reserved.
 * Licensed under GPLv3.
 */

document.addEventListener("DOMContentLoaded", function (event) {

    /**
     * Select class
     * @param selector
     * @param config
     * @constructor
     */
    window.DLM.Select = function (selector, config) {

        if ('string' === typeof selector) {
            selector = document.querySelector(selector);
        }

        this.init(selector, config);
    }

    /**
     * Initializes the select element
     * @param selector
     * @param config
     */
    window.DLM.Select.prototype.init = function (selector, config) {
        var options = config ? config : {}
        var plugins = ['clear_button', 'dropdown_input', 'remove_button']

        if (!selector.attributes.hasOwnProperty('multiple')) {
            plugins.push('no_backspace_delete');
            plugins.maxItems = 1;
        }

        if (options.hasOwnProperty('remote')) {
            plugins.push('virtual_scroll');
            var remote = options.remote;
            options.labelField = config.hasOwnProperty('labelField') ? config.hasOwnProperty('labelField') : 'text';
            options.searchField = config.hasOwnProperty('searchField') ? config.hasOwnProperty('searchField') : ['text', 'meta'];
            options.valueField = config.hasOwnProperty('valueField') ? config.hasOwnProperty('valueField') : 'id';
            options.plugins = plugins;
            options.maxOptions = 200;
            options.firstUrl = function (query) {
                var url = remote.url;
                url += remote.url.indexOf('?') === -1 ? '?' : '&';
                return url + 'action=' + remote.action + '&security=' + remote.nonce + '&type=' + remote.type + '&term=' + encodeURIComponent(query);
            }
            options.load = function (query, callback) {
                const url = this.getUrl(query);
                fetch(url)
                    .then(response => response.json())
                    .then(json => {
                        if (json && json.hasOwnProperty('pagination') && json.pagination.hasOwnProperty('more') && json.pagination.more) {
                            var next_page = json.pagination.current + 1;
                            var next_url = remote.url + (remote.url.indexOf('?') === -1 ? '?' : '&');
                            next_url += 'action=' + remote.action + '&security=' + remote.nonce + '&type=' + remote.type + '&term=' + encodeURIComponent(query) + '&page=' + next_page
                            this.setNextUrl(query, next_url);
                        }
                        callback(json.results);
                    }).catch((e) => {
                    callback();
                });
            }
            options.render = {
                loading_more: function (data, escape) {
                    return '<div class="loading-more-results py-2 d-flex align-items-center"><div class="spinner"></div> ' + dlm_select_i18n.loading + '</div>';
                }
            };
            delete options.remote;
        }
        this.select = new TomSelect(selector, options)
    }

});
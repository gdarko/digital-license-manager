/**
 * Copyright (C) 2020-2023 Darko Gjorgjijoski <https://darkog.com>
 * All Rights Reserved.
 * Licensed under GPLv3.
 */

window.DLM = window.hasOwnProperty('DLM') ? window.DLM : {};

document.addEventListener("DOMContentLoaded", function (event) {

    /**
     * A simple Vanilla Javascript Http client.
     * @author Darko Gjorgjioski <dg@darkog.com>
     * @copyright 2023
     *
     * @param params
     * @constructor
     */
    const Http = function (params) {
        this.params = params;
    }

    /**
     * Sends a http request
     * @param type
     * @param url
     * @param params
     */
    Http.prototype.request = function (type, url, params) {
        let self = this;
        let request = new XMLHttpRequest();
        let formData = null;
        let data = params.hasOwnProperty('data') && params.data ? params.data : {};
        switch (type.toUpperCase()) {
            case 'GET':
                let query = new URLSearchParams(data).toString();
                url = url.indexOf('?') === -1 ? url + '?' + query : url + '&' + query;
                request.open(type, url, true);
                break;
            case 'POST':
                if (!(data instanceof FormData)) {
                    formData = new FormData();
                    for (let key in data) {
                        formData.append(key, data[key]);
                    }
                } else {
                    formData = data;
                }
                request.open(type, url, true);
                break;
        }
        let headers = params.hasOwnProperty('headers') && params.headers ? params.headers : {};
        for (let key in headers) {
            request.setRequestHeader(key, headers[key]);
        }
        request.onreadystatechange = function () {
            if (request.readyState === request.DONE) {
                let headers = self.parseHeaders(request);
                if (request.status >= 200 && request.status <= 299) {
                    if (params.hasOwnProperty('success')) {
                        const response = headers.hasOwnProperty('content-type') && headers['content-type'].substring('application/json') !== -1
                            ? JSON.parse(request.responseText) : request.responseText;
                        params.success(response, request.status, headers);
                    }
                } else {
                    if (params.hasOwnProperty('error')) {
                        params.error(request.responseText, request.status, headers);
                    }
                }
                if(params.hasOwnProperty('complete')) {
                    params.complete(request.responseText, request.status, headers)
                }
            }
        };
        if (params.hasOwnProperty('beforeStart')) {
            params.beforeStart();
        }
        if (null !== formData) {
            request.send(formData);
        } else {
            request.send();
        }
    }

    /**
     * Sends a GET request
     * @param url
     * @param params
     */
    Http.prototype.get = function (url, params) {
        this.request('GET', url, params);
    }

    /**
     * Sends a GET request
     * @param url
     * @param params
     */
    Http.prototype.post = function (url, params) {
        this.request('POST', url, params);
    }

    /**
     * Parses the headers
     * @param request
     * @returns {{}}
     */
    Http.prototype.parseHeaders = function (request) {
        const headers = request.getAllResponseHeaders();
        const arr = headers.trim().split(/[\r\n]+/);
        const headerMap = {};
        arr.forEach((line) => {
            let parts = line.split(': ');
            let header = parts.shift();
            headerMap[header.toLowerCase()] = parts.join(': ');
        });
        return headerMap
    }

    window.DLM.Http = Http;

});

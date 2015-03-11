/*
    Capo, a web interface for querying multiple Cacti instances
    Copyright (C) 2013  Jochem Kossen <jochem@jkossen.nl>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Define namespace
var CAPO = CAPO || {};

////////////////////////////////////////////////////////////////////////////
// BASE
// Add some functionality to the namespace itself
////////////////////////////////////////////////////////////////////////////
(function(ns) {
    "use strict";
    var _cfg = {
        'request_method': 'POST'
    };

    var init = function(cfg) {
        $.extend(_cfg, cfg);
        enable_ajax_spinner();
    };

    var get = function(key) {
        return _cfg[key];
    };

    var set = function(key, value) {
        _cfg[key] = value;
    };

    var ajax_spinner_opts = {
        lines: 11, // The number of lines to draw
        length: 4, // The length of each line
        width: 2, // The line thickness
        radius: 5, // The radius of the inner circle
        corners: 1, // Corner roundness (0..1)
        rotate: 0, // The rotation offset
        direction: 1, // 1: clockwise, -1: counterclockwise
        color: '#000000', // #rgb or #rrggbb
        speed: 1, // Rounds per second
        trail: 60, // Afterglow percentage
        shadow: false, // Whether to render a shadow
        hwaccel: false, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2e9, // The z-index (defaults to 2000000000)
        top: '-25px', // Top position relative to parent in px
        left: '-10px' // Left position relative to parent in px
    };

    var selection_spinner_opts = {
        lines: 11, // The number of lines to draw
        length: 4, // The length of each line
        width: 2, // The line thickness
        radius: 5, // The radius of the inner circle
        corners: 1, // Corner roundness (0..1)
        rotate: 0, // The rotation offset
        direction: 1, // 1: clockwise, -1: counterclockwise
        color: '#000000', // #rgb or #rrggbb
        speed: 1, // Rounds per second
        trail: 60, // Afterglow percentage
        shadow: false, // Whether to render a shadow
        hwaccel: false, // Whether to use hardware acceleration
        className: 'spinner', // The CSS class to assign to the spinner
        zIndex: 2e9, // The z-index (defaults to 2000000000)
        top: '25px', // Top position relative to parent in px
        left: '25px' // Left position relative to parent in px
    };

    var show_error = function(msg) {
        var tpl = '<div class="alert alert-danger" role="alert">' +
            '<button type="button" class="close" data-dismiss="alert">' +
            '<span aria-hidden="true">&times;</span>' +
            '<span class="sr-only">Close</span></button>' +
            '<p><strong>ERROR:</strong> <%= message %></p>';

        var out = _.template(tpl);

        $('#error-container')
            .append(out({
                'message': msg
            }));
    };

    // Show a spinner when an ajax call is running
    var enable_ajax_spinner = function() {

        // Show the loading spinner when AJAX requests run
        $(document)
            .ajaxStart(function() {
                $('#search_is_loading').toggle();
                $('#loading-div').spin(ajax_spinner_opts);
            })
            .ajaxStop(function() {
                $('#search_is_loading').toggle();
                $('#loading-div').stopspin();
                $('#selection-loading').stopspin();
            });
    };

    var start_selection_spinner = function() {
        $('#selection-loading').spin(selection_spinner_opts);
    };

    // Create object and event handler for infinite scrollable containers
    var create_infinite_scroller = function(element, fn_load) {
        var scroller = new Object();
        scroller.page = 1;
        scroller.pos = 0;
        scroller.per_page = 100;
        scroller.lock = false;
        scroller.element = element;
        scroller.total = -1;

        scroller.unlock = function() {
            setTimeout(function() {
                scroller.lock = false;
                scroller.updated_while_loading()
            }, 500);
        }

        scroller.reset = function() {
            scroller.page = 1;
            scroller.pos = 0;
            scroller.lock = false;
            scroller.total = -1;
        }

        scroller.load_next = function() {
            // Get the height of the div
            var height = scroller.element.prop('scrollHeight') - scroller.element.height();

            // Get the vertical scroll position
            scroller.pos = scroller.element.scrollTop();

            var isScrolledToEnd = (scroller.pos >= (height - 250));

            if (scroller.total !== -1 &&
                (scroller.page * scroller.per_page) > scroller.total) {
                return;
            }

            if (isScrolledToEnd && scroller.lock === false) {
                scroller.lock = true;
                scroller.page++;
                fn_load(false);
            }
        }

        scroller.updated_while_loading = function() {
            if (scroller.element.scrollTop() >= scroller.pos) {
                scroller.load_next();
            }
        }

        // Event handler infinite scrolling of the graph results list
        element.on('scroll', function (event) {
            event.preventDefault();
            scroller.load_next();
        });

        return scroller;
    };

    // Delay a function call by a specified amount of milliseconds
    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();


    // Export public functions
    ns.init = init;
    ns.show_error = show_error;
    ns.start_selection_spinner = start_selection_spinner;
    ns.get = get;
    ns.set = set;
    ns.create_infinite_scroller = create_infinite_scroller;
    ns.delay = delay;
}(CAPO));

//
// jQuery extension for the ajax loading spinner
//
$.fn.spin = function(opts) {
    this.each(function() {
        var $this = $(this),
        data = $this.data();

        if (data.spinner) {
            data.spinner.stop();
            delete data.spinner;
        }

        if (opts !== false) {
            data.spinner = new Spinner($.extend({
                color: $this.css('color')
            }, opts)).spin(this);
        }
    });
    return this;
};

$.fn.stopspin = function() {
    this.each(function() {
        var $this = $(this),
        data = $this.data();

        if (data.spinner) {
            data.spinner.stop();
            delete data.spinner;
        }
    });
    return this;
};

$.fn.animateHighlight = function() {
    this.fadeOut(150);
    this.fadeIn(150);
};

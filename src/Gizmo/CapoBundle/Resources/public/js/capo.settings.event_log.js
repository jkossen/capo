////////////////////////////////////////////////////////////////////////////
// CAPO.settings.event_log sub namespace
// Functionality for the 'settings / eventlog' screen
////////////////////////////////////////////////////////////////////////////
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

CAPO.settings = CAPO.settings || {};
CAPO.settings.event_log = CAPO.settings.event_log || {};

(function(ns) {
    "use strict";
    var _scroller = null;

    //
    // Load required HTML templates
    //
    var tpl_eventlog_list_item = ns.html.eventlog_list_item();

    var init = function() {
        // Prevent submitting the search form on enter
        $('#search-form').on('submit', function(event) {
            event.preventDefault();
        });

        enable_search_event_log_input();
        enable_event_log();
    };

    var select2_graph_selection_format = function (item) {
        return item.name + ' (' + item.created.date + ')';
    };

    var refresh_results = function() {
        _scroller.reset();
        load_results(true);
    };

    // Load a set of cacti instance search results
    var load_results = function(clear) {
        $.ajax({
            url: ns.get('base_url') + 'api/admin/get_event_log/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                page: _scroller.page,
                page_limit: _scroller.per_page,
                q: $('#filter').val(),
            },
            success: function(response, textStatus, jqXHR) {
                _scroller.total = response.loglines_total;

                if (clear) {
                    $('#results').scrollTop(0);
                    $('#results-list').empty();
                }

                $.each(response.loglines, function(index, logline) {
                    var custom_data = JSON.parse(logline.custom_data);
                    $('#results-list').append(
                        tpl_eventlog_list_item({
                            'date': logline.event_date.date,
                            'username': logline.user_name,
                            'userid': logline.user_id,
                            'client_ip': logline.client_ip,
                            'request_uri': logline.request_uri,
                            'message': custom_data.message
                        })
                    );
                });

                $('#result-count')
                    .html('matches: ' + _scroller.total);
                _scroller.unlock();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                _scroller.unlock();
                var ret = $.parseJSON(jqXHR.responseText);
                ns.show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
    };

    // Refresh cacti instances results list when typing in the search input box
    var enable_search_event_log_input = function() {
        var filter_prev_len = 0;
        var filter_prev = '';

        $('#filter').on('keyup', function(event) {
            event.preventDefault();
            ns.delay(function() {
                var filter_cur = $('#filter').val();
                if ((filter_cur.length === 0 && filter_prev.length !== 0) ||
                   (filter_cur !== filter_prev && filter_cur.length >= 2)) {
                        refresh_results();
                        filter_prev = filter_cur;
                }
            }, 500);
        });
    };

    // Enable the resulting cacti instances list
    var enable_event_log = function() {
        _scroller = ns.create_infinite_scroller($('#results'), load_results);
        // Initial load of the cacti instances list
        refresh_results();
    };

    ns.settings.event_log.init = init;
}(CAPO));

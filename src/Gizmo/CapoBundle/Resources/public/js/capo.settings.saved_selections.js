////////////////////////////////////////////////////////////////////////////
// CAPO.settings.saved_selections sub namespace
// Functionality for the 'settings - saved selections' screen
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
CAPO.settings.saved_selections = CAPO.settings.saved_selections || {};

(function(ns) {
    "use strict";
    var _scroller = null;

    //
    // Load required HTML templates
    //
    var tpl_error_msg = ns.html.msg_container('error');
    var tpl_saved_selection_list_item = ns.html.saved_selection_list_item();

    var show_error = function(msg) {
        return $('#error_container').append(
            tpl_error_msg({
                'msg': msg
            }));
    };

    var init = function() {
        enable_saved_selections_select();
        enable_delete_saved_selection_btn();
        enable_rename_saved_selection_btn();
        _scroller = ns.create_infinite_scroller($('#results'),
                                                load_selection_graphs);
    };

    var select2_graph_selection_format = function (item) {
        return item.name + ' (' + item.created.date + ')';
    };

    var enable_saved_selections_select = function() {
        $('.saved_selections_select').select2({
            placeholder: 'Select graph selection',
            allowClear: false,
            width: '350px',
            height: '400px',
            minimumInputLength: 0,
            ajax: {
                url: ns.get('base_url') + 'api/get_graph_selections/',
                type: ns.get('request_method'),
                dataType: 'json',
                quietMillis: 100,
                data: function(term, page) {
                    return {
                        q: term,
                        page_limit: 25,
                        page: page
                    };
                },
                results: function (data, page) {
                    // whether or not there are more results available
                    var more = (page * 25) < data.graph_selections_total;

                    // notice we return the value of more so Select2 knows if
                    // more results can be loaded
                    return { results: data.graph_selections, more: more };
                }
            },
            formatResult: select2_graph_selection_format,
            formatSelection: select2_graph_selection_format,
            dropdownCssClass: "bigdrop" // apply css that makes the dropdown taller
        }).on('change', function(event) {
            event.preventDefault();
            if ($(this).val() !== '') {
                refresh_selection_graphs();
            }
        });
    };

    var enable_delete_saved_selection_btn = function() {
        $('#btn_delete_saved_selection').on('click', function(event) {
            event.preventDefault();

            if ($('#saved_selections_select').val() === '') {
                return;
            }

            var q = 'Are you sure you want to delete this graph selection?';
            if (confirm(q)) {
                $.ajax({
                    url: ns.get('base_url') + 'api/disable_graph_selection/',
                    type: ns.get('request_method'),
                    dataType: 'json',
                    data: {
                        graph_selection: $('#saved_selections_select').val()
                    },
                    success: function(response, textStatus, jqXHR) {
                        $('#saved_selections_select').select2('data', null);
                        $('#results_list').empty();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        show_error('Unable to delete');
                    }
                });
            }
        });
    };

    var enable_rename_saved_selection_btn = function() {
        $('#btn-rename-graph-selection').on('click', function(event) {
            event.preventDefault();
            if ($('#saved_selections_select').val() === '') {
                return;
            }
            $.ajax({
                url: ns.get('base_url') + 'api/rename_graph_selection/',
                type: ns.get('request_method'),
                dataType: 'json',
                data: {
                    graph_selection: $('#saved_selections_select').val(),
                    name: $('#graph-selection-new-name').val()
                },
                success: function(response, textStatus, jqXHR) {
                    $('#saved_selections_select').select2('data', null);
                    $('#graph-selection-new-name').val('');
                    $('#results_list').empty();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    show_error('Unable to rename');
                }
            });
        });
    };

    var refresh_selection_graphs = function() {
        _scroller.reset();
        load_selection_graphs(true);
    };

    // Load a set of cacti instance search results
    var load_selection_graphs = function(clear) {
        $.ajax({
            url: ns.get('base_url') + 'api/get_graph_selection_graphs/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                page: _scroller.page,
                page_limit: _scroller.per_page,
                graph_selection_id: $('#saved_selections_select').val(),
            },
            success: function(response, textStatus, jqXHR) {
                _scroller.total = response.graphs_total;

                if (clear) {
                    $('#results').scrollTop(0);
                    $('#results_list').empty();
                }

                $.each(response.graph_selection[0].graphs,
                       function(index, graph) {
                           var graph_url = graph.cacti_instance.base_url +
                               'graph.php?local_graph_id=' +
                               graph.graph_local_id;

                           $('#results_list')
                               .append(tpl_saved_selection_list_item({
                                   'ci_name': graph.cacti_instance.name,
                                   'graph_url': graph_url,
                                   'graph_title': graph.title_cache
                               }));
                       });

                $('#result_count')
                    .html('matches: ' + _scroller.total);
                _scroller.unlock();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                _scroller.unlock();
                var ret = $.parseJSON(jqXHR.responseText);
                show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
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
                q: $('#filter_1').val(),
            },
            success: function(response, textStatus, jqXHR) {
                _scroller.total = response.loglines_total;

                if (clear) {
                    $('#results').scrollTop(0);
                    $('#results_list').empty();
                }

                $.each(response.loglines, function(index, logline) {
                    var custom_data = JSON.parse(logline.custom_data);
                    $('#results_list').append(
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

                $('#result_count')
                .html('matches: ' + _scroller.total);
                _scroller.unlock();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                _scroller.unlock();
                var ret = $.parseJSON(jqXHR.responseText);
                show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
    };

    ns.settings.saved_selections.init = init;
}(CAPO));

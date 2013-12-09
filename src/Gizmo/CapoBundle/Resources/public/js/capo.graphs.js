////////////////////////////////////////////////////////////////////////////
// CAPO.graphs sub namespace
// Functionality for the 'graphs' screen
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

CAPO.graphs = CAPO.graphs || {};
(function(ns) {
    "use strict";
    var _scroller = null;
    var _filter_1_prev = '';
    var _max_select_all = 25;
    var _graph_pool = [];
    var _cur_saved_selection = [];
    var _rra_id = 1;

    //
    // select2 result formatting functions
    //
    var select2_default_format = function(item) {
        return item.name;
    };

    var select2_host_format_result = function (item) {
        return item.description + ' (' + item.hostname + ')';
    };

    var select2_host_format_selection = function (item) {
        return item.description + ' (' + item.hostname + ')';
    };

    var select2_graph_selection_format = function (item) {
        return item.name + ' (' + item.created.date + ')';
    };

    //
    // Load HTML templates
    //
    var tpl_graph_result_list_item_selected = ns.html.graph_result_list_item(true);
    var tpl_graph_result_list_item_deselected = ns.html.graph_result_list_item(false);
    var tpl_graph_selection_to_pdf_form = ns.html.graph_selection_to_pdf_form();
    var tpl_single_graph_to_pdf_form = ns.html.single_graph_to_pdf_form();
    var tpl_error_msg = ns.html.msg_container('error');
    var tpl_select_all_graphs = ns.html.select_all_graphs();
    var tpl_select_all_max_warning = ns.html.select_all_max_warning();
    var tpl_selected_graph_item  = ns.html.selected_graph_item();
    var tpl_graph_link_selected = ns.html.graph_link_selected();
    var tpl_graph_link_deselected = ns.html.graph_link_deselected();

    //
    // real work functions [tm]
    //

    var show_error = function(msg) {
        $('#error_container')
            .append(tpl_error_msg({
                'message': msg
            }));
    };

    // ensure equal height for the selected graph items
    function equal_height(group) {
        var tallest = 0;
        group.each(function() {
            var thisHeight = $(this).height();
            if(thisHeight > tallest) {
                tallest = thisHeight;
            }
        });

        $('.thumbnail').each(function() { $(this).height(tallest + 30); });
    }

    // Export the graph selection to PDF
    var pdf_graph_selection = function() {
        var graphs = _.map(
            $('.graph-img'),
            function(graph) {
                return graph.id.replace('graph-', '');
            });

        if (graphs.length > 0) {
            var form_id = 'graph-selection-to-pdf-form';
            $('#graph-selection-container').append(
                tpl_graph_selection_to_pdf_form({
                    'action': ns.get('base_url') + 'pdf/graph_selection/',
                    'form_id': form_id
                }));

            $('#input-graphs-selected')
            .attr('value', JSON.stringify(graphs));

            $('#input-rra-id')
            .attr('value', _rra_id);

            $('#' + form_id).submit();
        }
    };

    // Export a single graph to PDF
    var pdf_single_graph = function(graph_id) {
        var form_id = 'single-graph-to-pdf-form-' + graph_id;
        $('#' + form_id).remove();

        $('#graph-selection-container').append(
            tpl_single_graph_to_pdf_form({
                'action': ns.get('base_url') + 'pdf/single_graph/',
                'form_id': form_id,
                'graph_id': graph_id
            })
        );

        $('#' + form_id).submit();
    };

    // Enable the Cacti instances select box
    var enable_cacti_instance_select = function() {
        $('.cacti_instance_select').select2({
            placeholder: 'Any cacti instance',
            allowClear: true,
            width: '300px',
            height: '400px',
            minimumInputLength: 0,
            ajax: {
                url: ns.get('base_url') + 'api/get_cacti_instances/',
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
                    var more = (page * 25) < data.cacti_instances_total;

                    // notice we return the value of more so Select2 knows if
                    // more results can be loaded
                    return { results: data.cacti_instances, more: more };
                }
            },
            formatResult: select2_default_format,
            formatSelection: select2_default_format,
            dropdownCssClass: "bigdrop" // apply css that makes the dropdown taller
        })
        .on('change', function(event) {
            event.preventDefault();
            load_results(true);
        });
    };

    // Enable the saved graph selections select box
    var enable_graph_selections_select = function() {
        $('.graph_selections_select').select2({
            placeholder: 'Load saved selection',
            allowClear: true,
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
        })
        .on('change', function(event) {
            event.preventDefault();
            load_saved_selection();
        });

        $('#btn_save_graph_selection').on('click', function(event) {
            event.preventDefault();
            var name = $('#graph_selection_name').val();
            var graphs = [];

            $('#selected-graphs-list').children().each(function() {
                var graph_id = parseInt(this.id.split('-').pop());
                graphs.push(graph_id);
            });

            if (name !== '' && graphs.length > 0) {
                 $.ajax({
                    url: ns.get('base_url') + 'api/save_graph_selection/',
                    type: ns.get('request_method'),
                    dataType: 'json',
                    data: {
                        name: name,
                        graphs: JSON.stringify(graphs)
                    },
                    success: function(response, textStatus, jqXHR) {
                        $('#graph_selection_name').val('');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        show_error(jqXHR.status + ' ' + errorThrown +
                        '. Unable to save graph selection.'
                        );
                    }
                });
            }
        });
    };

    // Enable the Graph templates select box
    var enable_graph_templates_select = function() {
        $('.graph_template_select').select2({
            placeholder: 'Any graph template',
            allowClear: true,
            width: '400px',
            minimumInputLength: 0,
            ajax: {
                url: ns.get('base_url') + 'api/get_graph_templates/',
                type: ns.get('request_method'),
                dataType: 'json',
                quietMillis: 100,
                data: function(term, page) {
                    return {
                        q: term,
                        page_limit: 25,
                        page: page,
                        cacti_instance: $('#cacti_instance_1').val()
                    };
                },
                results: function (data, page) {
                    // whether or not there are more results available
                    var more = (page * 25) < data.graph_templates_total;

                    // notice we return the value of more so Select2 knows if
                    // more results can be loaded
                    return { results: data.graph_templates, more: more };
                }
            },
            id: select2_default_format,
            formatResult: select2_default_format,
            formatSelection: select2_default_format,
            dropdownCssClass: "bigdrop" // apply css that makes the dropdown taller
        })
        .on('change', function(event) {
            event.preventDefault();
            load_results(true);
        });
    };

    // Enable the Hosts select box
    var enable_hosts_select = function() {
        $('.host_select').select2({
            placeholder: 'Any host',
            allowClear: true,
            width: '400px',
            minimumInputLength: 0,
            ajax: {
                url: ns.get('base_url') + 'api/get_hosts/',
                type: 'POST',
                dataType: 'json',
                quietMillis: 100,
                data: function(term, page) {
                    return {
                        q: term,
                        page_limit: 25,
                        page: page,
                        cacti_instance: $('#cacti_instance_1').val()
                    };
                },
                results: function (data, page) {
                    // whether or not there are more results available
                    var more = (page * 25) < data.hosts_total;

                    // notice we return the value of more so Select2 knows if
                    // more results can be loaded
                    return { results: data.hosts, more: more };
                }
            },
            formatResult: select2_host_format_result,
            formatSelection: select2_host_format_selection,
            dropdownCssClass: "bigdrop" // apply css that makes the dropdown taller
        })
        .on('change', function(event) {
            event.preventDefault();
            load_results(true);
        });
    };

    // Enable the resulting graphs list
    var enable_graph_results = function() {
        _scroller = ns.create_infinite_scroller($('#results'), load_results);

        // Initial load of the graphs list
        load_results(true);
    };

    // Event handler for the search input
    var handle_search_input = function(fn_handler) {
        var filter_1_cur = $('#filter_1').val();

        if  (
            (filter_1_cur.length === 0 && _filter_1_prev.length !== 0) ||
            (filter_1_cur !== _filter_1_prev && filter_1_cur.length >= 2)
        ) {
            load_results(true);
            _filter_1_prev = filter_1_cur;
        }
    };

    // Refresh graph results list when typing in the search input box
    var enable_search_input = function() {
        $('#filter_1').on('keyup', function(event) {
            event.preventDefault();
            ns.delay(function() { handle_search_input(); }, 500);
        });
    };

    // Load saved graph selection
    var load_saved_selection = function() {
        var selector_id = 'graph-selections-select-1';
        if ($('#' + selector_id).val() === '') {
            return;
        }

        $.ajax({
            url: ns.get('base_url') + 'api/get_graph_selection_graphs/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                page: _scroller.page,
                page_limit: _scroller.per_page,
                graph_selection_id: $('#' + selector_id).val()
            },
            success: function(response, textStatus, jqXHR) {
                deselect_all_graphs();
                if (response.graph_selection.length > 0) {
                    $.each(response.graph_selection[0].graphs,
                           function(index, graph) {
                               _graph_pool[graph.id] = graph;
                               _cur_saved_selection.push(graph.id);
                               select_graph(graph.id);
                           });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                show_error(jqXHR.status + ' ' + errorThrown +
                           '. Unable to fetch graphs.'
                          );
            }
        });
    };

    // Load a set of graph search results
    var load_results = function(clear) {
        $.ajax({
            url: ns.get('base_url') + 'api/get_graphs/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                page: _scroller.page,
                page_limit: _scroller.per_page,
                cacti_instance: $('#cacti_instance_select_1').val(),
                graph_template: $('#template_select_1').val(),
                host: $('#host_select_1').val(),
                q: $('#filter_1').val()
            },
            success: function(response, textStatus, jqXHR) {
                if (clear) {
                    _scroller.reset();
                    _scroller.total = response.graphs_total;
                    _graph_pool = [];
                    $('#results').scrollTop(0);
                    $('#results-list').empty();
                }

                $.each(response.graphs, function(index, graph) {
                    _graph_pool[graph.id] = graph;

                    var selected = ($('#graph-selected-' + graph.id).length > 0) ? true : false;
                    var in_saved_selection = ($.inArray(graph.id, _cur_saved_selection) != -1) ? true : false;
                    var graph_link_id = 'graph-link-' + graph.id;
                    var tpl_graph_result_list_item = (selected) ? tpl_graph_result_list_item_selected : tpl_graph_result_list_item_deselected;

                    $('#results-list').append(
                        tpl_graph_result_list_item({
                            'class_selection': (in_saved_selection) ? 'current_selection' : '',
                            'ci_name': graph.cacti_instance.name,
                            'graph_id': graph.id,
                            'graph_link_id': graph_link_id,
                            'graph_name': graph.title_cache
                        }));

                    if (! selected) {
                        $('#' + graph_link_id)
                            .on('click', function(event) {
                                event.preventDefault();
                                select_graph(graph.id);
                            });
                    }

                    // enable export graph to pdf button
                    $('#' + 'export-graph-pdf-' + graph.id)
                        .on('click', function(event) {
                            event.preventDefault();
                            pdf_single_graph(graph.id);
                        });
                });

                $('#result-count').html('matches: ' + _scroller.total + ' ');
                if (_scroller.total > 0) {
                    var add_all_id = 'select-all-graphs';
                    if (_scroller.total <= _max_select_all) {
                        $('#result-count').append(tpl_select_all_graphs({
                            'id': add_all_id
                        }));

                        $('#' + add_all_id).on('click', function(event) {
                            event.preventDefault();
                            select_all_graphs();
                        });
                    } else {
                        $('#result-count')
                            .append(tpl_select_all_max_warning({
                                'id': add_all_id,
                                'max': _max_select_all
                            }));
                    }
                }
                _scroller.unlock();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                _scroller.unlock();
                if (jqXHR.status == 200) {
                    // 200 probably means the user was logged out
                    location.reload();
                } else {
                    show_error(jqXHR.status + ' ' + errorThrown +
                        '. Unable to fetch results.'
                    );
                }
            }
        });
    };

    // Add graph to selection
    var select_graph = function(graph_id) {
        var graph = _graph_pool[graph_id];

        var graph_link_id = 'graph-link-' + graph.id;
        var hlink_uri =  graph.cacti_instance.base_url + 'graph.php?local_graph_id=' + graph.graph_local_id;
        var hlink_img = ns.get('base_url') + 'api/show_graph/' + graph.id + '/' + _rra_id + '/';

        $('#selected-graphs-placeholder').remove();

        /*
        if ($('#start_date').val() !== '' && $('#end_date').val() !== '') {
            var start_date = new Date(Date.parse($('#start_date').val())).getTime()/1000;
            var end_date = new Date(Date.parse($('#end_date').val())).getTime()/1000;

            hlink_img = hlink_img + start_date + '/' + end_date + '/';
        }
        */

        $('#selected-graphs-list').append(
            tpl_selected_graph_item({
                'graph_id': graph.id,
                'graph_name': graph.title_cache,
                'ci_name': graph.cacti_instance.name,
                'hlink_uri': hlink_uri,
                'hlink_img': hlink_img,
                'hlink_title': graph.cacti_instance.name + ' - ' + graph.title_cache
            })
        );

        $('#deselect-btn-' + graph.id).on('click', function(event) {
            event.preventDefault();
            deselect_graph(graph.id);
        });

        if ($('#' + graph_link_id).length !== 0) {
            $('#row-graph-' + graph.id).attr({
                'class': 'selected'
            });

            if ($.inArray(graph.id, _cur_saved_selection) != -1) {
                $('#row-graph-' + graph.id).attr({
                    'class': 'current_selection'
                });
            }

            $('#' + graph_link_id)
            .replaceWith(tpl_graph_link_selected({
                'id': graph_link_id,
                'graph_name': graph.title_cache
            }));
        }

        $('#graph-' + graph_id).load(function() {
            equal_height($('.graph-img'));
        });
    };

    // Delete graph from selection
    var deselect_graph = function(graph_id) {
        var graph_link_id = 'graph-link-' + graph_id;
        var graph_name = $('#' + graph_link_id).html()

        $('#graph-selected-' + graph_id).fadeOut(150, function() {
            $(this).remove();
        });

        $('#row-graph-' + graph_id).removeAttr('class');

        $('#' + graph_link_id)
        .replaceWith(tpl_graph_link_deselected({
            'id': graph_link_id,
            'graph_name': graph_name
        }));

        $('#' + graph_link_id).on('click', function(event) {
            event.preventDefault();
            select_graph(graph_id);
        });

        if ($.inArray(graph_id, _cur_saved_selection) != -1) {
            _cur_saved_selection = [];
            $('#graph-selections-select-1').select2('data', null);
            $('.current_selection').attr({'class': 'selected'});
        }

        equal_height($('.graph-img'));
    };

    // Add all resulting graphs to selection
    var select_all_graphs = function() {
        $('#results-list').children().each(function() {
            var graph_id = parseInt(this.id.split('-').pop());
            if ($('#graph-selected-' + graph_id).length === 0) {
                select_graph(graph_id);
            }
        });
    };

    // Delete all graphs from selection
    var deselect_all_graphs = function() {
        if ($('#selected-graphs_list:first').attr('id ') == 'selected-graphs-placeholder') {
            return;
        }

        $('#selected-graphs-list').children().each(function() {
            var graph_id = parseInt(this.id.split('-').pop());
            deselect_graph(graph_id);
        });
    };

    // Graphs init function, page is loaded from this
    var init = function() {
        enable_cacti_instance_select();
        enable_graph_templates_select();
        enable_hosts_select();
        enable_graph_results();
        enable_search_input();
        enable_graph_selections_select();
        $('#selection-loading-indicator').hide();

        // Prevent submitting the search form on enter
        $('#search-form').on('submit', function(event) {
            event.preventDefault();
        });

        $('#rra-selector').on('change', function(event) {
            event.preventDefault();
            _rra_id = $(this).val();

            var spinner_opts = ns.spinner_opts;
            spinner_opts.top = '8px';
            spinner_opts.left = '-10px';

            if ($('.graph-img').length > 0) {
                $('#selection-loading').spin(spinner_opts);

                var cur_img = 0
                $('.graph-img').each(function() {
                    var graph_id = parseInt(this.id.split('-').pop());
                    this.src = ns.get('base_url') + 'api/show_graph/' +
                        graph_id + '/' + _rra_id + '/';

                    $(this).load(function() {
                        cur_img++;
                        if (cur_img == $('.graph-img').length) {
                            $('#selection-loading').stopspin();
                        }
                    });
                });
            }
        });

        $('#btn-refresh-graphs').on('click', function(event) {
            event.preventDefault();

            var spinner_opts = ns.spinner_opts;
            spinner_opts.top = '8px';
            spinner_opts.left = '-10px';

            if ($('.graph-img').length > 0) {
                $('#selection-loading').spin(spinner_opts);
                var cur_img = 0
                $('.graph-img').each(function() {
                    var graph_id = parseInt(this.id.split('-').pop());
                    this.src = ns.get('base_url') + 'api/show_graph/' +
                        graph_id + '/' + _rra_id + '/?' +  new Date().getTime();
                    $(this).load(function() {
                    cur_img++;
                        if (cur_img == $('.graph-img').length) {
                            $('#selection-loading').stopspin();
                        }
                    });
                });
            }
        });

        // Event handler for the toggle search box button
        $('#btn-toggle-search').on('click', function(event) {
            event.preventDefault();
            $('#search_box').slideToggle(100);
            var showhide = 'show search box';
            if ($('#btn-toggle-search').html() === 'show search box') {
                showhide = 'hide search box';
            }
            $('#btn-toggle-search').html(showhide);
        });

        // Event handler for the pdf export button
        $('#export-selected-pdf').on('click', function(event) {
            event.preventDefault();
            pdf_graph_selection();
        });

        // Event handler for the deselect all graphs button
        $('#deselect-all-graphs').on('click', function(event) {
            event.preventDefault();
            deselect_all_graphs();
        });
    };

    // Export public functions
    ns.graphs.init = init;
}(CAPO));

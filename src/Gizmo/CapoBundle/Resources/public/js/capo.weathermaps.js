////////////////////////////////////////////////////////////////////////////
// CAPO.weathermaps sub namespace
// Functionality for the 'weathermaps' screen
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

CAPO.weathermaps = CAPO.weathermaps || {};
(function(ns) {
    "use strict";
    var _scroller = null;
    var _filter_1_prev = '';
    var _selected_wmaps = [];
    var _autorefresh = false;
    var _autorefresher;
    var _slideshow = false;
    var _slideshower;
    var _active_slide = -1;

    //
    // Load HTML templates
    //
    var tpl_wmap_link_selected = ns.html.wmap_link_selected();
    var tpl_wmap_link_deselected = ns.html.wmap_link_deselected();
    var tpl_wmap_result_list_item_selected = ns.html.wmap_result_list_item(true);
    var tpl_wmap_result_list_item_deselected = ns.html.wmap_result_list_item(false);
    var tpl_wmap_result_list_item = tpl_wmap_result_list_item_deselected;

    //
    // select2 result formatting functions
    //
    var select2_default_format = function(item) {
        return item.name;
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
            refresh_results();
        });
    };

    // Add weathermap to selection
    var select_wmap = function(wmap) {
        $('#selected_wmaps_placeholder').remove();

        var wmap_tpl = ns.html.selected_wmap_item();

        $('#selected_wmaps_list').append(
            wmap_tpl({
                'wmap_id': wmap.id,
                'wmap_name': wmap.titlecache,
                'ci_name': wmap.cacti_instance.name,
                'hlink_uri': wmap.cacti_instance.base_url + 'plugins/weathermap/weathermap-cacti-plugin.php?action=viewmap&id=' + wmap.filehash,
                'hlink_img': ns.get('base_url') + 'api/show_wmap/' + wmap.id + '/',
                'hlink_title': wmap.cacti_instance.name +' - ' + wmap.titlecache
            })
        );

        _selected_wmaps[wmap.id] = wmap;

        $('#deselect-btn-' + wmap.id).on('click', function(event) {
            event.preventDefault();
            deselect_wmap(wmap);
        });

        $('#row-wmap-' + wmap.id).attr({
            'class': 'selected'
        });

        $('#col-wmap-link-' + wmap.id)
            .html(tpl_wmap_link_selected({
                'wmap_id': wmap.id,
                'wmap_name': wmap.titlecache
            }));
    };

    // Remove wmap from selected weathermaps list
    var register_wmap_deselected = function(wmap) {
        if (wmap.id in _selected_wmaps) {
            delete _selected_wmaps[wmap.id];
        } else {
            show_error('Tried to deselect a not-selected weathermap.');
        }
    };

    // Enable the wmap link
    var enable_select_wmap_link = function(wmap) {
        $('#wmap-link-' + wmap.id).on('click', function(event) {
            event.preventDefault();
            select_wmap(wmap);
        });
    }

    // Delete weathermap from selection
    var deselect_wmap = function(wmap) {
        $('#wmap-selected-' + wmap.id).fadeOut(150, function() {
            $(this).remove();
        });

        $('#row-wmap-' + wmap.id).removeAttr('class');

        $('#col-wmap-link-' + wmap.id)
            .html(tpl_wmap_link_deselected({
                'wmap_id': wmap.id,
                'wmap_name': wmap.titlecache
            }));

        register_wmap_deselected(wmap);

        enable_select_wmap_link(wmap);
    };

    // Refresh a weathermap in the selection
    var refresh_wmap = function(wmap) {
        var d = new Date();
        $('#wmap-' + wmap.id)
            .attr('src',
                  ns.get('base_url') + 'api/show_wmap/' +
                  wmap.id + '/' + '?' + d.getTime());
    };

    // Refresh all weathermaps in the selection
    var refresh_selected_wmaps = function() {
        $.each(_selected_wmaps, function(index, wmap) {
            if (wmap) {
                refresh_wmap(wmap);
            }
        });
    }

    // hide all wmaps from selection
    var hide_all_wmaps = function() {
        $.each(_selected_wmaps, function(index, wmap) {
            if (wmap) {
                $('#wmap-selected-' + wmap.id).hide();
            }
        });
    };

    // hide all wmaps from selection
    var show_all_wmaps = function() {
        $.each(_selected_wmaps, function(index, wmap) {
            if (wmap) {
                $('#wmap-selected-' + wmap.id).show();
            }
        });
    };

    // Delete all wmaps from selection
    var next_slide = function() {
        var wmaps = [];

        $.each(_selected_wmaps, function(index, wmap) {
            if (wmap) {
                wmaps.push(wmap);
            }
        });

        if (_active_slide == -1) {
            var wmap = wmaps[0];
            $('#wmap-selected-' + wmap.id).show();
            _active_slide = 0;
        } else if (_active_slide == wmaps.length - 1) {
            var wmap = wmaps[_active_slide];
            $('#wmap-selected-' + wmap.id).hide();

            wmap = wmaps[0];
            $('#wmap-selected-' + wmap.id).show();
            _active_slide = 0;
        } else {
            var wmap = wmaps[_active_slide];
            $('#wmap-selected-' + wmap.id).hide();

            wmap = wmaps[_active_slide+1];
            $('#wmap-selected-' + wmap.id).show();
            _active_slide++;
        }
    };

    // Delete all wmaps from selection
    var deselect_all_wmaps = function() {
        for (var key in _selected_wmaps) {
            deselect_wmap(_selected_wmaps[key]);
        }
    };

    // Empty the weathermaps resuls list and reload it
    var refresh_results = function() {
        _scroller.reset();
        load_results(true);
    };

    // Load a set of weathermap search results
    var load_results = function(clear) {
        $.ajax({
            url: ns.get('base_url') + 'api/get_weathermaps/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                page: _scroller.page,
                page_limit: _scroller.per_page,
                cacti_instance: $('#cacti_instance_select_1').val(),
                q: $('#filter_1').val()
            },
            success: function(response, textStatus, jqXHR) {
                _scroller.total = response.weathermaps_total;

                if (clear) {
                    $('#results').scrollTop(0);
                    $('#results_list').empty();
                }

                $.each(response.weathermaps, function(index, wmap) {
                    var selected = false;

                    if (_selected_wmaps.hasOwnProperty(wmap.id)) {
                        tpl_wmap_result_list_item = tpl_wmap_result_list_item_selected;
                    } else {
                        tpl_wmap_result_list_item = tpl_wmap_result_list_item_deselected;
                    }

                    $('#results_list').append(
                        tpl_wmap_result_list_item({
                            'ci_name': wmap.cacti_instance.name,
                            'wmap_id': wmap.id,
                            'wmap_name': wmap.titlecache
                        })
                    );

                    enable_select_wmap_link(wmap);
                });

                $('#result_count').html('matches: ' + _scroller.total);

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

    // Event handler for the search input
    var handle_search_input = function(fn_handler) {
        var filter_1_cur = $('#filter_1').val();

        if  (
            (filter_1_cur.length === 0 && _filter_1_prev.length !== 0) ||
            (filter_1_cur !== _filter_1_prev && filter_1_cur.length >= 2)
        ) {
            refresh_results();
            _filter_1_prev = filter_1_cur;
        }
    };

    // Refresh weathermap results list when typing in the search input box
    var enable_search_input = function() {
        $('#filter_1').on('keyup', function(event) {
            event.preventDefault();
            ns.delay(function() { handle_search_input(); }, 500);
        });
    };

    // Enable the resulting weathermaps list
    var enable_wmap_results = function() {
        _scroller = ns.create_infinite_scroller($('#results'), load_results);

        // Initial load of the weathermaps list
        refresh_results();
    };

    var init = function() {
        enable_cacti_instance_select();
        enable_search_input();
        enable_wmap_results();

        // Prevent submitting the search form on enter
        $('#search-form').on('submit', function(event) {
            event.preventDefault();
        });

        // Event handler for the toggle search box button
        $('#btn-toggle-search').on('click', function(event) {
            event.preventDefault();
            $('#search-box').slideToggle(100);
            var showhide = 'show search box';
            if ($('#btn-toggle-search').html() === 'show search box') {
                showhide = 'hide search box';
            }
            $('#btn-toggle-search').html(showhide);
        });

        $('#slideshow_activator').on('change', function(event) {
            if (_slideshow == false) {

                _slideshow = true;
                hide_all_wmaps();
                next_slide();
                _slideshower = setInterval(function() {
                    next_slide();
                }, 10000);
            } else {
                _slideshow = false;
                clearInterval(_slideshower);
                show_all_wmaps();
            }
        });

        $('#autorefresh_activator').on('change', function(event) {
            if (_autorefresh == false) {
                _autorefresh = true;
                _autorefresher = setInterval(function() {
                    refresh_selected_wmaps();
                }, 60000);
            } else {
                _autorefresh = false;
                clearInterval(_autorefresher);
            }
        });
    };

    // Export public functions
    ns.weathermaps.init = init;
}(CAPO));

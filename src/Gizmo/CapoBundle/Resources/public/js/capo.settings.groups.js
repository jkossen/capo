////////////////////////////////////////////////////////////////////////////
// CAPO.settings.groups sub namespace
// Functionality for the 'settings / groups' screen
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
CAPO.settings.groups = CAPO.settings.groups || {};

(function(ns) {
    "use strict";
    var _scroller = null;

    //
    // Load required HTML templates
    //
    var tpl_groups_result_list_item = ns.html.groups_result_list_item();
    var tpl_col_group_is_active = ns.html.col_group_is_active();
    var tpl_group_show_name = ns.html.group_show_name();
    var tpl_group_edit_name = ns.html.group_edit_name();
    var tpl_groups_cacti_instances_list_item = ns.html.groups_cacti_instances_list_item();

    var init = function() {
        // Prevent submitting the search form on enter
        $('#search-form').on('submit', function(event) {
            event.preventDefault();
        });

        enable_search_group_input();
        enable_results();
        enable_add_group_form();

        $('#active-groups-only').on('change', function(event) {
            event.preventDefault();
            refresh_results();
        });

    };

    var select2_graph_selection_format = function (item) {
        return item.name + ' (' + item.created.date + ')';
    };

    var refresh_results = function() {
        _scroller.reset();
        load_results(true);
    };

    var load_cacti_instances = function(group, available) {
        var el = '#group-' + group.id + '-ci-select-selected';
        var data = {
            page: _scroller.page,
            page_limit: _scroller.per_page,
        };

        if (available) {
            data['exclude_group_id'] = group.id;
            el = '#group-' + group.id + '-ci-select-deselected';
        } else {
            data['group_id'] = group.id;
        }

        $.ajax({
            url: ns.get('base_url') + 'api/admin/get_cacti_instances/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: data,
            success: function(response, textStatus, jqXHR) {
                $.each(response.cacti_instances, function(index, ci) {
                    $('<option>').attr({
                        'value': ci.id
                    })
                    .text(ci.name)
                    .appendTo($(el));
                });
            }
        });
    };

    var group_show_edit_cacti_instances_row = function(group) {
        var row_id = 'group-edit-cacti-instances-' + group.id;

        $('#group-' + group.id).after(
            tpl_groups_cacti_instances_list_item({
                'id': group.id
            }));

        load_cacti_instances(group, false);
        load_cacti_instances(group, true);

        $('#btn-edit-access-' + group.id).unbind('click')
            .on('click', function(event) {
                event.preventDefault();
                $('#' + row_id).toggle();
            });

        // event handler: remove selected nodeprop from node
        $('#group-' + group.id +'-ci-select-btn-deselect').on('click', function(event) {
            event.preventDefault();
            var cacti_instance_id = $('#group-' + group.id + '-ci-select-selected option:selected').val();
            if (cacti_instance_id != undefined) {
                disable_cacti_instance_for_group(group, cacti_instance_id);
            }
        });

        // event handler: add selected cacti_instance to node
        $('#group-' + group.id +'-ci-select-btn-select').on('click', function(event) {
            event.preventDefault();
            var cacti_instance_id = $('#group-' + group.id + '-ci-select-deselected option:selected').val();
            if (cacti_instance_id != undefined) {
                enable_cacti_instance_for_group(group, cacti_instance_id);
            }
        });
    }

    // Load a set of user search results
    var load_results = function(clear) {
        $.ajax({
            url: ns.get('base_url') + 'api/admin/get_groups/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                page: _scroller.page,
                page_limit: _scroller.per_page,
                q: $('#filter').val(),
                active_groups_only: $('#active-groups-only').is(':checked') ? 1 : 0
            },
            success: function(response, textStatus, jqXHR) {
                _scroller.total = response.groups_total;

                if (clear) {
                    $('#results').scrollTop(0);
                    $('#results-list').empty();
                }

                $.each(response.groups, function(index, group) {
                    $('#results-list').append(
                        tpl_groups_result_list_item({
                            'id': group.id,
                        })
                    );
                    group_name(group);
                    group_is_active(group);

                    $('#btn-edit-access-' + group.id)
                        .on('click',
                            function(event) {
                                event.preventDefault();
                                group_show_edit_cacti_instances_row(group);
                            });
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

    // The Group Instance name column
    var group_name = function(group, edit) {
        var col = 'col-groupname-' + group.id;
        var group_id = 'btn-group-name-' + group.id;

        var handler = function(el) {
            if (group.name != el.val()) {
                group.name = el.val();
                group_update(group, function() {});
            }
            group_name(group, false);
        }

        if (edit) {
            $('#' + col).html(tpl_group_edit_name({
                'id': group_id,
                'name': group.name
            }));

            $('#' + group_id)
                .focus()
                .on('keypress', function(event) {
                    if (event.which === 13) {
                        event.preventDefault();
                        handler($(this));
                    }
                })
                .on('focusout', function(event) {
                    event.preventDefault();
                    handler($(this));
                });
        } else {
            $('#' + col).html(
                tpl_group_show_name({
                    'id': group_id,
                    'name': group.name
                }));

            $('#' + group_id)
                .on('click', function(event) {
                    event.preventDefault();
                    group_name(group, true);
                });
        }
    };

    // The Cacti Instance is active column
    var group_is_active = function(group) {
        var btn_class = (group.active) ? 'success' : 'danger';
        var icon_class = (group.active) ? 'ok' : 'remove';
        var col_id = 'col-group-active-' + group.id;
        var btn_id = 'btn-group-is-active-' + group.id;

        var btn = tpl_col_group_is_active({
            'btn_id': btn_id,
            'btn_class': btn_class,
            'icon_class': icon_class
        });

        $('#' + col_id).html(btn);

        $('#' + btn_id)
            .on('click', function(event) {
                event.preventDefault();
                if (group.active) {
                    if (! confirm('This will prevent all users in the group from using Capo. Are you sure?')) {
                        return;
                    }
                }

                group.active = !group.active;
                group_update(group, function() {
                    group_is_active(group);
                });
            });
    };

    var enable_cacti_instance_for_group = function(group, cacti_instance_id) {
        var data = {
            'cacti_instance_id': cacti_instance_id,
            'group_id': group.id
        };

        var url = ns.get('base_url') + 'api/admin/enable_cacti_instance_for_group/';

        $.ajax({
            url: url,
            type: ns.get('request_method'),
            dataType: 'json',
            data: data,
            success: function(response, textStatus, jqXHR) {
                $('#group-' + group.id + '-ci-select-selected').append(
                    $('#group-' + group.id + '-ci-select-deselected option:selected'));
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var ret = $.parseJSON(jqXHR.responseText);
                ns.show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
    };

    var disable_cacti_instance_for_group = function(group, cacti_instance_id) {
        var data = {
            'cacti_instance_id': cacti_instance_id,
            'group_id': group.id
        };

        var url = ns.get('base_url') + 'api/admin/disable_cacti_instance_for_group/';

        $.ajax({
            url: url,
            type: ns.get('request_method'),
            dataType: 'json',
            data: data,
            success: function(response, textStatus, jqXHR) {
                $('#group-' + group.id + '-ci-select-deselected').append(
                    $('#group-' + group.id + '-ci-select-selected option:selected'));
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var ret = $.parseJSON(jqXHR.responseText);
                ns.show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
    };

    // Save a change to a Group
    var group_update = function(group, fn_success) {
        var url = ns.get('base_url') + 'api/admin/group/update/';
        $.ajax({
            url: url,
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                id: group.id,
                name: group.name,
                active: group.active ? 1 : 0,
            },
            success: function(response, textStatus, jqXHR) {
                return fn_success();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var ret = $.parseJSON(jqXHR.responseText);
                ns.show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
    };

    // Event handler for adding a new Group
    var enable_add_group_form = function() {
        $('#add-group-form').on('submit', function(event) {
            event.preventDefault();
            if ($('#new-group-name').val() != '') {
                $.ajax({
                    url: ns.get('base_url') + 'api/admin/group/create/',
                    type: ns.get('request_method'),
                    dataType: 'json',
                    data: {
                        name: $('#new-group-name').val()
                    },
                    success: function(response, textStatus, jqXHR) {
                        refresh_results();
                        $('#add-group-form')[0].reset();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        _scroller.unlock();

                        var ret = $.parseJSON(jqXHR.responseText);
                        ns.show_error(jqXHR.status + ' ' + errorThrown +
                            '. ' + ret.message
                        );
                    }
                });
            }
        })
    };

    // Refresh cacti instances results list when typing in the search input box
    var enable_search_group_input = function() {
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
    var enable_results = function() {
        _scroller = ns.create_infinite_scroller($('#results'), load_results);
        // Initial load of the cacti instances list
        refresh_results();
    };

    ns.settings.groups.init = init;
}(CAPO));

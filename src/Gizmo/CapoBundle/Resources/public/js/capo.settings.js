////////////////////////////////////////////////////////////////////////////
// CAPO.settings sub namespace
// Functionality for the 'settings' screen
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
(function(ns) {
    "use strict";
    var _scroller = null;

    //
    // Load required HTML templates
    //
    var tpl_error_msg = ns.html.msg_container('error');
    var tpl_cacti_instance_result_list_item = ns.html.cacti_instance_result_list_item();
    var tpl_cacti_instance_show_name = ns.html.cacti_instance_show_name();
    var tpl_cacti_instance_edit_name = ns.html.cacti_instance_edit_name();
    var tpl_cacti_instance_show_base_url = ns.html.cacti_instance_show_base_url();
    var tpl_cacti_instance_edit_base_url = ns.html.cacti_instance_edit_base_url();
    var tpl_users_result_list_item = ns.html.users_result_list_item();
    var tpl_col_user_is_active = ns.html.col_user_is_active();
    var tpl_groups_result_list_item = ns.html.groups_result_list_item();
    var tpl_col_group_is_active = ns.html.col_group_is_active();
    var tpl_group_show_name = ns.html.group_show_name();
    var tpl_group_edit_name = ns.html.group_edit_name();
    var tpl_groups_cacti_instances_list_item = ns.html.groups_cacti_instances_list_item();
    var tpl_saved_selection_list_item = ns.html.saved_selection_list_item();
    var tpl_eventlog_list_item = ns.html.eventlog_list_item();

    var show_error = function(msg) {
        return $('#error_container').append(
            tpl_error_msg({
                'msg': msg
            }));
    };

    var init = function() {
        // Prevent submitting the search form on enter
        $('#search-form').on('submit', function(event) {
            event.preventDefault();
        });

        enable_search_cacti_instances_input();
        enable_cacti_instances_results();
        enable_add_cacti_instance_form();
    };

    var init_users = function() {
        // Prevent submitting the search form on enter
        $('#search-form').on('submit', function(event) {
            event.preventDefault();
        });

        enable_search_user_input();
        enable_user_results();

        $('#active_users_only').on('change', function(event) {
            event.preventDefault();
            refresh_users();
        });
    };

    var init_groups = function() {
        // Prevent submitting the search form on enter
        $('#search-form').on('submit', function(event) {
            event.preventDefault();
        });

        enable_search_group_input();
        enable_group_results();
        enable_add_group_form();

        $('#active_groups_only').on('change', function(event) {
            event.preventDefault();
            refresh_groups();
        });

    };

    var init_event_log = function() {
        // Prevent submitting the search form on enter
        $('#search-form').on('submit', function(event) {
            event.preventDefault();
        });

        enable_search_event_log_input();
        enable_event_log();
    };

    var init_saved_selections = function() {
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

    // The Cacti Instance name column
    var cacti_name = function(cacti_instance, edit) {
        var column_id = 'col-name-' + cacti_instance.id;
        var name_id = 'cacti-instance-edit-name-' + cacti_instance.id;

        var handler = function(el) {
            if (cacti_instance.name != el.val()) {
                cacti_instance.name = el.val();
                cacti_instance_update(cacti_instance, function() {});
            }
            cacti_name(cacti_instance, false);
        }

        if (edit) {
            $('#' + column_id).html(
                tpl_cacti_instance_edit_name({
                    'id': name_id,
                    'name': cacti_instance.name
                }));

            $('#' + name_id)
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
            $('#' + column_id).html(
                tpl_cacti_instance_show_name({
                    'id': name_id,
                    'name': cacti_instance.name
                }));

            $('#' + name_id).on('click', function(event) {
                event.preventDefault();
                cacti_name(cacti_instance, true);
            });
        }
    };

    // The Cacti Instance URL column
    var cacti_url = function(cacti_instance, edit) {
        var column_id = 'col-base-url-' + cacti_instance.id;
        var base_url_id = 'cacti-instance-edit-base-url-' + cacti_instance.id;

        var handler = function(el) {
            if (cacti_instance.base_url != el.val()) {
                cacti_instance.base_url = el.val();
                cacti_instance_update(cacti_instance, function() {});
            }
            cacti_url(cacti_instance, false);
        }

        if (edit) {
            $('#' + column_id).html(
                tpl_cacti_instance_edit_base_url({
                    'id': base_url_id,
                    'base_url': cacti_instance.base_url
                }));

            $('#' + base_url_id)
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
            $('#' + column_id).html(
                tpl_cacti_instance_show_base_url({
                    'id': base_url_id,
                    'base_url': cacti_instance.base_url
                }));

            $('#' + base_url_id).on('click', function(event) {
                event.preventDefault();
                cacti_url(cacti_instance, true);
            });
        }
    };

    // Load a set of cacti instance search results
    var load_results = function(clear) {
        $.ajax({
            url: ns.get('base_url') + 'api/get_cacti_instances/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                page: _scroller.page,
                page_limit: _scroller.per_page,
                q: $('#filter_1').val(),
                active_only: 0
            },
            success: function(response, textStatus, jqXHR) {
                _scroller.total = response.cacti_instances_total;

                if (clear) {
                    $('#results').scrollTop(0);
                    $('#results_list').empty();
                }

                var curtime = (new Date).getTime();
                $.each(response.cacti_instances, function(index, cacti_instance) {
                    var d = cacti_instance.import_date.date
                        .replace(/[-,:\s]/g, ',').split(',');
                    var import_date = Date.UTC(d[0], d[1], d[2], d[3], d[4]);
                    var import_date_class = 'import_date';
                    var queue_btn_class = 'success';
                    var activate_btn_class = 'success';
                    var queue_icon = 'ok';
                    var activate_icon = 'ok';

                    if (import_date + 93600000 < curtime) {
                        import_date_class = 'import_date stale';
                    }

                    var str_import_date = 'never';
                    if (cacti_instance.import_date.date !== '1970-01-01 00:00:00') {
                        str_import_date = cacti_instance.import_date.date;
                    }

                    if (cacti_instance.queue_import !== true) {
                        queue_btn_class = 'danger';
                        queue_icon = 'remove';
                    }

                    if (cacti_instance.active !== true) {
                        activate_btn_class = 'danger';
                        activate_icon = 'remove';
                    }

                    $('#results_list').append(
                        tpl_cacti_instance_result_list_item({
                            'id': cacti_instance.id,
                            'name': cacti_instance.name,
                            'import_date': str_import_date,
                            'import_date_class': import_date_class,
                            'queue_btn_class': queue_btn_class,
                            'activate_btn_class': activate_btn_class,
                            'queue_icon': queue_icon,
                            'activate_icon': activate_icon
                            })
                    );

                    cacti_name(cacti_instance, false);
                    cacti_url(cacti_instance, false);

                    $('#btn-queue-ci-' + cacti_instance.id).on('click', function(event) {
                        event.preventDefault();
                        cacti_instance.queue_import = !cacti_instance.queue_import;
                        var new_class = (cacti_instance.queue_import) ? 'btn btn-success' : 'btn btn-danger';
                        var new_icon = (cacti_instance.queue_import) ? 'ok' : 'remove';
                        cacti_instance_update(cacti_instance, function() {
                            $('#btn-queue-ci-' + cacti_instance.id).attr('class', new_class);
                            $('#icon-queue-ci-' + cacti_instance.id).attr('class', 'icon-white icon-' + new_icon);
                        });
                    });

                    $('#btn-activate-ci-' + cacti_instance.id).on('click', function(event) {
                        event.preventDefault();
                        cacti_instance.active = !cacti_instance.active;
                        var new_class = (cacti_instance.active) ? 'btn btn-success' : 'btn btn-danger';
                        var new_icon = (cacti_instance.active) ? 'ok' : 'remove';
                        cacti_instance_update(cacti_instance, function() {
                            $('#btn-activate-ci-' + cacti_instance.id).attr('class', new_class);
                            $('#icon-activate-ci-' + cacti_instance.id).attr('class', 'icon-white icon-' + new_icon);
                        });
                    });

                    $('#edit-cacti-instance-name-' + cacti_instance.id).on('click', function(event) {
                        event.preventDefault();
                        cacti_name(cacti_instance, true);
                    });

                    $('#edit-cacti-instance-base-url-' + cacti_instance.id).on('click', function(event) {
                        event.preventDefault();
                        cacti_url(cacti_instance, true);
                    });
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

    var refresh_eventlog = function() {
        _scroller.reset();
        load_eventlog(true);
    };

    // Load a set of cacti instance search results
    var load_eventlog = function(clear) {
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

    var refresh_users = function() {
        load_users(true);
    };

    var refresh_groups = function() {
        load_groups(true);
    };

    var load_available_cacti_instances = function(group) {
        $.ajax({
            url: ns.get('base_url') + 'api/admin/get_cacti_instances/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                page: _scroller.page,
                page_limit: _scroller.per_page,
                exclude_group_id: group.id
            },
            success: function(response, textStatus, jqXHR) {
                $.each(response.cacti_instances, function(index, ci) {
                    $('<option>').attr({
                        'value': ci.id
                    })
                    .text(ci.name)
                    .appendTo($('#group_' + group.id + '_ci_select_deselected'));
                });
            }
        });
    }

    var group_show_edit_cacti_instances_row = function(group) {
        var row_id = 'group-edit-cacti-instances-' + group.id;

        $('#group_' + group.id).after(
            tpl_groups_cacti_instances_list_item({
                'id': group.id
            }));

        $.each(group.cacti_instances, function(index, ci) {
            $('<option>').attr({
                'value': ci.id
            })
            .text(ci.name)
            .appendTo($('#group_' + group.id + '_ci_select_selected'));
        });

        load_available_cacti_instances(group);

        $('#btn-edit-access-' + group.id).unbind('click')
            .on('click', function(event) {
                event.preventDefault();
                $('#' + row_id).toggle();
            });

        // event handler: remove selected nodeprop from node
        $('#group_' + group.id +'_ci_select_btn_deselect').on('click', function(event) {
            event.preventDefault();
            var cacti_instance_id = $('#group_' + group.id + '_ci_select_selected option:selected').val();
            if (cacti_instance_id != undefined) {
                disable_cacti_instance_for_group(group, cacti_instance_id);
            }
        });

        // event handler: add selected cacti_instance to node
        $('#group_' + group.id +'_ci_select_btn_select').on('click', function(event) {
            event.preventDefault();
            var cacti_instance_id = $('#group_' + group.id + '_ci_select_deselected option:selected').val();
            if (cacti_instance_id != undefined) {
                enable_cacti_instance_for_group(group, cacti_instance_id);
            }
        });
    }

    // The Cacti Instance is active column
    var user_is_active = function(user) {
        var btn_class = (user.enabled) ? 'success' : 'danger';
        var icon_class = (user.enabled) ? 'ok' : 'remove';
        var col_id = 'col-user-active-' + user.id;
        var btn_id = 'btn-user-is-active-' + user.id;

        var btn = tpl_col_user_is_active({
            'btn_id': btn_id,
            'btn_class': btn_class,
            'icon_class': icon_class
        });

        $('#' + col_id).html(btn);

        $('#' + btn_id)
            .on('click', function(event) {
                event.preventDefault();
                user.enabled = !user.enabled;
                user_update(user, function() {
                    user_is_active(user);
                });
            });
    };

    // Load a set of user search results
    var load_users = function(clear) {
        $.ajax({
            url: ns.get('base_url') + 'api/admin/get_users/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                page: _scroller.page,
                page_limit: _scroller.per_page,
                q: $('#filter_1').val(),
                active_users_only: $('#active_users_only').is(':checked') ? 1 : 0
            },
            success: function(response, textStatus, jqXHR) {
                _scroller.total = response.users_total;

                if (clear) {
                    $('#results').scrollTop(0);
                    $('#results_list').empty();
                }

                $.each(response.users, function(index, user) {
                    $('#results_list').append(
                        tpl_users_result_list_item({
                            'id': user.id,
                            'name': user.username,
                            'last_login': (user.lastLogin == null) ? 'never' : user.lastLogin.date,
                            'group_id': user.group.id
                        })
                    );
                    user_is_active(user);
                    user_groupselect(user);
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

    var user_groupselect = function(user) {
        return $('#group_' + user.id).select2({
            placeholder: 'Select group',
            allowClear: false,
            width: '350px',
            minimumInputLength: 0,
            ajax: {
                url: ns.get('base_url') + 'api/admin/get_groups/',
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
                    var more = (page * 25) < data.groups_total;

                    // notice we return the value of more so Select2 knows if
                    // more results can be loaded
                    return { results: data.groups, more: more };
                }
            },
            initSelection: function(element, callback) {
                var id = user.group.id;
                callback(user.group);
            },
            formatResult: function(item) { return item.name; },
            formatSelection: function(item) { return item.name; },
            dropdownCssClass: "bigdrop" // apply css that makes the dropdown taller
        }).on('change', function(event) {
            event.preventDefault();
            change_group_for_user(user);
        });
    }

    var change_group_for_user = function(user) {
        var data = {
            'user_id': user.id,
            'group_id': $('#group_' + user.id).val()
        };

        var url = ns.get('base_url') + 'api/admin/user/change_group/';

        $.ajax({
            url: url,
            type: ns.get('request_method'),
            dataType: 'json',
            data: data,
            success: function(response, textStatus, jqXHR) {
                $('#td_group_' + user.id).animateHighlight();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var ret = $.parseJSON(jqXHR.responseText);
                show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
    }

    // Load a set of user search results
    var load_groups = function(clear) {
        $.ajax({
            url: ns.get('base_url') + 'api/admin/get_groups/',
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                page: _scroller.page,
                page_limit: _scroller.per_page,
                q: $('#filter_1').val(),
                active_groups_only: $('#active_groups_only').is(':checked') ? 1 : 0
            },
            success: function(response, textStatus, jqXHR) {
                _scroller.total = response.groups_total;

                if (clear) {
                    $('#results').scrollTop(0);
                    $('#results_list').empty();
                }

                $.each(response.groups, function(index, group) {
                    $('#results_list').append(
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

    var group_edit_access = function(group) {
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
                $('#group_' + group.id + '_ci_select_selected').append(
                    $('#group_' + group.id + '_ci_select_deselected option:selected'));
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var ret = $.parseJSON(jqXHR.responseText);
                show_error(jqXHR.status + ' ' + errorThrown +
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
                $('#group_' + group.id + '_ci_select_deselected').append(
                    $('#group_' + group.id + '_ci_select_selected option:selected'));
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var ret = $.parseJSON(jqXHR.responseText);
                show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
    };

    // Save a change to a Cacti instance
    var cacti_instance_update = function(cacti_instance, fn_success) {
        var url = ns.get('base_url') + 'api/admin/cacti_instance/update/';
        $.ajax({
            url: url,
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                id: cacti_instance.id,
                name: cacti_instance.name,
                base_url: cacti_instance.base_url,
                active: cacti_instance.active ? 1 : 0,
                queue_import: cacti_instance.queue_import ? 1 : 0
            },
            success: function(response, textStatus, jqXHR) {
                return fn_success();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var ret = $.parseJSON(jqXHR.responseText);
                show_error(jqXHR.status + ' ' + errorThrown +
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
                show_error(jqXHR.status + ' ' + errorThrown +
                   '. ' + ret.message
                );
            }
        });
    };

    // Save a change to a User
    var user_update = function(user, fn_success) {
        var url = ns.get('base_url') + 'api/admin/user/update/';
        $.ajax({
            url: url,
            type: ns.get('request_method'),
            dataType: 'json',
            data: {
                id: user.id,
                enabled: user.enabled ? 1 : 0,
            },
            success: function(response, textStatus, jqXHR) {
                return fn_success();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var ret = $.parseJSON(jqXHR.responseText);
                show_error(jqXHR.status + ' ' + errorThrown +
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
                        refresh_groups();
                        $('#add-group-form')[0].reset();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        _scroller.unlock();

                        var ret = $.parseJSON(jqXHR.responseText);
                        show_error(jqXHR.status + ' ' + errorThrown +
                            '. ' + ret.message
                        );
                    }
                });
            }
        })
    };

    // Event handler for adding a new Cacti instance
    var enable_add_cacti_instance_form = function() {
        $('#add-cacti-instance-form').on('submit', function(event) {
            event.preventDefault();
            if ($('#new-cacti-instance-name').val() != '' &&
            $('#new-cacti-instance-url').val() != '') {
                $.ajax({
                    url: ns.get('base_url') + 'api/admin/cacti_instance/create/',
                    type: ns.get('request_method'),
                    dataType: 'json',
                    data: {
                        name: $('#new-cacti-instance-name').val(),
                        base_url: $('#new-cacti-instance-url').val(),
                    },
                    success: function(response, textStatus, jqXHR) {
                        refresh_results();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        _scroller.unlock();

                        var ret = $.parseJSON(jqXHR.responseText);
                        show_error(jqXHR.status + ' ' + errorThrown +
                            '. ' + ret.message
                        );
                    }
                });
            }
        })
    };

    // Refresh cacti instances results list when typing in the search input box
    var enable_search_cacti_instances_input = function() {
        var filter_1_prev_len = 0;
        var filter_1_prev = '';

        $('#filter_1').on('keyup', function(event) {
            event.preventDefault();
            ns.delay(function() {
                var filter_1_cur = $('#filter_1').val();
                if ((filter_1_cur.length === 0 && filter_1_prev.length !== 0) ||
                   (filter_1_cur !== filter_1_prev && filter_1_cur.length >= 2)) {
                        refresh_results();
                        filter_1_prev = filter_1_cur;
                }
            }, 500);
        });
    };

    // Refresh cacti instances results list when typing in the search input box
    var enable_search_event_log_input = function() {
        var filter_1_prev_len = 0;
        var filter_1_prev = '';

        $('#filter_1').on('keyup', function(event) {
            event.preventDefault();
            ns.delay(function() {
                var filter_1_cur = $('#filter_1').val();
                if ((filter_1_cur.length === 0 && filter_1_prev.length !== 0) ||
                   (filter_1_cur !== filter_1_prev && filter_1_cur.length >= 2)) {
                        refresh_eventlog();
                        filter_1_prev = filter_1_cur;
                }
            }, 500);
        });
    };

    // Refresh cacti instances results list when typing in the search input box
    var enable_search_user_input = function() {
        var filter_1_prev_len = 0;
        var filter_1_prev = '';

        $('#filter_1').on('keyup', function(event) {
            event.preventDefault();
            ns.delay(function() {
                var filter_1_cur = $('#filter_1').val();
                if ((filter_1_cur.length === 0 && filter_1_prev.length !== 0) ||
                   (filter_1_cur !== filter_1_prev && filter_1_cur.length >= 2)) {
                        refresh_users();
                        filter_1_prev = filter_1_cur;
                }
            }, 500);
        });
    };

    // Refresh cacti instances results list when typing in the search input box
    var enable_search_group_input = function() {
        var filter_1_prev_len = 0;
        var filter_1_prev = '';

        $('#filter_1').on('keyup', function(event) {
            event.preventDefault();
            ns.delay(function() {
                var filter_1_cur = $('#filter_1').val();
                if ((filter_1_cur.length === 0 && filter_1_prev.length !== 0) ||
                   (filter_1_cur !== filter_1_prev && filter_1_cur.length >= 2)) {
                        refresh_groups();
                        filter_1_prev = filter_1_cur;
                }
            }, 500);
        });
    };

    // Enable the resulting cacti instances list
    var enable_cacti_instances_results = function() {
        _scroller = ns.create_infinite_scroller($('#results'), load_results);
        // Initial load of the cacti instances list
        refresh_results();
    };

    // Enable the resulting cacti instances list
    var enable_user_results = function() {
        _scroller = ns.create_infinite_scroller($('#results'), load_users);
        // Initial load of the cacti instances list
        refresh_users();
    };

    // Enable the resulting cacti instances list
    var enable_group_results = function() {
        _scroller = ns.create_infinite_scroller($('#results'), load_groups);
        // Initial load of the cacti instances list
        refresh_groups();
    };

    // Enable the resulting cacti instances list
    var enable_event_log = function() {
        _scroller = ns.create_infinite_scroller($('#results'), load_eventlog);
        // Initial load of the cacti instances list
        refresh_eventlog();
    };

    ns.settings.init = init;
    ns.settings.init_users = init_users;
    ns.settings.init_groups = init_groups;
    ns.settings.init_event_log = init_event_log;
    ns.settings.init_saved_selections = init_saved_selections;
}(CAPO));

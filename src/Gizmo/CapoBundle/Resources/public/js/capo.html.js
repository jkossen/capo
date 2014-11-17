////////////////////////////////////////////////////////////////////////////
// CAPO.html sub namespace
// HTML Widgets
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

CAPO.html = CAPO.html || {};
(function(ns) {
    "use strict";

    // Container to show messages to the user
    var msg_container = function(msg_type) {
        var tpl =
            '<div class="alert alert-' + msg_type + '">' +
            '<a class="close" data-dismiss="alert" href="#">&times;</a>';

        var msg = '<p>'
        if (msg_type === 'error') {
            msg = msg + '<strong>ERROR:</strong> ';
        } else if (msg_type === 'success') {
            msg = msg + '<strong>SUCCESS:</strong> ';
        } else {
            msg = msg + '<strong>INFO:</strong ';
        }
        tpl = tpl + '<%= message %></p></div>';

        return _.template(tpl);
    };

    var multi_select = function() {
        var tpl = '<div class="pull-left capo_multiselect">' +
            '<div class="pull-left">' +
            '<strong><%= title_selected %>:</strong><br>' +
            '<select class="capo_multiselect" id="<%= id %>_selected" size="10"></select></div>' +
            '<div class="btn-group pull-left" style="margin-top:75px;padding: 10px;">' +
            '<button class="btn" id="<%= id %>_btn_select"><span class="glyphicon glyphicon-chevron-left"></span></button>' +
            '<button class="btn" id="<%= id %>_btn_deselect"><span class="glyphicon glyphicon-chevron-right"></span></button>' +
            '</div><div class="pull-left"><strong><%= title_deselected %>:</strong><br>' +
            '<select class="capo_multiselect" id="<%= id %>_deselected" size="10"></select>' +
            '</div></div>';

        return _.template(tpl);
    };

    // A Cacti Instance result row
    var cacti_instance_result_list_item = function() {
        var tpl =
            '<tr>' +
            '<td class="cacti-instance" id="col-name-<%= id %>"></td>' +
            '<td class="base_url" id="col-base-url-<%= id %>"></td>' +
            '<td class="<%= import_date_class %>"><%= import_date %></td>' +
            '<td class="queue_import"><a id="btn-queue-ci-<%= id %>" class="btn btn-<%= queue_btn_class %>"><span id="icon-queue-ci-<%= id %>" class="glyphicon glyphicon-<%= queue_icon %> icon-white"></span></a></td>' +
            '<td class="active"><a id="btn-activate-ci-<%= id %>" class="btn btn-<%= activate_btn_class %>"><span id="icon-activate-ci-<%= id %>" class="glyphicon glyphicon-<%= activate_icon %> icon-white"></span></a></td>' +
            '</tr>';

        return _.template(tpl);
    }

    var cacti_instance_edit_name = function() {
        var tpl =
            '<input id="<%= id %>" type="text" name="cacti_name" value="<%= name %>">';

        return _.template(tpl);
    };

    var cacti_instance_show_name = function() {
        var tpl =
            '<a id="<%= id %>" href="#"><%= name %></a>';

        return _.template(tpl);
    };

    var cacti_instance_edit_base_url = function() {
        var tpl =
            '<input id="<%= id %>" type="text" name="cacti_url" value="<%= base_url %>">';

        return _.template(tpl);
    };

    var cacti_instance_show_base_url = function() {
        var tpl =
            '<a id="<%= id %>" href="#"><%= base_url %></a>';

        return _.template(tpl);
    };

    // A User result row
    var users_result_list_item = function() {
        var tpl =
            '<tr id="user_<%= id %>">' +
            '<td class="username"><%= name %></td>' +
            '<td class="lastlogin"><%= last_login %></td>' +
            '<td class="group" id="col-group-<%= id %>">' +
            '<input type="hidden" id="group_<%= id %>" value="<%= group_id %>">' +
            '</td>' +
            '<td class="active" id="col-user-active-<%= id %>"></td>' +
            '</tr>';

        return _.template(tpl);
    };

    var col_user_is_active = function() {
        var tpl =
            '<a id="<%= btn_id %>" class="btn btn-<%= btn_class %>">' +
            '<span class="glyphicon glyphicon-<%= icon_class %> icon-white"></span>' +
            '</a>';

        return _.template(tpl);
    };

    // A Group result row
    var groups_result_list_item = function() {

        var tpl =
            '<tr id="group_<%= id %>">' +
            '<td id="col-groupname-<%= id %>" class="groupname"></td>' +
            '<td class="actions"><a id="btn-edit-access-<%= id %>" href="#">edit access</a></td>' +
            '<td id="col-group-active-<%= id %>" class="active"></td>' +
            '</tr>';

        return _.template(tpl);
    };

    var group_show_name = function() {
        var tpl =
            '<a id="<%= id %>" href="#"><%= name %></a>';

        return _.template(tpl);
    };

    var group_edit_name = function() {
        var tpl =
            '<input id="<%= id %>" type="text" name="group_name" value="<%= name %>">';

        return _.template(tpl);
    };

    var col_group_is_active = function() {
        var tpl =
            '<a id="<%= btn_id %>" class="btn btn-<%= btn_class %>">' +
            '<span class="glyphicon glyphicon-<%= icon_class %> icon-white"></span>' +
            '</a>';

        return _.template(tpl);
    };

    var groups_cacti_instances_list_item = function() {
        var tpl_multiselect = multi_select();
        var ms = tpl_multiselect({
            'id': 'group_<%= id %>_ci_select',
            'title_selected': 'has access to',
            'title_deselected':'has no access to'
        });

        var tpl =
            '<tr id="group-edit-cacti-instances-<%= id %>">' +
            '<td colspan="4" class="group-edit-cacti-instances">' +
            ms +
            '</td></tr>';

        return _.template(tpl);
    };

    // An API account result row
    var api_accounts_result_list_item = function() {

        var tpl =
            '<tr id="api-account-<%= id %>">' +
            '<td id="col-account-name-<%= id %>" class="account-name">&nbsp;</td>' +
            '<td id="col-account-secret-<%= id %>">&nbsp;</td>' +
            '<td class="actions"><a id="btn-edit-access-<%= id %>" href="#">edit access</a></td>' +
            '<td id="col-account-active-<%= id %>" class="active">&nbsp;</td>' +
            '</tr>';

        return _.template(tpl);
    };

    var api_account_show_username = function() {
        var tpl =
            '<a id="<%= id %>" href="#"><%= name %></a>';

        return _.template(tpl);
    };

    var api_account_edit_username = function() {
        var tpl =
            '<input id="<%= id %>" type="text" name="account_name" value="<%= name %>">';

        return _.template(tpl);
    };

    var api_account_show_secret = function() {
        var tpl =
            '<a id="<%= id %>" href="#">********</a>';

        return _.template(tpl);
    };

    var api_account_edit_secret = function() {
        var tpl =
            '<input id="<%= id %>" type="text" name="account_secret" value="<%= secret %>">';

        return _.template(tpl);
    };

    var col_api_account_is_active = function() {
        var tpl =
            '<a id="<%= btn_id %>" class="btn btn-<%= btn_class %>">' +
            '<span class="glyphicon glyphicon-<%= icon_class %> icon-white"></span>' +
            '</a>';

        return _.template(tpl);
    };

    var api_account_cacti_instances_list_item = function() {
        var tpl_multiselect = multi_select();
        var ms = tpl_multiselect({
            'id': 'api-account-<%= id %>-ci-select',
            'title_selected': 'has access to',
            'title_deselected':'has no access to'
        });

        var tpl =
            '<tr id="api-account-edit-cacti-instances-<%= id %>">' +
            '<td colspan="4" class="api-account-edit-cacti-instances">' +
            ms +
            '</td></tr>';

        return _.template(tpl);
    };

    var wmap_result_list_item = function(selected) {
        var tpl_wmap_link = (selected) ? wmap_link_selected() : wmap_link_deselected();
        var row_class = (selected) ? 'selected' : '';

        var wmap_link = tpl_wmap_link({
            'wmap_id': '<%= wmap_id %>',
            'wmap_name': '<%= wmap_name %>'
        });

        var tpl =
            '<tr id="row-wmap-<%= wmap_id %>" class="' + row_class + '">' +
            '<td class="cacti-instance"><%= ci_name %></td>' +
            '<td id="col-wmap-link-<%= wmap_id %>">' + wmap_link + '</td>' +
            '</tr>';

        return _.template(tpl);
    };

    var wmap_link_selected = function() {
        var tpl =
            '<span id="wmap_link_<%= wmap_id %>"><%= wmap_name %></span>';

        return _.template(tpl);
    }

    // Weathermap link
    var wmap_link_deselected = function() {
        var tpl =
            '<a id="wmap-link-<%= wmap_id %>" class="wmap_link" href="#"><%= wmap_name %></a>';

        return _.template(tpl);
    };

    // A Graph result row
    var graph_result_list_item = function(selected) {
        var fn_graph_link = graph_link_deselected();
        var class_selected = (selected) ? 'selected' : '';

        if (selected) {
            fn_graph_link = graph_link_selected();
        }

        var graph_link = fn_graph_link({
            'id': '<%= graph_link_id %>',
            'graph_name': '<%= graph_name %>'
        });

        var tpl =
            '<tr id="row-graph-<%= graph_id %>" class="' + class_selected + ' <%=class_selection%>">' +
            '<td id="col-cacti-instance-<%= graph_id%>" class="cacti-instance"><%= ci_name %></td>' +
            '<td>' + graph_link + '</td>' +
            '<td class="actions">' +
            '<a href="#" id="export-graph-pdf-<%= graph_id %>" class="export-single-graph-icon">' +
            '<span class="glyphicon glyphicon-print" id="export-single-graph-pdf-icon-<%= graph_id %>"></span>' +
            '</a></td></tr>';

        return _.template(tpl);
    };

    // Graph link
    var graph_link_selected = function() {
        var tpl =
            '<span id="<%= id %>"><%= graph_name %></span>';

        return _.template(tpl);
    };

    // Graph link
    var graph_link_deselected = function() {
        var tpl =
            '<a class="graph_link" href="#" id="<%= id %>"><%= graph_name %></a>';

        return _.template(tpl);
    };

    // The 'add all graphs to selection' link
    var select_all_graphs = function() {
        var tpl =
            '<a id="<%= id %>" href="#">add all to selection</a>';

        return _.template(tpl);
    };

    // Show a warning when there are too many results for selecting all
    var select_all_max_warning = function() {
        var tpl =
            '<span id="<%= id %>">' +
            '(too many results to be able to select all (max. <%= max %>)' +
            '</span>';

        return _.template(tpl);
    };

    // The html for an item in the graph selection
    var selected_graph_item = function() {
        var tpl =
            '<div class="col-xs-6 graph-selected" id="graph-selected-<%= graph_id %>">' +
            '<div class="thumbnail">' +
            '<a href="<%= hlink_uri %>" title="<%= hlink_title %>" target="_blank">' +
            '<img id="graph-<%= graph_id %>" class="graph-img" src="<%= hlink_img %>" alt="<%= graph_name %>">' +
            '</a>' +
            '<div class="caption">' +
            '<h3 class="graph-description"><%= ci_name %> - <%= graph_name %></h3>' +
            '<div class="actions"><button type="button" class="btn btn-danger btn-deselect-graph" id="deselect-btn-<%= graph_id %>">close</button></div>' +
            '<div class="clearfix"></div></div></div></div>';

        return _.template(tpl);
    };

    // The html for an item in the weather map selection
    var selected_wmap_item = function() {

        var tpl =
            '<div class="thumbnail" id="wmap-selected-<%= wmap_id %>">' +
            '<a href="#" class="pull-right btn-deselect-graph" id="deselect-btn-<%= wmap_id %>">&times;</a>' +
            '<a href="<%= hlink_uri %>" title="<%= hlink_title %>" target="_blank">' +
            '<p class="wmap-description"><%= ci_name %> - <%= wmap_name %></p>' +
            '<img id="wmap-<%= wmap_id %>" class="wmap-img" src="<%= hlink_img %>" alt="<%= wmap_name %>">' +
            '</a></div>';

        return _.template(tpl);
    };

    var single_graph_to_pdf_form = function() {
        var tpl =
            '<form action="<%= action %>" method="post" id="<%= form_id %>">' +
            '<input type="hidden" id="input-graph" name="graph" value="<%= graph_id %>">' +
            '</form>';

        return _.template(tpl);
    };

    var graph_selection_to_pdf_form = function() {
        var tpl =
            '<form action="<%= action %>" method="post" id="<%= form_id %>">' +
            '<input type="hidden" id="input-graphs-selected" name="graphs_selected">' +
            '<input type="hidden" id="input-rra-id" name="rra_id">' +
            '</form>';

        return _.template(tpl);
    };

    var saved_selection_list_item = function() {
        var tpl =
            '<tr>' +
            '<td class="cacti-instance"><%= ci_name %></td>' +
            '<td class="graph"><a href="<%= graph_url %>"><%= graph_title %></a></td>' +
            '<td class="position"><select name="position" class="select-pos" id="select-pos-<%= item_id %>"><%= pos_options %></select></td>' +
            '</tr>';

        return _.template(tpl);
    };

    var eventlog_list_item = function() {
        var tpl =
            '<tr>' +
            '<td><%= date %></td>' +
            '<td><%= username %> (<%= userid%>)</td>' +
            '<td><%= client_ip %></td>' +
            '<td><%= request_uri %></td>' +
            '<td><%= message %></td>' +
            '</tr>';

        return _.template(tpl);
    };

    // Export public functions
    ns.html.graph_result_list_item = graph_result_list_item;
    ns.html.wmap_result_list_item = wmap_result_list_item;
    ns.html.wmap_link_selected = wmap_link_selected;
    ns.html.wmap_link_deselected = wmap_link_deselected;
    ns.html.cacti_instance_result_list_item = cacti_instance_result_list_item;
    ns.html.cacti_instance_edit_name = cacti_instance_edit_name;
    ns.html.cacti_instance_show_name = cacti_instance_show_name;
    ns.html.cacti_instance_edit_base_url = cacti_instance_edit_base_url;
    ns.html.cacti_instance_show_base_url = cacti_instance_show_base_url;
    ns.html.users_result_list_item = users_result_list_item;
    ns.html.col_user_is_active = col_user_is_active;
    ns.html.groups_result_list_item = groups_result_list_item;
    ns.html.col_group_is_active = col_group_is_active;
    ns.html.group_show_name = group_show_name;
    ns.html.group_edit_name = group_edit_name;
    ns.html.groups_cacti_instances_list_item = groups_cacti_instances_list_item;
    ns.html.api_accounts_result_list_item = api_accounts_result_list_item;
    ns.html.col_api_account_is_active = col_api_account_is_active;
    ns.html.api_account_show_username = api_account_show_username;
    ns.html.api_account_edit_username = api_account_edit_username;
    ns.html.api_account_show_secret = api_account_show_secret;
    ns.html.api_account_edit_secret = api_account_edit_secret;
    ns.html.api_account_cacti_instances_list_item = api_account_cacti_instances_list_item;
    ns.html.selected_graph_item = selected_graph_item;
    ns.html.selected_wmap_item = selected_wmap_item;
    ns.html.select_all_graphs = select_all_graphs;
    ns.html.select_all_max_warning = select_all_max_warning;
    ns.html.msg_container = msg_container;
    ns.html.single_graph_to_pdf_form = single_graph_to_pdf_form;
    ns.html.graph_selection_to_pdf_form = graph_selection_to_pdf_form;
    ns.html.saved_selection_list_item = saved_selection_list_item;
    ns.html.eventlog_list_item = eventlog_list_item;
    ns.html.graph_link_selected = graph_link_selected;
    ns.html.graph_link_deselected = graph_link_deselected;
}(CAPO));

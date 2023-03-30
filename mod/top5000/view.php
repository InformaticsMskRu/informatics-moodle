<?php  // $Id: view.php,v 1.4 2006/08/28 16:41:20 mark-nielsen Exp $
/**
 * This page prints a particular instance of usermonitor
 * 
 * @author 
 * @version $Id: view.php,v 1.4 2006/08/28 16:41:20 mark-nielsen Exp $
 * @package usermonitor
 **/

    require_once("../../config.php");
    require_once("lib.php");

    $id = optional_param('id', 0, PARAM_INT); // Course Module ID, or
    if ($id) {
        if (! $cm = get_record("course_modules", "id", $id)) {
            error("Course Module ID was incorrect");
        }
    
        if (! $course = get_record("course", "id", $cm->course)) {
            error("Course is misconfigured");
        }
    
        if (! $top5000 = get_record("top5000", "id", $cm->instance)) {
            error("Course module is incorrect");
        }

    } else {
        if (! $top5000 = get_record("top5000", "id", $a)) {
            error("Course module is incorrect");
        }
        if (! $course = get_record("course", "id", $top5000->course)) {
            error("Course is misconfigured");
        }
        if (! $cm = get_coursemodule_from_instance("top5000", $top5000->id, $course->id)) {
            error("Course Module ID was incorrect");
        }
    }
    require_login($course->id);

    add_to_log($course->id, "top5000", "view", "view.php?id=$cm->id", "$top5000->id");

    /// Print the page header

    $strtop5000s = get_string("modulenameplural", "top5000");
    $strtop5000  = get_string("modulename", "top5000");

    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = "$strtop5000";
    }

    print_header( "$course->shortname: $top5000->name",
                  $course->fullname,
                "$navigation",
                  '',
                  '<style type="text/css">@import url('.$CFG->wwwroot.'/test_monitor/Styles/main.css);</style>',
                  true,
                  "",
                  navmenu($course, $cm)
                );  

    $content = '';

       ?>
    <script type="text/javascript" language="javascript" src="//code.jquery.com/jquery-1.11.1.js"></script>
    <script type="text/javascript" language="javascript" src="/jqpagination/jquery.jqpagination.js"></script>
    <style>
    .marked-row {background-color: #BABAB3}
    .header {
        background-color:#eeeeee !important;
        border: 1px solid #CCCCCC !important; 
    }
    #pagination {
        float: right;
    }
    .my-box {
        border: 1px solid #CCCCCC !important;
    }

    .sortable {
        cursor:pointer;
        /* Для Mozilla FireFox */ 
       -moz-user-select: none; 
       /* Для Safari, Chrome */ 
       -khtml-user-select: none; 
       /* Общее свойство */ 
       user-select: none; 
    }

    .no-sorting{
        background:url(data:image/gif;base64,R0lGODlhCwALAJEAAAAAAP///xUVFf///yH5BAEAAAMALAAAAAALAAsAAAIUnC2nKLnT4or00PvyrQwrPzUZshQAOw==) no-repeat center right;
    }
    .sorting-asc{
        background:url(data:image/gif;base64,R0lGODlhCwALAJEAAAAAAP///xUVFf///yH5BAEAAAMALAAAAAALAAsAAAIRnC2nKLnT4or00Puy3rx7VQAAOw==) no-repeat center right;
    }
    .sorting-desc{
        background:url(data:image/gif;base64,R0lGODlhCwALAJEAAAAAAP///xUVFf///yH5BAEAAAMALAAAAAALAAsAAAIPnI+py+0/hJzz0IruwjsVADs=) no-repeat center right;
    }
    td {
        border: 1px solid #CCCCCC !important;
        text-align: center;
    }
    </style>
    <link rel="stylesheet" type="text/css" href="/jqpagination/jqpagination.css">

    <div id="my-box">
    <div style="float:left">
    Показывать на странице:
    <select id="size_input" onchange="submit_filter()">
      <option value="10">10</option>
      <option value="20">20</option>
      <option value="50">50</option>
      <option value="100">100</option>
    </select>
    </div>
    <div style="float:right"> 
        Фильтровать в группе: 
        <select id="group_input" onchange="submit_filter()">                    
        </select>
    </div>
    <table id="rating-table" class="BlueTable my-box" cellspacing="0" cellpadding="2" width="100%" style="clear:both">
    <thead>
        <tr class="Caption">
        <th width="50" class="header">Место</th>
        <th width="300" class="header">Участник</th>
        <th width="100" class="header">Город</th>
        <th class="header">Школа</th>
        <th width="40" class="header sortable sort-by sorting-desc">
            Решено задач
        </th>
        <th width="40" class="header sortable no-sorting">
            Решено за 7 дней
        </th>
        </tr>
        <tr class="Caption">
        <th class="header">---</th>
        <th class="header"><input id="name_input" type="text" onchange="submit_filter()"></th>
        <th class="header"><input id="city_input" type="text" onchange="submit_filter()"></th>
        <th class="header"><input id="school_input" type="text" onchange="submit_filter()"></th>
        <th class="header"><input id="solved_input" type="text" onchange="submit_filter()"></th>
        <th class="header"><input id="solved_week_input" type="text" onchange="submit_filter()"></th>
        </tr>
    </thead>

    <tfoot class="Caption">
        <tr>
        <th class="header">Место</th>
        <th class="header">Участник</th>
        <th class="header">Город</th>
        <th class="header">Школа</th>
        <th class="header">Решено задач</th>
        <th class="header">Решено за 7 дней</th>
        </tr>
    </tfoot>

    <tbody>                    
    </tbody>
    </table>
    </div>
    <div class="rating pagination" id="pagination">
            <a href="#" class="first" data-action="first">&laquo;</a>
            <a href="#" class="previous" data-action="previous">&lsaquo;</a>
            <input type="text" readonly="readonly" data-max-page="0" />
            <a href="#" class="next" data-action="next">&rsaquo;</a>
            <a href="#" class="last" data-action="last">&raquo;</a>
    </div>
    <script>

        function fill_table(data) {
            var rating_url = "/py-dev/rating/get";
            $("#rating-table tbody").empty()    
            if (data['current_user_data'] != null && data['current_user_data']['position'] == "first") {
                $("#rating-table tbody").append(
                    $("<tr>").append(
                        $("<td>").append(
                            data['current_user_data']['place']
                        ),
                        $("<td>").append(
                            $("<a href='/user/view.php?id="+data['current_user_data']['id']+"'>").append(data['current_user_data']['name'])
                        ),
                        $("<td>").append(
                            data['current_user_data']['city']
                        ),
                        $("<td>").append(
                            data['current_user_data']['school']
                        ),
                        $("<td>").append(
                            data['current_user_data']['solved']
                        ),
                        $("<td>").append(
                            data['current_user_data']['solved_week']
                        )
                    )
                );
                $("#rating-table tbody").append(
                    $("<tr>").append(
                        $("<td colspan='6'>").append(
                            "..."
                        )
                    )
                );
            }
            for (id in data['data']) {
                tr_elem = $("<tr>").append(
                            $("<td>").append(
                                data['data'][id]['place']
                            ),
                            $("<td>").append(
                                $("<a href='/user/view.php?id="+data['data'][id]['id']+"'>").append(data['data'][id]['name'])
                            ),
                            $("<td>").append(
                                data['data'][id]['city']
                            ),
                            $("<td>").append(
                                data['data'][id]['school']
                            ),
                            $("<td>").append(
                                data['data'][id]['solved']
                            ),
                            $("<td>").append(
                                data['data'][id]['solved_week']
                            )
                        );
                $("#rating-table tbody").append(
                    tr_elem
                );
            }
            if (data['current_user_data'] != null && data['current_user_data']['position'] == "last") {
                $("#rating-table tbody").append(
                    $("<tr>").append(
                        $("<td colspan='6'>").append(
                            "..."
                        )
                    )
                );
                $("#rating-table tbody").append(
                    $("<tr>").append(
                        $("<td>").append(
                            data['current_user_data']['place']
                        ),
                        $("<td>").append(
                            $("<a href='/user/view.php?id="+data['current_user_data']['id']+"'>").append(data['current_user_data']['name'])
                        ),
                        $("<td>").append(
                            data['current_user_data']['city']
                        ),
                        $("<td>").append(
                            data['current_user_data']['school']
                        ),
                        $("<td>").append(
                            data['current_user_data']['solved']
                        ),
                        $("<td>").append(
                            data['current_user_data']['solved_week']
                        )
                    )
                );
            }
        }

        function reload_pagination() {
            $("#pagination").jqPagination('option', 'current_page', 1);
        }

        function init_pagination(data_max_page) {
            $.jqPagination('destroy')
            $("#pagination").empty();
            $("#pagination").append(
                '<a href="#" class="first" data-action="first">&laquo;</a>', 
                '<a href="#" class="previous" data-action="previous">&lsaquo;</a>',
                '<input type="text" reааadonly="readonly" data-max-page="' + data_max_page + '" />',
                '<a href="#" class="next" data-action="next">&rsaquo;</a>',
                '<a href="#" class="last" data-action="last">&raquo;</a>'
            );
            $("#pagination").jqPagination({
                paged: function(page) {
                    var rating_url = "/py/rating/get";
                    $.post(rating_url,
                        {
                            length : $("#size_input").val(),
                            page : page,
                            name_filter : $("#name_input").val(),
                            city_filter : $("#city_input").val(),
                            school_filter : $("#school_input").val(),
                            solved_filter : $("#solved_input").val(),
                            group_filter : $("#group_input").val(),
                            solved_week_filter : $("#solved_week_input").val(),
                            'sort-by': $(".sort-by").text(),
                            'sort-type': $(".sort-by").hasClass("sorting-asc") ? "asc" : "desc"
                        },
                        function(data, status) {
                            fill_table(data);
                            $('#pagination').jqPagination('option', 'max_page', Math.max(1, Math.ceil(data['recordsFiltered'] / $("#size_input").val())))
                        }
                    );
                } 
            });
        }

        function init_group_filter(data) {
            $("#group_input").append(
                $("<option value='2'>")
            );
            if (data['group_list'] != null) {
                for (id in data['group_list']) {
                    $("#group_input").append(
                        $("<option value='" + data['group_list'][id]['id'] + "'>").append(data['group_list'][id]['name'])
                    );
                }
            }
        }

        function init_page(data, status) {
            if (status == 'success') {
                fill_table(data);
                init_pagination(Math.ceil(data['recordsFiltered'] / data['data'].length));
                init_group_filter(data)
            }
        }

        function draw_loading_pix() {
            $("#rating-table tbody").empty();
            $("#rating-table tbody").append(
                $("<tr>").append(
                    $("<td colspan='6'>").append(
                        "<img src='/moodle/pix/ajax-loader.gif'> &nbsp; Загрузка..."
                    )
                )
            );
        }    

        function reload_page() {
            reload_pagination();
        }

        function submit_filter() {
            draw_loading_pix();
            reload_page();
        }

        function change_sort_by() {
            if( $(".sort-by")[0] === this) {
                $(this).toggleClass("sorting-desc");
                $(this).toggleClass("sorting-asc");
            }
            else {
                $(".sort-by").removeClass("sorting-asc");
                $(".sort-by").removeClass("sorting-desc");
                $(".sort-by").addClass("no-sorting");
                $(".sort-by").removeClass("sort-by");
                
                $(this).addClass("sort-by");
                $(this).addClass("sorting-desc");
            }
            reload_page();
        }

        $(document).ready(function() {
            draw_loading_pix();
            var rating_url = "/py/rating/get";
            $.post(rating_url,
                {
                    group_list : 'load',
                    group_filter : '2',
                },
                init_page
            );
            $(".sortable").click(change_sort_by)
        });

      </script>
<?php
 
/// Finish the page
    print_footer($course);
?>

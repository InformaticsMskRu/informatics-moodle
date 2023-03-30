require(['jquery', 'jqueryui', 'tmpl', 'handlebars'], function(jQuery) {
	jQuery.noConflict();
	function toggle_show(id) {
		document.getElementById(id).style.display = document.getElementById(id).style.display == 'none' ? 'block' : 'none';
	}

        function background_monitor_loading(contest_id, group_id) {
             var url = '/py/monitor?contest_id=' + contest_id;
             if (group_id > 0) {
                 url += '&group_id=' + group_id;
             }
             jQuery.get(url, (data) => jQuery('#MonitorResult').html(data)).fail(function() { 
                   setTimeout(background_monitor_loading, 2000, contest_id, group_id);
             });
        }

        function add_group_entry(where, elem, content_id_tag_prefix, f) {
                 new_tag = jQuery('<a href="'+window.location.href.replace(/\#.*/g, "").replace(/\&group\_id\=\d+/g,"")+'&group_id='+elem["id"]+'"></a>').attr("class", "list-group-item list-group-item-action py-0 group_elem_id");
                 new_tag.attr("data", elem["id"]).attr("id", "group_elem_id").append(elem["name"]);
                 var current_group_id = jQuery('#group_id').html();

                 if (current_group_id == elem["id"]) {
                      new_tag.addClass("active");
                 }

		 new_tag.on('click', function (e) {
               	     e.preventDefault();
                     jQuery(".group_elem_id").removeClass("active");
                     jQuery(this).addClass("active");
                     window.history.pushState({}, null, jQuery(this).attr("href"));
        	     var group_id = jQuery(this).attr("data");
	             jQuery('#' + content_id_tag_prefix).empty().append(jQuery('#' + content_id_tag_prefix + 'LoadingTpl').html());
             	     var statement_id = jQuery('#statement_id').html();
        	     jQuery('#group_id').html(group_id);
	             f(statement_id, group_id);
                 });
                 where.append(new_tag);
       }

        function create_monitor_group_list(content_id_tag_prefix, f) {
             groups_data = JSON.parse(jQuery('#groups_data')[0].innerHTML);
             add_group_entry(jQuery('#groups_nav'), 
                  {
			"id": "0",
			"name": "Все записавшиеся на курс"
		  },
                  content_id_tag_prefix,
                  f
             );
             groups_data.forEach(function(elem) {
                 add_group_entry(jQuery('#groups_nav'), elem, content_id_tag_prefix, f);
             });
        }

        function create_submit_group_list(content_id_tag_prefix, f) {
             var groups_data = [];
             if (jQuery('#groups_data').length > 0) {
                 groups_data = JSON.parse(jQuery('#groups_data')[0].innerHTML);
             }
	     add_group_entry(jQuery('#groups_nav'),
                  {
			"id": "",
			"name": "Все записавшиеся на курс"
		  },
                 content_id_tag_prefix,
                  f
             );
             add_group_entry(jQuery('#groups_nav'), 
                  {
			"id": "0",
			"name": "Все пользователи"
		  },
                  content_id_tag_prefix,
                  f);
            groups_data.forEach(function(elem) {
                 add_group_entry(jQuery('#groups_nav'), elem, content_id_tag_prefix, f);
             });
        }


	var statement_mode = jQuery('#statement_mode').html();
        var monitor_group_id = jQuery('#group_id').html();
        var monitor_statement_id = jQuery('#statement_id').html();
	if (statement_mode == 'standing') {
            setTimeout(background_monitor_loading, 0, monitor_statement_id, monitor_group_id);
            create_monitor_group_list('MonitorResult', background_monitor_loading);
        }

	function add_subjects_show(problem_id) {
		if (document.getElementById("add_subjects").style.display == 'none') {
			document.getElementById("add_subjects").style.display = 'block';
			jQuery("#add_subjects_content").html("<div style='color:\"#A0A0A0\"; font-size: 12px;'>Загрузка...</div>");
			jQuery.post("/py-source/problem/set/" + problem_id + "/subject", {}, function(result) {
				jQuery("#add_subjects_content").html(result);
			});
		}
		else {
			jQuery("#add_subjects_content").html('');
			document.getElementById("add_subjects").style.display = 'none';
		}
	}

	function ideal_toggle() {
		if (document.getElementById("ideal-solutions").style.display == 'none') {
					document.getElementById("ideal-solutions").style.display = 'block';
		}
		else {
					document.getElementById("ideal-solutions").style.display = 'none';
		}
	}

	function hint_toggle() {
		if (document.getElementById("hint-list").style.display == 'none') {
					document.getElementById("hint-list").style.display = 'block';
		}
		else {
					document.getElementById("hint-list").style.display = 'none';
		}
	}

	function add_sources_show(problem_id) {
		if (document.getElementById("add_sources").style.display == 'none') {
			document.getElementById("add_sources").style.display = 'block';
			jQuery("#add_sources_content").html("<div style='color:\"#A0A0A0\"; font-size: 12px;'>Загрузка...</div>");
			jQuery.post("/py-source/problem/set/" + problem_id + "/source", {}, function(result) {
				jQuery("#add_sources_content").html(result);
			});
		}
		else {
			jQuery("#add_sources_content").html('');
			document.getElementById("add_sources").style.display = 'none';
		}
	}


	function cur_subjects_show(problem_id) {
		if (document.getElementById("cur_subjects").style.display == 'none') {
			document.getElementById("cur_subjects").style.display = 'block';
			jQuery("#cur_subjects_content").html("<div style='color:\"#A0A0A0\"; font-size: 12px;'>Загрузка...</div>");
			jQuery.getJSON("/py-source/problem/get/" + problem_id + "/subject", {}, function(result) {
				var res = '<b style="font-size: 12px;"><font color="#009000">Темы:</font> ';
				for (var i = 0; i < result.length; ++i) {
					res += ' <a href="/py-source/source/dir/' + result[i].parent.id + '">[' + result[i].parent.name + ']</a>';
				}
				res += '</b>';
				jQuery("#cur_subjects_content").html(res);
			})
		}
		else {
			jQuery("#cur_subjects_content").html('');
			document.getElementById("cur_subjects").style.display = 'none';
		}
	}

	function cur_sources_show(problem_id) {
		if (document.getElementById("cur_sources").style.display == 'none') {
			document.getElementById("cur_sources").style.display = 'block';
			jQuery("#cur_sources_content").html("<div style='color:\"#A0A0A0\"; font-size: 12px;'>Загрузка...</div>");
			jQuery.get("/py-source/problem/get/" + problem_id + "/source/html", {}, function(result) {
				var res = '<b style="font-size: 12px;">' + result + '</b>';
				jQuery("#cur_sources_content").html(res);
			})
		}
		else {
			jQuery("#cur_sources_content").html('');
			document.getElementById("cur_sources").style.display = 'none';
		}
	}
	jQuery( "#show_source" ).click(function() {
		jQuery.get(
			"/py-source/problem/get/" + $("#problem_data").attr("problem_id") + "/source/html",
			'',
			function(data) {
				jQuery("#source_py").html(data);
				jQuery("#source_py").show();
			}
		);
		return false;                           
	});

    jQuery( "#invert_limits" ).click(function() {
		var problem_id = $("#problem_data").attr("problem_id");
		var limit_action = $("#problem_data").attr("limit_action");
       	jQuery.get(
			"/py/problem/" + problem_id + "/limits/" + limit_action,
			'',
			function() {
				location.reload();
			}
		);
		return false;                           
	});


	jQuery('#problem_tests').show();
    jQuery('#myModal .add_test_button').ajaxError(
    	function() {
			jQuery('#myAlert').html("<div class='alert alert-error'><a class='close' data-dismiss='alert'>×</a><span>Ошибка запроса</span></div>");  
			jQuery('#myModal').modal('hide');                    
		}
    );
            
	jQuery('#myModal .add_test_button').click(
    	function() {
			var problem_id = $("#problem_data").attr("problem_id");
			jQuery.post(
				'/py/problem/' + problem_id + '/tests/add',
				{
					input_data : jQuery('#input_data').val(),
					output_data : jQuery('#output_data').val()
				},
				function(data) {
					if (data.result == 'error') {
						jQuery('#myAlert').html("<div class='alert alert-error'><a class='close' data-dismiss='alert'>×</a><span>"+data.content+"</span></div>");
					} else {
						jQuery('#myAlert').html("<div class='alert alert-success'><a class='close' data-dismiss='alert'>×</a><span> Добавлен тест "+data.content+"</span></div>");
					}
					jQuery('#myModal').modal('hide');
				}                       
			);
		}
	);
	
	var sample_tests = $("#problem_data").attr("sample_tests");
	
	jQuery('#problem_tests_stuse').click(
		function() {
			var problem_id = $("#problem_data").attr("problem_id");
			sample_tests = '';
			jQuery('[test_num].info').each(
				function () {
					if (sample_tests !== '') {
					   sample_tests += ',';
					}
					sample_tests += jQuery(this).attr('test_num');
				}
			);
			
			jQuery.post(
				'/py/problem/' + problem_id + '/tests/set_preliminary',
				{
					sample_tests : sample_tests,
				},
				function(data) {
					if (data.result == 'error') {
						jQuery('#myAlert').html("<div class='alert alert-error'><a class='close' data-dismiss='alert'>×</a><span>"+data.content+"</span></div>");
					} else {
						jQuery('#myAlert').html("<div class='alert alert-success'><a class='close' data-dismiss='alert'>×</a><span> "+sample_tests+"</span></div>");
					}
				}                       
			);
			return false;
		}
	);
	
	jQuery('#problem_generate_samples').click(  
		function() {
			var problem_id = $("#problem_data").attr("problem_id");
			jQuery.get(
				'/py/problem/' + problem_id + '/generate_samples',
				'',
				function(data) {
					if (data.result != 'error') {
						jQuery('#myAlert').html('<pre>' + data['content'] + '</pre>');
					} else {
						jQuery('#myAlert').html("<div class='alert alert-error'><a class='close' data-dismiss='alert'>×</a><span>"+data.content+"</span></div>");
					}                        
				}
			);
			return false;
		}
	);
	
	jQuery('#statement_source').click(
		function () {
			var problem_id = $("#problem_data").attr("problem_id");
			var statement_id = $("#problem_data").attr("statement_id");
			jQuery.post('/py-source/source/adm/form', {}, function(data) {
					jQuery('#source_tree_div_body').html(data);
					document.getElementById('make_contest_statement_id').value = $statement_id;
					jQuery('#source_tree_div').show();
				});
		}
	);
	
	jQuery('#problem_tests_load').click(
		function() {
			var problem_id = $("#problem_data").attr("problem_id");
			jQuery.get(
				'/py/problem/' + problem_id + '/tests/count',
				'',
				function(data) {
					table = jQuery('<table class="table table-condensed table-bordered table-hover"/>');
					for (i = 0; i < data; ++i) {
						el = jQuery('<tr test_num="' + (i+1) + '"/>');
						if (sample_tests.split(',').indexOf(String(i + 1)) !== -1) {
						   el.addClass('info');
						}
						el.click( function() {
							if (jQuery(this).hasClass('info')) {
								jQuery(this).removeClass('info');
							} else {
								jQuery(this).addClass('info');
							}
						} );
						t_td = jQuery('<td class="test_' + (i + 1) + '">&nbsp;</td>');
						c_td = jQuery('<td class="corr_' + (i + 1) + '"/&nbsp;</td>');
						jQuery('<td>' + (i+1) + '</td>').appendTo(el);
						t_td.appendTo(el);
						c_td.appendTo(el);
						el.appendTo(table);
					}
					jQuery('#problem_tests').empty();
					table.appendTo(jQuery('#problem_tests'));
					jQuery('#problem_tests_stuse').removeClass('hide');
					for (i = 0; i < data; ++i) {
						jQuery.getJSON(
							'/py/problem/' + problem_id + '/tests/test/' + (i+1),
							'',
							function(data) {
								if (data.result != 'error') {
									jQuery('.test_' + data['num']).text(data['content']);
								} else {
									jQuery('.test_' + data['num']).html("<div class='alert alert-error'><a class='close' data-dismiss='alert'>×</a><span>"+data.content.text()+"</span></div>");
								}
							}
						);
						
						jQuery.getJSON(
							'/py/problem/' + problem_id + '/tests/corr/' + (i+1),
							'',
							function(data) {
								jQuery('.corr_' + data['num']).text(data['content']);
							}
						);                                
					}
				}
			);
			return false;  
		}
	);

            function showCommentEvent(run_id){
                return function addCommentEvent(data) {
                    comments = jQuery.parseJSON(data);
                    var markup = "<div>${date} - <a href=\"/moodle/user/view.php?id=${author_user.id}\">${author_user.firstname} ${author_user.lastname}</a><br/><pre>${comment}</pre><hr/></div>";
                    jQuery("#comment_content" + run_id).html(jQuery.tmpl(markup, comments));
                    jQuery("#comment_content" + run_id).removeClass('frame_loading');
                }
            }

            function Comment(run_id) {
                jQuery.post(
                    "/py/comment/add",
                    {
                        run_id : run_id,
                        comment :  jQuery("#comment_text"+ run_id)[0].value,
                        lines : ""
                    },
                    function() {
                        jQuery.ajax({
                            url: "/py/comment/get/" + run_id,
                            context: document.body,
                            contentType: "application/json; charset=utf-8",
                            success: showCommentEvent(run_id)
                        });
                        if (temp = jQuery("#comment_text" + run_id)[0]) {
                            temp.value = "";
                        }
                    }
                );
                return false;
            }
           


            function langToPrismLangName(lang_id){
                lang_map = {
                    1: 'pascal',
                    2: 'c',
                    3: 'cpp',
                    7: 'pascal',
                    8: 'pascal',
                    9: 'c',  
                    10: 'c',      
                    18: 'java',
                    22: 'php',
                    23: 'python',
                    24: 'perl',
                    25: 'csharp',
                    26: 'ruby',
                    27: 'python',
                    28: 'haskell',
                    29: 'pascal',
                    30: 'pascal',  
                    68: 'cpp',
                    53: 'go',
                    89: 'scala',
                    71: 'kotlin',
                };
                return lang_map[lang_id];
            }
			function initPagination() {
                create_submit_group_list('Searchresult', function(statement_id, group_id) {
                     console.warn(statement_id + " " + group_id);
                     jQuery("#Pagination").trigger("currentPage");
                });
		jQuery('#Searchresult').empty().append(jQuery('#SearchresultLoadingTpl').html());	
                //jQuery('#Searchresult').hide();
                //jQuery('#SearchresultLoading').show();
            
                if (window.location.hash == '') 
                {
                    window.location.hash = 1;
                }

                var problemId =   jQuery('#problem_id').html();
                var groupId =    jQuery('#group_id').html();
                var withComment =    jQuery('#withComment').html();
                var langId =    jQuery('#language_id').html();
                var statementId = jQuery('#statement_id').html();
                var userId =      jQuery('#user_id').html();                          
                var statusId =    jQuery('#status_id').html();                                
                var fromTimestamp =       jQuery('#from_timestamp').html();                           
                var toTimestamp =         jQuery('#to_timestamp').html();                             
                var count =       jQuery('#count').html();
                var run_id =      jQuery('#run_id').html();
                if (problemId == 0) {
                    var current_page_id = jQuery('#current_page_id').html();
                    statementId = current_page_id;
                }

                // For initial load, we need to retrieve
                // runs for for requested page index.
                // If no page_index supplied, it wiil be
                // redirected to #1
                var page_index;
                try {
                    page_index = document.URL.substr(document.URL.indexOf('#')+1);
                    page_index = parseInt(page_index)
                } catch (e) {
                    page_index = 1;
                }

                var params = {
                    problem_id : problemId,
                    from_timestamp: fromTimestamp,
                    to_timestamp: toTimestamp,
                    user_id: userId,
                    lang_id: langId,
                    status_id: statusId,
                    statement_id: statementId,
                    count: count,
                    with_comment: withComment,
                    page: page_index,
                };

                if (groupId != "") {
                    params["group_id"] = groupId;
                }
 
                var json = jQuery.getJSON(
                    '/py/problem/' + problemId + '/filter-runs',
                    params,
                    function(data) {
                        // Save runs for current problem to not re-load it later
                        window.CACHED_RUNS = data;

                        var num_entries = data.metadata.page_count;
                        window.PAGE_COUNT = data.metadata.page_count;
                        // Create pagination element
                        var paginate = jQuery("#Pagination")
                            .pagination(num_entries, {
                                num_edge_entries: 2,
                                current_page : window.location.hash.substring(1) - 1,
                                num_display_entries: 5,
                                callback: pageselectCallback,
                                show_if_single_page: true,
                                items_per_page:1,
                                next_text : '>',
                                prev_text : '<',
                                reload_text: 'Обновить'
                            });
						 jQuery("#ArchiveButton")
                         .bind("click", function (e) {
                                e.preventDefault();
                                window.location.href = '/ajax/ajax.php?problem_id='+problemId+'&'
                                    +'user_id='+userId+'&'
                                    +'group_id='+groupId+'&'
                                    +'lang_id='+langId+'&'
                                    +'from_timestamp='+fromTimestamp+'&'
                                    +'to_timestamp='+toTimestamp+'&'	
                                    +'statement_id='+statementId+'&'
                                    +'objectName=submits&'
                                    +'action=downloadRun&'
                                    +'status_id='+statusId;
                            })
                            
                        //jQuery('#SearchresultLoading').hide();						
                        jQuery('#Searchresult').show();
                        jQuery("[class=round_sb]").blur();
                        jQuery('#resetFocus').focus();							
                    }
                );
            }
			function pageselectCallback(page_index, jq){
                jQuery('#Searchresult').empty().append(jQuery('#SearchresultLoadingTpl').html());
                //jQuery('#SearchresultLoading').show();
            
                window.location.hash = page_index + 1;
                
                var problemId =   jQuery('#problem_id').html();
                var groupId =    jQuery('#group_id').html();
                var withComment =    jQuery('#withComment').html();
                var langId =    jQuery('#language_id').html();
                var statementId = jQuery('#statement_id').html();
                var userId = 	  jQuery('#user_id').html();				
                var statusId = 	  jQuery('#status_id').html();				
                var fromTimestamp = 	  jQuery('#from_timestamp').html();				
                var toTimestamp = 	  jQuery('#to_timestamp').html();				
                var count = 	  jQuery('#count').html();
                var run_id = 	  jQuery('#run_id').html();
                if (problemId == 0) {
                    var current_page_id = jQuery('#current_page_id').html();
                    statementId = current_page_id;
                }
                var params = {
                    problem_id : problemId,
                    from_timestamp: fromTimestamp,
                    to_timestamp: toTimestamp,
                    //group_id: groupId,
                    user_id: userId,
                    lang_id: langId,
                    status_id: statusId,
                    statement_id: statementId,
                    count: count,
                    with_comment: withComment,
                    page: page_index + 1,
                };
               
                if (groupId != "") {
                    params["group_id"] = groupId;
                }
 
                // console.log(run_id);
                // console.log(window.CACHED_RUNS);
                // If it\'s initial load, use data,
                // previously fetched in pagination request
                if (window.CACHED_RUNS) {
                    renderRunsTable(run_id, window.CACHED_RUNS);
                    delete window.CACHED_RUNS;
                    return;
                }

                // If no pre-loaded runs data found,
                // fetch runs from server

                var json = jQuery.getJSON(
                    '/py/problem/' + problemId + '/filter-runs',
                    params,
                    function(data) {
                         if (data.metadata.page_count != window.PAGE_COUNT) {
                         var paginate = jQuery("#Pagination")
                            .pagination(data.metadata.page_count, {
                                num_edge_entries: 2,
                                current_page : Math.min(window.location.hash.substring(1) - 1, data.metadata.page_count - 1),
                                num_display_entries: 5,
                                callback: pageselectCallback,
                                show_if_single_page: true,
                                items_per_page:1,
                                next_text : '>',
                                prev_text : '<',
                                reload_text: 'Обновить'
                            });
                            window.PAGE_COUNT = data.metadata.page_count;
                        }

                        renderRunsTable(run_id, data);
                    }
                );
                return false;
            }
	        function loadSourceWindow(run_id){
                var master_sid = jQuery('#run_master_sid').html();
                var wnd = jQuery("#protocolRow" + run_id);
                wnd.toggle();
                var content = `
                    <ul class="nav nav-tabs" id="ProtocolTab{{run_id}}" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link active" id="source-tab" data-toggle="tab" href="#sourceTab{{run_id}}" role="tab" aria-controls="source" aria-selected="true">Код</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" id="protocol-tab" data-toggle="tab" href="#protocolTab{{run_id}}" role="tab" aria-controls="protocol" aria-selected="false">Протокол</a>
                      </li>
                       <li class="nav-item">
                        <a class="nav-link" id="compiler-tab" data-toggle="tab" href="#compilerTab{{run_id}}" role="tab" aria-controls="compiler" aria-selected="false">Вывод компилятора</a>
                      </li>
                      {{#if master_sid}}
                      <li class="nav-item">
                        <a class="nav-link" id="full-protocol-tab" data-toggle="tab" href="#fullProtocolTab{{run_id}}" role="tab" aria-controls="full-protocol" aria-selected="false">Полный протокол</a>
                      </li>
                      {{/if}}
                    </ul>
                    <div class="tab-content" id="ProtocolTabContent{{run_id}}">
                      <div class="tab-pane fade show active" id="sourceTab{{run_id}}" role="tabpanel" aria-labelledby="source-tab">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                      </div>
                      <div class="tab-pane fade" id="protocolTab{{run_id}}" role="tabpanel" aria-labelledby="protocol-tab">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                      </div>
                      <div class="tab-pane fade" id="compilerTab{{run_id}}" role="tabpanel" aria-labelledby="compiler-tab">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                      </div>
                   {{#if master_sid}}
                      <div class="tab-pane fade" id="fullProtocolTab{{run_id}}" role="tabpanel" aria-labelledby="full-protocol-tab">
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                      </div>
                    {{/if}}
                    </div>
                `; 
                context = {
                    run_id: run_id,
                    master_sid: master_sid  
                };

                content += 
                "<div style='position: static' id='comment_content"+ run_id + "' name='comment_content"+ run_id + "'><div class='spinner-border text-primary' role='status'><span class='sr-only'>Loading...</span></div></div>";
                
                if (master_sid)
                {
                    content += "<div id='comment_panel' class='bootstrap'><div style='margin-top:7px; padding:10px; position: static' class='ui-widget-content'>"+
                "<div style='static'><a name='only_accept"+ run_id + "'  id='only_accept"+ run_id + "' href='#' class='btn'>Только зачесть</a></div>"+
                "<div  style='position: static;'><a name='submit_comment"+ run_id + "'  id='submit_comment"+ run_id + "' href='#' class='btn'>Прокомментировать</a> или прокомментировать и <a name='submit_acceptcomment"+ run_id + "'  id='submit_acceptcomment"+ run_id + "' href='#' class='btn'>Зачесть</a><a name='submit_stylecomment"+ run_id + "'  id='submit_stylecomment"+ run_id + "' href='#' class='btn'>Стиль</a><a name='submit_ignorecomment"+ run_id + "'  id='submit_ignorecomment"+ run_id + "' href='#' class='btn'>Проигнорировать</a></div>"+
                "<div width='100%'><textarea id='comment_text"+ run_id + "' rows='5' cols='50'></textarea></div></div></div></div>";
                }
                
                const Handlebars = require("handlebars");
                var template = Handlebars.compile(content);
                var html = template(context);

                wnd.find("div[data]").html(html);

                jQuery('#ProtocolTab' + run_id + ' a').on('click', function (e) {
                    e.preventDefault();
                    jQuery(this).tab('show');
                });
                 
                jQuery.ajax({
                    url: "/py/comment/get/" + run_id,
                    context: document.body,
                    contentType: "application/json; charset=utf-8",
                    success: showCommentEvent(run_id)
                });
                jQuery.ajax({
                    url: "/py/problem/run/" + run_id + "/source",
                    context: document.body,
                    success: function(data){
            
                        var lang_id = data.data.language_id;
                        var source = data.data.source;
            
                        prism_attr = langToPrismLangName(lang_id);
                        prism_func = Prism.languages[prism_attr];
                        var html = source      
                        var NEW_LINE_EXP = /\n(?!$)/g;
                        var lineNumbersWrapper = "";

                       if (prism_func) {                 
 Prism.hooks.add('after-tokenize', function (env) {
  var match = env.code.match(NEW_LINE_EXP);
  var linesNum = match ? match.length + 1 : 1;
  var lines = new Array(linesNum + 1).join('<span></span>');

  lineNumbersWrapper = `<span aria-hidden="true" class="line-numbers-rows">${lines}</span>`;
});
                           html = Prism.highlight(source, prism_func, prism_attr);
                        }
                        jQuery("#sourceTab" + run_id).html('<pre class="language-' + prism_attr + ' line-numbers" data-download-link><code>' + html + lineNumbersWrapper +'</code></pre>');
                    }
                });      
                jQuery.ajax({
                    url: "/py/protocol/get/" + run_id,
                    context: document.body,
                    success: function(data){
                        res = jQuery("#protocol-tmpl").tmpl(data, {"stat": getSubmitStatistic(data), "prot": "user"});
                        jQuery("#protocolTab"+ run_id).html(res);
                        prism_func = Prism.languages['shell-session'];
                        var html = '';      
                        if (prism_func) {                 
                            html = Prism.highlight(data["compiler_output"], prism_func, 'shell-session');
                        }
                        jQuery("#compilerTab"+ run_id).html('<pre class="language-shell-session"><code>' + html + '</code></pre>');
                    }
                });
                if (master_sid) {
                    jQuery.ajax({
                        url: "/py/protocol/get-full/" + run_id,
                        context: document.body,
                        success: function(data){
                            res = jQuery("#full-protocol-tmpl").tmpl(
                                data, 
                                {
                                    "run_id": run_id,
                                    "stat": getSubmitStatistic(data),
                                    "prot": "full"
                                }
                            );
                            jQuery("#fullProtocolTab"+ run_id).html(res);
                            grecaptcha.render("recaptcha_element", {
                                "sitekey" : "6Lee4wETAAAAAC4PYfOc2t74KBTBuMvW-HFswHxK"
                            });
                        }
                    });                   
                }
                jQuery(function() {
                    if (master_sid) {
                        jQuery("#submit_comment"+ run_id).button();
                        jQuery("#submit_stylecomment"+ run_id).button();
                        jQuery("#submit_acceptcomment"+ run_id).button();
                        jQuery("#submit_ignorecomment"+ run_id).button();
                        jQuery("#only_accept"+ run_id).button();
                        jQuery("#submit_comment"+ run_id).click(function() {
                            Comment(run_id);
                            wnd.close();
                        });
                        jQuery("#submit_acceptcomment"+ run_id).click(function() {
                            jQuery("#comment_text"+ run_id)[0].value = jQuery("#comment_text"+ run_id)[0].value + "\nРешение зачтено";
                            Comment(run_id);
                            updateRunStatus(run_id, 8);
                            wnd.close();
                        });                    
                        jQuery("#submit_ignorecomment"+ run_id).click(function() {
                            jQuery("#comment_text"+ run_id)[0].value = jQuery("#comment_text"+ run_id)[0].value + "\nРешение проигнорировано";
                            Comment(run_id);
                            updateRunStatus(run_id, 9);
                            wnd.close();
                        });
                        jQuery("#submit_stylecomment"+ run_id).click(function() {
                            jQuery("#comment_text"+ run_id)[0].value = jQuery("#comment_text"+ run_id)[0].value + "\nПлохой стиль программирования";
                            Comment(run_id);
                            updateRunStatus(run_id, 14);
                            wnd.close();
                        });     
                        jQuery("#only_accept"+ run_id).click(function() {
                            updateRunStatus(run_id, 8);
                            wnd.close();
                        });        
                        jQuery("#only_style"+ run_id).click(function() {
                            updateRunStatus(run_id, 14);
                            wnd.close();
                        });           
                        jQuery("#only_ignore"+ run_id).click(function() {
                            updateRunStatus(run_id, 9);
                            wnd.close();
                        });                          
                    }
                });

            }
            function toggleDownloadTests() {
                if ($("#download_all_tests_inp")[0].checked) {
                    $("#download_tests_inp")[0].disabled = true;
                }
                else {
                    $("#download_tests_inp")[0].disabled = false;   
                }
            }

            function getSubmitStatistic(data) {
                tests = data["tests"]
                stat = {"max_cpu": -1,
                        "max_cpu_test": -1, 
                        "max_real_time": -1,
                        "max_real_time_test": -1,
                        "max_memory_used": -1,
                        "max_memory_used_test": -1,
                        "first_failed_test_verdict": -1,
                        "first_failed_test": -1}
                for (var id in tests) {
                    if (parseInt(tests[id].max_memory_used) !== NaN && parseInt(tests[id].max_memory_used) > stat.max_memory_used) {
                        stat.max_memory_used = parseInt(tests[id].max_memory_used);
                        stat.max_memory_used_test = id;
                    }
                    if (parseInt(tests[id].time) !== NaN && parseInt(tests[id].time) > stat.max_cpu) {
                        stat.max_cpu = parseInt(tests[id].time);
                        stat.max_cpu_test = id;
                    }
                    if (parseInt(tests[id].real_time) !== NaN && parseInt(tests[id].real_time) > stat.max_real_time) {
                        stat.max_real_time = parseInt(tests[id].real_time);
                        stat.max_real_time_test = id;
                    }
                    if (tests[id].string_status !== "OK" && stat.first_failed_test == -1) {
                        stat.first_failed_test = id;
                        stat.first_failed_test_verdict = tests[id].string_status;
                    }
                }
                for (var prop in stat) {
                    if (stat[prop] === -1) {
                        stat[prop] = "&mdash;";
                    }
                }
                return stat
            }

/*			function loadProtocolWindow(url)
			{
				wwin = new UI.Window({theme: "alphacube", shadow: false }).setSize(600,500).center().show().setAjaxContent(url,
				{
					method: "GET",
					onCreate: function() {
						this.setContent('<div align="center" valign="center"><img src="/moodle/pix/ajax-loader.gif"> Загрузка...</div>');
					}
				});
				//wwin.adapt;
			}			

			function loadFullProtocolWindow(url)
			{
				wwin = new UI.Window({theme: "alphacube", shadow: false }).setSize(600,500).center().setContent(
				"<iframe name='protocol' id='protocol' height='95%' width='95%' src='"+url+"'/>").show().focus();
				//wwin.adapt;
            }
           */ 
            function addStrStatus(runs){
                statuses_map = {
                    0: 'OK',
                    1: 'CE',
                    2: 'RE',
                    3: 'TL',
                    4: 'PE',
                    5: 'WA',
                    6: 'CF',
                    7: 'Partial',
                    8: 'AC',
                    9: 'Ignore',
                    10: 'Disqualified',
                    11: 'Pending',
                    12: 'ML',
                    13: 'Security error',
                    96: 'Running...',
                    98: 'Compiling...',
                    377: 'In queue',
                    520: 'Submit error',
                };
                var master_sid = jQuery('#run_master_sid').html();
                var current_user_id = jQuery('#current_user_id').html();
                runs.forEach((run) => {
                    run.str_status = statuses_map[run.ejudge_status];
                    run.rejudge_sid = true;
                    run.master_sid = master_sid;
                    run.sb_uid = 'gfhgjghjgh'; // Это уникальный юид, переделать потом
                    run.sid = master_sid || run.user.id == current_user_id;
                });
            }
            
            function addArrStatus(runs){
                array_of_selections = [
                    {selection_status: 0, disabled: false, str: 'OK'},
                    {selection_status: 99, disabled: false, str: 'Перетестировать'},
                    {selection_status: 8, disabled: false, str: 'Зачтено/Принято'},
                    {selection_status: 14, disabled: false, str: 'Ошибка оформления кода'},
                    {selection_status: 9, disabled: false, str: 'Проигнорировано'},
                    {selection_status: 1, disabled: false, str: 'Ошибка компиляции'},
                    {selection_status: 10, disabled: false, str: 'Дисквалифицировано'},
                    {selection_status: 7, disabled: false, str: 'Частичное решение'},
                    {selection_status: 11, disabled: false, str: 'Ожидает проверки'},
                    {selection_status: 2, disabled: true, str: 'Ошибка во время выполненияы'},
                    {selection_status: 3, disabled: true, str: 'Превышено время работы'},
                    {selection_status: 4, disabled: true, str: 'Неправильный формат вывода'},
                    {selection_status: 5, disabled: true, str: 'Неправильный ответ'},
                    {selection_status: 6, disabled: true, str: 'Ошибка проверки'},
                    {selection_status: 12, disabled: true, str: 'Превышение лимита памяти'},
                    {selection_status: 13, disabled: true, str: 'Security error'},
                    {selection_status: 96, disabled: true, str: 'Тестирование...'},
                    {selection_status: 98, disabled: true, str: 'Компилирование...'},
                    {selection_status: 377, disabled: true, str: 'В очереди тестирования'},
                    {selection_status: 520, disabled: true, str: 'Ошибка отправки задачи'}
                ];
                runs.forEach((run) => {
                    run.arr_statuses = array_of_selections; 
                })
            }
            
            function addStrLang(runs){
                runs.forEach((run) => {
                    run.lang_str = lang_map_long_name[run.ejudge_language_id]; 
                })
            }

            function onRunSelectionChange(run_id){
                return function updateRunStatus(sel){
                    json = JSON.stringify({
                        'ejudge_status': sel.value,
                    })
                    jQuery.ajax({
                        url: "/py/problem/run/" + run_id + "/update",
                        type: "PUT",
                        data: json,
                        context: document.body,
                        success: function (data) {
                            jQuery("#Pagination").trigger("currentPage");
                        },
                        error: function (data) {
                            alert(data);
                        }
                    });
                }
            }
            
            function renderResultTable(runs){
                var context = {runs: runs};
                var source = `<table align=center class="table table-striped" width="100%" height="100%">
                <tr class=Caption>
                        <td>ID</td>
                        <td>Участник</td>
                        <td>Задача</td>
                        <td>Дата</td>
                        <td>Язык</td>
                        <td>Статус</td>
                        <td>Пройдено тестов</td>
                        <td>Баллы</td>
                        <td>Подробнее</td>
                    {{#each runs}}
                    <tr>
                        <td>{{id}}</td>
                        <td><a href="/user/view.php?id={{user.id}}">{{user.firstname}} {{user.lastname}}</a></td>
                        <td><a href="/mod/statements/view.php?chapterid={{problem.id}}">{{problem.id}}. {{problem.name}}</a></td>
                        <td>{{datetimeshorter create_time}}</td>
                        <td>{{lang_str}}</td>
                        <td>
                            {{#if rejudge_sid}}
                                <select name="{{sb_uid}}" data_run_id="{{id}}" {{#unless master_sid}} disabled {{/unless}} class="round_sb">
                                    <option value="0" disabled>---</option>
                                    {{#each arr_statuses}}
                                        <option value="{{selection_status}}" {{#ifEquals selection_status ../ejudge_status}}
                                                                    selected="selected"
                                                              {{/ifEquals}}
                                                              {{#if disabled}} 
                                                                    disabled 
                                                              {{/if}}>
                                            {{str}}
                                        </option>
                                    {{/each}}
                                </select>
                            {{/if}}
                        </td>
                        <td>
                            {{ejudge_test_num}}
                        </td>
                        <td>{{ejudge_score}}</td>
                        <td>{{#if sid}}
                        <button type="button" class="btn btn-link" data_run_id="{{id}}">Подробнее</button>
                        {{/if}}</td>
                    </tr>
                    <tr style="display:none" id="protocolRow{{id}}"><td colspan="9"><div data>
                        <div class="d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                    </div></td></tr>
                    {{/each}}
            </table>`
                const Handlebars = require("handlebars");
                Handlebars.registerHelper('ifEquals', function(arg1, arg2, options) {
                    return (arg1 == arg2) ? options.fn(this) : options.inverse(this);
                });
                Handlebars.registerHelper('datetimeshorter', function (aString) {
                    return aString.replace('T', "\n").replace(/\+.*/,"");
                });
                var template = Handlebars.compile(source);
                var html = template(context)
                return html;
            }
            
            function prepareData(data){
                addStrLang(data.data);
                addArrStatus(data.data);
                addStrStatus(data.data);
            }

            function renderRunsTable(run_id, data) {
                prepareData(data);
                var master_sid = jQuery('#run_master_sid').html();
                var result_table = renderResultTable(data.data);
                jQuery('#Searchresult').html(result_table);
                jQuery('button[data_run_id]').on('click', function() {loadSourceWindow(jQuery(this).attr("data_run_id"));} );
                jQuery('select[data_run_id]').on('change', function() { onRunSelectionChange(jQuery(this).attr("data_run_id"))(this); });
                //jQuery('#SearchresultLoading').hide();                        
                //jQuery('#Searchresult').show();;
                
        // Do not init run status selector for non-admin account.
        // For non-admin accounts plugin jQuery SelectBox is not loaded.
        // So running `.sb()` function will raise error, and further
        // JS code will not be executed
        // 
        // More info: https://github.com/revsystems/jQuery-SelectBox
        //if (master_sid) {
        //    jQuery("[class=round_sb]").sb();                            
        //    jQuery("[class=round_sb]").blur();;
        //}
                
        jQuery('#resetFocus').focus();
                if (run_id != "") {
                    var t = run_id.split("r");
                    loadSourceWindow(t[1]);
                }
            }


  
               function updateRunStatus(run_id, status_id) {
                    let json = JSON.stringify({"ejudge_status": status_id});
                    jQuery.ajax({
                        url: "/py/problem/run/" + run_id + "/update",
                        type: "PUT",
                        data: json,
                        context: document.body
                    });
                }

        if (statement_mode == 'submit' || statement_mode == 'statement') {
            initPagination(); 
        }
    jQuery('a[class*="lang_choose_option"]').bind("click", 
    	function() {
			jQuery('#lang_id').dropdown('hide');
			jQuery('#lang_id').html(this.text);
			jQuery('#lang_id').attr('value', this.getAttribute("value"));
            test._settings.data.lang_id = this.getAttribute("value");
			return false;
        }
    );
    jQuery('#submit_button').bind("click", function() { test.submit(); return false; });  
	var problemId = jQuery('#problem_id').html();
        if (jQuery('#submit_button').length) {
		var test = new AjaxUpload(jQuery('#upload_button'), {
			'action': '/py/problem/'+ problemId +'/submit',
		        name: 'file',
		        data: {
        		   lang_id : 3
        		},
		        autoSubmit: false,
		        responseType: "json",
		        onChange: function(file, extension) {
	                    jQuery('#filename').text(file);
			    jQuery.each(lang_array, function() {
				if (this.ext == extension) {
					jQuery('a[id="lang_choose_option"][value="' + this.id + '"]')[0].click();
				}
			    });
        		},
                        onSubmit: function(file, extension) {
                        },
                        onComplete: function(file, response) {
	                    jQuery('#filename').text('');
                            if (!response.submit && response.status != 'success') {
    	                        alert(response.error);
	                    } else {
				jQuery("#Pagination").trigger("currentPage");
            	                //jQuery('#reload').click(); 
        	            }
                        }
	          });
        }     
});


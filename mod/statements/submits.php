<?php

class Submits {
	
	// Пользователь от которого производятся действия
	private $USER = null;
	
	// Пользователь посылки которого хочется просматривать
	private $user_id = 0;

	// Статус посылки
	private $status_id = -1;

    private $from_timestamp = -1;
    private $to_timestamp = -1;

	// Статус посылки
	private $lang_id = -1;

	// Id посылки для отображения, если выбрана задача.
	private $run_id = "";
	
	// Задача посылки по которой хочется просматривать
	private $problem_id = 0;
	
	// Группа посылки которой просматриваются
	private $group_id = "";
	
	// Контест по которому просматриваются посылки
	private $statement_id = 0;
	
	private $withComment = false;
	
    private $withUnreadComment = false;
	
	function __construct(&$USER)
	{
		$this->USER = &$USER;
	}
    
    function setFromTimestamp($timestamp)
    {
        $this->from_timestamp = (int)$timestamp;
    }

    function setToTimestamp($timestamp)
    {
        $this->to_timestamp = (int)$timestamp;
    }

	function setUserId($user_id)
	{
		if ($user_id) {
			$this->user_id = $user_id;
			$this->group_id = 0;
		}
	}
	
	function setGroupId($group_id)
	{
		if ($group_id) {
			$this->group_id = $group_id;
			$this->user_id = 0;
		}
	}
	
	function setStatusId($status_id)
	{
		if ($status_id != -1) {
			$this->status_id = $status_id;
		}
	}	
	
	function setLangId($lang_id)
	{
		if ($lang_id != -1) {
			$this->lang_id = $lang_id;
		}
	}		


	function setRunIdForShow($run_id)
	{
		if ($run_id != "") {
			$this->run_id = $run_id;
		}
	}		


	
	function setProblemId($problem_id)
	{	
		if ($problem_id) 
		{
			$this->problem_id = $problem_id;
			$this->statement_id = 0;
		}
	}
	
    function setWithComment() {
        $this->withComment = true;
    }
    
    function setWithUnreadComment() {
        $this->withUnreadComment = true;
    }
    
	function setStatementId($statement_id)
	{
		if ($statement_id) {
			$this->statement_id = $statement_id;
			$this->problem_id = 0;
		}
	}
	
	public $base = 10;
	

	function getAJAXTable() {
        global $USER;

        $res='<script type="text/javascript" src="/mod/statements/lib/prism/prism.js"></script>
		      <script src="https://www.google.com/recaptcha/api.js"></script>
              <link type="text/css" href="/mod/statements/lib/prism/prism.css" rel="stylesheet" />';
        $run_master_sid = '';
        if (has_capability('moodle/ejudge_submits:rejudge', context_system::instance())) {
             $run_master_sid = "YES";
        }
       
	$current_page_id = "";
	if (array_key_exists("id", $_GET)) {
		$current_page_id = $_GET["id"];
        }

		$res .=	'
		   <script id="full-protocol-test-tmpl" type="text/x-jquery-tmpl">
            <tr>
                <td id="test_${$item.prot}_${$item.iter}">${$item.iter}</td>
                <td>${string_status}</td>
		<td>${score}</td>
                <td>${$data.time / 1000.0}</td>
                <td>${$data.real_time / 1000.0}</td>
                <td>${max_memory_used}</td>
                <td>${checker_output}</td>
                <td>
                    <div class="bootstrap">
                    <button data-toggle="collapse" data-target="#input${$item.contest_id}_${$item.run_id}_${$item.iter}">Input</button>
                    <button data-toggle="collapse" data-target="#output${$item.contest_id}_${$item.run_id}_${$item.iter}">Output</button>
                    <button data-toggle="collapse" data-target="#corr${$item.contest_id}_${$item.run_id}_${$item.iter}">Correct</button>
                    <button data-toggle="collapse" data-target="#stderr${$item.contest_id}_${$item.run_id}_${$item.iter}">Stderr</button>
                    <button data-toggle="collapse" data-target="#extra${$item.contest_id}_${$item.run_id}_${$item.iter}">Extra information</button>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="7">
                    <div class="bootstrap">
                    <div id="input${$item.contest_id}_${$item.run_id}_${$item.iter}" class="collapse">
                        <div class="well well-sm">
                            <div class="panel-body" style="text-align:left">
                                <pre>${input}</pre>
                                {{if $data["big_input"]}}
                                    <!-- a href="/py/protocol/get_test/${$item.contest_id}/${$item.run_id}/${$item.iter}" target="_blank"> Загрузить тест целиком</a -->
                                    <span>Чтобы увидеть тест целиком, скачайте архив</span>
                                {{/if}}
                            </div>
                        </div>
                    </div>
                    <div id="output${$item.contest_id}_${$item.run_id}_${$item.iter}" class="collapse" style="text-align:left">
                        <div class="well well-sm">
                            <div class="panel-body">
                                <pre>${output}</pre>
                                {{if $data["big_output"]}}
                                    <a href="/py/protocol/get_output/${$item.contest_id}/${$item.run_id}/${$item.iter}" target="_blank"> Загрузить вывод целиком</a>
                                {{/if}}
                            </div>
                        </div>
                    </div>
                    <div id="corr${$item.contest_id}_${$item.run_id}_${$item.iter}" class="collapse" style="text-align:left">
                        <div class="well well-sm">
                            <div class="panel-body">
                                <pre>${corr}</pre>
                                {{if $data["big_corr"]}}
                                    <a href="/py/protocol/get_corr/${$item.contest_id}/${$item.run_id}/${$item.iter}" target="_blank"> Загрузить ответ целиком</a>
                                {{/if}}
                            </div>
                        </div>
                    </div>
                    <div id="stderr${$item.contest_id}_${$item.run_id}_${$item.iter}" class="collapse" style="text-align:left">
                        <div class="well well-sm">
                            <div class="panel-body">
                                <pre>${error_output}</pre>
                            </div>
                        </div>
                    </div>
                    <div id="extra${$item.contest_id}_${$item.run_id}_${$item.iter}" class="collapse" style="text-align:left">
                        <div class="well well-sm">
                            <div class="panel-body">
                                <pre>${extra}</pre>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </script>

        <script id="statistic-tmpl" type="text/x-jquery-tmpl">
            <div class="bootstrap">
                <h5>Статистика</h5>
                <table class="table table-bordered table-condensed" style="margin-bottom: 5px;">
                    <thead>
                        <th>Параметр</th>
                        <th>Значение</th>
                        <th>Тест</th>
                    </thead>
                    <tr>
                        <td>Первый непройденный тест</td>
                        <td style="text-align:center">${$item.stat.first_failed_test_verdict}</td>
                        <td style="text-align:center">
                            {{if $item.stat.first_failed_test === "&mdash;"}}
                                ${$item.stat.first_failed_test}
                            {{else}}
                                <a href="#test_${$item.prot}_${$item.stat.first_failed_test}">${$item.stat.first_failed_test}</a>
                            {{/if}}
                        </td>
                    <tr>
                        <td>Максимальное процессорное время</td>
                        {{if $item.stat.max_cpu_test === "&mdash;"}}
                            <td style="text-align:center">${$item.stat.max_cpu}</td>
                            <td style="text-align:center">${$item.stat.max_cpu_test}</td>
                        {{else}}
                            <td style="text-align:center">${$item.stat.max_cpu / 1000.0}</td>
                            <td style="text-align:center"><a href="#test_${$item.prot}_${$item.stat.max_cpu_test}">${$item.stat.max_cpu_test}</a></td>
                        {{/if}}
                    </tr>
                    <tr>
                        <td>Максимальный расход памяти</td>
                        <td style="text-align:center">${$item.stat.max_memory_used}</td>
                        <td style="text-align:center">
                            {{if $item.stat.max_memory_used_test === "&mdash;"}}
                                ${$item.stat.max_memory_used_test}
                            {{else}}
                                <a href="#test_${$item.prot}_${$item.stat.max_memory_used_test}">${$item.stat.max_memory_used_test}</a>
                            {{/if}}
                        </td>
                    </tr>
                    <tr>
                        <td>Максимальное астрономическое время</td>
                            {{if $item.stat.max_real_time_test === "&mdash;"}}
                                <td style="text-align:center">${$item.stat.max_real_time}</td>
                                <td style="text-align:center">${$item.stat.max_real_time_test}</td>
                            {{else}}
                                <td style="text-align:center">${$item.stat.max_real_time / 1000.0}</td>
                                <td style="text-align:center"><a href="#test_${$item.prot}_${$item.stat.max_cpu_test}">${$item.stat.max_real_time_test}</a></td>
                            {{/if}}
                        </td>
                    </tr>
                </table>
            </div>
        </script>
        <script id="submit-archive-tmpl" type="text/x-jquery-tmpl">
            <h5>Архив для локального запуска</h5> 
            <form action="/py/protocol/get_submit_archive/${$item.problem_id}/${$item.run_id}" method="POST" target="_blank">
                <label class="checkbox" id="downlad_sources">
                    <input type="checkbox" id="downlad_sources_inp" checked="yes" name="sources"> Вложить исходный код.
                </label>
                <label class="checkbox" id="download_all_tests">
                    <input type="checkbox" id="download_all_tests_inp" onchange="toggleDownloadTests()" name="all_tests"> Вложить все тесты.
                </label>
                <input id="download_tests_inp" type="text"placeholder="Номера тестов…" value="{{if $item.stat.first_failed_test !== "&mdash;"}}${$item.stat.first_failed_test}{{/if}}" name="tests">
				<div id="recaptcha_element"></div>
                <span class="help-block">Номера тестов для скачивания через пробел.</span>
                <button class="btn">Загрузить</button>
            </form>
        </script>

        <script id="full-protocol-tmpl" type="text/x-jquery-tmpl">
            {{if $data.message != undefined}}
                <pre>${message}</pre>;
            {{else}}
                <div class="bootstrap">
                    <div class="well well-small">
                        <div class="row">
                            <div class="span5">
                                {{tmpl($data, $item) "#statistic-tmpl"}}
                            </div>
                            <div class="span4">
                                {{tmpl($data, $item) "#submit-archive-tmpl"}}
                            </div>
                        </div>
                    </div>
                </div>
                <table align="center" class="table table-striped" width="100%" height="100%">
                    <tr class="Caption">
                        <td>Тест</td>
                        <td>Статус</td>
			<td>Балл</td>
                        <td>Время работы</td>
                        <td>Астрономическое время работы</td>
                        <td>Используемая память</td>
                        <td>Checker output</td>
                        <td></td>
                    </tr>
                    {{each $data.tests}}
                        {{tmpl($data.tests[$index], {"iter": $index, "contest_id": $item.contest_id, "run_id": $item.run_id, "prot": "full"}) "#full-protocol-test-tmpl"}}
                    {{/each}}
                    <tr>
                        <td colspan=7>
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <pre> ${$data.audit} </pre>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            {{/if}}                
        </script>

        <script id="protocol-tmpl" type="text/x-jquery-tmpl">
            {{if $data.message != undefined}}
                <pre>${message}</pre>;
            {{else}}
                <div class="bootstrap">
                    <div class="well well-small">
                        <div class="row">
                            <div class="span9">
                                {{tmpl($data, $item) "#statistic-tmpl"}}
                            </div>
                        </div>
                    </div>
                </div>

                <table align="center" class="table table-striped" width="100%" height="100%">
                    <tr class="Caption">
                        <td>Тест</td>
                        <td>Статус</td>
			<td>Балл</td>
                        <td>Время работы</td>
                        <td>Астрономическое время работы</td>
                        <td>Используемая память</td>
                    </tr>
                    {{each $data.tests}}
                       <tr>
                            <td id="test_${$item.prot}_${$index}">${$index}</td>
                            <td>${$value.string_status}</td>
			    <td>${$value.score}</td>
                            <td>${$value.time / 1000.0}</td>
                            <td>${$value.real_time / 1000.0}</td>
                            <td>${$value.max_memory_used}</td>
			    {{if $value.game_id != undefined}}
			    <td><a href="https://informatics.msk.ru/starhungergames/${$value.game_id}.textpb">TextLog</a>
			    <a href="https://informatics.msk.ru/starhungergames/${$value.game_id}.binarypb">BinLog</a></td>
                            {{/if}}
                       </tr>
                    {{/each}}
                </table>
            {{/if}}
        </script>

        <div align="center" width="100%">		
			<div id="Pagination" class="pagination" align="center" width="100%">
			</div>
		</div>
		<br style="clear:both;" />
		<div id="problem_id" style="display: none">'.$this->problem_id.'</div>
		<div id="group_id" style="display: none">'.$this->group_id.'</div>
        <div id="user_id" style="display: none">'.$this->user_id.'</div>
        <div id="current_user_id" style="display: none">'.$USER->id.'</div>
		<div id="from_timestamp" style="display: none">'.$this->from_timestamp.'</div>
		<div id="to_timestamp" style="display: none">'.$this->to_timestamp.'</div>
		<div id="withComment" style="display: none">'.$this->withComment.'</div>
		<div id="withUnreadComment" style="display: none">'.$this->withUnreadComment.'</div>
		<div id="language_id" style="display: none">'.$this->lang_id.'</div>
		<div id="status_id" style="display: none">'.$this->status_id.'</div>
		<div id="run_id" style="display: none">'.$this->run_id.'</div>
		<div id="statement_id" style="display: none">'.$this->statement_id.'</div>
        <div id="count" style="display: none">'.$this->base.'</div>
        <div id="opened_window" style="display: none"></div>	
        <div id="run_master_sid" style="display: none">'.$run_master_sid.'</div>
        <div id="current_page_id" style="display: none">'. $current_page_id .'</div>
		<div id="Searchresult">
			<div class="spinner-border text-primary" role="status">
	    		<span class="sr-only">Loading...</span>
	 		</div>	 
		</div>		
		<div id="SearchresultLoadingTpl" style="display: none">
			<div class="spinner-border text-primary" role="status">
	    		<span class="sr-only">Loading...</span>
	 		</div>	 
		</div>';

        
		return $res;
	
	}	
}

?>


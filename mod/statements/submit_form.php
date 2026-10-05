<?php
    function get_submit_form($chapter)
    {
        global $_SERVER;

    	require("langs.php");
	    $l_array = $lang_array;
	    if ($chapter->id == 112583) {
	    	$l_array = $lang_ant_1;
	    }

	    if ($chapter->id == 112584) {
	        $l_array = $lang_ant_2;
	    }
	

    $res = '
                        <div style="display:none; float: left">
                            <input id="resetFocus" name="resetFocus"/>
                        </div>
						<div class="btn-group" role="group" aria-label="Button group with nested dropdown">
						<div id="submit" class="d-inline p-2 bg-secondary text-black" >'.get_string('submit_linktext','statements').':</div>
							<div class="btn-group" role="group">
							<button id="upload_button" class="btn btn-primary">Выбор файла</button>
								<button class="btn btn-light dropdown-toggle" type="button" data-toggle="dropdown" id="lang_id" value="3">
									FreePascal
								</button>
								<div class="dropdown-menu statement-api-languages" aria-labelledby="lang_id" data-problem-id="'.intval($chapter->id).'">';
					// The language options are loaded from the API (js/statement_api.js).
					$res .= '</div>
						</div>
						<button id="submit_button" class="btn btn-primary">Отправить <span class="badge badge-light" id="filename"></span></button>
						</div>
						<div class="btn-group" role="group" aria-label="Button group with nested dropdown">
						<nav aria-label="Submits pagination" id="Pagination">
						</nav>
						</div>
						<button id="ArchiveButton" class="btn btn-secondary">Архив посылок</button>
					'; 
        return $res;
    }


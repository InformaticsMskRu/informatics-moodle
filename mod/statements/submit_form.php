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
        <script  type="text/javascript" src="https://www.google.com/recaptcha/api.js"></script> 
        <!--<script type="text/javascript" src="/legacy/ajax/js/uploader/ajaxupload.js"></script>       -->
        <!--<link type="text/css" href="/moodle/ajax/js/jquery-window-5.01b/css/jquery.window.css" rel="stylesheet" />-->
        <!--<script type="text/javascript" src="/moodle/ajax/js/jquery-window-5.01b/jquery.window.min.js"></script>-->
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
								<div class="dropdown-menu" aria-labelledby="lang_id">';
                                if (!$chapter->output_only) {
					require("langs.php");
					$l_array = $lang_array;
					if ($chapter->id == 112583) {
					    $l_array = $lang_ant_1;
					}

					if ($chapter->id == 112584) {
					    $l_array = $lang_ant_2;
					}


					foreach ($l_array as $id => $val) {
						if ($val["priv"] && has_capability('moodle/ejudge_submits:rejudge', context_system::instance())) {
						$res.= '<a class="dropdown-item lang_choose_option" id="lang_choose_option" value="'.$id.'" href="#">'.$val["name"]."</a>";
					    } else if ($val["unpriv"]) {
						$res.= '<a class="dropdown-item lang_choose_option" id="lang_choose_option" value="'.$id.'" href="#">'.$val["name"]."</a>";					
					}}

                                } else {
						$res.= '<a class="dropdown-item lang_choose_option" id="lang_choose_option" value="0" href="#">Текстовый файл</a>';					
                        }
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


<?php
$set_lang = false;
$lang = get_locale();
$file_name = SL_PLUGIN_DIR.'/languages/salon-page-'.$lang.'.php';
if ( file_exists($file_name) ) {
	require_once($file_name);
	$set_lang = true;
}
if (! $set_lang ) {
	$file_name = SL_PLUGIN_DIR.'/languages/salon-page-com.php';
	if ( file_exists($file_name) ) require_once($file_name);
	else {
		throw new Exception(Salon_Component::getMsg('E007',basename(__FILE__).':'.__LINE__ ) );
	}
}


class Salon_Page {
	const INPUT_BOTTOM_MARGIN = 20;
	const SHORT_WIDTH = '50px';
	const MIDDLE_WIDTH = '100px';
	const LONG_WIDTH = '150px';

	const TARGET_DATE_PATERN = 'day';
	const FILTER_PRIORITY = 999;

	private $version = '1.0';
	protected $is_multi_branch = false;
	protected $is_salon_admin = false;
	protected $nonce = '';
	protected $config_datas = null;

	protected $is_no_change_booking_items = true;
	protected $user_branch_cd = '';


	public function __construct($is_multi_branch,$use_session ) {
		$this->is_multi_branch = $is_multi_branch;
		$nonce = SL_PLUGIN_DIR;
		if ($use_session) $nonce = session_id();
		$this->nonce = wp_create_nonce($nonce);

	}
	public function set_isSalonAdmin($is_salon_admin){
		$this->is_salon_admin = $is_salon_admin;
	}

	public function isSalonAdmin() {
		return $this->is_salon_admin;
	}

	public function set_user_branch_cd($branch_cd) {
		$this->user_branch_cd = $branch_cd;
	}

	public function get_default_brandh_cd() {
// 		return Salon_Default::BRANCH_CD;
		return $this->user_branch_cd;
	}

	public function set_config_datas($config_datas) {
		$config_datas['SALON_CONFIG_FRONT_ITEMS'] = $this->setFrontItems($config_datas['SALON_CONFIG_FRONT_ITEMS']);

		$this->config_datas = $config_datas;
	}


	static function getResponseType() {
		if (empty($_POST['func']) )	return Response_Type::JASON_406_RETURN;
		else return Response_Type::JASON;
	}

	static function echoInitData($datas) {
		$data_cnt = count($datas);
		//indexが歯抜けの可能性があるので降りなおす
		$i = 0;

		if ($datas ) {
			foreach ($datas as $k1 => $d1) {
				$i++;
				$datas[$k1]['no'] = sprintf("%03d",$i);
				$datas[$k1]['check'] = 0;	//[TODO]いらない？
			}
		}
		$jdata = array();
		$jdata['iTotalRecords'] = $data_cnt;
		$jdata['iTotalDisplayRecords'] = $data_cnt;
		$jdata['sEcho'] = 1;
		if (is_null($datas) )$datas = array();
		$jdata['aaData'] = $datas;
		echo json_encode($jdata);
	}

	static function echoClientItem($items) {
		$item_contents = Salon_Page::setItemContents();
		echo 'var check_items = { ';
		$tmp = array();
		if (is_array($items) ){
			foreach ($items as $d1) {
				$placeholder = "";
				if (!empty($item_contents[$d1]['place'])) {
					$placeholder = ',"placeholder" : "'.$item_contents[$d1]['place'].'"';
				}
				$add_class = '';
				$tmp[] ='"'.$item_contents[$d1]['id'].'": '.
						'{'.
						' "id" : "'.$item_contents[$d1]['id'].'"'.
						',"class" : "'.implode(" ",$item_contents[$d1]['check'])." ".implode(" ",$item_contents[$d1]['class']).'"'.
						',"label" : "'.$item_contents[$d1]['label'].'"'.
						',"tips" : "'.$item_contents[$d1]['tips'].'"'.
						$placeholder.
						'}';
			}
		}
		echo join(',',$tmp);
		echo '};';
		self::echoHtmlpecialchars();

	}

	static function echoBlueLogic() {
		$msg = __('please enter',SL_DOMAIN);
		$default_margin = self::INPUT_BOTTOM_MARGIN;
		echo <<<EOT
		function _fnCheckOfRequire() {
			var val = \$j(this).val();
			var label = \$j(this).prev().children(".small");
			label.removeClass("sl_coler_not_complete");
			label.removeAttr("style");
			if(val == "" || val === null){
				setTimeout(function(){
				label.text("{$msg}");
				label.addClass("error small");
				var label_tag = \$j(this).prev();
				var diff = label_tag.outerHeight(true) - \$j(this).outerHeight(true);
				if (diff > 0 ) {
					diff += {$default_margin}+5;
					\$j(this).attr("style","margin-bottom: "+diff+"px;");
					label.attr("style","text-align:left;");
				}
				},300);
			}
			else {
				var id = \$j(this).attr("id");
				label.text(check_items[id]["tips"]);
				label.removeClass("error");
				var label_tag = \$j(this).prev();
				var diff = label_tag.outerHeight(true) - \$j(this).outerHeight(true);
				if (diff > 0 ) {
					diff += {$default_margin}+5;
					\$j(this).attr("style","margin-bottom: "+diff+"px;");
					label.attr("style","text-align:left;");
				}
			}
		}
EOT;
	}

	static function echoBlueLogicMobile() {
		$msg = __('This field can\'t be empty',SL_DOMAIN);
		echo <<<EOT
		function _fnCheckOfRequire() {
		var val = \$j(this).val();
		\$j(this).css("background","#FFFFFF");
		if(val == "" || val === null){
			\$j(this).val("{$msg}");
			\$j(this).css("background","#FFCCFF");
		}
		}
		function _fnFocusOfRequire() {
		var val = \$j(this).val();
		\$j(this).css("background","#FFFFFF");
		if(val == "{$msg}"){
			\$j(this).val("");
		}
		}
EOT;
	}

	static function echoClientBlur($items,$isMobile=false) {
		echo '$j(function(){';
		$isExist = false;
		$item_contents = Salon_Page::setItemContents();
		if (is_array($items) ){
			foreach ($items as $d1) {
				if (in_array("chk_required",$item_contents[$d1]['check']))	{
					$isExist = true;
					echo '$j("#'.$item_contents[$d1]['id'].'").blur(_fnCheckOfRequire);';
					if ($isMobile) {
						echo '$j("#'.$item_contents[$d1]['id'].'").focus(_fnFocusOfRequire);';
					}
				}
			}
		}
		if ($isExist) {
			if ($isMobile) {
				self::echoBlueLogicMobile();
			}
			else {
				self::echoBlueLogic();
			}
		}
		echo '});';

	}

	static function echoClientItemMobile($items) {
		$item_contents = Salon_Page::setItemContents();
		echo 'var check_items = { ';
		$tmp = array();
		if (is_array($items) ){
			foreach ($items as $d1) {
				$placeholder = "";
				if (!empty($item_contents[$d1]['place'])) {
					$placeholder = ',"placeholder" : "'.$item_contents[$d1]['place'].'"';
				}
				$add_class = '';
				$tmp[] ='"'.$item_contents[$d1]['id'].'": '.
						'{'.
						' "id" : "'.$item_contents[$d1]['id'].'"'.
						',"label" : "'.$item_contents[$d1]['label'].'"'.
						$placeholder.
						'}';
			}
		}
		echo join(',',$tmp);
		echo '};';
		self::echoHtmlpecialchars();

	}


	static function echoHtmlpecialchars() {
		echo <<<EOT
			function htmlspecialchars_decode (data) {
				if (data ) {
					data = data.toString().replace(/&lt;/g, "<").replace(/&gt;/g, ">");
					data = data.replace(/&#0*39;/g, "'");
					data = data.replace(/&quot;/g, '"');
					data = data.replace(/&amp;/g, '&');
				}
				return data;
			}
			function htmlspecialchars (data) {
				if (data) {
					data = data.toString();
					data = data.replace(/&/g, "&amp;");
					data = data.replace(/</g, "&lt;").replace(/>/g, "&gt;");
					data = data.replace(/'/g, "&#039;");
					data = data.replace(/\"/g, "&quot;");
				}
				return data;
			}
EOT;
	}

	static  function echoSetItemLabel($is_Tables = true) {
		echo <<<EOT
			for(index in check_items) {
				if (check_items[index] ) {
					var id = check_items[index]["id"];
					if (check_items[index]["label"] == "") {
						\$j("#"+id).addClass(check_items[index]["class"]);
					}
					else {
						var ast = "";
						if (check_items[index]["class"].indexOf("chk_required") != -1) {
							ast = "<span class=\"sl_req\">*</span>";
						}
						\$j("#"+id).addClass(check_items[index]["class"]);
						\$j("#"+id).before("<label id=\""+id+"_lbl\" for=\""+id+"\" >"+check_items[index]["label"]+ast+":<span class=\"small\"></span></label>");
					}
					if (check_items[index]["placeholder"] ) {
						\$j("#"+id).attr("placeholder",check_items[index]["placeholder"]);
					}
				}
			}
EOT;
		if ($is_Tables ) {
			echo <<<EOT2
			\$j(window).bind('resize', function () {
					target.fnAdjustColumnSizing(true);
			} );

EOT2;
		}
	}

	static  function echoSetItemLabelMobile() {
		echo <<<EOT
			for(index in check_items) {
				if (check_items[index] ) {
					var id = check_items[index]["id"];

					if (check_items[index]["placeholder"] ) {
						\$j("#"+id).attr("placeholder",check_items[index]["placeholder"]);
					}
					else {
						\$j("#"+id).attr("placeholder",check_items[index]["label"]);
					}
					\$j("#"+id).parent().before("<li class=\"slm_label\"><label id=\""+id+"_lbl\" for=\""+id+"\" >"+check_items[index]["label"]+":</label></li>");
				}
			}
EOT;
	}

	static function echoCommonButton($add_operation = '"inserted"'){
		$show = __('Show Details',SL_DOMAIN);
		$hide = __('Hide Details',SL_DOMAIN);
		echo <<<EOT
			\$j("#salon_button_div input").addClass("sl_button");
			fnDetailInit();
			\$j("#button_insert").click(function(){
				if (\$j("#data_detail").is(":hidden")) {
					\$j("#data_detail").show();
					return;
				}
				fnClickAddRow({$add_operation});
			});
			\$j("#button_update").click(function(){
				fnClickAddRow("updated");
			});
			\$j("#button_clear").click(function(){
				fnDetailInit(true);
				\$j(target.fnSettings().aoData).each(function (){
					\$j(this.nTr).removeClass("row_selected");
				});
			});
			\$j("#button_detail").click(function(){
				\$j("#data_detail").toggle();
				if (\$j("#data_detail").is(":visible") ) \$j("#button_detail").val("{$hide}")
				else \$j("#button_detail").val("{$show}");
			});

			fnDetailInit();
			\$j("#data_detail").hide();
			\$j("#button_detail").val("{$show}");

EOT;
	}


	static function echoDataTableLang($iDisplayLength = 100,$only_lang_display = false,$sEmptyTable = "",$sInfoEmpty = "") {
		$sLengthMenu = __('Display _MENU_ records per page',SL_DOMAIN);
		$sNext = __('Next Page',SL_DOMAIN);
		$sPrevious = __('Prev Page',SL_DOMAIN);
		$sInfo = __('Showing _START_ to _END_ of _TOTAL_ records',SL_DOMAIN);
		$sSearch = __('search',SL_DOMAIN);
		if (empty($sEmptyTable)) $sEmptyTable = __('No data available in table',SL_DOMAIN);
		$sLoadingRecords = __('Loading...' ,SL_DOMAIN);
		if (empty($sInfoEmpty)) $sInfoEmpty = __('Showing 0 to 0 of 0 entries' ,SL_DOMAIN);
		$sZeroRecords = __('No matching records found' ,SL_DOMAIN);

		echo <<<EOT
			"bAutoWidth": false,
			"bProcessing": true,
			"sScrollX": "100%",
			"bScrollCollapse": true,
			"bLengthChange": false,
			"bPaginate": true,
			//"bServerSide": true,
			iDisplayLength : {$iDisplayLength},
			"oLanguage": {
			        "sLengthMenu": "{$sLengthMenu}"
			        ,"oPaginate": {
			            "sNext": "{$sNext}"
			            ,"sPrevious": "{$sPrevious}"
				    }
		        	,"sInfo": "{$sInfo}"
			        ,"sSearch": "{$sSearch}："
					,"sEmptyTable":"{$sEmptyTable}"
					,"sLoadingRecords":"{$sLoadingRecords}"
					,"sProcessing":"{$sLoadingRecords}"
					,"sInfoEmpty":"{$sInfoEmpty}"
					,"sZeroRecords":"{$sZeroRecords}"
			},
EOT;
		if ($only_lang_display) return;
		echo <<<EOT2
			fnServerData: function(sSource, aoData, fnCallback, oSettings) {
				\$j.ajax({
					url: sSource,
					type: "POST",
					data: aoData,
					dataType: "json",
					success: function(data) {
						if (data === null || data.status == "Error" ) {
							if (data) alert(data.message);
							fnCallback({"iTotalRecords":0,"iTotalDisplayRecords":0,"sEcho":1,"aaData":[]});
						}
						else {
							fnCallback(data);
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
				})
			},

EOT2;
	}


	static function echoTableItem($items,$is_only_common_part = false,$is_multi_branch = true,$operate_width = '120px',$isForceNoSort = false) {
		$operate_title = __('Operation',SL_DOMAIN);
		echo <<<EOT
			"aoColumns": [
				{ "mData":"no","sTitle": "No" ,"sClass":"sl_select","bSearchable": false,"bSortable": false,"sWidth":"20px"},
				{ "mData":"check","sTitle": "{$operate_title}","bSortable": false,"bSearchable": false,"sWidth":"{$operate_width}"},
EOT;

		if ($is_only_common_part ) return;
		$item_contents = Salon_Page::setItemContents();


		$tmp = array();
		foreach ($items as $d1) {
//			if ($isForceNoSort ) $sort = 'false';
//			else 		empty ($item_contents[$d1]['table']['sort'])  ? $sort = 'false' : $sort = $item_contents[$d1]['table']['sort'];
//			if ($isForceNoSort ) $search = 'false';
//			else 		empty ($item_contents[$d1]['table']['search']) ? $search = 'false' : $search = $item_contents[$d1]['table']['search'];
			empty ($item_contents[$d1]['table']['sort'])  ? $sort = 'false' : $sort = $item_contents[$d1]['table']['sort'];
			empty ($item_contents[$d1]['table']['search']) ? $search = 'false' : $search = $item_contents[$d1]['table']['search'];
			empty ($item_contents[$d1]['table']['visible']) ? $visible = 'false' : $visible = $item_contents[$d1]['table']['visible'];
			$width = '';
			if (!empty ($item_contents[$d1]['table']['width']) ) $width = ',"sWidth" : "'.$item_contents[$d1]['table']['width'].'"';
			if ($is_multi_branch == false && $d1 == 'branch_cd' ) {
				$visible = 'false';
			}

			$tmp[] =
					'{'.
					' "mData" : "'.$item_contents[$d1]['id'].'"'.
					',"sTitle" : "'.$item_contents[$d1]['label'].'"'.
					',"sClass" : "'.$item_contents[$d1]['table']['class'].'"'.
					$width.
					',"bSortable" : '.$sort.
					',"bSearchable" : '.$search.
					',"bVisible" : '.$visible.
					'}';
		}
		echo join(',',$tmp);
		echo '],';

	}


	public function echoEditableCommon($target_name,$add_col = "",$add_check_process = "") {
		$target_src = get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php?action=sl'.$target_name;
		$submit = __('change',SL_DOMAIN);
		$cancel = __('cancel',SL_DOMAIN);
		$placeholder = __('click edit',SL_DOMAIN);

		$menu_func = ucwords($target_name);
		$add_char1 = '';
		if ( ! empty($add_col) ) {
			if (is_array($add_col) ) {
				foreach ($add_col  as $d1 ) {
					$add_char1 .= ',"'.$d1.'":setData["aoData"][position[0]]["_aData"]["'.$d1.'"] ';
				}
			}
			else {
				$add_char1 = ',"'.$add_col.'":setData["aoData"][position[0]]["_aData"]["'.$add_col.'"] ';
			}
		}
		//positionは行位置、列位置(表示のみ）(=tdの数)、列位置（全体=aoColumnの数）
		echo <<<EOT
				\$j("#lists tbody .sl_editable").editable("{$target_src}", {

					submitdata: function ( value, settings ) {
						var setData = target. fnSettings();
						var position = target.fnGetPosition( this );
						return {
							"{$target_name}_cd": setData['aoData'][position[0]]['_aData']['{$target_name}_cd']
							,"column": position[2]
							,"nonce":"{$this->nonce}"
							,"menu_func":"{$menu_func}_Col_Edit"
							$add_char1
						};
					},
					callback: function( sValue, y ) {
						var jdata = \$j.evalJSON( sValue );
						var position = target.fnGetPosition( this );
						if (jdata.status ==  "Ok" ) {
							target.fnUpdate(jdata.set_data,position[0],position[2],false);
						}
						alert(jdata.message);
						fnDetailInit();
					},

					onsubmit:function(settings,td) {
						{$add_check_process	}
						if ( !checkColumnItem( td ) )return false;
					},

					onerror: function (settings, original, xhr) {
						var jdata = \$j.evalJSON( xhr.responseText );
						alert(jdata.message);
					},
					onreset: function (settings, original) {
						original.revert = htmlspecialchars(original.revert);
					},
					type : "text",
					submit : "{$submit}",
					cancel : "{$cancel}",
					placeholder : "{$placeholder}",
					"height": "20px"
				} );
				\$j("#lists  tbody .sl_select").click(function(event) {
					fnSelectRow(this);
				});
EOT;
	}

	static function echoDataTableSelecter($target_name,$is_append = true,$del_disp='',$del_msg='') {

		if (empty($del_disp) ) $del_disp = __('Delete ',SL_DOMAIN);
		if (empty($del_msg) ) $del_msg = __('Delete ok?',SL_DOMAIN);
		$sel_disp = __('Select',SL_DOMAIN);
		//セレクターは１列目
		echo <<<EOT
			var element = \$j("td:eq(1)", nRow);
			element.text("");
	//		var checkbox = \$j("<input>")
	//				.attr("type", "checkbox")
	//				.attr("id", "check_" + iDataIndex)
	//				.attr("value",1)
	//				.attr("checked", aData.check == 1 ? true : false)
	//				.change(function() {
	//	            	aData.check = \$j(this).val();
	//				});

			var sel_box = \$j("<input>")
					.attr("type","button")
					.attr("id","sl_select_btn_"+iDataIndex)
					.attr("name","sl_update_"+iDataIndex)
					.attr("value","{$sel_disp}")
					.attr("class","sl_button sl_button_short")
					.click(function(event) {
						fnSelectRow(this.parentNode);
					});
			var del_box = \$j("<input>")
					.attr("type","button")
					.attr("id","sl_delete_btn_"+iDataIndex)
					.attr("name","sl_delete_"+iDataIndex)
					.attr("class","sl_button sl_button_short")
					.attr("value","{$del_disp}")
					.click(function(event) {
						if (confirm(htmlspecialchars_decode(aData.{$target_name})+"{$del_msg}") ) {
							fnClickDeleteRow(this.parentNode);
						}
					});
EOT;
		if ($is_append) {
			echo <<<EOT2
	//		element.append(checkbox);
			element.append(sel_box);
			element.append(del_box);
EOT2;
		}

	}

	static function echoDataTableBranchData($target_column,$branch_datas) {
		$option_no_regist = __('not registered',SL_DOMAIN);
		echo <<<EOT
			var element_branch = \$j("td:eq({$target_column})", nRow);
			element_branch.text("");
			var selecter = \$j("<select>")
				.append(\$j("<option>").html("{$option_no_regist}").val(""))
EOT;
			if (is_array($branch_datas) ) {
				foreach($branch_datas as $k1 => $d1 ) {
					echo '.append($j("<option>").html("'.htmlspecialchars($d1['name'],ENT_QUOTES).'").val("'.$d1['branch_cd'].'"))';
				}
			}
		echo <<<EOT2
			.val(aData.branch_cd)
			.change(function(event) {
				fnUpdateColumn(this.parentNode,"branch_cd",\$j(this).val() );
			});
			element_branch.append(selecter);
EOT2;

	}

	static function echoDataTablePositionData($target_column,$position_datas) {
		$option_no_regist = __('not registered',SL_DOMAIN);
		$staff_cd_no_change = get_option('salon_initial_user',1);
		$set_position_name = __('MAINTENANCE',SL_DOMAIN);

		echo <<<EOT
			var element_position = \$j("td:eq({$target_column})", nRow);
			if ( aData.staff_cd == {$staff_cd_no_change} ){
				element_position.text("{$set_position_name}");
			}
			else {
				element_position.text("");
				var selecter = \$j("<select>")
					.append(\$j("<option>").html("{$option_no_regist}").val(""))
EOT;
				if (is_array($position_datas) ) {
					foreach($position_datas as $k1 => $d1 ) {
						echo '.append($j("<option>").html("'.htmlspecialchars($d1['name'],ENT_QUOTES).'").val("'.$d1['position_cd'].'"))';
					}
				}
		echo <<<EOT2
				.val(aData.position_cd)
				.change(function(event) {
					fnUpdateColumn(this.parentNode,"position_cd",\$j(this).val() );
				});
				element_position.append(selecter);
			}
EOT2;

	}
	static function echoDataTableOnlyPositionData($target_column,$position_datas) {
		$option_no_regist = __('not registered',SL_DOMAIN);
		$staff_cd_no_change = get_option('salon_initial_user',1);
		$set_position_name = __('MAINTENANCE',SL_DOMAIN);

		echo <<<EOT
			var element_position = \$j("td:eq({$target_column})", nRow);
			if ( aData.staff_cd == {$staff_cd_no_change} ){
				element_position.text("{$set_position_name}");
			}
			else {
				element_position.text("");
				var selecter = \$j("<select>")
					.append(\$j("<option>").html("{$option_no_regist}").val(""))
EOT;
		if (is_array($position_datas) ) {
			foreach($position_datas as $k1 => $d1 ) {
				echo '.append($j("<option>").html("'.htmlspecialchars($d1['name'],ENT_QUOTES).'").val("'.$d1['position_cd'].'"))';
			}
		}
		echo <<<EOT2
				.val(aData.position_cd)
				.change(function(event) {
					fnUpdateColumn(this.parentNode,"position_cd",\$j(this).val() );
				});
				element_position.append(selecter);
			}
EOT2;

	}

	public function echoDataTableEditColumn($target_name,$add_col = "",$add_callback_process="",$add_check_process = "") {

		$target_src = get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php?action=sl'.$target_name;
		$add_char1 = '';
		if ( ! empty($add_col) ) {	//[TODO]salesとresevationのIDで使用しているが、IDはやめられないか
			$add_char1 = ',"'.$add_col.'":setData["aoData"][position[0]]["_aData"]["'.$add_col.'"] ';
		}
		$menu_func = ucwords($target_name);

		$check_error_msg = __('select please',SL_DOMAIN);

		//セレクトボックスであれば何らかの値がはいるはずなので
		//クライアント側では空データのチェックのみをいれとく
		//不正データはサーバ側でチェックする。
		echo <<<EOT
			function fnUpdateColumn(target_col,column_name,set_value) {
				if (set_value == "" ) {
					alert("{$check_error_msg}");
					return false;
				}
				var position = target.fnGetPosition( target_col );
				var setData = target.fnSettings();
				var target_cd = setData['aoData'][position[0]]['_aData']['{$target_name}_cd'];
				{$add_check_process	}
				\$j.ajax({
						type: "post",
						url:  "{$target_src}",
						dataType : "json",
						data: 	{
							"{$target_name}_cd":target_cd
							,"column":position[2]
							,"value":set_value
							,"func":"update"
							,"nonce":"{$this->nonce}"
							,"menu_func":"{$menu_func}_Col_Edit"
							{$add_char1}
						},
						success: function(data) {
							if (data === null || data.status == "Error" ) {
								if (data) alert(data.message);
							}
							else {
								alert(data.message);
								setData['aoData'][position[0]]['_aData'][column_name] = set_value;
								{$add_callback_process}
								fnDetailInit();
							}
						},
						onerror:  function(XMLHttpRequest, textStatus){
							alert (textStatus);
						}
				 });
			}
EOT;

	}

	public function echoDataTableDeleteRow($target_name,$target_key_name = '',$is_delete_row = true,$add_parm = '',$add_check = '') {
		$target_src = get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php?action=sl'.$target_name;
		if (empty($target_key_name) ) $target_key_name = $target_name;
		$menu_func = ucwords($target_name);
		if ($is_delete_row) $delete_string = 'var rest = target.fnDeleteRow( position[0] );	fnDetailInit();';
		else $delete_string  = 'target.fnUpdate( data.set_data ,position[0] );	fnDetailInit();';

		echo <<<EOT
			function fnClickDeleteRow(target_col) {
				var position = target.fnGetPosition( target_col );
				var setData = target.fnSettings();
				var target_cd = setData['aoData'][position[0]]['_aData']['{$target_key_name}_cd'];
				{$add_check}
				 \$j.ajax({
						type: "post",
						url:  "{$target_src}",
						dataType : "json",
						data: 	{
							$add_parm
							"{$target_key_name}_cd":target_cd,
							"type":"deleted",
							"nonce":"{$this->nonce}",
							"menu_func":"{$menu_func}_Edit"
						},
						success: function(data) {
							if (data === null || data.status == "Error" ) {
								if (data) alert(data.message);
							}
							else {
								{$delete_string}
							}
						},
						error:  function(XMLHttpRequest, textStatus){
							alert (textStatus);
						}
				 });
			}
EOT;

	}

	static function echoClosedCheck($closed_day,$tag_id){
		//closed_day は０→日カンマ区切り
		echo '<div id="'.$tag_id.'_check" class="sl_checkbox" >	';
		$closed = array(false,false,false,false,false,false,false);
		$week = array(__('sun',SL_DOMAIN),__('mon',SL_DOMAIN), __('tue',SL_DOMAIN), __('wed',SL_DOMAIN), __('thr',SL_DOMAIN), __('fri',SL_DOMAIN), __('sat',SL_DOMAIN));
		$datas = explode(',',$closed_day);
		foreach ($datas as $d1 ) {
			$closed[$d1] = true;
		}
		for ( $i = 0 ; $i < 7 ; $i++ ) {
			echo '<input type="checkbox" id="'.$tag_id.'_'.$i.'" value="'.$i.'" '.($closed[$i] ? 'checked="checked"' :'').'/><label for="'.$tag_id.'_'.$i.'">&nbsp;'.$week[$i].'</label>';
		}
		echo '</div>';
		echo '<div id="sl_holiday_wrap" >';
		$week_long = explode(',',__('"Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"',SL_DOMAIN));
		for ( $i = 0 ; $i < 7 ; $i++ ) {
			echo '<div id="sl_holiday_detail_wrap_'.$i.'" class="sl_holiday_detail_wrap" >';
			echo '<label>'.__("Holiday detail",SL_DOMAIN).$week_long[$i].'</label><input type="text" id="'.$tag_id.'_'.$i.'_fr" class="sl_from sl_nocheck"/><label class="sl_holiday_in_label">-</label><input type="text" id="'.$tag_id.'_'.$i.'_to" class="sl_to sl_nocheck" /><label class="sl_holiday_in_label">'.__('is on Holiday',SL_DOMAIN).'</label>';
			echo '</div>';
		}
		echo '</div>';

	}


	static function echoClosedDetail($closed_day,$tag_id) {
		echo <<<EOT
			\$j("#closed_day_check input[type=checkbox]").click(function(){
				\$j(".sl_holiday_detail_wrap").hide();
				var tmp = new Array();
				var tmp_detail = new Array();
				var tmp_closed_array = save_closed.split(",")
				var tmp_closed_detail_array = save_closed_detail.split(";");
				\$j("#closed_day_check input[type=checkbox]").each(function (){
					if ( \$j(this).is(":checked") ) {
						var idx  = \$j(this).val();
						tmp.push( idx );
						tmp_detail.push(\$j("#{$tag_id}_"+idx+"_fr").val()+","+\$j("#{$tag_id}_"+idx+"_to").val());
						\$j("#sl_holiday_detail_wrap_"+idx).show();
					}
				});
				for( var i = 0; i < tmp.length ; i++ ) {
					var idx = tmp[i];
					var set_index =  tmp_closed_array.indexOf(idx);
					if (set_index == -1 ) {
						\$j("#{$tag_id}_"+idx+"_fr").val(\$j("#open_time").val());
						\$j("#{$tag_id}_"+idx+"_to").val(\$j("#close_time").val());
					}
					else {
						var each_array = tmp_closed_detail_array[set_index].split(",");
						if (!each_array[0] ) each_array[0] = \$j("#open_time").val();
						if (!each_array[1] ) each_array[1] = \$j("#close_time").val();
						\$j("#{$tag_id}_"+idx+"_fr").val(each_array[0]);
						\$j("#{$tag_id}_"+idx+"_to").val(each_array[1]);
					}
				}

				save_closed = tmp.join(",");
				save_closed_detail = tmp_detail.join(";");
			});
			\$j("#open_time").change(function() {
				for ( var i = 0 ; i < 7 ; i++ ) {
					\$j("#{$tag_id}_"+i+"_fr").val(\$j(this).val());
				}
				\$j("#sl_sp_date_detail_fr").val(\$j(this).val());

			});
			\$j("#close_time").change(function() {
				for ( var i = 0 ; i < 7 ; i++ ) {
					\$j("#{$tag_id}_"+i+"_to").val(\$j(this).val());
				}
				\$j("#sl_sp_date_detail_to").val(\$j(this).val());
			});
			\$j(".sl_from,.sl_to").change(function() {
				var tmp = new Array();
				\$j("#closed_day_check input[type=checkbox]").each(function (){
					if ( \$j(this).is(":checked") ) {
						var id=\$j(this).val();
						tmp.push(\$j("#{$tag_id}_"+id+"_fr").val()+","+\$j("#{$tag_id}_"+id+"_to").val());
					}

				});
				save_closed_detail = tmp.join(";");
			});

			for ( var i = 0 ; i < 7 ; i++ ) {
				\$j("#sl_holiday_detail_wrap_"+i).hide();
			}


EOT;
		$datas = explode(',',$closed_day);
		foreach ($datas as $d1 ) {
			echo '$j("#sl_holiday_detail_wrap_'.$d1.'").show();';
		}

	}

	static function echo_clear_error() {
	//[TODO]IEだとくずれてしまうのでmargin1加算
		$default_margin = self::INPUT_BOTTOM_MARGIN;
		echo <<<EOT
				var userAgent = window.navigator.userAgent.toLowerCase();
				var appVersion = window.navigator.appVersion.toLowerCase();

				\$j("span").removeClass("error");
				for(index in check_items) {
					var id = check_items[index]["id"];
					\$j("#"+id+"_lbl").children(".small").text(check_items[index]["tips"]);
					var diff = \$j("#"+id+"_lbl").outerHeight(true) - \$j("#"+id).outerHeight(true);
					if (diff > 0 ) {
						diff += {$default_margin}+5;
						\$j("#"+id).attr("style"," margin-bottom: "+diff+"px;");
						\$j("#"+id+"_lbl").children(".samll").attr("style","text-align:left;");
					}


					if (userAgent.indexOf('msie') != -1) {
					//ie9以下は無視
						var lineHeight = parseFloat(\$j("#"+id+"_lbl .small").css("line-height"))*parseFloat(\$j("body").css("font-size"));
						var bHeight = Math.round(lineHeight);
					}else{//ie以外
					    var lineHeight = parseFloat(\$j("#"+id+"_lbl .small").css("line-height"));
					    var bHeight = Math.round(lineHeight);
					}
					if (bHeight < \$j("#"+id+"_lbl .small").height() ) {
						\$j("#"+id+"_lbl .small").attr("style","text-align:left;");
					}


				}
EOT;

	}

	static function echoItemInputCheckTable($item_datas,$is_noEcho = false,$is_displayMenu = true){
		$echo_data  = '<div id="item_cds" class="sl_checkbox" >';
		if ( count($item_datas) == 1 && $is_displayMenu === false) {
			$echo_data  = '<div id="item_cds_wrap" style="display:none" >'.$echo_data;
		}
		if ($item_datas) {
			$echo_data .= '<table id="sl_front_items"><tbody>';
			$loop_max = count($item_datas);
			for($i = 0 ; $i < $loop_max ; $i += 2 ){
				$echo_data .= '<tr>';
				for($j= 0 ; $j < 2 ; $j++ ) {
					if ( $loop_max > ($i+$j) ) {
						$d1 = $item_datas[$i+$j];
						$edit_price = number_format($d1['price']);
//						$edit_name = htmlspecialchars($d1['short_name'],ENT_QUOTES);
						$edit_name = htmlspecialchars($d1['name'],ENT_QUOTES);
						$echo_data .= <<<EOT
							<td>
							<input type="checkbox" id="check_{$d1['item_cd']}" value="{$d1['item_cd']}" />
							<input type="hidden" id="check_price_{$d1['item_cd']}" value="{$d1['price']}" />
							<input type="hidden" id="check_minute_{$d1['item_cd']}" value="{$d1['minute']}" />
							</td><td>
							<label for="check_{$d1['item_cd']}" class="sl_items_label" id="sl_items_lbl_{$d1['item_cd']}">{$edit_name}({$edit_price})</label>
							</td>
EOT;
					}
				}
				$echo_data .= '</tr>';
			}
			$echo_data .= "</tbody></table>";
		}
		else {
			$echo_data .= self::setNoMenuData();
		}
		$echo_data .= '</div>';
		if ( count($item_datas) == 1 && $is_displayMenu === false) {
			$echo_data  .= '</div>';
		}
		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;

	}

	static function setNoMenuData() {
		$echo_data = '<table id="sl_front_items"><tbody><tr><td></td>';
		$echo_data .= '<td><label>'.__("No Menu Data",SL_DOMAIN).'</label></td>';
		$echo_data .= '<td></td><td><label></label></td></tr></tbody></table>';
		return $echo_data;
	}

	static function echoItemInputCheckTableForOneColunn($item_datas,$is_displayMenu = true){
		$echo_data  = '<div id="item_cds" class="sl_checkbox" >';
		if (  count($item_datas) == 1 && $is_displayMenu === false) {
			$echo_data  = '<div id="item_cds_wrap" style="display:none" >'.$echo_data;
		}
		if ($item_datas) {
			$echo_data .= '<table id="sl_front_items"><tbody>';
			$loop_max = count($item_datas);
			for($i = 0 ; $i < $loop_max ; $i++ ){
				$echo_data .= '<tr>';
						$d1 = $item_datas[$i];
						$edit_price = number_format($d1['price']);
						$edit_name = htmlspecialchars($d1['name'],ENT_QUOTES);
						$echo_data .= <<<EOT
							<td>
							<input type="checkbox" id="check_{$d1['item_cd']}" value="{$d1['item_cd']}" />
							<input type="hidden" id="check_price_{$d1['item_cd']}" value="{$d1['price']}" />
							<input type="hidden" id="check_minute_{$d1['item_cd']}" value="{$d1['minute']}" />
							</td><td>
							<label for="check_{$d1['item_cd']}" class="sl_items_label" id="sl_items_lbl_{$d1['item_cd']}">{$edit_name}({$edit_price})</label>
							</td>
EOT;
				$echo_data .= '</tr>';
			}
			$echo_data .= "</tbody></table>";
		}
		else {
			$echo_data .= self::setNoMenuData();
		}
		$echo_data .= '</div>';
		if (  count($item_datas) == 1 && $is_displayMenu === false) {
			$echo_data  .= '</div>';
		}
		echo $echo_data;

	}


	//echoItemInputCheckTableとはキーにbranch_cdがあるので受取るデータの形式がことなる
	static function echoItemInputCheckTableForSet($item_datas,$is_multi_branch){
		$echo_data  = '<div id="item_cds" class="sl_checkbox" >';
		if ($item_datas) {
			//単一店舗の場合は、branch_cdを無視
			$echo_data .= '<table id="sl_front_items" >';
			if (! $is_multi_branch) {
				$echo_data .= '<tbody>';
			}

			foreach ($item_datas as $k1 => $d1 ) {
				if ($is_multi_branch) {
					$echo_data .= '<tbody id="sl_tb_items_'.$k1.'">';
				}
				$loop_max = count($d1);
				for($i = 0 ; $i < $loop_max ; $i += 2 ){
					$echo_data .= '<tr>';
					for($j= 0 ; $j < 2 ; $j++ ) {
						if ( $loop_max > ($i+$j) ) {
							$d2 = $d1[$i+$j];
							$edit_name = htmlspecialchars($d2['name'],ENT_QUOTES);
							$echo_data .= <<<EOT
								<td>
								<input type="checkbox" id="check_{$d2['item_cd']}" value="{$d2['item_cd']}" class="sl_items_set" />
								<input type="hidden" id="check_all_flg_{$d2['item_cd']}" value="{$d2['all_flg']}" />
								</td><td>
								<label for="check_{$d2['item_cd']}">{$edit_name}</label>
								</td>
EOT;
						}
					}
					$echo_data .= '</tr>';
				}
				if ($is_multi_branch) {
					$echo_data .= "</tbody>";
				}

			}
			//単一店舗の場合は、branch_cdを無視
			if (! $is_multi_branch) {
				$echo_data .= "</tbody>";
			}
			$echo_data .= "</table>";

		}
		$echo_data .= '</div>';
		echo $echo_data;

	}


	static function echoStaffSelect($target_name,$staff_datas
			,$is_anyone = true,$is_noEcho = false,$is_displayStaff = true){

		$isNoDisplay = false;
		$echo_data = "";

		if ( count($staff_datas) == 1 && $is_displayStaff === false) {
			$isNoDisplay = true;
			$echo_data .= '<div id="sl_staff_cd_wrap" style="display:none">';
		}
		$echo_data .= '<select id="'.$target_name.'">';
		if ($is_anyone ) {
			$echo_data .= '<option value="'.Salon_Default::NO_PREFERENCE.'">'.__('Anyone',SL_DOMAIN).'</option>';
		}
		else {
			$echo_data .= '<option value="">'.__('select please',SL_DOMAIN).'</option>';
		}
		foreach($staff_datas as $k1 => $d1 ) {
			$echo_data .= '<option value="'.$d1['staff_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
		}
		$echo_data .= '</select>';
		if ($isNoDisplay) {
			$echo_data .= '</div>';
		}
		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;

	}

	static function echoTimeSelect($id,$open_time,$close_time,$time_step,$is_noEcho = false,$class = "",$isIncludeClose = false) {
		$dt = new DateTime($open_time);
		$last_hh = substr($close_time,0,2);
		if ($last_hh > 23 ) {
			$last_hh -= 24;
			$last_hour = $last_hh .":".substr($close_time,2,2);
			$dt_max = new DateTime($last_hour);
			$dt_max->modify('+1 days');
		}
		else {
			$last_hour = $last_hh .":".substr($close_time,2,2);
			$dt_max = new DateTime($last_hour);
		}

		if ($open_time = "0000" && $close_time >= "2359" && $time_step > 60) {
			$time_step = 30;
		}
		$echo_data =  '<select name="'.$id.'" id="'.$id.'" '.$class.' >';
//		$echo_data .=   '<option value="-1" >'.__('no setting',SL_DOMAIN).'</option>';
		while($dt <= $dt_max ) {
			if ($isIncludeClose || $dt != $dt_max) {
				$echo_data .= '<option value="'.$dt->format("H:i").'" >'.$dt->format("H:i").'</option>';
			}
			$dt->modify("+".$time_step." minutes");
		}
		$echo_data .= '</select>';

		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;


	}

	static function echoDisplayErrorLable() {
		echo <<<EOT
			function fnDisplayErrorLabel(target,msg) {
//			var label = \$j("#"+target).find("span");
			var label = \$j("#"+target).children(".small")
			var set_msg = msg;
			if (label.hasClass("error") ) {
				set_msg = label.text()+" "+set_msg;
			}
			label.text(set_msg );
			label.addClass("error small");
		}
EOT;
	}


	static function echoRoleSelect($id,$is_noEcho = false) {

		$echo_data =  '<select name="'.$id.'" id="'.$id.'">';
		global $wp_roles;
		foreach ($wp_roles->role_names as $k1 => $d1) {
			if ($k1 != 'subscriber' )
				$echo_data .=  '<option value="'.$k1.'">'._x($d1,'User role').'</option>';
		}
		$echo_data .= '</select>';

		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;


	}

	static function echoTimeStepSelect($id,$is_noEcho = false) {

		$echo_data =  '<select name="'.$id.'" id="'.$id.'">';
		$datas = array(10,15,30,60,90,120,180,240,300,360);
		foreach ($datas as  $d1) {
			$echo_data .=  '<option value="'.$d1.'">'.$d1.'</option>';
		}
		$echo_data .= '</select>';

		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;

	}

	static function echoMinuteSelect($id,$is_noEcho = false) {

		$echo_data =  '<select name="'.$id.'" id="'.$id.'">';
		$datas = array(10,20,30,40,50,60,70,80,90,100,110,120,130,140,150,160,170,180,190,200,210,220,230,240);
		foreach ($datas as  $d1) {
			$echo_data .=  '<option value="'.$d1.'">'.$d1.'</option>';
		}
		$echo_data .= '</select>';

		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;

	}

	static function echoSearchCustomer($url = '') {
		if (empty($url) ) $url = get_bloginfo( 'wpurl' );
		$target_src = $url.'/wp-admin/admin-ajax.php?action=slsearch';
		$check_char = __('No',SL_DOMAIN);
		echo <<<EOT
			\$j("#button_search").click(function(){
				\$j.ajax({
					type: "post",
					url:  "{$target_src}",
					dataType : "json",
					data: {
						"type":"reservation",
						"name":\$j("#name").val(),
						"mail":\$j("#mail").val(),
						"menu_func":"Search_Page",
						"tel":\$j("#tel").val()
					},

					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
						}
						else {
							var mW = \$j("#sl_search").find('.modalBody').innerWidth() / 2;
							var mH = \$j("#sl_search").find('.modalBody').innerHeight() / 2;
							\$j("#sl_search").find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
							\$j("#sl_search").css({'display':'block'});
							\$j("#sl_search").animate({'opacity':'1'},'fast');
							\$j("#sl_search_result").html(data.set_data);
							if (+data.cnt > 0 ) {
								\$j("#sl_search_result tr").click(function(event) {
									if (this.children[0].innerHTML == "{$check_char}" ) return;
									var name = this.children[1].innerHTML;
									\$j("#name").val(name);
									var tel = this.children[2].innerHTML;
									if (! tel) tel = this.children[3].innerHTML;
									\$j("#tel").val(tel);
									\$j("#mail").val(this.children[4].innerHTML);
									save_name = name;
									save_tel = tel;
									save_mail = this.children[4].innerHTML;
									save_user_login = \$j(this).find("input").val();
									fnRemoveModalResult(this.parentNode.parentNode);
								});
							}
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
				});
			});
		\$j('#button_close1,#button_close2').click(function(){
			fnRemoveModalResult(this);
		});
EOT;

	}

	static function echoDownloadEvent($target){
		$target_src = get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php?action=sldownload';
		echo <<<EOT
			\$j("#button_download").click(function(){
				 \$j.ajax({
						type: "post",
						url:  "{$target_src}",
						dataType : "json",
						data: {
							"type":"select",
							"target":"{$target}"
						},

						success: function(data) {
							if (data.status == "Error" ) {
								alert(data.message);
								return false;
							}
							else {
								var mW = \$j("#sl_download").find('.modalBody').innerWidth() / 2;
								var mH = \$j("#sl_download").find('.modalBody').innerHeight() / 2;
								\$j("#sl_download").find('.modalBody').css({'margin-left':-mW,'margin-top':-mH});
								\$j("#sl_download").css({'display':'block'});
								\$j("#sl_download").animate({'opacity':'1'},'fast');
								\$j("#sl_download_result").html(data.set_data);
								if (+data.cnt > 0 ) {
									\$j("#sl_download_result input[type=button]").click(function(event) {

									});
								}
							}
						},
						error:  function(XMLHttpRequest, textStatus){
							alert (textStatus);
							return false;
						}
				 });

			});
			\$j('#button_close1,#button_close2').click(function(){
				fnRemoveSearchResult();
			});

EOT;
	}

	static function echoRemoveModal() {
		echo <<<EOT
			function fnRemoveModalResult(element) {
				var target = \$j(element).parent().parent().parent();
				target.animate(
					{opacity:0,},
					{duration:'fast',complete:
						function() {
							\$j(element).parent().html("");
							target.css({'display':'none'});
						},
				});
			}
EOT;
	}

	static function echoDayFormat() {
		echo <<<EOT
			function fnDayFormat(date,format) {
				edit = format;
				edit = edit.replace("%Y",date.getFullYear());
				edit = edit.replace("%m",(date.getMonth()+1<10?"0":"")+(date.getMonth()+1));
				edit = edit.replace("%d",(date.getDate()+0<10?"0":'')+date.getDate());
				return edit;
			}
EOT;
	}

	//[2014/08/15]
	public function echoCheckDeadline($minutes,$first_valid_yyyymmdd) {
		//管理者は過去でも動作可能にする。
		if ($this->isSalonAdmin() ) {
			echo '	function _checkDeadline(checkTime,openTime,closeTime) { return true; }';
			return;
		}
		$msg_part = __('%m/%d/%Y',SL_DOMAIN);
		$msg = __('Your reservation is possible from %s.',SL_DOMAIN);

		$setLimitTime = 'limit_time.setMinutes(limit_time.getMinutes()+'.$minutes.');';
		if ((24 * 60 ) <= $minutes) {
			$setDates = round($minutes / (60 * 24));
			$setLimitTime = 'limit_time.setDate(limit_time.getDate()+'.$setDates.');';
			$setLimitTime .= 'limit_time.setHours(openTime.slice(0,2));';
			$setLimitTime .= 'limit_time.setMinutes(openTime.slice(-2));';
		}
		$load_yyyy = +substr($first_valid_yyyymmdd,0,4);
		$load_mm = +substr($first_valid_yyyymmdd,4,2);
		$load_dd = +substr($first_valid_yyyymmdd,6,2);

		echo <<<EOT
		function _checkDeadline(checkTime,openTime,closeTime) {
			var limit_time = new Date();
			{$setLimitTime}
			limit_time.setSeconds(0,0);
			if ("Date" !== Object.prototype.toString.call(checkTime).slice(8, -1) ){
				checkTime = new Date(checkTime);
			}
			if ( checkTime < limit_time ) {
				var firstDayOpen = new Date({$load_yyyy},{$load_mm} -1 ,{$load_dd}
									,openTime.slice(0,2),openTime.slice(-2));
				if (limit_time < firstDayOpen ) {
					limit_time = firstDayOpen;
				}
				var display_msg = fnDayFormat(limit_time,"{$msg_part}")+" "+('0'+limit_time.getHours()).slice(-2)+":"+('0'+limit_time.getMinutes()).slice(-2);
				var display_main = "{$msg}";
				display_main = display_main.replace("%s",display_msg);
				alert(display_main);
				return false;
			}
			return true;

		}
EOT;
	}

	public function echoOver24Confirm($last_hour,$open_time,$close_time,$current_time) {

		if ( $last_hour > 23  && $open_time > $current_time && $close_time >= $current_time)  {
			$msg = __('Is this Date OK ? ',SL_DOMAIN);
			$msg_format = __('%m/%d/%Y',SL_DOMAIN);
			echo <<<EOT
			var time_from_hhmm = ('0'+target_day_from.getHours()).slice(-2)+('0'+target_day_from.getMinutes()).slice(-2);
			 if ({$open_time}  > time_from_hhmm ) {
				 if (! confirm("{$msg}"+"["+fnDayFormat(target_day_from,"{$msg_format}")+" "+('0'+target_day_from.getHours()).slice(-2)+":"+('0'+target_day_from.getMinutes()).slice(-2)+"]") ) return false;
			}
EOT;
		}
	}


	static function echoWorkingCheck($is_noEcho = false){
		$working_status_data  = array(
			Salon_Working::USUALLY => __('Regular Duty',SL_DOMAIN)
			,Salon_Working::DAY_OFF => __('Day Off',SL_DOMAIN)
//			,Salon_Working::IN => __('IN',SL_DOMAIN)
//			,Salon_Working::OUT => __('OUT',SL_DOMAIN)
			,Salon_Working::LATE_IN => __('Late In',SL_DOMAIN)
			,Salon_Working::EARLY_OUT => __('Early Out',SL_DOMAIN)
			,Salon_Working::HOLIDAY_WORK => __('Holiday Shift',SL_DOMAIN)
//			,Salon_Working::ABSENCE => __('ABSENCE',SL_DOMAIN)
		);

		$echo_data = '<div id="working_cds" class="sl_checkbox" >';
		foreach ($working_status_data as $k1 => $d1 ) {
			$echo_data .= '<input type="checkbox" id="check_'.$k1.'" value="'.$k1.'" />';
			$echo_data .= '<label for="check_'.$k1.'">&nbsp;'.$d1.'&nbsp;</label>';
		}
		$echo_data .= '</div>';
		if ($is_noEcho) return str_replace(array("\r\n","\r","\n"), '', $echo_data);
		else echo $echo_data;

	}

	public function echoDownloadFunc($branch_cd,$target){
		$target_src = get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php?action=sldownload';
		echo <<<EOT
			function fnExecDownload(element) {
				var tmp = new Array();
				\$j("#sl_download_result input[type=checkbox]").each(function (){
					if ( \$j(this).is(":checked") ) {
						tmp.push( \$j(this).val() );
					}
				});

				\$j.ajax({
					type: "post",
					url:  "{$target_src}",
					dataType : "json",
					data: {
						"type":"exec",
						"target":"{$target}",
						"cols":tmp.join(","),
						"branch_cd":{$branch_cd},
						"nonce":"{$this->nonce}",
						"menu_func":"Download_Exec"
					},

					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
						}
						else {
							fnRemoveModalResult("#button_exec");
							location.href = data.redirect_url;
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
			});
		}


EOT;
	}



	public function set_datepicker_date ($branch_cd = null,$target_year = null,$sp_dates = null){
	//  "20130101":{type:0, title:"元日"},
	// で、cssの定義と連動させて色を変える
		echo 'var holidays = {';
		$holiday = unserialize(get_option("salon_holiday"));
		$tmp_table = array();
		$now = new DateTime();
		$min = clone $now;
		$max = clone $now;
// 		$min->sub(new DateInterval("P".$this->config_datas['SALON_CONFIG_BEFORE_DAY']."D"));
// 		$max->add(new DateInterval("P".$this->config_datas['SALON_CONFIG_AFTER_DAY']."D"));
		$min->modify("-".$this->config_datas['SALON_CONFIG_BEFORE_DAY']." day");
		$max->modify("+".$this->config_datas['SALON_CONFIG_AFTER_DAY']." day");

		$minYmd = $min->format("Ymd");
		$maxYmd = $max->format("Ymd");
 		foreach ($holiday as $k1 => $d1 ) {
			if (($minYmd <= +$d1[0]) && (+$d1[0] <= $maxYmd)) {
				$tmp_table[] = '"'.$d1[0].'":{type:0,title:"'.$d1[1].'"}';
			}
		}
		echo implode(',',$tmp_table);
		echo '};';
		//特殊な営業日・休業日の設定
		$tmp_table2 = array();
		echo "\n";
		echo 'var sp_dates = {';
		if (! empty($branch_cd) ) {

/*			$tmp_datas = sl_getBranchData($branch_cd,'sp_dates');
			$sp_dates = unserialize($tmp_datas['sp_dates']);
*/
			if (empty($target_year) ) {
				$target_year = date_i18n("Y");
			}

			if ($sp_dates && isset($sp_dates[$target_year]) && count($sp_dates[$target_year]) > 0) {
				foreach ($sp_dates[$target_year] as $k1 => $d1) {
					if (($minYmd <= $k1) && ($k1 <= $maxYmd)) {
						$from = isset($d1['fromHHMM'])?$d1['fromHHMM']:"";
						$to = isset($d1['toHHMM'])?$d1['toHHMM']:"";
						$sts = isset($d1['status'])?$d1['status']:Salon_Status::OPEN;
						$tmp_table2[] = '"'.$k1.'":{open:"'.$from.'",close:"'.$to.'",type:'.$sts.',title:"'.($sts== Salon_Status::OPEN ?  __('on business',SL_DOMAIN) :  __('Special Absence',SL_DOMAIN)).'"}';
					}

				}
			}
			echo implode(',',$tmp_table2);
		}
		echo '};';

	}

	static function echoLocaleDef($show_today = "") {
		if ($show_today == "" ) {
			$show_today = __('today',SL_DOMAIN);
		}
		echo '
			scheduler.locale={
				date: {
					month_full:['.__('"January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"',SL_DOMAIN).'],
					month_short:[ '.__('"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"',SL_DOMAIN).'],
					day_full:['.__('"Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"',SL_DOMAIN).'],
					day_short:['.__('"Sun","Mon","Tue","Wed","Thu","Fri","Sat"',SL_DOMAIN).']
				},
				labels:{
					dhx_cal_today_button:"'.$show_today.'",
					day_tab:"'.__('Day',SL_DOMAIN).'",
					week_tab:"'.__('Week',SL_DOMAIN).'",
					month_tab:"'.__('Month',SL_DOMAIN).'",
				}
			};

			scheduler.config.default_date = "'.__('%j %M %Y',SL_DOMAIN).'";
			scheduler.config.month_date ="'.__('%F %Y',SL_DOMAIN).'";
			scheduler.config.week_date = "'.__('%l',SL_DOMAIN).'";
			scheduler.config.day_date = "'.__('%D, %F %j',SL_DOMAIN).'";

//			scheduler.config.default_date = "%Y/%m/%d(%D)";
//			scheduler.config.month_date = "%Y/%m";
//			scheduler.config.week_date = "%l";
//			scheduler.config.day_date = "%n/%d(%D)";

		';

	}

	//[2014/06/22]
	static function echoItemFromto ($item_datas) {
		echo 'var item_fromto = Array();';
		foreach($item_datas as $k1 => $d1 ) {
			echo 'item_fromto['.$d1['item_cd'].'] = {"f":'.intval($d1['exp_from']).',"t":'.intval($d1['exp_to']).'};';
		}
	}

	static function set_datepickerDefault($is_maxToday = false,$is_all = false){
		$range = 'minDate: new Date()';
		if ($is_maxToday) $range = 'maxDate: new Date()';
		if ($is_all) $range = 'minDate:new Date(2000,0,1),maxDate:new Date(2099,11,31)';
		echo
			'$j.datepicker.setDefaults({
					closeText: "'.__('close',SL_DOMAIN).'",
					'.__('prevText: "&#x3C;"',SL_DOMAIN).',
					'.__('nextText: "&#x3E;"',SL_DOMAIN).',
					currentText: "'.__('today',SL_DOMAIN).'",
					monthNames: ['.__('"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"',SL_DOMAIN).'],
					monthNamesShort: ['.__('"Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"',SL_DOMAIN).'],
					dayNames: ['.__('"Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"',SL_DOMAIN).'],
					dayNamesShort: ['.__('"Sun","Mon","Tue","Wed","Thu","Fri","Sat"',SL_DOMAIN).'],
					dayNamesMin: ['.__('"Sun","Mon","Tue","Wed","Thu","Fri","Sat"',SL_DOMAIN).'],
					weekHeader: "'.__('week',SL_DOMAIN).'",
					dateFormat: "'.__('mm/dd/yy',SL_DOMAIN).'",
					changeMonth: true,
					firstDay: 1,
					isRTL: false,
					showMonthAfterYear: true,
					showButtonPanel: false,
					'.__('yearSuffix:"" ',SL_DOMAIN).',
					'.$range.',
			});';

	}

	static function set_datepicker ($tag_id,$select_ok = false,$closed_data = null,$addcode="",$display_month = 1){
		$tmp_status = Salon_Status::OPEN;
		if ($select_ok) $tmp_select = 'true';
		else $tmp_select = 'false';

		echo
				'$j("#'.$tag_id.'").datepicker({
					numberOfMonths: '.$display_month.'
					,beforeShowDay: function(day) {
					  var result = [true,"",""];
					  var holiday = holidays[$j.format.date(day, "yyyyMMdd")]
					  var sp_date = sp_dates[$j.format.date(day, "yyyyMMdd")]
					  if (sp_date) {
						if (sp_date.type == '.$tmp_status.' ) {
							result =  [true, "date-holiday3", sp_date.title];
						}
						else {
							result =  ['.$tmp_select.', "date-holiday2", sp_date.title];
						}
					  }
					  else {
						switch (day.getDay()) {';
		if ($closed_data == "") {
			echo 'case 0: result = [true,"date-sunday-show",""]; break; ';
			echo 'case 6: result = [true,"date-saturday-show",""]; break; ';
		}
		else {
			$datas = explode(",",$closed_data);
			foreach ($datas as $d1 ) {
				if (isset($d1))
					echo 'case '.$d1.': result = ['.$tmp_select.', "date-holiday1","'.__('holiday',SL_DOMAIN).'"];  break; ';
			}


			if (in_array(0,$datas) == false ) echo 'case 0: result = [true,"date-sunday",""]; break; ';
			if (in_array(6,$datas) == false ) echo 'case 6: result = [true,"date-satureday",""]; break; ';
		}
		//店のカレンダーでは祭日は表示しないけど
		//ふつうのカレンダーでは表示する
		$holiday_set = 'result[2] =  result[2] + holiday.title;';
		if ($closed_data == "") {
			$holiday_set .= 'result[1] =  "date-holiday0";';
		}
		echo <<<EOT2
						default:
							result = [true, "",""];
							break;
						}
					  }
					  if (holiday) {
						{$holiday_set}

					  }
					return result;
					}
					{$addcode}
				});
EOT2;
	}

	public function set_datepickerMobileFront ($tag_id,$branch_datas,$month_data){
		$tmp_status = Salon_Status::OPEN;
		$middle = Salon_Reservation_Full_Empty::MIDDLE;
		$high = Salon_Reservation_Full_Empty::HIGH;

		$dispEmptyFull = 'result[1] = "slm-reserved-low";';
 		if (($this->config_datas['SALON_CONFIG_STAFF_HOLIDAY_SET'] == Salon_Config::SET_STAFF_REVERSE)
 			|| (2400 < +substr($branch_datas["close_time"],0,4) ) ) {
 			$dispEmptyFull = '';
 		}

 		$open_time = $branch_datas["open_time"];
 		$close_time = $branch_datas["close_time"];
		echo
		'$j("#'.$tag_id.'").datepicker({
					numberOfMonths: 1
,changeMonth: false
,onSelect: function(dateText, inst) {
	target_day_from = new Date(dateText);
	target_day_to = new Date(dateText);
	var yyyymmdd = fnDayFormat(target_day_from,"%Y%m%d");
	slmSchedule.clearWidthData();
	setDayData(yyyymmdd);
}
				,beforeShowDay: function(day) {
					  var result = [true,"",""];

					var holiday = holidays[$j.format.date(day, "yyyyMMdd")]
					var sp_date = sp_dates[$j.format.date(day, "yyyyMMdd")]
					var emptyFull = month_datas[$j.format.date(day, "yyyyMMdd")]
					if (sp_date) {
						if (sp_date.type == '.$tmp_status.' ) {
							result =  [true, "slm-date-holiday3", sp_date.title];
						}
						else {
							if ((sp_date.open == "'.$open_time.'")
							&& (sp_date.close == "'.$close_time.'")) {
								result =  [false, "slm-date-holiday2", sp_date.title];
							}
							else {
								result =  [true, "slm-date-holiday2", sp_date.title];
							}
						}
					 }
					 else {
						switch (day.getDay()) {';
		//定休日なし
		if ($branch_datas['closed'] == "") {
			echo 'case 0: result = [true,"slm-date-sunday-show",""]; break; ';
			echo 'case 6: result = [true,"slm-date-saturday-show",""]; break; ';
		}
		else {
			$datas = explode(",",$branch_datas['closed']);
			$datas_detail = explode(";",$branch_datas['memo']);
			foreach ($datas as $k1 => $d1 ) {
				if (isset($d1)) {
					//全休は選択不可にするが、半休の場合は選択可能にする。
					$openClose = explode(",",$datas_detail[$k1]);
					if (($openClose[0] == $branch_datas['open_time'])
						&& ($openClose[1] == $branch_datas['close_time'])) {
						echo 'case '.$d1.': result = [false, "slm-date-holiday1","'.__('holiday',SL_DOMAIN).'"];  break; ';
					}
					else {
						echo 'case '.$d1.': result = [true, "slm-date-holiday4","'.__('holiday',SL_DOMAIN).'"];  break; ';
					}
				}
			}
			//定休日が土日ではないなら土日に色をつける
			if (in_array(0,$datas) == false ) echo 'case 0: result = [true,"slm-date-sunday",""]; break; ';
			if (in_array(6,$datas) == false ) echo 'case 6: result = [true,"slm-date-satureday",""]; break; ';
		}
		//店のカレンダーでは祭日は表示しないけど
		//ふつうのカレンダーでは表示する
		$holiday_set = 'result[2] =  result[2] + holiday.title;';
		if ($branch_datas['closed'] == "") {
			$holiday_set .= 'result[1] =  "slm-date-holiday0";';
		}

		echo <<<EOT2
						default:
							break;
						}
					  }
					  if (holiday) {
						{$holiday_set}
					  }
						var min = \$j("#{$tag_id}").datepicker("option","minDate");
						var max = \$j("#{$tag_id}").datepicker("option","maxDate");
						if ((day < min ) || (max < day)) {
							result[1] = "";
						}
						else {
							if (result[0]) {
								var saveClass = result[1];
								{$dispEmptyFull}
								if (emptyFull) {
									if (emptyFull.patern == {$middle} ) {
										result[1] = "slm-reserved-middle";
									}
									else if (emptyFull.patern == {$high} ) {
										result[1] = "slm-reserved-high";
									}
								}
								result[1] = saveClass + " " + result[1];
							}
						}
						return result;
					}
				});
EOT2;
	}

	public function echoSetHoliday($branch_datas,$target_year,$is_block = true) {
			$is_todayHoliday = false;
			//[2014/10/01]半休対応
			$set_html = htmlspecialchars($this->config_datas['SALON_CONFIG_DISPLAY_HOLIDAY'],ENT_QUOTES);
			$block = 'dhx_time_block';
			if (!$is_block ) $block = '';	//workingではブロックしない
			//詳細と休みの順番は一致している前提
			$set_days_array = explode(',',$branch_datas['closed']);
			//もし昔のをそのまま使用している場合はクリアしとく
			if ($branch_datas['memo'] == "MEMO") $branch_datas['memo'] = "";
			//ここは0のみがはいっていることはないのでemptyで
			if (!empty($branch_datas['memo']) ) {
				$set_days_detail_array = explode(';',$branch_datas['memo']);
				foreach ($set_days_array as $k1 => $d1) {
					$set_day = $d1;
					$time_array = array();
					if (count($set_days_detail_array) > 0 &&  !empty($set_days_detail_array[$k1]) )
						$time_array = explode(',',$set_days_detail_array[$k1]);
					else
						$time_array = array($branch_datas['open_time'],$branch_datas['close_time']);
					$frto = array();
					$frto[] = substr($time_array[0],0,2);
					$frto[] = substr($time_array[0],-2);
					$frto[] = substr($time_array[1],0,2);
					$frto[] = substr($time_array[1],-2);

					$zones = (+$frto[0]*60+$frto[1]).','.(+$frto[2]*60+$frto[3]);
					if(substr($branch_datas['close_time'],0,2)<24 && $time_array[0] <= $branch_datas['open_time'] && $branch_datas['close_time']  <= $time_array[1] ) {
						$zones = "\"fullday\"";
					}
					echo <<<EOT
					var options = {
						days:{$set_day},
						zones:[{$zones}],
						type: "{$block}",
						css: "dhx_sl_holiday",
						html: "{$set_html}"
					};
					scheduler.addMarkedTimespan(options);
EOT;

				}
			}
			if (strpos($branch_datas['closed'],date_i18n('w') ) !== false ) $is_todayHoliday = true;
			//特殊な日の設定（定休日だけど営業するor営業日だけど休むなど）
			$sp_dates = unserialize($branch_datas['sp_dates']);
//			$on_business_array = array();
			$on_business_array_from = array();
			$on_business_array_to = array();
//			$holiday_array = array();
			$holiday_array_from = array();
			$holiday_array_to = array();
			$today_check_array = array();

			$now = new DateTime(date_i18n('Ymd'));
			$min = clone $now;
			$max = clone $now;
// 			$min->sub(new DateInterval("P".$this->config_datas['SALON_CONFIG_BEFORE_DAY']."D"));
// 			$max->add(new DateInterval("P".$this->config_datas['SALON_CONFIG_AFTER_DAY']."D"));
			$min->modify("-".$this->config_datas['SALON_CONFIG_BEFORE_DAY']." day");
			$max->modify("+".$this->config_datas['SALON_CONFIG_AFTER_DAY']." day");
			$minYmd = $min->format("Ymd");
			$maxYmd = $max->format("Ymd");

			for ($i=0;$i<2;$i++) {	//指定年と＋１(年末のことを考えて）
				$tmp_year = intval($target_year) + $i;
				if ($sp_dates && !empty($sp_dates[$tmp_year])) {
					foreach ($sp_dates[$tmp_year] as $k1 => $d1) {
						if (($minYmd <= $k1) && ($k1 <= $maxYmd)) {
							$today_check_array[$k1] = $d1['status'];
							$tmp = 'new Date('.$tmp_year.','.(string)(intval(substr($k1,4,2))-1).','.(string)(intval(substr($k1,6,2))+0).')';

							$tmp_from_hhmm = $branch_datas['open_time'];
							if (isset($d1['fromHHMM'])) {
								$tmp_from_hhmm = $d1['fromHHMM'];
							}
// 							$tmp_from = '1100';
							$tmp_to_hhmm = $branch_datas['close_time'];
							if (isset($d1['toHHMM'])) {
								$tmp_to_hhmm = $d1['toHHMM'];
							}
// 							$tmp_from_hhmm = "1100";
// 							$tmp_to_hhmm = "1500";
							$tmp_from = 'new Date('.$tmp_year
							.','.(string)(intval(substr($k1,4,2))-1)
							.','.(string)(intval(substr($k1,6,2))+0)
							.','.+substr($tmp_from_hhmm,0,2)
							.','.+substr($tmp_from_hhmm,2,2)
							.')';
							$tmp_to = 'new Date('.$tmp_year
							.','.(string)(intval(substr($k1,4,2))-1)
							.','.(string)(intval(substr($k1,6,2))+0)
							.','.+substr($tmp_to_hhmm,0,2)
							.','.+substr($tmp_to_hhmm,2,2)
							.')';
							if ($d1['status'] == Salon_Status::OPEN ) {
								$on_business_array[] = $tmp;
								$on_business_array_from[] = $tmp_from;
								$on_business_array_to[] = $tmp_to;
							}
							elseif ($d1['status'] == Salon_Status::CLOSE ) {
								$holiday_array[] = $tmp;
								$holiday_array_from[] = $tmp_from;
								$holiday_array_to[] = $tmp_to;
							}
						}
					}
				}
			}
//			echo 'var on_business = [ '.implode(',',$on_business_array).' ];';
			echo 'var on_business_from = [ '.implode(',',$on_business_array_from).' ];';
			echo 'var on_business_to = [ '.implode(',',$on_business_array_to).' ];';
//			echo 'var holidays = [ '.implode(',',$holiday_array).' ];';
			echo 'var holidays_from = [ '.implode(',',$holiday_array_from).' ];';
			echo 'var holidays_to = [ '.implode(',',$holiday_array_to).' ];';


// 			if ($endHH > 23) {

// 				echo 'var add_date = 1;';
// 				$endHH -= 24;
// 			}
// 			else {
// 				echo 'var add_date = 0;';
// 			}
			//勤務時間を入力する場合は何も表示しない
			$set_on_business = "";
			$on_business_css = "";
			if ($this->config_datas['SALON_CONFIG_STAFF_HOLIDAY_SET'] == Salon_Config::SET_STAFF_NORMAL) {
				$set_on_business = htmlspecialchars($this->config_datas['SALON_CONFIG_DISPLAY_SPECIAL_ONBUSINESS'],ENT_QUOTES);
				$on_business_css = "on_business";
			}
//			echo <<<EOT2
// 			for (var i=0; i<on_business.length; i++) {
// 				var start_date = new Date(on_business[i].getFullYear(),on_business[i].getMonth(),on_business[i].getDate(),{$startHH},{$startMM},0);
// 				var end_date = new Date(on_business[i].getFullYear(),on_business[i].getMonth(),on_business[i].getDate(),{$endHH},{$endMM},0);
// 				end_date.setDate(end_date.getDate()+add_date);
// 				var options = {
// 					start_date: start_date,
// 					end_date:  end_date,
// 					type: "",
// 					css: "on_business",
// 					html: "{$set_on_business}"
// 				};
// 				scheduler.addMarkedTimespan(options);
// 			}

// 			for (var i=0; i<holidays.length; i++) {
// 				var date = holidays[i];
// 				var start_date = new Date(holidays[i].getFullYear(),holidays[i].getMonth(),holidays[i].getDate(),{$startHH},{$startMM},0);
// 				var end_date = new Date(holidays[i].getFullYear(),holidays[i].getMonth(),holidays[i].getDate(),{$endHH},{$endMM},0);
// 				end_date.setDate(end_date.getDate()+add_date);
// 				var options = {
// 					start_date: start_date,
// 					end_date:  end_date,
// 					type: "{$block}",
// 					css: "dhx_sl_holiday",
// 					html: "{$set_html}"
// 				};
// 				scheduler.addMarkedTimespan(options);
// 			}
//EOT2;

		$startHH = +substr($branch_datas['open_time'],0,2);
		$startMM = +substr($branch_datas['open_time'],2,2);
		$endHH = +substr($branch_datas['close_time'],0,2);
		$endMM = +substr($branch_datas['close_time'],2,2);


		echo <<<EOT3

		for (var i=0; i<on_business_from.length; i++) {
			var start_date = new Date(on_business_from[i].getFullYear(),on_business_from[i].getMonth(),on_business_from[i].getDate(),on_business_from[i].getHours(),on_business_from[i].getMinutes(),0);
			var end_date = new Date(on_business_to[i].getFullYear(),on_business_to[i].getMonth(),on_business_to[i].getDate(),on_business_to[i].getHours(),on_business_to[i].getMinutes(),0);
			var options = {
				start_date: start_date,
				end_date:  end_date,
				type: "",
				css: "{$on_business_css}",
				html: "{$set_on_business}"
			};
			if ((+start_date.getHours() == {$startHH})
				&& (+start_date.getMinutes() == {$startMM})
				&& (+end_date.getHours() == {$endHH})
				&& (+end_date.getMinutes() == {$endMM})) {
				options = {
					days:start_date,
					zones:["fullday"],
					type: "",
					css: "{$on_business_css}",
					html: "{$set_on_business}"
				};
			}
			scheduler.addMarkedTimespan(options);
		}
		for (var i=0; i<holidays_from.length; i++) {
			var start_date = new Date(holidays_from[i].getFullYear(),holidays_from[i].getMonth(),holidays_from[i].getDate(),holidays_from[i].getHours(),holidays_from[i].getMinutes(),0);
			var end_date = new Date(holidays_to[i].getFullYear(),holidays_to[i].getMonth(),holidays_to[i].getDate(),holidays_to[i].getHours(),holidays_to[i].getMinutes(),0);
			var options = {
				start_date: start_date,
				end_date:  end_date,
				type: "{$block}",
				css: "dhx_sl_holiday",
				html: "{$set_html}"
			};
			if ((+start_date.getHours() == {$startHH})
				&& (+start_date.getMinutes() == {$startMM})
				&& (+end_date.getHours() == {$endHH})
				&& (+end_date.getMinutes() == {$endMM})) {
				options = {
					days:start_date,
					zones:["fullday"],
					type: "{$block}",
					css: "dhx_sl_holiday",
					html: "{$set_html}"
				};
			}
 			scheduler.addMarkedTimespan(options);
	}
EOT3;

			if (isset($today_check_array[date_i18n('Ymd')]) ) {
				if ($today_check_array[date_i18n('Ymd')] == Salon_Status::OPEN ) $is_todayHoliday = false;
				else $is_todayHoliday = true;
			}
		return $is_todayHoliday;

	}



	static function echoCheckClinet($check_patern) {
		$default_margin = self::INPUT_BOTTOM_MARGIN;
	//reqOther_tel_ZZZZとあったらidがtelとZZZZに入力があるか確認する
	//[TODO]sl_checkboxは重いか？
	//[TODO]済 exceptはとりあえず１つだけ→カンマ区切り
		echo <<<EOT
			function checkItem(target,except ) {
				var is_error = false;
				var tmp_excepts = Array();
				var focusId = "";
				if (except) {
					if (except.indexOf(",") > -1) {
						var tmp_excepts = except.split(",");
					}
					else {
						tmp_excepts.push(except);
					}
				}
				\$j("#"+target).find("input[type=text],textarea,select,.sl_checkbox").each(function(){
					if (\$j(this).hasClass("sl_nocheck") ) return;
					var id = \$j(this).attr("id");
					if (except) {
						for(var i=0;i<tmp_excepts.length;i++){
							if ( id == tmp_excepts[i] ) return;
						}

					}
					var item_errors = Array();
					var cl = \$j(this).attr("class");
					if (cl) {
						var val = \$j(this).val();
EOT;
		//必須チェックがなくて、その他のチェックがある場合を考慮して
		$check_contents = self::setCheckContents();
		$key = array_search('chk_required',$check_patern);
		if ($key !== false) {
			echo $check_contents['chk_required'];
			unset($check_patern[$key]);
		}
		$key = array_search('reqOther',$check_patern) ;
		if ($key !== false) {
			echo $check_contents['reqOther'];
			unset($check_patern[$key]);
		}
		$key = array_search('reqCheck',$check_patern);
		if ($key !== false) {
			echo $check_contents['reqCheckbox'];
			unset($check_patern[$key]);
		}
		if ( count($check_patern) > 0 ) {
			echo 'if (( item_errors.length == 0 ) && (val != "" ) && (val != null) ){';
			foreach ($check_patern as $d1) {
				echo $check_contents[$d1];
			}
			echo '}';
		}
	//エラーの表示部

		echo <<<EOT2
					}

					\$j(this).removeAttr("style");
					var label = \$j(this).prev().children(".small");
					label.removeClass("sl_coler_not_complete");
					label.removeAttr("style");
					if (  item_errors.length > 0 ) {
						label.text(item_errors.join(" "));
						label.addClass("error small");
						is_error = true;
						if (focusId == "") focusId = this.id;
						var label_tag = \$j(this).prev();
						var diff = label_tag.outerHeight(true) - \$j(this).outerHeight(true);
						if (diff > 0 ) {
							diff += {$default_margin}+5;
							\$j(this).attr("style","margin-bottom: "+diff+"px;");
							label.attr("style","text-align:left;");
						}
					}
					else {
						label.text(check_items[id]["tips"]);
						label.removeClass("error");
						var label_tag = \$j(this).prev();
						var diff = label_tag.outerHeight(true) - \$j(this).outerHeight(true);
						if (diff > 0 ) {
							diff += {$default_margin}+5;
							\$j(this).attr("style","margin-bottom: "+diff+"px;");
							label.attr("style","text-align:left;");
						}
					}
				});
				if ( is_error ) {
					\$j("#"+focusId).focus();
					return false;
				}
				return true;
			}
EOT2;

	}


	static function echo_customize_dhtmlx($over24 = false){
		echo <<<EOT
			scheduler._pre_render_events = function(evs, hold) {
				var hb = this.xy.bar_height;
				var h_old = this._colsS.heights;
				var h = this._colsS.heights = [0, 0, 0, 0, 0, 0, 0];
				var data = this._els["dhx_cal_data"][0];
				if (!this._table_view)
					evs = this._pre_render_events_line(evs, hold);
				else
					evs = this._pre_render_events_table(evs, hold);

				if (this._table_view) {
					if (hold)
						this._colsS.heights = h_old;
					else {
						var evl = data.firstChild;
						if (evl.rows) {
							for (var i = 0; i < evl.rows.length; i++) {
								h[i]++;
								if ((h[i]) * hb > this._colsS.height - 22) {
									var before_heights = new Array();	//[hisa]
 									var cells = evl.rows[i].cells;

									for (var j = 0; j < cells.length; j++) {
										before_heights[j] = cells[j].childNodes[0].childNodes[1].style.height;	//[hisa]
										cells[j].childNodes[0].childNodes[1].style.height = h[i] * hb + "px";
										if (cells[j].childNodes[0].childNodes[1].childNodes[0])
											cells[j].childNodes[0].childNodes[1].childNodes[0].style.height = h[i] * hb + "px";
									}
									//[hisa]from
// 									for(var x = i+1;x < evl.rows.length; x++) {
// 										cells_next = evl.rows[x].cells;
// 										for(var y = 0 ; y < cells_next.length; y++ ) {
// 											if (cells_next[y].childNodes[0].childNodes[1].childNodes[0]) {
// 												var tmp_height = cells_next[y].childNodes[0].childNodes[1].childNodes[0].style.top.replace("px","");
// 												var tmp_before_height = before_heights[y].replace("px","");
// 												cells_next[y].childNodes[0].childNodes[1].childNodes[0].style.top = h[i] * hb + Number(tmp_height) - Number(tmp_before_height)  + "px";
// 											}
// 										}
// 									}
									//[hisa]to
									h[i] = (h[i - 1] || 0) + cells[0].offsetHeight;
								}
								h[i] = (h[i - 1] || 0) + evl.rows[i].cells[0].offsetHeight;
							}
							h.unshift(0);
							if (evl.parentNode.offsetHeight < evl.parentNode.scrollHeight && !evl._h_fix && scheduler.xy.scroll_width) {
								//we have v-scroll, decrease last day cell
// 								for (var i = 0; i < evl.rows.length; i++) {
// 									var cell = evl.rows[i].cells[6].childNodes[0];
// 									var w = cell.offsetWidth - scheduler.xy.scroll_width + "px";
// 									cell.style.width = w;
// 									cell.nextSibling.style.width = w;
// 								}
								evl._h_fix = true;
							}
						} else {
							if (!evs.length && this._els["dhx_multi_day"][0].style.visibility == "visible")
								h[0] = -1;
							if (evs.length || h[0] == -1) {
								//shift days to have space for multiday events
								var childs = evl.parentNode.childNodes;

								// +1 so multiday events would have 2px from top and 2px from bottom by default
								var full_multi_day_height = (h[0] + 1) * hb + 1;

								var used_multi_day_height = full_multi_day_height;
								var used_multi_day_height_css = full_multi_day_height + "px";
								if (this.config.multi_day_height_limit) {
									used_multi_day_height = Math.min(full_multi_day_height, this.config.multi_day_height_limit) ;
									used_multi_day_height_css = used_multi_day_height + "px";
								}

								data.style.top = (this._els["dhx_cal_navline"][0].offsetHeight + this._els["dhx_cal_header"][0].offsetHeight + used_multi_day_height ) + "px";
								data.style.height = (this._obj.offsetHeight - parseInt(data.style.top, 10) - (this.xy.margin_top || 0)) + "px";

								var multi_day_section = this._els["dhx_multi_day"][0];
								multi_day_section.style.height = used_multi_day_height_css;
								multi_day_section.style.visibility = (h[0] == -1 ? "hidden" : "visible");

								// icon
								var multi_day_icon = this._els["dhx_multi_day"][1];
								multi_day_icon.style.height = used_multi_day_height_css;
								multi_day_icon.style.visibility = (h[0] == -1 ? "hidden" : "visible");
								multi_day_icon.className = h[0] ? "dhx_multi_day_icon" : "dhx_multi_day_icon_small";
								this._dy_shift = (h[0] + 1) * hb;
								h[0] = 0;

								if (used_multi_day_height != full_multi_day_height) {
									data.style.top = (parseInt(data.style.top) + 2) + "px";

									multi_day_section.style.overflowY = "auto";
									multi_day_section.style.width = (parseInt(multi_day_section.style.width) - 2) + "px";

									multi_day_icon.style.position = "fixed";
									multi_day_icon.style.top = "";
									multi_day_icon.style.left = "";
								}
							}
						}
					}
				}

				return evs;
			};
			scheduler.showCover=function(box){
				if (box){
					box.style.display="block";

					var scroll_top = window.pageYOffset||document.body.scrollTop||document.documentElement.scrollTop;
					var scroll_left = window.pageXOffset||document.body.scrollLeft||document.documentElement.scrollLeft;

					var view_height = window.innerHeight||document.documentElement.clientHeight;

					if(scroll_top) // if vertical scroll on window
						box.style.top=Math.round(scroll_top+Math.max((view_height-box.offsetHeight)/2, 0))+"px";
					else // vertical scroll on body
						box.style.top=Math.round(Math.max(((view_height-box.offsetHeight)/2), 0) + 9)+"px"; // +9 for compatibility with auto tests

					// not quite accurate but used for compatibility reasons
					var left_pos = 0;	//[hisa]
					if(document.documentElement.scrollWidth > document.body.offsetWidth) // if horizontal scroll on the window
			//			box.style.left=Math.round(scroll_left+(document.body.offsetWidth-box.offsetWidth)/2)+"px";
						left_pos=Math.round(scroll_left+(document.body.offsetWidth-box.offsetWidth)/2);
					else // horizontal scroll on the body
			//			box.style.left=Math.round((document.body.offsetWidth-box.offsetWidth)/2)+"px";
						left_pos=Math.round((document.body.offsetWidth-box.offsetWidth)/2);
					if (left_pos < 0 ) left_pos = 0;
					box.style.left= left_pos + "px";
				}
				this.show_cover();
			};
			scheduler._on_dbl_click=function(e,a){if(a=a||e.target||e.srcElement,!this.config.readonly){var c=a.className.split(" ")[0];switch(c){case"dhx_scale_holder":case"dhx_scale_holder_now":case"dhx_month_body":case"dhx_wa_day_data":case"dhx_marked_timespan":if(!scheduler.config.dblclick_create)break;if(-1==a.className.indexOf("on_business"))this.addEventNow(this.getActionData(e).date,null,e);else{var t=this.dblclick_dhx_matrix_cell;t.call(this,e)}break;case"dhx_cal_event":case"dhx_wa_ev_body":case"dhx_agenda_line":case"dhx_grid_event":case"dhx_cal_event_line":case"dhx_cal_event_clear":var i=this._locate_event(a);if(!this.callEvent("onDblClick",[i,e]))return;this.config.details_on_dblclick||this._table_view||!this.getEvent(i)._timed||!this.config.select?this.showLightbox(i):this.edit(i);break;case"dhx_time_block":case"dhx_cal_container":return;default:var t=this["dblclick_"+c];if(t)t.call(this,e);else if(a.parentNode&&a!=this)return scheduler._on_dbl_click(e,a.parentNode)}}};
EOT;
		if ($over24) {

			echo <<<EOT2
	scheduler._on_mouse_move=function(e){if(this._drag_mode){var t=this._mouse_coords(e);if(!this._drag_pos||t.force_redraw||this._drag_pos.x!=t.x||this._drag_pos.y!=t.y){var s,i;if(this._edit_id!=this._drag_id&&this._close_not_saved(),this._drag_pos=t,"create"==this._drag_mode){this._close_not_saved(),this._loading=!0,s=this._get_date_from_pos(t).valueOf();var _=this.callEvent("onBeforeEventCreated",[e]);if(!_)return;if(!this._drag_start)return void(this._drag_start=s);i=s,i==this._drag_start;var a=new Date(this._drag_start),r=new Date(i);"day"!=this._mode&&"week"!=this._mode||a.getHours()!=r.getHours()||a.getMinutes()!=r.getMinutes()||(r=new Date(this._drag_start+1e3)),this._drag_id=this.uid(),this.addEvent(a,r,this.locale.labels.new_event,this._drag_id,t.fields),this.callEvent("onEventCreated",[this._drag_id,e]),this._loading=!1,this._drag_mode="new-size"}var d=this.getEvent(this._drag_id);if("move"==this._drag_mode)s=this._min_date.valueOf()+6e4*(t.y*this.config.time_step+24*t.x*60-(scheduler._move_pos_shift||0)),!t.custom&&this._table_view&&(s+=1e3*this.date.time_part(d.start_date)),s=this._correct_shift(s),i=d.end_date.valueOf()-(d.start_date.valueOf()-s);else{if(s=d.start_date.valueOf(),i=d.end_date.valueOf(),this._table_view){var h=this._min_date.valueOf()+t.y*this.config.time_step*6e4+(t.custom?0:864e5);"month"==this._mode&&(h=this._correct_shift(h,!1)),t.resize_from_start?s=h:i=h}else{var o;if(this.config.last_hour_48>2400){var l=this.date.date_part(new Date(d.start_date));d.start_date.getHours()<this.config.first_hour&&l.setDate(l.getDate()-1),i=l.valueOf()+t.y*this.config.time_step*6e4}else i=this.date.date_part(new Date(d.end_date)).valueOf()+t.y*this.config.time_step*6e4;this._els.dhx_cal_data[0].style.cursor="s-resize","week"!=this._mode&&"day"!=this._mode||(i=this._correct_shift(i))}if("new-size"==this._drag_mode)if(i<=this._drag_start){var n=t.shift||(this._table_view&&!t.custom?864e5:0);s=i-(t.shift?0:n),i=this._drag_start+(n||6e4*this.config.time_step)}else s=this._drag_start;else s>=i&&(i=s+6e4*this.config.time_step)}var o;this.config.last_hour_48>2400?(o=new Date(i),scheduler._allow_dnd=!0):o=new Date(i-1);var c=new Date(s);if(this._table_view||o.getDate()==c.getDate()&&o.getHours()<this.config.last_hour||scheduler._allow_dnd)if(d.start_date=c,d.end_date=new Date(i),this.config.update_render){var u=scheduler._els.dhx_cal_data[0].scrollTop;this.update_view(),scheduler._els.dhx_cal_data[0].scrollTop=u}else this.updateEvent(this._drag_id);this._table_view&&this.for_rendered(this._drag_id,function(e){e.className+=" dhx_in_move"})}}else if(scheduler.checkEvent("onMouseMove")){var f=this._locate_event(e.target||e.srcElement);this.callEvent("onMouseMove",[f,e])}},scheduler._reset_scale=function(){if(this.templates[this._mode+"_date"]){var e=this._els.dhx_cal_header[0],t=this._els.dhx_cal_data[0],s=this.config;e.innerHTML="",t.scrollTop=0,t.innerHTML="";var i=(s.readonly||!s.drag_resize?" dhx_resize_denied":"")+(s.readonly||!s.drag_move?" dhx_move_denied":"");i&&(t.className="dhx_cal_data"+i),this._scales={},this._cols=[],this._colsS={height:0},this._dy_shift=0,this.set_sizes();var _,a,r,d,h=parseInt(e.style.width,10),o=0;a=this.date[this._mode+"_start"](new Date(this._date.valueOf())),_=r=this._table_view?scheduler.date.week_start(a):a,d=this.date.date_part(scheduler._currentDate());var l=scheduler.date.add(a,1,this._mode),n=7;if(!this._table_view){var c=this.date["get_"+this._mode+"_end"];c&&(l=c(a)),n=Math.round((l.valueOf()-a.valueOf())/864e5)}this._min_date=_,this._els.dhx_cal_date[0].innerHTML=this.templates[this._mode+"_date"](a,l,this._mode);for(var u=0;n>u;u++){if(this._cols[u]=Math.floor(h/(n-u)),this._render_x_header(u,o,_,e),!this._table_view){var f=document.createElement("DIV"),v="dhx_scale_holder";_.valueOf()==d.valueOf()&&(v="dhx_scale_holder_now"),f.className=v+" "+this.templates.week_date_class(_,d),this.set_xy(f,this._cols[u]-1,s.hour_size_px*(s.last_hour-s.first_hour),o+this.xy.scale_width+1,0);for(var g=new Date(1980,1,1,this.config.first_hour,0,0),p=1*this.config.first_hour;p<this.config.last_hour;p++){var m=document.createElement("DIV");m.className="dhx_scale_hour dhx_scale_cell_plus",m.style.height=this.config.hour_size_px-(this._quirks?0:1)+"px";var y=this._cols[u]-1;this.config.left_border&&(y-=1,m.className+=" dhx_scale_hour_border"),m.style.width=y+"px",f.appendChild(m),g=this.date.add(g,1,"hour")}t.appendChild(f),this.callEvent("onScaleAdd",[f,_])}_=this.date.add(_,1,"day"),h-=this._cols[u],o+=this._cols[u],this._colsS[u]=(this._cols[u-1]||0)+(this._colsS[u-1]||(this._table_view?0:this.xy.scale_width+2)),this._colsS.col_length=n+1}if(this._max_date=_,this.config.last_hour_48>2400&&this._max_date.setHours(this.config.last_hour-24),this._colsS[n]=this._cols[n-1]+this._colsS[n-1],this._table_view){this._reset_month_scale(t,a,r),h=parseInt(t.clientWidth,10),o=0;for(var u=0;n>u;u++)this._cols[u]=Math.floor(h/(n-u)),e.childNodes[u].style.width=this._cols[u]-1+"px",e.childNodes[u].style.left=o+"px",h-=this._cols[u],o+=this._cols[u],this._colsS[u]=(this._cols[u-1]||0)+(this._colsS[u-1]||(this._table_view?0:this.xy.scale_width+2));this._colsS[n]=this._cols[n-1]+this._colsS[n-1]}else if(this._reset_hours_scale(t,a,r),s.multi_day){var x="dhx_multi_day";this._els[x]&&(this._els[x][0].parentNode.removeChild(this._els[x][0]),this._els[x]=null);var w=this._els.dhx_cal_navline[0],z=w.offsetHeight+this._els.dhx_cal_header[0].offsetHeight+1,b=document.createElement("DIV");b.className=x,b.style.visibility="hidden",this.set_xy(b,this._colsS[this._colsS.col_length-1]+this.xy.scroll_width,0,0,z),t.parentNode.insertBefore(b,t);var D=b.cloneNode(!0);D.className=x+"_icon",D.style.visibility="hidden",this.set_xy(D,this.xy.scale_width,0,0,z),b.appendChild(D),this._els[x]=[b,D],this._els[x][0].onclick=this._click.dhx_cal_data}}},scheduler._pre_render_events_line=function(e,t){e.sort(function(e,t){return e.start_date.valueOf()==t.start_date.valueOf()?e.id>t.id?1:-1:e.start_date>t.start_date?1:-1});var s=[],i=[];this._min_mapped_duration=Math.ceil(60*this.xy.min_event_height/this.config.hour_size_px);for(var _=0;_<e.length;_++){var a=e[_],r=a.start_date,d=a.end_date,h=r.getHours(),o=d.getHours();if(a._sday=this._get_event_sday(a),this.config.last_hour_48>2400&&a.start_date.getHours()<this.config.first_hour&&(a._sday-=1),s[a._sday]||(s[a._sday]=[]),!t){a._inner=!1;for(var l=s[a._sday];l.length;){var n=l[l.length-1],c=this._get_event_mapped_end_date(n);if(!(c.valueOf()<=a.start_date.valueOf()))break;l.splice(l.length-1,1)}for(var u=!1,f=0;f<l.length;f++){var n=l[f],c=this._get_event_mapped_end_date(n);if(c.valueOf()<=a.start_date.valueOf()){u=!0,a._sorder=n._sorder,l.splice(f,1),a._inner=!0;break}}if(l.length&&(l[l.length-1]._inner=!0),!u)if(l.length)if(l.length<=l[l.length-1]._sorder){if(l[l.length-1]._sorder)for(f=0;f<l.length;f++){for(var v=!1,g=0;g<l.length;g++)if(l[g]._sorder==f){v=!0;break}if(!v){a._sorder=f;break}}else a._sorder=0;a._inner=!0}else{var p=l[0]._sorder;for(f=1;f<l.length;f++)l[f]._sorder>p&&(p=l[f]._sorder);a._sorder=p+1,a._inner=!1}else a._sorder=0;l.push(a),l.length>(l.max_count||0)?(l.max_count=l.length,a._count=l.length):a._count=a._count?a._count:1}this.config.last_hour_48>2400&&(h<this.config.first_hour&&(h+=24),o<this.config.first_hour&&(o+=24)),(h<this.config.first_hour||o>=this.config.last_hour)&&(i.push(a),e[_]=a=this._copy_event(a),h<this.config.first_hour&&(a.start_date.setHours(this.config.first_hour),a.start_date.setMinutes(0)),o>=this.config.last_hour&&(a.end_date.setMinutes(0),a.end_date.setHours(this.config.last_hour)),a.start_date>a.end_date||h==this.config.last_hour)&&(e.splice(_,1),_--)}if(!t){for(var _=0;_<e.length;_++)e[_]._count=s[e[_]._sday].max_count;for(var _=0;_<i.length;_++)i[_]._count=s[i[_]._sday].max_count}return e},scheduler.render_event=function(e){var t=scheduler.xy.menu_width,s=this.config.use_select_menu_space?0:t;if(!(e._sday<0)){var i=scheduler.locate_holder(e._sday);if(i){var _=60*e.start_date.getHours()+e.start_date.getMinutes();if(this.config.last_hour_48>2400){e.start_date.getHours()<this.config.first_hour&&(_+=1440);var a=60*e.end_date.getHours()+e.end_date.getMinutes();e.end_date.getHours()<this.config.first_hour&&(a+=1440)}else var a=60*e.end_date.getHours()+e.end_date.getMinutes()||60*scheduler.config.last_hour;var r=e._count||1,d=e._sorder||0,h=Math.round((60*_*1e3-60*this.config.first_hour*60*1e3)*this.config.hour_size_px/36e5)%(24*this.config.hour_size_px),o=Math.max(scheduler.xy.min_event_height,(a-_)*this.config.hour_size_px/60),l=Math.floor((i.clientWidth-s)/r),n=d*l+1;if(e._inner||(l*=r-d),this.config.cascade_event_display){var c=this.config.cascade_event_count,u=this.config.cascade_event_margin;n=d%c*u;var f=e._inner?(r-d-1)%c*u/2:0;l=Math.floor(i.clientWidth-s-n-f)}var v=this._render_v_bar(e.id,s+n,h,l,o,e._text_style,scheduler.templates.event_header(e.start_date,e.end_date,e),scheduler.templates.event_text(e.start_date,e.end_date,e));if(this._rendered.push(v),i.appendChild(v),n=n+parseInt(i.style.left,10)+s,this._edit_id==e.id){v.style.zIndex=1,l=Math.max(l-4,scheduler.xy.editor_width),v=document.createElement("DIV"),v.setAttribute("event_id",e.id),this.set_xy(v,l,o-20,n,h+14),v.className="dhx_cal_editor";var g=document.createElement("DIV");this.set_xy(g,l-6,o-26),g.style.cssText+=";margin:2px 2px 2px 2px;overflow:hidden;",v.appendChild(g),this._els.dhx_cal_data[0].appendChild(v),this._rendered.push(v),g.innerHTML="<textarea class='dhx_cal_editor'>"+e.text+"</textarea>",this._quirks7&&(g.firstChild.style.height=o-12+"px"),this._editor=g.firstChild,this._editor.onkeydown=function(e){if((e||event).shiftKey)return!0;var t=(e||event).keyCode;t==scheduler.keys.edit_save&&scheduler.editStop(!0),t==scheduler.keys.edit_cancel&&scheduler.editStop(!1)},this._editor.onselectstart=function(e){return(e||event).cancelBubble=!0},scheduler._focus(g.firstChild,!0),this._els.dhx_cal_data[0].scrollLeft=0}if(0!==this.xy.menu_width&&this._select_id==e.id){this.config.cascade_event_display&&this._drag_mode&&(v.style.zIndex=1);for(var p=this.config["icons_"+(this._edit_id==e.id?"edit":"select")],m="",y=e.color?"background-color: "+e.color+";":"",x=e.textColor?"color: "+e.textColor+";":"",w=0;w<p.length;w++)m+="<div class='dhx_menu_icon "+p[w]+"' style='"+y+x+"' title='"+this.locale.labels[p[w]]+"'></div>";var z=this._render_v_bar(e.id,n-t+1,h,t,20*p.length+26-2,"","<div style='"+y+x+"' class='dhx_menu_head'></div>",m,!0);z.style.left=n-t+1,this._els.dhx_cal_data[0].appendChild(z),this._rendered.push(z)}}}},scheduler._prepare_timespan_options=function(e){var t=[],s=[];if("fullweek"==e.days&&(e.days=[0,1,2,3,4,5,6]),e.days instanceof Array){for(var i=e.days.slice(),_=0;_<i.length;_++){var a=scheduler._lame_clone(e);a.days=i[_],t.push.apply(t,scheduler._prepare_timespan_options(a))}return t}if(!e||!(e.start_date&&e.end_date&&e.end_date>e.start_date||void 0!==e.days&&e.zones))return t;var r=0,d=1440;"fullday"==e.zones&&(e.zones=[r,d]),e.zones&&e.invert_zones&&(e.zones=scheduler.invertZones(e.zones)),e.id=scheduler.uid(),e.css=e.css||"",e.type=e.type||"default";var h=e.sections;if(h){for(var o in h)if(h.hasOwnProperty(o)){var l=h[o];l instanceof Array||(l=[l]);for(var _=0;_<l.length;_++){var n=scheduler._lame_copy({},e);n.sections={},n.sections[o]=l[_],s.push(n)}}}else s.push(e);for(var c=0;c<s.length;c++){var u=s[c],f=u.start_date,v=u.end_date;if(f&&v)for(var g=scheduler.date.date_part(new Date(f)),p=scheduler.date.add(g,1,"day");v>g;){var n=scheduler._lame_copy({},u);delete n.start_date,delete n.end_date,n.days=g.valueOf();var m=f>g?scheduler._get_zone_minutes(f):r;if(this.config.last_hour_48>2400){var y=v>p||v.getDate()!=g.getDate()?d+scheduler._get_zone_minutes(v):scheduler._get_zone_minutes(v),x=f.getHours();x<this.config.first_hour&&(m+=1440,y+=1440,n.days-=864e5),n.zones=[m,y],t.push(n),g=p,p=scheduler.date.add(p,1,"day");break}var y=v>p||v.getDate()!=g.getDate()?d:scheduler._get_zone_minutes(v);n.zones=[m,y],t.push(n),g=p,p=scheduler.date.add(p,1,"day")}else u.days instanceof Date&&(u.days=scheduler.date.date_part(u.days).valueOf()),u.zones=e.zones.slice(),t.push(u)}return t};
EOT2;
		}
	}

	static function abnormal_error($msg) {
		echo '<div id="sl_error_display"><h2>'.$msg.'</h2></div>';
	}


	static function echoColumnCheck($check_patern) {

		echo <<<EOT
			function checkColumnItem(td) {
				var input = \$j(td).find("input");
				var val = input.val();
				var cl = \$j(td).attr("class");
				var item_errors = Array();
EOT;

				$check_contents = self::setCheckContents('td');
				$key = array_search('chk_required',$check_patern);
				if ($key !== false) {
					echo $check_contents['chk_required'];
					unset($check_patern[$key]);
				}
				if ( count($check_patern) > 0 ) {
					echo 'if (( item_errors.length == 0 ) && (val != "" ) ){';
					foreach ($check_patern as $d1) {
						echo $check_contents[$d1];
					}
					echo '}';
				}

		echo <<<EOT2
				if ( item_errors.length > 0 ) {
					alert(item_errors.join(" "));
					return false;
				}

				return true;
EOT2;
		echo '}';
	}




	static function setItemContents() {
		$item_contents = array();
		$item_contents['first_name'] =array('id' => 'first_name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax10')
		 ,'label' => __('first name',SL_DOMAIN)
		 ,'tips' => __('within 10 charactors',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable chk_required lenmax10'
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));


		$item_contents['last_name'] =array('id'=>'last_name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax10')
		 ,'label' => __('Last Name',SL_DOMAIN)
		 ,'tips' => __('within 10 charactors',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable chk_required lenmax10'
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['branch_name'] =array('id'=>'name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax20')
		 ,'label' => __('Salon Name',SL_DOMAIN)
		 ,'tips' => __('within 10 charctors',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable chk_required lenmax20'
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['item_name'] =array('id'=>'name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax30')
		 ,'label' => __('Menu Name',SL_DOMAIN)
		 ,'tips' => __('within 30 charctors',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable chk_required lenmax30'
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['position_name'] =array('id'=>'name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax30')
		 ,'label' => __('Position Name',SL_DOMAIN)
		 ,'tips' => __('within 30 charctors',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['short_name'] =array('id'=>'short_name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax5')
		 ,'label' => __('Short Name',SL_DOMAIN)
		 ,'tips' => __('within 5 charctors',SL_DOMAIN));

		$item_contents['customer_name'] =array('id'=>'name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax30','chkSpace')
		 ,'label' => __('Name',SL_DOMAIN)
		 ,'place' => __('John Smith',SL_DOMAIN)
		 ,'tips' => __('space input between first-name and last-name',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable'
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));



		$item_contents['staff_name_aft'] =array('id'=>'staff_name_aft'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Staff',SL_DOMAIN)
		 ,'tips' => ''
		 ,'table' => array(  'class'=>''
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['staff_name'] =array('id'=>'name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax30','chkSpace')
		 ,'label' => __('Name',SL_DOMAIN)
		 ,'tips' => __('space input between first-name and last-name',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable'
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['branch_cd'] =array('id'=>'branch_cd'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('Shop',SL_DOMAIN)
		 ,'tips' => __('select shop',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'false'
							,'search'=>'false'
							,'visible'=>'true' ));

		$item_contents['branch_name_table'] =array('id'=>'branch_name'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => ''
		 ,'tips' => ''
		 ,'table' => array(  'class'=>''
							,'width'=>''
							,'sort'=>'false'
							,'search'=>'true'
							,'visible'=>'false' ));

		$item_contents['position_cd'] =array('id'=>'position_cd'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('Position',SL_DOMAIN)
		 ,'tips' => __('when unknown, choose staff',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'false'
							,'search'=>'false'
							,'visible'=>'true' ));


		$item_contents['position_name_only_table'] =array('id'=>'position_name'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Position',SL_DOMAIN)
		 ,'tips' => ''
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'false'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['position_name_table'] =array('id'=>'position_name'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => ''
		 ,'tips' => ''
		 ,'table' => array(  'class'=>''
							,'width'=>''
							,'sort'=>'false'
							,'search'=>'true'
							,'visible'=>'false' ));

		$item_contents['wp_role'] =array('id'=>'wp_role'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('wp_role',SL_DOMAIN)
		 ,'tips' => __('when unknown, choose author',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'false'
							,'search'=>'false'
							,'visible'=>'true' ));

		$item_contents['role'] =array('id'=>'role'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('Role',SL_DOMAIN)
		 ,'tips' => __('check permit process',SL_DOMAIN));

		$item_contents['no_edit_remark'] =array('id'=>'remark'
		 ,'class' => array()
		 ,'check' => array( 'lenmax300')
		 ,'label' => __('Remark',SL_DOMAIN)
		 ,'tips' => __('within 300 charctors',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>''
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));


		$item_contents['staff_cd'] =array('id'=>'staff_cd'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('Assistant',SL_DOMAIN)
		 ,'tips' => __('select please',SL_DOMAIN));

		$item_contents['zip'] =array('id'=>'zip'
		 ,'class' => array()
		 ,'check' => array( 'chkZip')
		 ,'label' => __('Zip',SL_DOMAIN)
		 ,'tips' => __('please XXXXX-XXXX format',SL_DOMAIN));

		$item_contents['address'] =array('id'=>'address'
		 ,'class' => array()
		 ,'check' => array( 'lenmax200')
		 ,'label' => __('Address',SL_DOMAIN)
		 ,'tips' => __('within 200 charctors',SL_DOMAIN));

		$item_contents['tel'] =array('id'=>'tel'
		 ,'class' => array()
		 ,'check' => array( 'chkTel','reqOther_mobile')
		 ,'label' => __('Tel',SL_DOMAIN)
		 ,'tips' => __('within 15 charctors',SL_DOMAIN));

		$item_contents['branch_tel'] =array('id'=>'tel'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','chkTel')
		 ,'label' => __('Tel',SL_DOMAIN)
		 ,'tips' => __('within 15 charctors',SL_DOMAIN));

		$item_contents['customer_tel'] =array('id'=>'tel'
		 ,'class' => array()
		 ,'check' => array( 'chkTel','reqOther_mobile_mail')
		 ,'label' => __('Tel',SL_DOMAIN)
		 ,'tips' => __('within 15 charctors',SL_DOMAIN));

		$item_contents['mobile'] =array('id'=>'mobile'
		 ,'class' => array()
		 ,'check' => array( 'chkTel','reqOther_tel')
		 ,'label' => __('Mobile',SL_DOMAIN)
		 ,'tips' => __('within 15 charctors',SL_DOMAIN));

		$item_contents['customer_mobile'] =array('id'=>'mobile'
		 ,'class' => array()
		 ,'check' => array( 'chkTel','reqOther_tel_mail')
		 ,'label' => __('Mobile',SL_DOMAIN)
		 ,'tips' => __('within 15 charctors',SL_DOMAIN));

		$item_contents['mail'] =array('id'=>'mail'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','chkMail')
		 ,'label' => __('Mail',SL_DOMAIN)
		 ,'tips' => __('please XXX@XXX.XXX format',SL_DOMAIN));

		$item_contents['booking_tel'] =array('id'=>'tel'
		 ,'class' => array()
//		 ,'check' => array( 'chkTel','reqOther_mail')
		 ,'check' => array( 'chkTel')
		,'label' => __('Tel',SL_DOMAIN)
		 ,'tips' => __('within 15 charctors',SL_DOMAIN));

		$item_contents['mail_norequired'] =array('id'=>'mail'
		 ,'class' => array()
//		 ,'check' => array( 'chkMail','reqOther_tel')
		 ,'check' => array( 'chkMail')
		,'label' => __('Mail',SL_DOMAIN)
		 ,'tips' => __('please XXX@XXX.XXX format',SL_DOMAIN));


		$item_contents['customer_mail'] =array('id'=>'mail'
		 ,'class' => array()
		 ,'check' => array('chkMail','reqOther_tel_mobile')
		 ,'label' => __('Mail',SL_DOMAIN)
		 ,'tips' => __('when no mail , nothing input',SL_DOMAIN));

		$item_contents['reserved_tel'] =array('id'=>'tel'
		 ,'class' => array()
		 ,'check' => array( 'chkTel','reqOther_mail')
		 ,'label' => __('Tel',SL_DOMAIN)
		 ,'tips' => __('within 15 charctors',SL_DOMAIN));

		$item_contents['reserved_mail'] =array('id'=>'mail'
		 ,'class' => array()
		 ,'check' => array( 'chkMail','reqOther_tel')
		 ,'label' => __('Mail',SL_DOMAIN)
		 ,'tips' => __('when no mail , nothing input',SL_DOMAIN));


		$item_contents['user_login'] =array('id'=>'user_login'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('User Login',SL_DOMAIN)
		 ,'tips' => __('when possible ,same mail',SL_DOMAIN));

		$item_contents['remark'] =array('id'=>'remark'
		 ,'class' => array()
		 ,'check' => array( 'lenmax300')
		 ,'label' => __('Remark',SL_DOMAIN)
		 ,'tips' => __('within 300 charctors',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable lenmax300'
							,'width'=>''
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));


		$item_contents['target_day'] =array('id'=>'target_day'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','chkDate')
		 ,'label' => __('Date',SL_DOMAIN)
		 ,'tips' => __('please MM/DD/YYYY or MMDDYYYY format',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
		$item_contents['target_day_mobile'] =array('id'=>'target_day'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => ""
		 ,'tips' => __('please MM/DD/YYYY or MMDDYYYY format',SL_DOMAIN));

		$item_contents['employed_day'] =array('id'=>'employed_day'
		 ,'class' => array()
		 ,'check' => array( 'chkDate')
		 ,'label' => __('Entering Day',SL_DOMAIN)
		 ,'tips' => __('please MM/DD/YYYY or MMDDYYYY format',SL_DOMAIN));

		$item_contents['leaved_day'] =array('id'=>'leaved_day'
		 ,'class' => array()
		 ,'check' => array( 'chkDate')
		 ,'label' => __('Leaving Day',SL_DOMAIN)
		 ,'tips' => __('please MM/DD/YYYY or MMDDYYYY format',SL_DOMAIN));

		$item_contents['open_time'] =array('id'=>'open_time'
		 ,'class'=>array('sl_short_width')
		 ,'check'=>array('chk_required','chkTime')
		 ,'label'=> __('Open Time',SL_DOMAIN)
		 ,'tips' => __('please HH:MM or HHMM format',SL_DOMAIN));

		$item_contents['close_time'] =array('id'=>'close_time'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','chkTime')
		 ,'label' => __('Close Time',SL_DOMAIN)
		 ,'tips' => __('please HH:MM or HHMM format',SL_DOMAIN));

		$item_contents['time_step'] =array('id'=>'time_step'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num','range1_360')
		 ,'label' => __('Unit of Time (minutes)',SL_DOMAIN)
		 ,'tips' => __('select please',SL_DOMAIN));

		$item_contents['minute'] =array('id'=>'minute'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num')
		 ,'label' => __('Required Time(minutes)',SL_DOMAIN)
		 ,'tips' => __('select please',SL_DOMAIN));
		 //{TODO]カンマを許す？
		$item_contents['price'] =array('id'=>'price'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','num')
		 ,'label' => __('Price',SL_DOMAIN)
		 ,'tips' => __('please enter numeric',SL_DOMAIN)
		 ,'table' => array(  'class'=>'sl_editable chk_required num'
							,'width'=>self::SHORT_WIDTH
							,'sort'=>'false'
							,'search'=>'false'
							,'visible'=>'true' ));


		$item_contents['sp_date'] =array('id'=>'sp_date'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','chkDate')
		 ,'label' => __('Irregular Open/Closing day',SL_DOMAIN)
		 ,'tips' => __('holiday but does bussiness',SL_DOMAIN));

		$item_contents['closed_day_check'] =array('id'=>'closed_day_check'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Regular Closing Day',SL_DOMAIN)
		 ,'tips' => __('check please',SL_DOMAIN));

		$item_contents['item_cds'] =array('id'=>'item_cds'
		 ,'class' => array()
		 ,'check' => array( 'reqCheckbox')
		 ,'label' => __('Menu',SL_DOMAIN)
		 ,'tips' => __('please select',SL_DOMAIN));

		$item_contents['item_cds_set'] =$item_contents['item_cds'];
		$item_contents['item_cds_set']['tips'] = __('Check the menu which this staff member can treat',SL_DOMAIN);



// 		$item_contents['config_branch'] =array('id'=>'config_only_branch'
// 		 ,'class'	=>array()
// 		 ,'check' => array()
// 		 ,'label' => '1.'.__('Number of the Shops',SL_DOMAIN)
// 		 ,'tips' => __('Now plural shops select only.',SL_DOMAIN));

		$item_contents['config_user_login'] =array('id'=>'config_is_user_login'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => '1.'.__('Approval of the Login by the Clients',SL_DOMAIN)
		 ,'tips' => __('if customer login possible ,check here',SL_DOMAIN));

		$item_contents['config_log'] =array('id'=>'config_is_log_need'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => '2.'.__('Opration Log Setting',SL_DOMAIN)
		 ,'tips' => __('if write operation  to log ,check here',SL_DOMAIN));

		$item_contents['config_delete_record'] =array('id'=>'config_is_delete_record'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => '3.'.__('Automatic Deletion',SL_DOMAIN)
		 ,'tips' => __('if mask the personal information ,check here',SL_DOMAIN));

		$item_contents['config_delete_record_period'] =array('id'=>'delete_record_period'
		 ,'class'	=>array('sl_short_width')
		 ,'check' => array('num')
		 ,'label' => '4.'.__('Months when to delete ',SL_DOMAIN)
		 ,'tips' => __('enter the designated months ',SL_DOMAIN));

		$item_contents['config_show_detail_msg'] =array('id'=>'config_is_show_detail_msg'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => '5.'.__('Display Details at messages',SL_DOMAIN)
		 ,'tips' => __('when debgug, check here',SL_DOMAIN));

		$item_contents['regist_customer'] =array('id'=>'regist_customer'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Registered as a Member',SL_DOMAIN)
		 ,'tips' => __('when join as a member, check here',SL_DOMAIN));

		$item_contents['send_mail_text'] =array('id'=>'send_mail_text'
		 ,'class' => array()
		 ,'check' => array( 'lenmax300')
		 ,'label' => '17.'.__('The Content of the Mail to Confirming Notice to the Client',SL_DOMAIN)
		 ,'tips' => __('within 300 charctors. {X-TO_NAME} in the content replace customer name',SL_DOMAIN));

		$item_contents['send_mail_text_on_mail'] = $item_contents['send_mail_text'];
		$item_contents['send_mail_text_on_mail']['label'] = substr($item_contents['send_mail_text_on_mail']['label'],3);

		$item_contents['regist_mail_text'] =array('id'=>'regist_mail_text'
		 ,'class' => array()
		 ,'check' => array( 'lenmax300')
		 ,'label' => '18.'.__('The Content of the Mail to respond to the Client newly registered as a Member',SL_DOMAIN)
		 ,'tips' => __('within 300 charctors.  {X-TO_NAME} in the content replace customer name',SL_DOMAIN));

		$item_contents['regist_mail_text_on_mail'] = $item_contents['regist_mail_text'];
		$item_contents['regist_mail_text_on_mail']['label'] = substr($item_contents['regist_mail_text_on_mail']['label'],3);
		//[2014/11/01]Ver1.5.1
		$item_contents['information_mail_text_on_mail'] =array('id'=>'information_mail_text'
		 ,'class' => array()
		 ,'check' => array( 'lenmax300')
		 ,'label' => __('The Content of the Mail to staff member new reservations',SL_DOMAIN)
		 ,'tips' => __('within 300 charctors.  {X-TO_NAME} in the content replace customer name',SL_DOMAIN));


		$item_contents['send_mail_text_completed_on_mail'] =$item_contents['send_mail_text_on_mail'] ;
		$item_contents['send_mail_text_accepted_on_mail'] =$item_contents['send_mail_text_on_mail'] ;
		$item_contents['send_mail_text_canceled_on_mail'] =$item_contents['send_mail_text_on_mail'] ;

		$item_contents['send_mail_text_completed_on_mail']['id'] = 'sl_send_mail_text_completed';
		$item_contents['send_mail_text_accepted_on_mail']['id'] = 'sl_send_mail_text_accepted';
		$item_contents['send_mail_text_canceled_on_mail']['id'] = 'sl_send_mail_text_canceled';

		$item_contents['config_staff_holiday_set'] =array('id'=>'config_staff_holiday_normal'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => '6.'.__('Staff Holiday Settings',SL_DOMAIN)
		 ,'tips' => __('you may select “unable to enter other than when attendant”if you could register your attendance and the absence correctly in advance',SL_DOMAIN));

		$item_contents['config_name_order_set'] =array('id'=>'config_name_order_japan'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => '7.'.__('Sequence of Sur Name and Given Name',SL_DOMAIN)
		 ,'tips' => __('please select Sur Name First or Given Name first',SL_DOMAIN));

		$item_contents['config_no_prefernce'] =array('id'=>'config_is_no_preference'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => '8.'.__('No Designation of Staff',SL_DOMAIN)
		 ,'tips' => __('if allow the reservation without nomination of a certain staff ,check here',SL_DOMAIN));

		$item_contents['maintenance_include_staff'] =array('id'=>'config_maintenance_include_staff'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => '9.'.__('Maintenance staff member include staff',SL_DOMAIN)
		 ,'tips' => __('if maintenance staff member display front form  ,check here',SL_DOMAIN));


		$item_contents['booking_user_login'] =array('id'=>'login_username'
		 ,'class'	=>array()
		 ,'check' => array('chk_required')
		 ,'label' => __('User Login',SL_DOMAIN)
		 ,'tips' => '');

		$item_contents['booking_user_password'] =array('id'=>'login_password'
		 ,'class'	=>array()
		 ,'check' => array('chk_required')
		 ,'label' => __('Password',SL_DOMAIN)
		 ,'tips' => '');

		$item_contents['time_from'] =array('id'=>'start_date'
		 ,'class'	=>array()
		 ,'check' => array('chk_required')
		 ,'label' => __('Start time',SL_DOMAIN)
		 ,'tips' => '');

		$item_contents['time_to'] =array('id'=>'end_date'
		 ,'class'	=>array()
		 ,'check' => array('chk_required')
		 ,'label' => __('End time',SL_DOMAIN)
		 ,'tips' => '');

		$item_contents['start_time'] =array('id'=>'start_time'
		 ,'class'	=>array()
		 ,'check' => array('chk_required')
		 ,'label' => __('Reserved time',SL_DOMAIN)
		 ,'tips' => __('please select',SL_DOMAIN));

		$item_contents['reserved_time'] =array('id'=>'reserved_time'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Date',SL_DOMAIN)
		 ,'tips' => ''
		 ,'table' => array(  'class'=>''
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));


		$item_contents['in_time'] =array('id'=>'in_time'
		 ,'class'	=>array('sl_middle_width')
		 ,'check' => array('chk_required')
		 ,'label' => __('In Time',SL_DOMAIN)
		 ,'tips' => '');

		$item_contents['out_time'] =array('id'=>'out_time'
		 ,'class'	=>array('sl_middle_width')
		 ,'check' => array('chk_required')
		 ,'label' => __('Out Time',SL_DOMAIN)
		 ,'tips' => '');

		$item_contents['working_cds'] =array('id'=>'working_cds'
		 ,'class' => array()
		 ,'check' => array( 'reqCheckbox')
		 ,'label' => __('Working Status',SL_DOMAIN)
		 ,'tips' => '');

		$item_contents['duplicate_cnt_staff'] =array('id'=>'duplicate_cnt'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num')
		 ,'label' => __('The Maximum Number of the Redundant Reservations',SL_DOMAIN)
		 ,'tips' => __('redundant reservations a staff can handle at the same timeframe',SL_DOMAIN));

		$item_contents['duplicate_cnt'] =array('id'=>'duplicate_cnt'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num')
		 ,'label' => __('The Maximum Number of the Redundant Reservations',SL_DOMAIN)
		 ,'tips' => __('the maximum number of the reservations to coop simultaneously',SL_DOMAIN));


		$item_contents['before_day'] =array('id'=>'before_day'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num')
		 ,'label' => '11.'.__('past X days',SL_DOMAIN)
		 ,'tips' => __('the range of th days extarcted from the data base of the actual performance',SL_DOMAIN));

		$item_contents['after_day'] =array('id'=>'after_day'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num')
		 ,'label' => '12.'.__('X days ahead',SL_DOMAIN)
		 ,'tips' => __('the range of th days extarcted from the data base of the reservation',SL_DOMAIN));


		$item_contents['timeline_y_cnt'] =array('id'=>'timeline_y_cnt'
		 ,'class' => array('sl_short_width')
		 ,'check' => array( 'chk_required','num')
		 ,'label' => '13.'.__('Number of the staff displayed',SL_DOMAIN)
		 ,'tips' => __('screen showing staff for the reservation screen',SL_DOMAIN));

		$item_contents['logged_day'] =array('id'=>'logged_day'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Date',SL_DOMAIN)
		 ,'tips' => __('Logged date ',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['logged_time'] =array('id'=>'logged_time'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Time',SL_DOMAIN)
		 ,'tips' => __('Logged time',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['operation'] =array('id'=>'operation'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Operation',SL_DOMAIN)
		 ,'tips' => __('the operation to tables',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['logged_remark'] =array('id'=>'remark'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Remark',SL_DOMAIN)
		 ,'tips' => __('REMOTE_ADDR,REFERER',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
		//[20131110]Ver1.3.1
		$item_contents['display_sequence'] =array('id'=>'display_sequence'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Seq',SL_DOMAIN)
		 ,'tips' => ''
		 ,'table' => array(  'class'=>'salon_editable '
							,'width'=>'10px'
							,'sort'=>'false'
							,'search'=>'false'
							,'visible'=>'true' ));


		//[20140412]Ver1.3.6
		$item_contents['mail_from'] =array('id'=>'mail_from'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => '14.'.__('Mail from',SL_DOMAIN)
		 ,'tips' => 'Name<XXX@XXX.XXX>');


		$item_contents['mail_from_on_mail'] = $item_contents['mail_from'];
		$item_contents['mail_from_on_mail']['label'] = substr($item_contents['mail_from_on_mail']['label'],3);


		$item_contents['mail_returnPath'] =array('id'=>'mail_returnPath'
		 ,'class' => array()
		 ,'check' => array( 'chkMail')
		 ,'label' => '15.'.__('Mail return path',SL_DOMAIN)
		 ,'tips' => __('please XXX@XXX.XXX format',SL_DOMAIN));

		$item_contents['mail_returnPath_on_mail'] = $item_contents['mail_returnPath'];
		$item_contents['mail_returnPath_on_mail']['label'] = substr($item_contents['mail_returnPath_on_mail']['label'],3);

		//[20140416]Ver1.3.7
		$item_contents['mobile_search_day'] =array('id'=>'slm_searchdate'
		 ,'class' => array()
		 ,'check' => array( '')
		 ,'label' => __('Date',SL_DOMAIN)
		 ,'tips' => __('MM/DD/YYYY',SL_DOMAIN));

		$item_contents['mobile_tel'] =array('id'=>'tel'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','chkTel')
		 ,'label' => __('Tel',SL_DOMAIN)
		 ,'tips' => __('within 15 charctors',SL_DOMAIN));

		$item_contents['mobile_use'] =array('id'=>'config_mobile_use'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => '10.'.__('Mobile screen use',SL_DOMAIN)
		 ,'tips' => __('if use the screen of  mobiles and pc, check here',SL_DOMAIN));

		//[20140518]Ver1.3.8
		$item_contents['rstatus'] =array('id'=>'rstatus'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Status',SL_DOMAIN)
		 ,'tips' => ''
		 ,'table' => array(  'class'=>''
							,'width'=>'80px'
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));



		//[20140622]Ver1.4.1
		$item_contents['exp_from'] =array('id'=>'exp_from'
		 ,'class' => array()
		 ,'check' => array( 'chkDate')
		 ,'label' => __('Expiry date(from)',SL_DOMAIN)
		 ,'tips' => __('Please input Expiry date.If Expiry date(from) is not input,this menu is always valid.',SL_DOMAIN));

		$item_contents['exp_to'] =array('id'=>'exp_to'
		 ,'class' => array()
		 ,'check' => array( 'chkDate')
		 ,'label' => __('Expiry date(to)',SL_DOMAIN)
		 ,'tips' => __('Please input Expiry date.If Expiry date(to) is not input,this menu is always valid.',SL_DOMAIN));

		$item_contents['all_flg'] =array('id'=>'all_flg'
		 ,'class' => array()
		 ,'check' => array( )
		 ,'label' => __('All staff member can treat',SL_DOMAIN)
		 ,'tips' => __('If all staff member can treat this menu,check here.',SL_DOMAIN));

		//[20140714]Ver1.4.2
		$item_contents['load_tab'] =array('id'=>'config_load_staff'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => '14.'.__('Default load tab',SL_DOMAIN)
		 ,'tips' => __('Please select default load tab at the Reservation Screen.',SL_DOMAIN));


		//[2014/07/26]Ver1.4.5
		$item_contents['memo'] =array('id'=>'memo'
		 ,'class' => array()
		 ,'check' => array( 'lenmax300')
		 ,'label' => __('Introductions',SL_DOMAIN)
		 ,'tips' => __('Please input self-introductions.',SL_DOMAIN));

		//[2014/08/01]Ver1.4.6
		$item_contents['target_mail_patern'] =array('id'=>'target_mail_patern'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Select Mail',SL_DOMAIN)
		 ,'tips' => __('Please select.',SL_DOMAIN));

		$item_contents['send_mail_subject'] =array('id'=>'send_mail_subject'
		 ,'class' => array()
		 ,'check' => array('lenmax78')
		 ,'label' => __('The Subject of the Mail to Confirming Notice to the Client',SL_DOMAIN)
		 ,'tips' => __('within 78 charctors.',SL_DOMAIN));

		$item_contents['send_mail_subject_completed'] =$item_contents['send_mail_subject'] ;
		$item_contents['send_mail_subject_accepted'] =$item_contents['send_mail_subject'] ;
		$item_contents['send_mail_subject_canceled'] =$item_contents['send_mail_subject'] ;

		$item_contents['send_mail_subject_completed']['id'] = 'sl_send_mail_subject_completed';
		$item_contents['send_mail_subject_accepted']['id'] = 'sl_send_mail_subject_accepted';
		$item_contents['send_mail_subject_canceled']['id'] = 'sl_send_mail_subject_canceled';

		$item_contents['send_mail_subject_completed']['label'] = __('Reservation complete mail',SL_DOMAIN);
		$item_contents['send_mail_subject_accepted']['label'] = __('Reservation receipt mail',SL_DOMAIN);;
		$item_contents['send_mail_subject_canceled']['label'] = __('Reservation canceled mail',SL_DOMAIN);

		$item_contents['regist_mail_subject'] =array('id'=>'regist_mail_subject'
		 ,'class' => array()
		 ,'check' => array('lenmax78')
		 ,'label' => __('The Subject of the Mail to respond to the Client newly registered as a Member',SL_DOMAIN)
		 ,'tips' => __('within 78 charctors.',SL_DOMAIN));
		//[2014/11/01]Ver1.5.1
		$item_contents['information_mail_subject'] =array('id'=>'information_mail_subject'
		 ,'class' => array()
		 ,'check' => array('lenmax78')
		 ,'label' => __('The Subject of the Mail to staff member new reservations',SL_DOMAIN)
		 ,'tips' => __('within 78 charctors.',SL_DOMAIN));
		$item_contents['mail_bcc'] =array('id'=>'mail_bcc'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('Mail address (Bcc)',SL_DOMAIN)
		 ,'tips' => __('If you need the information of reservations,enter mail address separated by commas',SL_DOMAIN));

		//[20140714]Ver1.4.7
		$item_contents['reserve_deadline'] =array('id'=>'reserve_deadline'
		 ,'class'	=>array()
		 ,'check' => array("num")
		 ,'label' => '15.'.__('Deadline of reservations',SL_DOMAIN)
		 ,'tips' => __('How many days or hours is the deadline of reservation.',SL_DOMAIN));


		//[20140810]Ver1.4.8
		$item_contents['set_code'] =array('id'=>'set_code'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax10')
		 ,'label' => __('Code',SL_DOMAIN)
		 ,'tips' => __('within 10 charactors',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));

		$item_contents['description'] =array('id'=>'description'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax50')
		 ,'label' => __('Description',SL_DOMAIN)
		 ,'tips' => __('within 50 charactors',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
		$item_contents['valid_from'] =array('id'=>'valid_from'
		 ,'class' => array()
		 ,'check' => array( 'chkDate')
		 ,'label' => __('Valid from',SL_DOMAIN)
		 ,'tips' => __('Leave fields to blank to indicate that no limit applies.',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
		$item_contents['valid_to'] =array('id'=>'valid_to'
		 ,'class' => array()
		 ,'check' => array( 'chkDate')
		 ,'label' => __('Valid to',SL_DOMAIN)
		 ,'tips' => __('Leave fields to blank to indicate that no limit applies.',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
		$item_contents['discount_patern'] =array('id'=>'discount_wrap'
		 ,'class' => array()
		 ,'check' => array( )
		 ,'label' => __('Discount Patern',SL_DOMAIN)
		 ,'tips' => __('please select',SL_DOMAIN));
		$item_contents['discount'] =array('id'=>'discount'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','num')
		 ,'label' => __('Discount',SL_DOMAIN)
		 ,'tips' => __('please enter numeric',SL_DOMAIN));
		$item_contents['usable_patern'] =array('id'=>'usable_patern_cd'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('Usable',SL_DOMAIN)
		 ,'tips' => __('please select',SL_DOMAIN));
		$item_contents['times'] =array('id'=>'times'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('Times',SL_DOMAIN)
		 ,'tips' => __('The Maximum Number of this promotion.',SL_DOMAIN));
		$item_contents['rank_patern'] =array('id'=>'rank_patern_cd'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('Rank',SL_DOMAIN)
		 ,'tips' => __('please select',SL_DOMAIN));
		$item_contents['coupon'] =array('id'=>'coupon'
		 ,'class' => array()
		 ,'check' => array()
		 ,'label' => __('coupon',SL_DOMAIN)
		 ,'tips' => __('please select',SL_DOMAIN));

		$item_contents['record_time'] =array('id'=>'reserved_time'
		 ,'class' => array()
		 ,'check' => array( )
		 ,'label' => __('Reserved Time',SL_DOMAIN)
		 ,'tips' => ''
		 ,'table' => array(  'class'=>''
							,'width'=>self::LONG_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
		//[2014/10/30]Ver1.5.1
		$item_contents['category_name'] =array('id'=>'sl_category_name'
		 ,'class' => array()
		 ,'check' => array( 'chk_required','lenmax50')
		 ,'label' => __('Category name',SL_DOMAIN)
		 ,'tips' => __('within 50 charactors',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
		$item_contents['category_patern'] =array('id'=>'sl_category_patern'
		 ,'class' => array()
		 ,'check' => array( 'chk_required')
		 ,'label' => __('Category Patern',SL_DOMAIN)
		 ,'tips' => __('select please',SL_DOMAIN)
		 ,'table' => array(  'class'=>''
							,'width'=>self::MIDDLE_WIDTH
							,'sort'=>'true'
							,'search'=>'true'
							,'visible'=>'true' ));
		$item_contents['category_value'] =array('id'=>'sl_category_values'
		 ,'class' => array()
		 ,'check' => array('chk_required')
		 ,'label' => __('Category Value',SL_DOMAIN)
		 ,'tips' => __('Display values are separated by commas',SL_DOMAIN));

		$item_contents['target_table'] =array('id'=>'sl_target_table'
		 ,'class' => array()
		 ,'check' => array( '')
		 ,'label' => __('Select Target Table',SL_DOMAIN)
		 ,'tips' => __('Now only the information of record is available',SL_DOMAIN));

		$item_contents['show_tab'] =array('id'=>'config_show_staff'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => '16.'.__('Show tab',SL_DOMAIN)
		 ,'tips' => __('Please check show tab at the Reservation Screen.',SL_DOMAIN));

		$item_contents['config_use_session'] =array('id'=>'config_is_use_session'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => '17.'.__('Use session id.',SL_DOMAIN)
		 ,'tips' => __('If you get the message \"This request is invalid nonce\",uncheck this field',SL_DOMAIN));


		$item_contents['setting_patern_cd'] =array('id'=>'sl_setting_patern_cd'
		 ,'class'	=>array()
		 ,'check' => array('chk_required')
		 ,'label' => __('Setting the reservation time',SL_DOMAIN)
		 ,'tips' => __('\"Input time unit\" -> Setting for allowing the user to input a time.\"Input pre-determined time frames\" -> The user is able to select from time frames decided by the administrator. Selecting this item displays the following input selections. ',SL_DOMAIN));

		$item_contents['original_name'] =array('id'=>'sl_original_name'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Selection item name',SL_DOMAIN)
		 ,'tips' => __('The name of the item selected by the user. ',SL_DOMAIN));

		$item_contents['is_setting_patern'] =array('id'=>'sl_is_setting_patern'
		 ,'class'	=>array()
		 ,'check' => array()
		 ,'label' => __('Setting time of staff member',SL_DOMAIN)
		 ,'tips' => __('If staff member can set from-to time, check here. ',SL_DOMAIN));

		$item_contents['confirm_style'] =array('id'=>'sl_confirm_style'
		 ,'class'	=>array("sl_long_width_no_margin")
		 ,'check' => array("num")
		 ,'label' => '18.'.__('Reservation confirmation method',SL_DOMAIN)
		 ,'tips' => __('\"Confirmation by an administrator\":At the point that a user makes a reservation, this is treated as a temporary reservation. The reservation is confirmed when a person with administrator privileges updates the reservation. \"No confirm\":The reservation is confirmed when made by the user. \"Confirmation via user e-mail\":At the point that a user makes a reservation, this is treated as a temporary reservation. A link to the reservation confirmation screen is displayed in the e-mail sent to the user. The reservation is confirmed when the user uses the link to open the reservation confirmation page.',SL_DOMAIN));

		//[2017/03/03]Ver1.7.1
		$item_contents['menu_column'] =array('id'=>'config_menu_column1'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('Menu column',SL_DOMAIN)
				,'tips' => __('Please select menu column at the Reservation Screen.',SL_DOMAIN));
		$item_contents['before_title'] =array('id'=>'config_before_title'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('Display the comment on top of booking',SL_DOMAIN)
				,'tips' => __('If you want to display comment above the calendar on booking screen, set this.',SL_DOMAIN));
		$item_contents['holiday_display'] =array('id'=>'config_holiday_display'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('Display holiday charctor',SL_DOMAIN)
				,'tips' => __('If you need to replace charctors in the box of holiday, set this.',SL_DOMAIN));
		$item_contents['menu_type'] =array('id'=>'config_menu_type_checkbox'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('Menu type',SL_DOMAIN)
				,'tips' => __('If you need to multiple choice allowed,check \"Check Box\"',SL_DOMAIN));
		$item_contents['doSort'] =array('id'=>'config_is_doSort'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('sort order of staff members',SL_DOMAIN)
				,'tips' => __('If you need to sort staff members automatically, check this.',SL_DOMAIN));
		$item_contents['color_pc_back'] =array('id'=>'config_pc_back_color'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('PC back color',SL_DOMAIN)
				,'tips' => __('Back color on \"Reservtion\"',SL_DOMAIN));
		$item_contents['color_pc_event'] =array('id'=>'config_pc_event_color'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('PC event color',SL_DOMAIN)
				,'tips' => __('Reseved box color on \"Reservtion\"',SL_DOMAIN));
		$item_contents['color_pc_event_line'] =array('id'=>'config_pc_event_line_color'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('PC event line color',SL_DOMAIN)
				,'tips' => __('Reseved box line color on \"Reservtion\"',SL_DOMAIN));
		$item_contents['color_pc_selected_back'] =array('id'=>'config_pc_selected_back_color'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('PC Todays color',SL_DOMAIN)
				,'tips' => __('Todays color on \"Month tab of reservtion\"',SL_DOMAIN));
		$item_contents['color_pc_unselected_back'] =array('id'=>'config_pc_unselected_back_color'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('PC Ohter month color',SL_DOMAIN)
				,'tips' => __('Days of other month color on \"Month tab of reservtion\"',SL_DOMAIN));
		$item_contents['color_pc_holiday'] =array('id'=>'config_pc_holiday_color'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('PC Holiday color',SL_DOMAIN)
				,'tips' => __('Holiday color on \"Reservtion\"',SL_DOMAIN));
		$item_contents['color_pc_onbusiness'] =array('id'=>'config_pc_onbusiness_color'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('PC On business color',SL_DOMAIN)
				,'tips' => __('\"On business\" color on \"Reservtion\"',SL_DOMAIN));
		$item_contents['color_pc_focus'] =array('id'=>'config_pc_focus_color'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('PC focus field color',SL_DOMAIN)
				,'tips' => __('focus field color on \"Reservtion\"',SL_DOMAIN));
		$item_contents['special_onbusiness_display'] =array('id'=>'config_special_onbusiness_display'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('Display \"On business\" charctor',SL_DOMAIN)
				,'tips' => __('If you need to replace charctors in the box of the shop on business day, set this.',SL_DOMAIN));
		$item_contents['onbusiness_display'] =array('id'=>'config_onbusiness_display'
				,'class'	=>array()
				,'check' => array()
				,'label' => __('Display \"Booking\"',SL_DOMAIN)
				,'tips' => __('If you need to replace charctors in the box that the staff registers as attendant, set this.',SL_DOMAIN));

		$item_contents = apply_filters('salon_booking_set_check_item', $item_contents);
		return $item_contents;


	}

	public function serverCheck($items , &$msg ) {

		$nonce = SL_PLUGIN_DIR;
		if ($this->config_datas['SALON_CONFIG_USE_SESSION_ID'] == Salon_Config::USE_SESSION) $nonce = session_id();
		if ( empty($_REQUEST['nonce'])) {
			throw new Exception(Salon_Component::getMsg('E901',__function__.':'.__LINE__ ),1 );
		}
		if (wp_verify_nonce($_REQUEST['nonce'],$nonce) === false) {
			throw new Exception(Salon_Component::getMsg('E013',__function__.':'.__LINE__ ) ,1);
		}
		if (count($items) == 0 ) return true;
		if  ($_POST['type'] == 'deleted' ) return true;
		$item_contents = self::setItemContents();
		$err_msg = array();
		foreach ($items as $d1) {
			$id = $item_contents[$d1]['id'];
			foreach ($item_contents[$d1]['check'] as $d2 ) {
				//[2014/11/01]Ver1.5.1
				$key = "";
				if (array_key_exists($id,$_POST) ) {
					$key = $_POST[$id];
					self::serverEachCheck($key,trim($d2),$item_contents[$d1]['label'],$err_msg);
				}
				else {
					$exceptId = str_replace("sl_","",$id);
					if (array_key_exists($exceptId,$_POST) ) {
						$key = $_POST[$exceptId];
						self::serverEachCheck($key,trim($d2),$item_contents[$d1]['label'],$err_msg);
					}
					else {
						throw new Exception( Salon_Component::getMsg('E901',$item_contents[$d1]['label']),2);
					}
				}

			}
		}
		if (count($err_msg) > 0 ) {
			$msg = implode("\n",$err_msg);
			return false;
		}
		return true;
	}

	public function serverCheckBooking($items , &$msg , &$field) {

		$nonce = SL_PLUGIN_DIR;
		$field = "";

		if ($this->config_datas['SALON_CONFIG_USE_SESSION_ID'] == Salon_Config::USE_SESSION) $nonce = session_id();
		if (empty($_REQUEST['nonce'])) {
			throw new Exception(Salon_Component::getMsg('E901',__function__.':'.__LINE__ ),1 );
		}
		if (wp_verify_nonce($_REQUEST['nonce'],$nonce) === false) {
			throw new Exception(Salon_Component::getMsg('E013',__function__.':'.__LINE__ ) ,1);
		}
		if (count($items) == 0 ) return true;
		if  ($_POST['type'] == 'deleted' ) return true;
		$item_contents = self::setItemContents();
		$err_msg = array();
		foreach ($items as $d1) {
			$id = $item_contents[$d1]['id'];
			foreach ($item_contents[$d1]['check'] as $d2 ) {
				//[2014/11/01]Ver1.5.1
				$key = "";
				$before = count($err_msg);
				if (array_key_exists($id,$_POST) ) {
					$key = $_POST[$id];
					self::serverEachCheck($key,trim($d2),$item_contents[$d1]['label'],$err_msg);
				}
				else {
					$exceptId = str_replace("sl_","",$id);
					if (array_key_exists($exceptId,$_POST) ) {
						$key = $_POST[$exceptId];
						self::serverEachCheck($key,trim($d2),$item_contents[$d1]['label'],$err_msg);
					}
					else {
						throw new Exception( Salon_Component::getMsg('E901',$item_contents[$d1]['label']),2);
					}
				}
				if ($field == "" && $before != count($err_msg)) {
					$field = $id;
				}
			}
		}
		if (count($err_msg) > 0 ) {
			$msg = implode("\n",$err_msg);
			return false;
		}
		return true;
	}

	static function serverColumnCheck($target,$check_item,&$msg) {
		$item_contents = self::setItemContents();
		$err_msg = array();
		foreach ($item_contents[trim($check_item)]['check'] as $d1 ) {
			self::serverEachCheck($target,$d1,$item_contents[trim($check_item)]['label'],$err_msg);
		}
		if (count($err_msg) > 0 ) {
			$msg = implode("\n",$err_msg);
			return false;
		}
		return true;

	}

	static function serverEachCheck($target,$check,$label,&$err_msg){
		if (trim($check) == 'chk_required') {
			if ( '' == strval($target)) {
				$err_msg[] = Salon_Component::getMsg('E201',$label);
				return false;
			}
		}
		if (trim($check) == 'reqCheckbox' ) {
			if ( '' == strval($target)) {
				$err_msg[] = Salon_Component::getMsg('E201',$label);
				return false;
			}
		}
		else {
			if (empty($target) ) return;
			switch (trim($check)) {
				case 'chkTime':
					if (preg_match('/^(?:\d{1,2}:\d{1,2})$|^(?:\d{4})$/', $target, $matches) == 0 ) {
						$err_msg[] = Salon_Component::getMsg('E202',$label);
					}
					if ( +substr($target,0,2) > 47 ) {
						$err_msg[] = Salon_Component::getMsg('E202',$label);
					}
					break;
				case 'num':
					if (preg_match('/^\d*$/',$target,$matches) == 0 ) {
						$err_msg[] = Salon_Component::getMsg('E203',$label);
					}
					break;
//				case 'hankaku':
//					if (preg_match('/^[\s\w]+$/',$target,$matches) == 0 ) {
//						$err_msg[] = Salon_Component::getMsg('E204',$label);
//					}
//					break;
				case 'chkZip':
					if (preg_match('/'.__('^\d{5}(?:[-\s]\d{4})?$',SL_DOMAIN).'/',$target,$matches) == 0 ) {
						$err_msg[] = Salon_Component::getMsg('E205',$label);
					}
					break;

				case 'chkTel':
					if (preg_match('/^[\d\-]{1,15}$/',$target,$matches) == 0 ) {
						$err_msg[] = Salon_Component::getMsg('E206',$label);
					}
					break;
				case 'chkMail':
					if (preg_match('/^[\w!#$%&\'*+\/=?^_{}\\|~-]+([\w!#$%&\'*+\/=?^_{}\\|~\.-]+)*@([\w][\w-]*\.)+[\w][\w-]*$/',$target,$matches) == 0 ) {
						$err_msg[] = Salon_Component::getMsg('E207',$label);
					}

					break;

				case 'chkDate':
					if ((preg_match('/^'.__('(?<month>\d{1,2})[\/\.\-](?<day>\d{1,2})[\/\.\-](?<year>\d{4})',SL_DOMAIN).'$/',$target,$matches) == 0 ) &&
					   (preg_match('/^'.__('(?<month>\d{2})(?<day>\d{2})(?<year>\d{4})',SL_DOMAIN).'$/',$target,$matches) == 0 ) ){
						$err_msg[] = Salon_Component::getMsg('E208',$label);
					}
					elseif ( checkdate(+$matches['month'],+$matches['day'],+$matches['year']) == false ) {
						$err_msg[] = Salon_Component::getMsg('E209',$label);
					}
					break;
				case 'chkSpace':
					$tmp = str_replace("　"," ",$target);
					if (preg_match('/^.+\s+.+$/', $tmp, $matches) == 0 ) {
						$err_msg[] = Salon_Component::getMsg('E210',$label);
					}
					break;
				default:
					if (preg_match('/^lenmax(?<length>\d+)$/',trim($check),$matches) === 1 ) {
						$tmp_length = 0;
						if ( function_exists( 'mb_strlen' ) )  {
							$tmp_length = mb_strlen($target);
						}
						else {
							$tmp_length = strlen($target);
						}
						if ( $tmp_length > +$matches['length'] ) {
							$err_msg[] = Salon_Component::getMsg('E211',array(+$matches['length'],$label));
						}
					}
			}

		}
	}

	static function setCheckContents($target = 'this') {
		//valにはチェックする値を、clには対象のクラスを全部格納しとく
		//当初はdetail部分だけに使用していたが、datatableでも使用するため拡張
		$check_contens = array();


		$check_contens['chk_required'] = '
						if ($j('.$target.').hasClass("chk_required") ) {
							if(val == "" || val === null){
								item_errors.push( "'.__('please enter',SL_DOMAIN).'");
							}
						}';
		$check_contens['num'] = '
							if ($j('.$target.').hasClass("num") ) {
								if( ! val.match(/^\d*$/)  ){
									item_errors.push( "'.__('please enter numeric',SL_DOMAIN).'");
								}
							}';
		//全角チェックはいらない
		$check_contens['zenkaku'] = '
							if ($j('.$target.').hasClass("zenkaku") ) {
								if( ! val.match(/^[^ -~｡-ﾟ]*$/)  ){
									item_errors.push( "'.__('please full width enter',SL_DOMAIN).'");
								}
							}';
//		$check_contens['hankaku'] = '
//							if ($j('.$target.').hasClass("hankaku") ) {
//								if( ! val.match(/^[ -~｡-ﾟ]*$/)  ){
//									item_errors.push( "'.__('please half width enter',SL_DOMAIN).'");
//								}
//							}';
		$check_contens['chkZip'] = '
							if ($j('.$target.').hasClass("chkZip") ) {
								if( ! val.match(/'.__('^\d{5}(?:[-\s]\d{4})?$',SL_DOMAIN).'/) ){
									item_errors.push( "'.__('please XXXXX-XXXX format',SL_DOMAIN).'");
								}
							}';
//								if( ! val.match(/^(?:\d{3}\-\d{4})$|^(?:\d{7})$/) ){
		//パターンで例外を考慮すると複雑になるので単純に
		//数字だけだと見えにくいのでハイフンを入れる
		$check_contens['chkTel'] = '
							if ($j('.$target.').hasClass("chkTel") ) {
								if( ! val.match(/^[\d\-]{1,15}$/) ){
									item_errors.push( "'.__('within 15 charctors',SL_DOMAIN).'");
								}
							}';
		$check_contens['chkMail'] = '
							if ($j('.$target.').hasClass("chkMail") ) {
								if( ! val.match(/^[\w!#$%&\'*+/=?^_{}\\|~-]+([\w!#$%&\'*+/=?^_{}\\|~\.-]+)*@([\w][\w-]*\.)+[\w][\w-]*$/)  ){
									item_errors.push( "'.__('please XXX@XXX.XXX format',SL_DOMAIN).'");
								}
							}';
//								if( ! val.match(/^[^\@]+?@[\w\.\-]+\.[\w\.\-]+$/)  ){
		$check_contens['chkTime'] = '
							if ($j('.$target.').hasClass("chkTime") ) {
								if( ! val.match(/^(?:[ ]?\d{1,2}:\d{1,2})$|^(?:\d{4})$/)  ){
									item_errors.push( "'.__('please HH:MM or HHMM format',SL_DOMAIN).'");
								}
								if (+val.slice(0,2) > 47 ) {
									item_errors.push( "'.__('Hour is max 47',SL_DOMAIN).'");
								}

							}';

		$check_contens['chkDate'] = '
							if ($j('.$target.').hasClass("chkDate") ) {
								if( val.match(/^'.__('(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-](\d{4})',SL_DOMAIN).'$/) || val.match(/^'.__('(\d{2})(\d{2})(\d{4})',SL_DOMAIN).'$/)  ){
									var y = '.__('RegExp.$3',SL_DOMAIN).';
									var m = '.__('RegExp.$1',SL_DOMAIN).';
									var d = '.__('RegExp.$2',SL_DOMAIN).';

									var di = new Date(y, m - 1, d);
									if (!(di.getFullYear() == y && di.getMonth() == m - 1 &&  di.getDate() == d) ) {
										item_errors.push( "'.__('this day not exist?',SL_DOMAIN).'");
									}
								}
								else {
									item_errors.push( "'.__('please MM/DD/YYYY or MMDDYYYY format',SL_DOMAIN).'");
								}
							}';

		$check_contens['lenmax'] = '
							if ( cl.indexOf("lenmax") != -1 ) {
								var length = cl.match(/lenmax(\d+)/) ? RegExp.$1 : Number.MAX_VALUE;
								if ( val.length > length  ) {
									item_errors.push(
										length.replace(/[A-Za-z0-9]/g, function(s) {
											return String.fromCharCode(s.charCodeAt(0) + 0xFEE0);
										})+"'.__('within charactors',SL_DOMAIN).'");
								}
							}';
		$check_contens['range'] = '
							if ( cl.indexOf("range") != -1 ) {
								cl.match(/range(\d+?)_(\d+)/);
								var minval = RegExp.$1;
								var maxval = RegExp.$2;
								if ( ( +val < +minval  ) || ( +val > +maxval ) ){
									item_errors.push(
										minval.replace(/[A-Za-z0-9]/g, function(s) {
											return String.fromCharCode(s.charCodeAt(0) + 0xFEE0);
										})+"'.__('greater than',SL_DOMAIN).'"+
										maxval.replace(/[A-Za-z0-9]/g, function(s) {
											return String.fromCharCode(s.charCodeAt(0) + 0xFEE0);
										})+"'.__('within',SL_DOMAIN).'");
								}
							}';
		$check_contens['reqOther'] = '
						if (cl.indexOf("reqOther_") != -1 ){
							if(val == ""){
								var target_item = cl.match(/reqOther_(.+)/) ? RegExp.$1 : "";
								var tmp_items = target_item.split("_");
								var is_found = false;
								for(var i = 0 ;i<tmp_items.length;i++) {
									if ($j("#"+tmp_items[i]).val() ) {
										is_found = true;
										break;
									}
								}
								if (! is_found) {
									var msg = Array();
									msg.push(check_items[id]["label"]);
									for(var i = 0 ;i<tmp_items.length;i++) {
										msg.push(check_items[tmp_items[i]]["label"]);
									}
									item_errors.push(msg.join(",")+"'.__('select one or more',SL_DOMAIN).'");
								}
							}
						}';

		$check_contens['reqCheckbox'] = '
						if (cl.indexOf("reqCheckbox") != -1 ){
							var is_checked = false;
//							$j('.$target.').children().filter("input[type=checkbox]").each(function(){
							$j('.$target.').find("input[type=checkbox]").each(function(){
								if ( $j('.$target.').is(":checked") ) {
									is_checked = true;
								}

							});
							if (is_checked == false ) {
								item_errors.push("'.__('please check',SL_DOMAIN).'");
							}
						}';

		$check_contens['reqRadio'] = '
						if (cl.indexOf("reqCheckbox") != -1 ){
							var is_checked = false;
							$j('.$target.').find("input[type=radio]").each(function(){
								if ( $j('.$target.').is(":checked") ) {
									is_checked = true;
								}

							});
							if (is_checked == false ) {
								item_errors.push("'.__('please check',SL_DOMAIN).'");
							}
						}';

		$check_contens['chkSpace'] = '
						if ($j('.$target.').hasClass("chkSpace") ) {
							val = val.replace("　"," ");
							if( ! val.match(/^.+\s+.+$/) ){
								item_errors.push( "'.__('space input between first-name and last-name',SL_DOMAIN).'");
							}
						}';

		return $check_contens;

	}
	static function set_download_item () {
		if ($_POST['target'] == 'reservation' ) {
			$date = 'CONCAT (DATE_FORMAT(rs.time_from,"'.__('%m/%d/%Y',SL_DOMAIN).'")," ",DATE_FORMAT(rs.time_from, "%H:%i"),"-",DATE_FORMAT(rs.time_to, "%H:%i"))';
			$download_items['date'] = array('id'=>'date','label'=>__('Date',SL_DOMAIN),'check'=>'checked','col'=>$date,'no_disp'=>true);
			$download_items['name'] = array('id'=>'name','label'=>__('Name',SL_DOMAIN),'check'=>'checked','col'=>'non_regist_name');
			$download_items['staff'] = array('id'=>'staff','label'=>__('Staff',SL_DOMAIN),'check'=>'checked','col'=>'st.user_login','user'=>'need');
			$download_items['item'] = array('id'=>'item','label'=>__('Menu',SL_DOMAIN),'check'=>'checked','col'=>'item_cds','item'=>'need');
			$download_items['remark'] = array('id'=>'remark','label'=>__('Remark',SL_DOMAIN),'check'=>'','col'=>'rs.remark');
		}
		elseif ($_POST['target'] == 'sales' ) {
			$date = 'CONCAT (DATE_FORMAT(sa.time_from,"'.__('%m/%d/%Y',SL_DOMAIN).'")," ",DATE_FORMAT(sa.time_from, "%H:%i"),"-",DATE_FORMAT(sa.time_to, "%H:%i"))';
			$download_items['date'] = array('id'=>'date','label'=>__('Date',SL_DOMAIN),'check'=>'checked','col'=>$date,'no_disp'=>true);
			$download_items['name'] = array('id'=>'name','label'=>__('Name',SL_DOMAIN),'check'=>'checked','col'=>'non_regist_name');
			$download_items['staff'] = array('id'=>'staff','label'=>__('Staff',SL_DOMAIN),'check'=>'checked','col'=>'st.user_login','user'=>'need');
			$download_items['item'] = array('id'=>'item','label'=>__('Menu',SL_DOMAIN),'check'=>'checked','col'=>'sa.item_cds','item'=>'need');
			$download_items['coupon'] = array('id'=>'coupon','label'=>__('Coupon',SL_DOMAIN),'check'=>'checked','col'=>'po.description');
			$download_items['price'] = array('id'=>'price','label'=>__('Price',SL_DOMAIN),'check'=>'checked','col'=>'sa.price');
			$download_items['remark'] = array('id'=>'remark','label'=>__('Remark',SL_DOMAIN),'check'=>'','col'=>'sa.remark');
		}
		return $download_items;
	}
	static function calcTargetDate() {
		$zengo = 1;
		if ($_POST['target_date_zengo'] == 'before' ) $zengo = -1;
		$cnt = intval($_POST['target_date_num']);
		switch ($_POST['target_date_patern']) {
			case 'day':
				$target_day = Salon_Component::computeDate($cnt*$zengo);
				break;
			case 'week':
				$target_day = Salon_Component::computeDate($cnt*7*$zengo);
				break;
			case 'month':
				$target_day = Salon_Component::computeMonth($cnt*$zengo);
				break;
			case 'year':
				$target_day = Salon_Component::computeYear($cnt*$zengo);
				break;
		}
		return $target_day;
	}

	static function editYmdForHtml($in) {
		if (empty($in) ) return;
		$ymd = explode('-',$in);
		$edit = __('mm/dd/yyyy',SL_DOMAIN);
		$edit = str_replace('yyyy',$ymd[0],$edit);
		$edit = str_replace('mm',$ymd[1],$edit);
		$edit = str_replace('dd',$ymd[2],$edit);
		return $edit;;
	}
//[2013/11/10]Ver 1.3.1 from
	static function echoDataTableDisplaySequence($col) {
		$up_name = __('up',SL_DOMAIN);
		$down_name = __('down',SL_DOMAIN);
		//順番は引数で渡す。ここでは支店の後ろなので４
		//スタッフの場合、seqデータがNULLの場合（WPにのみ登録しているユーザ）の対処
		echo <<<EOT
			var element = \$j("td:eq({$col})", nRow);
			element.text("");
			if (aData.display_sequence) {
				var up_box = \$j("<input>")
						.attr("type","button")
						.attr("id","salon_up_btn_"+iDataIndex)
						.attr("name","salon_up_"+iDataIndex)
						.attr("value","{$up_name}")
						.attr("class","sl_button salon_button_updown")
						.click(function(event) {
							if (iDataIndex == 0 ) return;
							fnSeqUpdate(this.parentNode,iDataIndex,-1);
						});
				var down_box = \$j("<input>")
						.attr("type","button")
						.attr("id","salon_down_btn_"+iDataIndex)
						.attr("name","salon_down_"+iDataIndex)
						.attr("value","{$down_name}")
						.attr("class","sl_button salon_button_updown")
						.click(function(event) {
							if (iDataIndex == target.fnSettings().aoData.length-1) return;
							fnSeqUpdate(this.parentNode,iDataIndex,1);
						});
				element.append(up_box);
				element.append(down_box);
			}

EOT;

	}

	static function replaceResult($indata){
		return strtoupper(substr($indata,0,3));
	}

	public function echoDataTableSeqUpdateRow($target_name,$target_key_name,$is_multi_branch ) {
		$target_src = get_bloginfo( 'wpurl' ).'/wp-admin/admin-ajax.php?action=sl'.$target_name;
		if (empty($target_key_name) ) $target_key_name = $target_name;
		$menu_func = ucwords($target_name);
		$check_logic = '';
		if ($is_multi_branch) {
			$check_logic = "if (setData['aoData'][position[0]]['_aData']['branch_cd'] != setData['aoData'][position[0]+plus_minus]['_aData']['branch_cd']) return;";
		}

		echo <<<EOT
			function fnSeqUpdate(target_col,current_row,plus_minus) {
				var position = target.fnGetPosition( target_col );
				var setData = target.fnSettings();
				{$check_logic}
				var addIndex = position[0] + plus_minus;
				//スタッフの場合、歯抜けがあるので対処
				while(addIndex >= 0 && addIndex < target.fnSettings().aoData.length) {
					if (setData['aoData'][addIndex]['_aData']['display_sequence'] ) break;
					addIndex += plus_minus;
				}
				if (addIndex < 0 || addIndex ==  target.fnSettings().aoData.length) return;

				var source_index = setData['aoData'][position[0]]['nTr']['_DT_RowIndex'];
				var source_sequence = setData['aoData'][position[0]]['_aData']['display_sequence'];
				var target_index = setData['aoData'][addIndex]['nTr']['_DT_RowIndex'];
				var target_sequence = setData['aoData'][addIndex]['_aData']['display_sequence'];
				var source_key_id = setData['aoData'][position[0]]['_aData']['{$target_key_name}'];
				var target_key_id = setData['aoData'][addIndex]['_aData']['{$target_key_name}'];


				\$j.ajax({
					type: "post",
					url:  "{$target_src}",
					dataType : "json",
						data: {
							"{$target_key_name}":source_key_id + "," + target_key_id,
							"value":source_sequence + "," + target_sequence,
							"type":"updated",
							"nonce":"{$this->nonce}",
							"menu_func":"{$menu_func}_Seq_Edit"
						},
					success: function(data) {
						if (data === null || data.status == "Error" ) {
							alert(data.message);
						}
						else {
							var save = setData['aoData'][position[0]];
							setData['aoData'][position[0]] = setData['aoData'][addIndex];
							setData['aoData'][position[0]]['nTr']['_DT_RowIndex'] = source_index;
							setData['aoData'][position[0]]['_aData']['display_sequence'] = source_sequence;
							setData['aoData'][addIndex] = save;
							setData['aoData'][addIndex]['nTr']['_DT_RowIndex'] = target_index;
							setData['aoData'][addIndex]['_aData']['display_sequence'] = target_sequence;
							target.fnDraw();
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						var parse_arrary = JSON.parse( XMLHttpRequest.responseText )
						alert (parse_arrary.message);
					}

				 });
			}
EOT;

	}
//[2013/11/10]Ver 1.3.1 To

//[2014/04/23]Ver 1.3.7 From
//
	private function _editDate($yyyymmdd,$first_hour) {
		$edit_yyyymmdd = substr($yyyymmdd,0,4). substr($yyyymmdd,5,2).  substr($yyyymmdd,8,2);
		$target = new DateTime($edit_yyyymmdd);
		if (+substr($yyyymmdd,11,2)<$first_hour) {
			$target->modify('-1 day');
		}
		return $target->format('Ymd');
	}
	private function _editTime($yyyymmdd) {
		return substr($yyyymmdd,11,2). substr($yyyymmdd,14,2);
	}

	private function _checkEdit24($hhmm,$first_hour,$last_hour){
//		$hh =  substr($to,0,2);
//		if (substr($from,0,2) > $hh ){
//			$hh += 24;
//		}
//		return $hh.substr($to,2,2);

		$hh = substr($hhmm,0,2);

		if ($last_hour > 23 ) {
			if ($hh < $first_hour ) $hh += 24;
		}
		return $hh;

	}

	public function echoMobileData($reservation_datas ,$target_day,$first_hour,$last_hour,
			$user_login="",$role = "") {
		//全件読むパターン
		$dayStaff = array();
		$return_set = array();
		$randam_num = mt_rand(1000000,9999999);
		foreach($reservation_datas as $k1 => $d1 ) {
			$date = $this->_editDate($d1['time_from'],$first_hour);
			$from = $this->_editTime($d1['time_from']);
			$to = $this->_editTime($d1['time_to']);
			if (( ! empty($user_login) &&  $user_login === $d1['user_login'] )
					|| 	$this->isSalonAdmin()
					|| (in_array('edit_booking',$role) && $this->user_branch_cd == $d1['branch_cd'])) {
				$dayStaff[$date][$d1['staff_cd']][] =
					array('s'=>$from
						,'e'=>$to
						,'ev'=>$d1['reservation_cd']
						,'st'=>$d1['status']
						,'it'=>$d1['item_cds']
						,'name'=>$d1['name']
						,'tel'=>$d1['tel']
						,'mail'=>$d1['email']
						,'remark'=>$d1['remark']
						,'p2'=>$d1['non_regist_activate_key']
						,'user_login'=>$d1['user_login']
						,'coupon'=>$d1['coupon']
						,'memo'=>unserialize($d1['memo'])
					);
			}
			else {
				$dayStaff[$date][$d1['staff_cd']][] = array('s'=>$from,'e'=>$to,'ev'=>$d1['reservation_cd']+$randam_num,'st'=>Salon_Edit::NG);
			}
		}
		//同一スタッフでの重複をチェック->予約済みのDIVの高さを求める分母として使用
		//k1は日付単位。現状、１日単位にしかデータが設定されないが複数日も可能にしとく
		if(count($dayStaff) >  0 ) {
			foreach($dayStaff as $k1 => $d1 ) {
				//k2はスタッフコード単位
				//d2は予約が配列で格納
				$set_array = array();
				foreach($d1 as $k2 => $d2) {
					//複数ある場合のみチェック
					$dup_table = array();
					//添字は階層を意味する
					$dup_table[0][] = $d2[0];
					$set_cnt = 1;

					if (count($d2) > 1 ) {
						$max_dup = 0;
						for ($i = 1 ; $i < count($d2) ; $i++  ) {
							$dup_flg = false;
							//階層の中で重複しない
							for($j = 0 ;  $j <= $max_dup ;$j++ ) {
								//k3はただのINDEX
								foreach ($dup_table[$j]  as $k3 => $d3 ){
									//d3上のデータと重複したら次の階層へ
									//重複しないのはd3上の開始よりd2の終了が前かd3上の終了よりd2の開始が後の場合のみ
									//24時間対応
									$ws1 = $this->_checkEdit24($d2[$i]['s'],$first_hour,$last_hour);
									$we1 = $this->_checkEdit24($d2[$i]['e'],$first_hour,$last_hour);
									$ws2 = $this->_checkEdit24($d3['s'],$first_hour,$last_hour);
									$we2 = $this->_checkEdit24($d3['e'],$first_hour,$last_hour);
									//if ($d2[$i]['e'] <= $d3['s'] || $d3['e'] <= $d2[$i]['s'] ) {
									if ($we1 <= $ws2 || $we2 <= $ws1 ) {
									}
									else {
										$dup_flg = true;
										continue 2;
									}
								}
								//ここにきたら重複はない
								$dup_table[$j][] = $d2[$i];
								$dup_flg = false;
								break 1;
							}
							//新しい階層をつくる場合
							if ($dup_flg) {
								$max_dup++;
								$dup_table[$max_dup][] = $d2[$i];
							}
						}
						$set_cnt = $max_dup+1;
					}
					//ここで階層と階層の内容を設定k4は階層
					$set_time = array();
					foreach ($dup_table as  $k4 => $d4 ) {
						//d5は実際の時間
						foreach ($d4 as $k5 => $d5 ) {
							//5分単位で左と幅を算出$this->branch_datas['time_step']を使う？
							$left = salon_component::calcMinute($first_hour.'00',$d5['s'])/5;
							$width = salon_component::calcMinute($d5['s'], $d5['e'])/5;
							if ($d5['st'] != Salon_Edit::NG) {
								$set_time[] = array($k4=>array("b"=>array($left,$width,$d5['ev'],$d5['s'],$d5['e'],$d5['st']),
																"d"=>array($d5['it']
																			,$d5['remark']
																			,$d5['p2']
																			,$d5['name']
																			,$d5['tel']
																			,$d5['mail']
																			,$d5['user_login']
																			,$d5['coupon']
																			,$d5['memo']
																	)
																)
													);
							}
							else  {
								$set_time[] = array($k4=>array("b"=>array($left,$width,$d5['ev'],$d5['s'],$d5['e'],$d5['st']),"d"=>array()));
							}

						}
					}
					$set_array[] = array($k2=>array("s"=>$set_cnt,
													"d"=>$set_time)
										);
				}
				$return_set[$k1] = json_encode(array("e"=>1,"d"=>$set_array));
			}
		}
		else {
				$return_set[$target_day] = json_encode(array("e"=>0));
		}
		return $return_set;
	}

	public function echoSetHolidayMobile($branch_datas,$working_datas,$target_year,$first_hour) {
// 		if (!empty($branch_datas['closed']) || $branch_datas['closed']==0 ) {

			echo 'slmSchedule.config.days = ['.$branch_datas['closed'].'];';

			//[2014/10/01]半休対応
			if ($branch_datas['memo'] ) {
				$tmp_detail_array = array();
				$days_detail_array = explode(";",$branch_datas['memo']);
				foreach($days_detail_array as $k1 => $d1 ) {
					$time_array = explode(",",$d1);

					$from = str_replace(":","",$time_array[0]);
					$to = str_replace(":","",$time_array[1]);

					$left = salon_component::calcMinute($first_hour.'00',$from)/5;
					$width = salon_component::calcMinute($from,$to)/5;
					if ($from=="0000"&&$to=="2400") $width=288;
					$tmp_detail_array[] = array($left,$width,$from,$to);
				}
				echo 'slmSchedule.config.days_detail = '.json_encode($tmp_detail_array).';';
			}
			echo 'slmSchedule.config.open_position = '.salon_component::calcMinute($first_hour.'00',$branch_datas['open_time'])/5 .';';
			if ($branch_datas['open_time']=="0000" && $branch_datas['close_time']=="2400") {
				echo 'slmSchedule.config.close_width = 288;';
			}
			else {
				echo 'slmSchedule.config.close_width = '.salon_component::calcMinute($branch_datas['open_time'],$branch_datas['close_time'])/5 .';';
			}

			//特殊な日の設定（定休日だけど営業するor営業日だけど休むなど）
// 			$startHH = +substr($branch_datas['open_time'],0,2);
// 			$startMM = +substr($branch_datas['open_time'],2,2);
// 			$endHH = +substr($branch_datas['close_time'],0,2);
// 			$endMM = +substr($branch_datas['close_time'],2,2);

			$sp_dates = unserialize($branch_datas['sp_dates']);
			$on_business_array = array();
			$holiday_array = array();

			$on_business_detail_array = array();
			$holiday_detail_array = array();

			$now = new DateTime(date_i18n('Ymd'));
			$min = clone $now;
			$max = clone $now;
// 			$min->sub(new DateInterval("P".$this->config_datas['SALON_CONFIG_BEFORE_DAY']."D"));
// 			$max->add(new DateInterval("P".$this->config_datas['SALON_CONFIG_AFTER_DAY']."D"));
			$min->modify("-".$this->config_datas['SALON_CONFIG_BEFORE_DAY']." day");
			$max->modify("+".$this->config_datas['SALON_CONFIG_AFTER_DAY']." day");
			$minYmd = $min->format("Ymd");
			$maxYmd = $max->format("Ymd");

//			$holiday_check_array = array();
			$today_check_array = array();
			for ($i=0;$i<2;$i++) {	//指定年と＋１(年末のことを考えて）
				$tmp_year = intval($target_year) + $i;
				if ($sp_dates && !empty($sp_dates[$tmp_year])) {
					foreach ($sp_dates[$tmp_year] as $k1 => $d1) {
						if (($minYmd <= $k1) && ($k1 <= $maxYmd)) {
							$today_check_array[$k1] = $d1;
							$tmp = 'new Date('.$tmp_year.','.(string)(intval(substr($k1,4,2))-1).','.(string)(intval(substr($k1,6,2))+0).')';
							$tmp_from = $branch_datas['open_time'];
							if (isset($d1['fromHHMM'])) {
								$tmp_from = $d1['fromHHMM'];
							}
// 							$tmp_from = '1100';
							$tmp_to = $branch_datas['close_time'];
							if (isset($d1['toHHMM'])) {
								$tmp_to = $d1['toHHMM'];
							}
// 							$tmp_to = '1500';
							$left = salon_component::calcMinute($first_hour.'00',$tmp_from)/5;
							$width = salon_component::calcMinute($tmp_from,$tmp_to)/5;
							if ($tmp_from=="0000"&&$tmp_to=="2400") $width=288;

							if ($d1['status']== Salon_Status::OPEN ) {
								$on_business_array[] = $tmp;
								$on_business_detail_array[] = array($left,$width,$tmp_from,$tmp_to);

							}
							elseif ($d1['status'] == Salon_Status::CLOSE ) {
								$holiday_array[] = $tmp;
								$holiday_detail_array[] = array($left,$width,$tmp_from,$tmp_to);
							}
						}

					}
				}
			}



			echo 'slmSchedule.config.on_business = [ '.implode(',',$on_business_array).' ];';
			echo 'slmSchedule.config.holidays = [ '.implode(',',$holiday_array).' ];';

			echo 'slmSchedule.config.on_business_detail = '.json_encode($on_business_detail_array).';';
			echo 'slmSchedule.config.holidays_detail = '.json_encode($holiday_detail_array).';';
			//			echo 'slmSchedule.config.chkHolidays = Array();';
//			foreach($holiday_check_array as $d1 ) {
//				echo 'slmSchedule.config.chkHolidays["'.$d1.'"]="";';
//			}

// 		}

		echo "slmSchedule.config.staff_holidays = {}; ";
		if (count($working_datas)>0) {
			$timeline_array = array();
			//k1はYYYYMMDD
			foreach ($working_datas as $k1 => $d1 ) {
				echo  'slmSchedule.config.staff_holidays["'.$k1.'"] = {};';
				//1日複数回の出勤があり得る
				$tmp_time_array = array();
				$staff_cd = "";
				//k2は休日パターンの場合はスタッフコードだが、出勤パターンの場合は通番
				//→k2は通番に
				$save_staff_cd = $d1[0]['staff_cd'];
				foreach ($d1 as $k2 => $d2 ) {
					$staff_cd = $d2['staff_cd'];
					if ($staff_cd <> $save_staff_cd) {
						echo 'slmSchedule.config.staff_holidays["'.$k1.'"]["'.$save_staff_cd.'"] = '.json_encode($tmp_time_array).';';
						$tmp_time_array = array();
					}
					$from = substr($d2['in_time'],-4);
					$to = substr($d2['out_time'],-4);
					//通常パターンの場合、出勤が開店より前の場合は開店時間にする
					$left = salon_component::calcMinute($first_hour.'00',$from)/5;
					$width = salon_component::calcMinute($from,$to)/5;
					if($branch_datas['close_time'] < "2401" ) {
						if ($from < $branch_datas['open_time']) {
							$left = 0;
							$from = $first_hour.'00';
						}
						if ($to > $branch_datas['close_time']) {
							$to = $branch_datas['close_time'];
						}
						$width = salon_component::calcMinute($from ,$to )/5;
					}
					$tmp_time_array[] = array($left,$width,substr($d2['in_time'],-4),substr($d2['out_time'],-4));
					$save_staff_cd = $staff_cd;
				}
				echo 'slmSchedule.config.staff_holidays["'.$k1.'"]["'.$save_staff_cd.'"] = '.json_encode($tmp_time_array).';';
			}
		}
	}
//[2014/04/23]Ver 1.3.7 To

//[2014/08/12]Ver 1.4.8 From
	static function echoCouponSelect($target_name,$promotion_datas,$is_mobile = false) {
		$echo_data = '';
		if (!$is_mobile) {
			if (count($promotion_datas) == 0 ) {
				$echo_data = '<div id="'.$target_name.'_wrap" style="display:none" ><select id="'.$target_name.'">';
			}
			else {
				$echo_data = '<div id="'.$target_name.'_wrap" ><select id="'.$target_name.'">';
			}
		}
		if (count($promotion_datas) == 0 ) {
			$echo_data .= '<option value="">'.__('No Coupon',SL_DOMAIN).'</option>';
		}
		else {
			$echo_data .= '<option value="">'.__('select please',SL_DOMAIN).'</option>';
		}
		foreach($promotion_datas as $k1 => $d1 ) {
			$echo_data .= '<option value="'.$d1['set_code'].'">'.htmlspecialchars($d1['description'],ENT_QUOTES).'</option>';
		}
		if (!$is_mobile) {
			$echo_data .= '</select>';
			$echo_data .= '</div>';
		}
		echo $echo_data;
	}


	static function echoPromotionArray($datas) {
		$comma = '';
		$isDateSet = false;
		echo 'var promotions = {';
		foreach ($datas as $k1 => $d1 ) {
			echo $comma.'"'.$d1['promotion_cd'].'":{';
			echo 'key:"'.$d1['set_code'].'"';
			echo ',val:"'.htmlspecialchars($d1['description']).'"';
			if ($d1['valid_from']) {
				echo ',from:'.$d1['valid_from_check'];
				$isDateSet = true;
			}
			else echo ',from:0';

			if ($d1['valid_to']) {
				echo ',to:'.$d1['valid_to_check'];
				$isDateSet = true;
			}
			else echo ',to:20991231';
			echo '}';
			$comma = ',';
		}
		echo '};';
		echo 'var isNeedToCheckPromotionDate = '.($isDateSet ? 'true' : 'false').';';
		echo 'var coupons = new Array(); ';
		foreach ($datas as $k1=>$d1 ) {
			echo 'coupons["'.$d1['set_code'].'"] = {promotion_cd:'.$d1['promotion_cd'].',discount_patern_cd:'.$d1['discount_patern_cd'].',discount:'.$d1['discount'].'};';
		}

	}



	static function echoDayFromToCheck() {
		echo <<<EOT



EOT;
	}

	static function echoDateConvert() {
		$datePatern = __('MM/DD/YYYY',SL_DOMAIN);
		echo <<<EOT
		function _fnDateConvert(indate,addTime) {
			var yyyy,mm,dd;
			var sp_patern = "{$datePatern}";
			var sp_patern_array = sp_patern.split("/");
			if (indate.indexOf("/") == -1 ){
				var posMM = sp_patern_array.indexOf("MM");
				var posDD = sp_patern_array.indexOf("DD");

				switch (sp_patern_array.indexOf("YYYY")) {
				case 0:
					yyyy = indate.substr(0,4);
					mm   = indate.substr(2*posMM+2,2);
					dd   = indate.substr(2*posDD+2,2);
					break;
				case 1:
					yyyy = indate.substr(2,4);
					mm   = indate.substr(3*posMM,2);
					dd   = indate.substr(3*posDD,2);
					break;
				case 2:
					yyyy = indate.substr(4,4);
					mm   = indate.substr(2*posMM,2);
					dd   = indate.substr(2*posDD,2);
					break;
				}
			}
			else {
				var sp = indate.split("/");
				yyyy = sp[sp_patern_array.indexOf("YYYY")];
				mm = sp[sp_patern_array.indexOf("MM")];
				dd = sp[sp_patern_array.indexOf("DD")];
			}
			if (addTime) {
				var time_array = addTime.split(":");
				if (time_array.length == 2) time_array[2]=0;
				return new Date(yyyy,mm-1,dd,+time_array[0],+time_array[1],+time_array[2]);
			}
			return new Date(yyyy,mm-1,dd);

		}
EOT;

	}

	static function echoTime25Check() {
		$msg =  __('Time step is wrong ?',SL_DOMAIN);
		echo <<<EOT
		function _fnCheckTimeStep(step,targetMin){
			if ( targetMin%step === 0 ) return true;
			alert("{$msg}");
			return false;
		}
EOT;
	}

	static function echoClosedDetailCheck() {
		$msg1 = __('please HH:MM or HHMM format',SL_DOMAIN);
		$msg2 = __('Hour is max 47',SL_DOMAIN);
		echo <<<EOT

		function _fnCheckClosedDetail(step) {
			var rtn = true;
			\$j(".sl_to,.sl_from").each(function (){
				var err_msg = "";
				var val = \$j(this).val();
				if (val) {
					if( ! val.match(/^(?:[ ]?\d{1,2}:\d{1,2})$|^(?:\d{4})$/)  ){
						err_msg ="{$msg1}";
					}
					else if (+val.slice(0,2) > 47 ) {
						err_msg ="{$msg2}";
					}
					else if (!_fnCheckTimeStep(step,val.slice(-2) ) ) {
						\$j(this).focus();
						rtn = false;
						return false;
					}
					if (err_msg != "" ) {
						\$j(this).focus();
						alert(err_msg);
						rtn = false;
						return false;
					}
				}
			});
			return rtn;
		}
		function _fnCheckSpdateDetail(step) {
			var rtn = true;
			\$j(".sl_to_sp,.sl_from_sp").each(function (){
				var err_msg = "";
				var val = \$j(this).val();
				if (val) {
					if( ! val.match(/^(?:[ ]?\d{1,2}:\d{1,2})$|^(?:\d{4})$/)  ){
						err_msg ="{$msg1}";
					}
					else if (+val.slice(0,2) > 47 ) {
						err_msg ="{$msg2}";
					}
					else if (!_fnCheckTimeStep(step,val.slice(-2) ) ) {
						\$j(this).focus();
						rtn = false;
						return false;
					}
					if (err_msg != "" ) {
						\$j(this).focus();
						alert(err_msg);
						rtn = false;
						return false;
					}
				}
			});
			return rtn;
		}
EOT;
	}



	static function echoRankPatern($customer_rank_datas) {
		echo '<div id="rank_patern_wrap" >';
		echo '<select id="rank_patern_cd" >';
		foreach ($customer_rank_datas as $k1 => $d1 ) {
			echo '<option value="'.$k1.'" >'.$d1.'</option>';
		}
		echo '</select></div>';
	}

//[2014/08/12]Ver 1.4.8 To

	static function echoSubMenu($use_submenu,$category_datas) {
		if ($use_submenu == Salon_Config::USE_NO_SUBMENU ) {
			return;
		}
		echo '<div id="sl_submenu_wrap" class="sl_category_wrap">';
		foreach($category_datas as $k1 => $d1 ) {
			echo '<ul>';
			echo '<li class="slm_label"><label>'.$d1['category_name'].':</label></li>';
			if ($d1['category_patern'] == Salon_Category::RADIO ) {
				echo '<li class="slm_li slm_category_wrap slm_category_check_opt">';
				$tmp_array = explode(',',$d1['category_values']);
				$max_cnt = count($tmp_array);
				for ($i = 0 ; $i < $max_cnt ;$i++ ) {
					echo "<label class=\"slm_category_option\" ><input class=\"slm_category_option sl_nocheck\" type=\"radio\" id=\"category_i{$d1['category_cd']}_{$i}\" value=\"{$i}\" name=\"category_{$d1['category_cd']}\" />{$tmp_array[$i]}</label>";
				}
				echo "</li>";
			}
			elseif ($d1['category_patern'] == Salon_Category::CHECK_BOX ) {
				$tmp_array = explode(',',$d1['category_values']);
				$max_cnt = count($tmp_array);
				echo '<li class="slm_li slm_category_wrap slm_category_check_opt">';
				for ($i = 0 ; $i < $max_cnt ;$i++ ) {
					echo "<label class=\"slm_category_option\" ><input class=\"slm_category_option sl_nocheck\" type=\"checkbox\" id=\"category_i{$d1['category_cd']}_{$i}\" value=\"{$i}\" />{$tmp_array[$i]}</label>";
				}
				echo "</li>";
			}
			elseif ($d1['category_patern'] == Salon_Category::SELECT ) {
				echo '<li class="slm_li slm_category_wrap">';
				echo "<select class=\"slm_sel sl_nocheck\" id=\"category_i{$d1['category_cd']}\" name=\"category_{$d1['category_cd']}\" />";
				$tmp_array = explode(',',$d1['category_values']);
				foreach ($tmp_array as $d2 ) {
					echo "<option value=\"{$d2}\">{$d2}</option>";
				}
				echo "</select>";
				echo "</li>";
			}
			elseif ($d1['category_patern'] == Salon_Category::TEXT
					|| $d1['category_patern'] == Salon_Category::MAIL) {
				echo '<li class="slm_li slm_category_wrap">';
				echo "<textarea id=\"category_i{$d1['category_cd']}\" class=\"sl_nocheck\" ></textarea>";
				echo "</li>";
			}
			echo '</ul>';
		}
		echo '</div>';
	}

	static function echoSubMenuPc($use_submenu,$category_datas) {
		if ($use_submenu == Salon_Config::USE_NO_SUBMENU ) {
			return;
		}
		echo '<div id="sl_submenu_wrap" class="sl_category_wrap">';
		foreach($category_datas as $k1 => $d1 ) {
			echo '<label>'.$d1['category_name'].':</label>';
			if (($d1['category_patern'] == Salon_Category::RADIO ) ||
				($d1['category_patern'] == Salon_Category::CHECK_BOX )) {
				$type = "radio";
				if ($d1['category_patern'] == Salon_Category::CHECK_BOX ) {
					$type = "checkbox";
				}
				echo "<div id=\"category_{$d1['category_cd']}_cds\" class=\"sl_detail_table_div sl_checkbox sl_nocheck \" >";
				echo '<table class="sl_detail_front" ><tbody>';
				$tmp_array = explode(',',$d1['category_values']);
				$max_cnt = count($tmp_array);
				for ($i = 0 ; $i < $max_cnt ;$i += 2 ) {
					echo '<tr>';
					$echo_data = "";
					for($j= 0 ; $j < 2 ; $j++ ) {
						if ( $max_cnt > ($i+$j) ) {
						$idx = $i + $j;
						$echo_data .= <<<EOT
							<td>
							<input class="sl_nocheck" type="{$type}" id="category_i{$d1['category_cd']}_{$idx}" value="{$idx}" name="category_{$d1['category_cd']}" />
							</td><td>
							<label for="category_i{$d1['category_cd']}_{$idx}" >{$tmp_array[$idx]}</label>
							</td>
EOT;
						}
					}
					echo $echo_data;
					echo '</tr>';
				}
				echo "</tbody></table>";
				echo '</div>';
			}
			elseif ($d1['category_patern'] == Salon_Category::SELECT ) {
				echo "<select class=\"sl_sel sl_nocheck\" id=\"category_i{$d1['category_cd']}\" name=\"category_{$d1['category_cd']}\" />";
				$tmp_array = explode(',',$d1['category_values']);
				foreach ($tmp_array as $d2 ) {
					echo "<option value=\"{$d2}\">{$d2}</option>";
				}
				echo "</select>";
			}
			elseif ($d1['category_patern'] == Salon_Category::TEXT
					|| $d1['category_patern'] == Salon_Category::MAIL) {
				echo "<textarea id=\"category_i{$d1['category_cd']}\" class=\"sl_nocheck\" ></textarea>";
			}
		}
		echo '</div >';
	}

	static function echoSubMenuSet() {
		echo <<< EOT
			\$j(".sl_category_wrap").find("input[type=checkbox]:checked,input[type=radio]:checked,textarea,select").each(function(){

				var id = \$j(this).attr("id");
				var tag = \$j(this)[0].tagName.toLowerCase();


				var id_array = id.split("_");

				if (tag == "input" ) {
					var type =  \$j(this).attr("type");
					if (type == "checkbox" ) {
						if (record_array[id_array[1]])
							record_array[id_array[1]] += ","+id_array[2];
						else
							record_array[id_array[1]] = id_array[2];

					}
					else if (type == "radio" ) {
						record_array[id_array[1]] = id_array[2];
					}


				}
				else if (tag == "textarea") {
					record_array[id_array[1]] = \$j(this).val();

				}
				else if (tag == "select" ) {
					record_array[id_array[1]] = \$j(this).val();
				}
			});
EOT;
	}

	static function echoSubMenuUpdate($category_datas,$setItemName = "ev_tmp") {
		echo '$j(".slm_category_check_opt input").prop("checked",false);';
		foreach($category_datas as $k1 => $d1 ) {
			if ($d1['category_patern'] == Salon_Category::SELECT ) {
				$tmp_array = explode(',',$d1['category_values']);
				echo " \$j(\"#category_i{$d1['category_cd']}\").val(\"".$tmp_array[0]."\");";
			}
			elseif ($d1['category_patern'] == Salon_Category::TEXT) {
				echo " \$j(\"#category_i{$d1['category_cd']}\").val(\"\");";
			}
		}
		$text = Salon_Category::TEXT;
		$select = Salon_Category::SELECT;
		$radio = Salon_Category::RADIO;
		$check = Salon_Category::CHECK_BOX;
		echo <<<EOT
		var record = {$setItemName}["memo"];
		if (record) {
			for (var k1 in record) {
				if (record.hasOwnProperty(k1)){
					switch(category_patern[k1]) {
					case {$text}:
						\$j("#category_"+k1).val(record[k1]);
						break;
					case {$select}:
						\$j("#category_"+k1).val(record[k1]);
						break;
					case {$radio}:

						\$j("#category_"+k1+"_option_wrap input").attr("checked",false);
						\$j("#category_"+k1+"_"+record[k1]).attr("checked",true);
						break;
					case {$check}:
						\$j("#category_"+k1+"_check_wrap input").attr("checked",false);
						var tmp_split = record[k1].split(",");
						for ( var i = 0 ; i < tmp_split.length ; i++ ) {
							\$j("#category_"+k1+"_"+tmp_split[i]).attr("checked",true);
						}
						break;
					}
				}
			}
		}
EOT;
	}


	static function echoOpenCloseTime($tag,$open,$close,$step,$plusClass="") {
		echo '<select id="'.$tag.'" name="'.$tag.'" class="sl_sel rcal_time '.$plusClass.'" >';
		$dt = new DateTime(substr($open,0,2).":".substr($open,2,2));
		$last_hour = substr($close,0,2).":".substr($close,2,2);
		$dt_max = new DateTime($last_hour);
		$echo_data =  '';
		while($dt <= $dt_max ) {
			$echo_data .= '<option value="'.$dt->format("H:i").'" >'.$dt->format("H:i").'</option>';
			$dt->modify("+".$step." minutes");
		}
		echo $echo_data;
		echo '</select>';

	}

	static function echoSettingPaternSelect($tag,$datas) {

		$echo_data =  '<select name="'.$tag.'" id="'.$tag.'">';
		foreach($datas as $k1 => $d1) {
			$echo_data .= '<option value="'.$k1.'" >'.$d1.'</option>';
		}
		$echo_data .= '</select>';
		echo $echo_data;

	}

	static function echoConfigSetLabel($set_margin) {

		echo <<<EOT
		var userAgent = window.navigator.userAgent.toLowerCase();
		var appVersion = window.navigator.appVersion.toLowerCase();
		for(index in check_items) {
			if (check_items[index]) {
				var diff = 0;
				var id = check_items[index]["id"];
				\$j("#"+id+"_lbl").children(".small").text(check_items[index]["tips"]);
				if (\$j("#"+id)[0].tagName == "TEXTAREA" ) diff = 5;
				else {
					if ( \$j("#"+id).parent().hasClass("config_item_wrap") ) {
						diff = \$j("#"+id+"_lbl").outerHeight(true) - \$j("#"+id).parent().outerHeight(true);
					}
					else {
						diff = \$j("#"+id+"_lbl").outerHeight(true) - \$j("#"+id).outerHeight(true);
					}
				}
				if (diff > 0 ) {
					diff += {$set_margin}+5;
					\$j("#"+id).attr("style","margin-bottom: "+diff+"px;");
					\$j("#"+id+"_lbl").children(".small").attr("style","text-align:left;");
				}

				if (userAgent.indexOf('msie') != -1) {
				//ie9以下は無視
					var lineHeight = parseFloat(\$j("#"+id+"_lbl .small").css("line-height"))*parseFloat(\$j("body").css("font-size"));
					var bHeight = Math.round(lineHeight);
				}else{//ie以外
				    var lineHeight = parseFloat(\$j("#"+id+"_lbl .small").css("line-height"));
				    var bHeight = Math.round(lineHeight);
				}
				if (bHeight < \$j("#"+id+"_lbl .small").height() ) {
					\$j("#"+id+"_lbl .small").attr("style","text-align:left;");
				}

			}
		}
EOT;
	}

	static function echoShortcode() {
		$msg1 = __('(1) After installing, "Reservation Confirm" is added to "Pages".Do not delete this. You may change the title.',SL_DOMAIN);
		$msg2 = __('(2) The "Reservation Confirm" is not displayed in the front screen.',SL_DOMAIN);
		$msg3 = __('(3) Sample data has been registered. It is a good idea to first use the sample data.',SL_DOMAIN);
		$msg4 = __('(4) Notification mail to staff need to set "Mail address"',SL_DOMAIN);
		$hs = "<h4>";
		$he = "</h4>";
		echo <<<EOT
	<div id="sl_shortcode_wrap">
		{$hs}{$msg1}{$he}
		{$hs}{$msg2}{$he}
		{$hs}{$msg3}{$he}
		{$hs}{$msg4}{$he}
	</div>
EOT;
	}

	//最初の営業日を求める
	public function getFirstValidYYYYMMDD($branch_datas ,$limit_minutes = 0) {
		$now = new DateTime(date_i18n('Y-m-d H:i'));
		$isDayAdd = false;
		if ($limit_minutes > 0) {
			if ((24 * 60 ) <= $limit_minutes) {
				$setDates = round($limit_minutes / (60 * 24));
				$now->setTime(+substr($branch_datas['open_time'],0,2)
							,+substr($branch_datas['open_time'],2,2));
				$now->modify("+".$setDates." day");
				$isDayAdd = true;
			}
			else {
				$now->modify("+".$limit_minutes." min");
			}
		}
		$current_time = $now->format("Hi");
		//24時をまたぐ運用で2400以降の場合
		//実日付は変更されているが、運用日付＝カレンダーの当日は前日
		if ( +$branch_datas['close_time'] > 2400 ) {
			if (!$isDayAdd && $branch_datas["open_time"] > $current_time ) {
				$close24 = +$current_time + 2400;
				if ( +$branch_datas['close_time'] >= $close24	)  {
					$now->modify("-1 day");
				}
			}
		}
		else {
			if ($branch_datas['close_time'] < $current_time)  {
				$now->modify("+1 day");
			}
		}
		$firstDay = clone $now;
		$max = clone $now;
		$max->modify("+".$this->config_datas['SALON_CONFIG_AFTER_DAY']." day");
		$sp_dates = unserialize($branch_datas['sp_dates']);
		$yyyymmdd = "";

		$open = $branch_datas['open_time'];
		$close = $branch_datas['close_time'];
		$datas = explode(",",$branch_datas['closed']);
		$datas_detail = explode(";",$branch_datas['memo']);
		//日曜日～
		$days_array = array(
				0 => false
				,1 => false
				,2 => false
				,3 => false
				,4 => false
				,5 => false
				,6 => false
		);

		foreach ($datas as $k1 => $d1 ) {
			if (!empty($datas_detail[$k1])) {
				$openClose = explode(",",$datas_detail[$k1]);
				if ($this->checkAllDay($open, $close
						, $openClose[0]
						, $openClose[1]) ) {
							$days_array[$d1] = true;
						}
			}
		}

		$isFound = false;
		for ($i = 0 ; $i < $this->config_datas['SALON_CONFIG_AFTER_DAY'] ; $i++) {
			//check
			$yyyy = $now->format("Y");
			$yyyymmdd = $now->format("Ymd");
			//特別な休業・営業日
			if (isset($sp_dates[$yyyy])
			 &&isset($sp_dates[$yyyy][$yyyymmdd])) {
			 	//特別な営業日だったら該当
				if ($sp_dates[$yyyy][$yyyymmdd]['status'] == Salon_Status::OPEN) {
					$isFound = true;
					break;
				}
				//特別な休業日だけど開閉店時間と一致しないなら部分的に営業できるので該当
				if ($this->checkAllDay(
						$open, $close
						, $sp_dates[$yyyy][$yyyymmdd]['fromHHMM']
						, $sp_dates[$yyyy][$yyyymmdd]['toHHMM']) == false) {
					$isFound = true;
					break;
				}
			}

			if ($days_array[$now->format("w")] == false) {
				$isFound = true;
				break;
			}
			$now->modify("+1 day");
		}
		if (!$isFound) {
			$yyyymmdd = $firstDay->format("Ymd");
		}
		return $yyyymmdd;
	}

	static function checkAllDay($open,$close,$checkFrom,$checkTo) {
		return  (+$open == $checkFrom && +$close == +$checkTo );
	}

	static function setReservationUrl($branch_cd) {
		$pages = get_option('salon_reservation_pages_id');
		if ($pages === false) {
			return "";
		}
		$pages =  unserialize($pages);
		if (! isset($pages[$branch_cd])) {
			return "";
		}
		$url = sprintf('%s/?page_id=%d',get_bloginfo( 'url' ),intval($pages[$branch_cd]));
		return $url;
	}

	public function setDefaultBookingItems() {
		$items = $this->setItemContents();
		$result =
		array(
			'name' => array('label' => $items['customer_name']['label']
					,'set_label' => $items['customer_name']['label']
					,'set_tips' => $items['customer_name']['tips']
					,'is_possible_not_display' => false
					,'exist_check' => true
					,'check' => $items['customer_name']['check']
					,'id' => 'customer_name'
			)
			,'tel_staff' => array('label' => __('Tel(staff)',SL_DOMAIN)
					,'set_label' => $items['booking_tel']['label']
					,'set_tips' => $items['booking_tel']['tips']
					,'is_possible_not_display' => false
					,'exist_check' => true
					,'check' => $items['booking_tel']['check']
					,'id' => 'booking_tel'
			)
			,'tel_customer' => array('label' => __('Tel(customer)',SL_DOMAIN)
					,'set_label' => $items['branch_tel']['label']
					,'set_tips' => $items['branch_tel']['tips']
					,'is_possible_not_display' => false
					,'exist_check' => true
					,'check' => $items['branch_tel']['check']
					,'id' => 'branch_tel'
			)
			,'mail_staff' => array('label' => __('Mail(staff)',SL_DOMAIN)
					,'set_label' => $items['mail_norequired']['label']
					,'set_tips' => $items['mail_norequired']['tips']
					,'is_possible_not_display' => false
					,'exist_check' => true
					,'check' => $items['mail_norequired']['check']
					,'id' => 'mail_norequired'
			)
			,'mail_customer' => array('label' => __('Mail(customer)',SL_DOMAIN)
					,'set_label' => $items['mail']['label']
					,'set_tips' => $items['mail']['tips']
					,'is_possible_not_display' => false
					,'exist_check' => true
					,'check' => $items['mail']['check']
					,'id' => 'mail'
			)
			,'time' => array('label' => $items['start_time']['label']
					,'set_label' => $items['start_time']['label']
					,'set_tips' => $items['start_time']['tips']
					,'is_possible_not_display' => false
					,'exist_check' => false
					,'id' => 'start_time'
			)
			,'staff' => array('label' => $items['staff_cd']['label']
					,'set_label' => $items['staff_cd']['label']
					,'set_tips' => $items['staff_cd']['tips']
					,'is_possible_not_display' => false
					,'is_display' => true
					,'exist_check' => false
					,'check' => $items['staff_cd']['check']
					,'id' => 'staff_cd'
//					,'remark' => __('Registerd staff need to be only one staff.',SL_DOMAIN)
			)
			,'menu' => array('label' => $items['item_cds']['label']
					,'set_label' => $items['item_cds']['label']
					,'set_tips' => $items['item_cds']['tips']
					,'is_possible_not_display' => false
					,'is_display' => true
					,'exist_check' => false
					,'check' => $items['item_cds']['check']
					,'id' => 'item_cds'
//					,'remark' => __('Registerd menu need to be only one menu.',SL_DOMAIN)
			)
			,'remark' => array('label' => $items['remark']['label']
					,'set_label' => $items['remark']['label']
					,'set_tips' => $items['remark']['tips']
					,'is_possible_not_display' => true
					,'is_display' => true
					,'exist_check' => true
					,'check' => $items['remark']['check']
					,'id' => 'remark'
			)
			,'coupon' => array('label' => $items['coupon']['label']
					,'set_label' => $items['coupon']['label']
					,'set_tips' => $items['coupon']['tips']
					,'is_possible_not_display' => false
					,'is_display' => true
					,'exist_check' => false
					,'check' => $items['coupon']['check']
					,'id' => 'coupon'
			)
			,'price' => array('label' => __('price',SL_DOMAIN)
					,'set_label' => __('price',SL_DOMAIN)
					,'set_tips' => ''
					,'is_possible_not_display' => true
					,'is_display' => true
					,'exist_check' => false
					,'id' => 'price'
			)
			,'call' => array('label' => __('phone call',SL_DOMAIN)
					,'set_label' => __('Telephone Here',SL_DOMAIN)
					,'set_tips' => ''
					,'is_possible_not_display' => true
					,'is_display' => true
					,'exist_check' => false
					,'id' => 'call'
			)
			,'today' => array('label' => __('today button',SL_DOMAIN)
					,'set_label' => __('today',SL_DOMAIN)
					,'set_tips' => ''
					,'is_possible_not_display' => true
					,'is_display' => true
					,'exist_check' => false
					,'id' => 'today'
			)
		);
		$result['staff']['is_possible_not_display'] = true;
		$result['staff']['remark'] = __('If you want to hide this field,</br>registerd staff need to be only one staff.',SL_DOMAIN);
		$result['menu']['is_possible_not_display'] = true;
		$result['menu']['remark'] = __('If you want to hide this field,</br>registerd menu need to be only one menu.',SL_DOMAIN);

		return $result;

	}
	public function setFrontItems($configData) {
		$setData = $this->setDefaultBookingItems();

		if (is_array($configData) ) {
			foreach ($setData as $k1 => $d1) {
				if (array_key_exists ($k1,$configData) == true) {
					foreach($configData[$k1] as $k2 => $d2) {
						//デフォルト値と異なる場合
						if ($k2 != "label"
						&& $k2 != "exist_check"
						&& $k2 != "is_possible_not_display"
						&& $k2 != "id") {
							if ($setData[$k1][$k2] != $d2) {
								$this->is_no_change_booking_items = false;
							}
							if ($k2 == "set_label"
							 || $k2 == "set_tips") {
							 	$setData[$k1][$k2] = htmlspecialchars($d2,ENT_QUOTES);
							}
							else {
								//デフォルトに項目が増えた場合を考慮して個別に入れる。
								$setData[$k1][$k2] = $d2;
							}
						}
					}
				}
			}
		}
		return $setData;

	}

	static function transCheckWord($inWord) {
		$result = $inWord;
		switch($inWord) {
		case  "chk_required":
		case "reqCheckbox":
			$result = __("Mandatory field",SL_DOMAIN);
			break;
		case  "lenmax30":
		case "lenmax300":

			$result = __("Maximum length",SL_DOMAIN);
			break;
		case  "chkTel":
			$result = __("Format Phone Numbers",SL_DOMAIN);
			break;
		case  "chkMail":
			$result = __("Format Mail",SL_DOMAIN);
			break;
		case "chkSpace":
			$result = "";
			break;
		default:
			if (substr($inWord,0,8) == "reqOther") {
				$result = "";
			}
			break;
		}
		return $result;

	}

	public function setFilter() {
		if (is_null($this->config_datas) ) {
			return;
		}
		if (!empty($this->config_datas['SALON_CONFIG_DISPLAY_TITLE1'])) {
			add_filter('salon_booking_first_remark'
					,array( &$this,'setDisplayTitle')
					,self::FILTER_PRIORITY);
		}


		if (! $this->is_no_change_booking_items ) {
			add_filter('salon_booking_set_check_item'
					,array( &$this,'setCheckItems')
					,self::FILTER_PRIORITY);
		}
		return;
	}

	public function setDisplayTitle($body) {
		$body = $this->config_datas['SALON_CONFIG_DISPLAY_TITLE1'];
		return $body;
	}

	public function setCheckItems($check_array) {
		foreach($this->config_datas['SALON_CONFIG_FRONT_ITEMS'] as $k1 => $d1 ) {
			if (isset($check_array[$d1['id']]) ) {
				$check_array[$d1['id']]['label'] = $d1['set_label'];
				$check_array[$d1['id']]['tips'] = $d1['set_tips'];
				if ($d1['exist_check']) {
					$check_array[$d1['id']]['check'] = $d1['check'];
				}
			}
		}
		return $check_array;
	}
}
<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Record_Page extends Salon_Page {


	private $current_user_branch_cd = '';

	private $category_datas = null;
	private $reservation_datas = null;

	private $all_branch_datas = null;
	private $branch_datas = null;

	private $is_selected_customer = false;
	private $selected_user_login = "";


	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		if (!empty($_REQUEST['cu'])) {
			$this->is_selected_customer = true;
			$this->selected_user_login = $_REQUEST['cu'];
		}
	}

	public function get_customer_cd() {
		return $this->selected_user_login;
	}

	public function set_category_datas ($set_data ) {
		$this->category_datas = $set_data;
	}

	public function set_reservation_datas ($set_data ) {
		$this->reservation_datas = $set_data;
	}

	public function set_all_branch_datas ($branch_datas) {
		$this->all_branch_datas = $branch_datas;
	}
	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
	}
	public function set_current_user_branch_cd($branch_cd) {
		$this->current_user_branch_cd = $branch_cd;
	}
	public function get_set_branch_cd () {
		if (empty($_POST['set_branch_cd']) ) return;

		return @$_POST['set_branch_cd'];
	}


	public function show_page() {

?>

<script type="text/javascript">
		var $j = jQuery


		var target;
		var save_target_day = "";
		var save_k1 = "";

		<?php //parent::echoClientItem($this->set_items); //for only_branch?>
		<?php parent::set_datepicker_date(); ?>

		var getReservation = new Object();
		var setMonth = new Object();

		<?php
		$current = date_i18n('Ym');
		//該当データがなくても対象月の空配列は返却する。
		foreach ($this->reservation_datas as $k1 => $d1 ) {
			echo 'setMonth["'.$k1.'"] = '.count($this->reservation_datas[$k1]).';';
			if (count($this->reservation_datas[$k1]) > 0){
				$save_day = $d1[0]['target_day'];
				$tmp_array = array();
				$index = 0;
				foreach($d1 as $k2 => $d2  ) {
					if ($save_day != $d2['target_day']) {
						echo 'getReservation["'.$save_day.'"]={'.implode(',',$tmp_array).'};';
						$tmp_array = array();
						$index = 0;
					}
					$tmp_br= '""';
					if(isset($d2['record']) && !empty($d2['record'])) {
						$tmp_br = json_encode(unserialize($d2['record']));
					}
					$tmp_array[] = 	$index++.':'.
									'{time_from:"'.$d2['time_from'].'",'.
									' time_to:"'.$d2['time_to'].'",'.
									' name:"'.$d2['name'].'",'.
									' staff_name:"'.$d2['staff_name'].'",'.
									' email:"'.$d2['email'].'",'.
									' customer_cd:"'.$d2['customer_cd'].'",'.
									' reservation_cd:"'.$d2['reservation_cd'].'",'.
									' operate:"'.$d2['operate'].'",'.
									' record:'.$tmp_br.' }';
					$save_day = $d2['target_day'];
				}
				echo 'getReservation["'.$save_day.'"]={'.implode(',',$tmp_array).'};';
			}
		}
		//カテゴリーのパターンを設定する
		echo 'var category_patern = new Object();';
		foreach($this->category_datas as $k1 => $d1 ) {
			echo 'category_patern["i'.$d1['category_cd'].'"]='.$d1['category_patern'].';';
		}

		?>

		$j(document).ready(function() {

			<?php parent::echoCommonButton();			//共通ボタン	?>

			<?php  parent::set_datepickerDefault(true,false); ?>
			<?php

				$addCode = ',onSelect: function(dateText, inst) { alert(dateText); //setDayData(dateText.replace(/\//g,""));
				}';
				$display_month = 3;
				//parent::set_datepicker("sl_calendar",true,"",$addCode,$display_month);
			?>
<?php
			if ($this->isSalonAdmin() ) {
				$tmp_dir = SL_PLUGIN_SRC_URL;
				$tmp_action = str_replace('%7E', '~', $_SERVER['REQUEST_URI']);
				echo <<<EOT
				\$j("#branch_cd").change(function(){
					\$j("#sl_submit").html('<form id="sl_form" method="post" action="{$tmp_action}" ><input name="set_branch_cd" id="set_branch_cd" type="hidden"/></form>');
					\$j("#set_branch_cd").val(\$j("#branch_cd").val());
					\$j("#sl_form").submit();

				});
EOT;
			}
?>


			$j("#sl_calendar").datepicker({
					 numberOfMonths: 3
					,beforeShowDay: function(day) {
						<?php //1つめクリック可能、クラス名、タイトル ?>
						var yyyymmdd = $j.format.date(day, "yyyyMMdd");
						var holiday = holidays[yyyymmdd];
						if (holiday) {
							result = [false,"date-holiday0",holiday.title];
						}
						else {
							switch (day.getDay()) {
							case 0: result = [false,"date-sunday-show",""]; break;
							case 6: result = [false,"date-saturday-show",""]; break;
							default:result = [false, "",""];	break;
							}
						}
						var exist_reservation = getReservation[yyyymmdd]
						if (exist_reservation) {
							result[0] = true;
							result[1] += " date-reservation";
						}
						return result;
					}
					,changeMonth: false
					,onChangeMonthYear: function( year, month, inst ) {
						//var yyyymm = year+("0"+month).slice(-2);
						var lastMonth = new Date(year ,month-1,1);
						lastMonth.setMonth(lastMonth.getMonth()-1);
						var yyyymm = lastMonth.getFullYear() + ("0"+(lastMonth.getMonth()+1)).slice(-2);
						if (!setMonth[yyyymm]) {
							_fnGetServerData(yyyymm);
						}
					}
					,onSelect:function(dateText,inst) {
						save_target_day = inst.selectedYear+"/"+("0"+(inst.selectedMonth+1)).slice(-2)+"/"+ ("0"+inst.selectedDay).slice(-2)
//						save_target_day = dateText;
						$j("#data_detail").hide();
						_fnSetReservationData(save_target_day);
					}
				});



			target = $j("#lists").dataTable({
				<?php parent::echoDataTableLang(100,true,__('Click the date',SL_DOMAIN),__('Click the date',SL_DOMAIN)); ?>
				<?php parent::echoTableItem(array('customer_name','record_time','staff_name_aft','no_edit_remark'),false,true,"50px"); ?>
				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Record_Init" } )
				  aoData.push( { "name": "target_branch_cd","value":<?php echo $this->current_user_branch_cd; ?> } );
				},
				"fnDrawCallback": function () {
					$j("#lists  tbody .sl_select").click(function(event) {
						fnSelectRow(this);
					});
					<?php //parent::echoEditableCommon("item"); ?>
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					<?php parent::echoDataTableSelecter("name",false); ?>
					element.empty();
					element.append(sel_box);
				}
			});
		});

		function fnSelectRow(target_col) {
			fnDetailInit();

			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();

			save_k1 = position[0];

			var record = setData['aoData'][position[0]]['_aData']['record'];
			if (record) {
				for (var k1 in record) {
					if (record.hasOwnProperty(k1)){
						switch(category_patern[k1]) {
						case <?php echo Salon_Category::TEXT; ?> :
							$j("#category_"+k1).val(record[k1]);
							break;
						case <?php echo Salon_Category::SELECT; ?> :
							$j("#category_"+k1).val(record[k1]);
							break;
						case <?php echo Salon_Category::RADIO; ?> :

							$j("#category_"+k1+"_option_wrap input").attr("checked",false);
							$j("#category_"+k1+"_"+record[k1]).attr("checked",true);
							break;
						case <?php echo Salon_Category::CHECK_BOX; ?> :
							$j("#category_"+k1+"_check_wrap input").attr("checked",false);
							var tmp_split = record[k1].split(",");
							for ( var i = 0 ; i < tmp_split.length ; i++ ) {
								$j("#category_"+k1+"_"+tmp_split[i]).attr("checked",true);
							}
							break;
						}
					}
				}
			}



			$j("#sl_name").text(setData['aoData'][position[0]]['_aData']['name']);
			var edit_day = save_target_day + " " + setData['aoData'][position[0]]['_aData']['reserved_time'];
			$j("#target_day").text(edit_day);
			$j("#staff_name").text(setData['aoData'][position[0]]['_aData']['staff_name_aft']);

			$j("#button_update").removeAttr("disabled");
			$j("#button_clear").show();

			$j("#data_detail").show();
			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN);  ?>");


		}

		function fnClickAddRow() {

			var record_array = Object();

			$j("#sl_category_wrap").find("input[type=checkbox]:checked,input[type=radio]:checked,textarea,select").each(function(){

				var id = $j(this).attr("id");
				var tag = $j(this)[0].tagName.toLowerCase();


				var id_array = id.split("_");

				if (tag == "input" ) {
					var type =  $j(this).attr("type");
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
					record_array[id_array[1]] = $j(this).val();

				}
				else if (tag == "select" ) {
					record_array[id_array[1]] = $j(this).val();
				}
			});


			var setData = target.fnSettings();
			var operate = setData['aoData'][save_k1]['_aData']['operate']
			var reservation_cd = setData['aoData'][save_k1]['_aData']['reservation_cd'];
			var customer_cd = setData['aoData'][save_k1]['_aData']['customer_cd'];
			var user_login = setData['aoData'][save_k1]['_aData']['user_login'];
			 $j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slrecord",
					dataType : "json",

					data: {
						"reservation_cd":reservation_cd,
						"customer_cd":customer_cd,
						"user_login":user_login,
						"type":operate,
						"record":record_array,
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Record_Edit"
					},

					success: function(data) {
						if (data === null || data.status == "Error" ) {
							alert(data.message);
						}
						else {
<?php 						//target.fnUpdate( data.set_data ,parseInt(save_k1) ); ?>
							var yyyymmdd = save_target_day.replace(/\//g,"");
							getReservation[yyyymmdd][save_k1]["record"] = data.set_data.record;
							_fnSetReservationData(save_target_day)
							fnDetailInit();
							$j(target.fnSettings().aoData).each(function (){
								$j(this.nTr).removeClass("row_selected");
							});

						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
			 });
		}


		function fnDetailInit() {
			$j("#data_detail input[type=\"text\"]").val("");
			$j("#data_detail select").val("");
			$j("#data_detail textarea").val("");
			$j("#button_update").attr("disabled", "disabled");
			$j(".sl_category_span span").text("");
			$j(".sl_category_option").attr("checked",false);

			save_k1 = "";

			<?php
			if ($this->is_multi_branch && $this->isSalonAdmin() ) echo '$j("#branch_cd").val('.$this->current_user_branch_cd.');';
			?>
			<?php //parent::echo_clear_error(); ?>

		}

		function _fnGetServerData(base_day){
			<?php //base_day YYYYMM?>
			var yyyy = base_day.substr(0,4);
			var mm = base_day.substr(-2);
			var last = new Date(yyyy,mm,0); <?php //翌月の0日=今月末 ?>
			var user_login = "";
<?php
	if ($this->selected_user_login != "") {
		echo 'user_login = "'.$this->selected_user_login.'";';
	}
?>

			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slrecord",
					dataType : "json",
					data: {
						"target_branch_cd":<?php echo $this->current_user_branch_cd; ?>,
						"from":yyyy+'-'+mm+'-1',
						"to":$j.format.date(last, "yyyy-MM-dd"),
						"user_login":user_login,
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Record_Get_Month"
					},

					success: function(data) {
						setMonth[data.yyyymm] = data.cnt;
						if (data.cnt > 0 ) {
							var tmp_target_day = "";
							var index = 0;
							var tmp_array = new Object();
							for(var k1 = 0 ;k1 < data.cnt ;k1++) {
								if (tmp_target_day == "" ) tmp_target_day = data.datas[k1]["target_day"];
								if ( tmp_target_day != data.datas[k1]["target_day"]) {
									getReservation[tmp_target_day] = tmp_array;
									tmp_array = new Object();
									index = 0;
								}
								tmp_array[index++] = data.datas[k1];
								tmp_target_day = data.datas[k1]["target_day"];
							}
							getReservation[tmp_target_day] = tmp_array;
						}
					},
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
					}
			 });
		}
		function _fnSetReservationData(selDate) {

			target_day = selDate;
			var yyyymmdd = selDate.replace(/\//g,"");
			var getData = getReservation[yyyymmdd];
			var setData = Array();
			for ( var k1 in getData) {
				if (getData.hasOwnProperty(k1)){
					var time =	getData[k1]["time_from"].slice(0,2)+":"+ getData[k1]["time_from"].slice(-2)+ "-" +
								getData[k1]["time_to"].slice(0,2)+":"+ getData[k1]["time_to"].slice(-2);
					setData.push({
						"no": +k1+1,
						"check":false,
						"name":htmlspecialchars_decode(getData[k1]["name"]),
						"reserved_time":time,
						"staff_name_aft":htmlspecialchars_decode(getData[k1]["staff_name"]),
						"record":getData[k1]["record"],
						"reservation_cd":getData[k1]["reservation_cd"],
						"customer_cd":getData[k1]["customer_cd"],
						"user_login":getData[k1]["user_login"],
						"operate":getData[k1]["operate"],
						"remark":""
					});
				}
			}
			target.fnClearTable();
			target.fnAddData( setData );
		}

	<?php parent::echoCheckClinet(array('chk_required','lenmax')); ?>
	<?php parent::echoDayFormat(); ?>
	<?php parent::echoHtmlpecialchars(); ?>


	</script>

	<h2 id="sl_admin_title"><?php _e('Record Information',SL_DOMAIN); ?>
	<?php
			if ( $this->is_multi_branch ) {	//for only_branch
				if ($this->isSalonAdmin() ) {
					echo '(<select id="branch_cd">';
					foreach($this->all_branch_datas as $k1 => $d1 ) {
						echo '<option value="'.$d1['branch_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
					}
					echo '</select>)';
				}
				else {
					echo $this->branch_datas['name'];
				}
			}
	?>
    </h2>
	<?php echo parent::echoShortcode(); ?>

	<div type="text" id="sl_calendar" value="" /></div>

	<div id="salon_button_div" >
    <ul>
    <li>
	<input id="button_update" type="button" value="<?php _e('Update',SL_DOMAIN); ?>"/>
	<input id="button_clear" type="button" value="<?php _e('Clear',SL_DOMAIN); ?>"/>
	<input id="button_detail" type="button"/>
    </li>
<?php /*?>[TODO]次の変更で    <li>
		<input type="text" id="search_name" />
		<input id="button_search" type="button" class="sl_button" value=<?php _e('Search',SL_DOMAIN); ?> />
        <span><?php _e('The part of name or mail ',SL_DOMAIN); ?></span>
    </li>
<?php */?>
	</ul>
	</div>

	<div class="spacer"></div>

	<div id="data_detail" >
    <h3 ><?php _e('Each item can be registered or updated by the category screen',SL_DOMAIN); ?></h3>
	<dl id="sl_category_wrap" >
		<dt><label  ><?php _e('Name',SL_DOMAIN); ?>:</label></dt>
        <dd  class="sl_category_span"><span id="sl_name"></span></dd>
		<dt><label  ><?php _e('Reserved day',SL_DOMAIN); ?>:</label></dt>
		<dd  class="sl_category_span"><span id="target_day" ></span></dd>
		<dt><label ><?php _e('Reserved staff',SL_DOMAIN); ?>:</label></dt>
		<dd  class="sl_category_span"><span id="staff_name" ></span></dd>

<?php
	foreach($this->category_datas as $k1 => $d1 ) {
		echo '<dt><label>'.$d1['category_name'].'</label></dt>';
		if ($d1['category_patern'] == Salon_Category::RADIO ) {
			echo "<dd id=\"category_i{$d1['category_cd']}_option_wrap\" >";
			$tmp_array = explode(',',$d1['category_values']);
			$max_cnt = count($tmp_array);
			for ($i = 0 ; $i < $max_cnt ;$i++ ) {
				echo "<input class=\"sl_category_option\" type=\"radio\" id=\"category_i{$d1['category_cd']}_{$i}\" value=\"{$i}\" name=\"category_{$d1['category_cd']}\" /><label class=\"sl_category_option\" for=\"category_{$d1['category_cd']}_{$i}\">{$tmp_array[$i]}</label>";
			}
			echo "</dd>";
		}
		elseif ($d1['category_patern'] == Salon_Category::CHECK_BOX ) {
			$tmp_array = explode(',',$d1['category_values']);
			$max_cnt = count($tmp_array);
			echo "<dd id=\"category_i{$d1['category_cd']}_check_wrap\" >";
			for ($i = 0 ; $i < $max_cnt ;$i++ ) {
				echo "<input class=\"sl_category_option\" type=\"checkbox\" id=\"category_i{$d1['category_cd']}_{$i}\" value=\"{$d1['category_cd']}\" /><label class=\"sl_category_option\" for=\"category_{$d1['category_cd']}_{$i}\">{$tmp_array[$i]}</label>";
			}
			echo "</dd>";
		}
		if ($d1['category_patern'] == Salon_Category::SELECT ) {
			echo "<dd><select id=\"category_i{$d1['category_cd']}\" name=\"category_{$d1['category_cd']}\" />";
			$tmp_array = explode(',',$d1['category_values']);
			foreach ($tmp_array as $d1 ) {
				echo "<option value=\"{$d1}\">{$d1}</option>";
			}
			echo "</select></dd>";
		}
		elseif ($d1['category_patern'] == Salon_Category::TEXT ) {
//			echo "<dd><input type=\"text\" id=\"category_{$d1['category_cd']}\"  /></dd>";
			echo "<dd><textarea id=\"category_i{$d1['category_cd']}\" ></textarea></dd>";
		}
	}
?>
	</dl>

	</div>
		<div class="spacer"></div>

	<table class="flexme" id='lists'>
	<thead>
	</thead>
	</table>



<?php
	if ($this->isSalonAdmin() ) echo '<div id="sl_submit" ></div>';
	}	//show_page
}		//class


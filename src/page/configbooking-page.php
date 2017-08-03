<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Configbooking_Page extends Salon_Page {

	private $default_booking_items = null;

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}

	public function setItems() {
		if ($this->config_datas['SALON_CONFIG_STAFF_HOLIDAY_SET'] == Salon_Config::SET_STAFF_REVERSE) {
			$this->set_items = array('menu_column','holiday_display','menu_type','onbusiness_display','special_onbusiness_display','before_title','doSort');
		}
		else {
			$this->set_items = array('menu_column','holiday_display','menu_type','special_onbusiness_display','before_title');
		}
		if ( SALON_FOR_THEME
			|| isset($this->config_datas['SALON_CONFIG_DO_NEW_FUNCTION'])) {
			$this->set_items[] = 'color_pc_back';
			$this->set_items[] = 'color_pc_event';
			$this->set_items[] = 'color_pc_event_line';
			$this->set_items[] = 'color_pc_selected_back';
			$this->set_items[] = 'color_pc_unselected_back';
			$this->set_items[] = 'color_pc_holiday';
			$this->set_items[] = 'color_pc_onbusiness';
			$this->set_items[] = 'color_pc_focus';
			}
		$this->default_booking_items = $this->setDefaultBookingItems();
	}



	public function show_page() {
?>
<script type="text/javascript">
		var $j = jQuery
		<?php parent::echoClientItem($this->set_items); //for only_branch?>
		<?php

			$front_items = $this->setDefaultBookingItems();
			echo 'var restoreItems = {';
			foreach ($front_items as $k1 => $d1 ) {
				echo '"'.$k1.'" : new Array("'.$d1['set_label'].'","'.$d1['set_tips'].'","'.$d1['label'].'"),';
			}
			echo '}';
		?>

		$j(document).ready(function() {
			<?php parent::echoSetItemLabel(false); ?>
			<?php parent::echoConfigSetLabel(parent::INPUT_BOTTOM_MARGIN); ?>
<?php if ( SALON_FOR_THEME
		|| isset($this->config_datas['SALON_CONFIG_DO_NEW_FUNCTION'])) : ?>
$j('.color-picker').wpColorPicker({
				defaultColor:"<?php echo Salon_Color::PC_BACK; ?>"
				,mode:'hsl'
				,controls: {
					horiz: 's' // horizontal defaults to saturation
					,vert: 'l' // vertical defaults to lightness
					,strip: 'h' // right strip defaults to hue
				}
				,hide: true // hide the color picker by default
				,border: true // draw a border around the collection of UI elements
				,target: false // a DOM element / jQuery selector that the element will be appended within. Only used when called on an input.
				,width: 350 // the width of the collection of UI elements
//				,palettes: false // show a palette of basic colors beneath the square.
				,palettes:['<?php echo Salon_Color::PC_BACK; ?>'
						, '<?php echo Salon_Color::PC_BACK_PALLET1; ?>'
						, '<?php echo Salon_Color::PC_BACK_PALLET2; ?>'
						, '<?php echo Salon_Color::PC_BACK_PALLET3; ?>'
						, '<?php echo Salon_Color::PC_BACK_PALLET4; ?>'
						, '<?php echo Salon_Color::PC_BACK_PALLET5; ?>']
			});
<?php endif; ?>
<?php /*
			$j("#config_pc_back_color").css({width:'60px',margin:'0px'});
			$j("#config_pc_back_color+input").css({width:'100px',margin:'0px 0px 0px 10px'});
 */ ?>
			$j("#button_update").click(function()	{
				fnClickUpdate(false);
			});
            $j("#button_restore").click(function()	{
				if (!confirm("<?php _e('Restore OK ?',SL_DOMAIN); ?>") ) return false;
				fnClickUpdate(true);
			});

			$j(".sl_restore_button").click(function() {
				var key = this.id.replace("sl_restore_","");
				if (!confirm("<?php _e('Restore OK ?',SL_DOMAIN); ?>["+restoreItems[key][2]+"]" ) ) return false;
				var setLabel = this.id.replace("sl_restore","#sl_set_label");
				var setTips = this.id.replace("sl_restore","#sl_set_tips");
				var setChecks = this.id.replace("sl_restore","#sl_booking_items_col2");
				$j(setLabel).val(restoreItems[key][0]);
				if (restoreItems[key][1]) {
					$j(setTips).val(restoreItems[key][1]);
				}
				$j(setChecks + " input:checkbox").prop("checked",true);
			});

			$j("input[name=config_menu_column]").val([<?php echo $this->config_datas['SALON_CONFIG_DISPLAY_COLUMN']; ?>]);
			$j("input[name=config_menu_type]").val([<?php echo $this->config_datas['SALON_CONFIG_MENU_TYPE']; ?>]);
			$j("#config_before_title").text("<?php echo str_replace(array("\r\n","\r","\n",'"'), array('\n','\n','\n','\"'), $this->config_datas['SALON_CONFIG_DISPLAY_TITLE1']); ?>");
			$j("#config_holiday_display").val("<?php echo htmlspecialchars($this->config_datas['SALON_CONFIG_DISPLAY_HOLIDAY'],ENT_QUOTES); ?>");
			$j("#config_special_onbusiness_display").val("<?php echo htmlspecialchars($this->config_datas['SALON_CONFIG_DISPLAY_SPECIAL_ONBUSINESS'],ENT_QUOTES); ?>");

			<?php if ( $this->config_datas['SALON_CONFIG_DO_SORT_STAFF_AUTO'] == Salon_YesNo::Yes ) $set_boolean = 'true';
			else $set_boolean = 'false'; ?>

<?php if ($this->config_datas['SALON_CONFIG_STAFF_HOLIDAY_SET'] == Salon_Config::SET_STAFF_REVERSE) : ?>
				$j("#config_is_doSort").prop("checked",<?php echo $set_boolean; ?>);
				$j("#config_onbusiness_display").val("<?php echo $this->config_datas['SALON_CONFIG_DISPLAY_ONBUSINESS']; ?>");
<?php endif; ?>



//			$j("#target_mail_patern").val("confirm").change();

		});


		function fnClickUpdate(isRestore) {
			var fields_array = Object();
<?php
			foreach($this->config_datas['SALON_CONFIG_FRONT_ITEMS'] as $k1 => $d1) {
				echo 'fields_array["'.$k1.'"] = Object();';
				echo 'fields_array["'.$k1.'"]["set_label"] = $j("#sl_set_label_'.$k1.'").val();';
				if (empty($d1['set_tips'])) {
					echo 'fields_array["'.$k1.'"]["set_tips"] = "";';
				}
				else {
					echo 'fields_array["'.$k1.'"]["set_tips"] = $j("#sl_set_tips_'.$k1.'").val();';
				}
				if ($d1['is_possible_not_display']) {
 					echo 'fields_array["'.$k1.'"]["is_display"] = false;';
 					echo 'if ($j("#sl_set_display_'.$k1.'").prop("checked") ) {fields_array["'.$k1.'"]["is_display"] = true;}';
				}
				if ($d1['exist_check']) {
					echo 'fields_array["'.$k1.'"]["check"] = Array();';
					foreach ($this->default_booking_items[$k1]['check'] as $d2) {
						$checkFieldName = parent::transCheckWord($d2);
						//外せないチェックは表示しない
						if ($checkFieldName == "") {
							echo 'fields_array["'.$k1.'"]["check"].push("'.$d2.'");';
						}
						else {
 							echo 'if ($j("#sl_set_check_'.$k1.'_'.$d2.'").prop("checked") ) {fields_array["'.$k1.'"]["check"].push( $j("#sl_set_check_'.$k1.'_'.$d2.'").val());}';
						}
					}
				}
			}
?>

<?php if ($this->config_datas['SALON_CONFIG_STAFF_HOLIDAY_SET'] == Salon_Config::SET_STAFF_REVERSE) : ?>
			var config_is_doSort = null;
			if ($j("#config_is_doSort").prop("checked") ) config_is_doSort = "checked";
<?php endif; ?>


			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slconfigbooking",
					dataType : "json",
					data: {
						"config_menu_column":$j("input[name=config_menu_column]:checked").val()
						,"config_menu_type":$j("input[name=config_menu_type]:checked").val()
						,"config_before_title":$j("#config_before_title").val()
						,"config_holiday_display":$j("#config_holiday_display").val()
						,"config_special_onbusiness_display":$j("#config_special_onbusiness_display").val()
						,"config_fields":fields_array
						,"config_restore":isRestore
<?php if ($this->config_datas['SALON_CONFIG_STAFF_HOLIDAY_SET'] == Salon_Config::SET_STAFF_REVERSE) : ?>
						,"config_onbusiness_display":$j("#config_onbusiness_display").val()
						,"config_doSort" :config_is_doSort
<?php endif; ?>
<?php if ( SALON_FOR_THEME
		|| isset($this->config_datas['SALON_CONFIG_DO_NEW_FUNCTION'])) : ?>
,"config_pc_back_color":$j("#config_pc_back_color").val()
,"config_pc_event_color" : $j("#config_pc_event_color").val()
,"config_pc_event_line_color" : $j("#config_pc_event_line_color").val()
,"config_pc_selected_back_color" : $j("#config_pc_selected_back_color").val()
,"config_pc_unselected_back_color" : $j("#config_pc_unselected_back_color").val()
,"config_pc_holiday_color" : $j("#config_pc_holiday_color").val()
,"config_pc_onbusiness_color" : $j("#config_pc_onbusiness_color").val()
,"config_pc_focus_color" : $j("#config_pc_focus_color").val()
<?php endif; ?>
						,"nonce":"<?php echo $this->nonce; ?>"
						,"menu_func":"Configbooking_Edit"

					},
					success: function(data) {
						if (data.status == "Error" ) {
							alert(data.message);
							return false;
						}
						else {
							alert(data.message);
							location.reload();
						}
			        },
					error:  function(XMLHttpRequest, textStatus){
						alert (textStatus);
						return false;
					}
			 });
		}

		<?php //parent::echoCheckClinet(array('chk_required','num','lenmax','chkMail')); ?>



	</script>


	<h2 id="sl_admin_title"><?php _e('Reservation Screen',SL_DOMAIN); ?></h2>
	<?php echo parent::echoShortcode(); ?>
	<div id="salon_button_div" >
	<input id="button_update" type="button" value="<?php _e('Update',SL_DOMAIN); ?>"/>

	<input id="button_restore" type="button" value="<?php _e('Restore',SL_DOMAIN); ?>" />
	</div>

	<div id="data_detail" >
		<div id="config_menu_column" class="config_item_wrap" >
			<input id="config_menu_column1" name="config_menu_column"  type="radio" value="1" />
			<label for="config_menu_column1"><?php _e('1 column',SL_DOMAIN); ?></label>
			<input id="config_menu_column2" name="config_menu_column" type="radio" value="2" />
			<label for="config_menu_column2"><?php _e('2 column',SL_DOMAIN); ?></label>
		</div>
		<div id="config_menu_type" class="config_item_wrap" >
			<input id="config_menu_type_checkbox" name="config_menu_type" type="radio" value="<?php echo Salon_Category::CHECK_BOX; ?>" />
			<label for="config_menu_type_checkbox"><?php _e('Check Box',SL_DOMAIN); ?></label>
			<input id="config_menu_type_radio" name="config_menu_type" type="radio" value="<?php echo Salon_Category::RADIO; ?>" />
			<label for="config_menu_type_radio"><?php _e('Radio Button',SL_DOMAIN); ?></label>
		</div>
		<textarea id="config_before_title" ></textarea>
		<input type="text" id="config_holiday_display" />
		<input type="text" id="config_special_onbusiness_display" />
<?php if ($this->config_datas['SALON_CONFIG_STAFF_HOLIDAY_SET'] == Salon_Config::SET_STAFF_REVERSE) : ?>
		<input type="text" id="config_onbusiness_display" />
		<div id="config_is_doSort_wrap" class="config_item_wrap" >
			<input id="config_is_doSort" type="checkbox"  style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Config::USER_LOGIN_OK; ?>" />
		</div>
<?php endif; ?>

<?php if ( SALON_FOR_THEME
			|| isset($this->config_datas['SALON_CONFIG_DO_NEW_FUNCTION'])) : ?>
		<div  class="sl_color_warp" >
			<input id="config_pc_back_color" type="text" class="color-picker" value="<?php echo $this->config_datas['SALON_CONFIG_PC_BACK_COLOR'];?>" >
		</div>
		<div  class="sl_color_warp" >
			<input id="config_pc_event_color" type="text" class="color-picker" value="<?php echo $this->config_datas['SALON_CONFIG_PC_EVENT_COLOR'];?>" >
		</div>
		<div  class="sl_color_warp" >
			<input id="config_pc_event_line_color" type="text" class="color-picker" value="<?php echo $this->config_datas['SALON_CONFIG_PC_EVENT_LINE_COLOR'];?>" >
		</div>
		<div  class="sl_color_warp" >
			<input id="config_pc_selected_back_color" type="text" class="color-picker" value="<?php echo $this->config_datas['SALON_CONFIG_PC_SELECTED_BACK_COLOR'];?>" >
		</div>
		<div  class="sl_color_warp" >
			<input id="config_pc_unselected_back_color" type="text" class="color-picker" value="<?php echo $this->config_datas['SALON_CONFIG_PC_UNSELECTED_BACK_COLOR'];?>" >
		</div>
		<div  class="sl_color_warp" >
			<input id="config_pc_holiday_color" type="text" class="color-picker" value="<?php echo $this->config_datas['SALON_CONFIG_PC_HOLIDAY_COLOR'];?>" >
		</div>
		<div  class="sl_color_warp" >
			<input id="config_pc_onbusiness_color" type="text" class="color-picker" value="<?php echo $this->config_datas['SALON_CONFIG_PC_ONBUSINESS_COLOR'];?>" >
		</div>
		<div  class="sl_color_warp" >
			<input id="config_pc_focus_color" type="text" class="color-picker" value="<?php echo $this->config_datas['SALON_CONFIG_PC_FOCUS_COLOR'];?>" >
		</div>
		<?php endif; ?>

		<div id="config_fornt_item_wrap" >
		<?php
			$restoreDisplay = __('Restore',SL_DOMAIN);
			$front_items = $this->config_datas['SALON_CONFIG_FRONT_ITEMS'];
			$setLabelDisplay = __('Display this field',SL_DOMAIN);
			$setLabelFieldName = __('Display Field\'s  Name',SL_DOMAIN);
			$setLabelTips = __('Tips',SL_DOMAIN);
			$setLabelCheck = __('Deatails of check',SL_DOMAIN);
			$setHeaderCaption =  __('Setting of fields',SL_DOMAIN);
			$setHeaderTitleName =  __('Field\'s Name',SL_DOMAIN);
			$setHeaderTitleDetail =  __('Details',SL_DOMAIN);

			echo <<<EOT0
			<table >
			<caption>{$setHeaderCaption}</caption>
			<thead><tr><th>{$setHeaderTitleName}</th>
				<th>{$setHeaderTitleDetail}</th></tr></thead>
			<tbody>
EOT0;
			foreach($front_items as $k1 => $d1) {
				echo '<tr>';
				if (isset($d1['remark']) && !empty($d1['remark'])) {
					echo "<th>{$d1['label']}<span class='small' style='color:red;text-align:left;' >{$d1['remark']}</span></th>";
				}
				else {
					echo "<th>{$d1['label']}</th>";
				}
				echo <<< EOT
				<td>
				<div id="sl_booking_items_col2_{$k1}" >
				<ol>
				<li>
					<label>{$setLabelFieldName}</label>
					<input type="text" id="sl_set_label_{$k1}" value="{$d1['set_label']}" />
				</li>
EOT;
				if (!empty($d1['set_tips'])) {
					echo <<< EOT2
					<li>
						<label>{$setLabelTips}</label>
						<input type="text" id="sl_set_tips_{$k1}" value="{$d1['set_tips']}" />
					</li>
EOT2;
				}
				if ($d1['is_possible_not_display']) {
					$checked = "";
					if ($d1['is_display']) {
						$checked = "checked";
					}

					echo <<<EOT3
				<li>
					<label for="sl_set_displayl_{$k1}"  >{$setLabelDisplay}</label>
					<input id="sl_set_display_{$k1}" {$checked} type="checkbox"   />
EOT3;
				}
				if ($d1['exist_check']) {
					echo <<<EOT4
				<li>
					<label for="sl_set_displayl_{$k1}"  >{$setLabelCheck}</label>
<div>
EOT4;
					foreach ($this->default_booking_items[$k1]['check'] as $d2) {
						$checked = "";
						if (array_search($d2,$d1['check']) !== false ) {
							$checked = "checked";
						}
						$checkFieldName = parent::transCheckWord($d2);
						if ($checkFieldName != "") {
							echo <<<EOT5
							<input id="sl_set_check_{$k1}_{$d2}" {$checked} value="{$d2}" type="checkbox"   />
							<label for="sl_set_check_{$k1}_{$d2}"  >{$checkFieldName}</label>
EOT5;
						}
					}
echo "</div>";
				}
				echo <<<EOT7
				<li style="list-style:none;width:auto;text-align:left;margin:0px;margin-bottom:20px;">
					<input style="float:none;margin:0px;" class="sl_restore_button" id="sl_restore_{$k1}"  type="button" value="{$restoreDisplay}" />
				</li>
EOT7;
				echo <<<EOT6

				</ol>
				</div>
				</td>
				</tr>
EOT6;
			}
		?>
		</tbody></table>
		</div>
		<div class="spacer"></div>

	</div>

<?php
	}	//show_page
}		//class


<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Item_Page extends Salon_Page {

	private $branch_column = 3;

	private $set_items = null;


	private $branch_datas = null;
	private $position_datas = null;
	private $staff_datas = null;


	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
// 		if ($is_multi_branch ) {
// 			$this->set_items = array('item_name','short_name','branch_cd','minute','price','remark','exp_from','exp_to','all_flg');
// 		}
// 		else {
// 			$this->set_items = array('item_name','short_name','minute','price','remark','exp_from','exp_to','all_flg');
// 		}

	}

	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
	}

	public function set_check_staff_data ($datas) {
		$this->staff_datas = $datas;
	}


	public function show_page() {
		if ($this->isSalonAdmin() ) {
			$this->set_items = array('item_name','short_name','branch_cd','minute','price','remark','exp_from','exp_to','all_flg');
		}
		else {
			$this->set_items = array('item_name','short_name','minute','price','remark','exp_from','exp_to','all_flg');
		}
?>

<script type="text/javascript">
		var $j = jQuery


		var target;
		var save_k1 = "";
		var save_all_flg = false;

		var staff_items = Array();
		<?php  //[2014/06/22]ひとつしかないin_itemsのスタッフの情報を設定する。
				//メニュのALLFLGをはずしたときにチェックする。何もできないスタッフができないように
			if ($this->staff_datas) {
				$res = array();
				$edit_res = array();
				foreach($this->staff_datas as $k1 => $d1 ) {
					if ($d1['in_items'] !== "" ) {
						$item_array = explode(',',$d1['in_items']);
						if (count($item_array) == 1 ) {
							$res[] = array($item_array[0],$d1['name']);
							$edit_res[$item_array[0]] = array();
						}
					}
				}
				foreach ($res as $k1 => $d1 ) {
					$edit_res[$d1[0]][] = $d1[1];
				}
				foreach ($edit_res as $k1 => $d1 ) {
					$set = implode(',',$d1);
					echo "staff_items[$k1]= \"$set\";";
				}
			}
		?>

		<?php parent::echoClientItem($this->set_items); //for only_branch?>
		<?php parent::set_datepicker_date(); ?>

		$j(document).ready(function() {
			<?php parent::echoSetItemLabel(); ?>
			<?php parent::echoCommonButton();			//共通ボタン	?>
			<?php  parent::set_datepickerDefault(false,true); ?>
			<?php  parent::set_datepicker("exp_from",true); ?>
			<?php  parent::set_datepicker("exp_to",true); ?>
			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slitem",
				<?php parent::echoDataTableLang(); ?>
				<?php //[20131110]ver 1.3.1 ソートモードにしない ↓のbSortをfalseに
 					parent::echoTableItem(array('item_name','branch_cd','display_sequence','price','remark','branch_name_table'),false,$this->isSalonAdmin(),"120px",true);
				//for only_branch?>
				"bSort":false,
				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Item_Init" } )
				},



				"fnDrawCallback": function () {
					<?php parent::echoEditableCommon("item"); ?>
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					<?php parent::echoDataTableSelecter("name"); ?>
					<?php //[20131110]ver 1.3.1
						$seq_col = $this->branch_column;
						//if ($this->is_multi_branch) $seq_col = $this->branch_column+1;
						if ($this->isSalonAdmin()) $seq_col = $this->branch_column+1;
						parent::echoDataTableDisplaySequence($seq_col);
						//[20131110]ver 1.3.1 ?>
					<?php //if ($this->is_multi_branch ) parent::echoDataTableBranchData($this->branch_column,$this->branch_datas); ?>
					<?php
						if ($this->isSalonAdmin() ) {
							parent::echoDataTableBranchData($this->branch_column,$this->branch_datas);
						}
					?>


				},
			});
		});


		<?php //parent::echoDataTableSeqUpdateRow("item","item_cd",$this->is_multi_branch); ?>	//[20131110]ver 1.3.1
		<?php parent::echoDataTableSeqUpdateRow("item","item_cd",$this->isSalonAdmin()); ?>	//[20131110]ver 1.3.1

		function fnSelectRow(target_col) {
			fnDetailInit();



			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();

			save_k1 = position[0];

			$j("#name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['name']));
			$j("#short_name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['short_name']));
			$j("#branch_cd").val(setData['aoData'][position[0]]['_aData']['branch_cd']);
			$j("#minute").val(setData['aoData'][position[0]]['_aData']['minute']);
			$j("#price").val(setData['aoData'][position[0]]['_aData']['price']);
			$j("#remark").val( htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['remark']));
			<?php //[2014/06/22] ?>
			var exp_from = setData['aoData'][position[0]]['_aData']['exp_from'];
			if (exp_from.indexOf("0000") != -1 ) exp_from = "";
			$j("#exp_from").val(exp_from);
			var exp_to = setData['aoData'][position[0]]['_aData']['exp_to'];
			if (exp_to.indexOf("2099") != -1 ) exp_to = "";
			$j("#exp_to").val(exp_to);
			save_all_flg = false;
			if (setData['aoData'][position[0]]['_aData']['all_flg'] == "<?php echo Salon_Config::ALL_ITEMS_YES; ?>" ) {
				$j("#all_flg").attr("checked",true);
				save_all_flg = true;
			}
			else $j("#all_flg").attr("checked",false);
			<?php //[2014/06/22] ?>
			$j("#button_update").removeAttr("disabled");
			$j("#button_clear").show();

			$j("#data_detail").show();
			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN);  ?>");



		}
		<?php parent::echoDataTableEditColumn("item"); ?>

		<?php
			$chk_string = "if(staff_items[target_cd]){alert(\"".__('This menu checked as the following staff member.\nIf you will remove this menu,\nplease update the menu of the following staff member.\n',SL_DOMAIN)."\\n\"+staff_items[target_cd]);return false;}";
			parent::echoDataTableDeleteRow("item",'',true,'',$chk_string); ?>

		function fnClickAddRow(operate) {
			if ( ! checkItem("data_detail") ) return false;
			var item_cd = "";
			var display_sequence = 0;
			var setData = target.fnSettings();
			if ( save_k1 !== ""  ) {
				item_cd = setData['aoData'][save_k1]['_aData']['item_cd'];
				display_sequence = setData['aoData'][save_k1]['_aData']['display_sequence'];
			}
			var is_change_all_flg = save_all_flg != $j('#all_flg').is(':checked') ? <?php echo Salon_Config::ALL_ITEMS_CHANGE_YES; ?> : <?php echo Salon_Config::ALL_ITEMS_CHANGE_NO; ?>;
			<?php //チェックをはずされたときは、該当メニューのみのスタッフがいないかチェック ?>
			if (operate  !="inserted" && is_change_all_flg == <?php echo Salon_Config::ALL_ITEMS_CHANGE_YES; ?> && !$j('#all_flg').is(':checked') ){
				if (staff_items[item_cd] ) {
					alert("<?php _e('This menu checked as the following staff member.\nIf you will uncheck \"All staff member can treat\",\nplease update the menu of the following staff member.\n',SL_DOMAIN); ?>\n"+staff_items[item_cd]);
					return false;
				}
			}
		<?php //if ($this->is_multi_branch == false ) : //for only_branch ?>
		<?php if ($this->isSalonAdmin() == false ) : //for only_branch ?>
			if (operate  =="inserted") $j("#branch_cd").val("<?php echo $this->get_default_brandh_cd();?>");
		<?php endif; ?>
			var all_flg = null;
			if ($j("#all_flg").prop("checked")) all_flg = "checked";
			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slitem",
					dataType : "json",

					data: {
						"item_cd":item_cd,
						"no":save_k1,
						"type":operate,
						"name":$j("#name").val(),
						"short_name":$j("#short_name").val(),
						"branch_cd":$j("#branch_cd").val(),
						"minute":$j("#minute").val(),
						"price":$j("#price").val(),
						"remark":$j("#remark").val(),
						"display_sequence":display_sequence,
						"photo":'',
						"exp_from":$j("#exp_from").val(),
						"exp_to":$j("#exp_to").val(),
						"all_flg":all_flg,
						"is_change_all_flg":is_change_all_flg,
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Item_Edit"

					},

					success: function(data) {
						if (data === null || data.status == "Error" ) {
							alert(data.message);
						}
						else {
							if (operate =="inserted" ) {
								target.fnAddData( data.set_data );
							}
							else {
								target.fnUpdate( data.set_data ,parseInt(save_k1) );
							}
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
			$j("#all_flg").attr("checked",true);

			$j("#button_update").attr("disabled", "disabled");


			save_k1 = "";
			<?php parent::echo_clear_error(); ?>

		}

	<?php parent::echoCheckClinet(array('chk_required','zenkaku','lenmax','num','chkDate')); ?>
	<?php parent::echoColumnCheck(array('chk_required','lenmax','num')); ?>



	</script>

	<h2 id="sl_admin_title"><?php _e('Menu Information',SL_DOMAIN); ?></h2>
	<?php echo parent::echoShortcode(); ?>
	<div id="salon_button_div" >
	<input id="button_insert" type="button" value="<?php _e('Add',SL_DOMAIN); ?>"/>
	<input id="button_update" type="button" value="<?php _e('Update',SL_DOMAIN); ?>"/>
	<input id="button_clear" type="button" value="<?php _e('Clear',SL_DOMAIN); ?>"/>
	<input id="button_detail" type="button"/>
	</div>


	<div id="data_detail" >
		<input type="text" id="name" value="" />
		<input type="text" id="short_name" value="" />
<?php //if ($this->is_multi_branch ): //for only_branch?>
<?php if ($this->isSalonAdmin() ): //for only_branch?>
		<select name="branch_cd" id="branch_cd" >
			<option value=""><?php _e('please select',SL_DOMAIN); ?></option>
		<?php
			foreach($this->branch_datas as $k1 => $d1 ) {
				echo '<option value="'.$d1['branch_cd'].'">'.$d1['name'].'</option>';
			}
		?>
		</select>
<?php else: ?>
		<input name="branch_cd" id="branch_cd" type="hidden" >
<?php endif; ?>
		<?php parent::echoMinuteSelect('minute'); ?>
		<input type="text" id="price" value="" />
<?php //Ver 1.4.1 ?>

<?php /*?>		<div id="_multi_item_wrap" >
			<input type="text" id="sp_date" style="width:100px;margin-right:0px;"  />
			<INPUT type="radio"  id="sp_date_radio_open"  name="sp_date_radio"  style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Status::OPEN; ?>">
			<label for="sp_date_radio_open" style="margin:5px;text-align:left;width:50px;"><?php _e('On Business',SL_DOMAIN); ?></label>
			<INPUT type="radio" id="sp_date_radio_close"  name="sp_date_radio"  style="width:16px;margin:3px 5px 0px 10px;" value="<?php echo Salon_Status::CLOSE; ?>">
			<label for="sp_date_radio_close" style="margin:5px;text-align:left;width:50px;"><?php _e('Special Absence',SL_DOMAIN); ?></label>
			<input id="button_sp_date_insert" type="button" class="sl_button" value="<?php _e('Add',SL_DOMAIN); ?>" style="width:50px;margin-right:0px;"/>
		</div>
<?php */?>

        <input type="text" id="exp_from"/>
        <input type="text" id="exp_to" />
        <div id="all_flg_wrap" class="config_item_wrap" >
			<input id="all_flg" type="checkbox"  value="<?php echo Salon_Config::ALL_ITEMS_YES; ?>"/>
		</div>


		<textarea id="remark"  ></textarea>
		<div class="spacer"></div>

	</div>

	<table class="flexme" id='lists'>
	<thead>
	</thead>
	</table>
<?php
	}	//show_page
}		//class


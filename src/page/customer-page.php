<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Customer_Page extends Salon_Page {

	private $branch_column = 4;
	private $set_items = null;

	private $branch_datas = null;

	private $customer_rank_datas = null;


	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
// 		if ($is_multi_branch ) {
// 			$this->set_items = array('first_name','last_name','branch_cd','zip','address','customer_tel','customer_mobile','customer_mail','user_login','rank_patern','remark');
// 		}
// 		else {
// 			$this->set_items = array('first_name','last_name','zip','address','customer_tel','customer_mobile','customer_mail','user_login','rank_patern','remark');
// 		}

	}

	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
	}


	public function set_customer_rank_datas ($datas) {
		$this->customer_rank_datas = $datas;
	}

	public function set_current_user_branch_cd($branch_cd) {
		$this->current_user_branch_cd = $branch_cd;
	}

	public function show_page() {
		if ($this->isSalonAdmin() ) {
			$this->set_items = array('first_name','last_name','branch_cd','zip','address','customer_tel','customer_mobile','customer_mail','user_login','rank_patern','remark');
		}
		else {
			$this->set_items = array('first_name','last_name','zip','address','customer_tel','customer_mobile','customer_mail','user_login','rank_patern','remark');
		}
?>

<script type="text/javascript">
		var $j = jQuery


		var target;
		var save_k1 = "";
<?php //		var save_user_login_old = ""; ?>

		<?php parent::echoClientItem($this->set_items); //for only_branch?>
		<?php Salon_Country::echoZipTable(); //for only_branch?>

		$j(document).ready(function() {

			<?php parent::echoSetItemLabel(); ?>

			<?php Salon_Country::echoZipFunc("zip","address");	?>
			<?php parent::echoCommonButton();			//共通ボタン	?>


			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slcustomer",
				<?php parent::echoDataTableLang(); ?>
				<?php
					if ($this->config_datas['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN )
						$seq = array('last_name','first_name','branch_cd','remark','branch_name_table');
					else
						$seq = array('first_name','last_name','branch_cd','remark','branch_name_table');
// 					parent::echoTableItem($seq,false,$this->is_multi_branch,'200px');
					parent::echoTableItem($seq,false,$this->isSalonAdmin(),'200px');
					?>

				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Customer_Init" } )
				},
				"fnDrawCallback": function () {
					<?php parent::echoEditableCommon("customer",array("ID","first_name","last_name"),'var setData = target.fnSettings();var position = target.fnGetPosition( td );if (!setData["aoData"][position[0]]["_aData"]["customer_cd"]) { alert("'.__('this user not registerd',SL_DOMAIN).'"); return false; }'); ?>
				},
<?php	//iDisplayIndexFullがデータ上のindexでidisplayIndexがページ上のindexとなる　?>
		//aDataが実際のデータで、nRowがTrオブジェクト
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					<?php parent::echoDataTableSelecter("first_name",false); ?>
					var record_box = $j("<input>")
					.attr("type","button")
					.attr("id","sl_record_btn_"+iDataIndex)
					.attr("name","sl_record_"+iDataIndex)
					.attr("class","sl_button sl_button_short")
					.attr("value","<?php _e('Customer Record',SL_DOMAIN); ?>")
					.click(function(event) {
						fnClickRecordRow(this.parentNode);
					});
					if (aData.branch_cd ) {
						element.append(sel_box);
						element.append(del_box);
						element.append(record_box);
					}
					else {
						element.empty();
						element.append(sel_box);
					}

					<?php
					//if ($this->is_multi_branch ) parent::echoDataTableBranchData($this->branch_column,$this->branch_datas);
					if ($this->isSalonAdmin() ) {
						parent::echoDataTableBranchData($this->branch_column,$this->branch_datas);
					}
					?>
				}

			});

		});
		function fnClickRecordRow(target_col) {
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();

			var targetUrl =   "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin.php?page=salon_record&cu="
				+ setData['aoData'][position[0]]['_aData']['user_login'];
			location.assign(targetUrl);
		}

		<?php //taregt_colはtdが前提 ?>
		function fnSelectRow(target_col) {
			fnDetailInit();

			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();

			save_k1 = position[0];

//			$j("#name").val(setData['aoData'][position[0]]['_aData']['name']);
			$j("#first_name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['first_name']));
			$j("#last_name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['last_name']));
			$j("#branch_cd").val(setData['aoData'][position[0]]['_aData']['branch_cd']);
			$j("#address").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['address']));
			$j("#zip").val(setData['aoData'][position[0]]['_aData']['zip']);
			$j("#tel").val(setData['aoData'][position[0]]['_aData']['tel']);
			$j("#mobile").val(setData['aoData'][position[0]]['_aData']['mobile']);
			$j("#mail").val(setData['aoData'][position[0]]['_aData']['mail']);
			$j("#rank_patern_cd").val(setData['aoData'][position[0]]['_aData']['rank_patern_cd']);
			$j("#remark").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['remark']));
			if ( setData['aoData'][position[0]]['_aData']['customer_cd'] ) {
				$j("#button_update").removeAttr("disabled");
				$j("#button_insert").attr("disabled","disabled");
			}
			else {
				$j("#button_insert").removeAttr("disabled");
				$j("#button_update").attr("disabled","disabled");
			}
			$j("#button_clear").show();
<?php //	save_user_login_old = setData['aoData'][position[0]]['_aData']['user_login']; ?>
			$j("#user_login").val(setData['aoData'][position[0]]['_aData']['user_login']);

			$j("#data_detail").show();
			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN); ?>");

			$j("#mail").attr("readonly", true);
			$j("#user_login").attr("readonly", true);



		}


		<?php parent::echoDataTableEditColumn("customer","ID","",'if (!target_cd) { alert("'.__('this user not registerd',SL_DOMAIN).'"); return false; }'); ?>
		<?php parent::echoDataTableDeleteRow("customer","customer",false); ?>

		function fnClickAddRow(operate) {
			if ( ! checkItem("data_detail") ) return false;
			var customer_cd = "";
			var ID = "";
			if ( save_k1 !== "" ) {
				var setData = target.fnSettings();
				customer_cd = setData['aoData'][save_k1]['_aData']['customer_cd'];
<?php /*?>
				if ( save_user_login_old == $j("#user_login").val()  ) {
					ID = setData['aoData'][save_k1]['_aData']['ID'];
				}
<?php */?>
				ID = setData['aoData'][save_k1]['_aData']['ID'];
			}
		<?php //if ($this->is_multi_branch == false ) : //for only_branch ?>
		<?php if ($this->isSalonAdmin() == false ) : //for only_branch ?>
				if (operate  =="inserted") $j("#branch_cd").val("<?php echo $this->get_default_brandh_cd();?>");
		<?php endif; ?>
<?php //グローバル変数 target はどうにかならん？ ?>
			 $j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slcustomer",
					dataType : "json",
					data: {
						"customer_cd":customer_cd,
						"ID":ID,
						"no":save_k1,
						"type":operate,
						"last_name":$j("#last_name").val(),
						"first_name":$j("#first_name").val(),
						"branch_cd":$j("#branch_cd").val(),
						"address":$j("#address").val(),
						"remark":$j("#remark").val(),
						"user_login":$j("#user_login").val(),
						"zip":$j("#zip").val(),
						"tel":$j("#tel").val(),
						"mobile":$j("#mobile").val(),
						"mail":$j("#mail").val(),
						"rank_patern_cd":$j("#rank_patern_cd").val(),
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Customer_Edit"
					},

					success: function(data) {
						if (data === null || data.status == "Error" ) {
							alert(data.message);
						}
						else {

<?php //[TODO]salonのみの追加＝WPで登録のみの追加の時に、行を増えないように ?>
<?php //							if ( (operate =="inserted")  && (save_user_login_old != $j("#user_login").val())) { ?>
							if ( (operate =="inserted")  ) {
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
						alert ('<?php echo salon_component::getMsg('E401'); ?>['+textStatus+']');
					}
			 });
		}


		function fnDetailInit() {
			$j("#data_detail input[type=\"text\"]").val("");
			$j("#data_detail select").val("");
			$j("#data_detail textarea").val("");

			$j("#button_update").attr("disabled", true);
			$j("#button_insert").attr("disabled", false);


			$j("#uploadedImageView").html("");

			save_k1 = "";
<?php //			save_user_login_old = ""; ?>

			$j("#mail").attr("readonly", false);
			$j("#user_login").attr("readonly", false);

			<?php parent::echo_clear_error(); ?>

		}


	<?php parent::echoCheckClinet(array('chk_required','zenkaku','chkZip','chkTel','chkMail','chkTime','chkDate','lenmax','reqOther')); ?>
	<?php parent::echoColumnCheck(array('chk_required','lenmax')); ?>

	</script>

	<h2 id="sl_admin_title"><?php _e('Customer Information',SL_DOMAIN); ?></h2>
	<?php echo parent::echoShortcode(); ?>
	<input id="upload_image" type="hidden"  value="" />
	<div id="salon_button_div" >
	<input id="button_insert" type="button" value="<?php _e('Add',SL_DOMAIN); ?>"/>
	<input id="button_update" type="button" value="<?php _e('Update',SL_DOMAIN); ?>"/>
	<input id="button_clear" type="button" value="<?php _e('Clear',SL_DOMAIN); ?>"/>
	<input id="button_detail" type="button" />
	</div>

	<div id="data_detail" >
<?php if ($this->config_datas['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN ): ?>
		<input type="text" id="last_name" value="" />
		<input type="text" id="first_name" value="" />
<?php else: ?>
		<input type="text" id="first_name" value="" />
		<input type="text" id="last_name" value="" />
<?php endif; ?>



<?php //if ($this->is_multi_branch ): //for only_branch?>
<?php if ($this->isSalonAdmin() ): //for only_branch?>
		<select name="branch_cd" id="branch_cd" >
			<option value=""><?php _e('select please',SL_DOMAIN); ?></option>
		<?php
			foreach($this->branch_datas as $k1 => $d1 ) {
				echo '<option value="'.$d1['branch_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
			}
		?>
		</select>
<?php else: ?>
		<input name="branch_cd" id="branch_cd" type="hidden" >
<?php endif; ?>
		<input type="text" id="zip"/>
		<textarea id="address" ></textarea>
		<input type="text" id="tel"/>
		<input type="text" id="mobile"/>
		<input type="text" id="mail"/>
		<input type="text" id="user_login" value="" />
		<?php parent::echoRankPatern( $this->customer_rank_datas); ?>
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


<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Staff_Page extends Salon_Page {

	private $branch_column = 0;
	private $position_column = 0;
	private $set_items = null;

	private $branch_datas = null;
	private $position_datas = null;


	private $item_datas = null;

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
// 		if ($is_multi_branch ) {
// 			$this->branch_column = 4;
// 			$this->position_column = 5;
// 			$this->set_items = array('first_name','last_name','branch_cd','position_cd','zip','address','tel','mobile','mail','user_login','remark','employed_day','leaved_day','duplicate_cnt_staff','item_cds_set','memo');
// 		}
// 		else {
// 			$this->branch_column = 3;
// 			$this->position_column = 4;
// 			$this->set_items = array('first_name','last_name','position_cd','zip','address','tel','mobile','mail','user_login','remark','employed_day','leaved_day','duplicate_cnt_staff','item_cds_set','memo');
// 		}

	}

	public function get_branch_column() { return $this->branch_column; }
	public function get_position_column() { return $this->position_column; }
	public function get_set_items() { return $this->set_items; }

	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
	}

	public function set_position_datas ($position_datas) {

		$this->position_datas = $position_datas;
	}


	public function set_item_datas($item_datas) {
		$this->item_datas = $item_datas;
	}


	public function show_page() {
		if ($this->isSalonAdmin() ) {
			$this->branch_column = 4;
			$this->position_column = 5;
			$this->set_items = array('first_name','last_name','branch_cd','position_cd','zip','address','tel','mobile','mail','user_login','remark','employed_day','leaved_day','duplicate_cnt_staff','item_cds_set','memo');
		}
		else {
			$this->branch_column = 3;
			$this->position_column = 4;
			$this->set_items = array('first_name','last_name','position_cd','zip','address','tel','mobile','mail','user_login','remark','employed_day','leaved_day','duplicate_cnt_staff','item_cds_set','memo');
		}
?>

<script type="text/javascript">
		var $j = jQuery

		var target;
		var save_k1 = "";
		var save_user_login_old = "";
		//[photo]
		var insert_photo = false;	//登録または削除しているのに、確定していないかを判断
		var delete_photo = false;
		var insert_photo_ids = Array();

		function delete_photo_datas() {
			//写真を登録したけどやめてしまった場合
			$j.ajax({
				type: "post",
				url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slphoto",
				dataType : "json",
					data: {
						"photo_id":insert_photo_ids.join(","),
						"type":"deleted",
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Photo_Edit"
					},
				success: function(data) {
					if (data === null || data.status == "Error" ) {
						alert(data.message);
					}
				},
				error:  function(XMLHttpRequest, textStatus){
					alert (textStatus);
				}

			 });
		}

		//[photo]

		<?php parent::echoClientItem($this->set_items);  ?>
		<?php parent::set_datepicker_date(); ?>
		<?php Salon_Country::echoZipTable(); //for only_branch?>

		$j(document).ready(function() {

//［PHOTO]ここから
			Dropzone.autoDiscover = false;
			$j("#image_drop_area").dropzone({
				url: "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slphoto&menu_func=Photo_Edit&type=inserted&nonce=<?php echo $this->nonce; ?>"
				,maxFilesize:<?php echo SALON_MAX_FILE_SIZE; ?>
				,init: function() {
					$j(this.element).addClass("dropzone");

					this.on("addedfile",function(file) {
						$j(file.previewElement).addClass("ui-state-default");
					});
					this.on("success", function(file, text) {
						var res = JSON.parse(text);
						if (res.status == "Ok" ) {
							$j(file.previewElement).attr("id","photo_id_"+res.photo_id);
							$j(".lightbox").colorbox({rel:"staffs",width:"<?php echo SALON_COLORBOX_SIZE; ?>", height:"<?php echo SALON_COLORBOX_SIZE; ?>"});
							insert_photo = true;
							insert_photo_ids.push($j(file.previewElement).attr("id"));
						}
						else {
							alert(res.message);
						}
					});
					this.on("removedfile",function(file) {
		<?php 			//実際の削除はUPDATEまたはDELETEときに行う ?>
							delete_photo = true;
					});
				}
				,accept: function(file, done) {
					if(file.name.match(/\.(jpg|png|gif)$/i))  {
						done();
					}
					else {
						this.removeFile(file);
						alert("<?php _e('FILE TYPE ERROR',SL_DOMAIN); ?>");
					}
				}
				,error: function(file, message) {
					this.removeFile(file);
					alert(message);
				}
				,addRemoveLinks:true
				,dictDefaultMessage: "<?php _e('Drop files here to upload',SL_DOMAIN); ?>"
				,dictFileTooBig: "<?php _e('File is too big ({{filesize}}MB). Max filesize: {{maxFilesize}}MB.',SL_DOMAIN); ?>"
				,dictInvalidFileType: "<?php _e('You can upload files of this type(jpg,gif,png).',SL_DOMAIN); ?>"
				,dictRemoveFile:"<?php _e('Delete',SL_DOMAIN) ?>"
				,dictFallbackMessage:"<?php _e('Your browser does not support drag&drop file uploads.',SL_DOMAIN); ?>"
			    ,dictMaxFilesExceeded: "<?php _e('You can only upload {{maxFiles}} files.',SL_DOMAIN); ?>"

			});
			$j("#image_drop_area").sortable();
//ここまで


			<?php parent::echoSetItemLabel(); ?>
			<?php Salon_Country::echoZipFunc("zip","address");	?>

			<?php  parent::set_datepickerDefault(false,true); ?>
			<?php  parent::set_datepicker("employed_day",true); ?>
			<?php  parent::set_datepicker("leaved_day",true); ?>


			$j("#user_login").change(function(){

				if ( save_user_login_old == $j("#user_login").val()  ) {
					 $j("#button_insert").attr("disabled","disabled");
					 $j("#button_update").attr("disabled",false);
				}
				else {
					 $j("#button_insert").attr("disabled",false);
					 $j("#button_update").attr("disabled",true);
				}

			});



			<?php if ($this->isSalonAdmin() ): //複数店舗の場合は、該当支店のみ有効にする?>
				$j("#branch_cd").change(function() {
					_checkEnableItems();
				});
			<?php endif ?>



			<?php parent::echoCommonButton();			//共通ボタン	?>


			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slstaff",
				<?php parent::echoDataTableLang(); ?>
				<?php

					if ($this->config_datas['SALON_CONFIG_NAME_ORDER'] == Salon_Config::NAME_ORDER_JAPAN ){
						$seq = array('last_name','first_name','branch_cd','position_name_only_table','display_sequence','remark','branch_name_table','position_name_table');
						$set_name = 'last_name';
					}
					else {
						$seq = array('first_name','last_name','branch_cd','position_name_only_table','display_sequence','remark','branch_name_table','position_name_table');
						$set_name = 'first_name';
					}
					parent::echoTableItem($seq,false,$this->isSalonAdmin(),"120px",true);
				?>
				"bSort":false,
				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Staff_Init" } )
				},
				"fnDrawCallback": function () {
					<?php
						parent::echoEditableCommon("staff",array("ID","first_name","last_name"),'var setData = target.fnSettings();var position = target.fnGetPosition( td );if (!setData["aoData"][position[0]]["_aData"]["staff_cd"]) { alert("'.__('this user not registerd',SL_DOMAIN).'"); return false; }');
					?>
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					<?php parent::echoDataTableSelecter($set_name,false); ?>
					if (aData.branch_cd && aData.staff_cd != <?php echo get_option('salon_initial_user',1); ?>) {
						element.append(sel_box);
						element.append(del_box);
					}
					else {
						element.empty();
						element.append(sel_box);
					}
					<?php //[20131110]ver 1.3.1
						$seq_col = $this->position_column+1;
						parent::echoDataTableDisplaySequence($seq_col);
						//[20131110]ver 1.3.1 ?>

					<?php if ($this->isSalonAdmin() ) parent::echoDataTableBranchData($this->branch_column,$this->branch_datas); ?>

					<?php //parent::echoDataTablePositionData($this->position_column,$this->position_datas); ?>
				}

			});




		});


		<?php parent::echoDataTableSeqUpdateRow("staff","staff_cd",$this->isSalonAdmin()); ?>	//[20131110]ver 1.3.1
<?php //taregt_colはtdが前提 ?>
		function fnSelectRow(target_col) {

			if (insert_photo || delete_photo ) {
				if (confirm("<?php _e('Photos are updated,But staff data is not inserted or updated. Continue OK ?',SL_DOMAIN); ?>") ) {
					<?php //削除は実際に更新していないのでメッセージのみ ?>
					if (insert_photo) 	delete_photo_datas();
				}
				else return;
			}
			insert_photo_ids.length = 0;
			insert_photo = false;
			delete_photo = false;


			fnDetailInit();

			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();

			save_k1 = position[0];
			$j("#last_name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['last_name']));
			$j("#first_name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['first_name']));
			$j("#branch_cd").val(setData["aoData"][position[0]]["_aData"]["branch_cd"]).change();
			$j("#position_cd").val(setData['aoData'][position[0]]['_aData']['position_cd']);

			$j("#position_cd").attr("disabled",false);
			if (setData['aoData'][position[0]]['_aData']['staff_cd']  == <?php echo get_option('salon_initial_user',1); ?> )  {
				$j("#position_cd").attr("disabled",true);
			}

			$j("#address").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['address']));
			$j("#user_login").val(setData['aoData'][position[0]]['_aData']['user_login']);
			save_user_login_old = setData['aoData'][position[0]]['_aData']['user_login'];
			$j("#zip").val(setData['aoData'][position[0]]['_aData']['zip']);
			$j("#tel").val(setData['aoData'][position[0]]['_aData']['tel']);
			$j("#mobile").val(setData['aoData'][position[0]]['_aData']['mobile']);
			$j("#mail").val(setData['aoData'][position[0]]['_aData']['mail']);
			$j("#employed_day").val(setData['aoData'][position[0]]['_aData']['employed_day']);
			$j("#leaved_day").val(setData['aoData'][position[0]]['_aData']['leaved_day']);
			$j("#remark").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['remark']));

			$j("#duplicate_cnt").val(setData['aoData'][position[0]]['_aData']['duplicate_cnt']);


			if ( setData['aoData'][position[0]]['_aData']['staff_cd'] ) {
				$j("#button_update").removeAttr("disabled");
				$j("#button_insert").attr("disabled","disabled");

				$j("#user_login").prop("disabled",false);
				if (setData['aoData'][position[0]]['_aData']['staff_cd']  == <?php echo get_option('salon_initial_user',1); ?>) {
					$j("#user_login").prop("disabled",true);
				}
			}
			else {
				$j("#button_insert").removeAttr("disabled");
				$j("#button_update").attr("disabled","disabled");
			}


			$j("#button_clear").show();

			$j("#data_detail").show();
			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN); ?>");

			var size = setData['aoData'][position[0]]['_aData']['photo_result'].length;
			for(var i = 0; i < size ; i++ ) {
				var mockFile = Array();
				mockFile['name'] = setData['aoData'][position[0]]['_aData']['photo_result'][i]['photo_name'];
				mockFile['size'] = 0;	<?php //表示しないようにCSSでdisplay:noneにしている ?>
				mockFile['photo_id'] = "photo_id_" + setData['aoData'][position[0]]['_aData']['photo_result'][i]['photo_id'];
				$j("#image_drop_area")[0].dropzone.options.addedfile.call($j("#image_drop_area")[0].dropzone, mockFile);
				$j("#image_drop_area")[0].dropzone.options.thumbnail.call($j("#image_drop_area")[0].dropzone, mockFile,setData['aoData'][position[0]]['_aData']['photo_result'][i]['photo_resize_path'],setData['aoData'][position[0]]['_aData']['photo_result'][i]['photo_path']);

			}
			$j(".lightbox").colorbox({rel:"staffs",width:"<?php echo SALON_COLORBOX_SIZE; ?>", height:"<?php echo SALON_COLORBOX_SIZE; ?>"});

			<?php //[2014/06/22] ?>
			var items_array = Array();
			if (setData['aoData'][position[0]]['_aData']['in_items'])
				items_array = setData['aoData'][position[0]]['_aData']['in_items'].split(",");
			<?php //[2014/07/25] ?>
			$j("#memo").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['memo']));


			$j("#sl_tb_items_"+$j("#branch_cd").val()+" .sl_items_set").attr("checked",false);

			for (var i = 0; i < items_array.length; i++) {
				$j("#check_"+items_array[i]).attr("checked",true);
			}




		}

		<?php
		$add_callback_process = "";
		if ($this->isSalonAdmin() ) $add_callback_process = " if (position[2] == ".$this->branch_column." ) setData['aoData'][position[0]]['_aData']['in_items'] = data.in_items;";
		?>

		<?php parent::echoDataTableEditColumn("staff","ID",$add_callback_process,'if (!target_cd) { alert("'.__('this user not registerd',SL_DOMAIN).'"); return false; }'); ?>
		<?php parent::echoDataTableDeleteRow("staff","staff",false,'"user_login":setData["aoData"][position[0]]["_aData"]["user_login"],'); ?>

		function _getFileName(file_path) {
			file_name = file_path.substring(file_path.lastIndexOf('/')+1, file_path.length);
			return file_name;
		}

		function fnClickAddRow(operate) {
			if ( ! checkItem("data_detail") ) return false;

			var staff_cd = "";
			var ID = "";

			var photo_id_array = [];

			<?php //photo ?>
			$j(".dz-preview").each(function() {
				var id = $j(this).attr('id');
				photo_id_array.push(id);
				<?php //インサート時に既存の写真を使用しているかどうかをチェックする ?>
				<?php //既存の写真の場合はコピーする ?>
			});
			<?php //photo ?>

			var photo = photo_id_array.join(",");

			var display_sequence = 0;
			var used_photo_id_array = [];
			if ( save_k1 !== "" ) {
				var setData = target.fnSettings();
				staff_cd = setData['aoData'][save_k1]['_aData']['staff_cd'];
				if ( save_user_login_old == $j("#user_login").val()  ) {
					ID = setData['aoData'][save_k1]['_aData']['ID'];
				}
				display_sequence = setData['aoData'][save_k1]['_aData']['display_sequence'];
				<?php //photo ?>
				for(var i = 0 ; i < photo_id_array.length ; i++ ) {
					for(var j = 0; j < setData['aoData'][save_k1]['_aData']['photo_result'].length ; j++ ) {
						if (photo_id_array[i] == "photo_id_" + setData['aoData'][save_k1]['_aData']['photo_result'][j]['photo_id']) {
								used_photo_id_array.push(setData['aoData'][save_k1]['_aData']['photo_result'][j]['photo_id'] + ":" +
											_getFileName(setData['aoData'][save_k1]['_aData']['photo_result'][j]['photo_path']));
							break;
						}
					}
				}
				<?php //photo ?>
			}
			var used_photo = used_photo_id_array.join(",");

			<?php //items ?>
			var tmp_items = Array();
			$j(".sl_items_set").each(function() {
				if ( $j(this).is(":checked") ) {
					tmp_items.push( $j(this).val() );
				}
			});


		<?php if ($this->isSalonAdmin() == false ) : //for only_branch ?>
			if (operate  =="inserted") $j("#branch_cd").val("<?php echo $this->get_default_brandh_cd();?>");
		<?php endif; ?>
			 $j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slstaff",
					dataType : "json",
					data: {
						"ID":ID,
						"staff_cd":staff_cd,
						"no":save_k1,
						"type":operate,
						"first_name":$j("#first_name").val(),
						"last_name":$j("#last_name").val(),
						"branch_cd":$j("#branch_cd").val(),
						"position_cd":$j("#position_cd").val(),
						"address":$j("#address").val(),
						"remark":$j("#remark").val(),
						"photo":photo,
						"used_photo":used_photo,
						"user_login":$j("#user_login").val(),
						"zip":$j("#zip").val(),
						"tel":$j("#tel").val(),
						"mobile":$j("#mobile").val(),
						"mail":$j("#mail").val(),
						"employed_day":$j("#employed_day").val(),
						"leaved_day":$j("#leaved_day").val(),
						"item_cds":tmp_items.join(","),
						"memo":$j("#memo").val(),
						"menu_func":"Staff_Edit",
						"nonce":"<?php echo $this->nonce; ?>",
						"display_sequence":display_sequence,
						"duplicate_cnt":$j("#duplicate_cnt").val()
					},

					success: function(data) {
						if (data === null || data.status == "Error" ) {
							alert(data.message);
						}
						else {
							if ( (operate =="inserted")  && (save_user_login_old != $j("#user_login").val())) {
								target.fnAddData( data.set_data );
							}
							else {
								target.fnUpdate( data.set_data ,parseInt(save_k1) );
							}

							insert_photo_ids.length = 0;
							insert_photo = false;
							delete_photo = false;

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
			$j("#position_cd").attr("disabled",false);
			$j("#user_login").attr("disabled",false);

			$j("#duplicate_cnt").val("0");

			<?php if ($this->isSalonAdmin() ): ?>
				_checkEnableItems();
			<?php endif ?>

			fnPhotoClear();
			save_k1 = "";
			save_user_login_old = "";

			<?php parent::echo_clear_error(); ?>

		}

		function _checkEnableItems() {
			$j(".sl_items_set").attr("disabled",true);
			$j(".sl_items_set").attr("checked",false);
			//クリアしたときのみ、デフォルト状態を設定する。
			if ($j("#branch_cd").val()) {
				$j("#sl_tb_items_"+$j("#branch_cd").val()+" .sl_items_set").each(function() {
					$j(this).attr("checked",false);
					if( $j(this).next().val() == <?php echo Salon_Config::ALL_ITEMS_YES; ?> ){
						$j(this).attr("checked",true);
					}

				});
				$j("#sl_tb_items_"+$j("#branch_cd").val()+" .sl_items_set").attr("disabled",false);
			}
		}

		function fnPhotoClear (){
			$j("#image_drop_area").empty();
			$j("#image_drop_area").append("<div class=\"drag-drop-info\"><?php _e('Photos of staff member.<br> Drop files here or click here and select files',SL_DOMAIN);?></div>");
		}


	<?php parent::echoCheckClinet(array('chk_required','zenkaku','chkZip','chkTel','chkMail','chkTime','chkDate','lenmax','reqOther','num','reqCheck')); ?>
	<?php parent::echoColumnCheck(array('chk_required','lenmax')); ?>

	</script>

	<h2 id="sl_admin_title"><?php _e('Staff Information',SL_DOMAIN); ?></h2>
	<?php echo parent::echoShortcode(); ?>
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
<?php if ($this->isSalonAdmin() ): //for only_branch?>
		<select name="branch_cd" id="branch_cd" >
			<option value=""><?php _e('please select',SL_DOMAIN); ?></option>
		<?php
			foreach($this->branch_datas as $k1 => $d1 ) {
				echo '<option value="'.$d1['branch_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
			}
		?>
		</select>
<?php else: ?>
		<input name="branch_cd" id="branch_cd" type="hidden" >
<?php endif; ?>
		<select name="position_cd" id="position_cd">
			<option value=""><?php _e('please select',SL_DOMAIN); ?></option>
		<?php
			foreach($this->position_datas as $k1 => $d1 ) {
				echo '<option value="'.$d1['position_cd'].'">'.htmlspecialchars($d1['name'],ENT_QUOTES).'</option>';
			}
		?>
		</select>
		<input type="text" id="duplicate_cnt" />
		<?php parent::echoItemInputCheckTableForSet($this->item_datas,$this->isSalonAdmin() ); ?>
		<input type="text" id="zip"/>
		<textarea id="address" ></textarea>
		<input type="text" id="tel"/>
		<input type="text" id="mobile"/>
		<input type="text" id="mail"/>
		<input type="text" id="user_login" value="" />
		<textarea id="remark" ></textarea>
		<textarea id="memo" ></textarea>
		<input type="text" id="employed_day" value="" />
		<input type="text" id="leaved_day" value="" />
		<div class="spacer"></div>
		<div id="image_drop_area" ></div>

	</div>

	<table class="flexme" id='lists'>
	<thead>
	</thead>
	</table>
<?php
	}	//show_page
}		//class


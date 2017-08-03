<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Category_Page extends Salon_Page {


	private $category_patern_datas = null;
	private $target_table_datas = null;

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->set_items = array('category_name','category_patern','category_value','target_table');

	}

	public function set_category_patern_datas ($set_datas) {
		$this->category_patern_datas = $set_datas;
	}

	public function set_target_table_datas ($set_datas) {
		$this->target_table_datas = $set_datas;
	}


	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery


		var target;
		var save_k1 = "";
		var save_all_flg = false;

		var staff_items = Array();

		<?php parent::echoClientItem($this->set_items); //for only_branch?>

		$j(document).ready(function() {
			<?php parent::echoSetItemLabel(); ?>
			<?php parent::echoCommonButton();			//共通ボタン	?>
			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slcategory",
				<?php parent::echoDataTableLang(); ?>
				<?php // ソートモードにしない ↓のbSortをfalseに
 					parent::echoTableItem(array('category_name','display_sequence','no_edit_remark'),false,false,"120px",true);
				//for only_branch?>
				"bSort":false,
				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Category_Init" } )
				},



				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {
					<?php parent::echoDataTableSelecter("sl_category_name"); ?>
					<?php parent::echoDataTableDisplaySequence(3);  ?>

				},
			});
			$j("#sl_category_patern").change(function() {
					$j("#sl_category_values").attr("readonly",false);
					if ($j(this).val() == <?php echo Salon_Category::TEXT; ?> ) {
						$j("#sl_category_values").val("");
						$j("#sl_category_values").attr("readonly",true);
					}
			});



		});


		<?php parent::echoDataTableSeqUpdateRow("category","category_cd",$this->is_multi_branch); ?>

		function fnSelectRow(target_col) {
			fnDetailInit();


			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();

			save_k1 = position[0];

			$j("#sl_category_name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['sl_category_name']));
			$j("#sl_category_patern").val(setData['aoData'][position[0]]['_aData']['category_patern']);
			$j("#sl_category_values").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['category_values']));
			$j("#sl_target_table").val(setData['aoData'][position[0]]['_aData']['target_table_id']);

			$j("#sl_category_values").attr("readonly",false);
			if (setData['aoData'][position[0]]['_aData']['category_patern'] == <?php echo Salon_Category::TEXT; ?> ) {
				$j("#sl_category_values").attr("readonly",true);
			}

			$j("#button_update").removeAttr("disabled");
			$j("#button_clear").show();

			$j("#data_detail").show();
			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN);  ?>");



		}

		<?php	parent::echoDataTableDeleteRow("category"); ?>

		function fnClickAddRow(operate) {
			if($j("#sl_category_patern").val() == <?php echo Salon_Category::TEXT; ?> ) {
				if ( ! checkItem("data_detail","sl_category_values") ) return false;
			}
			else {
				if ( ! checkItem("data_detail") ) return false;
			}

			var category_cd = "";
			var display_sequence = 0;
			var setData = target.fnSettings();
			if ( save_k1 !== ""  ) {
				category_cd = setData['aoData'][save_k1]['_aData']['category_cd'];
				display_sequence = setData['aoData'][save_k1]['_aData']['display_sequence'];
			}
			$j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slcategory",
					dataType : "json",
					data: {
						"category_cd":category_cd,
						"no":save_k1,
						"type":operate,
						"category_name":$j("#sl_category_name").val(),
						"category_patern":$j("#sl_category_patern").val(),
						"category_values":$j("#sl_category_values").val(),
						"target_table_id":$j("#sl_target_table").val(),
						"display_sequence":display_sequence,
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Category_Edit"

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

			$j("#button_update").attr("disabled", "disabled");
			$j("#sl_category_values").attr("readonly",false);

			save_k1 = "";
			<?php parent::echo_clear_error(); ?>
			<?php //現状はカルテのみ ?>
			$j("#sl_target_table").val("1");

		}

	<?php parent::echoCheckClinet(array('chk_required','lenmax')); ?>



	</script>

	<h2 id="sl_admin_title"><?php _e('Category',SL_DOMAIN); ?></h2>
	<?php echo parent::echoShortcode(); ?>
	<div id="salon_button_div" >
	<input id="button_insert" type="button" value="<?php _e('Add',SL_DOMAIN); ?>"/>
	<input id="button_update" type="button" value="<?php _e('Update',SL_DOMAIN); ?>"/>
	<input id="button_clear" type="button" value="<?php _e('Clear',SL_DOMAIN); ?>"/>
	<input id="button_detail" type="button"/>
	</div>


	<div id="data_detail" >
		<input type="text" id="sl_category_name" value="" />
		<select name="sl_category_patern" id="sl_category_patern" >
			<option value=""><?php _e('please select',SL_DOMAIN); ?></option>
		<?php
			foreach($this->category_patern_datas as $k1 => $d1 ) {
				echo '<option value="'.$k1.'">'.$d1.'</option>';
			}
		?>
		</select>
		<textarea id="sl_category_values"  ></textarea>
		<select name="sl_target_table" id="sl_target_table" >
		<?php
			foreach($this->target_table_datas as $k1 => $d1 ) {
				echo '<option value="'.$k1.'">'.$d1.'</option>';
			}
		?>
		</select>
		<div class="spacer"></div>

	</div>

	<table class="flexme" id='lists'>
	<thead>
	</thead>
	</table>
<?php
	}	//show_page
}		//class


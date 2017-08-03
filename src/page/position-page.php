<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Position_Page extends Salon_Page {


	private $set_items = null;
	private $admin_menu_datas = null;
	
	

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->set_items = array('position_name','wp_role','no_edit_remark','role');

	}
	
	public function set_admin_menu_datas($set_data) {
		$this->admin_menu_datas = $set_data;
	}
	
	


	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery
		
		
		var target;
		var save_k1 = "";
		
		<?php parent::echoClientItem($this->set_items); //for only_branch?>	

		$j(document).ready(function() {
			
			<?php parent::echoSetItemLabel(); ?>	
			<?php parent::echoCommonButton();			//共通ボタン	?>
			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slposition",
				<?php parent::echoDataTableLang(); ?>
				<?php parent::echoTableItem(array('position_name','no_edit_remark')); ?>
	
				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Position_Init" } )
				},

				"fnDrawCallback": function () {
					<?php parent::echoEditableCommon("item"); ?>
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {	
					<?php parent::echoDataTableSelecter("name",false); ?>
					if (aData.position_cd != <?php echo Salon_Position::MAINTENANCE; ?> ) {
						element.append(sel_box);
						element.append(del_box);
					}
					else {
						element.empty();
						element.append(sel_box);
					}
					
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

			$j("#name").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['name']));	
			$j("#wp_role").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['wp_role']));	
			$j("#remark").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['remark']));	
			$j("#button_update").removeAttr("disabled");
			$j("#button_clear").show();

			$j("#data_detail").show();
			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN);  ?>");

			$j("#role input").attr("checked",false);
			var splits = setData['aoData'][position[0]]['_aData']['role'].split(",");
			var max_cnt = splits.length;
			for (var i = 0 ; i<max_cnt ; i++ ) {
				$j("#role input[value="+splits[i]+"]").attr("checked",true);
			}
			if (setData['aoData'][position[0]]['_aData']['position_cd'] == <?php echo Salon_Position::MAINTENANCE; ?> ) 
				$j("#button_update").attr("disabled",true);
				


		}
		<?php parent::echoDataTableDeleteRow("position"); ?>

		function fnClickAddRow(operate) {
			if ( ! checkItem("data_detail") ) return false;
			var tmp = new Array();  
			$j("#role input[type=checkbox]").each(function (){
				if ( $j(this).is(":checked") ) {
					tmp.push( $j(this).val() );
				}
			});

			var position_cd = '';
			if ( save_k1 !== ""  ) {
				var setData = target.fnSettings();
				position_cd = setData['aoData'][save_k1]['_aData']['position_cd']; 				
			}
			 $j.ajax({
				 	type: "post",
					url:  "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=slposition",
					dataType : "json",

					data: {
						"position_cd":position_cd,
						"no":save_k1,
						"type":operate,
						"name":$j("#name").val(),
						"wp_role":$j("#wp_role").val(),
						"role":tmp.join(","),
						"remark":$j("#remark").val(),
						"nonce":"<?php echo $this->nonce; ?>",
						"menu_func":"Position_Edit"
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
			$j("#role input").attr("checked",false);
			<?php if (get_locale() == 'ja' ) echo '$j("#data_detail .sl_role_table label").css("width","150px");'; ?>
			
			save_k1 = "";
			<?php parent::echo_clear_error(); ?>

		}

	<?php parent::echoCheckClinet(array('chk_required','lenmax')); ?>		

	
	</script>

	<h2 id="sl_admin_title"><?php _e('Position Information',SL_DOMAIN); ?></h2>
	<?php echo parent::echoShortcode(); ?>
	<div id="salon_button_div" >
	<input id="button_insert" type="button" value="<?php _e('Add',SL_DOMAIN); ?>"/>
	<input id="button_update" type="button" value="<?php _e('Update',SL_DOMAIN); ?>"/>
	<input id="button_clear" type="button" value="<?php _e('Clear',SL_DOMAIN); ?>"/>
	<input id="button_detail" type="button"/>
	</div>


	<div id="data_detail" >
		<input type="text" id="name" value="" />
		<?php parent::echoRoleSelect("wp_role"); ?>
		<textarea id="remark"  ></textarea>
		<div id="role" class="sl_role_table ">
			<table>
			<tbody>
				<?php 
					if ($this->admin_menu_datas) {
						foreach ($this->admin_menu_datas as $k1 => $d1 ) {
							echo '<tr><th><label for="check'.$k1.'">'.$d1['name'].'</label></th><td><input type="checkbox" id="check'.$k1.'" value="'.$d1['func'].'"></td></tr>';
//							echo '<tr><th>'.$d1['name'].'</th><td><input type="checkbox" id="check'.$k1.'" value="'.$d1['func'].'"></td></tr>';

						}
					}
				?>
			</tbody>
			</table>
		</div>
		<div class="spacer"></div>
	</div>
	<table class="flexme" id='lists'>
	<thead>
	</thead>
	</table>
<?php 
	}	//show_page
}		//class


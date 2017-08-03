<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');

	
class Log_Page extends Salon_Page {

	private $set_items = null;
	
	

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
		$this->set_items = array('logged_day','logged_time','operation','logged_remark');
	}
	

	

	public function show_page() {
?>

<script type="text/javascript">
		var $j = jQuery
		

		var target;
		var save_k1 = "";
		var save_item_cds_aft = "";
		var save_user_login = "";
		var save_mail = "";
		var save_tel = "";
		var save_p2 = "";
		
		var save_operate = "inserted";
		
		<?php parent::echoClientItem($this->set_items); //for only_branch?>	


		$j(document).ready(function() {
			
			<?php parent::echoSetItemLabel(); ?>	

			$j("#salon_button_div input").addClass("sl_button");
			$j("#button_detail").click(function(){
				$j("#data_detail").toggle();
				if ($j("#data_detail").is(":visible") ) $j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN); ?>")
				else $j("#button_detail").val("<?php _e('Show Details',SL_DOMAIN); ?>");
			});
	
			$j("#data_detail").hide();
			$j("#button_detail").val("<?php _e('Show Details',SL_DOMAIN); ?>");


			$j("#button_redisplay").click(function() {
				fnDetailInit();
				target.fnClearTable();					//テーブルデータクリア
				target.fnReloadAjax();				   //再読み込み
				target.fnPageChange( 'first' );		//ページングの最初へ移動
				
			});

			
			target = $j("#lists").dataTable({
				"sAjaxSource": "<?php echo get_bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php?action=sllog",
				<?php parent::echoDataTableLang(); ?>
				<?php parent::echoTableItem($this->set_items); ?>


				"fnServerParams": function ( aoData ) {
				  aoData.push( { "name": "menu_func","value":"Log_Init" } );
				  aoData.push( { "name": "get_cnt","value":$j("#get_cnt").val() } );
				},
				"fnDrawCallback": function () {
					$j("#lists  tbody .sl_select").click(function(event) {
						fnSelectRow(this);
					});
				},
				fnRowCallback: function( nRow, aData, iDisplayIndex, iDataIndex ) {	
					<?php  parent::echoDataTableSelecter("name",false); ?>
					element.append(sel_box);
				}
			});

		});

<?php //taregt_colはtdが前提 ?>		
		function fnSelectRow(target_col) {
			fnDetailInit();
			
			$j(target.fnSettings().aoData).each(function (){
				$j(this.nTr).removeClass("row_selected");
			});
			$j(target_col.parentNode).addClass("row_selected");
			var position = target.fnGetPosition( target_col );
			var setData = target.fnSettings();


			$j("#logged_day").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['logged_day']));
			$j("#logged_time").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['logged_time']));
			$j("#operation").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['operation']));	
			$j("#remark").val(htmlspecialchars_decode(setData['aoData'][position[0]]['_aData']['remark']));	

			$j("#data_detail").show();

			$j("#button_detail").val("<?php _e('Hide Details',SL_DOMAIN); ?>");

		}


		<?php //parent::echoDisplayErrorLable(); ?>




		
		function fnDetailInit( ) {

			$j("#data_detail input[type=\"text\"]").val("");
			$j("#data_detail textarea").val("");
			<?php parent::echo_clear_error(); ?>
		}


	</script>


	<h2 id="sl_admin_title"F><?php _e('View Log',SL_DOMAIN); ?></h2>
	
	
	<div id="salon_button_div" >
	<input id="button_detail" type="button" />
	<input id="get_cnt" type="text" value="100" class="sl_short_title_width"/>
	<input id="button_redisplay" type="button" value="<?php _e('Redisplay',SL_DOMAIN); ?>"/>
	</div>

	<div id="data_detail" >
		<input type="text" id="logged_day" value="" />
		<input type="text" id="logged_time" value="" />
		<textarea id="operation"  ></textarea>
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


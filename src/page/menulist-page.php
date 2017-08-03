<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Menulist_Page extends Salon_Page {

	private $branch_datas = null;
	private $menu_datas = null;

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}

	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
	}

	public function set_menu_datas ($menu_datas) {
		$this->menu_datas = $menu_datas;
	}



	public function show_page() {
		$reservation_url = $this->setReservationUrl($this->branch_datas['branch_cd']);
?>

<script type="text/javascript">
		var $j = jQuery

</script>
<style>
	.sl_menu_main .sl_description {
		margin:5px !important;
		max-width:600px;
	}

	.sl_menu_main .sl_menu_bottom {
		text-align: right !important;
		max-width:600px;
	}
	.sl_menu_main .sl_menu_button {
		margin-right:10px;
	}

	.sl_menu_main .sl_menu_price_wrap {
		text-align: right !important;
		max-width:600px;
	}
	.sl_menu_main .sl_menu_price {
		color:#CC4466;
		font-size:1.5em;
		margin-right:10px;
	}


</style>

<div id="sl_content" >
<?php foreach ($this->menu_datas as $k1 => $d1) : ?>
	<div><h2 ><?php echo $d1['name']; ?></h2></div>
	<div class="sl_menu_main">
		<div class="sl_menu_price_wrap">
			<p class="sl_menu_price">
			<?php echo __("$",SL_DOMAIN).number_format(+$d1['price']); ?>
			</p>
		</div>
		<div class="sl_description" >
			<?php if (empty($d1['memo']) ) : ?>
				<p class="sl_menu_memo"><?php _e("please set remark",SL_DOMAIN); ?></p>
			<?php else :?>
				<p class="sl_menu_memo"><?php echo $d1['memo']; ?></p>
			<?php endif; ?>
		</div>
		<div class="sl_menu_bottom">
			<p class="sl_menu_button slm_line">
				<a class="sl_button" data-role="button" href="<?php echo $reservation_url; ?>" ><?php _e('Create Reservation',SL_DOMAIN); ?></a>
			</p>
		</div>
	</div>
<?php endforeach; ?>

	<?php //<!-- sl_content --> ?>
	</div>

<?php
	}	//show_page
}		//class


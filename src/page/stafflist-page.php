<?php

	require_once(SL_PLUGIN_SRC_DIR . 'page/salon-page.php');


class Stafflist_Page extends Salon_Page {

	private $branch_datas = null;
	private $staff_datas = null;

	public function __construct($is_multi_branch,$use_session) {
		parent::__construct($is_multi_branch,$use_session);
	}

	public function set_branch_datas ($branch_datas) {
		$this->branch_datas = $branch_datas;
	}

	public function set_staff_datas ($staff_datas) {
		$this->staff_datas = $staff_datas;
	}



	public function show_page() {
		$reservation_url = $this->setReservationUrl($this->branch_datas['branch_cd']);
?>

<script type="text/javascript">
		var $j = jQuery

</script>
<style>
	.sl_staff_main .sl_staff_left
	,.sl_staff_main .sl_staff_right {
		display: inline-block !important;
		*display: inline !important;
		vertical-align: top;
		zoom: 1;
	}
	.sl_staff_main .sl_staff_left {
		max-width:100px;
	}
	.sl_staff_main .sl_staff_right {
		max-width:500px;
	}

	.sl_staff_main .sl_staff_bottom {
		text-align: right !important;
		max-width:600px;
	}

	.sl_staff_memo  {
		margin:5px !important;
	}
	.sl_staff_main .sl_staff_button {
		margin-right:10px;
	}

</style>

<div id="sl_content" >

<?php foreach ($this->staff_datas as $k1 => $d1) : ?>
	<?php
		$name = trim($d1['name']);
		if (empty($name)) {
			$name = __("Name is unregisterd", SL_DOMAIN);
		}
	?>
	<div><h2 ><?php echo $name; ?></h2></div>
	<div class="sl_staff_main">
		<div class="sl_staff_left">
			<div class="sl_staff_photo">
			<?php if (empty($d1['photo_result'][0]['photo_resize_path'])) : ?>
				<img src="<?php echo SL_PLUGIN_URL.'/images/no_image.png'; ?>" oncontextmenu="return false;">
			<?php else :?>
				<?php
					$fileName = basename($d1['photo_result'][0]['photo_resize_path']);
				?>
				<?php if (file_exists(SALON_UPLOAD_DIR.$fileName)) : ?>
					<img src="<?php echo $d1['photo_result'][0]['photo_resize_path']; ?>" oncontextmenu="return false;">
				<?php else : ?>
					<img src="<?php echo SL_PLUGIN_URL.'/images/no_image.png'; ?>" oncontextmenu="return false;">
				<?php endif; ?>
			<?php endif; ?>
			</div>
		</div>

		<div class="sl_staff_right">
			<?php if (empty($d1['memo']) ) : ?>
				<p class="sl_staff_memo"><?php _e("please set Introductions",SL_DOMAIN); ?></p>
			<?php else :?>
				<p class="sl_staff_memo"><?php echo $d1['memo']; ?></p>
			<?php endif; ?>
		</div>
		<div class="sl_staff_bottom">
			<p class="sl_staff_button slm_line">
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


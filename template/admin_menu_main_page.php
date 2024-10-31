
<?php


$obj = RGMP_RentalGoodsManager::obj();
if(isset($validator)){
	$errors = $validator->errors();
}
//print_r($errors);
$msg = array(RGMP_RentalGoodsManager::obj(), 'getMessage');

//権限の一覧を取得
$role_admin = get_role('administrator');
$caps = $role_admin->capabilities;
$caps_list = array();
foreach($caps as $name => $val){
	$caps_list[] = $name;
}
asort($caps_list);

?>
<style>
.my-message{
	font-weight: bold;
	color: red;
}
.my-error{
	padding: 0px;
	margin: 0px;
	margin-left: 20px;
	color: red;
}

form#my-submenu-form label{
	min-width: 170px;
	display: inline-block;
	vertical-align: top;
}
</style>

<div class="wrap">
<h1><?= $msg('MNG.TITLE.SUB_MENU_SETTINGS'); ?></h1>


<div class="my-message"><?= $result_message; ?></div>
<form action="" method="post" id="my-submenu-form">
	<?php //nonceの設定 ?>
	<?php wp_nonce_field(RGMP_RentalGoodsManager::CREDENTIAL_ACTION, RGMP_RentalGoodsManager::CREDENTIAL_NAME) ?>
	<input type="hidden" name="type" value="save">
	<p>
	  <label for="title"><?= $msg('OBJ.ADMIN.OPERATOR_ROLE'); ?>:
		<span class="rental-goods-manager-help">
			<span class="rental-goods-manager-help__link">
				<i class="icon-question-circle"></i>
			</span>
			<span class="rental-goods-manager-help__balloon">
				<?= $msg('HELP.OBJ.ADMIN.OPERATOR_ROLE'); ?>
			</span>
		</span>
	  </label>
	  <input name="admin_operator_role" value="<?= $obj->operator_role; ?>" readonly>
<?php
/*foreach($caps_list as $role){
	$selected = '';
	if($obj->operator_role === $role) $selected = 'selected';
	echo "<option value='$role' $selected >$role</option>";
}*/
?>
	  <div class="my-error"><?= $errors['admin_operator_role'][0]; ?></div>
	</p>
	<p>
	  <label for="title"><?= $msg('OBJ.ADMIN.USER_ROLE'); ?>:
		<span class="rental-goods-manager-help">
			<span class="rental-goods-manager-help__link">
				<i class="icon-question-circle"></i>
			</span>
			<span class="rental-goods-manager-help__balloon">
				<?= $msg('HELP.OBJ.ADMIN.USER_ROLE'); ?>
			</span>
		</span>
	  </label>
	  <select name="admin_user_role" >
<?php
foreach($caps_list as $role){
	$selected = '';
	if($obj->user_role === $role) $selected = 'selected';
	echo "<option value='$role' $selected >$role</option>";
}
?>
	  </select>
	  <div class="my-error"><?= $errors['admin_user_role'][0]; ?></div>
	</p>
	<p>
	  <label for="title"><?= $msg('OBJ.ADMIN.DEFAULT_LOCALE'); ?>:
		<span class="rental-goods-manager-help">
			<span class="rental-goods-manager-help__link">
				<i class="icon-question-circle"></i>
			</span>
			<span class="rental-goods-manager-help__balloon">
				<?= $msg('HELP.OBJ.ADMIN.DEFAULT_LOCALE'); ?>
			</span>
		</span>
	  </label>
	  <input type="text" name="admin_default_locale" value="<?= $obj->default_locale; ?>"/>
	  <div class="my-error"><?= $errors['admin_default_locale'][0]; ?></div>
	</p>
	<p>
	  <label for="title"><?= $msg('OBJ.ADMIN.RENTAL_BUF_DAYS'); ?>:
		<span class="rental-goods-manager-help">
			<span class="rental-goods-manager-help__link">
				<i class="icon-question-circle"></i>
			</span>
			<span class="rental-goods-manager-help__balloon">
				<?= $msg('HELP.OBJ.ADMIN.RENTAL_BUF_DAYS'); ?>
			</span>
		</span>
	  </label>
	  <input type="text" name="admin_rental_buf_days" value="<?= $obj->rental_buf_days; ?>"/>
	  <div class="my-error"><?= $errors['admin_rental_buf_days'][0]; ?></div>
	</p>
	<p>
	  <label for="title"><?= $msg('OBJ.ADMIN.MAX_ORDER_NUM_PER_USER'); ?>:
		<span class="rental-goods-manager-help">
			<span class="rental-goods-manager-help__link">
				<i class="icon-question-circle"></i>
			</span>
			<span class="rental-goods-manager-help__balloon">
				<?= $msg('HELP.OBJ.ADMIN.MAX_ORDER_NUM_PER_USER'); ?>
			</span>
		</span>
	  </label>
	  <input type="text" name="admin_max_order_num_per_user" value="<?= $obj->max_order_num_per_user; ?>"/>
	  <div class="my-error"><?= $errors['admin_max_order_num_per_user'][0]; ?></div>
	</p>
	<input type="submit" >
</form>

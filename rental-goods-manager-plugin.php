<?php 
/*
Plugin Name: Rental Goods Manager
Description: Plugin allows you to manage device lending orders.   物品の貸し出しを管理する。貸出期間などを管理し、履歴を追えるようにする。
Version: 1.0
Auther: nanajuly
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html



*/

//プラグイン

require_once 'rental_goods_manager.php';


add_action('init', 'RGMP_RentalGoodsManager::obj');
register_activation_hook(__FILE__, 'RGMP_RentalGoodsManager::install_plugin' );
add_action('plugins_loaded', 'RGMP_RentalGoodsManager::install_plugin' );
register_uninstall_hook(__FILE__, 'RGMP_RentalGoodsManager::uninstall_plugin' );
//add_action('init', 'RGMP_RentalGoodsManager::session_start');
add_action('admin_menu', array(RGMP_RentalGoodsManager::obj(), 'add_admin_menu'));
add_action('admin_menu', array(RGMP_RentalGoodsManager::obj(), 'remove_admin_menu_sub'));
//add_action('admin_init', array(RGMP_RentalGoodsManager::obj(), 'save_config'));
add_action('rest_api_init', 'RGMP_RentalGoodsManager::add_rest_original_endpoint');
//投稿されたテキストからショートコードを見つけて、IDをoptionに保存する。
add_action('save_post', array(RGMP_RentalGoodsManager::obj(), 'save_post_id_array_for_shortcode'));
//ショートコードがあるユーザ画面だけscriptとcssを書きだすアクション
add_action('wp_enqueue_scripts', array(RGMP_RentalGoodsManager::obj(), 'enqueue_script_user_page'));
//このプラグイン用の管理画面だけscriptとcssを書きだすアクション
add_action('admin_enqueue_scripts', array(RGMP_RentalGoodsManager::obj(), 'enqueue_script_admin_page'));
//ショートコード
//add_shortcode('rental_goods_enqueue_scripts', array(RGMP_RentalGoodsManager::obj(), 'RGMP_RentalGoodsManager::shcode_enqueue_scripts'));
//add_shortcode('rental_goods_reservation_edit_page', array(RGMP_RentalGoodsManager::obj(), 'shcode_write_reservation_edit'));
//add_shortcode('rental_goods_master_edit_page', array(RGMP_RentalGoodsManager::obj(), 'shcode_write_goods_master_edit'));
add_shortcode(RGMP_RentalGoodsManager::SHORTCODE_NAME_USER_PAGE, array(RGMP_RentalGoodsManager::obj(), 'shcode_write_user_reservation'));


?>
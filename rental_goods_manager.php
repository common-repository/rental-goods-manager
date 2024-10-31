<?php 
/*

このプラグインのメインクラス

【設定ファイル】
以下の設定ファイルがあり、カスタマイズしたい場合はファイル名に"custom_"を付けたファイルを作成する。
・HTMLメッセージ定義ファイル
　優先順位順
　(1)現在のthemes/rental-goods-manager/langs/messages_custom_[言語].php 
　(2)/langs/messages_[言語].php 

・エラーメッセージ(Valitron)定義ファイル
　優先順位順
　(1)現在のthemes/rental-goods-manager/Valitron/lang/custom_[言語].php 
　(2)/Valitron/lang/[言語].php 

・カスタムの印刷タブのHTMLテンプレート
　以下のファイルがなければreservation_edit_page.php内で記述したscriptを使用
　(1)現在のthemes/rental-goods-manager/template/tab_print_area_custom.hbs";

【Rest APIエンドポイント】
・rental-goods-manager/api/goods_masters [GET]
  商品マスタの一覧取得
・rental-goods-manager/api/goods_masters [POST]
  商品マスタ保存
  複数の商品マスタを一度に保存できる。
・rental-goods-manager/api/goods_masters/names [GET]
  商品マスタに登録されているの名前のみの一覧を取得。重複なし。
・rental-goods-manager/api/orders/(\d+)$ [GET]
  注文情報詳細取得
  最後の数字はorder_id
・rental-goods-manager/api/orders  [GET]
  注文情報取得
・rental-goods-manager/api/orders/(\d+)$ [POST]
  注文情報保存。変更、取消などができる。
  最後の数字はorder_id。
・rental-goods-manager/api/orders [POST]
  注文情報登録。新規登録する。

【権限について】
このプラグインの処理には、操作者(operator)とユーザ(user)があってそれぞれに処理を許可するための権限ロールを自由に設定できます。
既存の権限を使用してもよいですが、より管理をよくするなら「User Role Editor」などのプラグインを導入します。
「User Role Editor」プラグインは自作権限を追加できますので、例えば、「rental_mng_operator」「rental_mng_user」のような
ロールを追加し、許可したい権限グループ（購読者、編集者など）に対して自作権限を設定すれば、柔軟に許可をできます。
もちろん、このプラグインの管理設定画面で、操作者の権限に「rental_mng_operator」、ユーザの権限に「rental_mng_user」を
設定しなければ正しく動作しないので忘れずに設定すること。
また、間違えて権限を設定すると、操作させたくない人に操作許可してしまうので注意。
もし、Administratorにのみ操作者処理を許したい場合は、「User Role Editor」でどの役割にもrental_mng_operator」を
設定しなければAdministratorにのみ操作が許可されることになります。

*/


/**
 * クラス
 */
class RGMP_RentalGoodsManager{
	const VERSION           = '1.1';
	const PLUGIN_ID         = 'rental-goods-manager';
	const CREDENTIAL_ACTION = self::PLUGIN_ID . '-nonce-action';
	const CREDENTIAL_NAME   = self::PLUGIN_ID . '-nonce-key';
	const PLUGIN_DB_PREFIX  = self::PLUGIN_ID . '_';
	const COMPLETE_CONFIG   = self::PLUGIN_ID . '-complete-msg';
	// config画面のslug
	//const CONFIG_MENU_SLUG  = self::PLUGIN_ID . '-config';
	//セッション用クッキーの名前
	const PLUGIN_SESSION_NAME= self::PLUGIN_ID . '-sess';
	//APIのURIパスのプレフィックス
	const API_URI_PREFIX    = self::PLUGIN_ID . '/api';
	//ユーザ画面表示用のショートコード名
	const SHORTCODE_NAME_USER_PAGE = 'rental_goods_user_page';
	//
	const DEFAULT_SETTINGS = array('user_role'=>'read', 'operator_role'=>'rental_mng_operator', 
		'default_locale'=>'ja', 'rental_buf_days'=>1, 'max_order_num_per_user'=>2);
	
	static private $instance = NULL;
	//
	//private $def_params_validation = NULL;
	//貸出注文もしくはAPIを使用できる権限
	private $user_role = NULL;
	//オペレータ権限
	private $operator_role = NULL;
	//貸出期間の前後に設ける予備日（この日数を含めて貸出しができなくなる）
	private $rental_buf_days = 1;
	//一人が注文できる最大注文数
	private $max_order_num_per_user = 1;
	//ロケール言語情報
	private $lang_require_file = NULL;
	private $default_locale = NULL;
	private $messages = NULL;
	//リンクタグをshort codeですでに記述しているかを保存
	private $applied_enqueue_scripts = array();
	
	/**
	 * コンストラクタ---------------
	 * @param string $role このプラグインの機能を使用してよいユーザ権限(publish_posts, edit_pages, edit_postなど)
	 */
	public function __construct(string $default_locale=NULL, string $role='read', string $operator_role='edit_users', 
		int $max_order_num_per_user=2){
		//
		$settings = get_option(self::PLUGIN_DB_PREFIX . 'settings', self::DEFAULT_SETTINGS);
		$this->user_role = $settings['user_role'];
		$this->operator_role = $settings['operator_role'];
		$langs = $this->locales();
		//デフォルト言語
		$this->default_locale = $settings['default_locale'];
		$this->lang_require_file = $this->get_message_filename($langs, $this->default_locale);
		$this->rental_buf_days = $settings['rental_buf_days'];
		$this->max_order_num_per_user = $settings['max_order_num_per_user'];
		//
		
	}
	
	public function __destruct(){
	}
	
	/**
	 * getter
	 */
	function __get($name){
		if($name === 'user_role' || $name === 'operator_role' || $name === 'default_locale' || $name === 'rental_buf_days'
			|| $name === 'max_order_num_per_user'){
			return $this->$name;
		}
		//指定以外のプロパティ名はエラーにする
		throw new Exception('Invalid value');
	}
    
	/**
	 * メッセージを追加する。
	 * @param array $messages メッセージ。{key=>text}
	 */
	public function setMessages(array $messages){
		$this->messages = $messages;
	}
	
	/**
	 * メッセージ設定ファイルから指摘のキー名の文字列を取得する。
	 * @param string $key 検索するキー名。
	 * @param bool $is_escape trueのときHTMLエスケープをする。
	 * @param $fields {@internal メッセージ内にプレースホルダ（ {0}, {1}など）がある場合に置き替える。 
	 * @return string 文字列。プレースホルダがある場合は置換後の文字列が返る。
	*/
	public function getMessage(string $key, bool $is_escape=true, ...$fields): string{
		if(isset($this->lang_require_file)){
			require_once $this->lang_require_file;
			$this->lang_require_file = NULL;
		}
		//
		$text = $this->messages[$key] ?? '';
		for($i=0; $i<count($fields); ++$i){
			$text = str_replace('{'.$i.'}', $fields[$i], $text);
		}
		if($is_escape){
			$text = str_replace("\n", "<br>", htmlspecialchars($text));
		}
		return $text;
	}
	
	/**
	 * JSに渡すメッセージ定義を作成する。
	 * @return array メッセージの連想配列。JSのメッセージ定義変数に設定するためのもの（キーを絞って返す）。
	*/
	public function create_messages_for_js(): array{
		$this->getMessage('OK.SUCCESS');//一度呼び出さないとmessagesが設定されないので
		$ret = array();
		foreach($this->messages as $key => $value){
			if(substr($key, 0, 4) != 'MNG.') $ret[$key] = $value;
		}
		return $ret;
	}
	
	/**
	 * 指定の言語コードの設定ファイルがあるか検索し、見つかったファイルパスを返す。langs/フォルダ内を検索する。
	 * カスタム用の「messages_custom_」のプレフィックスのファイル名を優先的に検索する。
	 * @param array $langs 候補の言語コード(ja, enなど)の一覧。優先順位順に配列に設定すること。
	 * @param string $default_locale ファイルが見つからない時に使用するデフォルト言語コード。
	*/
	protected function get_message_filename(array $langs, string $default_locale) : string{
		//
		foreach($langs as $lang){
			$file = get_template_directory() . '/' . self::PLUGIN_ID . '/langs/messages_custom_' . $lang . '.php';
			if(file_exists($file)) return $file;
			$file = __DIR__ . '/langs/messages_' . $lang . '.php';
			if(file_exists($file)) return $file;
		}
		
		$file = __DIR__ . '/langs/messages_' . $default_locale . '.php';
		return $file;
	}
	
	/**
	 * 妥当性チェックValitron用のメッセージファイルの言語コード(ja, enなど)を取得する。
	 * Valitron/lang/配下の言語コードファイルの存在有無で判定する。一番最初に見つかったファイル名を言語コードとする。
	 * カスタム用の「custom_」のプレフィックスのファイル名を優先的に検索する。
	 * @return string 言語コード（例：'ja', 'custom_en'など）
	 * @see self::locales   言語コードのリスト
	*/
	protected function get_valitron_lang() : string{
		$locales = $this->locales();
		//
		foreach($locales as $lang){
			$file = get_template_directory() .'/'. self::PLUGIN_ID . '/Valitron/lang/custom_' . $lang . '.php';
			if(file_exists($file)) return 'custom_' . $lang;
			$file = __DIR__ . '/Valitron/lang/' . $lang . '.php';
			if(file_exists($file)) return $lang;
		}
		
		return $this->default_locale;
	}
	
	/**
	 * 妥当性チェックValitron用のメッセージファイルのディレクトリを取得する。
	 * 言語コードから判断して取得。言語コードが'custom_'から始まるときはカスタムのディレクトリを返す。
	 * @param string $lang 取得した言語コード
	 * @return string メッセージファイルのあるディレクトリ
	 * @see self::get_valitron_lang()   
	*/
	protected function get_valitron_dir(string $lang) : string{
		if(substr($lang, 0, strlen('custom_')) === 'custom_'){
			return get_template_directory() .'/'. self::PLUGIN_ID . '/Valitron/lang';
		}
		return __DIR__ . '/Valitron/lang';
	}
	
	/**
	 * HTTPヘッダからロケールの言語コードを取得する（'ja','en'とか）
	 */
	protected function locales() : array {
		$http_langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$langs = [];
		foreach ( $http_langs as $lang ) {
			$langs[] = explode( ';', $lang )[0];
		}
		return $langs;
	}
	
	/**
	 * 変数がない場合、NULLの場合、空の場合、デフォルト値を設定する
	 */
	protected function default_val($val, ?string $default){
		if(!isset($val)) return $default;
		if(is_array($val)) return $val;
		if($val == '') return $default;
		return stripslashes($val);
	}
	
	
	/**
	 * プラグインのインストール、有効時にDBなど必要な初期化処理をする
	*/
	static public function install_plugin(){
		$installed_ver = get_option( self::PLUGIN_ID );
		if(!isset($installed_ver) || strcmp($installed_ver, self::VERSION) != 0 ){
			// 権限グループを追加
			$role = get_role('administrator');
			$role->add_cap('rental_mng_operator');
			
			//
			update_option(self::PLUGIN_DB_PREFIX . 'settings', self::DEFAULT_SETTINGS);
			
			//バージョン管理のために
			update_option(self::PLUGIN_ID, self::VERSION);
		}
		
		//DB
		require_once 'rgm_install.php';
		RGMP_RentalGoodsManager_Init::db_install();
	}
	
	/**
	 * プラグインのアンインストール処理をする
	*/
	static public function uninstall_plugin(){
		$roles = wp_roles()->role_objects; //WP_Roleのハッシュ
		foreach($roles as $role){
			$role->remove_cap('rental_mng_operator');
		}
		
		//設定の削除
		delete_option(self::PLUGIN_ID );
		delete_option(self::PLUGIN_DB_PREFIX . 'settings');
		
		//DB
		require_once 'rgm_install.php';
		RGMP_RentalGoodsManager_Init::db_uninstall();
	}
	
	
	/** 
	 * WPにoption値を保存する。
	 * DBに保存する。キーがまだ登録されていない場合はaddで、登録済みならupdateで。
	 * @param array $key_values 保存する値を連想配列で渡す。key=>valueのセット
	 * @return bool 保存が成功した場合true
	*/
	protected function save_option(array $key_values) : bool{
		$stock = get_option(self::PLUGIN_DB_PREFIX . 'settings');
		$is_changed = false;
		foreach($key_values as $key => $value){
			//同じ値でupdateするとエラーになるのでここで制御
			if($stock[$key] === $value) continue;
			$is_changed = true;
			//値の保存
			$stock[$key] = $value;
		}
		//
		if(!$is_changed) return true;
		return update_option(self::PLUGIN_DB_PREFIX . 'settings', $stock);
	}
		
	/** 
	 * WPのoption値を取得する。
	 * @param string $key キー名
	 * @return object 保存が成功した場合true
	*/
	protected function get_option($key){
		$stock = get_option(self::PLUGIN_DB_PREFIX . 'settings');
		return $stock[$key];
	}
	
	
	/**
	 * ショートコードを記述している投稿だけにjs,cssファイルをインクルードするための関数。
	 * 投稿されたテキストからショートコードを見つけて、IDをoptionに保存する。
	 * add_action('save_post', array(RGMP_RentalGoodsManager::obj(),'save_option_shortcode_post_id_array'));すること。
	 * @param array $attrs 引数。[0]ファイル名。
	*/
	public function save_post_id_array_for_shortcode($post_id) {
		if ( wp_is_post_revision( $post_id ) OR 'page' != get_post_type( $post_id )) {
			return;
		}
		//保存処理開始
		$id_array = $this->get_option('shcode_ids_' . $option_name);
		$option_name = self::SHORTCODE_NAME_USER_PAGE;
		if($this->find_shortcode_occurences($option_name, $post_id)){
			$id_array[$post_id] = true;
		}else{
			unset($id_array[$post_id]);
		}
		$this->save_option(array('shcode_ids_' . $option_name => $id_array));
	}

	/**
	 * ショートコードを記述している投稿だけにjs,cssファイルをインクルードするための関数。
	 * 投稿されたテキストからショートコードを見つけて返却する。
	 * @param string $shortcode ショートコード名。
	 * @param string [$post_type] 投稿タイプ（'post', 'page'）。
	 * @return {@internal 連装配列{投稿ID=>true}
	 * @see save_option_shortcode_post_id_array()
	*/
	protected function find_shortcode_occurences(string $shortcode, int $post_id) : bool {
		$shortcode = '[' . $shortcode;
		$content = get_post($post_id)->post_content;
		if(strpos($content, $shortcode) !== false) return true;
		return false;
	}
	
	/**
	 * ファイルrental_goods_manager_common_def.jsを読み込んで置換文字を置換した結果を返す
	 * @return string 置換後のJS
	 * @see enqueue_script_user_page()
	*/
	protected function create_common_def_script() : string {
		$script = file_get_contents(__DIR__ . '/include/rental_goods_manager_common_def.js');
		$json = json_encode($this->create_messages_for_js(), JSON_UNESCAPED_UNICODE);
		$script = str_replace("{{messages}}", $json, $script);
		$script = str_replace("{{uriPrefix}}", '/wp-json/' . self::API_URI_PREFIX, $script);
		return $script;
	}
	
	/**
	 * ショートコードを記述している投稿だけにjs,cssファイルをインクルードするための関数。
	 * 実際の表示画面でjs,cssをenqueする。
	 * add_action('wp_enqueue_scripts', array(RGMP_RentalGoodsManager::obj(), 'enqueue_script_user_page'));すること。
	 * @see save_option_shortcode_post_id_array()
	*/
	public function enqueue_script_user_page() {
		$page_id = get_the_ID();
		$option_id_array = $this->get_option('shcode_ids_' . self::SHORTCODE_NAME_USER_PAGE);
//error_log(print_r($option_id_array,true).'######');
		if (isset($option_id_array[$page_id])) {
			$script = $this->create_common_def_script();
			wp_enqueue_script('handlebars', plugin_dir_url( __FILE__ ) . 'include/handlebars.min.js?ver='.self::VERSION);
			wp_enqueue_script('just-handlebars-helpers', plugin_dir_url( __FILE__ ) . 'include/just-handlebars-helpers.min.js?ver='.self::VERSION, array('handlebars'));
			wp_enqueue_style('rental_goods_manager_common', plugin_dir_url( __FILE__ ) . 'include/rental_goods_manager_common.css?ver='.self::VERSION);
			wp_enqueue_script('rental_goods_manager_common', plugin_dir_url( __FILE__ ) . 'include/rental_goods_manager_common.js?ver='.self::VERSION);
			wp_add_inline_script('rental_goods_manager_common', $script, 'after');
			wp_enqueue_style('rental_goods_reservation_user', plugin_dir_url( __FILE__ ) . 'include/rental_goods_reservation_user.css?ver='.self::VERSION);
			wp_enqueue_script('rental_goods_reservation_user', plugin_dir_url( __FILE__ ) . 'include/rental_goods_reservation_user.js?ver='.self::VERSION, array('rental_goods_manager_common'));
			wp_enqueue_style('flatpickr', plugin_dir_url( __FILE__ ) . 'include/flatpickr.min.css?ver='.self::VERSION);
			wp_enqueue_script('flatpickr', plugin_dir_url( __FILE__ ) . 'include/flatpickr.js?ver='.self::VERSION);
		}
	}
	
	/**
	 * ショートコードを記述している投稿だけにjs,cssファイルをインクルードするための関数。
	 * 実際の表示画面でjs,cssをenqueする。
	 * add_action('admin_enqueue_scripts', array(RGMP_RentalGoodsManager::obj(), 'enqueue_script_admin_page'));すること。
	 * @see save_option_shortcode_post_id_array()
	*/
	public function enqueue_script_admin_page($hook_suffix) {
		global $pagenow;
		//このプラグインの管理画面サフィックスはself::PLUGIN_ID.'-1'など
		$sufix = self::PLUGIN_ID . '-';
		if($pagenow == 'admin.php' && substr($hook_suffix, -strlen($sufix)-1, strlen($sufix)) == $sufix) {
			$script = $this->create_common_def_script();
			wp_enqueue_script('handlebars', plugin_dir_url( __FILE__ ) . 'include/handlebars.min.js?ver='.self::VERSION);
			wp_enqueue_script('just-handlebars-helpers', plugin_dir_url( __FILE__ ) . 'include/just-handlebars-helpers.min.js?ver='.self::VERSION, array('handlebars'));
			wp_enqueue_style('handsontable', plugin_dir_url( __FILE__ ) . 'include/handsontable.full.min.css?ver='.self::VERSION);
			wp_enqueue_script('handsontable', plugin_dir_url( __FILE__ ) . 'include/handsontable.full.min.js?ver='.self::VERSION, array('handlebars'));
			wp_enqueue_style('rental_goods_manager_common', plugin_dir_url( __FILE__ ) . 'include/rental_goods_manager_common.css?ver='.self::VERSION);
			wp_enqueue_script('rental_goods_manager_common', plugin_dir_url( __FILE__ ) . 'include/rental_goods_manager_common.js?ver='.self::VERSION);
			wp_add_inline_script('rental_goods_manager_common', $script, 'after');
			wp_enqueue_script('rental_goods_master_ctrl', plugin_dir_url( __FILE__ ) . 'include/rental_goods_master_ctrl.js?ver='.self::VERSION, array('rental_goods_manager_common'));
			wp_enqueue_style('rental_goods_reservation_edit', plugin_dir_url( __FILE__ ) . 'include/rental_goods_reservation_edit.css?ver='.self::VERSION);
			wp_enqueue_script('rental_goods_reservation_edit_ctrl', plugin_dir_url( __FILE__ ) . 'include/rental_goods_reservation_edit_ctrl.js?ver='.self::VERSION, array('rental_goods_manager_common'));
			wp_enqueue_script('rental_goods_reservation_print', plugin_dir_url( __FILE__ ) . 'include/print.js?ver='.self::VERSION, array('rental_goods_manager_common'));
			wp_enqueue_style('flatpickr', plugin_dir_url( __FILE__ ) . 'include/flatpickr.min.css?ver='.self::VERSION);
			wp_enqueue_script('flatpickr', plugin_dir_url( __FILE__ ) . 'include/flatpickr.js?ver='.self::VERSION);
		}
	}
	
	/**
	 * ショートコード[rental_goods_reservation_user_page]の実装関数：
	 * ユーザ画面（予約処理）のHTML書き出し。
	*/
	public function shcode_write_user_reservation($attrs=array()) {
		//ショートコードでincludeやrequireを使いたい場合はこうするらしい。
		ob_start();
		
		//以下のHTML内のHTMLノードのルートのid
		$rental_goods_content_id = (is_array($attrs) ? $attrs['id'] : '');
		require_once 'template/reservation_user_page.php';
		
		return ob_get_clean();
	}
	
	/**
	 * HTMLのカスタムhbsテンプレートをscriptタグとして書きだす。
	 * ファイルの置き場所は（theme名/プラグイン名/template/テンプレート名.hbs）
	 * @param string $rental_goods_content_id JSで制御する箇所。HTMLタグのID(例：'goods_resv_manager')で指定
	 * @param string $template_name テンプレート名(例：'tab_print_area')
	 * @return string jsで制御するクラスに渡す引数のテンプレートのconfigを記述した文字列を返す。
	 *    (例："'tab_print_area: '#goods_resv_manager_reservation_tab_print_area_custom'")
	*/
	public function write_script_html_template(string $rental_goods_content_id, string $template_name) {
		$file = get_template_directory() . '/' . self::PLUGIN_ID . "/template/{$template_name}_custom.hbs";
		if(!file_exists($file)) return '';
		//
		$html_id = "{$rental_goods_content_id}_reservation_{$template_name}_custom";
		//scriptタグ出力
		echo "<script id='$html_id' type='text/x-handlebars-template'>";
		require_once $file;
		echo "</script>";  
		return "$template_name: '#$html_id'";
	}
	
	/**
	 * リクエストパラメタをシングルクォートのエスケープを取り除いたものを返す。
	 * 値が配列の場合にも対応。
	 * @param string $method リクエストのメソッド('get', 'post')
	 * @return array 取り除いた結果のパラメタ
	 */
	protected function stripslashes_request_params(string $method): array {
		$ret = array();
		if($method === 'get'){
			$params = &$_GET;
		}else{
			$params = &$_POST;
		}
		foreach($params as $key => $value){
			if(is_array($value)){
				$ary_values = array();
				foreach($value as $id => $element){
					$ary_values[] = stripslashes($element);
				}
				$ret[$key] = $ary_values;
			}else{
				$ret[$key] = stripslashes($value);
			}
		}
		return $ret;
	}
	
	/**
	 * リクエストパラメタの妥当性チェックをする。
	 * @param array $req_params リクエストのメソッド('get', 'post')
	 * @param array $requiredFields 必須項目を指定(例：['goods_id', 'user_id'])。リクエストパラメタに存在しない場合はエラーにする。
	 * @param bool $include_goods_names 商品名goods_nameが商品マスタに存在しない名前だったらエラーにする。
	 * @param bool $include_deplicate_goods_names  goods_namesが一意でない場合にエラーにする
	 */
	protected function createValidator(array $req_params, ?array $requiredFields=NULL, bool $include_goods_names=true
		,bool $include_deplicate_goods_names=false
	): Valitron\Validator {
		if(!class_exists('Valitron\\Validator')){
			require_once 'Valitron/Validator.php';
		}
		$valitron_lang = $this->get_valitron_lang();
		Valitron\Validator::langDir($this->get_valitron_dir($valitron_lang));
		Valitron\Validator::lang($valitron_lang); 
		
		//オリジナルルールの追加(日付が他のフィールドより後)
		Valitron\Validator::addRule('my.dateAfterWith', function($field, $value, $params, $fields) {
			$p = $fields[$params[0]];
			if(!isset($p)) return false;
			$vtime = ($value instanceof \DateTime) ? $value->getTimestamp() : strtotime($value);
			$ptime = ($p instanceof \DateTime) ? $p->getTimestamp() : strtotime($p);
			return $vtime >= $ptime;
		}, 'must be date after field "%s"');
		//オリジナルルールの追加（配列の数）
		Valitron\Validator::addRule('my.arrayLengthBetween', function($field, $value, $params, $fields) {
			$min_num = $params[0];
			$max_num = $params[1];
			if(!isset($value)) return true;
			if(!is_array($value)) return false;
			$array_num = count($value);
			if($array_num >= $min_num && $array_num <= $max_num) return true;
			return false;
		}, 'must be number of params between %d and %d');
		
		//バリデータの作成
		$v = new Valitron\Validator($req_params);
		//ラベルの設定（エラーメッセージに設定した名前が出力されるようになる）
		$v->labels([
			//管理者側のパラメタ
			'admin_user_role'=> $this->getMessage('OBJ.ADMIN.USER_ROLE'),
			'admin_operator_role'=> $this->getMessage('OBJ.ADMIN.OPERATOR_ROLE'),
			'admin_default_locale'=> $this->getMessage('OBJ.ADMIN.DEFAULT_LOCALE'),
			'admin_rental_buf_days'=> $this->getMessage('OBJ.ADMIN.RENTAL_BUF_DAYS'),
			'admin_max_order_num_per_user'=> $this->getMessage('OBJ.ADMIN.MAX_ORDER_NUM_PER_USER'),
			//ユーザ側のパラメタ
			'order_id' => $this->getMessage('OBJ.RESERVATION_ID'),
			'rental_from' => $this->getMessage('OBJ.RESERVATION_RENTAL_FROM'),
			'rental_to' => $this->getMessage('OBJ.RESERVATION_RENTAL_TO'),
			'order_nums.*' => $this->getMessage('OBJ.RESERVATION_ORDER_NUM'),
			'order_nums'   => $this->getMessage('OBJ.RESERVATION_ORDER_NUM'),
			'user_mail' => $this->getMessage('OBJ.MAIL'),
			'pm_user_mail' => $this->getMessage('OBJ.MAIL'),
			'user_zip'=>$this->getMessage('OBJ.USER_ZIP'),
			'user_address'=>$this->getMessage('OBJ.USER_ADDRESS'),
			'user_department'=>$this->getMessage('OBJ.USER_DEPARTMENT'),
			'user_id'=>$this->getMessage('OBJ.USER_ID'),
			'goods_id' => $this->getMessage('OBJ.GOODS_ID'),
			'goods_names.*' => $this->getMessage('OBJ.GOODS_NAME'),
			'goods_names'   => $this->getMessage('OBJ.GOODS_NAME'),
			'goods_name'    => $this->getMessage('OBJ.GOODS_NAME'),
			'pm_goods_name' => $this->getMessage('OBJ.GOODS_NAME'),
			'goods_serialnos.*' => $this->getMessage('OBJ.GOODS_SERIALNO'),
			'goods_serialno'    => $this->getMessage('OBJ.GOODS_SERIALNO'),
			'pm_goods_serialno' => $this->getMessage('OBJ.GOODS_SERIALNO'),
			'goods_nos.*' => $this->getMessage('OBJ.GOODS_NO'),
			'goods_no'    => $this->getMessage('OBJ.GOODS_NO'),
			'pm_goods_no' => $this->getMessage('OBJ.GOODS_NO'),
			'rental_goods_ids.*' => $this->getMessage('OBJ.RESERVATION_RENTAL_GOODS_IDS'),
			'rental_goods_ids'   => $this->getMessage('OBJ.RESERVATION_RENTAL_GOODS_IDS'),
			'hides.*' => $this->getMessage('OBJ.HIDE'),
			'hides'   => $this->getMessage('OBJ.HIDE'),
			'hide'    => $this->getMessage('OBJ.HIDE'),
			'order_nums.*' => $this->getMessage('OBJ.RESERVATION_ORDER_NUM'),
			'order_nums'   => $this->getMessage('OBJ.RESERVATION_ORDER_NUM'),
			'order_num'    => $this->getMessage('OBJ.RESERVATION_ORDER_NUM'),
			'order_condition_start_date' => $this->getMessage('OBJ.ORDER_CONDITION_START_DATE'),
			'order_condition_months' => $this->getMessage('OBJ.ORDER_CONDITION_MONTHS'),
		]);
		if(isset($requiredFields)){
			$v->rule('required', $requiredFields);
		}
		//管理者側
		$v->rule('slug', 'admin_user_role');
		$v->rule('slug', 'admin_operator_role');
		$v->rule('regex', 'admin_default_locale', '/^[0-9a-zA-Z\-\.\_]{1,30}$/')->message($this->getMessage('ERR.OBJ_ADMIN_DEFAULT_LANG'));
		$v->rule('integer', 'admin_rental_buf_days');
		$v->rule('min', 'admin_rental_buf_days', 0);
		$v->rule('integer', 'admin_max_order_num_per_user');
		$v->rule('min', 'admin_max_order_num_per_user', 1);
		//ユーザ側
		$v->rule('dateFormat', 'rental_from', 'Y-m-d');
		$v->rule('dateFormat', 'rental_to', 'Y-m-d');
		$v->rule('in', 'term_include', [0, 1]);
		$v->rule('in', 'order_by_asc', [0, 1]);
		$v->rule('my.dateAfterWith','rental_to', 'rental_from');
		$v->rule('email'        , 'user_mail');
		$v->rule('lengthBetween', 'user_mail', 1, 50);
		$v->rule('lengthBetween', 'pm_user_mail', 1, 50);
		$v->rule('regex', 'user_zip', '/^[0-9]{1,10}$/')->message($this->getMessage('ERR.OBJ_USER_ZIP'));
		$v->rule('lengthBetween', 'user_department', 1, 20);
		$v->rule('min', 'goods_id', 0);
		$v->rule('min', 'order_id', 1);
		$v->rule('min'                  , 'order_nums.*', 1);
		$v->rule('my.arrayLengthBetween', 'order_nums', 1, 20);
		$v->rule('array', 'statuses');
		$v->rule('in'   , 'statuses.*', ['re', 'ou', 'cl', 'ca']);
		$v->rule('in'   , 'status', ['re', 'ou', 'cl', 'ca']);
		$v->rule('lengthBetween', 'remarks', 0, 500);
		$v->rule('lengthBetween'        , 'goods_names.*', 1, 30);
		$v->rule('my.arrayLengthBetween', 'goods_names', 1, 20);
		$v->rule('lengthBetween'        , 'goods_name', 1, 30);
		$v->rule('lengthBetween'        , 'pm_goods_name', 1, 30);
		$v->rule('lengthBetween'          , 'goods_serialnos.*', 1, 30);
		$v->rule('my.arrayLengthBetween'  , 'goods_serialnos', 1, 20);
		$v->rule('lengthBetween'          , 'goods_serialno', 1, 30);
		$v->rule('lengthBetween'          , 'pm_goods_serialno', 1, 30);
		$v->rule('lengthBetween'        , 'goods_nos.*', 1, 30);
		$v->rule('my.arrayLengthBetween', 'goods_nos', 1, 20);
		$v->rule('lengthBetween'        , 'goods_no', 1, 30);
		$v->rule('lengthBetween'        , 'pm_goods_no', 1, 30);
		$v->rule('integer'                , 'rental_goods_ids.*');
		$v->rule('my.arrayLengthBetween'  , 'rental_goods_ids', 1, 50);
		$v->rule('in'                   , 'hides.*', ['true','false']);
		$v->rule('my.arrayLengthBetween', 'hides', 1, 20);
		$v->rule('in'                   , 'hide', ['true','false']);
		$v->rule('min', 'offset', 0);
		$v->rule('max', 'limit', 100);
		$v->rule('dateFormat', 'order_condition_start_date', 'Y-m-d');
		$v->rule('integer', 'order_condition_months');
		$v->rule('min'    , 'order_condition_months', 1);
		$v->rule('max'    , 'order_condition_months', 5);
		
		//商品名の取得とルールの設定
		if($include_goods_names){
			require_once 'dao.php';
			$dao = new RGMP_RentalGoodsManagerDao();
			$search_result = $dao->find_goods_name_with_count();
			$goods_names = $search_result['list'];
			$v->rule('in', 'goods_name', $goods_names);
			$v->rule('in', 'goods_names.*', $goods_names);
		}
		
		//商品名が一意であることをルール設定
		if($include_deplicate_goods_names){
			$v->rule('containsUnique', 'goods_names');
		}
		
		return $v;
	}
	
	
	/**
	 * WP REST APIのオリジナルエンドポイント追加
	 * wp-json/RentalGoodsManager/api/...にアクセスできるようにする。
	 */
	static function add_rest_original_endpoint(){
		//商品マスタ取得
		register_rest_route( self::API_URI_PREFIX, '/goods_masters', array(
			'methods' => 'GET',
			'permission_callback' => array(self::obj(), 'api_authenticate_operator'),
			//エンドポイントにアクセスした際に実行される関数
			'callback' =>  array(self::obj(), 'api_find_goods_masters'),
		));
		//商品マスタ保存
		register_rest_route( self::API_URI_PREFIX, '/goods_masters', array(
			'methods' => 'POST',
			'permission_callback' => array(self::obj(), 'api_authenticate_operator'),
			//エンドポイントにアクセスした際に実行される関数
			'callback' =>  array(self::obj(), 'api_save_goods_master'),
		));
		//商品マスタの名前一覧取得
		register_rest_route( self::API_URI_PREFIX, '/goods_masters/names', array(
			'methods' => 'GET',
			'permission_callback' => array(self::obj(), 'api_authenticate_operator'),
			//エンドポイントにアクセスした際に実行される関数
			'callback' =>  array(self::obj(), 'api_find_goods_names_with_count'),
		));
		//注文情報詳細取得
		register_rest_route( self::API_URI_PREFIX, '/orders/(\d+)$', array(
			'methods' => 'GET',
			'permission_callback' => array(self::obj(), 'api_authenticate_operator'),
			//エンドポイントにアクセスした際に実行される関数
			'callback' =>  array(self::obj(), 'api_get_order'),
		));
		//注文情報取得
		register_rest_route( self::API_URI_PREFIX, '/orders', array(
			'methods' => 'GET',
			'permission_callback' => array(self::obj(), 'api_authenticate_operator'),
			//エンドポイントにアクセスした際に実行される関数
			'callback' =>  array(self::obj(), 'api_find_orders'),
		));
		//注文情報保存
		register_rest_route( self::API_URI_PREFIX, '/orders/(\d+)$', array(
			'methods' => 'POST',
			'permission_callback' => array(self::obj(), 'api_authenticate_operator'),
			//エンドポイントにアクセスした際に実行される関数
			'callback' =>  array(self::obj(), 'api_save_order_update'),
		));
		//注文情報登録
		register_rest_route( self::API_URI_PREFIX, '/orders', array(
			'methods' => 'POST',
			'permission_callback' => array(self::obj(), 'api_authenticate_operator'),
			//エンドポイントにアクセスした際に実行される関数
			'callback' =>  array(self::obj(), 'api_save_order_new'),
		));
	}
	
	
	
	//実体を取得
	static public function obj(){
		//require_once 'def_params_validation.php';
		if(is_null(self::$instance)) self::$instance = new RGMP_RentalGoodsManager();
		return self::$instance;
	}
	
	
	
	
	public function add_admin_menu() {
		//menu_slagはハイフンで繋ぐのがWordPressの流儀のようです。一意である必要もあるらしい。
		add_menu_page($this->getMessage('MNG.TITLE.MAIN_MENU'), $this->getMessage('MNG.TITLE.MAIN_MENU'), 
			'administrator', self::PLUGIN_ID, array($this, 'display_menu_main'));
		add_submenu_page(self::PLUGIN_ID, 
			$this->getMessage('MNG.TITLE.SUB_MENU_SETTINGS'), $this->getMessage('MNG.TITLE.SUB_MENU_SETTINGS'), 
			'administrator', self::PLUGIN_ID.'-1', array($this, 'display_menu_main'));
		add_submenu_page(self::PLUGIN_ID, 
			$this->getMessage('MNG.TITLE.SUB_MENU_MASTER'), $this->getMessage('MNG.TITLE.SUB_MENU_MASTER'), 
			$this->operator_role, self::PLUGIN_ID.'-2', array($this, 'display_menu_master'));
		add_submenu_page(self::PLUGIN_ID, 
			$this->getMessage('MNG.TITLE.SUB_MENU_RESERVATION'), $this->getMessage('MNG.TITLE.SUB_MENU_RESERVATION'), 
			$this->operator_role, self::PLUGIN_ID.'-3', array($this, 'display_menu_reservation'));
	}
	
	//サブメニューを作らないと内部ではエラーが出ているらしく、サブを使いたくない場合非表示にするらしい（本当？）
	public function remove_admin_menu_sub() {
		global $submenu;
		if($submenu[self::PLUGIN_ID][0][2] === self::PLUGIN_ID){
			unset($submenu[self::PLUGIN_ID][0]);
		}
	}
	
	public function display_menu_main(){
		if($_POST['type'] === 'save'){
			if(!isset($_POST[self::CREDENTIAL_NAME]) || !$_POST[self::CREDENTIAL_NAME]) return false;
			if(!check_admin_referer(self::CREDENTIAL_ACTION, self::CREDENTIAL_NAME)) return false;
			//パラメタ取得
			$req_params = $this->stripslashes_request_params('post');
			//画面に表示する処理メッセージ
			$result_message = $this->getMessage('ERR.ERR_OCCURED');
			//妥当性チェック
			$required = array('admin_user_role', 'admin_operator_role','admin_default_locale','admin_rental_buf_days','admin_max_order_num_per_user');
			$validator =$this->createValidator($req_params, $required, false);
			if($validator->validate()){
				//エラーがない場合
				$this->user_role       = $req_params['admin_user_role'];
				$this->operator_role   = $req_params['admin_operator_role'];
				$this->default_locale  = $req_params['admin_default_locale'];
				$this->rental_buf_days = (int)$req_params['admin_rental_buf_days'];
				$this->max_order_num_per_user = (int)$req_params['admin_max_order_num_per_user'];
				
				//wordpressのDBに設定を保存
				$settings = array('user_role'=>$this->user_role, 'operator_role'=>$this->operator_role, 'default_locale'=>$this->default_locale, 
					'rental_buf_days'=>$this->rental_buf_days, 'max_order_num_per_user'=>$this->max_order_num_per_user);
				//plugin dbに保存
				$this->save_option($settings);
				
				//成功の場合のメッセージ
				$result_message = $this->getMessage('OK.SUCCESS');
			}
		}
		
		//HTML表示
		require_once 'template/admin_menu_main_page.php';
	}
	
	public function display_menu_master(){
		require_once 'template/admin_menu_master_page.php';
	}
	
	public function display_menu_reservation(){
		require_once 'template/admin_menu_reservation_page.php';
	}
	
	
	
	
	
	/**
	 * APIでのアクセスの認証をする
	 * HTTPヘッダ(X-WP-Nonce)の認証はWPが勝手に行う。なので、ここではそれ以外の認証をする（権限とか）。
	 */
	public function api_authenticate_operator() : bool {
		if( current_user_can($this->user_role)) return true;
		return false;
	}

	
	protected function create_err_response(string $err_code, array $messages=array()) : WP_REST_Response {
		$errors = NULL;
		$msg = NULL;
		$status = 200;
		switch($err_code){
			case 'invalid_params':
				$errors = array('fields'=> $messages);
				$msg = $this->getMessage('ERR.INVALID_PARAMS', false);
				$status = 400;
				break;
			case 'not_found':
				$errors = $messages;
				$msg = $this->getMessage('ERR.NOT_FOUND', false);
				$status = 404;
				break;
			case 'access_error':
				$errors = $messages;
				$msg = $this->getMessage('ERR.ACCESS_ERROR', false);
				$status = 401;
				break;
			case 'db_error':
				$errors = $messages;
				$msg = $this->getMessage('ERR.DB_ERROR', false);
				$status = 500;
				break;
			default:
				$errors = $messages;
				$status = 500;
		}
		//
		return new WP_REST_Response(array('code'=> $err_code, 'message'=>$msg, 'errors'=> $errors), $status);
	}
	
	
	/**
	 * API(GET)
	 * 注文詳細を取得する
	 * 【パラメタ】
	 * uri:/order_id
	 */
	public function api_get_order() : WP_REST_Response {
		require_once 'dao.php';
		$dao = new RGMP_RentalGoodsManagerDao();
		$user_id = current_user_can($this->operator_role) ? NULL : get_current_user_id();
		$order_id = strrchr($_SERVER["REQUEST_URI"], '/');
		$order_id = substr($order_id,1);
		
		//DBから値取得
		$data = $dao->find_order(0, 1, true, NULL, NULL, NULL, NULL, (int)$order_id, $user_id);
		if($data === false){
			return $this->create_err_response('db_error');
		}
		
		$order = $data['list'][0];
		if(!isset($order)){
			return $this->create_err_response('not_found');
		}
		
		//貸し出した商品の取得と設定
		$rental_goods = array();
		if(count($order['rental_goods_ids']) > 0 ){
			$rental_goods = $dao->list_goods_simple($order['rental_goods_ids']);
		}
		$order['rental_goods'] = $rental_goods;
		
		$response = new WP_REST_Response( $order );
		return $response;
	}
	
	/**
	 * API(GET)
	 * 注文を検索する
	 * 【パラメタ】※プレフィックスpm_は部分一致
	 * pm_goods_name, pm_goods_serialno, pm_user_mail, statuses[], order_by_asc(1,0:結果のソート順)
	 */
	public function api_find_orders() : WP_REST_Response {
		require_once 'dao.php';
		$dao = new RGMP_RentalGoodsManagerDao();
	
		//パラメタ取得
		$req_params = $this->stripslashes_request_params('get');
		//
		$user_id = current_user_can($this->operator_role) ? NULL : get_current_user_id();
		$goods_name = $this->default_val($req_params['pm_goods_name'], NULL); 
		$serialno =  $this->default_val($req_params['pm_goods_serialno'], NULL);
		$user_mail = $this->default_val($req_params['pm_user_mail'], NULL);
		$statuses = $this->default_val($req_params['statuses'], NULL);
		$order_id = ctype_digit($req_params['order_id']) ? (int)$req_params['order_id'] : NULL;
		$offset = $this->default_val($req_params['offset'], 0);
		$limit = $this->default_val($req_params['limit'], 30);
		$order_by_asc = isset($req_params['order_by_asc']) ? $req_params['order_by_asc']=='1' : true;
		
		//妥当性チェック
		$validator =$this->createValidator($req_params);
		if(!$validator->validate()){
			//code, message, status
			return $this->create_err_response('invalid_params', $validator->errors());
		}
		
		//DBから値取得
		$data = $dao->find_order($offset, $limit, $order_by_asc, $goods_name, $serialno, $user_mail, $statuses, $order_id, $user_id);
		$response = new WP_REST_Response( $data );
		return $response;
	}
	
	/**
	 * API(GET)操作者用
	 * 商品マスタを検索する。
	 * 抽出した商品毎に、商品を貸出した注文情報を付与して返す。注文情報は貸出開始日がorder_condition_start_date,order_condition_monthsで指定。
	 * デフォルトは今月1日～3か月。
	 * 【パラメタ】※プレフィックスpm_は部分一致
	 * pm_goods_name, pm_serialno, pm_goods_no, statuses[], term_include,rental_from,rental_to(レンタル可能期間の指定)
	 * order_condition_start_date(注文の取得条件：開始日。デフォルト現在の月の1日), order_condition_months(注文の取得条件：月。デフォルト3か月)
	 */
	public function api_find_goods_masters() : WP_REST_Response {
		require_once 'dao.php';
		$dao = new RGMP_RentalGoodsManagerDao();
		
		//パラメタ取得
		$req_params = $this->stripslashes_request_params('get');
		//
		$goods_name = $this->default_val($req_params['pm_goods_name'], NULL); 
		$serialno =  $this->default_val($req_params['pm_goods_serialno'], NULL);
		$goods_no =  $this->default_val($req_params['pm_goods_no'], NULL);
		$statuses = $this->default_val($req_params['statuses'], NULL);
		$offset = $this->default_val($req_params['offset'], 0);
		$limit = $this->default_val($req_params['limit'], 30);
		$rental_from = $this->default_val($req_params['rental_from'], NULL);
		$rental_to   = $this->default_val($req_params['rental_to'], NULL);
		$term_include = $this->default_val($req_params['term_include'], NULL);
		$term = NULL;
		if(isset($term_include)){
			$term = array('renatal_from'=>$rental_from, 'renatal_to'=>$rental_to,
			 'include'=>(boolean)$term_include, 'buf_days'=>1);
		}
		$order_condition_start_date = $this->default_val($req_params['order_condition_start_date'], NULL);
		$order_condition_months = $this->default_val($req_params['order_condition_months'], '3');
		$order_condition = NULL;
		if(isset($order_condition_start_date)){
			$order_condition = array(
				'start_date' => $order_condition_start_date,
				'months' => $order_condition_months,
			);
		}
		
		//権限がオペレータ以上かチェックする
		if(!current_user_can($this->operator_role)){
			return $this->create_err_response('access_error');
		}
		
		//妥当性チェック
		$validator = $this->createValidator($req_params, NULL, false);
		if(!$validator->validate()){
			//code, message, status
			return $this->create_err_response('invalid_params', $validator->errors());
		}
		
		//DBから値取得
 		$data = $dao->find_goods($offset, $limit, $goods_id, $goods_name, $serialno, $goods_no, $statuses, $term, $order_condition);
		$response = new WP_REST_Response($data);
		return $response;
	}
	
	
	
	/**
	 * API(POST)操作者用
	 * 機器マスタの登録処理。
	 * 【パラメタ】
	 * goods_ids[], goods_names[], goods_serialnos[], goods_nos[], hides[]
	 * @return 
	 */
	public function api_save_goods_master(): WP_REST_Response{
		//権限がオペレータ以上かチェックする
		if(!current_user_can($this->operator_role)){
			return $this->create_err_response('access_error');
		}
		
		//パラメタ取得
		$req_params = $this->stripslashes_request_params('post');
		
		//妥当性チェック
		$validator = $this->createValidator($req_params, array('goods_names.*', 'goods_serialnos.*', 'hides.*'), false);
		if(!$validator->validate()){
			//code, message, status
			return $this->create_err_response('invalid_params', $validator->errors());
		}
		//
		require_once 'dao.php';
		$dao = new RGMP_RentalGoodsManagerDao();
		//
		$ary_goods_id = $req_params['goods_ids'];
		$ary_goods_name = $req_params['goods_names'];
		$ary_goods_serialno = $req_params['goods_serialnos'];
		$ary_goods_no = $req_params['goods_nos'];
		$ary_hide = $req_params['hides'];
		
		//DB登録処理
		$errors = array();
		for($i = 0; $i < count($ary_goods_id); ++$i){
			$goods_id = $ary_goods_id[$i];
			$goods_name = $ary_goods_name[$i];
			$goods_serialno = $ary_goods_serialno[$i];
			$goods_no = $ary_goods_no[$i];
			$hide = strcmp($ary_hide[$i], 'true')==0 ? true : false;
			
			//goods_noはNOT NULLなので値がない場合は設定する
			if(!isset($goods_no)) $goods_no = '';
			
			//機器を登録する
			$data = array(
				'goods_name'=>$goods_name,
				'goods_serialno'=>$goods_serialno,
				'goods_no'=>$goods_no,
				'hide'=>$hide,
			);
			if(!empty($goods_id)) $data['goods_id'] = (int)$goods_id;
			if(!$dao->save_goods_master($data)){
				if(stripos($dao->last_error, 'Duplicate') !== false && stripos($dao->last_error, 'goods_serialno') !== false){
					//goods_serialnoがDB重複登録エラーの場合
					$errors["goods_serialno[$i]"] = array($this->getMessage('ERR.DB_DUPLICATED', false, $goods_serialno));
				}else{
					//それ以外のエラーの場合
					$errors["goods_ids[$i]"] = $this->getMessage('ERR.DB_ERROR', false);
					return $this->create_err_response('db_error', $errors);
				}
				continue; 
			}
		}
		
		//エラー
		if(count($errors) != 0){
			return $this->create_err_response('invalid_params', $errors);
		}
		
		//
		$response = new WP_REST_Response(array('status'=>'success'));
		return $response;
	}
	
	
	/**
	 * API(POST)
	 * 注文情報を保存する(新規作成)
	 * 【パラメタ】
	 * order_id, rental_goods_ids, status
	 */
	public function api_save_order_new() : WP_REST_Response {
		return $this->_api_save_order();
	}
	
	/**
	 * API(POST)
	 * 注文情報を保存する(更新)
	 * 【パラメタ】
	 * order_id, rental_goods_ids, status
	 */
	public function api_save_order_update() : WP_REST_Response {
		$order_id = strrchr($_SERVER["REQUEST_URI"], '/');
		$order_id = substr($order_id,1);
		return $this->_api_save_order((int)$order_id);
	}
	
	/**
	 * API(POST)
	 * 注文情報を保存する
	 * 【パラメタ】
	 * order_id, rental_goods_ids, status, rental_goods_ids[], 
	 */
	protected function _api_save_order(?int $order_id=NULL) : WP_REST_Response {
		require_once 'dao.php';
		$dao = new RGMP_RentalGoodsManagerDao();
		
		//パラメタ取得
		$req_params = $this->stripslashes_request_params('POST');
		//
		$rental_goods_ids = $this->default_val($req_params['rental_goods_ids'], NULL); 
		$status = $this->default_val($req_params['status'], 're'); 
		$rental_from = $req_params['rental_from'];
		$rental_to = $req_params['rental_to'];
		$remarks = $req_params['remarks'];
		$zip = $req_params['user_zip'];
		$address = $req_params['user_address'];
		$department = $req_params['user_department'];
		//管理者の場合は注文検索のユーザIDの制限をしない
		$user_id = current_user_can($this->operator_role) ? NULL : get_current_user_id();
		
		//予約/取消以外の場合は権限がオペレータ以上かチェックする
		if($status !== 're' && $status !== 'ca'){
			if(!current_user_can($this->operator_role)) return $this->create_err_response('access_error');
		}
		
		//注文IDがある場合のチェックと、新規注文の場合のチェック
		$order = array();
		$required = array('user_zip', 'user_address', 'user_department', 'rental_from', 'rental_to', 'order_nums.*', 'goods_names.*');
		if(isset($order_id)){
			//既存注文の変更の場合のチェック(order_idが存在するか？)
			$order_id = (int)$order_id;
			$find_result = $dao->find_order(0, 1, true, NULL,NULL,NULL,NULL,$order_id, $user_id);
			$order = $find_result['list'][0];
			if(!isset($order)){
				error_log("_api_save_order(): order_id not exist. order_id=$order_id, user_id=$user_id");
				return $this->create_err_response('invalid_params', array());
			}
			$required = array('status');
		}else if(!is_null($user_id) && $status !== 'ca'){
			//一般ユーザの場合で新規注文の場合のチェック(1ユーザあたりの最大予約数を超えていないか？)
			$find_result = $dao->find_order(0, 1, true, NULL,NULL,NULL,array('re','ou'),NULL, $user_id);
			if($find_result['amount'] > $this->max_order_num_per_user){
				//一人で注文してよい数を超えた場合はエラー
				error_log("_api_save_order(): over max order num. user_id=$user_id, order num of user={$find_result['amount']}");
				$errors = array();
				$errors['order_id'] = array($this->getMessage('ERR.OVER_ORDER_MAX_NUM', false));
				return $this->create_err_response('invalid_params', $errors);
			}
		}
		
		//妥当性チェック
		$validator = $this->createValidator($req_params, $required, true, true);
		if(!$validator->validate()){
			//code, message, status
			return $this->create_err_response('invalid_params', $validator->errors());
		}

		//値の設定(statusが取消と完了の場合はステータスのみ変更する)---------
		$order['status'] = $status;
		if($status !== 'ca' && $status !== 'cl'){//取消と完了以外の場合だけ値を設定する。
			if(!isset($order_id))  $order['user_id'] = get_current_user_id();
			if(isset($remarks))    $order['remarks'] = $remarks;
			if(isset($department)) $order['user_department'] = $department;
			if(isset($zip))        $order['user_zip'] = $zip;
			if(isset($address))    $order['user_address'] = $address;
			if(isset($rental_from)) $order['rental_from'] = $rental_from;
			if(isset($rental_to))   $order['rental_to'] = $rental_to;
			
			//注文内訳
			$order_goods = array();
			$goods_names = $req_params['goods_names'] ?? array();
			$order_nums = $req_params['order_nums'];
			for($i = 0; $i < count($goods_names); ++$i){
				$order_goods[] = array('goods_name'=>$goods_names[$i], 'order_num'=>$order_nums[$i]);
			}
			if(count($order_goods) > 0) $order['order_goods'] = $order_goods;
			
			//値を設定
			$order['rental_goods_ids'] = $rental_goods_ids;
		}
		
		//注文情報を保存する
		$result = array();
		if($dao->save_order($order, $result, $this->rental_buf_days)){
			//成功時
			$response = new WP_REST_Response(array('status'=>'success'));
		}else if(count($result['rentable_num']) > 0){
			//数が足りなかった場合のエラー処理
			$err_rentable_num = $result['rentable_num'];
			//台数が不足している場合
			$errors = array();
			if($status === 're'){
				//注文受付の場合
				for($i=0; $i <count($goods_names); ++$i){
					$name = $goods_names[$i];
					$num = (int)$order_nums[$i];
					$result_num = -$err_rentable_num[$name];
					if($result_num <= 0) continue;
					$errors['order_nums['.$i.']'] = array($this->getMessage('ERR.OVER_ORDER_GOODS_NUM', false, $num, $result_num));
				}
			}else if($status === 'ou'){
				//注文確定の時
				$result0 = array_slice($err_rentable_num, 0, 1, true);
				$errors['rental_goods_ids'] = array($this->getMessage('ERR.OVER_RENTALED_GOODS_NUM', false, key($result0), -current($result0)));
			}
			$response = $this->create_err_response('invalid_params', $errors);
		}else if(count($result['rentaling_num']) > 0){
			//選択した商品IDがダブルブッキングして貸し出せない場合のエラー処理
			$err_rentaling_num = $result['rentaling_num'];
			$errors['rental_goods_ids'] = array($this->getMessage('ERR.DUPLICATE_RENTALED', false, key($err_rentaling_num)));
			$response = $this->create_err_response('invalid_params', $errors);
		}else{
			//その他のエラー
			$response = $this->create_err_response('db_error', array());
		}
		
		return $response;
	}
	
	/**
	 * API(GET)
	 * 商品マスタの商品名一覧を取得する
	 * 【パラメタ】
	 * なし
	 */
	public function api_find_goods_names_with_count(): WP_REST_Response {
		require_once 'dao.php';
		$dao = new RGMP_RentalGoodsManagerDao();
		$data = $dao->find_goods_name_with_count();
		$response = new WP_REST_Response($data);
		return $response;
	}
	

}



?>
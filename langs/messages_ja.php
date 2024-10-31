<?php 
/*
RentalGoodsManager のメッセージ定義（日本語）
権限グループ⇒Role　　権限⇒capabilities
※ブラウザにも送られるので非公開の情報を記述しないこと
*/


RGMP_RentalGoodsManager::obj()->setMessages(array(
	//管理画面のメニュー名
	'MNG.TITLE.MAIN_MENU'         =>'貸出機器管理',
	'MNG.TITLE.SUB_MENU_SETTINGS' =>'設定',
	'MNG.TITLE.SUB_MENU_MASTER'   =>'貸出機器マスタ',
	'MNG.TITLE.SUB_MENU_RESERVATION'=>'予約注文管理',
	//
	'BTN.BACK' => '戻る',
	'BTN.SAVE' => '保存する',
	'BTN.FIND' => '検索する',
	'BTN.NEW_ORER'  => '新規注文',
	'BTN.CANCEL'    => '取消',
	'BTN.EDIT'      => '編集',
	'BTN.PRINT'     => '印刷',
	'BTN.HISTORY'     => '注文履歴',
	'BTN.ADD'       => '追加',
	'BTN.DEL'       => '削除',
	'BTN.ORDER_REGISTER'  => '予約登録',
	'BTN.PAGE_PREV' => '<前へ',
	'BTN.PAGE_NEXT' => '次へ>',
	'BTN.PREV_MONTH' => '<前の月へ',
	'BTN.NEXT_MONTH' => '次の月へ>',
	'BTN.ALL_CHECK'  => '全選択/全削除',
	
	//日付の期間の間に入れるもの(英語ならto)
	'TEXT.TERM_TO' => '～',
	//値が登録後に設定される値のinputタグに入れる値
	'TEXT.NO_NEED_TO_SET'  => '値の設定不要',
	//
	'RESERVATION_STATUS.re' => '注文受付',
	'RESERVATION_STATUS.ou' => '注文確定',
	'RESERVATION_STATUS.cl' => '完了',
	'RESERVATION_STATUS.ca' => '取消',
	'RESERVATION_STATUS.JS_ARRAY' => '{re: "注文受付", ou:"注文確定", cl:"完了", ca:"取消"}',
	//
	'RESERVATION_USER_TAB_LIST.TITLE'       => '注文',
	'RESERVATION_USER_TAB_PROCESS.TITLE'    => '予約注文',
	//
	'RESERVATION_TAB_MAIN.TITLE'             => '注文情報',
	'RESERVATION_TAB_PROCESS.TITLE'          => '注文の内訳と貸出し処理',
	'RESERVATION_TAB_PROCESS.TITLE.DESIRED_GOODS'  =>'希望の商品',
	'RESERVATION_TAB_PROCESS.TITLE.SELECTED_GOODS' =>'貸出す商品',
	'RESERVATION_TAB_PROCESS.TITLE.SELECTING_GOODS'=>'貸出す商品の選択',
	'RESERVATION_TAB_PROCESS.TITLE.MODAL.GOODS_NUM_LIST'=>'商品の希望数と貸出し数',
	'RESERVATION_TAB_PRINT.TITLE'            => '貸出し書の印刷',
	'RESERVATION_TAB_PRINT.PRINTAREA.TITLE'  => '貸出書',
	'RESERVATION_TAB_PRINT.PRINTAREA.TITLE.RESERVATION_INFO'  => '予約情報',
	'RESERVATION_TAB_PRINT.PRINTAREA.TITLE.SELECTED_GOODS'    => '郵送する商品',
	'RESERVATION_TAB_PRINT.PRINTAREA.SELECTEDGOODS_DESCRIPTION' => "貸出した機器は以下の通りです。\n到着しましたらご確認ください。返却時には機器の箱も含めてすべて返却するようにお願いします。\n"
					. '機器の不足や破損の場合は代替機の購入等お願いする場合があります。',
	'RESERVATION_TAB_HISTORY.TITLE'        => '貸出し履歴',
	'RESERVATION_TAB_HISTORY.TITLE.MODAL.ORDER'  => '注文情報詳細',
	
	
	//管理者用
	'OBJ.ADMIN.OPERATOR_ROLE'  =>'操作者の権限',
	'OBJ.ADMIN.USER_ROLE'      =>'ユーザの権限',
	'OBJ.ADMIN.DEFAULT_LOCALE' =>'デフォルト言語',
	'OBJ.ADMIN.RENTAL_BUF_DAYS'=>'貸出期間予備日',
	'OBJ.ADMIN.MAX_ORDER_NUM_PER_USER'=>'ユーザ最大注文数',
	//ユーザ用
	'OBJ.RESERVATION_ID'  => '予約ID',
	'OBJ.RESERVATION_USER'=> '予約者',
	'OBJ.RESERVATION_USER_ID'=> '予約者ID',
	'OBJ.RESERVATION_DATE'=> '予約日',
	'OBJ.RESERVATION_ORDER_GOODS'=> '予約商品内訳',
	'OBJ.RESERVATION_SELECTED_GOODS_NUM' => '貸出台数',
	'OBJ.RESERVATION_RENTAL_GOODS_IDS'=> '貸出商品ID',
	'OBJ.RESERVATION_TERM'=> '貸出し期間',
	'OBJ.RESERVATION_RENTAL_FROM'=>'貸出開始日',
	'OBJ.RESERVATION_RENTAL_TO'  =>'貸出終了日',
	'OBJ.RESERVATION_ORDER_NUM'  =>'希望台数',
	'OBJ.RESERVATION_SEND_INFO'=>'送付先',
	'OBJ.RESERVATION_STATUS'=> '予約状態',
	'OBJ.MAIL'            => 'メールアドレス',
	'OBJ.USER_ZIP'             => '郵便番号',
	'OBJ.USER_ADDRESS'         => '郵送先住所',
	'OBJ.USER_DEPARTMENT'      => '部署',
	'OBJ.REMARKS'         => '補足事項',
	'OBJ.GOODS_ID'        => '商品ID',
	'OBJ.GOODS_NAME'      => '商品名',
	'OBJ.GOODS_SERIALNO'  => 'シリアルNo',
	'OBJ.GOODS_NO'        => '管理No',
	'OBJ.HIDE'            => '廃止フラグ',
	'OBJ.PROCESS'         => '処理',
	'OBJ.UPDATE'          => '更新',
	'OBJ.ORDER_CONDITION_START_DATE' => '検索開始日',
	'OBJ.ORDER_CONDITION_MONTHS' => '検索期間(月)',
	
	//処理
	'OK.SUCCESS'          => '登録に成功しました',
	'OK.CANCEL'           => 'キャンセルされました',
	'WARN.USER.RESGIST_RE'  => "「予約状態」が「注文受付」の場合は「貸出す商品」がクリアされます。\nよろしいですか？",
	//エラー
	'ERR.ERR_OCCURED'       => 'エラーが発生しました',
	'ERR.UNEXPECTED'        => '予期せぬエラーが発生しました',
	'ERR.INVALID_PARAMS'    => 'パラメタが間違っています',
	'ERR.NOT_FOUND'         => '指定のリソースが見つかりません',
	'ERR.UNAUTHORIZED'      => 'ログアウトしています。ログインしてください。',
	'ERR.ACCESS_ERROR'      => '権限がありません',
	'ERR.DB_ERROR'          => 'DB処理に失敗しました',
	'ERR.DB_DUPLICATED'     => '既に同じ値が登録されています({0})',
	'ERR.OVER_ORDER_MAX_NUM'    => '1人当たりの最大注文予約の数を超えました。',
	'ERR.OVER_ORDER_GOODS_NUM'    => '在庫台数が足りません(注文数:{0}/不足台数:{1})',
	'ERR.OVER_RENTALED_GOODS_NUM' => '貸出し数が在庫数を超えています(不足商品:{0}/不足台数:{1})',
	'ERR.DUPLICATE_RENTALED' => 'その期間は貸出し中です(商品ID:{0})',
	'ERR.OBJ_ADMIN_DEFAULT_LANG' => '{field}は英数字、ハイフン、アンダーバー、ピリオドのみ',
	'ERR.OBJ_USER_ZIP' => '{field}はハイフンなしの数字',
	//HTML上のメッセージ
	'HTML.ERR.NO_CHECK'     => '更新対象の行のチェックを入れてください。',
	
	//機能の説明等-------
	//管理者用
	'HELP.OBJ.ADMIN.OPERATOR_ROLE'    => '予約編集や商品マスタ編集などの管理操作をするのに必要な権限。プラグインが自動で作成しAdministratorグループに付与。Administratorグループ以外に権限を与えたい場合、該当グループにこの権限を設定してください。',
	'HELP.OBJ.ADMIN.USER_ROLE'        => '商品予約などのユーザ操作をするのに必要な権限。例：publish_posts, edit_pages, edit_post。',
	'HELP.OBJ.ADMIN.DEFAULT_LOCALE'   => 'ja,enなどの翻訳に使用する言語コード。ブラウザから送信された言語コードのメッセージファイルが見つからない時にこの値を使用する',
	'HELP.OBJ.ADMIN.RENTAL_BUF_DAYS'  => '貸出期間前後の予備日。予備日も他の人が貸出し予約できない。貸出期間が5日に対し例えば2日間を設定した場合、計9日間が他の人に貸出しできない。',
	'HELP.OBJ.ADMIN.MAX_ORDER_NUM_PER_USER'  => 'ユーザ1人が予約注文できる最大数(オペレータには最大数の制限がありません)。ステータスが「注文受付」「注文確定」の注文の数で判定するので、注文が「取消」「完了」になれば再注文可能。',
	//その他
	'HELP.RESERVATION_USER_TAB_LIST.TITLE'  => 'あなたの貸出し注文を一覧で確認できます。新規注文をする場合は「新規注文」ボタンをクリックしてください。',
	//商品予約管理：注文情報のヘルプ
	'HELP.RESERVATION_MANAGER_CTRL.MAIN.TITLE' => '貸出し注文の編集ができます。予約編集や、貸出す商品IDを指定して貸出し状態にできます。まずは編集対象の予約を検索してください。',
	//予約管理：の希望商品のヘルプ
	'HELP.RESERVATION_MANAGER_CTRL.PROCESS.STATUS'         => '貸出す商品を確定する場合「注文確定」、商品が返却された場合「完了」、注文が取消された場合「取消」に変更してください。',
	'HELP.RESERVATION_MANAGER_CTRL.PROCESS.DESIRED_GOODS'  => 'ユーザが予約した商品の内訳です。情報を編集したい場合は注文商品名と数を編集してください。',
	'HELP.RESERVATION_MANAGER_CTRL.PROCESS.SELECTED_GOODS' => 'ユーザに貸出す商品一覧です。貸出す商品を決定する場合、下の表から選択して、予約状態を「注文確定」に変更して保存してください。',
	'HELP.RESERVATION_MANAGER_CTRL.PROCESS.SELECTING_GOODS'=> '貸出す商品を選択して下さい。商品リンクをクリックすると選択されます。青いバーは既に貸出しが決定している期間です。',
	//注文履歴：ヘルプ
	'HELP.RESERVATION_MANAGER_CTRL.HISTORY.TITLE' => 'ある商品について貸出し履歴を確認できます。部品紛失や故障発覚時に過去の貸出し先を確認するのにも役に立つでしょう。表示されるのは「注文確定」「完了」です。表内のバーを押すと詳細が表示されます。',
	
));

?>
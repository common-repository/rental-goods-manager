=== Rental Goods Manager ===
Contributors: nanajuly
Tags: editor, rental
Requires at least: 5.2.1
Tested up to: 5.4
Stable tag: 1.0
Requires PHP: 7.3.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Rental Goods Manager plugin allows you to manage device lending orders.
One example of a use case is to manage lending for in-house products to various departments as a demo device in-house.
The lending is saved as an order and can be edit / print / view / cancel / return.
WARNING: This plugin is basically intended for internal use(in-house). Only users logged in can be made order.

日本語
Rental Goods Managerプラグインは、機器の貸出注文の管理ができます。
ユースケースの1つの例としては、社内で自社商品などを検証機として様々な部署に貸出すときに、貸出を管理する使い方です。
貸出は注文として保存され、編集/印刷/閲覧/取消/返却などができます。
注意）この機能は基本的に社内利用を想定しています。ログインしたユーザのみがレンタル注文できます。


== Description ==

Rental Goods Manager WordPress plugin allows you to manage rental orders.
The main use case is to use this plugin to manage device lending in in-house.
Users can make rental order.
Managers can edit orders and can reserve ordered goods, and can print order. 
WARNING: This plugin is basically intended for internal use(in-house). Only users logged in can be made order.

●日本語
Rental Goods Managerプラグインは、商品のレンタル注文の管理ができます。
主に社内でデモなどのため別部署に商品の貸出しをする場合に使うことを想定していて、面倒な貸出し履歴などを管理するのが目的です。
Wordpressのユーザはレンタルの注文など、以下のことができます。
注意）この機能は基本的に社内利用を想定しています。ログインしたユーザのみがレンタル注文できます。


Wordpress users can do below.

* Ordering a rental goods. 
* Viewing and canceling your rented orders. 
* Wordpress users can order rentals. 


Wordpress Administrators can do below. (It is called "operator", which has the Role "rental_mng_operator".)

* View all information ordered by the user. 
* Determining the equipment to rent for an order. 
* Manage the status of the rented device. You can also manage the returned items. 
* Print the enclosed loan document when sending the device to the user. 
* View past order history. 
* Changing the text displayed in Wordpress by customizing the message file. 
* Customizing the format of printed order documents. 


Ussage:
1. Go to the "RentalGoodsManger"-"Settings" menu item and change as you like.
   If you want to give operator authority to other than the administrator, 
   please add the Role "rental_mng_operator" with using such as the "User Role Editor" plugin.
2. To register the product master, go to "GoodsMaster" in "RentalGoodsManagemer".
 It can be edited like Excel.
3. Please write the short code "[rental_goods_user_page]" on the page for ordering rental , such as a fixed page of Wordpress.
 You can order a rental by opening the URL after creating the screen.
4. To view the contents of user's order, go to "RentalGoodsManagemer"-"Order".
 Please find order, and press the edit button, and change the status to "confirmed", select the goods device to rent and press the save button.
5. Press the Print button to print out the delivery slip of the rented device and send it with the device.
6. When the device is returned, you should find the order and press edit button in "RentalGoodsManager" - "Order", change the status to "completed" and press the save button.
7. If you want to check the past orders or future orders, please press the history button to confirm.


How to change the display message:
1. Copy one of file under "wp-content/plugins/rental-goods-manager/langs" directory 
   to wordpress theme directory "wp-content/themes/'theme name'/rental-goods-manager/langs".
2. Change the name of the copied file to the locale you want to use. Add "custome_" before locale string.
   Example: "meassages_custom_en.php" for English.
3. Open the file and change the text to the right of "=>" for the desired item. Any changes will be reflected immediately.
   Example: If you want to change the display of the "new order" button to "Rent", 'BTN.NEW_ORER' => 'Rent',
   #Please note that some HTML have a fixed display width.


How to change the validation message:
1. Copy one of file under "wp-content/plugins/rental-goods-manager/Valitron/lang" directory 
   to wordpress theme directory "wp-content/themes/'theme name'/rental-goods-manager/Valitron/lang".
2. Change the name of the copied file to the locale you want to use. Add "custom_" before locale string.
   Example: "custom_en.php" for English.
3. Open the file and change the text to the right of "=>" for the desired item. Any changes will be reflected immediately.


How to change the print screen:
1. Save the files under "wp-content/plugins/rental-goods-manager/custom_sample/template"
Copy it under your theme folder "wp-content/themes/'theme name'/rental-goods-manager/template".
2. Rename the copied file to "tab_print_area_custom.hbs".
3. Open the file and edit the HTML. Since we are using handlebars, dynamic items will be replaced with {{ITEM NAME}}.


●日本語
Wordpressのユーザは以下のことができます。

* レンタルの貸出し注文すること
* 自分がレンタルした注文を閲覧、取消すること
* Wordpressのユーザであればレンタルの注文ができます。


Wordpressの管理者は以下のことができます。

* ユーザが注文した情報をすべて閲覧すること
* 注文に対して貸出す機器を決定すること
* 貸出した機器のステータスを管理すること。返却されたことも管理できます。
* 機器をユーザに送るときに同封する貸出書を印刷すること。
* 過去の注文履歴を閲覧すること
* messageファイルをカスタマイズすることでWordpressに表示される文言を変更すること
* 印刷する貸出書のフォーマットをカスタマイズすること


使い方:
1.管理画面の「貸出機器管理」の「設定」へ行き、変更したい設定を変更してください。
 管理権限を管理者以外にも与えたい場合、「User Role Editor」プラグインなどでロール「rental_mng_operator」を付与してください。
2.商品マスタを登録するには、「貸出機器管理」の「貸出機器マスタ」へ遷移して行ってください。
 Excelのように編集できます。
3.商品レンタルをする画面は、Wordpressの固定ページで、ショートコード[rental_goods_user_page]を記述してください。
 画面作成後にURLを開くとレンタルの注文をすることができます。
4.注文した内容を見るには、「貸出機器管理」の「予約注文管理」へ遷移して行ってください。
 検索し、編集ボタンを押し、ステータスを「注文確定」に変更して、貸出す商品機器を選択して保存ボタンを押してください。
5.貸出す機器の送付票を印刷ボタンを押して、印刷して機器と一緒に送ります。
6.機器が返ってきたら、「貸出機器管理」の「予約注文管理」で検索、編集ボタンを押し、ステータスを「完了」に変更して保存ボタンを押して下さい。
7.もし、過去の注文履歴や、未来の注文を確認したい場合は、注文履歴ボタンを押して確認してください。


表示メッセージを変更する方法:
1."/wp-content/plugins/rental-goods-manager/langs"の配下のファイルの1つを
使用しているテーマフォルダ"/wp-content/themes/テーマ名/rental-goods-manager/langs"の下にコピーする。
2.コピーしたファイルの名前を使用したいロケールに変更する。ロケールの前に"custom_"を名前に付ける必要がある。
例：英語の場合は"meassages_custom_en.php"。
3.ファイルを開き、目的の項目の"=>"の右側のテキストを変更する。変更するとすぐに反映されます。
例：「新規注文」ボタンの表示を「レンタルする」に変更したい場合、'BTN.NEW_ORER'  => 'レンタルする',
※HTMLで表示幅が決まっているものもあるので注意すること。


妥当性チェックのメッセージを変更する方法:
1."/wp-content/plugins/rental-goods-manager/Valitron/lang"の配下のファイルの1つを
使用しているテーマフォルダ"/wp-content/themes/テーマ名/rental-goods-manager/Valitron/lang"の下にコピーする。
2.コピーしたファイルの名前を使用したいロケールに変更する。ロケールの前に"custom_"を名前に付ける必要がある。
例：英語の場合は"custom_en.php"。
3.ファイルを開き、目的の項目の"=>"の右側のテキストを変更する。変更するとすぐに反映されます。


印刷画面を変更する方法:
1."/wp-content/plugins/rental-goods-manager/custom_sample/template"の配下のファイルを
使用しているテーマフォルダ"/wp-content/themes/テーマ名/rental-goods-manager/template"の下にコピーする。
2.コピーしたファイルの名前を"tab_print_area_custom.hbs"に変更する。
3.ファイルを開き、HTML編集をする。handlebarsを使用しているので、動的項目は{{項目名}}で置換される。



== Installation ==

Installation procedure:

1. Deactivate plugin if you have the previous version installed.
2. Extract "rental-goods-manager.zip" archive content to the "/wp-content/plugins/rental-goods-manager" directory.
3. Activate "Rental Goods Manager" plugin via 'Plugins' menu in WordPress admin menu. 



●日本語
インストール方法:
1.古いバージョンがインストールされていれば無効にしてください。
2.rental-goods-manager.zipを解凍し、"/wp-content/plugins/rental-goods-manager"ディレクトリに入れてください。
3.Wordpress上の管理画面から、有効化してください。



== Frequently Asked Questions ==

Nothing..




== Screenshots ==

1. operator: edit goods master
2. operator: make user main screen
3. user: display order main screen
4. user: order lending goods.
5. operator: search the order
6. opertor: edit the order status
7. operator: print the order
8. operator: display orders history

日本語にも対応しています。




= Translations =

Japanese
English
if you make messages file, any language is applied.



== Changelog =

= [1.0] 2020.04.21 =

* New: Create Plugin



== Arbitrary section ==

use Handsontable 6.2.2. Library(MIT License).
use flatpickr.js Library.
use handlebars.js Library.
use just-handlebars-helpers.js Library.


== Upgrade Notice ==
Nothing..



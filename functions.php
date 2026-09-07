<?php
/**
 * SWELL CHILD - SIAORB Corporate
 *
 * 合同会社SIAORB コーポレートサイト用 子テーマ functions.php
 *
 * @package SWELL_CHILD_SIAORB
 */

// 直接アクセス禁止
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 子テーマのバージョン定数
 */
define( 'SIAORB_CHILD_VERSION', '7.0.0' );

/* ==========================================================================
 * アセット読み込み
 * ========================================================================== */

/**
 * 欧文・和文フォント（Google Fonts）の読み込み
 *
 * Chakra Petch … 欧文。角を落とした直線的な字面で、技術寄りの印象になる。
 * Murecho      … 和文。字幅が狭く縦のストロークが立っているので、
 *                丸ゴシック寄りの書体よりクールに見える。
 *                Chakra Petch を先に書いているので、
 *                ラテン文字は Chakra Petch、かなと漢字は Murecho が担当する。
 */
function siaorb_enqueue_fonts() {
	wp_enqueue_style(
		'siaorb-fonts',
		'https://fonts.googleapis.com/css2'
			. '?family=Chakra+Petch:wght@400;500;600;700'
			. '&family=Murecho:wght@400;500;700'
			. '&display=swap',
		array(),
		null
	);
}
add_action( 'wp_enqueue_scripts', 'siaorb_enqueue_fonts' );

/**
 * Google Fonts への接続を先に開ける
 */
function siaorb_preconnect_fonts( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = array( 'href' => 'https://fonts.googleapis.com' );
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => '' );
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'siaorb_preconnect_fonts', 10, 2 );

/**
 * カスタム CSS / JS の読み込み
 */
function siaorb_enqueue_assets() {
	// カスタム CSS
	$css_path = get_stylesheet_directory() . '/assets/css/custom.css';
	if ( file_exists( $css_path ) ) {
		wp_enqueue_style(
			'siaorb-custom',
			get_stylesheet_directory_uri() . '/assets/css/custom.css',
			array(),
			SIAORB_CHILD_VERSION
		);
	}

	// カスタム JS
	$js_path = get_stylesheet_directory() . '/assets/js/custom.js';
	if ( file_exists( $js_path ) ) {
		wp_enqueue_script(
			'siaorb-custom',
			get_stylesheet_directory_uri() . '/assets/js/custom.js',
			array(),
			SIAORB_CHILD_VERSION,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'siaorb_enqueue_assets' );

/* ==========================================================================
 * SWELL フック
 * ========================================================================== */

/**
 * フロントページで SWELL のデフォルト要素を抑制する
 *
 * - 「さぁ、始めよう。」= 固定ページのエディタコンテンツ → the_content をクリア
 * - #post_slider      = SWELLのピックアップスライダー  → フック解除 + CSS で非表示
 * - SWELL MV          = 管理画面設定のMV               → フック解除
 */
function siaorb_suppress_swell_on_front() {
	if ( ! is_front_page() ) {
		return;
	}

	// 固定ページのエディタコンテンツ（「さぁ、始めよう。」等）を空にする
	add_filter( 'the_content', '__return_empty_string', 99 );

	// SWELL のトップスライダー・MV フックを解除（フック名はSWELLバージョンにより変わる可能性あり）
	remove_action( 'swell_before_main',         'swell_the_topslider', 10 );
	remove_action( 'swell_before_main',         'swell_the_mv',        10 );
	remove_action( 'swell_main_before_content', 'swell_the_topslider', 10 );
	remove_action( 'swell_main_before_content', 'swell_the_mv',        10 );
}
add_action( 'wp', 'siaorb_suppress_swell_on_front' );

/* ==========================================================================
 * ショートコード
 * ========================================================================== */

/**
 * 会社概要テーブル ショートコード
 * 使い方: [siaorb_company_info]
 */
function siaorb_company_info_shortcode() {
	ob_start();
	?>
	<table class="siaorb-company-table">
		<tbody>
			<tr>
				<th>会社名</th>
				<td>合同会社SIAORB</td>
			</tr>
			<tr>
				<th>設立</th>
				<td>2023年5月</td>
			</tr>
			<tr>
				<th>代表社員</th>
				<td>白川 翔太</td>
			</tr>
			<tr>
				<th>所在地</th>
				<td>東京都北区田端6-6-16-201</td>
			</tr>
			<tr>
				<th>URL</th>
				<td><a href="https://siaorb.com" target="_blank" rel="noopener noreferrer">https://siaorb.com</a></td>
			</tr>
			<tr>
				<th>メール</th>
				<td><a href="mailto:info@siaorb.com">info@siaorb.com</a></td>
			</tr>
			<tr>
				<th>事業内容</th>
				<td>
					<ul>
						<li>Web解析業務 / グロースハック</li>
						<li>Webサイト制作 / システム開発 / アプリ開発のディレクション</li>
						<li>ウェブ解析士 / 上級ウェブ解析士 認定講座の開講</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
	return ob_get_clean();
}
add_shortcode( 'siaorb_company_info', 'siaorb_company_info_shortcode' );

/**
 * お問い合わせセクション用 ショートコード
 * Contact Form 7 が未設定の場合のフォールバック表示
 * 使い方: [siaorb_contact_placeholder]
 */
function siaorb_contact_placeholder_shortcode() {
	ob_start();
	?>
	<div class="siaorb-contact__notice">
		<p>お問い合わせフォームを準備中です。</p>
		<p>お急ぎの方はメールにてご連絡ください：
			<a href="mailto:info@siaorb.com">info@siaorb.com</a>
		</p>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'siaorb_contact_placeholder', 'siaorb_contact_placeholder_shortcode' );

/* ==========================================================================
 * SWELL フロントページ最適化
 * ========================================================================== */

/**
 * フロントページでヘッダー（#header）を非表示にする
 */
function siaorb_hide_header_on_front() {
	if ( ! is_front_page() ) {
		return;
	}
	// ヘッダー本体と、スクロールで出てくる追従ヘッダーの両方を止める
	echo '<style>' .
		'#header,.l-header,.p-header{display:none!important;}' .
		'#fix_header,.l-fixHeader,.p-fixHeader,.c-fixHeader,' .
		'.-fix-header,.l-header__bar,#sp_menu_btn,.p-spMenu{display:none!important;}' .
		'body.-fix-header,body.is-fixHeader{padding-top:0!important;}' .
	'</style>' . "\n";
}
add_action( 'wp_head', 'siaorb_hide_header_on_front', 99 );

/* フッターロゴは削除 */

/**
 * フロントページではサイドバーを非表示にする
 * SWELL の is_active_sidebar チェックを無効化
 */
function siaorb_disable_sidebar_on_front( $active ) {
	if ( is_front_page() ) {
		return false;
	}
	return $active;
}
add_filter( 'is_active_sidebar', 'siaorb_disable_sidebar_on_front' );

/**
 * フロントページのボディクラスにカスタムクラスを追加
 */
function siaorb_body_classes( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'siaorb-is-front';
		// SWELL のサイドバーレイアウトを無効化するクラスを追加
		$classes[] = 'is-layout-one-column';
	}
	return $classes;
}
add_filter( 'body_class', 'siaorb_body_classes' );

/* ==========================================================================
 * ファビコン
 * ========================================================================== */

/**
 * 子テーマの assets/images/favicon.ico をファビコンとして出力する
 */
function siaorb_favicon() {
	$favicon_url = get_stylesheet_directory_uri() . '/assets/images/favicon.ico';
	echo '<link rel="icon" href="' . esc_url( $favicon_url ) . '" type="image/x-icon">' . "\n";
	echo '<link rel="shortcut icon" href="' . esc_url( $favicon_url ) . '" type="image/x-icon">' . "\n";
}
add_action( 'wp_head', 'siaorb_favicon', 1 );
add_action( 'admin_head', 'siaorb_favicon', 1 );

/* ==========================================================================
 * ユーティリティ
 * ========================================================================== */

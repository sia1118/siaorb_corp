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
define( 'SIAORB_CHILD_VERSION', '1.0.0' );

/* ==========================================================================
 * アセット読み込み
 * ========================================================================== */

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

// 必要に応じて SWELL のアクション/フィルターフックを追加
// 例: add_filter( 'swell_xxx', 'siaorb_custom_function' );

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
				<td><!-- TODO: 設立年月を入力 --></td>
			</tr>
			<tr>
				<th>代表社員</th>
				<td><!-- TODO: 代表者名を入力 --></td>
			</tr>
			<tr>
				<th>所在地</th>
				<td><!-- TODO: 住所を入力 --></td>
			</tr>
			<tr>
				<th>URL</th>
				<td><a href="https://siaorb.co.jp" target="_blank" rel="noopener noreferrer">https://siaorb.co.jp</a></td>
			</tr>
			<tr>
				<th>メール</th>
				<td><a href="mailto:info@siaorb.co.jp">info@siaorb.co.jp</a></td>
			</tr>
			<tr>
				<th>事業内容</th>
				<td>
					<ul>
						<li>Web解析業務 / グロースハック</li>
						<li>Webサイト / システム開発 / アプリ開発ディレクション・PM業務</li>
						<li>ウェブ解析士認定講座 / 上級ウェブ解析士講座の開講</li>
						<li>ウェブディレクタースキル開発・コーチング事業</li>
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
			<a href="mailto:info@siaorb.co.jp">info@siaorb.co.jp</a>
		</p>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'siaorb_contact_placeholder', 'siaorb_contact_placeholder_shortcode' );

/* ==========================================================================
 * ユーティリティ
 * ========================================================================== */

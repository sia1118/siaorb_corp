<?php
/**
 * フロントページ テンプレート
 *
 * 構成:
 *   1. ナビゲーション（フロントページ専用・固定）
 *   2. ヒーロー（グラデーションの地 + 層に分けたロゴ + 極大の欧文）
 *   3. サービス（1事業 = 1チャプター、全3件）
 *      2 と 3 は仕切りを入れずにつなげてある（.sia-lead）。
 *      ヒーローのロゴがそのまま左へ移り、上昇曲線が章ごとの図版に変形していく。
 *   4. 最新記事（記事があるときだけ／最大3件）
 *   5. 会社概要
 *   6. お問い合わせ（Contact Form 7）
 *   7. 追従の相談ボタン
 *
 * スクロール演出は data-reveal / data-drift / data-scramble 属性で指定し、
 * 実際の付け外しは assets/js/custom.js が行う。
 *
 * @package SWELL_CHILD_SIAORB
 */

get_header();

$uri      = get_stylesheet_directory_uri();
$dir      = get_stylesheet_directory();
$blog_url = get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' );

/**
 * Contact Form 7 のフォームID
 * 管理画面「お問い合わせ」でフォームを作り直したらここを差し替える。
 */
$cf7_id = '3df22ad';

/**
 * 最新記事。新しい順に最大3件。
 * 記事が1件も無いときはセクションごと出さず、
 * ナビゲーションからも News を落とす（$has_posts で両方を制御する）。
 */
$news_query = new WP_Query( array(
	'post_type'           => 'post',
	'posts_per_page'      => 3,
	'post_status'         => 'publish',
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );
$has_posts = ! empty( $news_query->posts );

/**
 * ロゴの上昇曲線。座標は 1200 x 1200 の viewBox。
 * ヒーローのロゴ、サービスの図版の出発点、お問い合わせの印で共通に使う。
 */
$logo_curve = 'M 92 914 C 322 914 342 666 600 616 C 858 566 878 318 1108 318';

/**
 * 事業は3つ。ヒーローの Analyze / Improve / Teach がそのまま3章に対応する。
 *
 * 写真は使わず、章ごとに線の図版を持たせる。座標は $logo_curve と同じ viewBox。
 *   line     … 主線。ロゴの曲線がスクロールでこの形に変わる（custom.js）
 *   guides   … 薄い補助線
 *   axis     … 軸
 *   branches … 主線に添えるシアンの線（外へ広がる円など）
 *   nodes    … 主線の上に置く点。node で形（dot = 丸 / diamond = ひし形）を選ぶ
 *   pulse    … true にすると、シアンの線が外へ広がる動きを繰り返す（追従するロゴのときだけ）
 */
$services = array(
	array(
		'en'       => 'Analyze',
		'group'    => 'クライアントワーク',
		'name'     => 'Web解析 / グロースハック',
		'desc'     => 'データに基づいた改善施策でコンバージョン率を向上させます。LPO・EFO・A/Bテストなどのグロースハック手法を活用し、定量的な根拠のある施策でビジネス成果に直結する改善を実行します。',
		'tags'     => array( 'LPO', 'EFO', 'A/Bテスト', 'GA4解析' ),
		// 折れ線グラフ
		'line'     => 'M120 900L290 800L440 850L600 620L760 690L920 430L1090 250',
		'guides'   => 'M100 300H1100M100 500H1100M100 700H1100',
		'axis'     => 'M100 180V920H1120',
		'branches' => array(),
		'node'     => 'dot',
		'pulse'    => false,
		'nodes'    => array( array( 120, 900 ), array( 290, 800 ), array( 440, 850 ), array( 600, 620 ), array( 760, 690 ), array( 920, 430 ), array( 1090, 250 ) ),
	),
	array(
		'en'       => 'Improve',
		'group'    => 'クライアントワーク',
		'name'     => 'Web / システム / アプリ開発のディレクション',
		'desc'     => 'Webサイト・Webシステム・スマートフォンアプリの開発を、ディレクター・プロジェクトマネージャーとして推進。要件定義から納品まで一貫してサポートします。',
		'tags'     => array( '要件定義', 'プロジェクト管理', 'ベンダーコントロール' ),
		// 工程を一段ずつ上がっていく階段
		'line'     => 'M110 900H370V690H630V480H890V270H1100',
		'guides'   => 'M240 900V960M500 690V960M760 480V960M1020 270V960',
		'axis'     => 'M100 960H1120',
		'branches' => array(),
		'node'     => 'diamond',
		'pulse'    => false,
		'nodes'    => array( array( 240, 900 ), array( 500, 690 ), array( 760, 480 ), array( 1020, 270 ) ),
	),
	array(
		'en'       => 'Teach',
		'group'    => '教育・育成',
		'name'     => 'ウェブ解析士 / 上級ウェブ解析士 認定講座',
		'desc'     => '一般社団法人ウェブ解析士協会（WACA）認定の講師として、ウェブ解析士・上級ウェブ解析士の認定講座を開講。実務で即戦力となるスキルの習得をサポートします。',
		'tags'     => array( 'ウェブ解析士', '上級ウェブ解析士', 'WACA認定講座' ),
		// 中心から外へ広がっていく円。ロゴの外環と同じく、一部が開いている。
		'line'     => 'M675 730A150 150 0 1 1 750 600',
		'guides'   => '',
		'axis'     => '',
		'branches' => array( 'M735 834A270 270 0 1 1 870 600', 'M765 886A330 330 0 1 1 930 600', 'M795 938A390 390 0 1 1 990 600' ),
		'node'     => 'dot',
		'pulse'    => true,
		'nodes'    => array( array( 600, 600 ) ),
	),
);

/**
 * 図版のうち、主線の下に敷く部分（補助線・軸・シアンの線）を出す。
 */
$fig_under = function ( $s ) {
	if ( $s['guides'] ) {
		echo '<path class="sia-fig__guide" d="' . esc_attr( $s['guides'] ) . '"></path>';
	}
	if ( $s['axis'] ) {
		echo '<path class="sia-fig__axis" d="' . esc_attr( $s['axis'] ) . '"></path>';
	}
	foreach ( $s['branches'] as $d ) {
		echo '<path class="sia-fig__branch sia-draw" pathLength="100" d="' . esc_attr( $d ) . '"></path>';
	}
};

/**
 * 図版のうち、主線の上に載せる点を出す。
 */
/**
 * ロゴの環。太さと大きさは現行のロゴの比率に合わせてある。
 * 外環は一部が開いているので、全周の 78% だけ線を引く（CSS 側で指定）。
 */
$logo_outer = '<circle cx="600" cy="600" r="581" pathLength="100" fill="none" stroke="var(--sia-blue)" stroke-width="37" stroke-linecap="round" transform="rotate(130 600 600)"></circle>';
$logo_inner = '<circle cx="600" cy="600" r="433" pathLength="100" fill="none" stroke="var(--sia-cyan)" stroke-width="32"></circle>';

$fig_nodes = function ( $s ) {
	foreach ( $s['nodes'] as $pt ) {
		$x = (int) $pt[0];
		$y = (int) $pt[1];
		if ( 'diamond' === $s['node'] ) {
			printf(
				'<rect class="sia-fig__node" x="%1$d" y="%2$d" width="34" height="34" transform="rotate(45 %3$d %4$d)"></rect>',
				$x - 17,
				$y - 17,
				$x,
				$y
			);
		} else {
			printf( '<circle class="sia-fig__node" cx="%1$d" cy="%2$d" r="12"></circle>', $x, $y );
		}
	}
};

?>

<!-- ================================================================
     固定背景
     ページ全体の後ろに敷きっぱなしにする一枚。
     セクションを斜めに断ち切って隙間を空けてあるので、
     スクロールするとこの面が合間から覗く。
     ================================================================ -->
<div class="sia-bg" aria-hidden="true">
	<canvas class="sia-bg__dots" id="sia-bg-dots"></canvas>
	<svg class="sia-bg__mark" viewBox="0 0 1200 1200" focusable="false">
		<circle cx="600" cy="600" r="516" pathLength="100" fill="none"
			stroke="rgba(104,212,224,0.16)" stroke-width="18" stroke-linecap="round"
			stroke-dasharray="78 22" transform="rotate(130 600 600)"></circle>
		<circle cx="600" cy="600" r="386" fill="none"
			stroke="rgba(104,212,224,0.10)" stroke-width="14"></circle>
		<path d="M 92 914 C 322 914 342 666 600 616 C 858 566 878 318 1108 318"
			fill="none" stroke="rgba(120,150,255,0.18)" stroke-width="30" stroke-linecap="round"></path>
	</svg>
</div>

<!-- ================================================================
     ナビゲーション
     ================================================================ -->
<header class="sia-nav" id="sia-nav">
	<div class="sia-nav__inner">
		<a class="sia-nav__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php if ( file_exists( $dir . '/assets/images/siaorb_logo_default.svg' ) ) : ?>
				<img src="<?php echo esc_url( $uri . '/assets/images/siaorb_logo_default.svg' ); ?>"
					alt="合同会社SIAORB" width="120" height="120">
			<?php else : ?>
				<span class="sia-nav__logo-text">SIAORB</span>
			<?php endif; ?>
		</a>

		<nav class="sia-nav__menu" aria-label="サイト内メニュー">
			<a href="#services">Services</a>
			<?php if ( $has_posts ) : ?>
				<a href="#news">News</a>
			<?php endif; ?>
			<a href="#company">Company</a>
			<a href="<?php echo esc_url( $blog_url ); ?>">Blog</a>
		</nav>

		<a href="#contact" class="sia-btn sia-btn--solid sia-btn--sm sia-nav__cta">相談する</a>
	</div>
</header>

<!-- ================================================================
     1〜2. ヒーローとサービス
     仕切りを入れずにひと続きにする。地・粒子・ロゴはこの中で共有する。
     ================================================================ -->
<div class="sia-lead" id="sia-lead">

	<!-- 地のグラデーション。ブランドの2色だけで作る。 -->
	<span class="sia-hero__wash" aria-hidden="true"></span>

	<!-- 奥を流れる粒子。PC 幅でだけ描く（custom.js）。 -->
	<canvas class="sia-hero__dots" id="sia-hero-dots" aria-hidden="true"></canvas>

	<!--
		追従するロゴ。JS が動くときだけ表示する。
		ヒーローのロゴの位置から始まり、スクロールすると章ごとの置き場所へ移っていく。
		その間に、手前の環から順に広がって消え、残った上昇曲線が
		data-shapes の形（1章 → 2章 → 3章）へ順に変わっていく。
		置き場所と大きさは custom.js が決める（PC では章ごとに左右が入れ替わる）。
		表示しないとき（動きを減らす設定・JS なし）は、
		ヒーローの中の .sia-logo と、各章の図版（.sia-chapter__fig）が代わりに出る。
	-->
	<div class="sia-lead__stage" aria-hidden="true">
		<div class="sia-lead__logo" id="sia-lead-logo">
			<svg class="sia-lead__layer sia-lead__layer--outer" viewBox="0 0 1200 1200" focusable="false"><?php echo $logo_outer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></svg>
			<svg class="sia-lead__layer sia-lead__layer--inner" viewBox="0 0 1200 1200" focusable="false"><?php echo $logo_inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></svg>
			<svg class="sia-lead__layer sia-lead__layer--main" id="sia-stage" viewBox="0 0 1200 1200" focusable="false"
				data-shapes="<?php echo esc_attr( wp_json_encode( array_merge( array( $logo_curve ), wp_list_pluck( $services, 'line' ) ) ) ); ?>">
				<?php foreach ( $services as $n => $s ) : ?>
					<g class="sia-stage__under<?php echo $s['pulse'] ? ' sia-stage__under--pulse' : ''; ?>" data-k="<?php echo (int) $n + 1; ?>"><?php $fig_under( $s ); ?></g>
				<?php endforeach; ?>
				<path class="sia-fig__line sia-stage__line" pathLength="100" stroke-width="56" d="<?php echo esc_attr( $logo_curve ); ?>"></path>
				<?php foreach ( $services as $n => $s ) : ?>
					<g class="sia-stage__nodes" data-k="<?php echo (int) $n + 1; ?>"><?php $fig_nodes( $s ); ?></g>
				<?php endforeach; ?>
			</svg>
		</div>
	</div>

<!-- ================================================================
     1. ヒーロー
     ================================================================ -->
<section class="sia-hero" id="hero">

	<!--
		動かないロゴ。追従するロゴ（.sia-lead__stage）を出さないときに見せる。
		開いた外環 + シアンの内環 + 中を貫く上昇曲線。
		追従するロゴを出すときも、出発点の位置と大きさを測るために場所だけは残す。
	-->
	<div class="sia-logo" aria-hidden="true">
		<svg class="sia-logo__layer sia-logo__layer--outer" viewBox="0 0 1200 1200" focusable="false"><?php echo $logo_outer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></svg>
		<svg class="sia-logo__layer sia-logo__layer--inner" viewBox="0 0 1200 1200" focusable="false"><?php echo $logo_inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></svg>
		<svg class="sia-logo__layer sia-logo__layer--wave" viewBox="0 0 1200 1200" focusable="false">
			<path pathLength="100" d="<?php echo esc_attr( $logo_curve ); ?>"
				fill="none" stroke="var(--sia-blue)" stroke-width="56" stroke-linecap="round"></path>
		</svg>
	</div>

	<div class="sia-hero__inner">
		<h1 class="sia-hero__title">
			<span class="sia-hero__line" data-scramble="500">Analyze.</span>
			<span class="sia-hero__line" data-scramble="500">Improve.</span>
			<span class="sia-hero__line" data-scramble="500">Teach.</span>
		</h1>

		<p class="sia-hero__ja">解析して、改善して、その方法を、教える。</p>

		<p class="sia-hero__lead">
			合同会社SIAORBは、Web解析とグロースハックを軸に企業の意思決定と実行を支援し、
			その手法を次のウェブ担当者へ手渡す教育事業を行っています。
		</p>

		<div class="sia-hero__actions">
			<a href="#contact" class="sia-btn sia-btn--solid">相談する</a>
			<a href="#services" class="sia-btn sia-btn--line">サービスを見る</a>
		</div>
	</div>
</section>

<!-- ================================================================
     2. サービス（1事業 = 1チャプター）
     ================================================================ -->
<section class="sia-sec sia-sec--services" id="services" aria-labelledby="services-title">

	<div class="sia-wrap sia-story">

		<div class="sia-story__chapters">
			<div class="sia-sechead" data-reveal="slide">
				<h2 class="sia-sechead__en" id="services-title">
					<span class="sia-slash" aria-hidden="true"></span>
					<span class="sia-sechead__text" data-scramble>Services</span>
				</h2>
				<p class="sia-sechead__ja">3つの事業</p>
			</div>

			<?php foreach ( $services as $n => $s ) : ?>
				<article class="sia-chapter" data-chapter>
					<span class="sia-chapter__num" aria-hidden="true" data-drift="70"><?php echo esc_html( sprintf( '%02d', $n + 1 ) ); ?></span>

					<figure class="sia-chapter__fig" data-reveal="draw" aria-hidden="true">
						<svg class="sia-fig" viewBox="60 200 1100 800" focusable="false">
							<?php $fig_under( $s ); ?>
							<path class="sia-fig__line sia-draw" pathLength="100" d="<?php echo esc_attr( $s['line'] ); ?>"></path>
							<?php $fig_nodes( $s ); ?>
						</svg>
					</figure>

					<div class="sia-chapter__body" data-reveal="shear">
						<p class="sia-chapter__en" data-scramble><?php echo esc_html( $s['en'] ); ?></p>
						<p class="sia-chapter__group"><?php echo esc_html( $s['group'] ); ?></p>
						<h3 class="sia-chapter__name"><?php echo esc_html( $s['name'] ); ?></h3>
						<p class="sia-chapter__desc"><?php echo esc_html( $s['desc'] ); ?></p>
						<ul class="sia-chapter__tags">
							<?php foreach ( $s['tags'] as $tag ) : ?>
								<li><?php echo esc_html( $tag ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

</div><!-- /.sia-lead -->

<?php if ( $has_posts ) : ?>
<!-- ================================================================
     3. 最新記事
     セクションの断ち切りと隙間は .sia-sec が共通で持っているので、
     ここに置くだけで前後と同じようにパララックスの背景が覗く。
     ================================================================ -->
<section class="sia-sec sia-sec--news" id="news" aria-labelledby="news-title">
	<div class="sia-wrap">
		<div class="sia-sechead" data-reveal="slide">
			<h2 class="sia-sechead__en" id="news-title">
				<span class="sia-slash" aria-hidden="true"></span>
				<span class="sia-sechead__text" data-scramble>News</span>
			</h2>
			<p class="sia-sechead__ja">最新記事</p>
		</div>

		<div class="sia-news">
			<?php
			while ( $news_query->have_posts() ) :
				$news_query->the_post();
				$cats = get_the_category();
				?>
				<article class="sia-news__item" data-reveal="shear">
					<a href="<?php the_permalink(); ?>" class="sia-news__link">
						<div class="sia-news__thumb">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php
								the_post_thumbnail( 'medium_large', array(
									'loading'  => 'lazy',
									'decoding' => 'async',
								) );
								?>
							<?php else : ?>
								<span class="sia-news__nothumb" aria-hidden="true"></span>
							<?php endif; ?>
						</div>

						<div class="sia-news__meta">
							<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>">
								<?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?>
							</time>
							<?php if ( $cats ) : ?>
								<span class="sia-news__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
							<?php endif; ?>
						</div>

						<h3 class="sia-news__title"><?php the_title(); ?></h3>
					</a>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>

		<p class="sia-sec__foot" data-reveal="shear">
			<a href="<?php echo esc_url( $blog_url ); ?>" class="sia-btn sia-btn--line">More</a>
		</p>
	</div>
</section>
<?php endif; ?>

<!-- ================================================================
     4. 会社概要
     ================================================================ -->
<section class="sia-sec sia-sec--company" id="company" aria-labelledby="company-title">
	<div class="sia-wrap">
		<div class="sia-sechead" data-reveal="slide">
			<h2 class="sia-sechead__en" id="company-title">
				<span class="sia-slash" aria-hidden="true"></span>
				<span class="sia-sechead__text" data-scramble>Company</span>
			</h2>
			<p class="sia-sechead__ja">会社概要</p>
		</div>

		<p class="sia-vision">
			<span class="sia-vision__line" data-reveal="shear">データとクリエイティブの力で、</span>
			<span class="sia-vision__line" data-reveal="shear">すべての企業の成長を加速させる。</span>
		</p>

		<div class="sia-def" data-reveal="shear">
			<p class="sia-def__label">約束していること</p>
			<div class="sia-def__body">
				<ul class="sia-mission">
					<li>誠実なデータ分析で、正しい意思決定を支援する</li>
					<li>クライアントのビジネス成果に最大限コミットする</li>
					<li>デジタルマーケティングの知見を社会に還元する</li>
				</ul>
			</div>
		</div>

		<div class="sia-def" data-reveal="shear">
			<p class="sia-def__label">代表挨拶</p>
			<div class="sia-def__body">
				<div class="sia-greeting">
					<figure class="sia-greeting__media">
						<?php if ( file_exists( $dir . '/assets/images/shirakawa_01.png' ) ) : ?>
							<img src="<?php echo esc_url( $uri . '/assets/images/shirakawa_01.png' ); ?>"
								alt="代表社員 白川 翔太" loading="lazy" decoding="async">
						<?php else : ?>
							<span class="sia-greeting__noimg" aria-hidden="true"></span>
						<?php endif; ?>
					</figure>

					<div class="sia-greeting__text">
						<p>この度は、合同会社SIAORBのウェブサイトをご覧いただきありがとうございます。</p>
						<p>私たちは「データとクリエイティブの力で成果を出す」をモットーに、Web解析・グロースハックを軸としたデジタルマーケティング支援を行っています。</p>
						<p>クライアントの皆様と共に課題に向き合い、データに基づいた施策で確実な成果をお届けすることをお約束します。</p>
						<p class="sia-greeting__sign">
							<span class="sia-greeting__role">代表社員</span>
							<span class="sia-greeting__name">白川 翔太</span>
						</p>
					</div>
				</div>
			</div>
		</div>

		<div class="sia-def" data-reveal="shear">
			<p class="sia-def__label">会社データ</p>
			<div class="sia-def__body">
				<?php echo do_shortcode( '[siaorb_company_info]' ); ?>
			</div>
		</div>
	</div>
</section>

<!-- ================================================================
     5. お問い合わせ（Contact Form 7）
     ================================================================ -->
<section class="sia-sec sia-sec--blue sia-sec--last" id="contact" aria-labelledby="contact-title">

	<!-- 章ごとに形を変えてきた線が、ここでロゴに戻る。画面に入ったら一度だけ描く。 -->
	<svg class="sia-contact__mark" viewBox="0 0 1200 1200" data-reveal="draw" data-drift="-60" aria-hidden="true" focusable="false">
		<circle class="sia-draw" style="--len: 78" cx="600" cy="600" r="516" pathLength="100"
			transform="rotate(130 600 600)"></circle>
		<circle class="sia-draw sia-contact__mark-inner" cx="600" cy="600" r="386" pathLength="100"></circle>
		<path class="sia-draw" pathLength="100" d="<?php echo esc_attr( $logo_curve ); ?>"></path>
	</svg>

	<div class="sia-wrap">
		<div class="sia-sechead" data-reveal="slide">
			<h2 class="sia-sechead__en" id="contact-title">
				<span class="sia-slash" aria-hidden="true"></span>
				<span class="sia-sechead__text" data-scramble>Contact</span>
			</h2>
			<p class="sia-sechead__ja">ご相談</p>
		</div>

		<div class="sia-contact" data-reveal="shear">
			<p class="sia-contact__lead">
				サービスの内容、費用、講座の日程。どれもお気軽にどうぞ。<br>
				通常1営業日以内にご返信いたします。
			</p>

			<div class="sia-form">
				<?php
				if ( shortcode_exists( 'contact-form-7' ) ) {
					echo do_shortcode( '[contact-form-7 id="' . esc_attr( $cf7_id ) . '"]' );
				} else {
					?>
					<p class="sia-form__fallback">
						お問い合わせフォームは準備中です。メールでご連絡ください。<br>
						<a href="mailto:info@siaorb.com">info@siaorb.com</a>
					</p>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</section>

<!-- 追従の相談ボタン。SWELL の「TOPへ戻る」は右下なので、こちらは左下に置く。 -->
<a href="#contact" class="sia-badge" id="sia-badge">相談する</a>

<?php get_footer(); ?>

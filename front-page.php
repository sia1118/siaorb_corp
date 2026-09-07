<?php
/**
 * フロントページ テンプレート
 *
 * 構成:
 *   1. ナビゲーション（フロントページ専用・固定）
 *   2. ヒーロー（グラデーションの地 + 立体で回るロゴ + 極大の欧文）
 *   3. サービス（1事業 = 1チャプター、全3件。背景のロゴがスクロールに連動）
 *   4. 最新記事（$show_news で切り替え）
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

// 記事セクションの表示切り替え（記事が揃ったら true に）
$show_news = false;

$news_query = null;
$has_posts  = false;
if ( $show_news ) {
	$news_query = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post_status'    => 'publish',
	) );
	$has_posts = $news_query->have_posts();
}

/**
 * 事業は3つ。ヒーローの Analyze / Improve / Teach がそのまま3章に対応する。
 */
$services = array(
	array(
		'en'    => 'Analyze',
		'group' => 'クライアントワーク',
		'image' => 'service-01.jpg',
		'alt'   => 'Web解析・グロースハックのイメージ',
		'name'  => 'Web解析 / グロースハック',
		'desc'  => 'データに基づいた改善施策でコンバージョン率を向上させます。LPO・EFO・A/Bテストなどのグロースハック手法を活用し、定量的な根拠のある施策でビジネス成果に直結する改善を実行します。',
		'tags'  => array( 'LPO', 'EFO', 'A/Bテスト', 'GA4解析' ),
	),
	array(
		'en'    => 'Improve',
		'group' => 'クライアントワーク',
		'image' => 'service-02.jpg',
		'alt'   => 'Web / システム / アプリ開発ディレクションのイメージ',
		'name'  => 'Web / システム / アプリ開発のディレクション',
		'desc'  => 'Webサイト・Webシステム・スマートフォンアプリの開発を、ディレクター・プロジェクトマネージャーとして推進。要件定義から納品まで一貫してサポートします。',
		'tags'  => array( '要件定義', 'プロジェクト管理', 'ベンダーコントロール' ),
	),
	array(
		'en'    => 'Teach',
		'group' => '教育・育成',
		'image' => 'service-03.jpg',
		'alt'   => 'ウェブ解析士認定講座のイメージ',
		'name'  => 'ウェブ解析士 / 上級ウェブ解析士 認定講座',
		'desc'  => '一般社団法人ウェブ解析士協会（WACA）認定の講師として、ウェブ解析士・上級ウェブ解析士の認定講座を開講。実務で即戦力となるスキルの習得をサポートします。',
		'tags'  => array( 'ウェブ解析士', '上級ウェブ解析士', 'WACA認定講座' ),
	),
);

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
			<a href="#company">Company</a>
			<a href="<?php echo esc_url( $blog_url ); ?>">Blog</a>
		</nav>

		<a href="#contact" class="sia-btn sia-btn--solid sia-btn--sm sia-nav__cta">相談する</a>
	</div>
</header>

<!-- ================================================================
     1. ヒーロー
     ================================================================ -->
<section class="sia-hero" id="hero">

	<!-- 地のグラデーション。ブランドの2色だけで作る。 -->
	<span class="sia-hero__wash" aria-hidden="true"></span>

	<!--
		ロゴの形（開いた外環 + シアンの内環 + 中を貫く上昇曲線）を立体で置く。
		外環は Z 軸で回り、内環は Y 軸で往復し、全体はスクロールで傾く。
	-->
	<div class="sia-orb" aria-hidden="true">
		<div class="sia-orb__gyro">
			<span class="sia-orb__ring"></span>
			<span class="sia-orb__ring2"></span>
			<svg class="sia-orb__wave" viewBox="0 0 1200 1200" focusable="false">
				<path pathLength="100"
					d="M 92 914 C 322 914 342 666 600 616 C 858 566 878 318 1108 318"
					fill="none" stroke="var(--sia-blue)" stroke-width="56" stroke-linecap="round"></path>
			</svg>
		</div>
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

	<div class="sia-wrap">
		<div class="sia-sechead" data-reveal="slide">
			<h2 class="sia-sechead__en" id="services-title">
				<span class="sia-slash" aria-hidden="true"></span>
				<span class="sia-sechead__text" data-scramble>Services</span>
			</h2>
			<p class="sia-sechead__ja">3つの事業</p>
		</div>
	</div>

	<?php foreach ( $services as $n => $s ) : ?>
		<article class="sia-chapter sia-chapter--<?php echo ( $n % 2 ) ? 'b' : 'a'; ?>">
			<div class="sia-wrap sia-chapter__grid">
				<figure class="sia-chapter__media" data-reveal="wipe" data-drift="16">
					<img src="<?php echo esc_url( $uri . '/assets/images/' . $s['image'] ); ?>"
						alt="<?php echo esc_attr( $s['alt'] ); ?>" loading="lazy" decoding="async">
				</figure>

				<div class="sia-chapter__body" data-reveal="shear" data-drift="-22">
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
			</div>
		</article>
	<?php endforeach; ?>
</section>

<?php if ( $show_news && $has_posts ) : ?>
<!-- ================================================================
     3. 最新記事
     ================================================================ -->
<section class="sia-sec" id="news" aria-labelledby="news-title">
	<div class="sia-wrap">
		<div class="sia-sechead" data-reveal="slide">
			<h2 class="sia-sechead__en" id="news-title">
				<span class="sia-slash" aria-hidden="true"></span>
				<span class="sia-sechead__text" data-scramble>Journal</span>
			</h2>
			<p class="sia-sechead__ja">解析と改善の記録</p>
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
								<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
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

		<p class="sia-sec__foot">
			<a href="<?php echo esc_url( $blog_url ); ?>" class="sia-btn sia-btn--line">すべての記事を見る</a>
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
	<div class="sia-wrap">
		<div class="sia-sechead" data-reveal="slide">
			<h2 class="sia-sechead__en" id="contact-title">
				<span class="sia-slash" aria-hidden="true"></span>
				<span class="sia-sechead__text" data-scramble>Contact</span>
			</h2>
			<p class="sia-sechead__ja">ご相談</p>
		</div>

		<div class="sia-contact">
			<div class="sia-contact__main" data-reveal="shear">
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

			<aside class="sia-contact__aside" data-reveal="shear">
				<h3 class="sia-contact__aside-title">フォーム以外でも</h3>
				<dl>
					<div>
						<dt>メール</dt>
						<dd><a href="mailto:info@siaorb.com">info@siaorb.com</a></dd>
					</div>
					<div>
						<dt>所在地</dt>
						<dd>東京都北区田端6-6-16-201</dd>
					</div>
				</dl>
			</aside>
		</div>
	</div>
</section>

<!-- 追従の相談ボタン。SWELL の「TOPへ戻る」は右下なので、こちらは左下に置く。 -->
<a href="#contact" class="sia-badge" id="sia-badge">相談する</a>

<?php get_footer(); ?>

<?php
/**
 * フロントページ テンプレート
 * @package SWELL_CHILD_SIAORB
 */

get_header();

// 最新記事の事前クエリ（0件ならセクションごとトルツメ）
$news_query = new WP_Query( array(
	'post_type'      => 'post',
	'posts_per_page' => 3,
	'post_status'    => 'publish',
) );
$has_posts = $news_query->have_posts();
wp_reset_postdata();
?>

<!-- ================================================================
     1. FV
     ================================================================ -->
<section class="siaorb-fv" id="fv" aria-label="ファーストビュー">
	<div class="siaorb-fv__bg" aria-hidden="true">
		<div class="siaorb-fv__bg-grid"></div>
		<!-- 球体 canvas は JS で動的に挿入 -->
	</div>

	<!-- FV 左上ロゴ -->
	<div class="siaorb-fv__logo">
		<?php
		$wl_path = get_stylesheet_directory() . '/assets/images/siaorb_logo_white.svg';
		$wl_url  = get_stylesheet_directory_uri() . '/assets/images/siaorb_logo_white.svg';
		if ( file_exists( $wl_path ) ) :
			?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<img src="<?php echo esc_url( $wl_url ); ?>" alt="合同会社SIAORB" width="180" height="auto">
			</a>
		<?php else : ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="siaorb-fv__logo-text">SIAORB</a>
		<?php endif; ?>
	</div>

	<div class="siaorb-fv__inner">
		<p class="siaorb-fv__eyebrow">DATA-DRIVEN DIGITAL MARKETING</p>
		<h1 class="siaorb-fv__title">
			デジタルマーケティングで<br>
			ビジネスを次のステージへ
		</h1>
		<p class="siaorb-fv__desc">
			合同会社SIAORBは、Web解析・グロースハックを軸に<br class="siaorb-br--pc">
			企業のデジタルトランスフォーメーションを支援します。
		</p>
		<div class="siaorb-fv__actions">
			<a href="#contact" class="siaorb-fv__btn siaorb-fv__btn--primary">お問い合わせ</a>
			<a href="#services" class="siaorb-fv__btn siaorb-fv__btn--secondary">サービスを見る</a>
		</div>
	</div>
	<div class="siaorb-fv__scroll" aria-hidden="true">
		<span class="siaorb-fv__scroll-line"></span>
		<span class="siaorb-fv__scroll-label">SCROLL</span>
	</div>
</section>

<!-- ================================================================
     2. 最新記事（記事が1件以上ある場合のみ表示）
     ================================================================ -->
<?php if ( $has_posts ) : ?>
<section class="siaorb-news" id="news" aria-label="最新記事">
	<div class="siaorb-section-inner">
		<div class="siaorb-section-header">
			<h2 class="siaorb-section-header__title">LATEST NEWS</h2>
		</div>

		<div class="siaorb-news__grid">
			<?php
			// 再クエリ（事前チェック済みなので必ず記事あり）
			$news_query = new WP_Query( array(
				'post_type'      => 'post',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
			) );
			while ( $news_query->have_posts() ) :
				$news_query->the_post();
				$cats = get_the_category();
				?>
				<article class="siaorb-news__item">
					<a href="<?php the_permalink(); ?>" class="siaorb-news__link">
						<div class="siaorb-news__thumb">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'medium_large', array(
									'class'   => 'siaorb-news__img',
									'loading' => 'lazy',
								) ); ?>
							<?php else : ?>
								<div class="siaorb-news__no-thumb" aria-hidden="true"></div>
							<?php endif; ?>
						</div>
						<div class="siaorb-news__body">
							<div class="siaorb-news__meta">
								<time class="siaorb-news__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>">
									<?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?>
								</time>
								<?php if ( $cats ) : ?>
									<span class="siaorb-news__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
								<?php endif; ?>
							</div>
							<h3 class="siaorb-news__title"><?php the_title(); ?></h3>
							<p class="siaorb-news__excerpt">
								<?php echo esc_html( wp_trim_words( get_the_excerpt(), 45, '…' ) ); ?>
							</p>
						</div>
					</a>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>

		<div class="siaorb-section-footer">
			<?php
			$blog_url = get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' );
			?>
			<a href="<?php echo esc_url( $blog_url ); ?>" class="siaorb-btn siaorb-btn--outline">
				すべての記事を見る
			</a>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ================================================================
     3. サービス内容
     ================================================================ -->
<section class="siaorb-services" id="services" aria-label="サービス内容">
	<div class="siaorb-section-inner">
		<div class="siaorb-section-header">
			<h2 class="siaorb-section-header__title">SERVICES</h2>
		</div>

		<!-- 01 写真左・テキスト右 -->
		<div class="siaorb-services__row">
			<figure class="siaorb-services__photo">
				<?php $img1 = get_stylesheet_directory_uri() . '/assets/images/service-01.jpg'; ?>
				<img src="<?php echo esc_url( $img1 ); ?>" alt="Web解析・グロースハックのイメージ" loading="lazy"
					onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
				<div class="siaorb-services__photo-placeholder" style="display:none;" aria-hidden="true">
					<span>Web解析 / グロースハック</span>
				</div>
			</figure>
			<div class="siaorb-services__body">
				<span class="siaorb-services__number">01</span>
				<h3 class="siaorb-services__name">Web解析 / グロースハック</h3>
				<p class="siaorb-services__desc">
					データに基づいた改善施策でコンバージョン率を向上させます。
					LPO・EFO・A/Bテストなどのグロースハック手法を活用し、
					定量的な根拠のある施策でビジネス成果に直結する改善を実行します。
				</p>
				<ul class="siaorb-services__tags">
					<li>LPO</li><li>EFO</li><li>A/Bテスト</li><li>GA4解析</li>
				</ul>
			</div>
		</div>

		<!-- 02 テキスト左・写真右 -->
		<div class="siaorb-services__row siaorb-services__row--reverse">
			<figure class="siaorb-services__photo">
				<?php $img2 = get_stylesheet_directory_uri() . '/assets/images/service-02.jpg'; ?>
				<img src="<?php echo esc_url( $img2 ); ?>" alt="Web/システム開発ディレクションのイメージ" loading="lazy"
					onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
				<div class="siaorb-services__photo-placeholder" style="display:none;" aria-hidden="true">
					<span>Web / システム / アプリ開発</span>
				</div>
			</figure>
			<div class="siaorb-services__body">
				<span class="siaorb-services__number">02</span>
				<h3 class="siaorb-services__name">Web / システム / アプリ開発<br>ディレクション・PM業務</h3>
				<p class="siaorb-services__desc">
					Webサイト・Webシステム・スマートフォンアプリの開発を、
					ディレクター・プロジェクトマネージャーとして推進。
					要件定義から納品まで一貫してサポートします。
				</p>
				<ul class="siaorb-services__tags">
					<li>要件定義</li><li>プロジェクト管理</li><li>ベンダーコントロール</li>
				</ul>
			</div>
		</div>

		<!-- 03 写真左・テキスト右 -->
		<div class="siaorb-services__row">
			<figure class="siaorb-services__photo">
				<?php $img3 = get_stylesheet_directory_uri() . '/assets/images/service-03.jpg'; ?>
				<img src="<?php echo esc_url( $img3 ); ?>" alt="ウェブ解析士認定講座のイメージ" loading="lazy"
					onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
				<div class="siaorb-services__photo-placeholder" style="display:none;" aria-hidden="true">
					<span>ウェブ解析士認定講座</span>
				</div>
			</figure>
			<div class="siaorb-services__body">
				<span class="siaorb-services__number">03</span>
				<h3 class="siaorb-services__name">ウェブ解析士認定講座 /<br>上級ウェブ解析士講座</h3>
				<p class="siaorb-services__desc">
					一般社団法人ウェブ解析士協会（WACA）認定の講師として、
					ウェブ解析士・上級ウェブ解析士の認定講座を開講。
					実務で即戦力となるスキルの習得をサポートします。
				</p>
				<ul class="siaorb-services__tags">
					<li>ウェブ解析士</li><li>上級ウェブ解析士</li><li>WACA認定講座</li>
				</ul>
			</div>
		</div>

		<!-- 04 テキスト左・写真右 -->
		<div class="siaorb-services__row siaorb-services__row--reverse">
			<figure class="siaorb-services__photo">
				<?php $img4 = get_stylesheet_directory_uri() . '/assets/images/service-04.jpg'; ?>
				<img src="<?php echo esc_url( $img4 ); ?>" alt="ウェブディレクタースキル開発のイメージ" loading="lazy"
					onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
				<div class="siaorb-services__photo-placeholder" style="display:none;" aria-hidden="true">
					<span>スキル開発・コーチング</span>
				</div>
			</figure>
			<div class="siaorb-services__body">
				<span class="siaorb-services__number">04</span>
				<h3 class="siaorb-services__name">ウェブディレクター<br>スキル開発・コーチング</h3>
				<p class="siaorb-services__desc">
					次世代のウェブディレクターを育成するコーチング事業。
					実務経験を持つ講師が、キャリアアップや独立に向けた
					スキル開発を1on1で個別にサポートします。
				</p>
				<ul class="siaorb-services__tags">
					<li>1on1コーチング</li><li>スキルアップ</li><li>キャリア支援</li>
				</ul>
			</div>
		</div>
	</div>
</section>

<!-- ================================================================
     4. 企業理念・代表挨拶・会社概要（統合セクション）
     ================================================================ -->
<section class="siaorb-company siaorb-philosophy" id="company" aria-label="企業理念・会社概要">
	<div class="siaorb-section-inner">
		<div class="siaorb-section-header">
			<h2 class="siaorb-section-header__title">COMPANY</h2>
		</div>

		<!-- VISION / MISSION: 1カラム（フル幅） -->
		<div class="siaorb-philosophy__vision">
			<h3 class="siaorb-philosophy__vision-title">VISION</h3>
			<p class="siaorb-philosophy__vision-text">
				データとクリエイティブの力で、<br>すべての企業の成長を加速させる。
			</p>
			<h3 class="siaorb-philosophy__mission-title">MISSION</h3>
			<ul class="siaorb-philosophy__mission-list">
				<li>誠実なデータ分析で、正しい意思決定を支援する</li>
				<li>クライアントのビジネス成果に最大限コミットする</li>
				<li>デジタルマーケティングの知見を社会に還元する</li>
			</ul>
		</div>

		<!-- 代表挨拶: 2カラム（写真 | テキスト） -->
		<div class="siaorb-philosophy__greeting">
			<div class="siaorb-philosophy__greeting-image">
				<?php
				$photo_path = get_stylesheet_directory() . '/assets/images/shirakawa_01.png';
				$photo_url  = get_stylesheet_directory_uri() . '/assets/images/shirakawa_01.png';
				if ( file_exists( $photo_path ) ) :
				?>
					<img src="<?php echo esc_url( $photo_url ); ?>"
						alt="代表社員 白川 翔太"
						loading="lazy"
						class="siaorb-philosophy__greeting-photo">
				<?php else : ?>
					<div class="siaorb-philosophy__greeting-photo-placeholder" aria-hidden="true"></div>
				<?php endif; ?>
			</div>
			<div class="siaorb-philosophy__greeting-body">
				<h3 class="siaorb-philosophy__greeting-title">代表挨拶</h3>
				<div class="siaorb-philosophy__greeting-text">
					<p>この度は、合同会社SIAORBのウェブサイトをご覧いただきありがとうございます。</p>
					<p>私たちは「データとクリエイティブの力で成果を出す」をモットーに、Web解析・グロースハックを軸としたデジタルマーケティング支援を行っています。</p>
					<p>クライアントの皆様と共に課題に向き合い、データに基づいた施策で確実な成果をお届けすることをお約束します。</p>
				</div>
				<div class="siaorb-philosophy__greeting-sign">
					<p class="siaorb-philosophy__greeting-role">代表社員</p>
					<p class="siaorb-philosophy__greeting-name">
						<span class="siaorb-philosophy__greeting-name-ja">白川 翔太</span>
					</p>
				</div>
			</div>
		</div>

		<!-- 会社概要テーブル -->
		<div class="siaorb-company__inner siaorb-company__table-wrap">
			<?php echo do_shortcode( '[siaorb_company_info]' ); ?>
		</div>
	</div>
</section>

<!-- ================================================================
     6. お問い合わせ
     ================================================================ -->
<section class="siaorb-contact" id="contact" aria-label="お問い合わせ">
	<div class="siaorb-section-inner">
		<div class="siaorb-section-header">
			<h2 class="siaorb-section-header__title">CONTACT</h2>
		</div>
		<p class="siaorb-contact__lead">
			サービスに関するご質問・ご相談はお気軽にお問い合わせください。<br>
			通常1営業日以内にご返信いたします。
		</p>
		<div class="siaorb-contact__form">
			<?php
			if ( function_exists( 'wpcf7' ) ) :
				echo do_shortcode( '[contact-form-7 id="3df22ad" title="お問い合わせ"]' );
			else :
				?>
				<div class="siaorb-contact__notice">
					<p>お問い合わせフォームを準備中です。</p>
					<p>お急ぎの方はメールにてご連絡ください：
						<a href="mailto:info@siaorb.com">info@siaorb.com</a>
					</p>
				</div>
				<?php
			endif;
			?>
		</div>
	</div>
</section>

<?php get_footer(); ?>

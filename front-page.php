<?php
/**
 * フロントページ テンプレート
 *
 * TOPページ セクション構成:
 * 1. MV（メインビジュアル）
 * 2. 最新記事セクション
 * 3. サービス内容セクション
 * 4. 企業理念・代表挨拶セクション
 * 5. 会社概要セクション
 * 6. お問い合わせフォームセクション
 *
 * @package SWELL_CHILD_SIAORB
 */

get_header();
?>

<!-- ================================================================
     1. MV（メインビジュアル）
     SWELLのメインビジュアル機能を使用する場合は管理画面で設定。
     以下はフォールバック用のカスタムMV。
     ================================================================ -->
<?php if ( ! function_exists( 'swell_mv' ) || ! get_theme_mod( 'mv_use', false ) ) : ?>
<section class="siaorb-mv" id="mv">
	<div class="siaorb-mv__inner">
		<div class="siaorb-mv__content">
			<p class="siaorb-mv__lead">データドリブンで、成果にコミットする。</p>
			<h1 class="siaorb-mv__title">
				デジタルマーケティングで<br>
				ビジネスを次のステージへ
			</h1>
			<p class="siaorb-mv__desc">
				合同会社SIAORBは、Web解析・グロースハックを軸に<br>
				企業のデジタルトランスフォーメーションを支援します。
			</p>
			<div class="siaorb-mv__actions">
				<a href="#contact" class="siaorb-mv__btn siaorb-mv__btn--primary">お問い合わせ</a>
				<a href="#services" class="siaorb-mv__btn siaorb-mv__btn--secondary">サービスを見る</a>
			</div>
		</div>
		<div class="siaorb-mv__image">
			<!-- TODO: MV用の画像を差し込む -->
			<div class="siaorb-mv__image-placeholder" aria-hidden="true"></div>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ================================================================
     2. 最新記事セクション
     ================================================================ -->
<section class="siaorb-news" id="news">
	<div class="siaorb-section-inner">
		<div class="siaorb-section-header">
			<span class="siaorb-section-header__en">NEWS</span>
			<h2 class="siaorb-section-header__ja">最新記事</h2>
		</div>

		<div class="siaorb-news__list">
			<?php
			$news_query = new WP_Query( array(
				'post_type'      => 'post',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
			) );

			if ( $news_query->have_posts() ) :
				while ( $news_query->have_posts() ) :
					$news_query->the_post();
					$cats = get_the_category();
					?>
					<article class="siaorb-news__item">
						<a href="<?php the_permalink(); ?>" class="siaorb-news__link">
							<div class="siaorb-news__thumb">
								<?php if ( has_post_thumbnail() ) : ?>
									<?php the_post_thumbnail( 'medium', array( 'class' => 'siaorb-news__img', 'loading' => 'lazy' ) ); ?>
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
								<p class="siaorb-news__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 40, '…' ) ); ?></p>
							</div>
						</a>
					</article>
					<?php
				endwhile;
				wp_reset_postdata();
			else :
				?>
				<p class="siaorb-news__empty">記事を準備中です。</p>
			<?php endif; ?>
		</div>

		<div class="siaorb-section-footer">
			<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>" class="siaorb-btn siaorb-btn--outline">
				すべての記事を見る
			</a>
		</div>
	</div>
</section>

<!-- ================================================================
     3. サービス内容セクション
     ================================================================ -->
<section class="siaorb-services" id="services">
	<div class="siaorb-section-inner">
		<div class="siaorb-section-header">
			<span class="siaorb-section-header__en">SERVICES</span>
			<h2 class="siaorb-section-header__ja">サービス内容</h2>
		</div>

		<div class="siaorb-services__grid">

			<!-- サービス 1 -->
			<div class="siaorb-services__item">
				<div class="siaorb-services__icon" aria-hidden="true">
					<!-- TODO: SVGアイコンに差し替える -->
					<span class="siaorb-services__icon-placeholder">📊</span>
				</div>
				<h3 class="siaorb-services__name">Web解析 / グロースハック</h3>
				<p class="siaorb-services__desc">
					データに基づいた改善施策でコンバージョン率を向上させます。
					LPO・EFO・A/Bテストなどのグロースハック手法を活用し、
					ビジネス成果に直結する改善を実行します。
				</p>
				<ul class="siaorb-services__tags">
					<li>LPO</li>
					<li>EFO</li>
					<li>A/Bテスト</li>
					<li>GA4解析</li>
				</ul>
			</div>

			<!-- サービス 2 -->
			<div class="siaorb-services__item">
				<div class="siaorb-services__icon" aria-hidden="true">
					<span class="siaorb-services__icon-placeholder">💻</span>
				</div>
				<h3 class="siaorb-services__name">Web / システム / アプリ開発<br>ディレクション・PM業務</h3>
				<p class="siaorb-services__desc">
					Webサイト・Webシステム・スマートフォンアプリの開発を、
					ディレクター・プロジェクトマネージャーとして推進。
					要件定義から納品まで一貫してサポートします。
				</p>
				<ul class="siaorb-services__tags">
					<li>要件定義</li>
					<li>プロジェクト管理</li>
					<li>ベンダーコントロール</li>
				</ul>
			</div>

			<!-- サービス 3 -->
			<div class="siaorb-services__item">
				<div class="siaorb-services__icon" aria-hidden="true">
					<span class="siaorb-services__icon-placeholder">🎓</span>
				</div>
				<h3 class="siaorb-services__name">ウェブ解析士認定講座 /<br>上級ウェブ解析士講座</h3>
				<p class="siaorb-services__desc">
					一般社団法人ウェブ解析士協会（WACA）認定の講師として、
					ウェブ解析士・上級ウェブ解析士の認定講座を開講。
					実務に直結したスキルの習得をサポートします。
				</p>
				<ul class="siaorb-services__tags">
					<li>ウェブ解析士</li>
					<li>上級ウェブ解析士</li>
					<li>WACA認定講座</li>
				</ul>
			</div>

			<!-- サービス 4 -->
			<div class="siaorb-services__item">
				<div class="siaorb-services__icon" aria-hidden="true">
					<span class="siaorb-services__icon-placeholder">🚀</span>
				</div>
				<h3 class="siaorb-services__name">ウェブディレクター<br>スキル開発・コーチング</h3>
				<p class="siaorb-services__desc">
					次世代のウェブディレクターを育成するコーチング事業。
					実務経験を持つ講師が、キャリアアップや独立に向けた
					スキル開発を個別にサポートします。
				</p>
				<ul class="siaorb-services__tags">
					<li>1on1コーチング</li>
					<li>スキルアップ</li>
					<li>キャリア支援</li>
				</ul>
			</div>

		</div>
	</div>
</section>

<!-- ================================================================
     4. 企業理念・代表挨拶セクション
     ================================================================ -->
<section class="siaorb-philosophy" id="philosophy">
	<div class="siaorb-section-inner">
		<div class="siaorb-section-header">
			<span class="siaorb-section-header__en">PHILOSOPHY</span>
			<h2 class="siaorb-section-header__ja">企業理念・代表挨拶</h2>
		</div>

		<div class="siaorb-philosophy__inner">

			<!-- 企業理念 -->
			<div class="siaorb-philosophy__vision">
				<h3 class="siaorb-philosophy__vision-title">VISION</h3>
				<p class="siaorb-philosophy__vision-text">
					<!-- TODO: 企業ビジョン文を入力 -->
					データとクリエイティブの力で、<br>すべての企業の成長を加速させる。
				</p>

				<h3 class="siaorb-philosophy__mission-title">MISSION</h3>
				<ul class="siaorb-philosophy__mission-list">
					<li><!-- TODO: ミッション項目を入力 -->誠実なデータ分析で、正しい意思決定を支援する</li>
					<li>クライアントのビジネス成果に最大限コミットする</li>
					<li>デジタルマーケティングの知見を社会に還元する</li>
				</ul>
			</div>

			<!-- 代表挨拶 -->
			<div class="siaorb-philosophy__greeting">
				<div class="siaorb-philosophy__greeting-image">
					<!-- TODO: 代表者の写真を差し込む -->
					<div class="siaorb-philosophy__greeting-photo-placeholder" aria-hidden="true"></div>
				</div>
				<div class="siaorb-philosophy__greeting-body">
					<h3 class="siaorb-philosophy__greeting-title">代表挨拶</h3>
					<div class="siaorb-philosophy__greeting-text">
						<p>
							<!-- TODO: 代表挨拶文を入力 -->
							この度は、合同会社SIAORBのウェブサイトをご覧いただきありがとうございます。
						</p>
						<p>
							私たちは「データとクリエイティブの力で成果を出す」をモットーに、
							Web解析・グロースハックを軸としたデジタルマーケティング支援を行っています。
						</p>
						<p>
							クライアントの皆様と共に課題に向き合い、データに基づいた施策で
							確実な成果をお届けすることをお約束します。
						</p>
					</div>
					<div class="siaorb-philosophy__greeting-sign">
						<p class="siaorb-philosophy__greeting-role">代表社員</p>
						<p class="siaorb-philosophy__greeting-name">
							<!-- TODO: 代表者名を入力 -->
							<span class="siaorb-philosophy__greeting-name-ja">〇〇 〇〇</span>
						</p>
					</div>
				</div>
			</div>

		</div>
	</div>
</section>

<!-- ================================================================
     5. 会社概要セクション
     ================================================================ -->
<section class="siaorb-company" id="company">
	<div class="siaorb-section-inner">
		<div class="siaorb-section-header">
			<span class="siaorb-section-header__en">COMPANY</span>
			<h2 class="siaorb-section-header__ja">会社概要</h2>
		</div>

		<div class="siaorb-company__inner">
			<?php echo do_shortcode( '[siaorb_company_info]' ); ?>
		</div>
	</div>
</section>

<!-- ================================================================
     6. お問い合わせフォームセクション
     ================================================================ -->
<section class="siaorb-contact" id="contact">
	<div class="siaorb-section-inner">
		<div class="siaorb-section-header">
			<span class="siaorb-section-header__en">CONTACT</span>
			<h2 class="siaorb-section-header__ja">お問い合わせ</h2>
		</div>

		<p class="siaorb-contact__lead">
			サービスに関するご質問・ご相談はお気軽にお問い合わせください。<br>
			通常2〜3営業日以内にご返信いたします。
		</p>

		<div class="siaorb-contact__form">
			<?php
			// Contact Form 7 が有効な場合はショートコードで呼び出す
			// TODO: CF7 フォームID を設定後に差し替える
			if ( function_exists( 'wpcf7' ) ) :
				echo do_shortcode( '[contact-form-7 id="YOUR_FORM_ID" title="お問い合わせ"]' );
			else :
				?>
				<div class="siaorb-contact__notice">
					<p>お問い合わせフォームを準備中です。</p>
					<p>
						お急ぎの方はメールにてご連絡ください：
						<a href="mailto:info@siaorb.co.jp">info@siaorb.co.jp</a>
					</p>
				</div>
				<?php
			endif;
			?>
		</div>
	</div>
</section>

<?php
get_footer();

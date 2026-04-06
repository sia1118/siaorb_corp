<?php
/**
 * カテゴリアーカイブ テンプレート (category.php)
 *
 * /blog/category/{slug}/ に適用。
 * home.php と同じデザインでカテゴリ絞り込み一覧を表示する。
 *
 * @package SWELL_CHILD_SIAORB
 */

get_header();

$cat = get_queried_object();
?>

<div class="siaorb-archive">

	<!-- ページヘッダー -->
	<div class="siaorb-archive__hero">
		<div class="siaorb-archive__hero-inner">
			<span class="siaorb-archive__hero-en">CATEGORY</span>
			<h1 class="siaorb-archive__hero-title"><?php echo esc_html( $cat->name ); ?></h1>
			<?php if ( $cat->description ) : ?>
				<p class="siaorb-archive__hero-desc"><?php echo esc_html( $cat->description ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<div class="siaorb-archive__body">
		<div class="siaorb-archive__inner">

			<main class="siaorb-archive__main" id="main" role="main">

				<?php if ( have_posts() ) : ?>

					<div class="siaorb-archive__grid">
						<?php
						while ( have_posts() ) :
							the_post();
							$cats = get_the_category();
							?>
							<article class="siaorb-archive__item" id="post-<?php the_ID(); ?>">
								<a href="<?php the_permalink(); ?>" class="siaorb-archive__link">
									<div class="siaorb-archive__thumb">
										<?php if ( has_post_thumbnail() ) : ?>
											<?php the_post_thumbnail( 'medium_large', array(
												'class'   => 'siaorb-archive__img',
												'loading' => 'lazy',
											) ); ?>
										<?php else : ?>
											<div class="siaorb-archive__no-thumb" aria-hidden="true"></div>
										<?php endif; ?>
									</div>
									<div class="siaorb-archive__body-inner">
										<div class="siaorb-archive__meta">
											<time class="siaorb-archive__date" datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>">
												<?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?>
											</time>
											<?php if ( $cats ) : ?>
												<span class="siaorb-archive__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
											<?php endif; ?>
										</div>
										<h2 class="siaorb-archive__title"><?php the_title(); ?></h2>
										<p class="siaorb-archive__excerpt">
											<?php echo esc_html( wp_trim_words( get_the_excerpt(), 50, '…' ) ); ?>
										</p>
										<span class="siaorb-archive__more">続きを読む →</span>
									</div>
								</a>
							</article>
							<?php
						endwhile;
						?>
					</div>

					<!-- ページネーション -->
					<nav class="siaorb-archive__pagination" aria-label="記事ページナビゲーション">
						<?php
						the_posts_pagination( array(
							'mid_size'           => 2,
							'prev_text'          => '← 前へ',
							'next_text'          => '次へ →',
							'before_page_number' => '<span class="screen-reader-text">ページ </span>',
						) );
						?>
					</nav>

				<?php else : ?>

					<div class="siaorb-archive__empty">
						<p>この カテゴリの記事が見つかりませんでした。</p>
						<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>" class="siaorb-btn siaorb-btn--outline">
							記事一覧へ戻る
						</a>
					</div>

				<?php endif; ?>

			</main>

			<!-- サイドバー（検索・カテゴリ・アーカイブ） -->
			<aside class="siaorb-archive__sidebar" aria-label="サイドバー">

				<!-- 検索 -->
				<div class="siaorb-sidebar__widget">
					<h3 class="siaorb-sidebar__title">記事を検索</h3>
					<form class="siaorb-sidebar__search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
						<label class="screen-reader-text" for="siaorb-cat-search-input">キーワードで検索:</label>
						<div class="siaorb-sidebar__search-inner">
							<input
								type="search"
								id="siaorb-cat-search-input"
								class="siaorb-sidebar__search-input"
								placeholder="キーワードを入力..."
								value="<?php echo esc_attr( get_search_query() ); ?>"
								name="s"
							>
							<button type="submit" class="siaorb-sidebar__search-btn" aria-label="検索">
								<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
									<circle cx="11" cy="11" r="8"></circle>
									<line x1="21" y1="21" x2="16.65" y2="16.65"></line>
								</svg>
							</button>
						</div>
					</form>
				</div>

				<!-- カテゴリ -->
				<div class="siaorb-sidebar__widget">
					<h3 class="siaorb-sidebar__title">カテゴリ</h3>
					<ul class="siaorb-sidebar__cat-list">
						<?php
						$categories = get_categories( array(
							'orderby'    => 'count',
							'order'      => 'DESC',
							'hide_empty' => true,
						) );
						foreach ( $categories as $category ) :
							$is_current = ( is_category( $category->term_id ) ) ? ' aria-current="page"' : '';
							?>
							<li class="siaorb-sidebar__cat-item">
								<a href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>" class="siaorb-sidebar__cat-link"<?php echo $is_current; ?>>
									<span class="siaorb-sidebar__cat-name"><?php echo esc_html( $category->name ); ?></span>
									<span class="siaorb-sidebar__cat-count"><?php echo absint( $category->count ); ?></span>
								</a>
							</li>
							<?php
						endforeach;
						?>
					</ul>
				</div>

				<!-- 月別アーカイブ -->
				<div class="siaorb-sidebar__widget">
					<h3 class="siaorb-sidebar__title">アーカイブ</h3>
					<ul class="siaorb-sidebar__archive-list">
						<?php
						wp_get_archives( array(
							'type'            => 'monthly',
							'format'          => 'html',
							'show_post_count' => false,
						) );
						?>
					</ul>
				</div>

			</aside>

		</div>
	</div>
</div>

<?php get_footer(); ?>

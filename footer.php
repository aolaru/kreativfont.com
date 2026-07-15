		</main>

		<footer class="kreativ-footer">
			<div class="container kreativ-footer-inner">
				<div class="kreativ-footer-brand">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kreativ-footer-mark">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/img/logo-96.png' ); ?>" alt="" width="96" height="96" class="kreativ-footer-logo" aria-hidden="true">
						<span>Kreativ Font</span>
					</a>
					<p class="kreativ-footer-copy">Curated fonts, font reviews, and practical tools for discovering better type faster.</p>
				</div>

				<div class="kreativ-footer-links">
					<div class="kreativ-footer-group">
						<h2>Explore</h2>
						<a href="<?php echo esc_url( home_url( '/fonts' ) ); ?>">Fonts</a>
						<a href="<?php echo esc_url( home_url( '/collections' ) ); ?>">Collections</a>
						<a href="<?php echo esc_url( home_url( '/tools' ) ); ?>">Font Tools</a>
						<a href="<?php echo esc_url( add_query_arg( 'font_filter', 'free', home_url( '/fonts' ) ) ); ?>">Free Fonts</a>
						<a href="<?php echo esc_url( home_url( '/blog' ) ); ?>">Blog</a>
					</div>

					<div class="kreativ-footer-group">
						<h2>Info</h2>
						<a href="<?php echo esc_url( home_url( '/blog/about' ) ); ?>">About</a>
						<a href="<?php echo esc_url( home_url( '/updates' ) ); ?>">Updates</a>
						<a href="<?php echo esc_url( home_url( '/blog/contact' ) ); ?>">Contact</a>
						<a href="<?php echo esc_url( home_url( '/blog/terms-of-use-privacy-policy' ) ); ?>">Terms of Use</a>
						<a href="<?php echo esc_url( home_url( '/blog/terms-of-use-privacy-policy' ) ); ?>">Privacy Policy</a>
					</div>

					<div class="kreativ-footer-group">
						<h2>Collections</h2>
						<a href="<?php echo esc_url( home_url( '/collections/trending-commercial-fonts' ) ); ?>">Trending Commercial</a>
						<a href="<?php echo esc_url( home_url( '/collections/best-free-fonts-commercial-use' ) ); ?>">Free Commercial Use</a>
						<a href="<?php echo esc_url( home_url( '/collections/best-modern-sans-serif-fonts' ) ); ?>">Modern Sans Serif</a>
					</div>

					<div class="kreativ-footer-group">
						<h2>Network</h2>
						<a href="https://kreativtools.com/" target="_blank" rel="noopener">Kreativ Tools</a>
						<a href="https://kreativsound.com" target="_blank" rel="noopener">Kreativ Sound</a>
						<a href="https://kreativwp.com" target="_blank" rel="noopener">Kreativ WP</a>
						<a href="https://www.whatfontis.com" target="_blank" rel="noopener nofollow">WhatFontIs</a>
					</div>
				</div>

				<div class="kreativ-footer-meta">
					<p>Kreativ Font does not host or distribute commercial fonts. Font names, trademarks, and copyrights belong to their respective owners.</p>
					<p>Some links may be affiliate links, which can earn a commission at no extra cost to you.</p>
					<p>&copy; 2026 <a href="https://madebykreativ.com" target="_blank" rel="noopener">Made by KREATIV</a> · Independent creative tools and assets by Andrei Olaru</p>
				</div>
			</div>
		</footer>

        <?php wp_footer(); ?>
</body>
</html>

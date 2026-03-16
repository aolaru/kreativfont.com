		<footer class="kreativ-footer">
			<div class="container kreativ-footer-inner">
				<div class="kreativ-footer-brand">
					<a href="/" class="kreativ-footer-mark">Kreativ Font</a>
					<p class="kreativ-footer-copy">Curated fonts, font reviews, and practical tools for discovering better type faster.</p>
				</div>

				<div class="kreativ-footer-links">
					<div class="kreativ-footer-group">
						<h2>Explore</h2>
						<a href="/category/fonts">Fonts</a>
						<a href="https://kreativtools.com" target="_blank" rel="noopener">Kreativ Tools</a>
						<a href="/category/free">Free Fonts</a>
						<a href="/blog">Blog</a>
					</div>

					<div class="kreativ-footer-group">
						<h2>Info</h2>
						<a href="/blog/about">About</a>
						<a href="/blog/contact">Contact</a>
						<a href="/blog/terms-of-use">Terms of Use</a>
						<a href="/blog/privacy-policy">Privacy Policy</a>
					</div>

					<div class="kreativ-footer-group">
						<h2>Network</h2>
						<a href="https://madebykreativ.com" target="_blank" rel="noopener">Made by KREATIV</a>
						<a href="https://kreativsound.com" target="_blank" rel="noopener">Kreativ Sound</a>
						<a href="https://kreativwp.com" target="_blank" rel="noopener">Kreativ WP</a>
						<a href="https://www.whatfontis.com" target="_blank" rel="noopener nofollow">WhatFontIs</a>
					</div>
				</div>

				<div class="kreativ-footer-meta">
					<p>Kreativ Font does not host or distribute commercial fonts. Font names, trademarks, and copyrights belong to their respective owners.</p>
					<p>Some links may be affiliate links, which can earn a commission at no extra cost to you.</p>
					<p>&copy; 2026 <a href="https://madebykreativ.com" target="_blank" rel="noopener">Made by KREATIV</a> · Independent creative tools and assets by <a href="/blog/about">Andrei Olaru</a></p>
				</div>
			</div>
		</footer>

		<script>
		document.addEventListener('DOMContentLoaded', function() {
		  const toggle = document.createElement('button');
		  toggle.type = 'button';
		  toggle.classList.add('dark-toggle');
		  toggle.setAttribute('aria-label', 'Toggle dark mode');
		  toggle.setAttribute('title', 'Toggle dark mode');
		  toggle.innerHTML = '<svg viewBox="0 0 24 24"><path d="M21.64 13a9 9 0 0 1-8.64 8.95A9 9 0 0 1 12 3v0a9 9 0 0 1 9.64 10z"/></svg>';
		  document.body.appendChild(toggle);

		  const isDark = localStorage.getItem('kreativ-dark') === 'true';
		  if (isDark) document.body.classList.add('dark-mode');

		  toggle.addEventListener('click', () => {
			document.body.classList.toggle('dark-mode');
			localStorage.setItem('kreativ-dark', document.body.classList.contains('dark-mode'));
		  });
		});
		</script>

        <?php wp_footer(); ?>
    </div>
</body>
</html>

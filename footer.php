		<footer class="kreativ-footer">
			<div class="container kreativ-footer-inner">
				<div class="kreativ-footer-brand">
					<a href="/" class="kreativ-footer-mark">Kreativ Font</a>
					<p class="kreativ-footer-copy">Curated fonts, font reviews, and practical tools for discovering better type faster.</p>
				</div>

				<div class="kreativ-footer-links">
					<div class="kreativ-footer-group">
						<h2>Explore</h2>
						<a href="https://kreativfont.com/fonts">Fonts</a>
						<a href="https://kreativfont.com/tools">Font Tools</a>
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

		<script>
		document.addEventListener('DOMContentLoaded', function() {
		  const toggles = document.querySelectorAll('.kreativ-theme-toggle');
		  const storageKey = 'kreativ-dark';
		  let storageAvailable = true;

		  const readStoredPreference = () => {
			try {
			  return window.localStorage.getItem(storageKey) === 'true';
			} catch (error) {
			  storageAvailable = false;
			  return document.body.classList.contains('dark-mode');
			}
		  };

		  const writeStoredPreference = (value) => {
			if (!storageAvailable) {
			  return;
			}

			try {
			  window.localStorage.setItem(storageKey, value ? 'true' : 'false');
			} catch (error) {
			  storageAvailable = false;
			}
		  };

		  const syncThemeToggleState = () => {
			const darkEnabled = document.body.classList.contains('dark-mode');

			toggles.forEach((toggle) => {
			  const icon = toggle.querySelector('i');
			  toggle.setAttribute('aria-pressed', darkEnabled ? 'true' : 'false');
			  toggle.setAttribute('title', darkEnabled ? 'Switch to light mode' : 'Switch to dark mode');
			  toggle.setAttribute('aria-label', darkEnabled ? 'Switch to light mode' : 'Switch to dark mode');

			  if (icon) {
				icon.classList.toggle('fa-moon', !darkEnabled);
				icon.classList.toggle('fa-sun', darkEnabled);
			  }
			});
		  };

		  const setTheme = (darkEnabled) => {
			document.body.classList.toggle('dark-mode', darkEnabled);
			writeStoredPreference(darkEnabled);
			syncThemeToggleState();
		  };

		  setTheme(readStoredPreference());

		  toggles.forEach((toggle) => {
			toggle.addEventListener('click', () => {
			  setTheme(!document.body.classList.contains('dark-mode'));
			});
		  });
		});
		</script>

        <?php wp_footer(); ?>
    </div>
</body>
</html>

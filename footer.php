		<footer class="kreativ-footer">

			DISCLAIMER: Kreativ Font does not host or distribute commercial fonts.
			All font names, trademarks, and copyrights belong to their respective owners.<br />

			We are supported by our audience.
			Some links on this site are affiliate links, which may earn us a small commission at no extra cost to you.<br />

			Looking for font identification? You may also try
			<a href="https://www.whatfontis.com" target="_blank" rel="noopener nofollow">
			  WhatFontIs
			</a>.<br />

			<a href="/blog/contact">Contact</a> ·
			<a href="/blog/terms-of-use">Terms of Use</a> ·
			<a href="/blog/privacy-policy">Privacy Policy</a>
			<br/><br/>

						<a href="/">Kreativ Font</a> ·
			<a href="https://kreativsound.com" target="_blank" rel="noopener">Kreativ Sound</a> ·
			<a href="/tools">Kreativ Tools</a> ·
			<a href="https://kreativwp.com" target="_blank" rel="noopener">Kreativ WP</a>

			<br>

			© 2026 <a href="https://madebykreativ.com" target="_blank" rel="noopener">Made by KREATIV</a> ·
			Independent creative tools and assets by <a href="/blog/about">Andrei Olaru</a>

		</footer>

		<script>
		document.addEventListener('DOMContentLoaded', function() {
		  const toggle = document.createElement('button');
		  toggle.classList.add('dark-toggle');
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
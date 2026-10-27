jQuery(function ($) {
  'use strict';
  const form = document.querySelector('#gpt-font-id-form');
  if (!form || typeof GPTFontId === 'undefined') return;
  const input = form.querySelector('input[name="font_image"]');
  // Selection is held in memory for drag/drop and rotation, not in the native FileList.
  input.required = false;
  const submit = form.querySelector('button[type="submit"]');
  const consent = form.querySelector('#kfi-analysis-consent');
  const dropzone = form.querySelector('.kfi-upload-label');
  const help = form.querySelector('#kfi-submit-help');
  const publicationReady = GPTFontId.publicationRequiresConsent === true
    && GPTFontId.publicationConsentVersion === 'public-image-and-results-v1'
    && form.dataset.publicationConsent === GPTFontId.publicationConsentVersion;
  const loading = document.querySelector('#gpt-font-id-loading');
  const result = document.querySelector('#gpt-font-id-result');
  const actions = form.querySelector('.kfi-image-actions');
  const preview = document.querySelector('#gpt-font-preview') || form.appendChild(document.createElement('div'));
  preview.id = 'gpt-font-preview';
  const allowed = new Set(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
  let selected = null, previewUrl = null, previewImage = null;
  let busy = false, preparing = false, generation = 0;

  function sync() {
    submit.disabled = !publicationReady || busy || preparing || !selected || (consent && !consent.checked);
    submit.textContent = busy ? 'Finding matches…' : 'Identify & publish';
    input.disabled = !publicationReady || busy || preparing;
    input.tabIndex = selected ? -1 : 0;
    if (consent) consent.disabled = !publicationReady || busy || preparing;
    if (dropzone) dropzone.hidden = Boolean(selected);
    if (help) help.textContent = !publicationReady ? 'Refresh this page to load the updated public-sharing notice.' : (busy ? 'Finding matches and creating your post…' : (preparing ? 'Preparing your image…' : (!selected ? 'Choose an image to get started.' : (consent && !consent.checked ? 'Confirm public sharing to continue.' : 'Ready to identify your font.'))));
    form.querySelectorAll('.kfi-image-actions button').forEach(button => { button.disabled = busy || preparing; });
    form.setAttribute('aria-busy', String(busy || preparing));
    if (loading) { loading.hidden = !busy; loading.style.display = busy ? '' : 'none'; }
  }

  function clearResult() { result.replaceChildren(); result.hidden = true; result.className = ''; }
  function paragraph(text, parent = result) {
    const node = document.createElement('p'); node.textContent = text; parent.appendChild(node); return node;
  }
  function showError(message) {
    clearResult(); result.hidden = false; result.className = 'error';
    paragraph(message); result.focus();
  }
  function resetConsent() {
    if (consent) consent.checked = false;
  }
  function releasePreview() {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = null; previewImage = null;
    preview.replaceChildren(); preview.hidden = true;
    if (actions) actions.hidden = true;
  }
  function resetFile() {
    generation++; selected = null; input.value = ''; preparing = false;
    releasePreview(); resetConsent(); clearResult(); sync();
  }

  function choose(file) {
    if (busy || preparing) return;
    resetFile();
    if (!file) return;
    if (!allowed.has(file.type)) { showError('Choose a JPG, PNG, GIF or WebP image.'); return; }
    if (!file.size || file.size > 4 * 1024 * 1024) { showError('Choose an image smaller than 4 MB.'); return; }
    const token = generation;
    preparing = true; sync();
    const img = new Image();
    previewUrl = URL.createObjectURL(file);
    img.onload = function () {
      if (token !== generation) return;
      preparing = false;
      if (img.naturalWidth < 16 || img.naturalHeight < 16 || img.naturalWidth > 12000 || img.naturalHeight > 12000 || img.naturalWidth * img.naturalHeight > 25000000) {
        resetFile(); showError('Choose a readable crop up to 25 megapixels and 12,000 pixels per side.'); return;
      }
      selected = file; previewImage = img;
      img.alt = 'Your selected font sample';
      preview.replaceChildren(img);
      paragraph(`${file.name} · ${Math.round(file.size / 1024)} KB · ${img.naturalWidth} × ${img.naturalHeight} px`, preview);
      preview.hidden = false;
      if (actions) actions.hidden = false;
      sync();
    };
    img.onerror = function () {
      if (token !== generation) return;
      resetFile(); showError('This file could not be opened as an image. Please choose another.');
    };
    img.src = previewUrl;
  }

  function safeSiteUrl(value) {
    try {
      const url = new URL(value);
      return url.protocol === 'https:' && url.origin === new URL(GPTFontId.ajaxurl).origin ? url.href : null;
    } catch { return null; }
  }
  function safePreviewUrl(value) {
    try {
      const url = new URL(value), site = new URL(GPTFontId.ajaxurl);
      const own = url.origin === site.origin && url.pathname.startsWith('/wp-content/uploads/');
      const cdn = ['i0.wp.com', 'i1.wp.com', 'i2.wp.com'].includes(url.hostname) && url.pathname.startsWith('/' + site.hostname + '/wp-content/uploads/');
      return url.protocol === 'https:' && !url.username && !url.password && !url.port && (own || cdn) && /\.(png|jpe?g|webp)$/i.test(url.pathname) ? url.href : null;
    } catch { return null; }
  }
  function showSuccess(data) {
    clearResult(); result.hidden = false; result.className = 'success';
    const title = document.createElement('h2'); title.textContent = 'Your font shortlist'; result.appendChild(title);
    paragraph('Compare these suggestions with your sample. Exact identification is not guaranteed.');
    const resultActions = document.createElement('div'); resultActions.className = 'kfi-result-actions';
    const postUrl = safeSiteUrl(data?.post_url);
    if (postUrl) {
      const post = document.createElement('a'); post.href = postUrl; post.textContent = 'View your published identification'; post.className = 'kfi-post-link'; resultActions.appendChild(post);
      const copy = document.createElement('button'); copy.type = 'button'; copy.textContent = 'Copy post link';
      copy.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(postUrl); copy.textContent = 'Link copied'; }
        catch { copy.textContent = 'Open the post to copy its link'; }
      }); resultActions.appendChild(copy);
    }
    const another = document.createElement('button'); another.type = 'button'; another.textContent = 'Try another image';
    another.addEventListener('click', () => { resetFile(); input.click(); }); resultActions.appendChild(another);
    result.appendChild(resultActions);
    if (data && typeof data === 'object' && Array.isArray(data.candidates)) {
      if (data.catalogue_note) {
        const details = document.createElement('details'); details.className = 'kfi-catalogue-note';
        const summary = document.createElement('summary'); summary.textContent = 'How these suggestions were checked'; details.appendChild(summary);
        paragraph(data.catalogue_note, details); result.appendChild(details);
      }
      if (data.sample_text) paragraph('Text sample: ' + data.sample_text).className = 'kfi-sample-text';
      if (!data.candidates.length) paragraph('No reliable suggestions from this image. Try a sharper crop with one line of text.');
      const renderCandidate = candidate => {
        const card = document.createElement('article'); card.className = 'kfi-match';
        const tag = paragraph(candidate.kind === 'similar_alternative' ? 'Similar alternative' : 'Possible match', card); tag.className = 'kfi-match-kind';
        const heading = document.createElement('h3'); heading.textContent = candidate.name; card.appendChild(heading);
        const listed = candidate.catalogue_status === 'listed';
        const comparison = document.createElement('details'); comparison.className = 'kfi-comparison-details';
        const summary = document.createElement('summary'); summary.textContent = 'Comparison details'; comparison.appendChild(summary);
        if (candidate.catalogue_status) {
          paragraph(listed
            ? (candidate.evidence === 'local_render_alternative' ? 'Catalogue alternative · original font not identified' : (candidate.evidence === 'local_render_match' ? 'AI suggestion supported by a font-file comparison · identity unverified' : 'In Kreativ · name checked · identity unverified'))
            : 'AI suggestion · no matching catalogue entry found in this search', comparison).className = 'kfi-catalogue-evidence';
        }
        const faceLabels = {regular:'Regular',bold:'Bold',italic:'Italic',bold_italic:'Bold italic'};
        if (faceLabels[candidate.matched_style]) paragraph('Compared style: ' + faceLabels[candidate.matched_style] + '. Exact face remains unverified.', comparison);
        const previewHref = listed ? safePreviewUrl(candidate.preview_url) : null;
        if (previewHref) {
          const image = document.createElement('img'); image.src = previewHref; image.alt = candidate.name + ' catalogue specimen';
          image.loading = 'lazy'; image.decoding = 'async'; image.className = 'kfi-catalogue-preview';
          image.addEventListener('error', () => image.remove(), {once: true}); card.appendChild(image);
        }
        paragraph(candidate.reason, card);
        const href = safeSiteUrl(candidate.url);
        if (href) {
          const link = document.createElement('a'); link.href = href; link.textContent = (listed ? 'View ' : 'Search for ') + candidate.name + ' in Kreativ →'; card.appendChild(link);
        }
        if (candidate.catalogue_status || faceLabels[candidate.matched_style]) card.appendChild(comparison);
        result.appendChild(card);
      };
      data.candidates.slice(0, 3).forEach(renderCandidate);
      if (Array.isArray(data.catalogue_alternatives) && data.catalogue_alternatives.length) {
        const alternativesTitle = document.createElement('h2'); alternativesTitle.textContent = 'Similar catalogue alternatives'; result.appendChild(alternativesTitle);
        paragraph('Comparison options, not identifications of the font in your image.');
        data.catalogue_alternatives.slice(0, 2).forEach(renderCandidate);
      }
      if (data.note) paragraph(data.note);
      if (data.notice) paragraph(data.notice).className = 'kfi-result-notice';
    } else {
      // Transitional cached pages/responses: text only, never execute server HTML.
      const doc = new DOMParser().parseFromString(typeof data === 'string' ? data : String(data?.html || ''), 'text/html');
      doc.querySelectorAll('script,style').forEach(node => node.remove());
      paragraph(doc.body.textContent || 'No font suggestions were returned.');
    }
    result.focus();
  }

  input.addEventListener('change', () => choose(input.files?.[0]));
  consent?.addEventListener('change', sync);
  form.querySelector('#kfi-change')?.addEventListener('click', () => input.click());
  form.querySelector('#kfi-remove')?.addEventListener('click', resetFile);
  form.querySelector('#kfi-rotate')?.addEventListener('click', () => {
    if (!previewImage || busy || preparing) return;
    const image = previewImage, name = selected.name;
    preparing = true; sync();
    const canvas = document.createElement('canvas');
    canvas.width = image.naturalHeight; canvas.height = image.naturalWidth;
    const ctx = canvas.getContext('2d');
    ctx.translate(canvas.width / 2, canvas.height / 2); ctx.rotate(Math.PI / 2);
    ctx.drawImage(image, -image.naturalWidth / 2, -image.naturalHeight / 2);
    canvas.toBlob(blob => {
      preparing = false;
      if (!blob || blob.size > 4 * 1024 * 1024) {
        sync(); showError('The rotated image is too large. Crop or resize the image first.'); return;
      }
      choose(new File([blob], name.replace(/\.[^.]*$/, '') + '-rotated.png', {type: 'image/png'}));
    }, 'image/png');
  });
  ['dragenter', 'dragover'].forEach(name => form.addEventListener(name, event => {
    event.preventDefault(); if (!busy && !preparing) form.classList.add('dragover');
  }));
  form.addEventListener('dragleave', event => {
    if (!form.contains(event.relatedTarget)) form.classList.remove('dragover');
  });
  form.addEventListener('drop', event => {
    event.preventDefault(); form.classList.remove('dragover'); choose(event.dataTransfer?.files?.[0]);
  });
  form.addEventListener('submit', event => {
    event.preventDefault();
    if (busy || preparing) return;
    if (!publicationReady) { showError('Refresh this page to load the current publication notice before submitting an image.'); return; }
    if (!selected) { showError('Please choose an image first.'); return; }
    if (consent && !consent.checked) { showError('Please confirm your image can be analysed and published.'); return; }
    const body = new FormData();
    body.append('action', 'gpt_font_id_identify');
    body.append('_ajax_nonce', GPTFontId.nonce);
    body.append('font_image', selected, selected.name);
    body.append('client', 'website');
    body.append('publish_identification', 'yes');
    body.append('publication_consent', GPTFontId.publicationConsentVersion);
    busy = true; clearResult(); sync();
    $.ajax({url: GPTFontId.ajaxurl, type: 'POST', data: body, contentType: false, processData: false, timeout: 70000})
      .done(response => {
        if (response?.success) showSuccess(response.data);
        else showError(typeof response?.data === 'string' ? response.data : (response?.data?.message || 'The image could not be analysed. Please try again.'));
      }).fail((_xhr, status) => {
        showError(status === 'timeout' ? 'The request timed out. Please wait a moment before trying again; the server may still be finishing.' : 'The connection failed. Refresh this page and try again.');
      }).always(() => { busy = false; sync(); });
  });
  window.addEventListener('pagehide', () => { if (previewUrl) URL.revokeObjectURL(previewUrl); });
  sync();
  if (!publicationReady) showError('This page has changed. Refresh to review the public-sharing notice before uploading an image.');
});

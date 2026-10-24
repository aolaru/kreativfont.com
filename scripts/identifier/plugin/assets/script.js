jQuery(function ($) {
  'use strict';
  const form = document.querySelector('#gpt-font-id-form');
  if (!form || typeof GPTFontId === 'undefined') return;
  const input = form.querySelector('input[name="font_image"]');
  // Selection is held in memory for drag/drop and rotation, not in the native FileList.
  input.required = false;
  const submit = form.querySelector('button[type="submit"]');
  const consent = form.querySelector('#kfi-analysis-consent');
  const share = form.querySelector('#kfi-share-review');
  const loading = document.querySelector('#gpt-font-id-loading');
  const result = document.querySelector('#gpt-font-id-result');
  const actions = form.querySelector('.kfi-image-actions');
  const preview = document.querySelector('#gpt-font-preview') || form.appendChild(document.createElement('div'));
  preview.id = 'gpt-font-preview';
  const allowed = new Set(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
  let selected = null, previewUrl = null, previewImage = null;
  let busy = false, preparing = false, generation = 0;

  function sync() {
    submit.disabled = busy || preparing || !selected || (consent && !consent.checked);
    submit.textContent = busy ? 'Comparing fonts…' : 'Find font matches';
    input.disabled = busy || preparing;
    if (consent) consent.disabled = busy || preparing;
    if (share) share.disabled = busy || preparing;
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
    if (share) share.checked = false;
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
  function showSuccess(data) {
    clearResult(); result.hidden = false; result.className = 'success';
    const title = document.createElement('h2'); title.textContent = 'Your font shortlist'; result.appendChild(title);
    paragraph('AI suggestions, not a verified identification. Compare the letterforms and check licensing before use.');
    if (data && typeof data === 'object' && Array.isArray(data.candidates)) {
      if (data.sample_text) paragraph('Text sample: ' + data.sample_text).className = 'kfi-sample-text';
      if (!data.candidates.length) paragraph('No reliable suggestions from this image. Try a sharper crop with one line of text.');
      data.candidates.slice(0, 3).forEach(candidate => {
        const card = document.createElement('article'); card.className = 'kfi-match';
        const tag = paragraph(candidate.kind === 'similar_alternative' ? 'Similar alternative' : 'Possible match', card); tag.className = 'kfi-match-kind';
        const heading = document.createElement('h3'); heading.textContent = candidate.name; card.appendChild(heading);
        paragraph(candidate.reason, card);
        const href = safeSiteUrl(candidate.url);
        if (href) {
          const link = document.createElement('a'); link.href = href; link.textContent = 'Find ' + candidate.name + ' in Kreativ →'; card.appendChild(link);
        }
        result.appendChild(card);
      });
      if (data.note) paragraph(data.note);
      if (data.notice) paragraph(data.notice).className = 'kfi-result-notice';
    } else {
      // Transitional cached pages/responses: text only, never execute server HTML.
      const doc = new DOMParser().parseFromString(typeof data === 'string' ? data : String(data?.html || ''), 'text/html');
      doc.querySelectorAll('script,style').forEach(node => node.remove());
      paragraph(doc.body.textContent || 'No font suggestions were returned.');
    }
    const free = document.createElement('a');
    free.href = new URL('/collections/best-free-fonts-commercial-use', GPTFontId.ajaxurl).href;
    free.textContent = 'Explore free fonts for commercial use';
    paragraph('Need a lower-cost starting point?').append(' ', free);
    result.focus();
  }

  input.addEventListener('change', () => choose(input.files?.[0]));
  consent?.addEventListener('change', sync);
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
    if (!selected) { showError('Please choose an image first.'); return; }
    if (consent && !consent.checked) { showError('Please confirm the image can be sent for analysis.'); return; }
    const body = new FormData();
    body.append('action', 'gpt_font_id_identify');
    body.append('_ajax_nonce', GPTFontId.nonce);
    body.append('font_image', selected, selected.name);
    // Explicit opt-in is never inferred from legacy auto-post settings or processing consent.
    if (share?.checked) { body.append('share_for_review', 'yes'); body.append('review_consent', 'yes'); }
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
});

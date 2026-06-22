(() => {
  const bandInput = document.getElementById('bandSearchInput');
  const bandResults = document.getElementById('bandSearchResults');
  const artistInput = document.getElementById('artistSearchInput');
  const artistResults = document.getElementById('artistSearchResults');

  if (!bandInput || !bandResults || !artistInput || !artistResults) {
    return;
  }

  const data = window.kbtSearchData || {};
  const bands = Array.isArray(data.bands) ? data.bands : [];
  const artists = Array.isArray(data.artists) ? data.artists : [];

  const renderBands = (items) => {
    if (!items.length) {
      bandResults.innerHTML = '';
      return;
    }

    bandResults.innerHTML = items.slice(0, 30).map((item) => {
      const href = 'band.php?a=' + encodeURIComponent(item.slug);
      const years = item.years ? ' <span class="search-date">[' + item.years + ']</span>' : '';
      return '<div class="search-result-line"><a href="' + href + '">' + item.name + '</a>' + years + '</div>';
    }).join('');
  };

  const renderArtists = (items) => {
    if (!items.length) {
      artistResults.innerHTML = '';
      return;
    }

    artistResults.innerHTML = items.slice(0, 30).map((item) => {
      const href = 'artist.php?a=' + encodeURIComponent(item.slug);
      return '<div class="search-result-line"><a href="' + href + '">' + item.name + '</a></div>';
    }).join('');
  };

  const updateBands = () => {
    const q = (bandInput.value || '').toLowerCase();
    if (!q) {
      bandResults.innerHTML = '';
      return;
    }

    const filtered = bands.filter((band) => (band.name || '').toLowerCase().includes(q));
    renderBands(filtered);
  };

  const updateArtists = () => {
    const q = (artistInput.value || '').toLowerCase();
    if (!q) {
      artistResults.innerHTML = '';
      return;
    }

    const filtered = artists.filter((artist) => (artist.name || '').toLowerCase().includes(q));
    renderArtists(filtered);
  };

  bandInput.addEventListener('input', updateBands);
  artistInput.addEventListener('input', updateArtists);
  bandResults.innerHTML = '';
  artistResults.innerHTML = '';
})();

document.addEventListener("DOMContentLoaded", function () {
  const header = document.querySelector(".heading");
  if (!header) return;

  const sections = {
    "/band.php": "/bands_grid.php",
    "/artist.php": "/artists_grid.php",
    "/artists_table_detailed.php": "/artists_table.php",
    "/release_band.php": "/releases_band_grid.php",
    "/release_related.php": "/releases_related_grid.php"
  };
  const currentPath = sections[window.location.pathname] || window.location.pathname;
  const home = header.querySelector(".logo-link");
  const links = [home, ...document.querySelectorAll("#toc a[href]")]
    .filter(link => link && new URL(link.href).pathname !== "/tree.php");

  function normalizePath(path) {
    return path === "/index.php" ? "/" : path;
  }

  const currentIndex = links.findIndex(link => {
    const destinations = [link.href, link.dataset.viewGrid, link.dataset.viewTable, link.dataset.viewList];
    return destinations.some(href => href
      && normalizePath(new URL(href, window.location.origin).pathname) === normalizePath(currentPath));
  });
  if (currentIndex < 0) return;

  attachSwipeNavigation(header, function (direction) {
    const offset = direction === "forward" ? 1 : -1;
    const target = links[(currentIndex + offset + links.length) % links.length];
    window.location.href = target.href;
  });
});

// END OF FILE

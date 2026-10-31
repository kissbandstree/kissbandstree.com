# Kiss Bands Tree Style Rules

## Required working procedure

1. Read this document before each task. Inspect the relevant PHP reader and
   several comparable existing records before changing data or presentation.
2. Verify that localhost:8003 serves
   `X:\My Drive\WEBSITES\WEB SITES\kissbandstree.com`. Check repository status
   and preserve unrelated local work. Do not edit the old D: copy.
3. Perform the requested work, not just a proposal. Research missing fields and
   try another reasonable source when the first one fails.
4. Ask the user when an identity, chronology, lineup number or intended behavior
   cannot be established confidently. Do not invent an answer or substitute a
   different person's profile. If the user is unavailable, complete verified
   work and explicitly list unresolved fields.
5. Follow the established site conventions. Do not introduce a new writing or
   data format without agreement. Suggest useful improvements separately; do
   not expand a content task into a redesign or migration.
6. Validate the actual rendered result and links. Report exactly what changed,
   what was checked, and what remains missing. Do not describe partial work as
   a complete entry.

## Presentation

- Never use easing transitions.
- Centralize band and artist update dates in `get_band_mtime` and
  `get_artist_mtime` in `functions.php`. Use the newest timestamp among the
  entry, small photo, large photo and credits, plus comments for bands.
  Artists have no separate comments collection. JSON records use their own
  `modified` values, not the collection file's timestamp. Use these helpers for
  detail pages, table dates and sorting, LAST MODIFIED BANDS, LAST MODIFIED
  ARTISTS and ACTIVE BANDS. Run `php .github/tests/updated.php` and
  `php .github/tests/home.php` after changing update-date behavior.
- Home shows Kiss's LINEUPS above MEMBERS, including reunion periods in the
  chronological order of Gene Simmons's Tree timeline. Lineups, LAST MODIFIED
  BANDS and ACTIVE BANDS share `render_band_card` and their responsive layout.
  Cards show the photo, linked name and lineup number, period, linked Kiss
  members and SHOW ON TREE. Keep update dates in the two existing sections.
  Reuse `/tree?focus=SLUG` for navigation. Run
  `php .github/tests/home.php` after changing these sections.
- Standard page and Tree footers share `footer-branding.php`. Show copyright
  and production credits on separate lines, with the Focus Studios logo last.
  Keep the standard page's update date and social links above these credits.
  Match Pekosoft's Focus logo sizing: 32px height with automatic width.
  Run `php .github/tests/footer.php` after changing the footer.
- Settings replaces the header's Dark mode control. It contains Dark mode,
  Fullscreen, Share and Reset. Tree shows these four controls directly on the
  page instead of a Settings menu, sharing their markup in `settings-controls.php`.
  Tree controls are icon-only buttons with tooltips and screen-reader labels.
  Settings menus on other pages keep their visible control labels.
  Dark mode is enabled by default. Its active and pressed states follow the
  displayed theme in both Tree and Settings. Preserve the legacy stored `mode`
  values for compatibility: `light` means the default dark appearance and
  `dark` means the inverted light appearance.
  Keep Tree artist names vertically centered with Photos enabled or disabled.
  Tree also has Photos and Comments toggles, enabled by default and remembered
  across visits. Photos controls band and artist photographs, including the
  artist thumbnail gallery, but not album artwork or site logos. Comments
  controls lineup comments. Hidden columns collapse while lineup arrows stay
  aligned. Reset restores both toggles. Adapt the
  existing Pekosoft controls and sharing fallback in `index.js`, without adding
  another JavaScript dependency.
- TOC and Settings follow Pekosoft's responsive sidebar behavior. At 800px and
  wider, both are open by default, remain open during outside clicks, Escape
  and navigation, and remember their individual open states. Page content makes
  room for open sidebars. Tree keeps its poster layout and drag-to-pan behavior.
  Below 800px, menus start closed, open one at a time and close on outside clicks
  or Escape. Mobile TOC also closes on navigation, including Back.
- The header stays fixed across the full site frame above both sidebars.
  TOC links and Settings controls share row sizing, padding, icon alignment,
  label typography and the grey2 hover background. Their scroll areas and
  bottom Close controls use the same spacing.
- TOC labels use regular capitalization, such as Bands and Related, not all caps.
- Public sections use clean root URLs: `/bands`, `/artists`, `/releases`,
  `/related`, `/calendar`, `/about`, `/thanks`, `/stats` and `/tree`.
  Grid is the default; the four view sections use `?view=table` and `?view=list`.
  `page.php` selects existing templates through `page-routes.php`. Old section
  PHP URLs permanently redirect, preserving the original view and other query
  parameters. Explicit Grid queries and trailing slashes redirect to the base URL.
  Invalid view parameters return HTTP 400. Canonical URLs omit the view parameter;
  sharing retains it. Keep `.htaccess` and local Caddy dispatch rules consistent.
  Detail-page URLs are unchanged. Run `php .github/tests/routes.php` for routing
  checks and add `--http http://localhost:8003` to test the running server.
- Calendar shows artist birthdays and appears between Related and About in
  the main menu and header swipe sequence. Keep its existing artist links,
- Sortable tables let users resize columns by dragging a header edge while
  keeping the table full width. On touchscreens, drag the right side of the
  header. Column proportions are saved once when a drag ends, not during
  pointer movement, and restored across visits as the table fits its container.
  Double-click or double-tap
  the edge to restore its default width.
  date tooltips and current-day indicator. Months shrink to the available
  content width between open sidebars; day rows grow when birthday photos wrap.
- Reset clears all localStorage settings for this site, including Kiss-member
  and band filters, slideshow state, theme, collapsed sections, view and sidebar
  preferences. It reloads with defaults in Grid view, preserving the current
  section or detail page and its query parameters.
- Keep `icons.svg` identical to Pekosoft's sprite when updating it. Check that
  all icons referenced by this site remain present.
- Page bitmaps use the TOC symbols: `png/` contains 512 by 512 PNGs and
  `png/og/` contains 1200 by 630 sharing previews, with white symbols on black.
  Home uses the site icon. Grid, Table and List share their section preview.
  Keep detail-page photos and album covers ahead of these generic previews
  in `head.php`. Run `php .github/tests/previews.php` after changing previews.
- Run `node --test .github/tests/settings.test.cjs` after changing Settings.
- Swipe left or right on the header to move through Home and the main-menu
  sections, wrapping at either end and preserving the Grid/Table/List view.
  The linked header logo accepts swipes too. A tap still opens Home; a
  horizontal drag suppresses the follow-up click. Other links and header
  controls stay excluded.
  Detail pages use their parent section. Tree is excluded from the swipe
  sequence and retains its own drag-to-pan interaction.
- Keep swipe handling in a single `swipe.js` on each site. Reuse Pekosoft's
  existing implementation, adapting navigation and controls for this site;
  do not refactor Pekosoft merely to add behavior here. Run
  `node --test .github/tests/swipe.test.cjs .github/tests/swipe-pointer.test.cjs`
  after changing swipe behavior.
- Never use rounded corners (no border-radius).
- Avoid decorative visual effects unless explicitly requested.
- Use `<h2>` for all section headers. Reserve `<h1>` only for the main page title/top-line heading.
- Artist band lists keep spacing between entries, without extra blank lines
  before the first or after the last entry. Artist pages and Tree share
  `render_lineup_arrows` and arrow styling. Between lineups, show an arrow for
  each continuing member, retaining the previous lineup's positions and blanks
  for departing members. Do not connect unknown members or put arrows before
  the first or after the last lineup. Artist-page arrows fit the content width.
  Hovering an artist name or arrow highlights that artist's arrows alongside matching
  names throughout the page, on both artist pages and Tree. Clear the highlight
  when the pointer leaves, without highlighting other artists' arrows.
  Run `php .github/tests/credits.php` to check all artist pages and arrow cases.
- Stats charts have empty centres. Preserve slice hover, selection and keyboard
  interactions and their matching legend highlights.
- ACTIVE BANDS counts individual lineup records. YES means the active-date
  field contains the word `present`, case-insensitively. NO includes all other
  lineups. Use the shared `is_band_active` helper for Stats and the homepage.
  Run `php .github/tests/stats.php` after changing this behavior.

## Licensing and copyright

- Never include years or year ranges in the site's own licences or copyright
  sentences, including the footer. Preserve required third-party notices.

## Working copy

- The authoritative working site is
  `X:\My Drive\WEBSITES\WEB SITES\kissbandstree.com`, served at
  `http://localhost:8003`. Verify the server document root before editing or
  previewing. Other computers should use their corresponding synced copy.
- Google Drive is the intended working location so the site can be updated
  from different computers and locations. This takes priority over the faster
  response times measured on D:.
- `D:\kissbandstree.com` is no longer the active working copy. Do not edit it
  or synchronize it over Google Drive without an explicit request and a
  conflict check.

## Adding bands and artists

- Verify names, roles, dates and links against official sources. Preserve blank
  fields in the JSON record's positional `lines` array; do not invent metadata.
- Complete band entries with lineup, official link, photos, credits and working
  artist links. Create missing artist pages with portraits and credits, and add
  reciprocal entries to the relevant chronological `bands_list` files.
- Match lineup names exactly to artist names and reuse existing artist records.
- Store new records in the appropriate JSON collection under `data`; do not
  recreate the old per-record text files or directories. See JSON content below.
- Preserve lineup numbering in the display name, filename, photos, comments,
  credits, chronological artist lists and tree links. Joining an existing trio
  is not the same as founding a new band. Do not create an earlier lineup with
  guessed dates.
- Write URLs without a trailing slash, including official website links,
  photo sources and documentation references.
- Prefer URLs without the `www.` prefix where the destination supports it.
  Always use `facebook.com`, not `www.facebook.com`, for Facebook URLs.
- Read representative existing entries before adding or changing site content.
  Follow established conventions rather than introducing a new format.
- Keep band Comments short and factual, in a single unbroken text block with
  no paragraph breaks. Focus on relevant band history or lineup changes rather
  than repeating the lineup or writing a promotional biography.
- Never mention sources or include source URLs in Comments. Keep research
  references in project documentation, separate from the published comment.
  Distinguish announced plans from completed releases.
- Band `lines` arrays contain name, active dates, website, Facebook identifier, a
  reserved blank line, associated Kiss members, then lineup lines.
- Artist `lines` arrays contain name, instruments, Facebook identifier, gender, date
  of passing, associated Kiss members, aliases, website and date of birth.
- A missing birth date is unknown, not an error. Display it as blank in the
  detailed artist table, matching the artist page; do not invent a date.
- Band credit `lines` contain description, photographer, source name/text and
  an optional source URL. Artist credits retain description, photographer,
  source URL/citation and an optional source name, in that order.
  Keep these established positions so data-first deployments remain compatible.
  Preserve blank fields when reading either kind of credit; missing values
  display as `n/a`. Never use `FILE_SKIP_EMPTY_LINES` for positional credits.
  Both page types link the source name when a URL is supplied; otherwise display the source
  text without a link, for example `Emailed to Kiss Bands Tree by Gene Simmons`.
  Preserve existing source names such as `Facebook, Richie Scarlet`.
  If a source account is unverified, use its platform name without guessing
  the account owner. Do not display a raw URL as the source name.

## Credit source URL checks

- Verify that the original and cleaned URLs reach the same source. Preserve
  paths, query strings and image IDs. A verified clean URL may redirect to the
  publisher's preferred `www.` host or trailing-slash form.
- On 29 October 2026, Black Sabbath #9's Metal Edge article returned 404 with
  and without `www.`. Its existing credit URL was retained; do not substitute
  a guessed article URL.
- Nautilus's Backstage Auctions item returned 403 with and without `www.` in
  HTTP checks; the bare host also returned 403 in the browser. Its existing
  credit URL was retained until the item destination can be verified.

## JSON content

- `data/*.json` is the authoritative content store. Thirteen collections replace
  1,738 individual text files. Images remain ordinary files in their existing
  directories. Public URLs, slugs and relationships are unchanged.
- Each collection has `schema_version: 1` and a `records` object keyed by the
  existing slug. Each record contains `modified` (Unix timestamp in seconds)
  and `lines` (an ordered array of strings).
- The first migration deliberately retains the existing positional fields.
  Line strings preserve whitespace and their original `\n` or `\r\n` endings,
  including blank fields and the absence of a final newline. Do not remove
  blank fields, put multiple lines into one array item, or insert a line without
  an ending before another line. An empty original has `lines: []`.
- Edit only the intended record and update its `modified` timestamp when its
  content changes. Collection file modification times do not set every entry's
  date. Photo modification times still contribute to artist/band update dates.
- Runtime data access is centralized in `content.php`. It loads and validates
  each required collection once per request. There is no cross-request cache
  and no legacy text-file fallback.
- Existing templates use logical keys such as `artists/dan_reed.txt` through
  the `content_*` helpers. These are lookup keys, not physical files or public
  data URLs. Do not call native filesystem functions on these keys.
- Invalid/missing collection data is an error, not an empty catalog. Optional
  absent records are checked with `content_exists`; required reads must fail
  explicitly.

| Content | JSON collection |
|---|---|
| Artists | `data/artists.json` |
| Band lineups | `data/bands.json` |
| Chronological artist band lists | `data/artist-bands.json` |
| Artist photo credits | `data/artist-credits.json` |
| Band photo credits | `data/band-credits.json` |
| Band comments | `data/band-comments.json` |
| Band releases | `data/band-releases.json` |
| Related releases | `data/related-releases.json` |
| Band release comments | `data/band-release-comments.json` |
| Related release comments | `data/related-release-comments.json` |
| Band tracklists | `data/band-release-tracklists.json` |
| Related tracklists | `data/related-release-tracklists.json` |
| About, intro, footer, thanks and other page text | `data/pages.json` |

- Run `php .github/tests/content.php` after content or loader changes.
- Run `php .github/tests/credits.php` after credit or credit-display changes.
  It checks field positions and source links on every credited page.
- `php .github/tools/benchmark.php OUTPUT.json [REFERENCE.json] [BASE_URL]`
  checks every detail page and the main views, then measures twelve routes
  with two warmups and 25 samples each. The default base URL is
  `http://localhost:8003`. The benchmark requires PHP 8.4 or newer for HTTP
  response-header inspection. Keep output files outside the website.
- The benchmark rejects HTTP server errors and PHP warnings, notices,
  deprecations and fatal/parse errors, including HTML-formatted diagnostics.
  Run `php .github/tests/benchmark.php` to test that detection with local
  fixture responses; it starts and stops its own temporary server.
- The optional reference compares HTTP status and HTML hashes, ignoring only
  the source-file dates labelled `Document updated` and `Version`.
- The one-time migration tool is `.github/tools/migrate-content.php`.
  It refuses to overwrite existing JSON and only removes old sources with
  `--remove-text` after verifying all source content and timestamps.

## Facebook and website links

- Check Facebook for each new band and artist; it is part of completing an entry.
- Store only the Facebook path, not a full URL: `DanReedMusic`, `ikizmusic` or
  a verified `profile.php?id=...` path. Do not include a trailing slash,
  tracking parameters, post links, share links or login redirects.
- Facebook is line 4 of a band record and line 3 of an artist record.
  Preserve every other positional field, including empty lines.
- Prefer a link from an official site or an identified artist profile.
  Confirm identity with biographical or musical context, not a matching name
  or a successful HTTP response alone.
- Do not substitute a band's Facebook for an individual artist's page.
  Do not guess handles or mark an inaccessible candidate as verified.
- If official sources and reasonable alternative checks reveal no verifiable
  account, leave the Facebook field empty and report it as unverified. Do not
  repeatedly probe guessed handles or block other completed work indefinitely.
  An empty field does not mean the person has no Facebook account.
- A website field must identify the artist's or band's own website/profile,
  not an unrelated research source. Ask before substituting a biography page.

## Facebook page posts

- Write short, visitor-facing announcements, not development summaries.
  For a new band page, use `<Band name> page added.` as the opening line.
  When reusing a commit title, add sentence punctuation to the Facebook opening
  line; the Git commit title itself must still have no final period.
  Preserve the site's sentence case: write `Related releases`, not
  `Related Releases`, including when adapting a commit title.
- Welcome newly added artists using the established wording:
  `Welcome to Kiss Bands Tree <artist names>!`
  Separate names with commas and use `and` before the last name.
- Do not add a band-history paragraph or repeat the lineup unless requested.
- Add the public band-page URL first, without a trailing slash. Check that the
  link preview identifies the correct numbered lineup and shows its band photo.
  Once the card is generated, remove the visible band URL while keeping the
  linked preview card. Keep any GitHub commit URL as a text link only, without
  replacing the band's photo preview.
- Add relevant, verified Facebook artist or band mentions before the hashtags
  when appropriate. Select actual accounts in Facebook's mention picker;
  plain text beginning with `@` is not a verified linked mention.
- Include everyone in the featured lineup and everyone named in its comments,
  without duplicates, in the artist hashtags. Include only working, verified
  mentions in the mention line; do not substitute plain names or profile URLs
  for unavailable mentions. Try a verified stored Facebook handle when
  searching by name does not return the correct person.
- End with relevant band and artist hashtags, including the associated Kiss
  member. Do not add `#KISS` or `#KissBandsTree`.
  Preserve readable name capitalization, such as `#RobertMehmetIkiz`, rather
  than accepting a lowercased hashtag suggestion. Keep verified mention
  display names as supplied by the account, including `ikiz`.
- An emoji is optional and must fit the specific band or subject. Do not use
  a generic guitar by default. If no suitable emoji exists, omit it; for
  Shogun Mojo, use no emoji rather than an unrelated substitute.
- Separate the announcement, welcome, link and hashtags with blank lines.
- Creating a post means drafting it unless the user explicitly requests
  publication. Publish only through an available logged-in session with access
  to the correct Kiss Bands Tree Facebook Page. Verify the final text, link
  preview and Page identity before publishing, then verify the published post.
  When publication is authorized, the assistant publishes the approved post;
  do not hand the publishing step back to the user.
  If access is unavailable, provide the draft and report that limitation.

Approved Shogun Mojo band-page example:

```text
Shogun Mojo page added.

Welcome to Kiss Bands Tree Dan Reed, Bengan Jonasson and Robert Mehmet Ikiz!

https://kissbandstree.com/band.php?a=shogun_mojo_2

#ShogunMojo #TommyThayer #DanReed #BenganJonasson #RobertMehmetIkiz
```

Approved Shogun Mojo album announcement (user-edited wording and case;
the mention line uses actual linked accounts in Facebook):

```text
Added Shogun Mojo album to Related releases.

https://github.com/kissbandstree/kissbandstree.com/commit/74770be2e6ef95ec5ed7552bb2758f172f010e1a

Shogun Mojo Dan Reed ikiz

#ShogunMojo #TommyThayer #DanReed #BenganJonasson #RobertMehmetIkiz
```

## Completion checklist

- Band: correct numbered lineup, dates, website, verified Facebook when
  available, full lineup, short Comments, band photo, L-R caption and credits.
- Artists: matching names, instruments, verified metadata, own website and
  Facebook when available, individual photos, thumbnails and photo credits.
- Relationships: all relevant chronological artist lists updated; reciprocal
  artist/band links and numbered tree links resolve.
- Photos: untouched originals archived in the correct SOURCE directory; large
  images and thumbnails generated from the best original; no band-photo crops
  used as artist portraits.
- Validation: new pages and images respond correctly without PHP warnings;
  table/grid and tree discover the records; show the rendered page after
  meaningful changes and leave the browser on the relevant page.
- Run PHP lint for PHP changes and `git diff --check` for text changes.
- Keep project Markdown and agent instructions in GitHub, not on production.
  Keep push filters and incremental/full deployment exclusions consistent.

## Photos

- Strongly prefer color photos. Do not replace a color photo with a
  black-and-white alternative without the user's approval.
- Save untouched band originals in
  `X:\My Drive\WEBSITES\WEB SITES SOURCE\kissbandstree.com\photos_bands_originals`.
- Save untouched artist originals in
  `X:\My Drive\WEBSITES\WEB SITES SOURCE\kissbandstree.com\photos_artists_originals`.
- These SOURCE archives are separate from the Google Drive website directory.
  Do not put originals in the deployed site.
- Keep the website's `photos_bands_originals/` folder local only, excluded from
  Git and FTP/SFTP deployment. It is awaiting cleanup and may be added later
  only on explicit request. Exclusion does not authorize deleting originals.
  Keep `.gitignore`, `.ftpignore` and the workflow's push filters and both
  deployment modes consistent.
- The artist-original archive must contain only originals of photos currently
  used on the site. Keep research candidates in temporary session storage.
  Remove rejected candidates and superseded originals from this archive;
  verify the current published photo before deleting. Do not retain band-photo
  copies in the artist-original archive.
- Always find the best available source: inspect official full-size links and
  `srcset`, compare actual dimensions and detail, and avoid preview downloads
  when a better original exists. Do not substitute artificial enlargement for
  a better source.
- Archive downloaded or received bytes unchanged. Never overwrite a different
  original.
- Never create artist portraits by cropping band or group photos. Find an
  individual portrait or performance photo of the artist instead. If a suitable
  photo is unavailable, use the standard artist placeholder and report the gap.
- Compare existing photos before cropping. Frame bands closely while retaining
  all members. Prepare square artist portraits with clear faces.
- Generate final images directly from the original, not from resized previews.
  Band PNG outputs normally use 1018x650 large and 155x99 thumbnail dimensions.
  Artist thumbnails use 64x64; large portraits should retain native detail
  rather than being enlarged merely to reach 1024x1024.
- Include verified **L-R** names in band photo descriptions. Record the actual
  photographer and source for each photo.
- Verify rendered band and artist pages, reciprocal links, indexes and tree,
  photo dimensions and credits before completing an addition.

## Joey Melotti photo reference

- User-approved individual performance photo:
  <https://discogs.com/artist/978166/image/SW1hZ2U6MTUzNzg3ODA=>.
- The untouched 600x473 JPEG is retained in the artist-original archive as
  `joey_melotti_discogs_15378780.jpg`. Crop `(80,45,400,400)` and generate the
  native 400x400 portrait and 64x64 thumbnail directly from that original.
- The photographer is unverified. Keep the existing photographer field blank
  rather than guessing an attribution.

## Shogun Mojo references

- The lineup with Tommy is **Shogun Mojo #2** (`shogun_mojo_2`).
  Tommy joined the longstanding trio of Dan Reed, Bengan Jonasson and Robert
  Mehmet Ikiz; he did not form a new band with them.
- Official lineup: <https://shogunmojo.com/the-cast>.
- Related release `shogun_mojo` is the self-titled debut, announced for
  30 October 2026. Frontiers' EU and US CD listings and Apple Music agree on
  this date; the earlier Ultimate Classic Rock announcement said 6 November.
  Use the label's CD track order, which differs from Apple's digital order:
  <https://frontiers.shop/products/shogun-mojo-shogun-mojo-cd> and
  <https://frontiers-us.shop/products/shogun-mojo-shogun-mojo-cd>.
- Album cover: <https://music.apple.com/us/album/shogun-mojo/6789621161>.
  The untouched 3000x3000 download is retained in the external SOURCE archive
  under `photos_releases_originals/shogun_mojo_apple_music_6789621161.jpg`.
  Generate the 1024x1024 cover and 128x128 thumbnail directly from that image,
  without cropping. It matches the cover on Frontiers' CD product mockup.
- Full-resolution photo, 3567x2290:
  <https://shogunmojo.com/storage/2026/04/SHOGUN-MOJO-BAND-SHOT-EXTERIOR-MEDIUM.png>.
- Photographer attribution, Paja Hartmanova / Frontiers Music Srl:
  <https://ultimateclassicrock.com/shogun-mojo-tommy-thayer>.
- L-R: Robert Mehmet Ikiz, Tommy Thayer, Dan Reed, Bengan Jonasson.
- Robert Mehmet Ikiz's individual performance photo is by Gerald Langer:
  <https://www.turkishjazz.org/artist/robert-mehmet-ikiz>.
  Its untouched original is retained in the artist-original archive.
- Dan Reed's individual December 2019 color portrait is by JürnC, CC BY-SA 4.0:
  <https://commons.wikimedia.org/wiki/File:Dan_Reed.jpg>.
  License: <https://creativecommons.org/licenses/by-sa/4.0>.
  The untouched original is retained in the artist-original archive as
  `Dan_Reed_JuernC_2019.jpg`. Rotate 90 degrees clockwise, then crop
  `(450,150,2400,2400)` in the upright image; generate the 1024x1024 portrait
  and 64x64 thumbnail directly from that original.
  Restored at the user's request instead of the black-and-white 2016
  performance photo; only the currently used color original belongs in the
  artist-original archive.
- Bengan Jonasson's artist-selected performance photo is by Mattias Boström:
  <https://facebook.com/photo/?fbid=10151280424111128>.
  The untouched 640x640 download is retained in the artist-original archive as
  `bengan_jonasson_mattias_bostrom_facebook_10151280424111128.jpg`.
  Crop `(145,30,400,400)` and generate the native 400x400 portrait and 64x64
  thumbnail directly from that original. Do not restore the rejected
  band-photo crop.
- Individual-photo candidate: Peter Bothén's photograph of Bengan playing bass,
  taken 25 September 2020:
  <https://www.flickr.com/photos/134299215@N03/50395425767>.
  Flickr marks it All rights reserved. Obtain reuse permission before
  publishing; attribution alone is not permission. Do not use the Myspace
  image URL, which now returns a camera placeholder.
- Verified Facebook: Shogun Mojo <https://facebook.com/ShogunMojoOfficial>
  (the page name and band description match the official website);
  Dan Reed <https://facebook.com/DanReedMusic> (the page
  identifies him and promotes Shogun Mojo); Robert Mehmet Ikiz
  <https://facebook.com/ikizmusic> (linked by his Turkish Jazz artist profile).
- Bengan Jonasson's Facebook: <https://facebook.com/benganjonasson>,
  confirmed by the user.
- Outstanding content: verified personal websites for the new artists.
  Current Dan/Bengan website fields link to the band's cast biography and
  Robert's to Yamaha's biography; do not mistake these for personal websites.
- User-supplied individual-photo candidate: Bengan performing at Nordic Guitar
  Con in Ed, published 30 October 2018, photographer Tommy Lofgren
  (credited as Tommy Löfgren):
  <https://www.dalslanningen.se/dals-ed/forsta-gangen-for-nordic-guitar-i-ed>.
  The caption identifies Bengan. The uncropped preview shows an individual
  performance photograph, not a band-photo crop. Reuse permission remains
  unconfirmed; do not publish until cleared. Image asset:
  <https://static.bonniernews.se/ba/0f45c269-0f4d-5b09-a446-0d91d8abe312.jpeg>.

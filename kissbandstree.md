# Kiss Bands Tree Style Rules

## Required working procedure

1. Read this document before each task. Inspect the relevant PHP reader and
   several comparable existing records before changing data or presentation.
2. Verify that localhost:8003 serves `D:\kissbandstree.com`. Check repository
   status and preserve unrelated local work. Never use the Google Drive website
   copy as the working site.
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
- Never use rounded corners (no border-radius).
- Avoid decorative visual effects unless explicitly requested.
- Use `<h2>` for all section headers. Reserve `<h1>` only for the main page title/top-line heading.

## Working copy

- The authoritative working site is `D:\kissbandstree.com`, served at
  `http://localhost:8003`. The Google Drive website copy is not the active
  working copy. Verify the server document root before editing or previewing.
- Do not synchronize the old Google Drive site over this working copy.
  Transfer individual additions only after checking for conflicts.

## Adding bands and artists

- Verify names, roles, dates and links against official sources. Preserve blank
  fields in the existing positional text-file formats; do not invent metadata.
- Complete band entries with lineup, official link, photos, credits and working
  artist links. Create missing artist pages with portraits and credits, and add
  reciprocal entries to the relevant chronological `bands_list` files.
- Match lineup names exactly to artist names and reuse existing artist records.
- Preserve lineup numbering in the display name, filename, photos, comments,
  credits, chronological artist lists and tree links. Joining an existing trio
  is not the same as founding a new band. Do not create an earlier lineup with
  guessed dates.
- Write URLs without a trailing slash, including official website links,
  photo sources and documentation references.
- Read representative existing entries before adding or changing site content.
  Follow established conventions rather than introducing a new format.
- Keep band Comments short and factual, in a single unbroken text block with
  no paragraph breaks. Focus on relevant band history or lineup changes rather
  than repeating the lineup or writing a promotional biography.
- Never mention sources or include source URLs in Comments. Keep research
  references in project documentation, separate from the published comment.
  Distinguish announced plans from completed releases.
- Band records contain name, active dates, website, Facebook identifier, a
  reserved blank line, associated Kiss members, then lineup lines.
- Artist records contain name, instruments, Facebook identifier, gender, date
  of passing, associated Kiss members, aliases, website and date of birth.
- Band credits contain description, photographer, source name and source URL.
  Artist credits contain description, photographer and source URL. Artist
  credits skip blank lines when read, so preserve those three field positions.

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
- Welcome newly added artists using the established wording:
  `Welcome to Kiss Bands Tree <artist names>!`
  Separate names with commas and use `and` before the last name.
- Do not add a band-history paragraph or repeat the lineup unless requested.
- Include the public band-page URL without a trailing slash. Check that the
  link preview identifies the correct numbered lineup and shows its band photo.
- End with relevant band and artist hashtags, including the associated Kiss
  member. Do not add `#KISS` or `#KissBandsTree`.
- An emoji is optional and must fit the specific band or subject. Do not use
  a generic guitar by default. If no suitable emoji exists, omit it; for
  Shogun Mojo, use no emoji rather than an unrelated substitute.
- Separate the announcement, welcome, link and hashtags with blank lines.
- Creating a post means drafting it unless the user explicitly requests
  publication. Publish only through an available logged-in session with access
  to the correct Kiss Bands Tree Facebook Page. Verify the final text, link
  preview and Page identity before publishing, then verify the published post.
  If access is unavailable, provide the draft and report that limitation.

Approved Shogun Mojo example:

```text
Shogun Mojo page added.

Welcome to Kiss Bands Tree Dan Reed, Bengan Jonasson and Robert Mehmet Ikiz!

https://kissbandstree.com/band.php?a=shogun_mojo_2

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
- These Google Drive source archives remain intentional even though the
  working website is on D:. Do not put originals in the deployed site.
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

## Shogun Mojo references

- The lineup with Tommy is **Shogun Mojo #2** (`shogun_mojo_2`).
  Tommy joined the longstanding trio of Dan Reed, Bengan Jonasson and Robert
  Mehmet Ikiz; he did not form a new band with them.
- Official lineup: <https://shogunmojo.com/the-cast>.
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
- Bengan Jonasson uses the standard artist placeholder pending a suitable
  individual photo; do not restore the rejected band-photo crop.
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
- Outstanding content: Bengan's individual photo and verified personal websites
  for the new artists.
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

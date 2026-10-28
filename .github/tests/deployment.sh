#!/usr/bin/env bash
set -euo pipefail

workflow="${1:?Pass the workflow path}"
test_dir=$(mktemp -d)
cleanup() {
  rm -f "$test_dir/upload.sh" "$test_dir/full.sh" "$test_dir/uploads.txt" \
    "$test_dir/deletes.txt" "$test_dir/batch.txt" "$test_dir/result.txt" \
    "$test_dir/plan.sh" "$test_dir/output.txt" "$test_dir/site/.ftpignore" \
    "$test_dir/site/photos_bands_originals/keep.png" \
    "$test_dir/site/photos_bands/keep.png"
  rmdir "$test_dir/site/photos_bands_originals" "$test_dir/site/photos_bands" \
    "$test_dir/site" "$test_dir"
}
trap cleanup EXIT
mkdir -p "$test_dir/site/photos_bands_originals" "$test_dir/site/photos_bands"

extract_step() {
  awk -v step="$1" '
    $0 == "      - name: " step { found = 1; next }
    found && /^      - name:/ { exit }
    found && /^        run: \|/ { body = 1; next }
    body { sub(/^          /, ""); print }
  ' "$workflow" > "$2"
  test -s "$2"
  bash -n "$2"
}

extract_step "Upload changed website files" "$test_dir/upload.sh"
extract_step "Full sync website files" "$test_dir/full.sh"
extract_step "Prepare deployment plan" "$test_dir/plan.sh"

export DEPLOY_UPLOAD_LIST="$test_dir/uploads.txt"
export DEPLOY_DELETE_LIST="$test_dir/deletes.txt"
export DEPLOY_SFTP_BATCH="$test_dir/batch.txt"
export TEST_RESULT="$test_dir/result.txt"
export SFTP_REMOTE_DIR=/website
export SFTP_PASSWORD=test SFTP_USERNAME=test SFTP_HOST=test SFTP_PORT=22

git() {
  case "$1" in
    rev-parse) echo base ;;
    diff)
      printf '%s\n' \
        $'A\tphotos_bands_originals/keep.png' \
        $'M\tphotos_bands_originals/keep.png' \
        $'T\tphotos_bands_originals/keep.png' \
        $'D\tphotos_bands_originals/deleted.png' \
        $'R100\tphotos_bands_originals/old.png\tphotos_bands_originals/keep.png' \
        $'C100\tphotos_bands_originals/old.png\tphotos_bands_originals/keep.png' \
        $'A\t.ftpignore'
      if [[ "${TEST_ARCHIVE_ONLY:-false}" != true ]]; then
        printf '%s\n' $'M\tphotos_bands/keep.png' $'D\tphotos_bands/deleted.png'
      fi
      ;;
    *) echo "Unexpected git command: $*" >&2; return 1 ;;
  esac
}
export -f git
export DEPLOY_MODE=changed GITHUB_SHA=test GITHUB_OUTPUT="$test_dir/output.txt"
printf 'original\n' > "$test_dir/site/photos_bands_originals/keep.png"
printf 'published\n' > "$test_dir/site/photos_bands/keep.png"
printf 'photos_bands_originals/**\n' > "$test_dir/site/.ftpignore"
(
  cd "$test_dir/site"
  bash -e -o pipefail "$test_dir/plan.sh"
)
test "$(cat "$DEPLOY_UPLOAD_LIST")" = photos_bands/keep.png
test "$(cat "$DEPLOY_DELETE_LIST")" = photos_bands/deleted.png
grep -q '^has_changes=true$' "$GITHUB_OUTPUT"

export TEST_ARCHIVE_ONLY=true
: > "$GITHUB_OUTPUT"
(
  cd "$test_dir/site"
  bash -e -o pipefail "$test_dir/plan.sh"
)
test ! -s "$DEPLOY_UPLOAD_LIST"
test ! -s "$DEPLOY_DELETE_LIST"
grep -q '^has_changes=false$' "$GITHUB_OUTPUT"

sshpass() {
  cat > "$TEST_RESULT"
}
export -f sshpass

printf '%s\n' index.php content.php data/artists.json data/bands.json style.css > "$DEPLOY_UPLOAD_LIST"
printf '%s\n' artists/old.txt bands/old.txt > "$DEPLOY_DELETE_LIST"
bash -e -o pipefail "$test_dir/upload.sh"

line_number() {
  grep -n -F -- "$1" "$TEST_RESULT" | head -1 | cut -d: -f1
}
data_line=$(line_number 'put "data/bands.json"')
loader_line=$(line_number 'put "content.php"')
page_line=$(line_number 'put "index.php"')
last_upload=$(line_number 'put "style.css"')
delete_line=$(line_number '-rm "/website/artists/old.txt"')
test "$data_line" -lt "$loader_line"
test "$loader_line" -lt "$page_line"
test "$last_upload" -lt "$delete_line"

printf '' > "$DEPLOY_UPLOAD_LIST"
bash -e -o pipefail "$test_dir/upload.sh"
test "$(grep -c '^-rm ' "$TEST_RESULT")" -eq 2
if grep -q '^put ' "$TEST_RESULT"; then
  echo "Unexpected upload in deletion-only deployment" >&2
  exit 1
fi

lftp() {
  cat > "$TEST_RESULT"
}
export -f lftp
bash -e -o pipefail "$test_dir/full.sh"
data_line=$(line_number 'mirror --reverse --verbose --only-newer --no-perms ./data')
loader_line=$(line_number 'put ./content.php')
page_line=$(line_number 'mirror --reverse --verbose --only-newer --no-perms --parallel=4')
test "$data_line" -lt "$loader_line"
test "$loader_line" -lt "$page_line"
grep -q -F -- '--exclude-glob .github/**' "$TEST_RESULT"
grep -q -F -- '--exclude-glob .ftpignore' "$TEST_RESULT"
grep -q -F -- '--exclude-glob photos_bands_originals/**' "$TEST_RESULT"
grep -q -F -- '      - photos_bands_originals/**' "$workflow"
grep -q -F -- '      - .ftpignore' "$workflow"
site_root=$(cd "$(dirname "$workflow")/../.." && pwd)
grep -q -x -F -- '/photos_bands_originals/' "$site_root/.gitignore"
grep -q -x -F -- 'photos_bands_originals/**' "$site_root/.ftpignore"

echo "PASS: deployment syntax, upload order, deletes last, deletion-only mode, tooling/archive exclusions, deployable photos."

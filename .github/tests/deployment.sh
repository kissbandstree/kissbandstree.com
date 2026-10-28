#!/usr/bin/env bash
set -euo pipefail

workflow="${1:?Pass the workflow path}"
test_dir=$(mktemp -d)
cleanup() {
  rm -f "$test_dir/upload.sh" "$test_dir/full.sh" "$test_dir/uploads.txt" \
    "$test_dir/deletes.txt" "$test_dir/batch.txt" "$test_dir/result.txt"
  rmdir "$test_dir"
}
trap cleanup EXIT

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

export DEPLOY_UPLOAD_LIST="$test_dir/uploads.txt"
export DEPLOY_DELETE_LIST="$test_dir/deletes.txt"
export DEPLOY_SFTP_BATCH="$test_dir/batch.txt"
export TEST_RESULT="$test_dir/result.txt"
export SFTP_REMOTE_DIR=/website
export SFTP_PASSWORD=test SFTP_USERNAME=test SFTP_HOST=test SFTP_PORT=22

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

echo "PASS: deployment syntax, changed/full upload order, deletes last, deletion-only mode, tooling exclusion."

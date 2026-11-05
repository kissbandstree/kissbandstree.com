#!/usr/bin/env bash
set -euo pipefail
workflow="${1:?Pass the workflow path}"
test -f "$workflow"
site_root=$(cd "$(dirname "$workflow")/../.." && pwd)
node --test "$site_root/.github/tests/deployment.cjs"

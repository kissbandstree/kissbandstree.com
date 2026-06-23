(function () {
  "use strict";

  var tableSelector = "table.sortable, table.sortable2";
  var indicatorClass = "sorttable2-indicator";
  var cacheVersion = 1;
  var rowCacheProp = "__sort2cache";
  var collator = new Intl.Collator(undefined, { numeric: true, sensitivity: "base" });

  var monthValue = {
    jan: 0 / 12,
    feb: 1 / 12,
    mar: 2 / 12,
    apr: 3 / 12,
    may: 4 / 12,
    jun: 5 / 12,
    jul: 6 / 12,
    aug: 7 / 12,
    sep: 8 / 12,
    oct: 9 / 12,
    nov: 10 / 12,
    dec: 11 / 12
  };

  function init() {
    var tables = document.querySelectorAll(tableSelector);
    for (var i = 0; i < tables.length; i++) {
      wireTable(tables[i]);
    }
  }

  function wireTable(table) {
    if (!table.tHead || !table.tHead.rows.length) return;

    var headers = table.tHead.rows[0].cells;
    var defaultSort = null;
    for (var col = 0; col < headers.length; col++) {
      var th = headers[col];
      if (isNoSort(th)) continue;
      th.style.cursor = "pointer";
      th.addEventListener("click", createHeaderHandler(table, th, col));

      var defaultDir = (th.getAttribute("data-sort-default") || "").toLowerCase();
      if (!defaultSort && (defaultDir === "asc" || defaultDir === "desc")) {
        defaultSort = {
          header: th,
          colIndex: col,
          dir: defaultDir
        };
      }
    }

    if (defaultSort) {
      applySort(table, defaultSort.header, defaultSort.colIndex, defaultSort.dir);
    }
  }

  function isNoSort(th) {
    return /\bsorttable_nosort\b/.test(th.className || "");
  }

  function createHeaderHandler(table, header, colIndex) {
    return function () {
      var prevCol = table.getAttribute("data-sort-col");
      var prevDir = table.getAttribute("data-sort-dir") || "asc";
      var dir = prevCol === String(colIndex) && prevDir === "asc" ? "desc" : "asc";

      applySort(table, header, colIndex, dir);
    };
  }

  function applySort(table, header, colIndex, dir) {
    var tbody = table.tBodies[0];
    if (!tbody) return;

    var type = detectType(table, header, colIndex);
    sortBodyRows(tbody, colIndex, type, dir);
    reindexIfConfigured(table, tbody);
    table.setAttribute("data-sort-col", String(colIndex));
    table.setAttribute("data-sort-dir", dir);
    setSortIndicator(table, header, dir);

    cacheVersion++;

    if (typeof CountRows === "function") {
      CountRows();
    }
  }

  function reindexIfConfigured(table, tbody) {
    var reindexAttr = table.getAttribute("data-sort-reindex-col");
    if (reindexAttr === null || reindexAttr === "") return;

    var colIndex = Number(reindexAttr);
    if (!Number.isInteger(colIndex) || colIndex < 0) return;

    var rows = tbody.rows;
    for (var i = 0; i < rows.length; i++) {
      if (rows[i].cells[colIndex]) {
        rows[i].cells[colIndex].textContent = String(i + 1);
      }
    }
  }

  function detectType(table, header, colIndex) {
    var explicit = (header.getAttribute("data-sort-type") || "").toLowerCase();
    if (explicit) return explicit;

    var rows = table.tBodies[0] ? table.tBodies[0].rows : [];
    var max = Math.min(rows.length, 25);
    for (var i = 0; i < max; i++) {
      var text = cellText(rows[i], colIndex);
      if (!text) continue;
      if (isDmyDate(text)) return "date-dmy";
      if (isMostlyNumeric(text)) return "number";
      if (looksLikeYearText(text)) return "year";
      return "text";
    }

    return "text";
  }

  function sortBodyRows(tbody, colIndex, type, dir) {
    var rows = Array.prototype.slice.call(tbody.rows);
    var prepared = new Array(rows.length);

    for (var i = 0; i < rows.length; i++) {
      prepared[i] = {
        row: rows[i],
        originalIndex: i,
        key: keyFor(rows[i], colIndex, type)
      };
    }

    prepared.sort(function (a, b) {
      var cmp = compareByType(a.key, b.key, type);
      if (cmp === 0) cmp = a.originalIndex - b.originalIndex;
      return dir === "asc" ? cmp : -cmp;
    });

    for (var j = 0; j < prepared.length; j++) {
      tbody.appendChild(prepared[j].row);
    }
  }

  function keyFor(row, colIndex, type) {
    var cell = row.cells[colIndex];
    if (!cell) return "";

    if (!row[rowCacheProp] || row[rowCacheProp].version !== cacheVersion) {
      row[rowCacheProp] = { version: cacheVersion, keys: {} };
    }

    var cacheKey = colIndex + ":" + type;
    if (Object.prototype.hasOwnProperty.call(row[rowCacheProp].keys, cacheKey)) {
      return row[rowCacheProp].keys[cacheKey];
    }

    var source = cell.getAttribute("data-sort-value");
    var hasCustomValue = source !== null;
    if (!hasCustomValue) source = cellText(row, colIndex);

    var key;
    if (type === "number") {
      key = parseNumber(source);
    } else if (type === "date-dmy") {
      if (hasCustomValue) {
        var numericCustom = Number(source);
        key = Number.isFinite(numericCustom) ? numericCustom : parseDmy(source);
      } else {
        key = parseDmy(source);
      }
    }
    else if (type === "year") key = parseYearRange(source);
    else key = normalize(source);

    row[rowCacheProp].keys[cacheKey] = key;
    return key;
  }

  function compareByType(a, b, type) {
    if (type === "year") return compareYearKeys(a, b);

    if (typeof a === "number" && typeof b === "number") {
      if (a === b) return 0;
      return a < b ? -1 : 1;
    }

    return collator.compare(String(a), String(b));
  }

  function compareYearKeys(a, b) {
    if (!a.known && !b.known) return collator.compare(a.raw, b.raw);
    if (!a.known) return 1;
    if (!b.known) return -1;

    if (a.start !== b.start) return a.start < b.start ? -1 : 1;
    if (a.end !== b.end) return a.end < b.end ? -1 : 1;
    return collator.compare(a.raw, b.raw);
  }

  function cellText(row, colIndex) {
    var cell = row.cells[colIndex];
    if (!cell) return "";
    return (cell.textContent || "").replace(/\s+/g, " ").trim();
  }

  function normalize(value) {
    return String(value || "").replace(/\s+/g, " ").trim().toLowerCase();
  }

  function isMostlyNumeric(value) {
    var n = parseNumber(value);
    return Number.isFinite(n);
  }

  function parseNumber(value) {
    var cleaned = String(value || "").replace(/[^0-9.-]/g, "");
    if (!cleaned) return Number.POSITIVE_INFINITY;
    var n = Number(cleaned);
    return Number.isFinite(n) ? n : Number.POSITIVE_INFINITY;
  }

  function isDmyDate(value) {
    return /^(\d{1,2}|XX)-(\d{1,2}|XX)-(\d{4}|XXXX)$/i.test(String(value || "").trim());
  }

  function parseDmy(value) {
    var m = String(value || "").trim().match(/^(\d{1,2}|XX)-(\d{1,2}|XX)-(\d{4}|XXXX)$/i);
    if (!m) return Number.POSITIVE_INFINITY;

    if (m[3].toUpperCase() === "XXXX") return Number.POSITIVE_INFINITY;

    var day = m[1].toUpperCase() === "XX" ? 1 : Number(m[1]);
    var month = m[2].toUpperCase() === "XX" ? 0 : Number(m[2]) - 1;
    var year = Number(m[3]);
    var ts = Date.UTC(year, month, day);
    return Number.isFinite(ts) ? ts : Number.POSITIVE_INFINITY;
  }

  function looksLikeYearText(value) {
    var text = normalize(value);
    return /\d{4}/.test(text) || /(present|current|ongoing|[x\?])/.test(text);
  }

  function parseYearRange(value) {
    var raw = normalize(value).replace(/[–—]/g, "-").replace(/\s*-\s*/g, " - ");
    if (!raw) return unknownYear(raw);

    var parts = raw.split(" - ");
    var start = parseYearPoint(parts[0], false);
    var end = parts.length > 1 ? parseYearPoint(parts.slice(1).join(" - "), true) : parseYearPoint(parts[0], true);

    if (start === null && end !== null) start = end;
    if (end === null && start !== null) end = start;
    if (start === null || end === null) return unknownYear(raw);

    return { known: true, start: start, end: end, raw: raw };
  }

  function unknownYear(raw) {
    return { known: false, start: Number.POSITIVE_INFINITY, end: Number.POSITIVE_INFINITY, raw: raw || "" };
  }

  function parseYearPoint(part, isRangeEnd) {
    var text = normalize(part);
    if (!text) return null;

    if (/(present|current|ongoing|now)/.test(text)) return Number.POSITIVE_INFINITY;
    if (/(unknown|\?)/.test(text) && !/\d{4}/.test(text)) return null;

    var m = text.match(/^(\d{2})[x\?]{2}$/i);
    if (m) return Number(m[1]) * 100 + (isRangeEnd ? 99 : 0);

    m = text.match(/^(\d{3})[x\?]$/i);
    if (m) return Number(m[1]) * 10 + (isRangeEnd ? 9 : 0);

    m = text.match(/^(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\s+(\d{4})$/);
    if (m) return Number(m[2]) + monthValue[m[1]];

    m = text.match(/^(\d{4})$/);
    if (m) return Number(m[1]) + (isRangeEnd ? 0.99 : 0);

    var yearMatch = text.match(/(\d{4})/);
    if (!yearMatch) return null;

    var y = Number(yearMatch[1]);
    var mo = text.match(/\b(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)\b/);
    if (mo) return y + monthValue[mo[1]];

    return y + (isRangeEnd ? 0.99 : 0);
  }

  function setSortIndicator(table, activeHeader, dir) {
    var headers = table.tHead.rows[0].cells;
    for (var i = 0; i < headers.length; i++) {
      var old = headers[i].querySelector("." + indicatorClass);
      if (old) old.remove();
    }

    var indicator = document.createElement("span");
    indicator.className = indicatorClass;
    indicator.textContent = dir === "asc" ? " \u25be" : " \u25b4";
    activeHeader.appendChild(indicator);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();

document.addEventListener("DOMContentLoaded", async () => {

  /* ============================================================
     Local Storage / Persistence Configuration
     ------------------------------------------------------------
     We store:
     - Table row data
     - Column header names
     - Timestamp for expiration logic

     Table auto-resets if 60 minutes pass without saving.
     ============================================================ */

  const STORAGE_KEY = "dataverse_cargo_data";
  const HEADER_KEY = "dataverse_cargo_headers";
  const STORAGE_TIME = "dataverse_cargo_timestamp";
  const EXPIRY_MINUTES = 60;

  // Returns true if stored data is older than defined expiry window
  const isExpired = () => {
    const t = Number(localStorage.getItem(STORAGE_TIME) || 0);
    const diff = (Date.now() - t) / 60000;
    return diff > EXPIRY_MINUTES;
  };

  // Save table data + timestamp
  const saveData = (data) => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    localStorage.setItem(STORAGE_TIME, Date.now().toString());
  };

  // Load saved table data unless expired
  const loadData = () => {
    if (isExpired()) return [];
    return JSON.parse(localStorage.getItem(STORAGE_KEY) || "[]");
  };

  // Save array of column headers + timestamp
  const saveHeaders = (arr) => {
    localStorage.setItem(HEADER_KEY, JSON.stringify(arr));
    localStorage.setItem(STORAGE_TIME, Date.now().toString());
  };

  // Load saved header labels unless expired
  const loadHeaders = () => {
    if (isExpired()) return [];
    return JSON.parse(localStorage.getItem(HEADER_KEY) || "[]");
  };

  /* ============================================================
     Fetch Commodity Names from API
     ------------------------------------------------------------
     Used for dropdown/autocomplete list in Commodity column.
     ============================================================ */

  let commodities = [];
  try {
    const res = await fetch("/api/uex/commodities");
    const json = await res.json();
    commodities = json.map(i => i.name).filter(Boolean);
  } catch (e) {
    console.warn("Commodity fetch failed:", e);
  }

  /* ============================================================
     Table Initialization Data
     ------------------------------------------------------------
     If saved rows exist → load them
     Otherwise → create 15 empty rows
     ============================================================ */

  const saved = loadData();
  const baseData = saved.length
    ? saved
    : Array.from({ length: 15 }, () => ({
        commodity: "",
        locA: 0, locB: 0, locC: 0, locD: 0, locE: 0,
        price: 0,
        total: 0,
      }));

  /* ============================================================
     Column Headers Setup
     ------------------------------------------------------------
     Load saved custom headers if available
     Otherwise fall back to default header labels
     ============================================================ */

  const savedHeaders = loadHeaders();
  const defaultHeaders = [
    "Commodity","Location A","Location B","Location C",
    "Location D","Location E","Total","Price (aUEC)"
  ];

  // Only use saved headers if correct length, otherwise reset
  const headerMap =
    savedHeaders.length === defaultHeaders.length
      ? savedHeaders
      : defaultHeaders;


  /* ============================================================
     Initialize Tabulator Table
     ============================================================ */

  const table = new Tabulator("#cargoTable", {
    data: baseData,
    layout: "fitColumns",
    autoResize: true,
    height: 500,
    theme: "midnight",
    reactiveData: true,
    resizableColumns: true,
    responsiveLayout: "collapse",   // enable automatic row collaps
    responsiveLayoutCollapseStartOpen: false,
    // We manage our own persistence, so disable Tabulator's
    persistence: false,

    columnDefaults: {
      headerSort: false,
      minWidth: 165,
    },

    // Table Columns
    columns: [
      /* Commodity Column (Dropdown / Editable List) */
      {
        title: headerMap[0],
        field: "commodity",
        editor: "list",
        editorParams: {
          values: commodities,
          sortValuesList: "asc",
          clearOnEdit: true,
          autocomplete: true,
          listOnEmpty: true,
        },
        editable: true,
      },

      /* Locations A–E Columns (numbers + bottom totals) */
      ...["A","B","C","D","E"].map((l,i) => ({
        title: headerMap[i+1],
        field: `loc${l}`,
        editor: "number",
        bottomCalc: "sum",
      })),

      /* Total Column (Calculated) */
      {
        title: headerMap[6],
        field: "total",
        mutator: (value, data) =>
          ["locA","locB","locC","locD","locE"]
            .reduce((s,k)=> s + (Number(data[k]) || 0), 0),
        bottomCalc: "sum",
      },

      /* Price Column */
      {
        title: headerMap[7],
        field: "price",
        editor: "number",
        bottomCalc: "sum",
      },
    ],
  });

  /* ============================================================
     Save Data on Cell Edit
     ============================================================ */
  table.on("cellEdited", () => {
    saveData(table.getData());
  });

  /* ============================================================
     Inline Header Editing (single-click rename)
     ------------------------------------------------------------
     Replaces Tabulator's default prompt editor.
     ============================================================ */
 table.on("headerClick", (e, column) => {
  const col = column;
  const def = col.getDefinition();

  // Only allow editing on locA-locE columns
  if (!def.field || !def.field.startsWith("loc")) return;

  const el = column.getElement();

  // Prevent double inputs
  if (el.querySelector("input")) return;

  const input = document.createElement("input");
  input.type = "text";
  input.value = def.title;

  input.style.width = "100%";
  input.style.padding = "2px 4px";
  input.style.background = "rgba(0,0,0,0.7)";
  input.style.color = "#00eaff";
  input.style.border = "1px solid #00eaff";
  input.style.outline = "none";
  input.style.fontSize = "12px";

  el.innerHTML = "";
  el.appendChild(input);
  input.focus();
  input.select();

  const save = () => {
    const newVal = input.value.trim() || def.title;
    table.updateColumnDefinition(col, { title: newVal });

    // Persist only A-E headers
    const headers = table.getColumns().map(c => c.getDefinition().title);
    saveHeaders(headers);
  };

  input.addEventListener("blur", save);
  input.addEventListener("keydown", (e) => {
    if (e.key === "Enter") save();
    if (e.key === "Escape") {
      table.updateColumnDefinition(col, { title: def.title });
    }
  });
  });
// Clear table data (reset back to blank rows)
document.getElementById("clearTableBtn").addEventListener("click", () => {
  if (confirm("Clear all cargo rows? This cannot be undone.")) {
    const blankRows = Array.from({ length: 15 }, () => ({
      commodity: "",
      locA: 0, locB: 0, locC: 0, locD: 0, locE: 0,
      price: 0,
      total: 0,
    }));

    table.setData(blankRows);
    saveData(blankRows);
    localStorage.removeItem(STORAGE_TIME);
  }
});
// Reset A–E headers to defaults
document.getElementById("resetHeadersBtn").addEventListener("click", () => {
  if (!confirm("Reset headers A–E back to default names?")) return;

  // canonical defaults by FIELD, not index
  const defaultTitles = {
    commodity: "Commodity",
    locA: "Location A",
    locB: "Location B",
    locC: "Location C",
    locD: "Location D",
    locE: "Location E",
    total: "Total",
    price: "Price (aUEC)",
  };

  // build NEW column defs based on field name NOT index
  const newColumns = table.getColumns().map(col => {
    const def = col.getDefinition();
    const f = def.field;

    if (defaultTitles[f]) {
      return { ...def, title: defaultTitles[f] };
    }

    return def; // untouched safety net
  });

  table.setColumns(newColumns);

  // persist canonical header array in correct order
  saveHeaders(Object.values(defaultTitles));

  localStorage.setItem(STORAGE_TIME, Date.now().toString());

  setTimeout(() => table.redraw(true), 10);
});



});

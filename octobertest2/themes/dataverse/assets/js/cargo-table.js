document.addEventListener("DOMContentLoaded", async () => {
  const STORAGE_KEY = "dataverse_cargo_data";
  const STORAGE_TIME = "dataverse_cargo_timestamp";
  const HEADER_KEY = "dataverse_cargo_headers";
  const EXPIRY_MINUTES = 60;

  const COL_KEYS = ["locA", "locB", "locC", "locD", "locE"];
  const COL_INDEX = { commodity: 0, locA: 1, locB: 2, locC: 3, locD: 4, locE: 5, total: 6, price: 7 };

  const isExpired = () => {
    const t = localStorage.getItem(STORAGE_TIME);
    return !t || (Date.now() - +t) / 60000 > EXPIRY_MINUTES;
  };

  const saveData = (data) => {
    const clean = data.slice(0, data.length - 1);
    localStorage.setItem(STORAGE_KEY, JSON.stringify(clean));
    localStorage.setItem(STORAGE_TIME, Date.now().toString());
  };

  const loadHeaders = () => JSON.parse(localStorage.getItem(HEADER_KEY) || "[]");
  const saveHeaders = (arr) => {
    localStorage.setItem(HEADER_KEY, JSON.stringify(arr));
    localStorage.setItem(STORAGE_TIME, Date.now().toString());
  };

  // fetch commodities
  let commodities = [];
  try {
    const res = await fetch("/api/uex/commodities");
    const json = await res.json();
    commodities = json.map(i => i.name).filter(Boolean);
  } catch (e) {
    console.warn("Commodity fetch failed:", e);
  }

  const container = document.getElementById("cargoTable");

  const blankRow = () => ({ commodity: "", locA: 0, locB: 0, locC: 0, locD: 0, locE: 0, total: 0, price: 0 });

  const baseData = !isExpired() && localStorage.getItem(STORAGE_KEY)
    ? JSON.parse(localStorage.getItem(STORAGE_KEY))
    : Array.from({ length: 15 }, blankRow);

  baseData.push({ commodity: "TOTAL", locA: 0, locB: 0, locC: 0, locD: 0, locE: 0, total: 0, price: 0, __summary: true });

  const storedHeaders = loadHeaders();
  const defaultHeaders = ["Commodity", "Location A", "Location B", "Location C", "Location D", "Location E", "Total", "Price (aUEC)"];
  const headerMap = defaultHeaders.map((h, i) => storedHeaders[i] ?? h);

  let hot;

  // Summary row renderer
  function summaryRenderer(instance, td, row, col, prop, value, cellProperties) {
    td.style.background = "#111";
    td.style.color = "#00eaff";
    td.style.fontWeight = "700";
    td.style.borderTop = "2px solid #ff00aa";
    Handsontable.renderers.NumericRenderer.call(this, instance, td, row, col, prop, value, cellProperties || {});
    return td;
  }

  function recalcAll(source = "calc") {
    if (!hot) return;
    const rows = hot.countRows();
    if (rows === 0) return;
    const summaryRow = rows - 1;

    hot.batch(() => {
      for (let r = 0; r < summaryRow; r++) {
        let rowSum = 0;
        for (const key of COL_KEYS) rowSum += Number(hot.getDataAtRowProp(r, key)) || 0;
        hot.setDataAtCell(r, COL_INDEX.total, rowSum, source);
      }

      for (const key of [...COL_KEYS, "total", "price"]) {
        let colSum = 0;
        for (let r = 0; r < summaryRow; r++) colSum += Number(hot.getDataAtRowProp(r, key)) || 0;
        hot.setDataAtCell(summaryRow, COL_INDEX[key], colSum, source);
      }

      hot.setDataAtCell(summaryRow, COL_INDEX.commodity, "TOTAL", source);
    });
  }

  hot = new Handsontable(container, {
    data: baseData,
    themeName: "ht-theme-main-dark",
    colHeaders: headerMap,
    columns: [
      { data: "commodity", type: "autocomplete", source: commodities, strict: false, filter: true, placeholder: "Type or select..." },
      { data: "locA", type: "numeric", numericFormat: { pattern: "0,0" } },
      { data: "locB", type: "numeric", numericFormat: { pattern: "0,0" } },
      { data: "locC", type: "numeric", numericFormat: { pattern: "0,0" } },
      { data: "locD", type: "numeric", numericFormat: { pattern: "0,0" } },
      { data: "locE", type: "numeric", numericFormat: { pattern: "0,0" } },
      { data: "total", type: "numeric", readOnly: true, numericFormat: { pattern: "0,0" } },
      { data: "price", type: "numeric", numericFormat: { pattern: "0,0.00 ₳" } },
    ],
    licenseKey: "non-commercial-and-evaluation",
    stretchH: "all",
    height: 500,
    rowHeaders: true,
    contextMenu: true,
    manualColumnResize: true,
    manualRowResize: true,

    cells(row) {
      const props = {};
      if (row === this.instance.countRows() - 1) {
        props.readOnly = true;
        props.renderer = summaryRenderer;
      }
      return props;
    },

    afterInit() {
      setTimeout(() => recalcAll(), 0);
    },

    afterChange(changes, source) {
      if (!changes || source === "loadData" || source === "calc") return;
      recalcAll();
      saveData(this.getSourceData());
    },

    afterGetColHeader(col, TH) {
      const text = TH.querySelector(".colHeader");
      if (!text || ["Total", "Price (aUEC)"].includes(headerMap[col])) return;

      text.style.cursor = "text";
      text.ondblclick = () => {
        const oldVal = text.textContent;
        const input = document.createElement("input");
        Object.assign(input.style, {
          width: "90%", background: "#0a0a0f", color: "#00eaff",
          border: "1px solid #ff00aa", borderRadius: "6px",
          padding: "2px 6px", fontFamily: "inherit", textAlign: "center",
        });
        input.value = oldVal;
        text.textContent = "";
        text.appendChild(input);
        input.focus(); input.select();
        input.addEventListener("blur", () => {
          const val = input.value.trim() || oldVal;
          headerMap[col] = val;
          saveHeaders(headerMap);
          hot.updateSettings({ colHeaders: headerMap });
        });
        input.addEventListener("keydown", e => { if (e.key === "Enter") input.blur(); });
      };
    },
  });

  saveData(hot.getSourceData());
});

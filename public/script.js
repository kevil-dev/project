// ── Shared state ──────────────────────────────────────────────────────────────
// These maps are filled once on page load, then reused by every fetchProducts() call.
// They live outside DOMContentLoaded so fetchProducts() can read them from anywhere.
var categoryMap = {};
var unitMap     = {};

// ── Boot ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {

    // Fetch meta (categories + units) first — these only need to be loaded once.
    // Products are fetched separately after the maps are ready.
    fetch('api/meta')
        .then(function (res) { return res.json(); })
        .then(function (metaData) {

            fillSelect('category-select', metaData.categories);
            fillSelect('unit-select',     metaData.units);

            // Build id → name lookup maps so buildRow() can display names instead of IDs
            metaData.categories.forEach(function (c) { categoryMap[c.id] = c.name; });
            metaData.units.forEach(function (u)      { unitMap[u.id]     = u.name; });

            // Load the initial product list with no filters applied
            fetchProducts();
        })
        .catch(function (err) {
            showMessage('Could not load page data: ' + err.message);
            console.error(err);
        });

    // ── Event listeners ───────────────────────────────────────────────────────

    // Search input: wrap fetchProducts in a 200ms debounce.
    //
    // Debouncing means: "wait until the user has stopped typing for 200ms, THEN
    // fire the request." Without it, every keystroke fires a separate fetch — so
    // typing "neem" sends 4 requests (n, ne, nee, neem). With debounce, it sends 1.
    document.getElementById('search-input')
        .addEventListener('input', debounce(fetchProducts, 200));

    // Dropdowns: fire immediately — the user made a deliberate pick, no need to wait
    document.getElementById('category-select').addEventListener('change', fetchProducts);
    document.getElementById('unit-select').addEventListener('change', fetchProducts);

    // Clear button: reset all three filter controls, then reload without any filters
    document.querySelector('.filter-bar .btn-secondary')
        .addEventListener('click', function () {
            document.getElementById('search-input').value     = '';
            document.getElementById('category-select').value = '';
            document.getElementById('unit-select').value     = '';
            fetchProducts();
        });

});

// ── Data fetching ─────────────────────────────────────────────────────────────

// Reads the current values of all three filter controls, builds the API URL,
// fetches matching products, and re-renders the product list.
function fetchProducts() {
    var search     = document.getElementById('search-input').value.trim();
    var categoryId = document.getElementById('category-select').value;
    var unitId     = document.getElementById('unit-select').value;

    // Build the query string from whichever filters have a value.
    // An empty string or "0" means "no filter" so we leave those out.
    var parts = [];
    if (search)     { parts.push('search='      + encodeURIComponent(search)); }
    if (categoryId) { parts.push('category_id=' + encodeURIComponent(categoryId)); }
    if (unitId)     { parts.push('unit_id='     + encodeURIComponent(unitId)); }

    var url = 'api/products' + (parts.length ? '?' + parts.join('&') : '');

    showMessage('Loading products…');

    fetch(url)
        .then(function (res) { return res.json(); })
        .then(function (productsData) {
            if (!productsData.data) {
                showMessage('API error: ' + JSON.stringify(productsData));
                return;
            }
            buildProductTable(productsData.data, categoryMap, unitMap);
        })
        .catch(function (err) {
            showMessage('Could not load products: ' + err.message);
            console.error(err);
        });
}

// ── Utilities ─────────────────────────────────────────────────────────────────

// Returns a wrapper function that delays calling fn until ms milliseconds have
// passed since the LAST time the wrapper was invoked.
//
// How it works: every time the wrapper is called, it cancels any pending timer
// and sets a new one. fn only actually runs when the timer expires without being
// cancelled — i.e. the user stopped triggering the event for ms ms.
function debounce(fn, ms) {
    var timer;
    return function () {
        clearTimeout(timer);        // cancel the previous pending call
        timer = setTimeout(fn, ms); // schedule a fresh one starting now
    };
}

// Replaces the content of #product-list with a plain text message
function showMessage(text) {
    var el = document.getElementById('product-list');
    if (el) { el.textContent = text; }
}

// Adds one <option> per item to a <select>
function fillSelect(selectId, items) {
    var select = document.getElementById(selectId);
    if (!select || !items) { return; }

    items.forEach(function (item) {
        var option         = document.createElement('option');
        option.value       = item.id;
        option.textContent = item.name;
        select.appendChild(option);
    });
}

// ── Rendering ─────────────────────────────────────────────────────────────────

// Builds a full <table> and injects it into #product-list
function buildProductTable(products, categoryMap, unitMap) {
    var container = document.getElementById('product-list');
    container.innerHTML = '';

    if (products.length === 0) {
        container.textContent = 'No products found.';
        return;
    }

    var table = document.createElement('table');

    var thead     = document.createElement('thead');
    var headerRow = document.createElement('tr');
    ['#', 'Image', 'Name', 'Price', 'Category', 'Unit', 'Actions'].forEach(function (label) {
        var th         = document.createElement('th');
        th.textContent = label;
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    table.appendChild(thead);

    var tbody = document.createElement('tbody');
    products.forEach(function (product) {
        tbody.appendChild(buildRow(product, categoryMap, unitMap));
    });
    table.appendChild(tbody);

    container.appendChild(table);
}

// Builds one <tr> for a product
function buildRow(product, categoryMap, unitMap) {
    var tr = document.createElement('tr');

    tr.appendChild(makeCell(product.id));

    // Image thumbnail, or a colourful gradient block when there is no image
    var tdImg = document.createElement('td');
    if (product.image_path) {
        var img       = document.createElement('img');
        img.src       = product.image_path;
        img.alt       = product.name;
        img.className = 'row-thumb';
        // If the image file is missing, replace the broken img with a gradient div
        img.onerror = function () {
            var div       = document.createElement('div');
            div.className = 'row-thumb';
            div.style.background = randomGradient();
            this.parentNode.replaceChild(div, this);
        };
        tdImg.appendChild(img);
    } else {
        var placeholder       = document.createElement('div');
        placeholder.className = 'row-thumb';
        placeholder.style.background = randomGradient();
        tdImg.appendChild(placeholder);
    }
    tr.appendChild(tdImg);

    tr.appendChild(makeCell(product.name));
    tr.appendChild(makeCell('$' + Number(product.price).toFixed(2)));
    tr.appendChild(makeCell(categoryMap[product.category_id] || '—'));
    tr.appendChild(makeCell(unitMap[product.unit_id]         || '—'));

    var editBtn           = document.createElement('button');
    editBtn.textContent   = 'Edit';
    editBtn.className     = 'btn-secondary';

    var deleteBtn         = document.createElement('button');
    deleteBtn.textContent = 'Delete';
    deleteBtn.className   = 'btn-danger';

    var btnWrap       = document.createElement('div');
    btnWrap.className = 'action-btns';
    btnWrap.appendChild(editBtn);
    btnWrap.appendChild(deleteBtn);

    var tdActions = document.createElement('td');
    tdActions.appendChild(btnWrap);
    tr.appendChild(tdActions);

    return tr;
}

// Returns a random CSS linear-gradient using 3 random HSL colours.
// Keeping saturation (70%) and lightness (62%) fixed means colours are always vivid —
// never washed out and never too dark.
function randomGradient() {
    var colours = [];
    for (var i = 0; i < 3; i++) {
        var hue = Math.floor(Math.random() * 360);
        colours.push('hsl(' + hue + ', 70%, 62%)');
    }
    return 'linear-gradient(135deg, ' + colours.join(', ') + ')';
}

// Helper: <td> with text content
function makeCell(text) {
    var td         = document.createElement('td');
    td.textContent = text;
    return td;
}

document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("search-input");
    if (!input) return;

    const cards = document.querySelectorAll(".item-card");
    const initialQuery = (input.dataset.initialQuery || "").trim();
    const initialSearchState = input.dataset.searchState || "";
    const listOrigin = input.dataset.listOrigin || "browser";
    const initialCategory = parseInt(input.dataset.initialCategory || "0", 10) || 0;
    const noItemsMsg = document.getElementById("no-items-message");
    const categorySelect = document.getElementById("category-select");

    const base = document.querySelector("base");
    const encodeUrl = (base ? base.getAttribute("href") : "") + "search_state/encode";
    const listBaseUrl = (base ? base.getAttribute("href") : "") + listOrigin + "/index";

    if (initialQuery) {
        input.value = initialQuery;
        filterCards(initialQuery);
        showNoItemsMessage();
    }

    input.addEventListener("input", function () {
        filterCards(input.value.trim());
        showNoItemsMessage();
    });

    cards.forEach(function (card) {
        card.addEventListener("click", function (e) {
            const link = card.querySelector("a");
            if (!link) return;

            e.preventDefault();

            const query = input.value.trim();
            const category = getCategoryValue();
            const baseUrl = link.getAttribute("href");

            if (!query && category === 0) {
                goToUrl(baseUrl);
                return;
            }

            if (initialSearchState && query === initialQuery && category === initialCategory) {
                goToUrl(withSearchState(baseUrl, initialSearchState));
                return;
            }

            encodeSearchState(query, category, function (token) {
                goToUrl(token ? withSearchState(baseUrl, token) : baseUrl);
            }, function () {
                goToUrl(baseUrl);
            });
        });
    });

    if (categorySelect) {
        categorySelect.addEventListener("change", function () {
            const query = input.value.trim();
            const category = getCategoryValue();

            if (!query && category === 0) {
                goToUrl(listBaseUrl);
                return;
            }

            encodeSearchState(query, category, function (token) {
                goToUrl(token ? (listBaseUrl + "/" + token) : listBaseUrl);
            }, function () {
                goToUrl(listBaseUrl);
            });
        });
    }

    function filterCards(query) {
        var q = query.toLowerCase();
        cards.forEach(function (card) {
            var title = (card.dataset.title || "").toLowerCase();
            var seller = (card.dataset.seller || "").toLowerCase();
            var description = (card.dataset.description || "").toLowerCase();
            var match = !q
                || title.indexOf(q) !== -1
                || seller.indexOf(q) !== -1
                || description.indexOf(q) !== -1;
            card.style.display = match ? "" : "none";
        });
    }

    function showNoItemsMessage() {
        if (!noItemsMsg) return;
        var q = input.value.trim();
        if (!q) {
            noItemsMsg.style.display = "none";
            return;
        }
        var visible = 0;
        cards.forEach(function (card) {
            if (card.style.display !== "none") visible++;
        });
        noItemsMsg.style.display = visible === 0 ? "" : "none";
    }

    function withSearchState(baseUrl, token) {
        var parts = baseUrl.replace(/\/$/, "").split("/");
        var idx = parts.indexOf("index");
        if (idx === -1 || parts.length < idx + 2) {
            return baseUrl;
        }
        var itemId = parts[idx + 1];
        // open_item/index/{id}/{search_state}
        return parts.slice(0, idx + 2).concat([token]).join("/");
    }

    function getCategoryValue() {
        if (!categorySelect) return 0;
        var value = parseInt(categorySelect.value, 10);
        return Number.isNaN(value) ? 0 : value;
    }

    function encodeSearchState(query, category, onSuccess, onError) {
        fetch(encodeUrl, {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
            body: "from=" + encodeURIComponent(listOrigin)
                + "&q=" + encodeURIComponent(query)
                + "&category=" + encodeURIComponent(String(category))
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var token = data.search_state || "";
                onSuccess(token);
            })
            .catch(function () {
                onError();
            });
    }

    function goToUrl(url) {
        var form = document.createElement("form");
        form.method = "GET";
        form.action = url;
        document.body.appendChild(form);
        form.submit();
    }
});
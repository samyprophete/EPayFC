document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search-input");
    if (!searchInput) return;

    const base = document.querySelector("base");
    if (!base) return;
    const baseHref = base.getAttribute("href");
    const controller = location.pathname.includes("my_items") ? "my_items" : "browser";
    const listBase = baseHref + controller + "/index";

    const path = location.pathname;
    const rePath = new RegExp("/" + controller + "/index(/([^/]+))?/?$");
    const pathMatch = path.match(rePath);
    const pathToken = pathMatch && pathMatch[2] ? pathMatch[2] : null;

    /* Pas de jeton (base64) dans l'URL de la page liste pendant la saisie.
       Le jeton n'est que dans les liens / onclick vers open_item. */
    function setListPageUrlToBase() {
        history.replaceState(null, "", listBase);
    }

    function applyMatchesToCards(data) {
        const cartes = document.querySelectorAll(".item-card");
        for (let i = 0; i < cartes.length; i++) {
            const id = parseInt(cartes[i].getAttribute("data-id"), 10);
            if (data.matches.indexOf(id) === -1) {
                cartes[i].style.display = "none";
            } else {
                cartes[i].style.display = "";
            }
        }
    }

    function setOpenItemLinksOnCards(encodedState) {
        if (!encodedState) return;
        const cartes = document.querySelectorAll(".item-card");
        for (let j = 0; j < cartes.length; j++) {
            const id = cartes[j].getAttribute("data-id");
            const a = cartes[j].querySelector("a[href^='open_item/']");
            if (a) {
                a.href = "open_item/index/" + id + "/" + encodedState + "/0";
            } else {
                cartes[j].setAttribute("onclick", "location.href='open_item/index/" + id + "/" + encodedState + "/0'");
            }
        }
    }

    function replaceMyItemsBelowSearch(nPage) {
        const curPage = document.querySelector(".my-items-page");
        if (!curPage || !nPage) return false;
        const sbar = curPage.querySelector(".search-bar");
        const sbarF = nPage.querySelector(".search-bar");
        if (!sbar || !sbarF) return false;
        let n = sbar.nextElementSibling;
        while (n) {
            const t = n.nextElementSibling;
            n.remove();
            n = t;
        }
        n = sbarF.nextElementSibling;
        while (n) {
            const t = n.nextElementSibling;
            curPage.appendChild(n);
            n = t;
        }
        return true;
    }

    function replaceListPageFromHtml(html) {
        const doc = (new DOMParser()).parseFromString(html, "text/html");
        if (replaceMyItemsBelowSearch(doc)) {
            return;
        }
        const newMy = doc.querySelector(".my-items-page");
        const curMy = document.querySelector(".my-items-page");
        if (newMy && curMy) {
            curMy.innerHTML = newMy.innerHTML;
            return;
        }
        [".participating .item-list", ".available .item-list", ".items-section .item-list"].forEach(
            function (s) {
                const listeActuelle = document.querySelector(s);
                const nouvelleListe = doc.querySelector(s);
                if (listeActuelle && nouvelleListe) {
                    listeActuelle.innerHTML = nouvelleListe.innerHTML;
                }
            }
        );
    }

    (function initFromServerState() {
        const q = searchInput.value.trim();
        if (q !== "" && !pathToken) {
            fetch(listBase + "?ajax=1&query=" + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    applyMatchesToCards(data);
                    setOpenItemLinksOnCards(data.encoded_state);
                });
            return;
        }
        if (q === "" && pathToken) {
            setListPageUrlToBase();
            fetch(listBase)
                .then(function (r) { return r.text(); })
                .then(replaceListPageFromHtml);
        }
    })();

    searchInput.addEventListener("input", function () {
        const query = searchInput.value.trim();
        setListPageUrlToBase();

        if (query === "") {
            fetch(listBase)
                .then(function (r) { return r.text(); })
                .then(replaceListPageFromHtml);
            return;
        }

        const ajaxUrl = listBase + "?ajax=1&query=" + encodeURIComponent(query);
        fetch(ajaxUrl)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                const cartes = document.querySelectorAll(".item-card");
                for (let k = 0; k < cartes.length; k++) {
                    const id = parseInt(cartes[k].getAttribute("data-id"), 10);
                    if (data.matches.indexOf(id) === -1) {
                        cartes[k].style.display = "none";
                    } else {
                        cartes[k].style.display = "";
                        const lien = cartes[k].querySelector("a");
                        if (lien) {
                            lien.href = "open_item/index/" + id + "/" + data.encoded_state + "/0";
                        } else {
                            cartes[k].setAttribute("onclick", "location.href='open_item/index/" + id + "/" + data.encoded_state + "/0'");
                        }
                    }
                }
            });
    });

    addEventListener("pageshow", function () {
        const q = searchInput.value.trim();
        if (q === "") return;
        fetch(listBase + "?ajax=1&query=" + encodeURIComponent(q))
            .then(function (r) { return r.json(); })
            .then(applyMatchesToCards);
    });
});

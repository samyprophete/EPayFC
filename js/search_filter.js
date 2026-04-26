document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search-input");
    if (!searchInput) return;

    const baseHref = document.querySelector("base").getAttribute("href");
    const controller = window.location.pathname.includes("my_items") ? "my_items" : "browser";
    const url = baseHref + controller + "/index";

    searchInput.addEventListener("input", function () {
        const query = searchInput.value.trim();
        
        // 1. Si la barre est vide, il nous manque des cartes (car le PHP les a filtrées).
        // On fait une requête AJAX pour récupérer le HTML de la page avec toutes les cartes,
        // puis on remplace le HTML sans recharger la page (donc pas de perte du curseur !).
        if (query === "") {
            history.replaceState(null, "", url);
            
            fetch(url)
                .then(res => res.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, "text/html");
                    
                    // On cherche les listes d'items dans la nouvelle page et on les injecte
                    const selectors = [".participating .item-list", ".available .item-list", ".items-section .items-grid"];
                    selectors.forEach(selector => {
                        const listeActuelle = document.querySelector(selector);
                        const nouvelleListe = doc.querySelector(selector);
                        if (listeActuelle && nouvelleListe) {
                            listeActuelle.innerHTML = nouvelleListe.innerHTML;
                        }
                    });
                });
            return;
        }

        // 2. Si on tape du texte, on utilise l'AJAX classique (JSON) pour filtrer
        const ajaxUrl = url + "?ajax=1&query=" + encodeURIComponent(query);
        
        fetch(ajaxUrl)
            .then(res => res.json())
            .then(data => {
                history.replaceState(null, "", url + "/" + data.encoded_state);

                const cartes = document.querySelectorAll(".item-card");
                for (let i = 0; i < cartes.length; i++) {
                    const id = parseInt(cartes[i].getAttribute("data-id"));
                    
                    if (data.matches.includes(id)) {
                        cartes[i].style.display = "";
                        const lien = cartes[i].querySelector("a");
                        if (lien) lien.href = "open_item/index/" + id + "/0/" + data.encoded_state;
                    } else {
                        cartes[i].style.display = "none";
                    }
                }
            });
    });

    // En cas de retour avec le bouton Back (ou chargement de page)
    // Comme le PHP filtre à la source, il n'y a pas de clignotement.
    // On relance juste l'AJAX discrètement en arrière-plan pour synchroniser.
    window.addEventListener("pageshow", function (event) {
        if (searchInput.value.trim() !== "") {
            const query = searchInput.value.trim();
            const ajaxUrl = url + "?ajax=1&query=" + encodeURIComponent(query);
            fetch(ajaxUrl)
                .then(res => res.json())
                .then(data => {
                    const cartes = document.querySelectorAll(".item-card");
                    for (let i = 0; i < cartes.length; i++) {
                        const id = parseInt(cartes[i].getAttribute("data-id"));
                        if (!data.matches.includes(id)) {
                            cartes[i].style.display = "none";
                        }
                    }
                });
        }
    });
});
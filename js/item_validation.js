$(function () {

    const BASE    = window.APP_BASE || '/prwb_2526_c04/';
    const form    = $('#item-form');
    const saveBtn = $('.save-btn');
    const itemId  = form.data('item-id') || null;

    let config     = {};
    let touched    = {};
    let errors     = {};
    let titleTimer = null;

    // ── 1. Récupération des constantes de validation ───────────────────────
    $.getJSON(BASE + 'config/validation_service', function (data) {
        config = data;
    });

    // ── 2. Références aux champs ───────────────────────────────────────────
    const $title       = $('input[name="title"]');
    const $desc        = $('textarea[name="description"]');
    const $duration    = $('input[name="duration_days"]');
    const $startingBid = $('input[name="starting_bid"]');
    const $buyNow      = $('input[name="buy_now_price"]');
    const $salePrice   = $('input[name="sale_price"]');

    // ── 3. Helpers UI ──────────────────────────────────────────────────────
    function setError(key, msg, $input) {
        errors[key] = msg;
        if (!$input) { updateBtn(); return; }

        $input.removeClass('is-valid').addClass('is-invalid');

        let $fb = $input.siblings('.js-feedback');
        if (!$fb.length) {
            $fb = $('<div class="js-feedback"></div>');
            $input.after($fb);
        }

        if (key === 'title' || key === 'description' || key === 'duration_days') {
            $fb.html('• ' + msg);
        } else {
            if ($fb.text().indexOf(msg) === -1) {
                let currentHtml = $fb.html();
                $fb.html(currentHtml + (currentHtml ? '<br>' : '') + '• ' + msg);
            }
        }

        updateBtn();
    }

    function setValid(key, $input) {
        delete errors[key];

        if (!$input) { updateBtn(); return; }

        $input.removeClass('is-invalid').addClass('is-valid');
        let $fb = $input.siblings('.js-feedback');
        if ($fb.length) $fb.html('');

        updateBtn();
    }

    function resetField($input) {
        $input.removeClass('is-invalid is-valid');
        $input.siblings('.js-feedback').html('');
    }

    function updateBtn() {
        saveBtn.prop('disabled', Object.keys(errors).length > 0);
    }

    // ── 4. Validations synchrones ──────────────────────────────────────────
    function validateTitle() {
        if (!touched.title) return;

        const val = $title.val().trim();
        const min = config.title_min_length || 3;
        const max = config.title_max_length || 255;

        if (!val) {
            setError('title', 'Le titre est obligatoire.', $title);
        } else if (val.length < min) {
            setError('title', `Le titre doit faire au moins ${min} caractères.`, $title);
        } else if (val.length > max) {
            setError('title', `Le titre ne peut pas dépasser ${max} caractères.`, $title);
        } else {
            checkTitleAsync(val);
        }
    }

    function validateDescription() {
        if (!touched.description) return;

        const val = $desc.val().trim();
        const min = config.description_min_length || 3;

        if (val && val.length < min) {
            setError('description', `La description doit faire au moins ${min} caractères.`, $desc);
        } else {
            setValid('description', $desc);
        }
    }

    function validateDuration() {
        if (!touched.duration_days) return;

        const val = parseInt($duration.val(), 10);
        const min = config.duration_min_days || 1;
        const max = config.duration_max_days || 365;

        if (isNaN(val) || val < min || val > max) {
            setError('duration_days', `La durée doit être entre ${min} et ${max} jours.`, $duration);
        } else {
            setValid('duration_days', $duration);
        }
    }

    function validatePricing() {
        const sb = $startingBid.val().trim();
        const bn = $buyNow.val().trim();
        const sp = $salePrice.val().trim();

        ['starting_bid', 'buy_now_price', 'sale_price'].forEach(k => delete errors[k]);
        [$startingBid, $buyNow, $salePrice].forEach($i => resetField($i));

        const hasAuction    = (sb !== '' || bn !== '');
        const hasDirectSale = (sp !== '');

        // 1. CONFLIT : les deux options remplies
        if (hasAuction && hasDirectSale) {
            if (sb !== '') setError('starting_bid', 'Cannot create both auction and direct sale.', $startingBid);
            if (bn !== '') setError('buy_now_price', 'Choose only one sale type.', $buyNow);
            if (sp !== '') setError('sale_price',    'Cannot create both auction and direct sale.', $salePrice);
            updateBtn();
            return;
        }

        // 2. OPTION 1 : enchère
        if (hasAuction) {
            if (sb === '') {
                if (touched.starting_bid || touched.buy_now_price) {
                    setError('starting_bid', 'Starting bid is required for an auction.', $startingBid);
                }
            } else {
                const sbVal = parseFloat(sb);
                if (isNaN(sbVal) || sbVal <= 0) {
                    setError('starting_bid', 'Starting bid must be a positive number.', $startingBid);
                } else {
                    setValid('starting_bid', $startingBid);

                    if (bn !== '') {
                        const bnVal = parseFloat(bn);
                        if (isNaN(bnVal) || bnVal <= sbVal) {
                            setError('buy_now_price', 'Instant purchase price must be greater than starting bid.', $buyNow);
                        } else {
                            setValid('buy_now_price', $buyNow);
                        }
                    }
                }
            }
        }
        // 3. OPTION 2 : vente directe
        else if (hasDirectSale) {
            const spVal = parseFloat(sp);
            if (isNaN(spVal) || spVal <= 0) {
                setError('sale_price', 'Sale price must be a positive number.', $salePrice);
            } else {
                setValid('sale_price', $salePrice);
            }
        }
        // 4. AUCUNE OPTION
        else {
            if (touched.sale_price || touched.starting_bid) {
                setError('starting_bid', 'Please choose an option.', $startingBid);
                setError('sale_price',   'Please choose an option.', $salePrice);
            }
        }

        updateBtn();
    }

    // ── 5. Validation asynchrone (unicité du titre) ────────────────────────
    function checkTitleAsync(title) {
        clearTimeout(titleTimer);
        titleTimer = setTimeout(function () {
            $.ajax({
                url     : BASE + 'item/check_title_service',
                method  : 'POST',
                data    : { title: title, item_id: itemId || '' },
                dataType: 'json',
                success : function (res) {
                    if (res.available) {
                        setValid('title', $title);
                    } else {
                        setError('title', 'Vous avez déjà une annonce avec ce titre.', $title);
                    }
                }
            });
        }, 400);
    }

    // ── 6. Événements ─────────────────────────────────────────────────────
    $title.on('input blur', function () {
        touched.title = true;
        validateTitle();
    });

    $desc.on('input blur', function () {
        touched.description = true;
        validateDescription();
    });

    $duration.on('input blur', function () {
        touched.duration_days = true;
        validateDuration();
    });

    $startingBid.on('input blur', function () {
        touched.starting_bid = true;
        validatePricing();
    });

    $buyNow.on('input blur', function () {
        touched.buy_now_price = true;
        validatePricing();
    });

    $salePrice.on('input blur', function () {
        touched.sale_price = true;
        validatePricing();
    });

    // ── 7. Soumission ─────────────────────────────────────────────────────
    form.on('submit', function (e) {
        touched = {
            title        : true,
            description  : true,
            duration_days: true,
            starting_bid : true,
            buy_now_price: true,
            sale_price   : true
        };

        validateTitle();
        validateDescription();
        validateDuration();
        validatePricing();

        if (Object.keys(errors).length > 0) {
            e.preventDefault();
        }
    });

    // ── 8. CHANGEMENTS NON SAUVEGARDÉS ────────────────────────────────────

    // 8.1 Snapshot des valeurs initiales au chargement
    const initialSnapshot = {};
    form.find('input, textarea, select').each(function () {
        const name = $(this).attr('name');
        if (name) initialSnapshot[name] = $(this).val();
    });

    function formHasChanges() {
        let changed = false;
        form.find('input, textarea, select').each(function () {
            const name = $(this).attr('name');
            if (name && name in initialSnapshot && $(this).val() !== initialSnapshot[name]) {
                changed = true;
                return false; // break
            }
        });
        return changed;
    }

    let targetUrl = '';

    // 8.2 Intercepter les clics sur les liens internes
    $('a').on('click', function (e) {
        if (formHasChanges()) {
            e.preventDefault();
            targetUrl = $(this).attr('href');
            $('#unsavedModal').fadeIn('fast');
        }
    });

    // 8.3 Boutons de la modale
    $('#cancelLeaveBtn, #closeUnsavedCross').on('click', function () {
        $('#unsavedModal').fadeOut('fast');
        targetUrl = '';
    });

    $('#confirmLeaveBtn').on('click', function () {
        window.location.href = BASE + targetUrl;
    });

    // 8.4 Bouton précédent / F5
    window.addEventListener('beforeunload', function (e) {
        if (formHasChanges()) {
            const msg = 'You have unsaved changes. Leave anyway?';
            e.returnValue = msg;
            return msg;
        }
    });

});

$(function () {
    const BASE = window.APP_BASE || '/prwb_2526_c04/';
    const $editForm = $('#edit-form');
    const isEditProfile = $editForm.length > 0;
    const form = isEditProfile ? $editForm : $('form').first();
    const submitBtn = isEditProfile
        ? $('button.header-right-btn[form="edit-form"]')
        : form.find('button[type="submit"]');

    let config = {};
    let errors = {};
    let touched = {};

    $.getJSON(BASE + 'config/validation_service', (data) => { config = data; });

    function setError(key, msg, $input) {
        delete errors[key + '_server'];
        errors[key] = msg;
        if (!$input) { updateBtn(); return; }

        $input.removeClass('is-valid').addClass('is-invalid');

        const $container = $input.closest('.form-group, .input-group');
        let $fb = $container.next('.js-feedback');

        if (!$fb.length) {
            $fb = $('<div class="js-feedback" style="color: #f87171; font-size: 0.85em; margin-top: 5px;"></div>');
            $container.after($fb);
        }
        $fb.html('• ' + msg);
        updateBtn();
    }

    function setValid(key, $input) {
        delete errors[key];
        delete errors[key + '_server'];
        if (!$input) { updateBtn(); return; }

        $input.removeClass('is-invalid').addClass('is-valid');
        $input.closest('.form-group, .input-group').next('.js-feedback').remove();
        updateBtn();
    }

    function updateBtn() {
        if (!submitBtn.length) return;

        const hasErrors = Object.keys(errors).length > 0;

        let allRequiredFilled = true;
        const requiredFields = isEditProfile
            ? ['email', 'full_name', 'pseudo']
            : ['email', 'full_name', 'pseudo', 'password', 'password_confirm'];

        requiredFields.forEach(field => {
            const $input = form.find(`input[name="${field}"]`);
            if ($input.length > 0 && $input.val().trim() === '') {
                allRequiredFilled = false;
            }
        });

        const disabled = hasErrors || !allRequiredFilled;
        submitBtn.prop('disabled', disabled);
        submitBtn.toggleClass('is-disabled', disabled);
    }

    function initEditProfileFromServer() {
        if (!isEditProfile) return;

        form.find('.form-group').each(function () {
            const $group = $(this);
            const $input = $group.find('input[name]').first();
            const name = $input.attr('name');
            const $serverError = $group.find('.error').first();

            if (name && $serverError.length) {
                const msg = $serverError.text().replace(/^\s*•\s*/, '').trim();
                errors[name + '_server'] = msg;
                $input.addClass('is-invalid');
            }
        });
    }

    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function validateIBAN(iban) {
        if (!iban) return true;
        return /^[A-Z]{2}\d{2}[A-Z\d]{4,30}$/.test(iban.replace(/\s/g, ''));
    }

    function validatePassword(p) {
        const errs = [];
        if (p.length < (config.password_min_length || 8)) errs.push("8-16 chars");
        if (!/[A-Z]/.test(p)) errs.push("1 uppercase");
        if (!/[a-z]/.test(p)) errs.push("1 lowercase");
        if (!/\d/.test(p)) errs.push("1 digit");
        if (!/[^a-zA-Z0-9]/.test(p)) errs.push("1 special char");
        return errs;
    }

    function checkAvailability() {
        const data = {
            email: $('input[name="email"]').val().trim(),
            pseudo: $('input[name="pseudo"]').val().trim(),
            full_name: $('input[name="full_name"]').val().trim()
        };

        const url = isEditProfile ? 'edit_profile/check_availability_service' : 'signup/check_availability_service';

        $.post(BASE + url, data, function (res) {
            if (!res.full_name_available) {
                setError('full_name_ajax', 'Full name is already used.', $('input[name="full_name"]'));
            } else {
                delete errors['full_name_ajax'];
                if (!errors['full_name']) setValid('full_name', $('input[name="full_name"]'));
            }

            if (!res.pseudo_available) {
                setError('pseudo_ajax', 'Pseudo is already used.', $('input[name="pseudo"]'));
            } else {
                delete errors['pseudo_ajax'];
                if (!errors['pseudo']) setValid('pseudo', $('input[name="pseudo"]'));
            }

            if (!res.email_available) {
                setError('email_ajax', 'Email address is already used.', $('input[name="email"]'));
            } else {
                delete errors['email_ajax'];
                if (!errors['email']) setValid('email', $('input[name="email"]'));
            }

            updateBtn();
        });
    }

    form.find('input').on('input blur', function () {
        const $el = $(this);
        const name = $el.attr('name');
        const val = $el.val().trim();
        touched[name] = true;

        if (name === 'email') {
            if (!val) setError(name, "Required", $el);
            else if (!validateEmail(val)) setError(name, "Invalid format", $el);
            else { setValid(name, $el); checkAvailability(); }
        }

        if (name === 'password' && !isEditProfile) {
            const pErrors = validatePassword(val);
            if (pErrors.length > 0) setError(name, "Strength: " + pErrors.join(', '), $el);
            else setValid(name, $el);
        }

        if (name === 'password_confirm' && !isEditProfile) {
            if (val !== $('input[name="password"]').val()) setError(name, "Passwords don't match", $el);
            else setValid(name, $el);
        }

        if (name === 'iban' && isEditProfile) {
            if (val && !validateIBAN(val)) setError(name, "Invalid IBAN format", $el);
            else setValid(name, $el);
        }

        if (name === 'pseudo' || name === 'full_name') {
            if (!val) setError(name, "Required", $el);
            else { setValid(name, $el); checkAvailability(); }
        }
    });

    initEditProfileFromServer();
    updateBtn();
});

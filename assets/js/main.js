// ============ Smart E-Commerce — Client-side jQuery logic ============

$(function () {

    // ---------- Add to cart (AJAX) ----------
    $(document).on('click', '.add-to-cart-btn', function (e) {
        e.preventDefault();
        const $btn = $(this);
        const productId = $btn.data('id');
        const qty = $('#qty-input').val() || 1;

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Adding...');

        $.post(BASE_URL + '/cart.php', { action: 'add', product_id: productId, quantity: qty }, function (res) {
            if (res.success) {
                $('#cart-count').text(res.count);
                showToast('Added to cart!', 'success');
            } else {
                showToast(res.message || 'Could not add to cart', 'danger');
                if (res.redirect) setTimeout(() => location.href = res.redirect, 800);
            }
        }, 'json').fail(function () {
            showToast('Network error', 'danger');
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="bi bi-cart-plus"></i> Add to Cart');
        });
    });

    // ---------- Update cart quantity ----------
    $(document).on('change', '.cart-qty', function () {
        const $row = $(this).closest('tr');
        const id = $row.data('cart-id');
        const qty = parseInt($(this).val()) || 1;
        $.post(BASE_URL + '/cart.php', { action: 'update', cart_id: id, quantity: qty }, function (res) {
            if (res.success) {
                $row.find('.line-total').text('$' + res.line_total);
                $('#cart-subtotal').text('$' + res.subtotal);
                $('#cart-count').text(res.count);
            }
        }, 'json');
    });

    // ---------- Remove cart item ----------
    $(document).on('click', '.remove-cart-btn', function () {
        if (!confirm('Remove this item from cart?')) return;
        const $row = $(this).closest('tr');
        const id = $row.data('cart-id');
        $.post(BASE_URL + '/cart.php', { action: 'remove', cart_id: id }, function (res) {
            if (res.success) {
                $row.fadeOut(200, function () {
                    $(this).remove();
                    $('#cart-subtotal').text('$' + res.subtotal);
                    $('#cart-count').text(res.count);
                    if (res.count === 0) location.reload();
                });
            }
        }, 'json');
    });

    // ---------- Form validation (login / register / checkout) ----------
    $('form[data-validate]').on('submit', function (e) {
        let ok = true;
        $(this).find('[required]').each(function () {
            if (!$.trim($(this).val())) {
                $(this).addClass('is-invalid');
                ok = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        const pwd = $(this).find('input[name=password]');
        if (pwd.length && pwd.val().length > 0 && pwd.val().length < 6) {
            pwd.addClass('is-invalid');
            showToast('Password must be at least 6 characters', 'danger');
            ok = false;
        }
        const cpwd = $(this).find('input[name=confirm_password]');
        if (cpwd.length && cpwd.val() !== pwd.val()) {
            cpwd.addClass('is-invalid');
            showToast('Passwords do not match', 'danger');
            ok = false;
        }
        if (!ok) e.preventDefault();
    });

    // ---------- Live search highlighting on product list ----------
    $('#client-filter').on('keyup', function () {
        const term = $(this).val().toLowerCase();
        $('.product-card-wrapper').each(function () {
            const name = $(this).data('name').toLowerCase();
            $(this).toggle(name.indexOf(term) !== -1);
        });
    });

    // ---------- Confirm delete (admin) ----------
    $('.confirm-delete').on('click', function (e) {
        if (!confirm('Are you sure you want to delete this?')) e.preventDefault();
    });
});

// ---------- Toast helper ----------
function showToast(msg, type) {
    type = type || 'info';
    const id = 'toast-' + Date.now();
    const html = `<div id="${id}" class="toast align-items-center text-bg-${type} border-0 show position-fixed"
                     style="top:80px; right:20px; z-index:1080;" role="alert">
        <div class="d-flex">
            <div class="toast-body">${msg}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div></div>`;
    $('body').append(html);
    setTimeout(() => $('#' + id).fadeOut(300, function () { $(this).remove(); }), 2500);
}

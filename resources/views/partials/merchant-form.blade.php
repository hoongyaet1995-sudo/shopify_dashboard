<form id="storeConnectForm">
    @csrf <div class="mb-3">
        <label class="form-label fw-bold">Store Name</label>
        <input type="text" name="marketplace_user_name" class="form-control" placeholder="e.g. JOSHUAS Official" required>
    </div>
    <div class="mb-3">
        <label class="form-label fw-bold">Shopify Store Name</label>
        <div class="input-group">
            <input type="text" name="marketplace_shop_name" class="form-control" placeholder="my-shop-name" required>
            <span class="input-group-text">.myshopify.com</span>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary px-4">Connect Store</button>
    </div>
</form>
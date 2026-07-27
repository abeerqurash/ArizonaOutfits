@extends('admin.layouts.app')

@section('title', 'Manage Orders')

@section('page-heading', 'Orders')

@section('content')

<div class="admin-orders-page">

    <div class="admin-page-header">

        <div>
            <span class="admin-page-eyebrow">
                Order management
            </span>

            <h2>Manage Orders</h2>

            <p>
                Search, filter and manage customer orders.
            </p>
        </div>

        <div class="admin-page-actions">

            <a
                href="{{ route('admin.dashboard') }}"
                class="admin-button admin-button-secondary"
            >
                <i class="fa-solid fa-arrow-left"></i>
                Dashboard
            </a>

        </div>

    </div>

    @if (session('success'))
        <div class="admin-alert admin-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="admin-alert admin-alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div
        id="ordersActionMessage"
        class="admin-alert"
        hidden
    ></div>

    <section class="admin-panel admin-orders-panel">

        <div class="admin-panel-header admin-orders-panel-header">

            <div>
                <span class="admin-panel-eyebrow">
                    Store orders
                </span>

                <h3>All Orders</h3>

                <p
                    id="ordersResultsSummary"
                    class="admin-orders-results-summary"
                >
                    @if ($orders->total() > 0)
                        Showing
                        {{ number_format($orders->firstItem()) }}–{{ number_format($orders->lastItem()) }}
                        of {{ number_format($orders->total()) }} orders
                    @else
                        No orders found
                    @endif
                </p>
            </div>

        </div>

        @include('admin.orders.partials.filters', [
            'filters' => $filters,
        ])

        <div
            id="ordersAjaxError"
            class="admin-alert admin-alert-error admin-orders-error"
            hidden
        ></div>

        <div
            id="ordersResultsContainer"
            class="admin-orders-results-container"
            aria-live="polite"
        >

            <div id="ordersTableContainer">
                @include('admin.orders.partials.table', [
                    'orders' => $orders,
                ])
            </div>

            <div id="ordersPaginationContainer">
                @include('admin.orders.partials.pagination', [
                    'orders' => $orders,
                ])
            </div>

            <div
                id="ordersLoadingOverlay"
                class="admin-orders-loading-overlay"
                hidden
            >
                <span class="admin-orders-spinner"></span>
                <span>Loading orders...</span>
            </div>

        </div>

    </section>

    <div
        id="ordersBulkToolbar"
        class="admin-orders-bulk-toolbar"
        hidden
    >

        <div class="admin-orders-bulk-summary">

            <span class="admin-orders-bulk-icon">
                <i class="fa-solid fa-check"></i>
            </span>

            <strong id="ordersSelectedCount">
                0 orders selected
            </strong>

            <button
                type="button"
                id="ordersClearSelection"
                class="admin-orders-clear-selection"
            >
                Clear
            </button>

        </div>

        <div class="admin-orders-bulk-controls">

            <select
                id="ordersBulkAction"
                aria-label="Choose bulk action"
            >
                <option value="">Choose an action</option>

                <optgroup label="Order status">
                    <option value="order_status:pending">
                        Mark order pending
                    </option>

                    <option value="order_status:processing">
                        Mark order processing
                    </option>

                    <option value="order_status:shipped">
                        Mark order shipped
                    </option>

                    <option value="order_status:completed">
                        Mark order completed
                    </option>

                    <option value="order_status:delivered">
                        Mark order delivered
                    </option>

                    <option value="order_status:cancelled">
                        Mark order cancelled
                    </option>

                    <option value="order_status:refunded">
                        Mark order refunded
                    </option>
                </optgroup>

                <optgroup label="Payment status">
                    <option value="payment_status:pending">
                        Payment pending
                    </option>

                    <option value="payment_status:paid">
                        Payment paid
                    </option>

                    <option value="payment_status:completed">
                        Payment completed
                    </option>

                    <option value="payment_status:succeeded">
                        Payment succeeded
                    </option>

                    <option value="payment_status:failed">
                        Payment failed
                    </option>

                    <option value="payment_status:refunded">
                        Payment refunded
                    </option>
                </optgroup>

                <optgroup label="Other actions">
                    <option value="export">
                        Export selected CSV
                    </option>

                    <option value="delete">
                        Delete selected orders
                    </option>
                </optgroup>
            </select>

            <button
                type="button"
                id="ordersApplyBulkAction"
            >
                Apply
            </button>

        </div>

    </div>

</div>

<form
    id="ordersExportForm"
    action="{{ route('admin.orders.bulk-export') }}"
    method="POST"
    hidden
>
    @csrf
</form>

<style>
    .admin-orders-page {
        position: relative;
        padding-bottom: 100px;
    }

    .admin-orders-results-summary {
        margin: 6px 0 0;
        color: #64748b;
        font-size: 14px;
    }

    .admin-orders-filter-form {
        display: grid;
        grid-template-columns:
            minmax(240px, 2fr)
            repeat(4, minmax(150px, 1fr))
            auto;
        gap: 12px;
        padding: 20px 24px;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .admin-orders-search-group {
        position: relative;
    }

    .admin-orders-search-group > i {
        position: absolute;
        top: 50%;
        left: 14px;
        color: #94a3b8;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .admin-orders-search-group input {
        padding-left: 40px !important;
    }

    .admin-orders-filter-form input,
    .admin-orders-filter-form select {
        width: 100%;
        min-height: 44px;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 9px;
        background: #ffffff;
        color: #0f172a;
        font: inherit;
    }

    .admin-orders-custom-date-fields {
        display: none;
        grid-column: 1 / -1;
        grid-template-columns: repeat(2, minmax(180px, 1fr));
        gap: 12px;
        padding: 14px;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        background: #ffffff;
    }

    .admin-orders-custom-date-fields.is-visible {
        display: grid;
    }

    .admin-orders-date-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .admin-orders-date-field label {
        color: #334155;
        font-size: 13px;
        font-weight: 600;
    }

    .admin-orders-filter-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .admin-orders-filter-actions button,
    .admin-orders-filter-actions a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 44px;
        padding: 10px 15px;
        border-radius: 9px;
        font: inherit;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        cursor: pointer;
    }

    .admin-orders-filter-actions button {
        border: 1px solid #0f172a;
        background: #0f172a;
        color: #ffffff;
    }

    .admin-orders-filter-actions a {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
    }

    .admin-orders-results-container {
        position: relative;
        min-height: 220px;
    }

    .admin-orders-table-wrapper {
        overflow-x: auto;
    }

    .admin-orders-checkbox-column {
        width: 42px;
        min-width: 42px;
        text-align: center;
    }

    .admin-order-checkbox {
        width: 17px;
        height: 17px;
        margin: 0;
        cursor: pointer;
        accent-color: #0f172a;
    }

    .admin-orders-table tbody tr.is-selected {
        background: #f0f7ff;
    }

    .admin-orders-customer {
        min-width: 210px;
    }

    .admin-orders-customer strong,
    .admin-orders-customer small {
        display: block;
    }

    .admin-orders-customer small {
        margin-top: 4px;
        color: #64748b;
    }

    .admin-orders-number {
        display: inline-flex;
        flex-direction: column;
        gap: 4px;
        min-width: 125px;
    }

    .admin-orders-number small {
        color: #94a3b8;
    }

    .admin-orders-money {
        white-space: nowrap;
        font-weight: 600;
    }

    .admin-orders-tracking {
        min-width: 140px;
    }

    .admin-orders-date {
        min-width: 120px;
        white-space: nowrap;
    }

    .admin-orders-view-button {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
    }

    .admin-orders-empty {
        padding: 48px 20px !important;
        text-align: center;
    }

    .admin-orders-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .admin-orders-empty-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 56px;
        height: 56px;
        margin-bottom: 14px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #64748b;
        font-size: 22px;
    }

    .admin-orders-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        padding: 18px 24px;
        border-top: 1px solid #e2e8f0;
    }

    .admin-orders-loading-overlay {
        position: absolute;
        inset: 0;
        z-index: 20;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        background: rgba(255, 255, 255, 0.85);
    }

    .admin-orders-loading-overlay[hidden],
    .admin-orders-error[hidden],
    #ordersActionMessage[hidden] {
        display: none;
    }

    .admin-orders-spinner {
        width: 38px;
        height: 38px;
        border: 4px solid #e2e8f0;
        border-top-color: #0f172a;
        border-radius: 50%;
        animation: adminOrdersSpin 0.75s linear infinite;
    }

    .admin-orders-bulk-toolbar {
        position: fixed;
        right: 30px;
        bottom: 24px;
        left: var(--admin-sidebar-width, 280px);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        max-width: 1050px;
        margin: 0 auto;
        padding: 14px 18px;
        border: 1px solid #334155;
        border-radius: 14px;
        background: #0f172a;
        color: #ffffff;
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.3);
    }

    .admin-orders-bulk-toolbar[hidden] {
        display: none;
    }

    .admin-orders-bulk-summary,
    .admin-orders-bulk-controls {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .admin-orders-bulk-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #ffffff;
        color: #0f172a;
    }

    .admin-orders-clear-selection {
        border: 0;
        background: transparent;
        color: #cbd5e1;
        text-decoration: underline;
        cursor: pointer;
    }

    .admin-orders-bulk-controls select,
    .admin-orders-bulk-controls button {
        min-height: 40px;
        border-radius: 8px;
        font: inherit;
    }

    .admin-orders-bulk-controls select {
        min-width: 230px;
        padding: 8px 10px;
        border: 1px solid #475569;
        background: #ffffff;
        color: #0f172a;
    }

    .admin-orders-bulk-controls button {
        padding: 8px 17px;
        border: 1px solid #ffffff;
        background: #ffffff;
        color: #0f172a;
        font-weight: 700;
        cursor: pointer;
    }

    @keyframes adminOrdersSpin {
        to {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 1250px) {
        .admin-orders-filter-form {
            grid-template-columns:
                repeat(3, minmax(180px, 1fr));
        }

        .admin-orders-search-group {
            grid-column: span 2;
        }
    }

    @media (max-width: 900px) {
        .admin-orders-bulk-toolbar {
            right: 15px;
            bottom: 15px;
            left: 15px;
            align-items: stretch;
            flex-direction: column;
        }

        .admin-orders-bulk-summary,
        .admin-orders-bulk-controls {
            justify-content: space-between;
        }
    }

    @media (max-width: 700px) {
        .admin-orders-filter-form {
            grid-template-columns: 1fr;
            padding: 16px;
        }

        .admin-orders-search-group {
            grid-column: auto;
        }

        .admin-orders-custom-date-fields {
            grid-template-columns: 1fr;
        }

        .admin-orders-filter-actions,
        .admin-orders-bulk-controls {
            align-items: stretch;
            flex-direction: column;
        }

        .admin-orders-bulk-controls select,
        .admin-orders-bulk-controls button {
            width: 100%;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]'
    )?.getAttribute('content');

    const filterForm = document.getElementById(
        'ordersFilterForm'
    );

    const searchInput = document.getElementById(
        'ordersSearchInput'
    );

    const dateRangeSelect = document.getElementById(
        'ordersDateRange'
    );

    const customDateFields = document.getElementById(
        'ordersCustomDateFields'
    );

    const dateFromInput = document.getElementById(
        'ordersDateFrom'
    );

    const dateToInput = document.getElementById(
        'ordersDateTo'
    );

    const tableContainer = document.getElementById(
        'ordersTableContainer'
    );

    const paginationContainer = document.getElementById(
        'ordersPaginationContainer'
    );

    const resultsSummary = document.getElementById(
        'ordersResultsSummary'
    );

    const loadingOverlay = document.getElementById(
        'ordersLoadingOverlay'
    );

    const errorContainer = document.getElementById(
        'ordersAjaxError'
    );

    const messageContainer = document.getElementById(
        'ordersActionMessage'
    );

    const bulkToolbar = document.getElementById(
        'ordersBulkToolbar'
    );

    const selectedCount = document.getElementById(
        'ordersSelectedCount'
    );

    const bulkAction = document.getElementById(
        'ordersBulkAction'
    );

    const applyBulkButton = document.getElementById(
        'ordersApplyBulkAction'
    );

    const clearSelectionButton = document.getElementById(
        'ordersClearSelection'
    );

    const exportForm = document.getElementById(
        'ordersExportForm'
    );

    const resetButton = document.getElementById(
        'ordersResetFilters'
    );

    if (!filterForm || !tableContainer) {
        return;
    }

    let searchTimer = null;
    let activeRequest = null;
    let selectedOrderIds = new Set();

    function getRowCheckboxes() {
        return Array.from(
            tableContainer.querySelectorAll(
                '.js-order-checkbox'
            )
        );
    }

    function getSelectAllCheckbox() {
        return tableContainer.querySelector(
            '#ordersSelectAll'
        );
    }

    function updateSelectionInterface() {
        const checkboxes = getRowCheckboxes();
        const selectAll = getSelectAllCheckbox();

        checkboxes.forEach(function (checkbox) {
            const orderId = String(checkbox.value);
            const selected = selectedOrderIds.has(orderId);

            checkbox.checked = selected;

            checkbox.closest('tr')?.classList.toggle(
                'is-selected',
                selected
            );
        });

        const selectedOnPage = checkboxes.filter(
            function (checkbox) {
                return checkbox.checked;
            }
        ).length;

        if (selectAll) {
            selectAll.checked =
                checkboxes.length > 0 &&
                selectedOnPage === checkboxes.length;

            selectAll.indeterminate =
                selectedOnPage > 0 &&
                selectedOnPage < checkboxes.length;
        }

        const count = selectedOrderIds.size;

        if (selectedCount) {
            selectedCount.textContent =
                count === 1
                    ? '1 order selected'
                    : count + ' orders selected';
        }

        if (bulkToolbar) {
            bulkToolbar.hidden = count === 0;
        }
    }

    function clearSelection() {
        selectedOrderIds.clear();

        if (bulkAction) {
            bulkAction.value = '';
        }

        updateSelectionInterface();
    }

    function showLoader() {
        if (loadingOverlay) {
            loadingOverlay.hidden = false;
        }
    }

    function hideLoader() {
        if (loadingOverlay) {
            loadingOverlay.hidden = true;
        }
    }

    function hideError() {
        if (errorContainer) {
            errorContainer.hidden = true;
            errorContainer.textContent = '';
        }
    }

    function showError(message) {
        if (errorContainer) {
            errorContainer.textContent = message;
            errorContainer.hidden = false;
        }
    }

    function showMessage(message, type = 'success') {
        if (!messageContainer) {
            return;
        }

        messageContainer.className =
            'admin-alert admin-alert-' + type;

        messageContainer.textContent = message;
        messageContainer.hidden = false;

        window.setTimeout(function () {
            messageContainer.hidden = true;
        }, 5000);
    }

    function isCustomDateRange() {
        return dateRangeSelect?.value === 'custom';
    }

    function customDatesAreComplete() {
        return Boolean(
            dateFromInput?.value &&
            dateToInput?.value
        );
    }

    function toggleCustomDateFields() {
        const isCustom = isCustomDateRange();

        customDateFields?.classList.toggle(
            'is-visible',
            isCustom
        );

        if (dateFromInput) {
            dateFromInput.disabled = !isCustom;
        }

        if (dateToInput) {
            dateToInput.disabled = !isCustom;
        }
    }

    function validateCustomDates() {
        if (!isCustomDateRange()) {
            return true;
        }

        if (!customDatesAreComplete()) {
            showError(
                'Please select both the from date and to date.'
            );

            return false;
        }

        if (dateFromInput.value > dateToInput.value) {
            showError(
                'The from date cannot be later than the to date.'
            );

            return false;
        }

        return true;
    }

    function buildRequestUrl(pageUrl = null) {
        const formData = new FormData(filterForm);
        const params = new URLSearchParams();

        formData.forEach(function (value, key) {
            const normalized = String(value).trim();

            if (normalized !== '') {
                params.set(key, normalized);
            }
        });

        if (pageUrl) {
            const clickedUrl = new URL(
                pageUrl,
                window.location.origin
            );

            const page = clickedUrl.searchParams.get('page');

            if (page) {
                params.set('page', page);
            }
        }

        const query = params.toString();

        return query
            ? filterForm.action + '?' + query
            : filterForm.action;
    }

    async function loadOrders(
        requestUrl,
        updateHistory = true
    ) {
        hideError();

        if (!validateCustomDates()) {
            return;
        }

        if (activeRequest) {
            activeRequest.abort();
        }

        activeRequest = new AbortController();

        showLoader();

        try {
            const response = await fetch(requestUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },

                signal: activeRequest.signal
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    getResponseError(
                        data,
                        'Unable to load orders.'
                    )
                );
            }

            tableContainer.innerHTML =
                data.table_html || '';

            paginationContainer.innerHTML =
                data.pagination_html || '';

            if (resultsSummary) {
                resultsSummary.textContent =
                    data.results_summary ||
                    'No orders found';
            }

            clearSelection();

            if (updateHistory) {
                window.history.pushState(
                    {},
                    '',
                    requestUrl
                );
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                showError(error.message);
            }
        } finally {
            hideLoader();
            activeRequest = null;
        }
    }

    function getResponseError(data, fallback) {
        const firstValidationError = Object
            .values(data?.errors || {})
            .flat()
            .find(Boolean);

        return firstValidationError ||
            data?.message ||
            fallback;
    }

    async function sendBulkRequest(url, method, body) {
        showLoader();
        hideError();

        try {
            const response = await fetch(url, {
                method: method,

                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },

                body: JSON.stringify(body)
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    getResponseError(
                        data,
                        'The bulk action could not be completed.'
                    )
                );
            }

            showMessage(data.message);

            clearSelection();

            await loadOrders(
                window.location.href,
                false
            );
        } catch (error) {
            showError(error.message);
        } finally {
            hideLoader();
        }
    }

    function exportSelectedOrders(orderIds) {
        if (!exportForm) {
            return;
        }

        exportForm
            .querySelectorAll(
                'input[name="order_ids[]"]'
            )
            .forEach(function (input) {
                input.remove();
            });

        orderIds.forEach(function (orderId) {
            const input = document.createElement('input');

            input.type = 'hidden';
            input.name = 'order_ids[]';
            input.value = orderId;

            exportForm.appendChild(input);
        });

        exportForm.submit();
    }

    tableContainer.addEventListener(
        'change',
        function (event) {
            const target = event.target;

            if (target.id === 'ordersSelectAll') {
                getRowCheckboxes().forEach(
                    function (checkbox) {
                        const orderId =
                            String(checkbox.value);

                        if (target.checked) {
                            selectedOrderIds.add(orderId);
                        } else {
                            selectedOrderIds.delete(orderId);
                        }
                    }
                );

                updateSelectionInterface();

                return;
            }

            if (
                target.classList.contains(
                    'js-order-checkbox'
                )
            ) {
                const orderId = String(target.value);

                if (target.checked) {
                    selectedOrderIds.add(orderId);
                } else {
                    selectedOrderIds.delete(orderId);
                }

                updateSelectionInterface();
            }
        }
    );

    applyBulkButton?.addEventListener(
        'click',
        function () {
            const selectedAction = bulkAction.value;
            const orderIds = Array.from(selectedOrderIds);

            if (orderIds.length === 0) {
                showError('Please select at least one order.');
                return;
            }

            if (!selectedAction) {
                showError('Please choose a bulk action.');
                return;
            }

            if (selectedAction === 'export') {
                exportSelectedOrders(orderIds);
                return;
            }

            if (selectedAction === 'delete') {
                const confirmed = window.confirm(
                    'Delete ' +
                    orderIds.length +
                    ' selected order(s)? This cannot be undone.'
                );

                if (!confirmed) {
                    return;
                }

                sendBulkRequest(
                    @json(route('admin.orders.bulk-delete')),
                    'DELETE',
                    {
                        order_ids: orderIds
                    }
                );

                return;
            }

            const parts = selectedAction.split(':');
            const action = parts[0];
            const value = parts[1];

            sendBulkRequest(
                @json(route('admin.orders.bulk-update')),
                'PATCH',
                {
                    order_ids: orderIds,
                    action: action,
                    value: value
                }
            );
        }
    );

    clearSelectionButton?.addEventListener(
        'click',
        clearSelection
    );

    filterForm.addEventListener(
        'submit',
        function (event) {
            event.preventDefault();

            loadOrders(buildRequestUrl());
        }
    );

    dateRangeSelect?.addEventListener(
        'change',
        function () {
            toggleCustomDateFields();

            if (isCustomDateRange()) {
                dateFromInput?.focus();
                return;
            }

            if (dateFromInput) {
                dateFromInput.value = '';
            }

            if (dateToInput) {
                dateToInput.value = '';
            }

            loadOrders(buildRequestUrl());
        }
    );

    filterForm
        .querySelectorAll(
            'select:not(#ordersDateRange)'
        )
        .forEach(function (select) {
            select.addEventListener(
                'change',
                function () {
                    loadOrders(buildRequestUrl());
                }
            );
        });

    function handleCustomDateChange() {
        if (
            customDatesAreComplete() &&
            validateCustomDates()
        ) {
            loadOrders(buildRequestUrl());
        }
    }

    dateFromInput?.addEventListener(
        'change',
        handleCustomDateChange
    );

    dateToInput?.addEventListener(
        'change',
        handleCustomDateChange
    );

    searchInput?.addEventListener(
        'input',
        function () {
            window.clearTimeout(searchTimer);

            searchTimer = window.setTimeout(
                function () {
                    if (
                        isCustomDateRange() &&
                        !customDatesAreComplete()
                    ) {
                        return;
                    }

                    loadOrders(buildRequestUrl());
                },
                450
            );
        }
    );

    paginationContainer.addEventListener(
        'click',
        function (event) {
            const link = event.target.closest('a[href]');

            if (!link) {
                return;
            }

            event.preventDefault();

            loadOrders(
                buildRequestUrl(link.href)
            );
        }
    );

    resetButton?.addEventListener(
        'click',
        function (event) {
            event.preventDefault();

            filterForm.reset();

            if (dateFromInput) {
                dateFromInput.value = '';
            }

            if (dateToInput) {
                dateToInput.value = '';
            }

            toggleCustomDateFields();
            clearSelection();

            loadOrders(filterForm.action);
        }
    );

    window.addEventListener(
        'popstate',
        function () {
            const currentUrl = new URL(
                window.location.href
            );

            filterForm
                .querySelectorAll(
                    'input[name], select[name]'
                )
                .forEach(function (field) {
                    field.value =
                        currentUrl.searchParams.get(
                            field.name
                        ) || '';
                });

            toggleCustomDateFields();

            loadOrders(
                currentUrl.toString(),
                false
            );
        }
    );

    document.addEventListener(
        'keydown',
        function (event) {
            if (event.key === 'Escape') {
                clearSelection();
            }
        }
    );

    toggleCustomDateFields();
    updateSelectionInterface();
});
</script>

@endsection
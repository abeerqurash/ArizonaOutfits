@push('page-styles')

<style>
    .supplier-editor-page {
        --editor-text: #111827;
        --editor-muted: #64748b;
        --editor-border: #e5e7eb;
        --editor-soft-border: #eef2f7;
        --editor-indigo: #4f46e5;
        --editor-indigo-dark: #4338ca;
        --editor-indigo-soft: #eef2ff;
        --editor-green: #15803d;
        --editor-green-soft: #ecfdf3;
        --editor-blue: #0369a1;
        --editor-blue-soft: #f0f9ff;
        --editor-orange: #c2410c;
        --editor-orange-soft: #fff7ed;
        --editor-red: #b91c1c;
        --editor-red-soft: #fef2f2;
        --editor-yellow: #a16207;
        --editor-yellow-soft: #fefce8;

        display: flex;
        flex-direction: column;
        gap: 22px;
        min-width: 0;
        color: var(--editor-text);
    }

    .supplier-editor-page *,
    .supplier-editor-page *::before,
    .supplier-editor-page *::after {
        box-sizing: border-box;
    }

    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    .supplier-editor-header {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        padding: 27px 29px;
        border: 1px solid var(--editor-border);
        border-radius: 19px;
        background:
            radial-gradient(
                circle at top right,
                rgba(79, 70, 229, 0.14),
                transparent 38%
            ),
            linear-gradient(
                135deg,
                #ffffff,
                #f8f9ff
            );
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .supplier-editor-header::after {
        content: "";
        position: absolute;
        top: -60px;
        right: -40px;
        width: 185px;
        height: 185px;
        border: 28px solid rgba(79, 70, 229, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .supplier-editor-header-content {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 17px;
        min-width: 0;
    }

    .supplier-editor-header-icon {
        width: 58px;
        height: 58px;
        flex: 0 0 58px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        background: var(--editor-indigo-soft);
        color: var(--editor-indigo);
        font-size: 22px;
    }

    .supplier-editor-eyebrow,
    .supplier-form-eyebrow {
        display: block;
        margin-bottom: 6px;
        color: var(--editor-indigo);
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .supplier-editor-header h1 {
        margin: 0;
        color: var(--editor-text);
        font-size: 29px;
        line-height: 1.2;
    }

    .supplier-editor-header p {
        max-width: 680px;
        margin: 7px 0 0;
        color: var(--editor-muted);
        font-size: 12px;
        line-height: 1.65;
    }

    .supplier-editor-title-row {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 9px;
    }

    .supplier-editor-status,
    .supplier-editor-preferred {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 8px;
        border-radius: 999px;
        font-size: 8px;
        font-weight: 800;
    }

    .supplier-editor-status.active {
        background: var(--editor-green-soft);
        color: var(--editor-green);
    }

    .supplier-editor-status.inactive {
        background: #f1f5f9;
        color: #475569;
    }

    .supplier-editor-status.blocked {
        background: var(--editor-red-soft);
        color: var(--editor-red);
    }

    .supplier-editor-preferred {
        background: var(--editor-yellow-soft);
        color: var(--editor-yellow);
    }

    .supplier-editor-header-actions {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 9px;
        flex-shrink: 0;
    }

    .supplier-editor-button {
        min-height: 43px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 0 14px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }

    .supplier-editor-button.secondary {
        border-color: #d7dce5;
        background: #ffffff;
        color: #475569;
    }

    .supplier-editor-button.purchase {
        border-color: #c7d2fe;
        background: var(--editor-indigo-soft);
        color: var(--editor-indigo);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation Summary
    |--------------------------------------------------------------------------
    */

    .supplier-validation-summary {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 17px 18px;
        border: 1px solid #fecaca;
        border-radius: 13px;
        background: var(--editor-red-soft);
        color: var(--editor-red);
    }

    .supplier-validation-icon {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #fee2e2;
    }

    .supplier-validation-summary h2 {
        margin: 0 0 8px;
        font-size: 14px;
    }

    .supplier-validation-summary ul {
        margin: 0;
        padding-left: 18px;
        font-size: 10px;
        line-height: 1.7;
    }

    /*
    |--------------------------------------------------------------------------
    | Main Layout
    |--------------------------------------------------------------------------
    */

    .supplier-form-layout {
        display: grid;
        grid-template-columns:
            minmax(0, 1fr)
            minmax(285px, 330px);
        align-items: start;
        gap: 19px;
    }

    .supplier-form-main {
        display: flex;
        flex-direction: column;
        gap: 18px;
        min-width: 0;
    }

    .supplier-form-sidebar {
        position: sticky;
        top: 20px;
        display: flex;
        flex-direction: column;
        gap: 15px;
        min-width: 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Cards
    |--------------------------------------------------------------------------
    */

    .supplier-form-card,
    .supplier-sidebar-card {
        border: 1px solid var(--editor-border);
        border-radius: 16px;
        background: #ffffff;
        box-shadow: 0 7px 22px rgba(15, 23, 42, 0.04);
    }

    .supplier-form-card {
        padding: 21px 22px;
    }

    .supplier-form-card-header,
    .supplier-sidebar-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .supplier-form-card-header {
        margin-bottom: 20px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--editor-soft-border);
    }

    .supplier-form-card-icon,
    .supplier-sidebar-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
    }

    .supplier-form-card-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
    }

    .supplier-form-card-icon.company,
    .supplier-sidebar-icon.status {
        background: var(--editor-indigo-soft);
        color: var(--editor-indigo);
    }

    .supplier-form-card-icon.address {
        background: var(--editor-blue-soft);
        color: var(--editor-blue);
    }

    .supplier-form-card-icon.commercial {
        background: var(--editor-green-soft);
        color: var(--editor-green);
    }

    .supplier-form-card-icon.banking {
        background: var(--editor-orange-soft);
        color: var(--editor-orange);
    }

    .supplier-form-card-icon.notes,
    .supplier-sidebar-icon.information {
        background: #f1f5f9;
        color: #475569;
    }

    .supplier-sidebar-icon.preferred {
        background: var(--editor-yellow-soft);
        color: var(--editor-yellow);
    }

    .supplier-form-card-header h2 {
        margin: 0 0 4px;
        color: var(--editor-text);
        font-size: 17px;
    }

    .supplier-form-card-header p {
        margin: 0;
        color: var(--editor-muted);
        font-size: 9px;
        line-height: 1.5;
    }

    /*
    |--------------------------------------------------------------------------
    | Fields
    |--------------------------------------------------------------------------
    */

    .supplier-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }

    .supplier-form-field {
        min-width: 0;
    }

    .supplier-form-field-wide {
        grid-column: 1 / -1;
    }

    .supplier-form-field label {
        display: block;
        margin-bottom: 7px;
        color: #374151;
        font-size: 10px;
        font-weight: 800;
    }

    .required-mark {
        color: var(--editor-red);
    }

    .private-label {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-left: 6px;
        padding: 3px 6px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 7px;
        font-weight: 800;
    }

    .supplier-form-field input,
    .supplier-form-field select,
    .supplier-form-field textarea {
        width: 100%;
        border: 1px solid #d7dce5;
        border-radius: 9px;
        background: #ffffff;
        color: var(--editor-text);
        font-family: inherit;
        font-size: 10px;
        outline: none;
        transition: 0.2s ease;
    }

    .supplier-form-field input,
    .supplier-form-field select {
        height: 42px;
        padding: 0 11px;
    }

    .supplier-form-field textarea {
        min-height: 115px;
        padding: 10px 11px;
        line-height: 1.6;
        resize: vertical;
    }

    .supplier-form-field input:focus,
    .supplier-form-field select:focus,
    .supplier-form-field textarea:focus {
        border-color: var(--editor-indigo);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .supplier-form-field input::placeholder,
    .supplier-form-field textarea::placeholder {
        color: #94a3b8;
    }

    .supplier-input-with-icon {
        position: relative;
    }

    .supplier-input-with-icon > i {
        position: absolute;
        top: 50%;
        left: 12px;
        color: #94a3b8;
        font-size: 11px;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .supplier-input-with-icon input {
        padding-left: 35px;
    }

    .supplier-input-suffix,
    .supplier-money-input {
        display: flex;
        align-items: stretch;
        border: 1px solid #d7dce5;
        border-radius: 9px;
        background: #ffffff;
        overflow: hidden;
    }

    .supplier-input-suffix:focus-within,
    .supplier-money-input:focus-within {
        border-color: var(--editor-indigo);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }

    .supplier-input-suffix input,
    .supplier-money-input input {
        min-width: 0;
        border: 0;
        border-radius: 0;
        box-shadow: none !important;
    }

    .supplier-input-suffix span,
    .supplier-money-input > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 11px;
        border-left: 1px solid var(--editor-border);
        background: #f8fafc;
        color: var(--editor-muted);
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
    }

    .supplier-money-input > span {
        min-width: 40px;
        border-right: 1px solid var(--editor-border);
        border-left: 0;
    }

    .supplier-field-help,
    .supplier-field-error {
        display: block;
        margin-top: 5px;
        font-size: 8px;
        line-height: 1.5;
    }

    .supplier-field-help {
        color: var(--editor-muted);
    }

    .supplier-field-error {
        color: var(--editor-red);
        font-weight: 700;
    }

    .supplier-character-count {
        margin-top: 5px;
        color: var(--editor-muted);
        font-size: 8px;
        text-align: right;
    }

    /*
    |--------------------------------------------------------------------------
    | Banking Notice
    |--------------------------------------------------------------------------
    */

    .supplier-bank-warning {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 17px;
        padding: 10px 11px;
        border: 1px solid #fed7aa;
        border-radius: 9px;
        background: var(--editor-orange-soft);
        color: var(--editor-orange);
        font-size: 8px;
        font-weight: 700;
        line-height: 1.5;
    }

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    */

    .supplier-sidebar-card {
        padding: 17px;
    }

    .supplier-sidebar-card-header {
        margin-bottom: 15px;
    }

    .supplier-sidebar-icon {
        width: 39px;
        height: 39px;
        flex: 0 0 39px;
    }

    .supplier-sidebar-card-header h3 {
        margin: 0 0 3px;
        color: var(--editor-text);
        font-size: 13px;
    }

    .supplier-sidebar-card-header p {
        margin: 0;
        color: var(--editor-muted);
        font-size: 8px;
        line-height: 1.5;
    }

    .supplier-status-preview {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-top: 12px;
        padding: 11px;
        border-radius: 9px;
    }

    .supplier-status-preview.active {
        background: var(--editor-green-soft);
        color: var(--editor-green);
    }

    .supplier-status-preview.inactive {
        background: #f1f5f9;
        color: #475569;
    }

    .supplier-status-preview.blocked {
        background: var(--editor-red-soft);
        color: var(--editor-red);
    }

    .supplier-status-preview-dot {
        width: 7px;
        height: 7px;
        flex: 0 0 7px;
        margin-top: 4px;
        border-radius: 50%;
        background: currentColor;
    }

    .supplier-status-preview strong,
    .supplier-status-preview small {
        display: block;
    }

    .supplier-status-preview strong {
        margin-bottom: 3px;
        font-size: 10px;
    }

    .supplier-status-preview small {
        font-size: 7px;
        line-height: 1.5;
    }

    /*
    |--------------------------------------------------------------------------
    | Preferred Toggle
    |--------------------------------------------------------------------------
    */

    .supplier-toggle-card {
        display: flex;
        align-items: flex-start;
        gap: 11px;
        padding: 12px;
        border: 1px solid var(--editor-border);
        border-radius: 10px;
        background: #f8fafc;
        cursor: pointer;
    }

    .supplier-toggle-card > input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .supplier-toggle-switch {
        position: relative;
        width: 39px;
        height: 22px;
        flex: 0 0 39px;
        border-radius: 999px;
        background: #cbd5e1;
        transition: 0.2s ease;
    }

    .supplier-toggle-switch::after {
        content: "";
        position: absolute;
        top: 3px;
        left: 3px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.2);
        transition: 0.2s ease;
    }

    .supplier-toggle-card > input:checked
    + .supplier-toggle-switch {
        background: var(--editor-indigo);
    }

    .supplier-toggle-card > input:checked
    + .supplier-toggle-switch::after {
        transform: translateX(17px);
    }

    .supplier-toggle-content strong,
    .supplier-toggle-content small {
        display: block;
    }

    .supplier-toggle-content strong {
        margin-bottom: 4px;
        color: var(--editor-text);
        font-size: 9px;
    }

    .supplier-toggle-content small {
        color: var(--editor-muted);
        font-size: 7px;
        line-height: 1.5;
    }

    /*
    |--------------------------------------------------------------------------
    | Record Information
    |--------------------------------------------------------------------------
    */

    .supplier-record-information {
        display: flex;
        flex-direction: column;
        gap: 9px;
    }

    .supplier-record-information > div {
        padding: 10px;
        border-radius: 9px;
        background: #f8fafc;
    }

    .supplier-record-information span,
    .supplier-record-information strong {
        display: block;
    }

    .supplier-record-information span {
        margin-bottom: 3px;
        color: var(--editor-muted);
        font-size: 7px;
    }

    .supplier-record-information strong {
        color: var(--editor-text);
        font-size: 9px;
        word-break: break-word;
    }

    /*
    |--------------------------------------------------------------------------
    | Submit Card
    |--------------------------------------------------------------------------
    */

    .supplier-submit-card {
        border-color: #c7d2fe;
        background:
            linear-gradient(
                135deg,
                #ffffff,
                #f8f9ff
            );
    }

    .supplier-submit-summary {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
    }

    .supplier-submit-summary > i {
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: var(--editor-indigo-soft);
        color: var(--editor-indigo);
    }

    .supplier-submit-summary strong,
    .supplier-submit-summary span {
        display: block;
    }

    .supplier-submit-summary strong {
        margin-bottom: 3px;
        font-size: 10px;
    }

    .supplier-submit-summary span {
        color: var(--editor-muted);
        font-size: 7px;
    }

    .supplier-submit-button,
    .supplier-cancel-button {
        width: 100%;
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 9px;
        font-family: inherit;
        font-size: 10px;
        font-weight: 800;
        text-decoration: none;
    }

    .supplier-submit-button {
        border: 1px solid var(--editor-indigo);
        background: var(--editor-indigo);
        color: #ffffff;
        cursor: pointer;
    }

    .supplier-submit-button:hover:not(:disabled) {
        border-color: var(--editor-indigo-dark);
        background: var(--editor-indigo-dark);
    }

    .supplier-submit-button:disabled {
        border-color: #cbd5e1;
        background: #cbd5e1;
        cursor: not-allowed;
    }

    .supplier-cancel-button {
        margin-top: 8px;
        border: 1px solid #d7dce5;
        background: #ffffff;
        color: #475569;
    }

    /*
    |--------------------------------------------------------------------------
    | Responsive
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1150px) {
        .supplier-editor-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .supplier-form-layout {
            grid-template-columns: 1fr;
        }

        .supplier-form-sidebar {
            position: static;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .supplier-submit-card {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 700px) {
        .supplier-editor-header {
            padding: 22px 18px;
        }

        .supplier-editor-header-content {
            align-items: flex-start;
        }

        .supplier-editor-header-icon {
            width: 48px;
            height: 48px;
            flex-basis: 48px;
        }

        .supplier-editor-header h1 {
            font-size: 24px;
        }

        .supplier-editor-header-actions,
        .supplier-editor-button {
            width: 100%;
        }

        .supplier-editor-header-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .supplier-form-grid,
        .supplier-form-sidebar {
            grid-template-columns: 1fr;
        }

        .supplier-form-field-wide,
        .supplier-submit-card {
            grid-column: auto;
        }

        .supplier-form-card {
            padding: 18px 16px;
        }
    }
</style>

@endpush
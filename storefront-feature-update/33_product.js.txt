(()=>{"use strict";document.addEventListener("DOMContentLoaded",function(){"use strict";M(),N(),L(),D(),S(),rt(),St(),ct(),lt(),ft(),mt(),ht(),qt(),xt()});function w(){const t=document.querySelector('meta[name="csrf-token"]');if(t)return t.getAttribute("content");const e=document.querySelector('input[name="_token"]');return e?e.value:""}function y(t,e="$"){const n=Number(t||0);return e+n.toLocaleString(void 0,{minimumFractionDigits:2,maximumFractionDigits:2})}function f(t){t&&(t.hidden=!1,t.classList.remove("d-none"))}function v(t){t&&(t.hidden=!0,t.classList.add("d-none"))}function M(){const t=document.querySelectorAll(".custom-sort-form, #sort-form");t.length&&t.forEach(function(e){const n=e.querySelector(".custom-sort-dropdown"),o=e.querySelector(".sort-trigger"),a=e.querySelector(".sort-options"),r=e.querySelector(".sort-label, #sort-label"),s=e.querySelector('input[name="sort"], #sort-value'),c=e.querySelectorAll(".sort-option");!n||!o||!a||!s||!c.length||(o.addEventListener("click",function(i){i.preventDefault(),i.stopPropagation();const u=a.classList.toggle("show");o.setAttribute("aria-expanded",u?"true":"false")}),c.forEach(function(i){i.addEventListener("click",function(){const u=i.dataset.value||"",l=i.textContent.trim();c.forEach(function(d){d.classList.remove("active"),d.setAttribute("aria-selected","false")}),i.classList.add("active"),i.setAttribute("aria-selected","true"),s.value=u,r&&(r.textContent=l),a.classList.remove("show"),o.setAttribute("aria-expanded","false"),e.submit()})}),document.addEventListener("click",function(i){n.contains(i.target)||(a.classList.remove("show"),o.setAttribute("aria-expanded","false"))}),document.addEventListener("keydown",function(i){i.key==="Escape"&&(a.classList.remove("show"),o.setAttribute("aria-expanded","false"))}))})}function N(){document.addEventListener("click",function(t){const e=t.target.closest(".product-card-gallery-image");if(!e)return;t.preventDefault();const n=e.closest(".product-card, .card-parent");if(!n)return;const o=n.querySelector(".background-image"),a=n.querySelector(".card-main-image, .product-card-main-image"),r=e.dataset.image;r&&(o&&(o.style.backgroundImage='url("'+r+'")'),a&&(a.src=r),n.querySelectorAll(".product-card-gallery-image").forEach(function(s){s.classList.remove("active")}),e.classList.add("active"))})}function L(){const t=document.getElementById("product-quick-view-modal"),e=document.getElementById("product-quick-view-content");if(!t||!e)return;let n=null,o=null;function a(){t.classList.add("active"),t.setAttribute("aria-hidden","false"),document.body.classList.add("product-popup-open");const s=t.querySelector(".product-popup-close");s&&s.focus()}function r(){t.classList.remove("active"),t.setAttribute("aria-hidden","true"),document.body.classList.remove("product-popup-open"),n&&(n.abort(),n=null),o&&o.focus()}document.addEventListener("click",function(s){const c=s.target.closest(".open-product-popup");if(!c)return;s.preventDefault();const i=c.dataset.popupUrl||c.getAttribute("href");i&&(o=c,n&&n.abort(),n=new AbortController,e.innerHTML='<div class="product-popup-loader">Loading product...</div>',a(),fetch(i,{method:"GET",headers:{"X-Requested-With":"XMLHttpRequest",Accept:"text/html"},signal:n.signal}).then(function(u){if(!u.ok)throw new Error("Unable to load product.");return u.text()}).then(function(u){e.innerHTML=u,j(e)}).catch(function(u){u.name!=="AbortError"&&(e.innerHTML='<div class="product-popup-loader">Unable to load product.</div>')}))}),document.addEventListener("click",function(s){s.target.closest("[data-close-product-popup]")&&r()}),document.addEventListener("keydown",function(s){s.key==="Escape"&&t.classList.contains("active")&&r()}),t.addEventListener("click",function(s){const c=s.target.closest(".quick-view-gallery-item");if(c){const i=t.querySelector("#quick-view-main-image, .quick-view-main-image"),u=c.dataset.popupImage;i&&u&&(i.src=u),t.querySelectorAll(".quick-view-gallery-item").forEach(function(l){l.classList.remove("active")}),c.classList.add("active")}})}function j(t){_(t),W(t),A(t)}function D(){A(document)}function A(t){const e=t.querySelectorAll(".single-product-gallery, .product-gallery, .quick-view-images");e.length&&e.forEach(function(n){const o=n.querySelector(".single-product-main-image, .product-main-image, #quick-view-main-image, .quick-view-main-image"),a=n.querySelectorAll(".single-gallery-thumbnail, .product-gallery-thumbnail, .quick-view-gallery-item, [data-gallery-image]");!o||!a.length||a.forEach(function(r){r.dataset.galleryInitialized!=="true"&&(r.dataset.galleryInitialized="true",r.addEventListener("click",function(s){s.preventDefault();const c=r.dataset.image||r.dataset.galleryImage||r.dataset.popupImage||r.querySelector("img")?.src;c&&(o.src=c,r.dataset.largeImage&&(o.dataset.zoomImage=r.dataset.largeImage),a.forEach(function(i){i.classList.remove("active"),i.setAttribute("aria-selected","false")}),r.classList.add("active"),r.setAttribute("aria-selected","true"))}))})})}function S(){_(document)}function _(t){const e=t.querySelectorAll(".product-form, .single-product-form, .quick-view-cart-form, [data-product-form]");e.length&&e.forEach(function(n){if(n.dataset.optionsInitialized==="true")return;n.dataset.optionsInitialized="true";const o=n.querySelectorAll(".option-value-button, .quick-view-option-value"),a=n.querySelectorAll(".product-option-select");o.forEach(function(r){r.addEventListener("click",function(){if(r.disabled||r.classList.contains("disabled"))return;const s=r.dataset.optionId||r.dataset.option,c=r.dataset.valueId||r.dataset.value;if(!s||c===void 0)return;const i=n.querySelector('.product-option-select[data-option-id="'+s+'"], .product-option-select[name="options['+s+']"]'),u=r.classList.contains("active")&&i&&String(i.value||"")===String(c);n.querySelectorAll('[data-option-id="'+s+'"]').forEach(function(d){d.classList.remove("active"),d.setAttribute("aria-pressed","false")});const l=n.querySelector('[data-selected-option="'+s+'"], .selected-option-value[data-option-id="'+s+'"]');if(u){i.value="",l&&(l.textContent=""),i.dispatchEvent(new Event("change",{bubbles:!0})),z(n);return}r.classList.add("active"),r.setAttribute("aria-pressed","true"),i&&(i.value=c,i.dispatchEvent(new Event("change",{bubbles:!0}))),l&&(l.textContent=r.dataset.label||r.textContent.trim()),Y(n,s),z(n)})}),a.forEach(function(r){r.addEventListener("change",function(){const s=r.dataset.optionId;s&&Y(n,s),z(n)})}),n.addEventListener("submit",function(r){G(n)||r.preventDefault()}),z(n)})}function G(t){const e=t.querySelectorAll(".product-option-select[required]");let n=!0,o=null;return e.forEach(function(a){const r=a.dataset.optionId;if(!a.value){n=!1;const s=t.querySelector('[data-option-error="'+r+'"], .product-option-error');s&&(s.textContent="Please select this option.",f(s)),o||(o=t.querySelector('[data-option-id="'+r+'"]')||a)}}),o&&o.focus(),n}function Y(t,e){const n=t.querySelector('[data-option-error="'+e+'"], .product-option-error');n&&(n.textContent="",v(n))}function Q(t){const e=t.querySelector("[data-product-variants]");if(e)return e;const n=t.closest(".quick-view-product, #product-quick-view-content, .single-product-page, .single-product, [data-product-container]");return n?n.querySelector("[data-product-variants]"):null}function E(t){const e=Q(t);if(!e)return[];const n=e.dataset.productVariants||e.textContent||"[]";try{const o=JSON.parse(n);return Array.isArray(o)?o:[]}catch(o){return console.error("Invalid product variant data.",o),[]}}function U(t){const e=t.options||t.option_values||t.values||{},n={};return Array.isArray(e)?(e.forEach(function(o){const a=o.option_id||o.product_option_id||o.option?.id||"",r=o.value_id||o.option_value_id||o.product_option_value_id||o.value?.id||o.value||"";a&&r&&(n[String(a)]=String(r))}),n):(e&&typeof e=="object"&&Object.entries(e).forEach(function([o,a]){a&&typeof a=="object"?n[String(o)]=String(a.value_id||a.option_value_id||a.id||a.value||""):n[String(o)]=String(a)}),n)}function b(t){return typeof t.available=="boolean"?t.available:t.available===1||t.available==="1"?!0:t.available===0||t.available==="0"?!1:Number(t.stock??t.quantity??t.stock_quantity??0)>0}function Z(t){const e={};return t.querySelectorAll(".product-option-select").forEach(function(n){const o=n.dataset.optionId||n.name.match(/\[(.*?)\]/)?.[1],a=String(n.value||"").trim();o&&a&&(e[String(o)]=a)}),e}function V(t,e,n=null){const o=U(t);return Object.entries(e).every(function([a,r]){return n!==null&&String(a)===String(n)?!0:String(o[a]||"")===String(r)})}function R(t){const e=E(t);if(!e.length)return;const n=Z(t);t.querySelectorAll(".product-option-select").forEach(function(o){const a=o.dataset.optionId||o.name.match(/\[(.*?)\]/)?.[1];a&&Array.from(o.options).forEach(function(r){const s=String(r.value||"").trim();if(!s){r.disabled=!1;return}const c={...n,[String(a)]:s},i=e.some(function(u){return b(u)?V(u,c,null):!1});r.disabled=!i})}),t.querySelectorAll(".option-value-button, .quick-view-option-value").forEach(function(o){const a=o.dataset.optionId||o.dataset.option,r=o.dataset.valueId||o.dataset.value;if(!a||r===void 0)return;const s={...n,[String(a)]:String(r)},c=e.some(function(i){return b(i)?V(i,s,null):!1});o.disabled=!c,o.classList.toggle("disabled",!c),o.setAttribute("aria-disabled",c?"false":"true"),c?o.removeAttribute("title"):o.setAttribute("title","This option is currently out of stock.")})}function tt(t){const e=E(t);if(e.length!==1)return!1;const n=e[0];if(b(n))return!1;const o=t.querySelector('input[name="variant_id"], [data-selected-variant]');return o&&(o.value=""),O(t,"This product is currently out of stock and cannot be purchased.",!0),C(t,!1),R(t),!0}function et(t){const e=t.querySelector("[data-custom-measurements]");if(!e)return;const n=Array.from(t.querySelectorAll(".product-option-select")).some(o=>{const a=o.getAttribute("aria-label")||o.closest(".product-option-group")?.textContent||"",r=o.selectedOptions[0]?.dataset.label||o.selectedOptions[0]?.textContent||"";return/\bsize\b/i.test(a)&&/\bcustom\b/i.test(r)});e.hidden=!n,e.disabled=!n}function z(t){const e=E(t);if(!e.length||tt(t))return;if(e.length===1&&b(e[0])){const c=U(e[0]);t.querySelectorAll(".product-option-select").forEach(function(i){i.value||(i.value=c[String(i.dataset.optionId||i.name.match(/\[(.*?)\]/)?.[1])]||"")})}et(t),R(t);const n=Array.from(t.querySelectorAll(".product-option-select")),o=t.querySelector('input[name="variant_id"], [data-selected-variant]');o&&(o.value="");const a={};let r=!0;if(n.forEach(function(c){const i=c.dataset.optionId||c.name.match(/\[(.*?)\]/)?.[1],u=String(c.value||"").trim();if(!i||!u){r=!1;return}a[String(i)]=u}),!n.length||!r||Object.keys(a).length!==n.length){O(t,"Please select one value from every option.",!1),C(t,e.some(b));return}const s=e.find(function(c){const i=U(c),u=Object.entries(a);return Object.entries(i).length!==u.length?!1:u.every(function([l,d]){return String(i[l]||"")===String(d)})});if(!s){ot(t);return}nt(t,s),R(t)}function nt(t,e){const n=t.querySelector('input[name="variant_id"], [data-selected-variant]');n&&(n.value=e.id||"");const o=t.dataset.currencySymbol||document.body.dataset.currencySymbol||"$",a=Number(e.regular_price??e.price??e.original_price??0),r=Number(e.sale_price??e.discount_price??a),s=t.querySelector(".current-product-price, .product-sale-price, [data-product-price]")||document.querySelector(".current-product-price, [data-product-price]"),c=t.querySelector(".product-regular-price, [data-regular-price]")||document.querySelector("[data-regular-price]");s&&(s.textContent=y(r,o)),c&&(r<a?(c.textContent=y(a,o),f(c)):v(c));const i=b(e),u=t.querySelector("[data-product-sku], .product-sku-value")||document.querySelector("[data-product-sku]");u&&e.sku&&(u.textContent=e.sku);const l=t.closest(".quick-view-product, .single-product-page, [data-product-container]")?.querySelector(".single-product-main-image, .product-main-image, .quick-view-main-image")||document.querySelector(".single-product-main-image, .product-main-image"),d=e.image_url||e.image||e.featured_image;l&&d&&(l.src=d),i?O(t,"",!1,!0):O(t,"This selected option is currently out of stock. Please choose another available option.",!0),at(t,i),C(t,i)}function ot(t){const e=t.querySelector('input[name="variant_id"], [data-selected-variant]');e&&(e.value=""),O(t,"This option combination is currently unavailable.",!0),C(t,!1)}function O(t,e,n=!1,o=!1){const a=t.querySelector(".variant-message, [data-variant-message]");a&&(a.textContent=e,a.classList.toggle("error",n),a.classList.toggle("success",!n&&!!e),o&&!e?v(a):e&&f(a));const r=t.querySelector("[data-stock-message], .quick-view-stock, .product-stock-message")||t.closest(".quick-view-product, .single-product-page, [data-product-container]")?.querySelector("[data-stock-message], .quick-view-stock, .product-stock-message");r&&(n&&e?(r.textContent=e,r.classList.remove("in-stock"),r.classList.add("out-of-stock"),f(r)):e||v(r))}function at(t,e){const n=t.closest(".quick-view-product, .single-product-page, [data-product-container]");[t.querySelector("#product-stock"),t.querySelector("[data-product-stock]"),n?.querySelector("#product-stock"),n?.querySelector("[data-product-stock]")].filter(Boolean).forEach(function(a){a.textContent=e?"Available":"Out of Stock",a.classList.toggle("in-stock",e),a.classList.toggle("out-of-stock",!e)});const o=n?.querySelector("#product-stock-badge");o&&(o.textContent=e?"In Stock":"Out of Stock",o.classList.toggle("in-stock-quick-view",e),o.classList.toggle("out-of-stock-quick-view",!e))}function C(t,e){const n=t.querySelectorAll("[data-add-to-cart], [data-buy-now], .add-to-cart-button, .buy-now-button");if(!n.length)return;const o=E(t).length>0,a=Array.from(t.querySelectorAll(".product-option-select")),r=a.length>0&&a.every(function(i){return String(i.value||"").trim()!==""}),s=t.querySelector('input[name="variant_id"], [data-selected-variant]'),c=!!String(s?.value||"").trim();n.forEach(function(i){const u=i.dataset.readyText||(i.hasAttribute("data-buy-now")?"Buy Now":"Add To Cart"),l=i.querySelector(".button-text")||i;if(!e){i.disabled=!0,l.textContent="Out of Stock";return}if(o&&(!r||!c)){i.disabled=!1,l.textContent=u;return}i.disabled=!1,l.textContent=u})}function rt(){W(document)}function W(t){const e=t.querySelectorAll(".quantity-wrapper, .product-quantity, [data-quantity-wrapper]");e.length&&e.forEach(function(n){if(n.dataset.quantityInitialized==="true")return;n.dataset.quantityInitialized="true";const o=n.querySelector('input[type="number"], .quantity-input'),a=n.querySelector(".quantity-minus, [data-quantity-minus]"),r=n.querySelector(".quantity-plus, [data-quantity-plus]");if(!o)return;const s=Number(o.min||1),c=Number(o.max||n.dataset.max||1/0);a&&a.addEventListener("click",function(){const i=Number(o.value||s),u=Math.max(s,i-1);o.value=u,o.dispatchEvent(new Event("change",{bubbles:!0}))}),r&&r.addEventListener("click",function(){const i=Number(o.value||s),u=Math.min(c,i+1);o.value=u,o.dispatchEvent(new Event("change",{bubbles:!0}))}),o.addEventListener("change",function(){let i=Number(o.value||s);i<s&&(i=s),i>c&&(i=c),o.value=i})})}function ct(){document.addEventListener("submit",async function(t){const e=t.target.closest('form[action*="cart/add"], #add-to-cart-form');if(!e||t.defaultPrevented)return;const n=t.submitter;if(n&&(n.hasAttribute("data-buy-now")||n.classList.contains("buy-now-button")||String(n.name||"")==="buy_now"))return;const o=n?.matches("[data-add-to-cart], .add-to-cart-button, .quick-view-cart-button")?n:e.querySelector("[data-add-to-cart], .add-to-cart-button, .quick-view-cart-button");if(!o||(t.preventDefault(),o.disabled))return;const a=o.innerHTML,r=o.querySelector(".button-text")||o;o.disabled=!0,o.setAttribute("aria-busy","true"),r===o?o.textContent="Adding...":r.textContent="Adding...";try{const s=await fetch(e.action,{method:(e.method||"POST").toUpperCase(),headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},body:new FormData(e),credentials:"same-origin"});let c={};try{c=await s.json()}catch{c={}}if(!s.ok||c.success===!1){const u=c.message||Object.values(c.errors||{}).flat().filter(Boolean)[0]||"The product could not be added to your cart.";throw new Error(u)}B(c.cart_count);const i=String(e.querySelector('input[name="product_id"]')?.value||"");ut(e,c.cart||{},i),g({type:"success",title:"Added to your cart",message:c.message||"Your selected item has been added successfully.",form:e})}catch(s){g({type:"error",title:"Unable to add item",message:s?.message||"Something went wrong while adding this item to your cart.",form:e})}finally{o.removeAttribute("aria-busy"),o.innerHTML=a;const s=e.querySelector('input[name="variant_id"], [data-selected-variant]');if(s&&String(s.value||"").trim()){const c=E(e).find(function(i){return String(i.id)===String(s.value)});C(e,c?b(c):!1)}else{const c=E(e);if(c.length){const i=c.some(function(u){return b(u)});C(e,i)}else o.disabled=!1}}})}function B(t){const e=Math.max(0,Number(t||0));document.querySelectorAll("[data-header-cart-count], .cart-count, [data-cart-count]").forEach(function(n){n.textContent=String(e),n.classList.toggle("is-empty",e<1),n.setAttribute("aria-label",e+" items in cart")})}function it(t){return Array.isArray(t)?t:t&&typeof t=="object"?Object.values(t):[]}function st(t){return t?Array.isArray(t)?t.map(function(e){return typeof e=="string"?e:!e||typeof e!="object"?"":e.value_label||e.value||e.label||e.name||""}).filter(Boolean).join(" \xB7 "):typeof t=="object"?Object.values(t).map(function(e){return typeof e=="string"?e:!e||typeof e!="object"?"":e.value_label||e.value||e.label||e.name||""}).filter(Boolean).join(" \xB7 "):"":""}function ut(t,e,n){if(!t||!n)return;const o=it(e).filter(function(s){return String(s?.product_id||"")===n});let a=t.parentElement?.querySelector("[data-product-added-items]");a||(a=document.createElement("section"),a.className="product-added-items",a.setAttribute("data-product-added-items",""),a.setAttribute("aria-live","polite"),a.innerHTML='<div class="product-added-items-heading"><span class="product-added-items-kicker">ADDED TO CART</span><strong>Selected Variations</strong></div><div class="product-added-items-list" data-product-added-items-list></div>',t.insertAdjacentElement("afterend",a));const r=a.querySelector("[data-product-added-items-list]");r&&(r.innerHTML="",o.forEach(function(s){const c=document.createElement("div");c.className="product-added-item";const i=st(s.options)||"Standard option",u=Math.max(1,Number(s.quantity||1)),l=document.createElement("span");l.className="product-added-item-check",l.setAttribute("aria-hidden","true"),l.innerHTML='<i class="fa-solid fa-check"></i>';const d=document.createElement("span");d.className="product-added-item-variation",d.textContent=i;const p=document.createElement("span");p.className="product-added-item-quantity",p.textContent=String(u),p.setAttribute("aria-label","Quantity "+u),c.appendChild(l),c.appendChild(d),c.appendChild(p),r.appendChild(c)}),a.hidden=o.length===0,H())}function dt(){const t=document.querySelector('[data-header-cart-trigger], a[href$="/cart"], a[href*="/cart?"]')?.href||new URL("cart",window.location.href).href;let e=document.querySelector('a[href$="/checkout"], a[href*="/checkout?"]')?.href||"";if(!e)try{const n=new URL(t);n.pathname=n.pathname.replace(/\/cart\/?$/,"/checkout"),e=n.href}catch{e=new URL("checkout",window.location.href).href}return{cartUrl:t,checkoutUrl:e}}function g(t={}){H();let e=document.querySelector("[data-cart-action-popup]");e||(e=document.createElement("div"),e.className="cart-action-popup",e.setAttribute("data-cart-action-popup",""),e.setAttribute("aria-hidden","true"),e.innerHTML='<div class="cart-action-popup-backdrop" data-cart-popup-close></div><div class="cart-action-popup-dialog" role="dialog" aria-modal="true" aria-labelledby="cart-action-popup-title"><button type="button" class="cart-action-popup-close" data-cart-popup-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button><div class="cart-action-popup-icon" data-cart-popup-icon></div><div class="cart-action-popup-copy"><span class="cart-action-popup-kicker">ARIZONA OUTFITS</span><h3 id="cart-action-popup-title" data-cart-popup-title></h3><p data-cart-popup-message></p></div><div class="cart-action-popup-actions" data-cart-popup-actions></div></div>',document.body.appendChild(e),e.addEventListener("click",function(c){c.target.closest("[data-cart-popup-close]")&&I()}),document.addEventListener("keydown",function(c){c.key==="Escape"&&e.classList.contains("is-open")&&I()}));const n=t.type==="error"?"error":"success",o=e.querySelector("[data-cart-popup-title]"),a=e.querySelector("[data-cart-popup-message]"),r=e.querySelector("[data-cart-popup-icon]"),s=e.querySelector("[data-cart-popup-actions]");if(e.dataset.type=n,o&&(o.textContent=t.title||(n==="success"?"Added to your cart":"Unable to add item")),a&&(a.textContent=t.message||""),r&&(r.innerHTML=n==="success"?'<i class="fa-solid fa-check" aria-hidden="true"></i>':'<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>'),s)if(s.innerHTML="",n==="success"){const c=dt(),i=document.createElement("button");i.type="button",i.className="cart-action-popup-button cart-action-popup-button-secondary",i.textContent="Add Another Variation",i.addEventListener("click",function(){I();const d=t.form?.querySelector(".product-option-select");(d?.closest(".product-options, .product-variations, [data-product-options]")||d)?.scrollIntoView({behavior:"smooth",block:"center"}),d?.focus()});const u=document.createElement("a");u.className="cart-action-popup-button cart-action-popup-button-secondary",u.href=c.cartUrl,u.textContent="View Cart";const l=document.createElement("a");l.className="cart-action-popup-button cart-action-popup-button-primary",l.href=c.checkoutUrl,l.textContent="Checkout",s.appendChild(i),s.appendChild(u),s.appendChild(l)}else{const c=document.createElement("button");c.type="button",c.className="cart-action-popup-button cart-action-popup-button-primary",c.textContent="Close",c.addEventListener("click",I),s.appendChild(c)}e.classList.add("is-open"),e.setAttribute("aria-hidden","false"),document.body.classList.add("cart-action-popup-open"),window.setTimeout(function(){e.querySelector(".cart-action-popup-button, .cart-action-popup-close")?.focus()},30)}function I(){const t=document.querySelector("[data-cart-action-popup]");if(!t)return;const e=t.dataset.reloadOnClose==="true";t.classList.remove("is-open"),t.setAttribute("aria-hidden","true"),document.body.classList.remove("cart-action-popup-open"),delete t.dataset.reloadOnClose,e&&window.location.reload()}function H(){if(document.getElementById("product-cart-ajax-ui-styles"))return;const t=document.createElement("style");t.id="product-cart-ajax-ui-styles",t.textContent=`
        .product-added-items {
            margin-top: 14px;
            padding: 13px 14px;
            border: 1px solid rgba(17, 17, 17, .14);
            border-radius: 9px;
            background: rgba(255, 255, 255, .96);
            color: #151515;
        }

        .product-added-items-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .product-added-items-heading strong {
            font-size: 14px;
            font-weight: 700;
        }

        .product-added-items-kicker,
        .cart-action-popup-kicker {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 2px;
            opacity: .58;
        }

        .product-added-items-list {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .product-added-item {
            min-height: 32px;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 5px 7px 5px 9px;
            border: 1px solid rgba(17, 17, 17, .14);
            border-radius: 999px;
            background: #f7f7f7;
        }

        .product-added-item-variation {
            font-size: 10px;
            font-weight: 700;
            line-height: 1.2;
            white-space: nowrap;
        }

        .product-added-item-quantity {
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            background: #171717;
            color: #ffffff;
            font-size: 8px;
            font-weight: 800;
            line-height: 1;
        }

        .product-added-item-check {
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 18px;
            border-radius: 50%;
            background: #e9f8ef;
            color: #18864b;
            font-size: 8px;
        }

        body.cart-action-popup-open {
            overflow: hidden;
        }

        .cart-action-popup {
            position: fixed;
            inset: 0;
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 22px;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transition: opacity .2s ease, visibility .2s ease;
        }

        .cart-action-popup.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .cart-action-popup-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(10, 10, 12, .64);
            backdrop-filter: blur(5px);
        }

        .cart-action-popup-dialog {
            width: min(470px, 100%);
            position: relative;
            z-index: 1;
            padding: 28px;
            border-radius: 18px;
            background: #ffffff;
            color: #171717;
            box-shadow: 0 28px 80px rgba(0, 0, 0, .28);
            transform: translateY(12px) scale(.98);
            transition: transform .2s ease;
        }

        .cart-action-popup.is-open .cart-action-popup-dialog {
            transform: translateY(0) scale(1);
        }

        .cart-action-popup-close {
            width: 34px;
            height: 34px;
            position: absolute;
            top: 14px;
            right: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            border-radius: 50%;
            background: #f1f1f1;
            color: #171717;
            cursor: pointer;
        }

        .cart-action-popup-icon {
            width: 48px;
            height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
            border-radius: 50%;
            background: #e9f8ef;
            color: #18864b;
            font-size: 18px;
        }

        .cart-action-popup[data-type="error"] .cart-action-popup-icon {
            background: #fff0f0;
            color: #c83232;
        }

        .cart-action-popup-copy h3 {
            margin: 7px 0 8px;
            font-size: 22px;
            line-height: 1.2;
        }

        .cart-action-popup-copy p {
            margin: 0;
            font-size: 13px;
            line-height: 1.65;
            color: #666;
        }

        .cart-action-popup-actions {
            display: grid;
            grid-template-columns: 1.35fr 1fr 1fr;
            gap: 8px;
            margin-top: 22px;
        }

        .cart-action-popup-button {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 12px;
            border: 1px solid #171717;
            border-radius: 9px;
            font: inherit;
            font-size: 10px;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: transform .16s ease, background .16s ease, color .16s ease;
        }

        .cart-action-popup-button:hover,
        .cart-action-popup-button:focus-visible {
            transform: translateY(-1px);
            outline: none;
        }

        .cart-action-popup-button-secondary {
            background: #ffffff;
            color: #171717;
        }

        .cart-action-popup-button-secondary:hover,
        .cart-action-popup-button-secondary:focus-visible {
            background: #f4f4f4;
            color: #171717;
        }

        .cart-action-popup-button-primary {
            background: #171717;
            color: #ffffff;
        }

        .cart-action-popup-button-primary:hover,
        .cart-action-popup-button-primary:focus-visible {
            background: #303030;
            color: #ffffff;
        }

        @media (max-width: 600px) {
            .cart-action-popup {
                align-items: flex-end;
                padding: 12px;
            }

            .cart-action-popup-dialog {
                width: 100%;
                padding: 24px 18px 18px;
                border-radius: 18px;
            }

            .cart-action-popup-actions {
                grid-template-columns: 1fr;
            }

            .cart-action-popup-button {
                width: 100%;
            }

            .product-added-items-list {
                gap: 6px;
            }

            .product-added-item {
                max-width: 100%;
            }

            .product-added-item-variation {
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }
    `,document.head.appendChild(t)}function lt(){const t=document.querySelector(".cart-page, .cart-container, [data-cart-container]");if(!t)return;let e=null;function n(o,a=!1){const r=Math.max(1,Number(o.min||1)),s=Number(o.max),c=Number.isFinite(s)&&s>=r?s:1/0;let i=String(o.value||"").replace(/[^\d]/g,""),u=Math.floor(Number(i||r));return(!Number.isFinite(u)||u<r)&&(u=r),u>c&&(u=c,a&&Number.isFinite(c)&&g({type:"error",title:"Stock limit reached",message:"Only "+c+" item"+(c===1?"":"s")+" currently available."})),o.value=u,T(o),u}t.addEventListener("input",function(o){const a=o.target.closest('.cart-quantity-input, input[name^="quantities"], [data-cart-quantity]');if(!a)return;const r=String(a.value||"").replace(/[^\d]/g,"");a.value!==r&&(a.value=r)}),t.addEventListener("change",function(o){const a=o.target.closest('.cart-quantity-input, input[name^="quantities"], [data-cart-quantity]');a&&(n(a,!0),clearTimeout(e),e=setTimeout(function(){pt(a)},250))}),t.addEventListener("click",function(o){const a=o.target.closest(".cart-quantity-minus"),r=o.target.closest(".cart-quantity-plus"),s=a||r;if(!s||(o.preventDefault(),s.disabled))return;const c=s.closest(".cart-item, [data-cart-item]");if(!c)return;const i=c.querySelector(".cart-quantity-input, [data-cart-quantity]");if(!i||i.disabled)return;const u=Math.max(1,Number(i.min||1)),l=Number(i.max),d=Number.isFinite(l)&&l>=u?l:1/0,p=n(i);if(a&&(i.value=Math.max(u,p-1)),r){if(p>=d){Number.isFinite(d)&&g({type:"error",title:"Stock limit reached",message:"Only "+d+" item"+(d===1?"":"s")+" currently available."}),T(i);return}i.value=Math.min(d,p+1)}T(i),i.dispatchEvent(new Event("change",{bubbles:!0}))}),t.querySelectorAll('.cart-quantity-input, input[name^="quantities"], [data-cart-quantity]').forEach(function(o){n(o)})}function T(t){const e=t.closest(".cart-item, [data-cart-item]");if(!e)return;const n=Math.max(1,Math.floor(Number(t.value||1))),o=Math.max(1,Number(t.min||1)),a=Number(t.max),r=Number.isFinite(a)&&a>=o?a:1/0,s=e.querySelector(".cart-quantity-minus"),c=e.querySelector(".cart-quantity-plus");s&&(s.disabled=t.disabled||n<=o),c&&(c.disabled=t.disabled||n>=r)}function pt(t){const e=t.closest(".cart-item, [data-cart-item]");if(!e)return;const n=t.dataset.updateUrl||e.dataset.updateUrl||document.querySelector("[data-cart-update-url]")?.dataset.cartUpdateUrl,o=t.dataset.cartKey||e.dataset.cartKey||t.name.match(/\[(.*?)\]/)?.[1],a=Math.max(1,Number(t.min||1)),r=Number(t.max),s=Number.isFinite(r)&&r>=a?r:1/0;let c=Math.floor(Number(t.value||a));if((!Number.isFinite(c)||c<a)&&(c=a),c>s&&(c=s),t.value=c,!n||!o){const i=t.closest("form");i&&i.submit();return}t.disabled=!0,e.classList.add("updating"),T(t),fetch(n,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":w(),"X-Requested-With":"XMLHttpRequest",Accept:"application/json"},body:JSON.stringify({cart_key:o,quantity:c})}).then(async function(i){const u=await i.json().catch(function(){return{}});if(!i.ok||u.success===!1){const l=u.errors?.quantity?.[0]||u.errors?.cart_key?.[0];throw new Error(u.message||l||"Unable to update cart.")}return u}).then(function(i){const u=Math.max(a,Math.floor(Number(i.quantity||c)));t.value=Math.min(s,u),P(i,e),B(i.cart_count)}).catch(function(i){console.error(i),g({type:"error",title:"Unable to update quantity",message:i?.message||"Please check the available stock and try again."});const u=document.querySelector("[data-cart-action-popup]");u&&(u.dataset.reloadOnClose="true")}).finally(function(){t.disabled=!1,e.classList.remove("updating"),T(t)})}function P(t,e){if(!t||typeof t!="object")return;const n=t.currency_symbol||document.body.dataset.currencySymbol||"$",o=e.querySelector(".cart-item-subtotal, [data-item-subtotal]");o&&t.item_subtotal!==void 0&&(o.textContent=y(t.item_subtotal,n));const a=document.querySelector(".cart-subtotal, [data-cart-subtotal]");a&&t.subtotal!==void 0&&(a.textContent=y(t.subtotal,n));const r=document.querySelector(".cart-discount, [data-cart-discount]");r&&t.discount!==void 0&&(r.textContent=y(t.discount,n));const s=document.querySelector(".cart-total, [data-cart-total]");s&&t.total!==void 0&&(s.textContent=y(t.total,n));const c=document.querySelectorAll(".cart-count, [data-cart-count]");t.cart_count!==void 0&&c.forEach(function(i){i.textContent=t.cart_count})}function mt(){document.addEventListener("submit",function(t){const e=t.target.closest(".coupon-form, form[data-cart-coupon-apply], form[data-cart-coupon-remove]");if(!e)return;const n=e.matches(".coupon-form, form[data-cart-coupon-apply]"),o=e.matches("form[data-cart-coupon-remove]")||!!e.querySelector(".remove-coupon-button");if(!n&&!o)return;t.preventDefault();const a=e.querySelector('button[type="submit"], input[type="submit"]'),r=e.querySelector('input[name="coupon_code"]');if(n&&r){const c=String(r.value||"").trim().toUpperCase();if(r.value=c,!c){g({type:"error",title:"Enter a coupon code",message:"Please enter a coupon code before applying it."}),r.focus();return}if(c.length>100){g({type:"error",title:"Coupon code is too long",message:"Coupon codes can contain up to 100 characters."}),r.focus();return}}a&&(a.disabled=!0);const s=new FormData(e);fetch(e.action,{method:e.method.toUpperCase()||"POST",headers:{"X-CSRF-TOKEN":w(),"X-Requested-With":"XMLHttpRequest",Accept:"application/json"},body:s}).then(async function(c){const i=await c.json().catch(function(){return{}});if(!c.ok||i.success===!1){const u=i.errors?.coupon_code?.[0];throw new Error(i.message||u||"Unable to update the coupon.")}return i}).then(function(c){P(c,document.createElement("div")),B(c.cart_count),g(n?{type:"success",title:"Coupon applied",message:c.message||"Your coupon was applied successfully."}:{type:"success",title:"Coupon removed",message:c.message||"Your coupon was removed successfully."});const i=document.querySelector("[data-cart-action-popup]");i&&(i.dataset.reloadOnClose="true")}).catch(function(c){console.error(c),g({type:"error",title:n?"Coupon not applied":"Unable to remove coupon",message:c?.message||"Please check the coupon and try again."})}).finally(function(){a&&(a.disabled=!1)})}),document.addEventListener("input",function(t){const e=t.target.closest('.coupon-form input[name="coupon_code"]');e&&(e.value=e.value.replace(/[\r\n\t]/g,"").toUpperCase())})}function ft(){document.addEventListener("click",function(t){const e=t.target.closest(".cart-remove-button, [data-remove-cart-item]");if(!e)return;const n=e.closest("form"),o=e.dataset.removeUrl||n?.getAttribute("action"),a=e.closest(".cart-item, [data-cart-item]"),r=e.dataset.cartKey||a?.dataset.cartKey||n?.querySelector('input[name="cart_key"], input[name="key"]')?.value;!o||!r||n&&!e.dataset.ajax||(t.preventDefault(),vt({onConfirm:function(){yt({removeButton:e,form:n,removeUrl:o,cartItem:a,cartKey:r})}}))})}function vt(t={}){H();let e=document.querySelector("[data-cart-action-popup]");if(e||(g({type:"error",title:"Remove item?",message:"Please confirm this cart action."}),e=document.querySelector("[data-cart-action-popup]")),!e)return;const n=e.querySelector("[data-cart-popup-title]"),o=e.querySelector("[data-cart-popup-message]"),a=e.querySelector("[data-cart-popup-icon]"),r=e.querySelector("[data-cart-popup-actions]");if(e.dataset.type="error",n&&(n.textContent="Remove from your cart?"),o&&(o.textContent="This item will be removed from your shopping cart."),a&&(a.innerHTML='<i class="fa-solid fa-trash-can" aria-hidden="true"></i>'),r){r.innerHTML="";const s=document.createElement("button");s.type="button",s.className="cart-action-popup-button cart-action-popup-button-secondary",s.textContent="Keep Item",s.addEventListener("click",I);const c=document.createElement("button");c.type="button",c.className="cart-action-popup-button cart-action-popup-button-primary",c.textContent="Remove Item",c.addEventListener("click",function(){I(),typeof t.onConfirm=="function"&&t.onConfirm()}),r.appendChild(s),r.appendChild(c)}e.classList.add("is-open"),e.setAttribute("aria-hidden","false"),document.body.classList.add("cart-action-popup-open"),window.setTimeout(function(){r?.querySelector(".cart-action-popup-button-secondary")?.focus()},30)}function yt({removeButton:t,form:e,removeUrl:n,cartItem:o,cartKey:a}){t.disabled=!0,o&&o.classList.add("removing"),fetch(n,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":w(),"X-Requested-With":"XMLHttpRequest",Accept:"application/json"},body:JSON.stringify({cart_key:a})}).then(async function(r){const s=await r.json().catch(function(){return{}});if(!r.ok||s.success===!1)throw new Error(s.message||"Unable to remove cart item.");return s}).then(function(r){if(o&&o.remove(),P(r,document.createElement("div")),B(r.cart_count),g({type:"success",title:"Removed from your cart",message:r.message||"The item was removed successfully."}),!document.querySelectorAll(".cart-item, [data-cart-item]").length){const s=document.querySelector(".cart-page, .cart-container, [data-cart-container]"),c=document.querySelector(".empty-cart, [data-empty-cart]");s&&s.classList.add("cart-is-empty"),f(c)}}).catch(function(r){console.error(r),g({type:"error",title:"Unable to remove item",message:r?.message||"Something went wrong while removing this item from your cart."})}).finally(function(){t.disabled=!1,o&&o.classList.remove("removing")})}const F=document.querySelector(".navigation-cover"),X=document.querySelector(".navbar");function $(){if(!F||!X)return;const t=(document.documentElement.scrollHeight-window.innerHeight)*.05;window.scrollY>=t?(F.classList.add("active"),X.classList.add("scrolled")):(F.classList.remove("active"),X.classList.remove("scrolled"))}window.addEventListener("scroll",$,{passive:!0}),window.addEventListener("resize",$),$(),document.querySelectorAll(".moving-circle").forEach(t=>{t.addEventListener("mousemove",e=>{const n=t.getBoundingClientRect(),o=e.clientX-n.left-n.width/2,a=e.clientY-n.top-n.height/2,r=o/4,s=a/4;t.style.transform=`translate3d(${r}px, ${s}px, 0) scale(1.15)`}),t.addEventListener("mouseleave",()=>{t.style.transform="translate3d(0, 0, 0) scale(1)"})}),document.querySelectorAll(".btn-style-1, .btn-style-2, .btn-style-3, .product-popup-close, .quick-view-cart-button").forEach(t=>{const e=t.querySelector(".button-text");e&&(t.addEventListener("mousemove",n=>{const o=t.getBoundingClientRect(),a=n.clientX-o.left-o.width/2,r=n.clientY-o.top-o.height/2;e.style.transform=`translate3d(${a/6}px, ${r/6}px, 0) scale(1.12)`}),t.addEventListener("mouseleave",()=>{e.style.transform="translate3d(0, 0, 0) scale(1)"}))});const gt=document.querySelector(".menu-button"),bt=document.querySelector(".mega-menu");gt?.addEventListener("click",function(){bt?.classList.toggle("active")});function Lt(){document.querySelector(".menu-button")?.classList.toggle("open")}document.addEventListener("DOMContentLoaded",function(){const t=document.querySelectorAll(".blog-posts .about-info-parent, .recent-projects .list-item,.blog-posts .card-parent,.products-grid .card-parent "),e=new IntersectionObserver((n,o)=>{n.forEach(a=>{if(a.isIntersecting){const r=Array.from(t).indexOf(a.target);setTimeout(()=>{a.target.classList.add("in-view")},r*150),o.unobserve(a.target)}})},{threshold:.2});t.forEach(n=>e.observe(n))});function ht(){const t=document.querySelectorAll(".product-tabs");t.length&&t.forEach(function(e){if(e.dataset.tabsInitialized==="true")return;e.dataset.tabsInitialized="true";const n=e.querySelectorAll(".tab-btn"),o=e.querySelectorAll(".tab-content");!n.length||!o.length||(n.forEach(function(a){a.setAttribute("role","tab");const r=a.dataset.tab,s=r?e.querySelector("#"+CSS.escape(r)):null;a.setAttribute("aria-selected",a.classList.contains("active")?"true":"false"),r&&a.setAttribute("aria-controls",r),s&&s.setAttribute("role","tabpanel"),a.addEventListener("click",function(){const c=a.dataset.tab;if(!c)return;const i=e.querySelector("#"+CSS.escape(c));i&&(n.forEach(function(u){u.classList.remove("active"),u.setAttribute("aria-selected","false")}),o.forEach(function(u){u.classList.remove("active"),u.setAttribute("aria-hidden","true")}),a.classList.add("active"),a.setAttribute("aria-selected","true"),i.classList.add("active"),i.setAttribute("aria-hidden","false"))})}),o.forEach(function(a){a.setAttribute("aria-hidden",a.classList.contains("active")?"false":"true")}))})}function St(){document.addEventListener("submit",function(t){const e=t.target.closest(".product-form, .single-product-form, .quick-view-cart-form, [data-product-form]");if(!e)return;const n=E(e);if(!n.length)return;const o=Array.from(e.querySelectorAll(".product-option-select")),a=o.find(function(d){return!String(d.value||"").trim()}),r=e.querySelector('input[name="variant_id"], [data-selected-variant]'),s=String(r?.value||"").trim(),c=e.querySelector(".variant-message, [data-variant-message]");if(!o.length||a||!s){t.preventDefault(),t.stopImmediatePropagation(),r&&(r.value=""),c&&(c.textContent="Please select one value from every option.",c.classList.remove("success"),c.classList.add("error"),f(c)),(a?.closest(".product-option-group")||e.querySelector(".product-option-group"))?.scrollIntoView({behavior:"smooth",block:"center"}),a?.focus();return}const i=n.find(function(d){return String(d.id)===s});if(!i){t.preventDefault(),t.stopImmediatePropagation(),r.value="",c&&(c.textContent="The selected option combination is unavailable.",c.classList.remove("success"),c.classList.add("error"),f(c));return}const u={};o.forEach(function(d){const p=d.dataset.optionId||d.name.match(/\[(.*?)\]/)?.[1];p&&(u[String(p)]=String(d.value))});const l=U(i);if(!(Object.keys(u).length===Object.keys(l).length&&Object.entries(u).every(function([d,p]){return String(l[d]||"")===String(p)}))){t.preventDefault(),t.stopImmediatePropagation(),r.value="",c&&(c.textContent="Please select a valid value from every option.",c.classList.remove("success"),c.classList.add("error"),f(c));return}b(i)||(t.preventDefault(),t.stopImmediatePropagation(),c&&(c.textContent="This selected option is currently out of stock and cannot be purchased.",c.classList.remove("success"),c.classList.add("error"),f(c)))},!0)}document.addEventListener("DOMContentLoaded",function(){const t=document.querySelector(".review-rating-field");if(!t)return;const e=t.querySelector('input[name="rating"]'),n=t.querySelectorAll(".review-rating-option");n.forEach(function(a){a.addEventListener("click",function(){const r=a.dataset.ratingValue||"";e.value=r,n.forEach(function(s){s.classList.remove("active"),s.setAttribute("aria-pressed","false")}),a.classList.add("active"),a.setAttribute("aria-pressed","true")})});const o=t.closest("form");o&&o.addEventListener("submit",function(a){e.value?t.classList.remove("has-error"):(a.preventDefault(),t.classList.add("has-error"),n[0]?.focus())})});function qt(){const t=document.querySelector(".review-select");if(t){const e=t.querySelector(".review-select-trigger"),n=t.querySelectorAll(".review-option"),o=document.getElementById("review-rating"),a=document.getElementById("selected-rating-text");e.addEventListener("click",()=>{t.classList.toggle("active")}),n.forEach(r=>{r.addEventListener("click",()=>{n.forEach(s=>s.classList.remove("active")),r.classList.add("active"),o.value=r.dataset.value,a.textContent=r.textContent,t.classList.remove("active")})}),document.addEventListener("click",r=>{t.contains(r.target)||t.classList.remove("active")})}}function xt(){document.addEventListener("DOMContentLoaded",function(){const t=document.getElementById("shop-filter-form");if(t){const d=t.querySelectorAll(".auto-submit-filter");let p=!1;d.forEach(function(k){k.addEventListener("change",function(){p||(p=!0,t.submit())})})}const e=document.getElementById("sort-form"),n=document.getElementById("sort-trigger"),o=document.getElementById("sort-options"),a=document.getElementById("sort-value"),r=document.getElementById("sort-label");if(!e||!n||!o||!a||!r)return;const s=o.querySelectorAll(".sort-option");function c(){o.classList.add("active"),n.classList.add("active"),n.setAttribute("aria-expanded","true")}function i(){o.classList.remove("active"),n.classList.remove("active"),n.setAttribute("aria-expanded","false")}function u(){o.classList.contains("active")?i():c()}function l(d){const p=d.dataset.value||"",k=d.textContent.trim();a.value=p,r.textContent=k,s.forEach(function(q){const x=q===d;q.classList.toggle("active",x),q.setAttribute("aria-selected",x?"true":"false")}),i(),e.submit()}n.addEventListener("click",function(){u()}),s.forEach(function(d){d.addEventListener("click",function(){l(d)}),d.addEventListener("keydown",function(p){(p.key==="Enter"||p.key===" ")&&(p.preventDefault(),l(d))})}),document.addEventListener("click",function(d){e.contains(d.target)||i()}),document.addEventListener("keydown",function(d){d.key==="Escape"&&i()})})}document.addEventListener("DOMContentLoaded",()=>{document.querySelectorAll("[data-filter-dropdown]").forEach(e=>{const n=e.querySelector("[data-filter-trigger]"),o=e.querySelector("[data-filter-options]"),a=e.querySelectorAll("[data-value]"),r=e.querySelector("[data-filter-input]"),s=e.querySelector("[data-filter-label]");if(!n||!o||!r||!s)return;function c(){e.classList.add("active"),n.setAttribute("aria-expanded","true")}function i(){e.classList.remove("active"),n.setAttribute("aria-expanded","false")}function u(){e.classList.contains("active")?i():(t(e),c())}function l(d){const p=d.dataset.value,k=d.dataset.label||d.textContent.trim();r.value=p,s.textContent=k,a.forEach(x=>{const m=x===d;x.classList.toggle("active",m),x.setAttribute("aria-selected",m?"true":"false")}),i(),r.dispatchEvent(new Event("change",{bubbles:!0}));const q=e.closest("form");q&&q.requestSubmit()}n.addEventListener("click",u),a.forEach(d=>{d.addEventListener("click",()=>{l(d)}),d.addEventListener("keydown",p=>{(p.key==="Enter"||p.key===" ")&&(p.preventDefault(),l(d))})})});function t(e=null){document.querySelectorAll("[data-filter-dropdown].active").forEach(n=>{n!==e&&(n.classList.remove("active"),n.querySelector("[data-filter-trigger]")?.setAttribute("aria-expanded","false"))})}document.addEventListener("click",e=>{e.target.closest("[data-filter-dropdown]")||t()}),document.addEventListener("keydown",e=>{e.key==="Escape"&&t()})}),document.addEventListener("DOMContentLoaded",()=>{const t=document.querySelectorAll("[data-multi-select]");function e(n=null){t.forEach(o=>{o!==n&&(o.classList.remove("active"),o.querySelector("[data-multi-select-trigger]")?.setAttribute("aria-expanded","false"))})}t.forEach(n=>{const o=n.querySelector("[data-multi-select-trigger]"),a=n.querySelector("[data-multi-select-label]"),r=n.querySelector("[data-multi-select-search]"),s=Array.from(n.querySelectorAll(".filter-select-option")),c=Array.from(n.querySelectorAll("[data-multi-select-checkbox]")),i=n.querySelector("[data-select-all]"),u=n.querySelector("[data-clear-all]"),l=n.querySelector("[data-multi-select-empty]");if(!o||!a)return;const d=a.textContent.trim().includes("selected")?null:a.textContent.trim();function p(){const m=c.filter(h=>h.checked);if(!m.length){a.textContent=d||"Select options";return}if(m.length===1){const h=m[0].closest(".filter-select-option")?.querySelector(".filter-option-label");a.textContent=h?.textContent.trim()||"1 selected";return}a.textContent=`${m.length} selected`}function k(){if(!r)return;const m=r.value.trim().toLowerCase();let h=0;s.forEach(K=>{const J=(K.dataset.searchText||K.textContent.trim().toLowerCase()).includes(m);K.hidden=!J,J&&h++}),l&&(l.hidden=h!==0)}function q(){e(n),n.classList.add("active"),o.setAttribute("aria-expanded","true"),window.setTimeout(()=>{r?.focus()},50)}function x(){n.classList.remove("active"),o.setAttribute("aria-expanded","false")}o.addEventListener("click",()=>{n.classList.contains("active")?x():q()}),c.forEach(m=>{m.addEventListener("change",p)}),r?.addEventListener("input",k),i?.addEventListener("click",()=>{s.forEach(m=>{if(m.hidden)return;const h=m.querySelector("[data-multi-select-checkbox]");h&&(h.checked=!0)}),p()}),u?.addEventListener("click",()=>{c.forEach(m=>{m.checked=!1}),p()})}),document.addEventListener("click",n=>{n.target.closest("[data-multi-select]")||e()}),document.addEventListener("keydown",n=>{n.key==="Escape"&&e()})})})(),(()=>{function w(y){const f=[...y.querySelectorAll("[data-product-form]")];y.matches?.("[data-product-form]")&&f.push(y),f.forEach(v=>{const M=v.querySelector("[data-product-variants]");if(!M)return;let N;try{N=JSON.parse(M.textContent)}catch{return}!Array.isArray(N)||N.length!==1||v.querySelectorAll(".product-option-select").forEach(L=>{if(!L.value)return;const j=L.dataset.optionId,D=L.selectedOptions[0]?.dataset.label||L.selectedOptions[0]?.textContent,A=v.querySelector('[data-selected-option="'+j+'"]');A&&A.textContent.trim()!==D&&(A.textContent=D),v.querySelectorAll(".option-value-button, .quick-view-option-value").forEach(S=>{if(String(S.dataset.optionId||S.dataset.option)!==String(j))return;const _=String(S.dataset.valueId||S.dataset.value)===String(L.value);S.classList.toggle("active",_),S.setAttribute("aria-pressed",String(_))})})})}document.addEventListener("DOMContentLoaded",()=>{w(document),new MutationObserver(y=>y.forEach(f=>f.addedNodes.forEach(v=>{v.nodeType===1&&(v.matches("[data-product-form]")||v.querySelector("[data-product-form]"))&&w(v)}))).observe(document.body,{childList:!0,subtree:!0})})})();

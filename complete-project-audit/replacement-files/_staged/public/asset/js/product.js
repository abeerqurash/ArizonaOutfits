(()=>{"use strict";document.addEventListener("DOMContentLoaded",function(){"use strict";X(),$(),K(),Y(),W(),ot(),bt(),rt(),lt(),ft(),pt(),ht(),St(),qt()});function T(){const t=document.querySelector('meta[name="csrf-token"]');if(t)return t.getAttribute("content");const e=document.querySelector('input[name="_token"]');return e?e.value:""}function x(t,e="$"){const n=Number(t||0);return e+n.toLocaleString(void 0,{minimumFractionDigits:2,maximumFractionDigits:2})}function h(t){t&&(t.hidden=!1,t.classList.remove("d-none"))}function A(t){t&&(t.hidden=!0,t.classList.add("d-none"))}function X(){const t=document.querySelectorAll(".custom-sort-form, #sort-form");t.length&&t.forEach(function(e){const n=e.querySelector(".custom-sort-dropdown"),a=e.querySelector(".sort-trigger"),o=e.querySelector(".sort-options"),r=e.querySelector(".sort-label, #sort-label"),c=e.querySelector('input[name="sort"], #sort-value'),i=e.querySelectorAll(".sort-option");!n||!a||!o||!c||!i.length||(a.addEventListener("click",function(s){s.preventDefault(),s.stopPropagation();const u=o.classList.toggle("show");a.setAttribute("aria-expanded",u?"true":"false")}),i.forEach(function(s){s.addEventListener("click",function(){const u=s.dataset.value||"",d=s.textContent.trim();i.forEach(function(l){l.classList.remove("active"),l.setAttribute("aria-selected","false")}),s.classList.add("active"),s.setAttribute("aria-selected","true"),c.value=u,r&&(r.textContent=d),o.classList.remove("show"),a.setAttribute("aria-expanded","false"),e.submit()})}),document.addEventListener("click",function(s){n.contains(s.target)||(o.classList.remove("show"),a.setAttribute("aria-expanded","false"))}),document.addEventListener("keydown",function(s){s.key==="Escape"&&(o.classList.remove("show"),a.setAttribute("aria-expanded","false"))}))})}function $(){document.addEventListener("click",function(t){const e=t.target.closest(".product-card-gallery-image");if(!e)return;t.preventDefault();const n=e.closest(".product-card, .card-parent");if(!n)return;const a=n.querySelector(".background-image"),o=n.querySelector(".card-main-image, .product-card-main-image"),r=e.dataset.image;r&&(a&&(a.style.backgroundImage='url("'+r+'")'),o&&(o.src=r),n.querySelectorAll(".product-card-gallery-image").forEach(function(c){c.classList.remove("active")}),e.classList.add("active"))})}function K(){const t=document.getElementById("product-quick-view-modal"),e=document.getElementById("product-quick-view-content");if(!t||!e)return;let n=null,a=null;function o(){t.classList.add("active"),t.setAttribute("aria-hidden","false"),document.body.classList.add("product-popup-open");const c=t.querySelector(".product-popup-close");c&&c.focus()}function r(){t.classList.remove("active"),t.setAttribute("aria-hidden","true"),document.body.classList.remove("product-popup-open"),n&&(n.abort(),n=null),a&&a.focus()}document.addEventListener("click",function(c){const i=c.target.closest(".open-product-popup");if(!i)return;c.preventDefault();const s=i.dataset.popupUrl||i.getAttribute("href");s&&(a=i,n&&n.abort(),n=new AbortController,e.innerHTML='<div class="product-popup-loader">Loading product...</div>',o(),fetch(s,{method:"GET",headers:{"X-Requested-With":"XMLHttpRequest",Accept:"text/html"},signal:n.signal}).then(function(u){if(!u.ok)throw new Error("Unable to load product.");return u.text()}).then(function(u){e.innerHTML=u,Q(e)}).catch(function(u){u.name!=="AbortError"&&(e.innerHTML='<div class="product-popup-loader">Unable to load product.</div>')}))}),document.addEventListener("click",function(c){c.target.closest("[data-close-product-popup]")&&r()}),document.addEventListener("keydown",function(c){c.key==="Escape"&&t.classList.contains("active")&&r()}),t.addEventListener("click",function(c){const i=c.target.closest(".quick-view-gallery-item");if(i){const s=t.querySelector("#quick-view-main-image, .quick-view-main-image"),u=i.dataset.popupImage;s&&u&&(s.src=u),t.querySelectorAll(".quick-view-gallery-item").forEach(function(d){d.classList.remove("active")}),i.classList.add("active")}})}function Q(t){V(t),z(t),U(t)}function Y(){U(document)}function U(t){const e=t.querySelectorAll(".single-product-gallery, .product-gallery, .quick-view-images");e.length&&e.forEach(function(n){const a=n.querySelector(".single-product-main-image, .product-main-image, #quick-view-main-image, .quick-view-main-image"),o=n.querySelectorAll(".single-gallery-thumbnail, .product-gallery-thumbnail, .quick-view-gallery-item, [data-gallery-image]");!a||!o.length||o.forEach(function(r){r.dataset.galleryInitialized!=="true"&&(r.dataset.galleryInitialized="true",r.addEventListener("click",function(c){c.preventDefault();const i=r.dataset.image||r.dataset.galleryImage||r.dataset.popupImage||r.querySelector("img")?.src;i&&(a.src=i,r.dataset.largeImage&&(a.dataset.zoomImage=r.dataset.largeImage),o.forEach(function(s){s.classList.remove("active"),s.setAttribute("aria-selected","false")}),r.classList.add("active"),r.setAttribute("aria-selected","true"))}))})})}function W(){V(document)}function V(t){const e=t.querySelectorAll(".product-form, .single-product-form, .quick-view-cart-form, [data-product-form]");e.length&&e.forEach(function(n){if(n.dataset.optionsInitialized==="true")return;n.dataset.optionsInitialized="true";const a=n.querySelectorAll(".option-value-button, .quick-view-option-value"),o=n.querySelectorAll(".product-option-select");a.forEach(function(r){r.addEventListener("click",function(){if(r.disabled||r.classList.contains("disabled"))return;const c=r.dataset.optionId||r.dataset.option,i=r.dataset.valueId||r.dataset.value;if(!c||i===void 0)return;const s=n.querySelector('.product-option-select[data-option-id="'+c+'"], .product-option-select[name="options['+c+']"]'),u=r.classList.contains("active")&&s&&String(s.value||"")===String(i);n.querySelectorAll('[data-option-id="'+c+'"]').forEach(function(l){l.classList.remove("active"),l.setAttribute("aria-pressed","false")});const d=n.querySelector('[data-selected-option="'+c+'"], .selected-option-value[data-option-id="'+c+'"]');if(u){s.value="",d&&(d.textContent=""),s.dispatchEvent(new Event("change",{bubbles:!0})),I(n);return}r.classList.add("active"),r.setAttribute("aria-pressed","true"),s&&(s.value=i,s.dispatchEvent(new Event("change",{bubbles:!0}))),d&&(d.textContent=r.dataset.label||r.textContent.trim()),R(n,c),I(n)})}),o.forEach(function(r){r.addEventListener("change",function(){const c=r.dataset.optionId;c&&R(n,c),I(n)})}),n.addEventListener("submit",function(r){G(n)||r.preventDefault()}),I(n)})}function G(t){const e=t.querySelectorAll(".product-option-select[required]");let n=!0,a=null;return e.forEach(function(o){const r=o.dataset.optionId;if(!o.value){n=!1;const c=t.querySelector('[data-option-error="'+r+'"], .product-option-error');c&&(c.textContent="Please select this option.",h(c)),a||(a=t.querySelector('[data-option-id="'+r+'"]')||o)}}),a&&a.focus(),n}function R(t,e){const n=t.querySelector('[data-option-error="'+e+'"], .product-option-error');n&&(n.textContent="",A(n))}function J(t){const e=t.querySelector("[data-product-variants]");if(e)return e;const n=t.closest(".quick-view-product, #product-quick-view-content, .single-product-page, .single-product, [data-product-container]");return n?n.querySelector("[data-product-variants]"):null}function b(t){const e=J(t);if(!e)return[];const n=e.dataset.productVariants||e.textContent||"[]";try{const a=JSON.parse(n);return Array.isArray(a)?a:[]}catch(a){return console.error("Invalid product variant data.",a),[]}}function M(t){const e=t.options||t.option_values||t.values||{},n={};return Array.isArray(e)?(e.forEach(function(a){const o=a.option_id||a.product_option_id||a.option?.id||"",r=a.value_id||a.option_value_id||a.product_option_value_id||a.value?.id||a.value||"";o&&r&&(n[String(o)]=String(r))}),n):(e&&typeof e=="object"&&Object.entries(e).forEach(function([a,o]){o&&typeof o=="object"?n[String(a)]=String(o.value_id||o.option_value_id||o.id||o.value||""):n[String(a)]=String(o)}),n)}function S(t){return typeof t.available=="boolean"?t.available:t.available===1||t.available==="1"?!0:t.available===0||t.available==="0"?!1:Number(t.stock??t.quantity??t.stock_quantity??0)>0}function Z(t){const e={};return t.querySelectorAll(".product-option-select").forEach(function(n){const a=n.dataset.optionId||n.name.match(/\[(.*?)\]/)?.[1],o=String(n.value||"").trim();a&&o&&(e[String(a)]=o)}),e}function F(t,e,n=null){const a=M(t);return Object.entries(e).every(function([o,r]){return n!==null&&String(o)===String(n)?!0:String(a[o]||"")===String(r)})}function N(t){const e=b(t);if(!e.length)return;const n=Z(t);t.querySelectorAll(".product-option-select").forEach(function(a){const o=a.dataset.optionId||a.name.match(/\[(.*?)\]/)?.[1];o&&Array.from(a.options).forEach(function(r){const c=String(r.value||"").trim();if(!c){r.disabled=!1;return}const i={...n,[String(o)]:c},s=e.some(function(u){return S(u)?F(u,i,null):!1});r.disabled=!s})}),t.querySelectorAll(".option-value-button, .quick-view-option-value").forEach(function(a){const o=a.dataset.optionId||a.dataset.option,r=a.dataset.valueId||a.dataset.value;if(!o||r===void 0)return;const c={...n,[String(o)]:String(r)},i=e.some(function(s){return S(s)?F(s,c,null):!1});a.disabled=!i,a.classList.toggle("disabled",!i),a.setAttribute("aria-disabled",i?"false":"true"),i?a.removeAttribute("title"):a.setAttribute("title","This option is currently out of stock.")})}function tt(t){const e=b(t);if(e.length!==1)return!1;const n=e[0];if(S(n))return!1;const a=t.querySelector('input[name="variant_id"], [data-selected-variant]');return a&&(a.value=""),L(t,"This product is currently out of stock and cannot be purchased.",!0),E(t,!1),N(t),!0}function I(t){const e=b(t);if(!e.length||tt(t))return;N(t);const n=Array.from(t.querySelectorAll(".product-option-select")),a=t.querySelector('input[name="variant_id"], [data-selected-variant]');a&&(a.value="");const o={};let r=!0;if(n.forEach(function(i){const s=i.dataset.optionId||i.name.match(/\[(.*?)\]/)?.[1],u=String(i.value||"").trim();if(!s||!u){r=!1;return}o[String(s)]=u}),!n.length||!r||Object.keys(o).length!==n.length){L(t,"Please select one value from every option.",!1),E(t,!1);return}const c=e.find(function(i){const s=M(i),u=Object.entries(o);return Object.entries(s).length!==u.length?!1:u.every(function([l,p]){return String(s[l]||"")===String(p)})});if(!c){nt(t);return}et(t,c),N(t)}function et(t,e){const n=t.querySelector('input[name="variant_id"], [data-selected-variant]');n&&(n.value=e.id||"");const a=t.dataset.currencySymbol||document.body.dataset.currencySymbol||"$",o=Number(e.regular_price??e.price??e.original_price??0),r=Number(e.sale_price??e.discount_price??o),c=t.querySelector(".current-product-price, .product-sale-price, [data-product-price]")||document.querySelector(".current-product-price, [data-product-price]"),i=t.querySelector(".product-regular-price, [data-regular-price]")||document.querySelector("[data-regular-price]");c&&(c.textContent=x(r,a)),i&&(r<o?(i.textContent=x(o,a),h(i)):A(i));const s=S(e),u=t.querySelector("[data-product-sku], .product-sku-value")||document.querySelector("[data-product-sku]");u&&e.sku&&(u.textContent=e.sku);const l=t.closest(".quick-view-product, .single-product-page, [data-product-container]")?.querySelector(".single-product-main-image, .product-main-image, .quick-view-main-image")||document.querySelector(".single-product-main-image, .product-main-image"),p=e.image_url||e.image||e.featured_image;l&&p&&(l.src=p),s?L(t,"",!1,!0):L(t,"This selected option is currently out of stock. Please choose another available option.",!0),at(t,s),E(t,s)}function nt(t){const e=t.querySelector('input[name="variant_id"], [data-selected-variant]');e&&(e.value=""),L(t,"This option combination is currently unavailable.",!0),E(t,!1)}function L(t,e,n=!1,a=!1){const o=t.querySelector(".variant-message, [data-variant-message]");o&&(o.textContent=e,o.classList.toggle("error",n),o.classList.toggle("success",!n&&!!e),a&&!e?A(o):e&&h(o));const r=t.querySelector("[data-stock-message], .quick-view-stock, .product-stock-message")||t.closest(".quick-view-product, .single-product-page, [data-product-container]")?.querySelector("[data-stock-message], .quick-view-stock, .product-stock-message");r&&(n&&e?(r.textContent=e,r.classList.remove("in-stock"),r.classList.add("out-of-stock"),h(r)):e||A(r))}function at(t,e){const n=t.closest(".quick-view-product, .single-product-page, [data-product-container]");[t.querySelector("#product-stock"),t.querySelector("[data-product-stock]"),n?.querySelector("#product-stock"),n?.querySelector("[data-product-stock]")].filter(Boolean).forEach(function(r){r.textContent=e?"Available":"Out of Stock",r.classList.toggle("in-stock",e),r.classList.toggle("out-of-stock",!e)});const o=n?.querySelector("#product-stock-badge");o&&(o.textContent=e?"In Stock":"Out of Stock",o.classList.toggle("in-stock-quick-view",e),o.classList.toggle("out-of-stock-quick-view",!e))}function E(t,e){const n=t.querySelectorAll("[data-add-to-cart], [data-buy-now], .add-to-cart-button, .buy-now-button");if(!n.length)return;const o=b(t).length>0,r=Array.from(t.querySelectorAll(".product-option-select")),c=r.length>0&&r.every(function(u){return String(u.value||"").trim()!==""}),i=t.querySelector('input[name="variant_id"], [data-selected-variant]'),s=!!String(i?.value||"").trim();n.forEach(function(u){const d=u.dataset.readyText||(u.hasAttribute("data-buy-now")?"Buy Now":"Add To Cart"),l=u.querySelector(".button-text")||u;if(!e){u.disabled=!0,l.textContent="Out of Stock";return}if(o&&(!c||!s)){u.disabled=!0,l.textContent="Select Options";return}u.disabled=!1,l.textContent=d})}function ot(){z(document)}function z(t){const e=t.querySelectorAll(".quantity-wrapper, .product-quantity, [data-quantity-wrapper]");e.length&&e.forEach(function(n){if(n.dataset.quantityInitialized==="true")return;n.dataset.quantityInitialized="true";const a=n.querySelector('input[type="number"], .quantity-input'),o=n.querySelector(".quantity-minus, [data-quantity-minus]"),r=n.querySelector(".quantity-plus, [data-quantity-plus]");if(!a)return;const c=Number(a.min||1),i=Number(a.max||n.dataset.max||1/0);o&&o.addEventListener("click",function(){const s=Number(a.value||c),u=Math.max(c,s-1);a.value=u,a.dispatchEvent(new Event("change",{bubbles:!0}))}),r&&r.addEventListener("click",function(){const s=Number(a.value||c),u=Math.min(i,s+1);a.value=u,a.dispatchEvent(new Event("change",{bubbles:!0}))}),a.addEventListener("change",function(){let s=Number(a.value||c);s<c&&(s=c),s>i&&(s=i),a.value=s})})}function rt(){document.addEventListener("submit",async function(t){const e=t.target.closest('form[action*="cart/add"], #add-to-cart-form');if(!e||t.defaultPrevented)return;const n=t.submitter;if(n&&(n.hasAttribute("data-buy-now")||n.classList.contains("buy-now-button")||String(n.name||"")==="buy_now"))return;const a=n?.matches("[data-add-to-cart], .add-to-cart-button, .quick-view-cart-button")?n:e.querySelector("[data-add-to-cart], .add-to-cart-button, .quick-view-cart-button");if(!a||(t.preventDefault(),a.disabled))return;const o=a.innerHTML,r=a.querySelector(".button-text")||a;a.disabled=!0,a.setAttribute("aria-busy","true"),r===a?a.textContent="Adding...":r.textContent="Adding...";try{const c=await fetch(e.action,{method:(e.method||"POST").toUpperCase(),headers:{Accept:"application/json","X-Requested-With":"XMLHttpRequest"},body:new FormData(e),credentials:"same-origin"});let i={};try{i=await c.json()}catch{i={}}if(!c.ok||i.success===!1){const u=i.message||Object.values(i.errors||{}).flat().filter(Boolean)[0]||"The product could not be added to your cart.";throw new Error(u)}O(i.cart_count);const s=String(e.querySelector('input[name="product_id"]')?.value||"");st(e,i.cart||{},s),g({type:"success",title:"Added to your cart",message:i.message||"Your selected item has been added successfully.",form:e})}catch(c){g({type:"error",title:"Unable to add item",message:c?.message||"Something went wrong while adding this item to your cart.",form:e})}finally{a.removeAttribute("aria-busy"),a.innerHTML=o;const c=e.querySelector('input[name="variant_id"], [data-selected-variant]');if(c&&String(c.value||"").trim()){const s=b(e).find(function(u){return String(u.id)===String(c.value)});E(e,s?S(s):!1)}else{const i=b(e);if(i.length){const s=i.some(function(u){return S(u)});E(e,s)}else a.disabled=!1}}})}function O(t){const e=Math.max(0,Number(t||0));document.querySelectorAll("[data-header-cart-count], .cart-count, [data-cart-count]").forEach(function(n){n.textContent=String(e),n.classList.toggle("is-empty",e<1),n.setAttribute("aria-label",e+" items in cart")})}function it(t){return Array.isArray(t)?t:t&&typeof t=="object"?Object.values(t):[]}function ct(t){return t?Array.isArray(t)?t.map(function(e){return typeof e=="string"?e:!e||typeof e!="object"?"":e.value_label||e.value||e.label||e.name||""}).filter(Boolean).join(" \xB7 "):typeof t=="object"?Object.values(t).map(function(e){return typeof e=="string"?e:!e||typeof e!="object"?"":e.value_label||e.value||e.label||e.name||""}).filter(Boolean).join(" \xB7 "):"":""}function st(t,e,n){if(!t||!n)return;const a=it(e).filter(function(c){return String(c?.product_id||"")===n});let o=t.parentElement?.querySelector("[data-product-added-items]");o||(o=document.createElement("section"),o.className="product-added-items",o.setAttribute("data-product-added-items",""),o.setAttribute("aria-live","polite"),o.innerHTML='<div class="product-added-items-heading"><span class="product-added-items-kicker">ADDED TO CART</span><strong>Selected Variations</strong></div><div class="product-added-items-list" data-product-added-items-list></div>',t.insertAdjacentElement("afterend",o));const r=o.querySelector("[data-product-added-items-list]");r&&(r.innerHTML="",a.forEach(function(c){const i=document.createElement("div");i.className="product-added-item";const s=ct(c.options)||"Standard option",u=Math.max(1,Number(c.quantity||1)),d=document.createElement("span");d.className="product-added-item-check",d.setAttribute("aria-hidden","true"),d.innerHTML='<i class="fa-solid fa-check"></i>';const l=document.createElement("span");l.className="product-added-item-variation",l.textContent=s;const p=document.createElement("span");p.className="product-added-item-quantity",p.textContent=String(u),p.setAttribute("aria-label","Quantity "+u),i.appendChild(d),i.appendChild(l),i.appendChild(p),r.appendChild(i)}),o.hidden=a.length===0,_())}function ut(){const e=document.querySelector('[data-header-cart-trigger], a[href$="/cart"], a[href*="/cart?"]')?.href||new URL("cart",window.location.href).href;let a=document.querySelector('a[href$="/checkout"], a[href*="/checkout?"]')?.href||"";if(!a)try{const o=new URL(e);o.pathname=o.pathname.replace(/\/cart\/?$/,"/checkout"),a=o.href}catch{a=new URL("checkout",window.location.href).href}return{cartUrl:e,checkoutUrl:a}}function g(t={}){_();let e=document.querySelector("[data-cart-action-popup]");e||(e=document.createElement("div"),e.className="cart-action-popup",e.setAttribute("data-cart-action-popup",""),e.setAttribute("aria-hidden","true"),e.innerHTML='<div class="cart-action-popup-backdrop" data-cart-popup-close></div><div class="cart-action-popup-dialog" role="dialog" aria-modal="true" aria-labelledby="cart-action-popup-title"><button type="button" class="cart-action-popup-close" data-cart-popup-close aria-label="Close"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button><div class="cart-action-popup-icon" data-cart-popup-icon></div><div class="cart-action-popup-copy"><span class="cart-action-popup-kicker">ARIZONA OUTFITS</span><h3 id="cart-action-popup-title" data-cart-popup-title></h3><p data-cart-popup-message></p></div><div class="cart-action-popup-actions" data-cart-popup-actions></div></div>',document.body.appendChild(e),e.addEventListener("click",function(i){i.target.closest("[data-cart-popup-close]")&&k()}),document.addEventListener("keydown",function(i){i.key==="Escape"&&e.classList.contains("is-open")&&k()}));const n=t.type==="error"?"error":"success",a=e.querySelector("[data-cart-popup-title]"),o=e.querySelector("[data-cart-popup-message]"),r=e.querySelector("[data-cart-popup-icon]"),c=e.querySelector("[data-cart-popup-actions]");if(e.dataset.type=n,a&&(a.textContent=t.title||(n==="success"?"Added to your cart":"Unable to add item")),o&&(o.textContent=t.message||""),r&&(r.innerHTML=n==="success"?'<i class="fa-solid fa-check" aria-hidden="true"></i>':'<i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>'),c)if(c.innerHTML="",n==="success"){const i=ut(),s=document.createElement("button");s.type="button",s.className="cart-action-popup-button cart-action-popup-button-secondary",s.textContent="Add Another Variation",s.addEventListener("click",function(){k();const l=t.form?.querySelector(".product-option-select");(l?.closest(".product-options, .product-variations, [data-product-options]")||l)?.scrollIntoView({behavior:"smooth",block:"center"}),l?.focus()});const u=document.createElement("a");u.className="cart-action-popup-button cart-action-popup-button-secondary",u.href=i.cartUrl,u.textContent="View Cart";const d=document.createElement("a");d.className="cart-action-popup-button cart-action-popup-button-primary",d.href=i.checkoutUrl,d.textContent="Checkout",c.appendChild(s),c.appendChild(u),c.appendChild(d)}else{const i=document.createElement("button");i.type="button",i.className="cart-action-popup-button cart-action-popup-button-primary",i.textContent="Close",i.addEventListener("click",k),c.appendChild(i)}e.classList.add("is-open"),e.setAttribute("aria-hidden","false"),document.body.classList.add("cart-action-popup-open"),window.setTimeout(function(){e.querySelector(".cart-action-popup-button, .cart-action-popup-close")?.focus()},30)}function k(){const t=document.querySelector("[data-cart-action-popup]");if(!t)return;const e=t.dataset.reloadOnClose==="true";t.classList.remove("is-open"),t.setAttribute("aria-hidden","true"),document.body.classList.remove("cart-action-popup-open"),delete t.dataset.reloadOnClose,e&&window.location.reload()}function _(){if(document.getElementById("product-cart-ajax-ui-styles"))return;const t=document.createElement("style");t.id="product-cart-ajax-ui-styles",t.textContent=`
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
    `,document.head.appendChild(t)}function lt(){const t=document.querySelector(".cart-page, .cart-container, [data-cart-container]");if(!t)return;let e=null;function n(a,o=!1){const r=Math.max(1,Number(a.min||1)),c=Number(a.max),i=Number.isFinite(c)&&c>=r?c:1/0;let s=String(a.value||"").replace(/[^\d]/g,""),u=Math.floor(Number(s||r));return(!Number.isFinite(u)||u<r)&&(u=r),u>i&&(u=i,o&&Number.isFinite(i)&&g({type:"error",title:"Stock limit reached",message:"Only "+i+" item"+(i===1?"":"s")+" currently available."})),a.value=u,w(a),u}t.addEventListener("input",function(a){const o=a.target.closest('.cart-quantity-input, input[name^="quantities"], [data-cart-quantity]');if(!o)return;const r=String(o.value||"").replace(/[^\d]/g,"");o.value!==r&&(o.value=r)}),t.addEventListener("change",function(a){const o=a.target.closest('.cart-quantity-input, input[name^="quantities"], [data-cart-quantity]');o&&(n(o,!0),clearTimeout(e),e=setTimeout(function(){dt(o)},250))}),t.addEventListener("click",function(a){const o=a.target.closest(".cart-quantity-minus"),r=a.target.closest(".cart-quantity-plus"),c=o||r;if(!c||(a.preventDefault(),c.disabled))return;const i=c.closest(".cart-item, [data-cart-item]");if(!i)return;const s=i.querySelector(".cart-quantity-input, [data-cart-quantity]");if(!s||s.disabled)return;const u=Math.max(1,Number(s.min||1)),d=Number(s.max),l=Number.isFinite(d)&&d>=u?d:1/0,p=n(s);if(o&&(s.value=Math.max(u,p-1)),r){if(p>=l){Number.isFinite(l)&&g({type:"error",title:"Stock limit reached",message:"Only "+l+" item"+(l===1?"":"s")+" currently available."}),w(s);return}s.value=Math.min(l,p+1)}w(s),s.dispatchEvent(new Event("change",{bubbles:!0}))}),t.querySelectorAll('.cart-quantity-input, input[name^="quantities"], [data-cart-quantity]').forEach(function(a){n(a)})}function w(t){const e=t.closest(".cart-item, [data-cart-item]");if(!e)return;const n=Math.max(1,Math.floor(Number(t.value||1))),a=Math.max(1,Number(t.min||1)),o=Number(t.max),r=Number.isFinite(o)&&o>=a?o:1/0,c=e.querySelector(".cart-quantity-minus"),i=e.querySelector(".cart-quantity-plus");c&&(c.disabled=t.disabled||n<=a),i&&(i.disabled=t.disabled||n>=r)}function dt(t){const e=t.closest(".cart-item, [data-cart-item]");if(!e)return;const n=t.dataset.updateUrl||e.dataset.updateUrl||document.querySelector("[data-cart-update-url]")?.dataset.cartUpdateUrl,a=t.dataset.cartKey||e.dataset.cartKey||t.name.match(/\[(.*?)\]/)?.[1],o=Math.max(1,Number(t.min||1)),r=Number(t.max),c=Number.isFinite(r)&&r>=o?r:1/0;let i=Math.floor(Number(t.value||o));if((!Number.isFinite(i)||i<o)&&(i=o),i>c&&(i=c),t.value=i,!n||!a){const s=t.closest("form");s&&s.submit();return}t.disabled=!0,e.classList.add("updating"),w(t),fetch(n,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":T(),"X-Requested-With":"XMLHttpRequest",Accept:"application/json"},body:JSON.stringify({cart_key:a,quantity:i})}).then(async function(s){const u=await s.json().catch(function(){return{}});if(!s.ok||u.success===!1){const d=u.errors?.quantity?.[0]||u.errors?.cart_key?.[0];throw new Error(u.message||d||"Unable to update cart.")}return u}).then(function(s){const u=Math.max(o,Math.floor(Number(s.quantity||i)));t.value=Math.min(c,u),B(s,e),O(s.cart_count)}).catch(function(s){console.error(s),g({type:"error",title:"Unable to update quantity",message:s?.message||"Please check the available stock and try again."});const u=document.querySelector("[data-cart-action-popup]");u&&(u.dataset.reloadOnClose="true")}).finally(function(){t.disabled=!1,e.classList.remove("updating"),w(t)})}function B(t,e){if(!t||typeof t!="object")return;const n=t.currency_symbol||document.body.dataset.currencySymbol||"$",a=e.querySelector(".cart-item-subtotal, [data-item-subtotal]");a&&t.item_subtotal!==void 0&&(a.textContent=x(t.item_subtotal,n));const o=document.querySelector(".cart-subtotal, [data-cart-subtotal]");o&&t.subtotal!==void 0&&(o.textContent=x(t.subtotal,n));const r=document.querySelector(".cart-discount, [data-cart-discount]");r&&t.discount!==void 0&&(r.textContent=x(t.discount,n));const c=document.querySelector(".cart-total, [data-cart-total]");c&&t.total!==void 0&&(c.textContent=x(t.total,n));const i=document.querySelectorAll(".cart-count, [data-cart-count]");t.cart_count!==void 0&&i.forEach(function(s){s.textContent=t.cart_count})}function pt(){document.addEventListener("submit",function(t){const e=t.target.closest(".coupon-form, form[data-cart-coupon-apply], form[data-cart-coupon-remove]");if(!e)return;const n=e.matches(".coupon-form, form[data-cart-coupon-apply]"),a=e.matches("form[data-cart-coupon-remove]")||!!e.querySelector(".remove-coupon-button");if(!n&&!a)return;t.preventDefault();const o=e.querySelector('button[type="submit"], input[type="submit"]'),r=e.querySelector('input[name="coupon_code"]');if(n&&r){const i=String(r.value||"").trim().toUpperCase();if(r.value=i,!i){g({type:"error",title:"Enter a coupon code",message:"Please enter a coupon code before applying it."}),r.focus();return}if(i.length>100){g({type:"error",title:"Coupon code is too long",message:"Coupon codes can contain up to 100 characters."}),r.focus();return}}o&&(o.disabled=!0);const c=new FormData(e);fetch(e.action,{method:e.method.toUpperCase()||"POST",headers:{"X-CSRF-TOKEN":T(),"X-Requested-With":"XMLHttpRequest",Accept:"application/json"},body:c}).then(async function(i){const s=await i.json().catch(function(){return{}});if(!i.ok||s.success===!1){const u=s.errors?.coupon_code?.[0];throw new Error(s.message||u||"Unable to update the coupon.")}return s}).then(function(i){B(i,document.createElement("div")),O(i.cart_count),g(n?{type:"success",title:"Coupon applied",message:i.message||"Your coupon was applied successfully."}:{type:"success",title:"Coupon removed",message:i.message||"Your coupon was removed successfully."});const s=document.querySelector("[data-cart-action-popup]");s&&(s.dataset.reloadOnClose="true")}).catch(function(i){console.error(i),g({type:"error",title:n?"Coupon not applied":"Unable to remove coupon",message:i?.message||"Please check the coupon and try again."})}).finally(function(){o&&(o.disabled=!1)})}),document.addEventListener("input",function(t){const e=t.target.closest('.coupon-form input[name="coupon_code"]');e&&(e.value=e.value.replace(/[\r\n\t]/g,"").toUpperCase())})}function ft(){document.addEventListener("click",function(t){const e=t.target.closest(".cart-remove-button, [data-remove-cart-item]");if(!e)return;const n=e.closest("form"),a=e.dataset.removeUrl||n?.getAttribute("action"),o=e.closest(".cart-item, [data-cart-item]"),r=e.dataset.cartKey||o?.dataset.cartKey||n?.querySelector('input[name="cart_key"], input[name="key"]')?.value;!a||!r||n&&!e.dataset.ajax||(t.preventDefault(),mt({onConfirm:function(){gt({removeButton:e,form:n,removeUrl:a,cartItem:o,cartKey:r})}}))})}function mt(t={}){_();let e=document.querySelector("[data-cart-action-popup]");if(e||(g({type:"error",title:"Remove item?",message:"Please confirm this cart action."}),e=document.querySelector("[data-cart-action-popup]")),!e)return;const n=e.querySelector("[data-cart-popup-title]"),a=e.querySelector("[data-cart-popup-message]"),o=e.querySelector("[data-cart-popup-icon]"),r=e.querySelector("[data-cart-popup-actions]");if(e.dataset.type="error",n&&(n.textContent="Remove from your cart?"),a&&(a.textContent="This item will be removed from your shopping cart."),o&&(o.innerHTML='<i class="fa-solid fa-trash-can" aria-hidden="true"></i>'),r){r.innerHTML="";const c=document.createElement("button");c.type="button",c.className="cart-action-popup-button cart-action-popup-button-secondary",c.textContent="Keep Item",c.addEventListener("click",k);const i=document.createElement("button");i.type="button",i.className="cart-action-popup-button cart-action-popup-button-primary",i.textContent="Remove Item",i.addEventListener("click",function(){k(),typeof t.onConfirm=="function"&&t.onConfirm()}),r.appendChild(c),r.appendChild(i)}e.classList.add("is-open"),e.setAttribute("aria-hidden","false"),document.body.classList.add("cart-action-popup-open"),window.setTimeout(function(){r?.querySelector(".cart-action-popup-button-secondary")?.focus()},30)}function gt({removeButton:t,form:e,removeUrl:n,cartItem:a,cartKey:o}){t.disabled=!0,a&&a.classList.add("removing"),fetch(n,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-TOKEN":T(),"X-Requested-With":"XMLHttpRequest",Accept:"application/json"},body:JSON.stringify({cart_key:o})}).then(async function(r){const c=await r.json().catch(function(){return{}});if(!r.ok||c.success===!1)throw new Error(c.message||"Unable to remove cart item.");return c}).then(function(r){if(a&&a.remove(),B(r,document.createElement("div")),O(r.cart_count),g({type:"success",title:"Removed from your cart",message:r.message||"The item was removed successfully."}),!document.querySelectorAll(".cart-item, [data-cart-item]").length){const i=document.querySelector(".cart-page, .cart-container, [data-cart-container]"),s=document.querySelector(".empty-cart, [data-empty-cart]");i&&i.classList.add("cart-is-empty"),h(s)}}).catch(function(r){console.error(r),g({type:"error",title:"Unable to remove item",message:r?.message||"Something went wrong while removing this item from your cart."})}).finally(function(){t.disabled=!1,a&&a.classList.remove("removing")})}const j=document.querySelector(".navigation-cover"),D=document.querySelector(".navbar");function P(){if(!j||!D)return;const e=(document.documentElement.scrollHeight-window.innerHeight)*.05;window.scrollY>=e?(j.classList.add("active"),D.classList.add("scrolled")):(j.classList.remove("active"),D.classList.remove("scrolled"))}window.addEventListener("scroll",P,{passive:!0}),window.addEventListener("resize",P),P(),document.querySelectorAll(".moving-circle").forEach(t=>{t.addEventListener("mousemove",e=>{const n=t.getBoundingClientRect(),a=e.clientX-n.left-n.width/2,o=e.clientY-n.top-n.height/2,r=a/4,c=o/4;t.style.transform=`translate3d(${r}px, ${c}px, 0) scale(1.15)`}),t.addEventListener("mouseleave",()=>{t.style.transform="translate3d(0, 0, 0) scale(1)"})}),document.querySelectorAll(".btn-style-1, .btn-style-2, .btn-style-3, .product-popup-close, .quick-view-cart-button").forEach(t=>{const e=t.querySelector(".button-text");e&&(t.addEventListener("mousemove",n=>{const a=t.getBoundingClientRect(),o=n.clientX-a.left-a.width/2,r=n.clientY-a.top-a.height/2;e.style.transform=`translate3d(${o/6}px, ${r/6}px, 0) scale(1.12)`}),t.addEventListener("mouseleave",()=>{e.style.transform="translate3d(0, 0, 0) scale(1)"}))});const yt=document.querySelector(".menu-button"),vt=document.querySelector(".mega-menu");yt.addEventListener("click",function(){vt.classList.toggle("active")});function kt(){document.querySelector(".menu-button").classList.toggle("open")}document.addEventListener("DOMContentLoaded",function(){const t=document.querySelectorAll(".blog-posts .about-info-parent, .recent-projects .list-item,.blog-posts .card-parent,.products-grid .card-parent "),e=new IntersectionObserver((n,a)=>{n.forEach(o=>{if(o.isIntersecting){const r=Array.from(t).indexOf(o.target);setTimeout(()=>{o.target.classList.add("in-view")},r*150),a.unobserve(o.target)}})},{threshold:.2});t.forEach(n=>e.observe(n))});function ht(){const t=document.querySelectorAll(".product-tabs");t.length&&t.forEach(function(e){if(e.dataset.tabsInitialized==="true")return;e.dataset.tabsInitialized="true";const n=e.querySelectorAll(".tab-btn"),a=e.querySelectorAll(".tab-content");!n.length||!a.length||(n.forEach(function(o){o.setAttribute("role","tab");const r=o.dataset.tab,c=r?e.querySelector("#"+CSS.escape(r)):null;o.setAttribute("aria-selected",o.classList.contains("active")?"true":"false"),r&&o.setAttribute("aria-controls",r),c&&c.setAttribute("role","tabpanel"),o.addEventListener("click",function(){const i=o.dataset.tab;if(!i)return;const s=e.querySelector("#"+CSS.escape(i));s&&(n.forEach(function(u){u.classList.remove("active"),u.setAttribute("aria-selected","false")}),a.forEach(function(u){u.classList.remove("active"),u.setAttribute("aria-hidden","true")}),o.classList.add("active"),o.setAttribute("aria-selected","true"),s.classList.add("active"),s.setAttribute("aria-hidden","false"))})}),a.forEach(function(o){o.setAttribute("aria-hidden",o.classList.contains("active")?"false":"true")}))})}function bt(){document.addEventListener("submit",function(t){const e=t.target.closest(".product-form, .single-product-form, .quick-view-cart-form, [data-product-form]");if(!e)return;const n=b(e);if(!n.length)return;const a=Array.from(e.querySelectorAll(".product-option-select")),o=a.find(function(f){return!String(f.value||"").trim()}),r=e.querySelector('input[name="variant_id"], [data-selected-variant]'),c=String(r?.value||"").trim(),i=e.querySelector(".variant-message, [data-variant-message]");if(!a.length||o||!c){t.preventDefault(),t.stopImmediatePropagation(),r&&(r.value=""),i&&(i.textContent="Please select one value from every option.",i.classList.remove("success"),i.classList.add("error"),h(i)),(o?.closest(".product-option-group")||e.querySelector(".product-option-group"))?.scrollIntoView({behavior:"smooth",block:"center"}),o?.focus();return}const s=n.find(function(f){return String(f.id)===c});if(!s){t.preventDefault(),t.stopImmediatePropagation(),r.value="",i&&(i.textContent="The selected option combination is unavailable.",i.classList.remove("success"),i.classList.add("error"),h(i));return}const u={};a.forEach(function(f){const y=f.dataset.optionId||f.name.match(/\[(.*?)\]/)?.[1];y&&(u[String(y)]=String(f.value))});const d=M(s);if(!(Object.keys(u).length===Object.keys(d).length&&Object.entries(u).every(function([f,y]){return String(d[f]||"")===String(y)}))){t.preventDefault(),t.stopImmediatePropagation(),r.value="",i&&(i.textContent="Please select a valid value from every option.",i.classList.remove("success"),i.classList.add("error"),h(i));return}S(s)||(t.preventDefault(),t.stopImmediatePropagation(),i&&(i.textContent="This selected option is currently out of stock and cannot be purchased.",i.classList.remove("success"),i.classList.add("error"),h(i)))},!0)}document.addEventListener("DOMContentLoaded",function(){const t=document.querySelector(".review-rating-field");if(!t)return;const e=t.querySelector('input[name="rating"]'),n=t.querySelectorAll(".review-rating-option");n.forEach(function(o){o.addEventListener("click",function(){const r=o.dataset.ratingValue||"";e.value=r,n.forEach(function(c){c.classList.remove("active"),c.setAttribute("aria-pressed","false")}),o.classList.add("active"),o.setAttribute("aria-pressed","true")})});const a=t.closest("form");a&&a.addEventListener("submit",function(o){e.value?t.classList.remove("has-error"):(o.preventDefault(),t.classList.add("has-error"),n[0]?.focus())})});function St(){const t=document.querySelector(".review-select");if(t){const e=t.querySelector(".review-select-trigger"),n=t.querySelectorAll(".review-option"),a=document.getElementById("review-rating"),o=document.getElementById("selected-rating-text");e.addEventListener("click",()=>{t.classList.toggle("active")}),n.forEach(r=>{r.addEventListener("click",()=>{n.forEach(c=>c.classList.remove("active")),r.classList.add("active"),a.value=r.dataset.value,o.textContent=r.textContent,t.classList.remove("active")})}),document.addEventListener("click",r=>{t.contains(r.target)||t.classList.remove("active")})}}function qt(){document.addEventListener("DOMContentLoaded",function(){const t=document.getElementById("shop-filter-form");if(t){const l=t.querySelectorAll(".auto-submit-filter");let p=!1;l.forEach(function(f){f.addEventListener("change",function(){p||(p=!0,t.submit())})})}const e=document.getElementById("sort-form"),n=document.getElementById("sort-trigger"),a=document.getElementById("sort-options"),o=document.getElementById("sort-value"),r=document.getElementById("sort-label");if(!e||!n||!a||!o||!r)return;const c=a.querySelectorAll(".sort-option");function i(){a.classList.add("active"),n.classList.add("active"),n.setAttribute("aria-expanded","true")}function s(){a.classList.remove("active"),n.classList.remove("active"),n.setAttribute("aria-expanded","false")}function u(){a.classList.contains("active")?s():i()}function d(l){const p=l.dataset.value||"",f=l.textContent.trim();o.value=p,r.textContent=f,c.forEach(function(y){const q=y===l;y.classList.toggle("active",q),y.setAttribute("aria-selected",q?"true":"false")}),s(),e.submit()}n.addEventListener("click",function(){u()}),c.forEach(function(l){l.addEventListener("click",function(){d(l)}),l.addEventListener("keydown",function(p){(p.key==="Enter"||p.key===" ")&&(p.preventDefault(),d(l))})}),document.addEventListener("click",function(l){e.contains(l.target)||s()}),document.addEventListener("keydown",function(l){l.key==="Escape"&&s()})})}document.addEventListener("DOMContentLoaded",()=>{document.querySelectorAll("[data-filter-dropdown]").forEach(n=>{const a=n.querySelector("[data-filter-trigger]"),o=n.querySelector("[data-filter-options]"),r=n.querySelectorAll("[data-value]"),c=n.querySelector("[data-filter-input]"),i=n.querySelector("[data-filter-label]");if(!a||!o||!c||!i)return;function s(){n.classList.add("active"),a.setAttribute("aria-expanded","true")}function u(){n.classList.remove("active"),a.setAttribute("aria-expanded","false")}function d(){n.classList.contains("active")?u():(e(n),s())}function l(p){const f=p.dataset.value,y=p.dataset.label||p.textContent.trim();c.value=f,i.textContent=y,r.forEach(m=>{const v=m===p;m.classList.toggle("active",v),m.setAttribute("aria-selected",v?"true":"false")}),u(),c.dispatchEvent(new Event("change",{bubbles:!0}));const q=n.closest("form");q&&q.requestSubmit()}a.addEventListener("click",d),r.forEach(p=>{p.addEventListener("click",()=>{l(p)}),p.addEventListener("keydown",f=>{(f.key==="Enter"||f.key===" ")&&(f.preventDefault(),l(p))})})});function e(n=null){document.querySelectorAll("[data-filter-dropdown].active").forEach(a=>{if(a===n)return;a.classList.remove("active"),a.querySelector("[data-filter-trigger]")?.setAttribute("aria-expanded","false")})}document.addEventListener("click",n=>{n.target.closest("[data-filter-dropdown]")||e()}),document.addEventListener("keydown",n=>{n.key==="Escape"&&e()})}),document.addEventListener("DOMContentLoaded",()=>{const t=document.querySelectorAll("[data-multi-select]");function e(n=null){t.forEach(a=>{if(a===n)return;a.classList.remove("active"),a.querySelector("[data-multi-select-trigger]")?.setAttribute("aria-expanded","false")})}t.forEach(n=>{const a=n.querySelector("[data-multi-select-trigger]"),o=n.querySelector("[data-multi-select-label]"),r=n.querySelector("[data-multi-select-search]"),c=Array.from(n.querySelectorAll(".filter-select-option")),i=Array.from(n.querySelectorAll("[data-multi-select-checkbox]")),s=n.querySelector("[data-select-all]"),u=n.querySelector("[data-clear-all]"),d=n.querySelector("[data-multi-select-empty]");if(!a||!o)return;const l=o.textContent.trim().includes("selected")?null:o.textContent.trim();function p(){const m=i.filter(v=>v.checked);if(!m.length){o.textContent=l||"Select options";return}if(m.length===1){const C=m[0].closest(".filter-select-option")?.querySelector(".filter-option-label");o.textContent=C?.textContent.trim()||"1 selected";return}o.textContent=`${m.length} selected`}function f(){if(!r)return;const m=r.value.trim().toLowerCase();let v=0;c.forEach(C=>{const H=(C.dataset.searchText||C.textContent.trim().toLowerCase()).includes(m);C.hidden=!H,H&&v++}),d&&(d.hidden=v!==0)}function y(){e(n),n.classList.add("active"),a.setAttribute("aria-expanded","true"),window.setTimeout(()=>{r?.focus()},50)}function q(){n.classList.remove("active"),a.setAttribute("aria-expanded","false")}a.addEventListener("click",()=>{n.classList.contains("active")?q():y()}),i.forEach(m=>{m.addEventListener("change",p)}),r?.addEventListener("input",f),s?.addEventListener("click",()=>{c.forEach(m=>{if(m.hidden)return;const v=m.querySelector("[data-multi-select-checkbox]");v&&(v.checked=!0)}),p()}),u?.addEventListener("click",()=>{i.forEach(m=>{m.checked=!1}),p()})}),document.addEventListener("click",n=>{n.target.closest("[data-multi-select]")||e()}),document.addEventListener("keydown",n=>{n.key==="Escape"&&e()})})})();

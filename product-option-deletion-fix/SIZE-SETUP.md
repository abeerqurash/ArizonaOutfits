# Standard and custom sizes

1. Open Admin → Products → Create or Edit → Product Options & Variants.
2. Click New Option. Name it **Size**, choose **Select**, and save. Reuse the existing Size attribute if it already exists.
3. Click Add Value beside Size. Add XS, S, M, L, XL and any other sizes you sell. Add **Custom** as another value when custom tailoring is available. The optional internal value can be `custom`; the visible label should be Custom.
4. Enable Size and check only the values available for this product. Click Generate Variants. If Color is also enabled, this creates every selected size/color combination.
5. Enter each variant's unique SKU, price and available stock. Custom also needs a variant with a price and stock greater than zero so customers can purchase it. Save the product.

## Customer flow

After installing the custom-sizing files from the earlier storefront update, selecting Custom on the product page or quick view reveals five required measurements in inches: Chest, Waist, Shoulder, Sleeve length, and Jacket/body length. Each must be numeric and between 0.1 and 200.

The customer enters the measurements, adds the item to the cart, reviews its size and measurements, and proceeds to checkout. Identical product/variant/measurement selections merge into one cart line; different measurements create separate lines. Both share the variant's stock limit.

On order placement, measurements are saved with the order item's options. The customer opens Dashboard → Orders → the order details to see the selected size and measurements. The administrator opens Admin → Orders → the order details; measurements also appear through the existing order-option invoice display. The size chart is a reference image and does not fill these fields automatically.

## Current installation requirement

The live CartController and CheckoutController inspected during this update do not contain the custom-measurement integration. Creating a Custom value alone does not complete the workflow. Install the earlier complete storefront feature update using `storefront-feature-update/FILES.md` and `INSTALL.txt`; it includes the product/quick-view fields, cart and checkout controllers, measurement service, cart/checkout summaries and supporting files. Back up first and install the related files together. This deletion update does not overwrite those commerce controllers.

## Deleting unused attributes and values

Create and Edit now offer Delete attribute and a Delete button beside each value. Uncheck an unused selection before deleting it. Deletion is global: the attribute/value is removed from the shared catalog, not just the open product.

An attribute with no product assignments can be deleted whether empty or containing unused values. An individual unused value can be deleted even if another value in the same attribute has products. Any product assignment or saved variant reference blocks deletion. Stock of zero still counts as a used variant; it is not the same as having zero assigned products.

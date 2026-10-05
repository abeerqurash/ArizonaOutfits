Restored the original dashboard primary purple (#635bff); success and alert styles remain separate.

Fixed duplicated controls by recognizing existing custom-select implementations and hiding their backing native inputs. Calendars now use one visible control with the current value. Added Inventory Alerts style short banners across dashboard pages, responsive store-settings fields, purple file chooser buttons, and a single keyword input boundary.

Validated 15 rendered pages at desktop and mobile widths: all Configuration menus, payment filters, Inventory Alerts, products listing, product creation and populated product editing. Checks found one banner per page, no visible backing native controls, no horizontal overflow and no JavaScript exceptions. Browser interaction checks cover dropdown submitted values, date/time selection, keyboard dismissal and responsive controls. The populated variation image input accepts a selected PNG and uses the purple chooser styling.

These are three replacement files on top of the previous product/dashboard package. No live sources or live database were changed. Offline rendering uses fixture data and does not exercise remote CDN chart/font assets.

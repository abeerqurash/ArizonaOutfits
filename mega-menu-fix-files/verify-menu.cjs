const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
function element() {
    const classes = new Set();
    return {
        dataset: {}, attrs: {}, listeners: {},
        classList: {
            contains: name => classes.has(name),
            toggle(name, state) { state = state === undefined ? !classes.has(name) : state; state ? classes.add(name) : classes.delete(name); }
        },
        setAttribute(name, value) { this.attrs[name] = value; },
        addEventListener(name, fn, capture = false) { (this.listeners[name] ||= []).push({fn, capture}); },
        focus() { this.focused = true; }
    };
}
for (const page of ['Home', 'Shop', 'Favorites']) {
    const trigger = element(), panel = element();
    const navbar = { querySelector: selector => selector === '.menu-button' ? trigger : panel, contains: node => node === trigger || node === panel };
    const document = { listeners: {}, querySelector: () => navbar, addEventListener(name, fn) { this.listeners[name] = fn; } };
    vm.runInNewContext(fs.readFileSync(__dirname + '/menu-handler.js', 'utf8'), {document});
    trigger.addEventListener('click', () => panel.classList.toggle('active')); // old page handler
    trigger.addEventListener('click', () => trigger.classList.toggle('open')); // old inline equivalent
    function click() {
        const event = { stopped: false, preventDefault() {}, stopImmediatePropagation() { this.stopped = true; } };
        for (const handler of [...trigger.listeners.click].sort((a, b) => Number(b.capture) - Number(a.capture))) {
            handler.fn(event);
            if (event.stopped) break;
        }
    }
    click();
    assert(panel.classList.contains('active') && trigger.classList.contains('open'));
    assert.equal(trigger.attrs['aria-expanded'], 'true');
    click();
    assert(!panel.classList.contains('active') && !trigger.classList.contains('open'));
    click(); document.listeners.keydown({key: 'Escape'});
    assert(!panel.classList.contains('active') && trigger.focused);
    click(); document.listeners.click({target: {}});
    assert(!panel.classList.contains('active') && !trigger.classList.contains('open'));
    console.log('PASS: ' + page + ' shared open/close, legacy-handler isolation, Escape and outside click.');
}

const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '..', '..', 'swipe.js'), 'utf8');

function createGesture(documentScope = false) {
  const handlers = {};
  const directions = [];
  const scope = {
    style: {},
    addEventListener(name, handler) { handlers[name] = handler; }
  };
  const attach = vm.runInNewContext(`${source}\nattachSwipeNavigation`, {
    document: documentScope ? scope : { addEventListener() {} }
  });
  attach(scope, direction => directions.push(direction));

  function send(type, x, y = 0, options = {}) {
    const event = {
      pointerType: 'touch', button: 0, pointerId: 1, clientX: x, clientY: y,
      target: { isContentEditable: false, closest: () => null },
      prevented: false,
      preventDefault() { this.prevented = true; },
      ...options
    };
    handlers[type](event);
    return event;
  }
  return { scope, directions, send };
}

test('horizontal gestures navigate in both directions and reset after committing', () => {
  const gesture = createGesture();
  assert.equal(gesture.scope.style.touchAction, 'pan-y');
  for (const end of [-76, 76]) {
    gesture.send('pointerdown', 0);
    assert.equal(gesture.send('pointermove', end).prevented, true);
    gesture.send('pointerup', end);
  }
  assert.deepEqual(gesture.directions, ['forward', 'backward']);
  gesture.send('pointerup', -200);
  assert.equal(gesture.directions.length, 2);
});

test('commit requires more than 75 pixels and an established horizontal lock', () => {
  for (const end of [15, 16, 74, 75]) {
    const gesture = createGesture();
    gesture.send('pointerdown', 0);
    gesture.send('pointermove', end);
    gesture.send('pointerup', end);
    assert.deepEqual(gesture.directions, []);
  }
  const gesture = createGesture();
  gesture.send('pointerdown', 0);
  gesture.send('pointerup', 100);
  assert.deepEqual(gesture.directions, []);
});

test('vertical scrolling and vertically locked diagonal gestures never navigate', () => {
  const gesture = createGesture();
  gesture.send('pointerdown', 0);
  assert.equal(gesture.send('pointermove', 8, 20).prevented, false);
  gesture.send('pointermove', 200, 30);
  gesture.send('pointerup', 200, 30);
  assert.deepEqual(gesture.directions, []);
});

test('links, controls, inline SVG controls, no-swipe regions and editable text are excluded', () => {
  for (const selector of ['a:not([data-swipe-link])', 'button', 'input', 'textarea', 'select', 'canvas', 'table',
    '[onclick]', "[role='button']", '.no-swipe', 'editable']) {
    const gesture = createGesture();
    gesture.send('pointerdown', 0, 0, {
      target: {
        isContentEditable: selector === 'editable',
        closest: selectors => selectors.split(', ').includes(selector) ? {} : null
      }
    });
    gesture.send('pointermove', 100);
    gesture.send('pointerup', 100);
    assert.deepEqual(gesture.directions, [], selector);
  }
});

test('logo taps preserve native clicks and horizontal drags suppress only their follow-up click', () => {
  const gesture = createGesture();
  const captured = [];
  gesture.scope.setPointerCapture = id => captured.push(id);
  const target = {
    closest: selector => selector === 'a[data-swipe-link]' ? {} : null
  };
  gesture.send('pointerdown', 0, 0, { target });
  assert.deepEqual(captured, []);
  gesture.send('pointerup', 0);
  assert.equal(gesture.send('click', 0, 0, { detail: 1 }).prevented, false);
  assert.deepEqual(gesture.directions, []);

  for (const distance of [-100, 100, 30]) {
    gesture.send('pointerdown', 0, 0, { target });
    gesture.send('pointermove', distance);
    gesture.send('pointerup', distance);
    assert.equal(gesture.send('click', distance, 0, { detail: 1, stopPropagation() {} }).prevented, true);
    assert.equal(gesture.send('click', distance, 0, { detail: 1 }).prevented, false);
  }
  assert.deepEqual(gesture.directions, ['forward', 'backward']);
  assert.equal(captured.length, 3);
});

test('logo swipes do not suppress a new tap, keyboard click or cancelled gesture', () => {
  const gesture = createGesture();
  const target = { closest: selector => selector === 'a[data-swipe-link]' ? {} : null };
  gesture.send('pointerdown', 0, 0, { target });
  gesture.send('pointermove', -100);
  gesture.send('pointerup', -100);
  assert.equal(gesture.send('click', 0, 0, { detail: 0 }).prevented, false);
  gesture.send('pointerdown', 0, 0, { target });
  gesture.send('pointerup', 0);
  assert.equal(gesture.send('click', 0, 0, { detail: 1 }).prevented, false);
  gesture.send('pointerdown', 0, 0, { target });
  gesture.send('pointermove', -100);
  gesture.send('pointercancel', -100);
  assert.equal(gesture.send('click', 0, 0, { detail: 1 }).prevented, false);
});

test('secondary mouse buttons and other pointer IDs cannot complete a gesture', () => {
  const gesture = createGesture();
  gesture.send('pointerdown', 0, 0, { pointerType: 'mouse', button: 2 });
  gesture.send('pointermove', 100);
  gesture.send('pointerup', 100);
  assert.deepEqual(gesture.directions, []);
  gesture.send('pointerdown', 0);
  assert.equal(gesture.send('pointermove', 100, 0, { pointerId: 2 }).prevented, false);
  gesture.send('pointerup', 100, 0, { pointerId: 2 });
  assert.deepEqual(gesture.directions, []);
  gesture.send('pointermove', -100);
  gesture.send('pointerup', -100);
  assert.deepEqual(gesture.directions, ['forward']);
});

test('cancellation and lost capture reset gestures', () => {
  for (const event of ['pointercancel', 'lostpointercapture']) {
    const gesture = createGesture();
    gesture.send('pointerdown', 0);
    gesture.send('pointermove', 100);
    gesture.send(event, 100);
    gesture.send('pointerup', 100);
    assert.deepEqual(gesture.directions, []);
  }
});

test('document-scoped navigation remains supported without changing document styles', () => {
  const gesture = createGesture(true);
  assert.equal(gesture.scope.style.touchAction, undefined);
  gesture.send('pointerdown', 0);
  gesture.send('pointermove', -100);
  gesture.send('pointerup', -100);
  assert.deepEqual(gesture.directions, ['forward']);
});

test('pointer capture tolerates ended pointers but surfaces unexpected errors', () => {
  const gesture = createGesture();
  gesture.scope.setPointerCapture = () => { throw { name: 'NotFoundError' }; };
  assert.doesNotThrow(() => gesture.send('pointerdown', 0));
  gesture.scope.setPointerCapture = () => { throw new Error('Capture failed'); };
  assert.throws(() => gesture.send('pointerdown', 0), /Capture failed/);
});

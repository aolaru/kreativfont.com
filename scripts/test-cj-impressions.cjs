const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

class Request {
    constructor() { this.withCredentials = false; }
    open(...args) { this.openArgs = args; return 'opened'; }
    send(...args) {
        this.sent = { credentials: this.withCredentials, args };
        return 'sent';
    }
}

const context = vm.createContext({
    window: { XMLHttpRequest: Request, WeakSet },
    WeakSet,
    URL,
    document: { baseURI: 'https://kreativfont.com/' },
});
const source = fs.readFileSync(path.join(__dirname, '../js/kreativ-cj-impressions.js'), 'utf8');
vm.runInContext(source, context);

const payload = '{"publisherId":6184495,"websiteId":100743026,"urls":[],"impressions":[]}';
const request = new Request();
assert.equal(request.open('POST', 'https://origin.cjtrack.me/pageImpression', true), 'opened');
request.withCredentials = true;
assert.equal(request.send(payload), 'sent');
assert.deepEqual(request.sent, { credentials: false, args: [payload] });

for (const [method, url] of [
    ['GET', 'https://origin.cjtrack.me/pageImpression'],
    ['POST', 'https://origin.cjtrack.me/other'],
    ['POST', 'https://origin.cjtrack.me/pageImpression?other=1'],
    ['POST', 'https://origin.cjtrack.me.example.com/pageImpression'],
    ['POST', 'https://kreativfont.com/wp-admin/admin-ajax.php'],
    ['POST', 'http://origin.cjtrack.me/pageImpression'],
]) {
    // Reuse the same instance to ensure it cannot retain the CJ-only override.
    request.open(method, url, true);
    request.withCredentials = true;
    request.send(payload);
    assert.equal(request.sent.credentials, true, `${method} ${url}`);
}

const open = Request.prototype.open;
const send = Request.prototype.send;
vm.runInContext(source, context);
assert.equal(Request.prototype.open, open, 'Initialization must be idempotent');
assert.equal(Request.prototype.send, send, 'Initialization must be idempotent');
console.log('PASS: CJ impression credentials, unchanged payload, unrelated requests, request reuse, and duplicate initialization');

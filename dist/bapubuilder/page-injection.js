! function(t) {
    var e = {};

    function n(r) {
        if (e[r]) return e[r].exports;
        var o = e[r] = {
            i: r,
            l: !1,
            exports: {}
        };
        return t[r].call(o.exports, o, o.exports, n), o.l = !0, o.exports
    }
    n.m = t, n.c = e, n.d = function(t, e, r) {
        n.o(t, e) || Object.defineProperty(t, e, {
            configurable: !1,
            enumerable: !0,
            get: r
        })
    }, n.n = function(t) {
        var e = t && t.__esModule ? function() {
            return t.default
        } : function() {
            return t
        };
        return n.d(e, "a", e), e
    }, n.o = function(t, e) {
        return Object.prototype.hasOwnProperty.call(t, e)
    }, n.p = "", n(n.s = 15)
}({
    15: function(t, e, n) {
        t.exports = n(16)
    },
    16: function(t, e) {
        window.addEventListener("keydown", function(t) {
            if (t.ctrlKey && "s" === t.key) return t.preventDefault(), !1
        }), window.parent.postMessage("page-loaded", "*"), document.addEventListener("touchstart", function(t) {
            window.parent.postMessage("touch-start", "*")
        })
    }
});
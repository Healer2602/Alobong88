/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.1.1 (2019-10-28)
 */
!function () {
    "use strict";

    function e() {
    }

    function o(e) {
        return function () {
            return e
        }
    }

    function t() {
        return u
    }

    var r, n = tinymce.util.Tools.resolve("tinymce.PluginManager"),
        d = function () {
            return (d = Object.assign || function (e) {
                for (var t, r = 1, n = arguments.length; r < n; r++) {
                    for (var i in t = arguments[r]) {
                        Object.prototype.hasOwnProperty.call(t, i) && (e[i] = t[i]);
                    }
                }
                return e
            }).apply(this, arguments)
        }, a = o(!1), c = o(!0), u = (r = {
            fold: function (e, t) {
                return e()
            },
            is: a,
            isSome: a,
            isNone: c,
            getOr: l,
            getOrThunk: s,
            getOrDie: function (e) {
                throw new Error(e || "error: getOrDie called on none.")
            },
            getOrNull: o(null),
            getOrUndefined: o(undefined),
            or: l,
            orThunk: s,
            map: t,
            each: e,
            bind: t,
            exists: a,
            forall: c,
            filter: t,
            equals: i,
            equals_: i,
            toArray: function () {
                return []
            },
            toString: o("none()")
        }, Object.freeze && Object.freeze(r), r);

    function i(e) {
        return e.isNone()
    }

    function s(e) {
        return e()
    }

    function l(e) {
        return e
    }

    function m(t) {
        return function (e) {
            return function (e) {
                if (null === e) {
                    return "null";
                }
                var t = typeof e;
                return "object" == t && (Array.prototype.isPrototypeOf(e) || e.constructor && "Array" === e.constructor.name) ? "array" : "object" == t && (String.prototype.isPrototypeOf(e) || e.constructor && "String" === e.constructor.name) ? "string" : t
            }(e) === t
        }
    }

    function f(e, t) {
        for (var r = 0, n = e.length; r < n; r++) {
            t(e[r], r)
        }
    }

    function h(e, t) {
        return G(e, t) ? E(e[t]) : D()
    }

    function p(t) {
        return function (e) {
            return e ? function (e) {
                return e.replace(/px$/, "")
            }(e.style[t]) : ""
        }
    }

    function g(r) {
        return function (e, t) {
            e && (e.style[r] = function (e) {
                return /^[0-9.]+$/.test(e) ? e + "px" : e
            }(t))
        }
    }

    function v(e, t) {
        if (e) {
            for (var r = 0; r < e.length; r++) {
                if (-1 !== t.indexOf(e[r].filter)) {
                    return e[r]
                }
            }
        }
    }

    function b(e) {
        return ae.getAttrib(e, "data-ephox-embed-iri")
    }

    function w(e, t) {
        return function (e) {
            var t = ae.createFragment(e);
            return "" !== b(t.firstChild)
        }(t) ? function (e) {
            var t = ae.createFragment(e).firstChild;
            return {
                type: "ephox-embed-iri",
                source1: b(t),
                source2: "",
                poster: "",
                width: oe.getMaxWidth(t),
                height: oe.getMaxHeight(t)
            }
        }(t) : function (n, e) {
            var i = {};
            return ne({
                validate: !1,
                allow_conditional_comments: !0,
                start: function (e, t) {
                    if (i.source1 || "param" !== e || (i.source1 = t.map.movie), "iframe" !== e && "object" !== e && "embed" !== e && "video" !== e && "audio" !== e || (i.type || (i.type = e), i = re.extend(t.map, i)), "script" === e) {
                        var r = v(n, t.map.src);
                        if (!r) {
                            return;
                        }
                        i = {
                            type: "script",
                            source1: t.map.src,
                            width: String(r.width),
                            height: String(r.height)
                        }
                    }
                    "source" === e && (i.source1 ? i.source2 || (i.source2 = t.map.src) : i.source1 = t.map.src), "img" !== e || i.poster || (i.poster = t.map.src)
                }
            }).parse(e), i.source1 = i.source1 || i.src || i.data, i.source2 = i.source2 || "", i.poster = i.poster || "", i
        }(e, t)
    }

    function y(e, t) {
        var r, n, i, o;
        for (r in t) {
            if (i = "" + t[r], e.map[r]) {
                for (n = e.length; n--;) {
                    (o = e[n]).name === r && (i ? (e.map[r] = i, o.value = i) : (delete e.map[r], e.splice(n, 1)));
                }
            }
            else {
                i && (e.push({
                    name: r,
                    value: i
                }), e.map[r] = i)
            }
        }
    }

    function x(e, t) {
        var r = me.createFragment(e).firstChild;
        return oe.setMaxWidth(r, t.width), oe.setMaxHeight(r, t.height), function (e) {
            var t = se();
            return ne(t).parse(e), t.getContent()
        }(r.outerHTML)
    }

    function j(r, e) {
        var n = re.extend({}, e);
        if (!n.source1 && (re.extend(n, w(J(r), n.embed)), !n.source1)) {
            return "";
        }
        n.source2 || (n.source2 = ""), n.poster || (n.poster = ""), n.source1 = r.convertURL(n.source1, "source"), n.source2 = r.convertURL(n.source2, "source"), n.source1mime = ue(n.source1), n.source2mime = ue(n.source2), n.poster = r.convertURL(n.poster, "poster");
        var t = function (t) {
            var e = fe.filter(function (e) {
                return e.regex.test(t)
            });
            return 0 < e.length ? re.extend({}, e[0], {
                url: function (e, t) {
                    for (var r = e.regex.exec(t), n = e.url, i = function (e) {
                        n = n.replace("$" + e, function () {
                            return r[e] ? r[e] : ""
                        })
                    }, o = 0; o < r.length; o++) {
                        i(o);
                    }
                    return n.replace(/\?$/, "")
                }(e[0], t)
            }) : null
        }(n.source1);
        if (t && (n.source1 = t.url, n.type = t.type, n.allowFullscreen = t.allowFullscreen, n.width = n.width || String(t.w), n.height = n.height || String(t.h)), n.embed) {
            return de(n.embed, n, !0);
        }
        var i = v(J(r), n.source1);
        i && (n.type = "script", n.width = String(i.width), n.height = String(i.height));
        var o = K(r), a = Q(r);
        return n.width = n.width || "300", n.height = n.height || "150", re.each(n, function (e, t) {
            n[t] = r.dom.encode("" + e)
        }), "iframe" === n.type ? function (e) {
            var t = e.allowFullscreen ? ' allowFullscreen="1"' : "";
            return '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="' + e.source1 + '" width="' + e.width + '" height="' + e.height + '"' + t + "></iframe></div>"
        }(n) : "application/x-shockwave-flash" === n.source1mime ? function (e) {
            var t = '<object data="' + e.source1 + '" width="' + e.width + '" height="' + e.height + '" type="application/x-shockwave-flash">';
            return e.poster && (t += '<img src="' + e.poster + '" width="' + e.width + '" height="' + e.height + '" />'), t += "</object>"
        }(n) : -1 !== n.source1mime.indexOf("audio") ? function (e, t) {
            return t ? t(e) : '<audio controls="controls" src="' + e.source1 + '">' + (e.source2 ? '\n<source src="' + e.source2 + '"' + (e.source2mime ? ' type="' + e.source2mime + '"' : "") + " />\n" : "") + "</audio>"
        }(n, o) : "script" === n.type ? function (e) {
            return '<script src="' + e.source1 + '"><\/script>'
        }(n) : function (e, t) {
            return t ? t(e) : '<video width="' + e.width + '" height="' + e.height + '"' + (e.poster ? ' poster="' + e.poster + '"' : "") + ' controls="controls">\n<source src="' + e.source1 + '"' + (e.source1mime ? ' type="' + e.source1mime + '"' : "") + " />\n" + (e.source2 ? '<source src="' + e.source2 + '"' + (e.source2mime ? ' type="' + e.source2mime + '"' : "") + " />\n" : "") + "</video>"
        }(n, a)
    }

    function O(t) {
        return function (e) {
            return j(t, e)
        }
    }

    function A(e) {
        var r = q(e, {
            source1: e.source1.value,
            source2: h(e, "source2").bind(function (e) {
                return h(e, "value")
            }).getOr(""),
            poster: h(e, "poster").bind(function (e) {
                return h(e, "value")
            }).getOr("")
        });
        return h(e, "dimensions").each(function (e) {
            f(["width", "height"], function (t) {
                h(e, t).each(function (e) {
                    return r[t] = e
                })
            })
        }), r
    }

    function S(e) {
        var n = q(e, {
            source1: {value: h(e, "source1").getOr("")},
            source2: {value: h(e, "source2").getOr("")},
            poster: {value: h(e, "poster").getOr("")}
        });
        return f(["width", "height"], function (r) {
            h(e, r).each(function (e) {
                var t = n.dimensions || {};
                t[r] = e, n.dimensions = t
            })
        }), n
    }

    function _(r) {
        return function (e) {
            var t = e && e.msg ? "Media embed handler error: " + e.msg : "Media embed handler threw unknown error.";
            r.notificationManager.open({type: "error", text: t})
        }
    }

    function C(e, t) {
        return w(J(e), t)
    }

    function M(i, o) {
        return function (e) {
            if (N(e.url) && 0 < e.url.trim().length) {
                var t = e.html, r = C(o, t),
                    n = d(d({}, r), {source1: e.url, embed: t});
                i.setData(S(n))
            }
        }
    }

    function F(e, t) {
        var r = e.dom.select("img[data-mce-object]");
        e.insertContent(t), function (e, t) {
            for (var r = e.dom.select("img[data-mce-object]"), n = 0; n < t.length; n++) {
                for (var i = r.length - 1; 0 <= i; i--) {
                    t[n] === r[i] && r.splice(i, 1);
                }
            }
            e.selection.select(r[0])
        }(e, r), e.nodeChanged()
    }

    function P(e, t) {
        var r, n = t.name;
        return (r = new ye("img", 1)).shortEnded = !0, Oe(e, t, r), r.attr({
            width: t.attr("width") || "300",
            height: t.attr("height") || ("audio" === n ? "30" : "150"),
            style: t.attr("style"),
            src: xe.transparentSrc,
            "data-mce-object": n,
            "class": "mce-object mce-object-" + n
        }), r
    }

    function k(e, t) {
        var r, n, i, o = t.name;
        return (r = new ye("span", 1)).attr({
            contentEditable: "false",
            style: t.attr("style"),
            "data-mce-object": o,
            "class": "mce-preview-object mce-object-" + o
        }), Oe(e, t, r), (n = new ye(o, 1)).attr({
            src: t.attr("src"),
            allowfullscreen: t.attr("allowfullscreen"),
            style: t.attr("style"),
            "class": t.attr("class"),
            width: t.attr("width"),
            height: t.attr("height"),
            frameborder: "0"
        }), (i = new ye("span", 1)).attr("class", "mce-shim"), r.append(n), r.append(i), r
    }

    function T(e) {
        for (; e = e.parent;) {
            if (e.attr("data-ephox-embed-iri") || (void 0, (t = e.attr("class")) && /\btiny-pageembed\b/.test(t))) {
                return !0;
            }
        }
        var t;
        return !1
    }

    var $, z = function (r) {
            function e() {
                return i
            }

            function t(e) {
                return e(r)
            }

            var n = o(r), i = {
                fold: function (e, t) {
                    return t(r)
                },
                is: function (e) {
                    return r === e
                },
                isSome: c,
                isNone: a,
                getOr: n,
                getOrThunk: n,
                getOrDie: n,
                getOrNull: n,
                getOrUndefined: n,
                or: e,
                orThunk: e,
                map: function (e) {
                    return z(e(r))
                },
                each: function (e) {
                    e(r)
                },
                bind: t,
                exists: t,
                forall: t,
                filter: function (e) {
                    return e(r) ? i : u
                },
                toArray: function () {
                    return [r]
                },
                toString: function () {
                    return "some(" + r + ")"
                },
                equals: function (e) {
                    return e.is(r)
                },
                equals_: function (e, t) {
                    return e.fold(a, function (e) {
                        return t(r, e)
                    })
                }
            };
            return i
        }, D = t, E = function (e) {
            return null === e || e === undefined ? u : z(e)
        }, N = m("string"), U = m("array"), R = m("function"),
        L = Array.prototype.slice, W = Array.prototype.push,
        H = (R(Array.from) && Array.from, function (e) {
            function t() {
                return r
            }

            var r = e;
            return {
                get: t, set: function (e) {
                    r = e
                }, clone: function () {
                    return H(t())
                }
            }
        }), I = Object.prototype.hasOwnProperty, q = ($ = function (e, t) {
            return t
        }, function () {
            for (var e = new Array(arguments.length), t = 0; t < e.length; t++) {
                e[t] = arguments[t];
            }
            if (0 === e.length) {
                throw new Error("Can't merge zero objects");
            }
            for (var r = {}, n = 0; n < e.length; n++) {
                var i = e[n];
                for (var o in i) {
                    I.call(i, o) && (r[o] = $(r[o], i[o]))
                }
            }
            return r
        }), B = Object.hasOwnProperty, G = function (e, t) {
            return B.call(e, t)
        }, J = function (e) {
            return e.getParam("media_scripts")
        }, K = function (e) {
            return e.getParam("audio_template_callback")
        }, Q = function (e) {
            return e.getParam("video_template_callback")
        }, V = function (e) {
            return e.getParam("media_live_embeds", !0)
        }, X = function (e) {
            return e.getParam("media_filter_html", !0)
        }, Y = function (e) {
            return e.getParam("media_url_resolver")
        }, Z = function (e) {
            return e.getParam("media_alt_source", !0)
        }, ee = function (e) {
            return e.getParam("media_poster", !0)
        }, te = function (e) {
            return e.getParam("media_dimensions", !0)
        }, re = tinymce.util.Tools.resolve("tinymce.util.Tools"),
        ne = tinymce.util.Tools.resolve("tinymce.html.SaxParser"),
        ie = tinymce.util.Tools.resolve("tinymce.dom.DOMUtils"), oe = {
            getMaxWidth: p("maxWidth"),
            getMaxHeight: p("maxHeight"),
            setMaxWidth: g("maxWidth"),
            setMaxHeight: g("maxHeight")
        }, ae = ie.DOM, ce = tinymce.util.Tools.resolve("tinymce.util.Promise"),
        ue = function (e) {
            var t = {
                mp3: "audio/mpeg",
                m4a: "audio/x-m4a",
                wav: "audio/wav",
                mp4: "video/mp4",
                webm: "video/webm",
                ogg: "video/ogg",
                swf: "application/x-shockwave-flash"
            }[e.toLowerCase().split(".").pop()];
            return t || ""
        }, se = tinymce.util.Tools.resolve("tinymce.html.Writer"),
        le = tinymce.util.Tools.resolve("tinymce.html.Schema"), me = ie.DOM,
        de = function (e, t, r) {
            return function (e) {
                var t = me.createFragment(e);
                return "" !== me.getAttrib(t.firstChild, "data-ephox-embed-iri")
            }(e) ? x(e, t) : function (e, i, o) {
                var a, c = se(), u = 0;
                return ne({
                    validate: !1,
                    allow_conditional_comments: !0,
                    comment: function (e) {
                        c.comment(e)
                    },
                    cdata: function (e) {
                        c.cdata(e)
                    },
                    text: function (e, t) {
                        c.text(e, t)
                    },
                    start: function (e, t, r) {
                        switch (e) {
                            case"video":
                            case"object":
                            case"embed":
                            case"img":
                            case"iframe":
                                i.height !== undefined && i.width !== undefined && y(t, {
                                    width: i.width,
                                    height: i.height
                                })
                        }
                        if (o) {
                            switch (e) {
                                case"video":
                                    y(t, {
                                        poster: i.poster,
                                        src: ""
                                    }), i.source2 && y(t, {src: ""});
                                    break;
                                case"iframe":
                                    y(t, {src: i.source1});
                                    break;
                                case"source":
                                    if (++u <= 2 && (y(t, {
                                        src: i["source" + u],
                                        type: i["source" + u + "mime"]
                                    }), !i["source" + u])) {
                                        return;
                                    }
                                    break;
                                case"img":
                                    if (!i.poster) {
                                        return;
                                    }
                                    a = !0
                            }
                        }
                        c.start(e, t, r)
                    },
                    end: function (e) {
                        if ("video" === e && o) {
                            for (var t = 1; t <= 2; t++) {
                                if (i["source" + t]) {
                                    var r = [];
                                    r.map = {}, u < t && (y(r, {
                                        src: i["source" + t],
                                        type: i["source" + t + "mime"]
                                    }), c.start("source", r, !0))
                                }
                            }
                        }
                        if (i.poster && "object" === e && o && !a) {
                            var n = [];
                            n.map = {}, y(n, {
                                src: i.poster,
                                width: i.width,
                                height: i.height
                            }), c.start("img", n, !0)
                        }
                        c.end(e)
                    }
                }, le({})).parse(e), c.getContent()
            }(e, t, r)
        }, fe = [{
            regex: /youtu\.be\/([\w\-_\?&=.]+)/i,
            type: "iframe",
            w: 560,
            h: 314,
            url: "//www.youtube.com/embed/$1",
            allowFullscreen: !0
        }, {
            regex: /youtube\.com(.+)v=([^&]+)(&([a-z0-9&=\-_]+))?/i,
            type: "iframe",
            w: 560,
            h: 314,
            url: "//www.youtube.com/embed/$2?$4",
            allowFullscreen: !0
        }, {
            regex: /youtube.com\/embed\/([a-z0-9\?&=\-_]+)/i,
            type: "iframe",
            w: 560,
            h: 314,
            url: "//www.youtube.com/embed/$1",
            allowFullscreen: !0
        }, {
            regex: /vimeo\.com\/([0-9]+)/,
            type: "iframe",
            w: 425,
            h: 350,
            url: "//player.vimeo.com/video/$1?title=0&byline=0&portrait=0&color=8dc7dc",
            allowFullscreen: !0
        }, {
            regex: /vimeo\.com\/(.*)\/([0-9]+)/,
            type: "iframe",
            w: 425,
            h: 350,
            url: "//player.vimeo.com/video/$2?title=0&amp;byline=0",
            allowFullscreen: !0
        }, {
            regex: /maps\.google\.([a-z]{2,3})\/maps\/(.+)msid=(.+)/,
            type: "iframe",
            w: 425,
            h: 350,
            url: '//maps.google.com/maps/ms?msid=$2&output=embed"',
            allowFullscreen: !1
        }, {
            regex: /dailymotion\.com\/video\/([^_]+)/,
            type: "iframe",
            w: 480,
            h: 270,
            url: "//www.dailymotion.com/embed/video/$1",
            allowFullscreen: !0
        }, {
            regex: /dai\.ly\/([^_]+)/,
            type: "iframe",
            w: 480,
            h: 270,
            url: "//www.dailymotion.com/embed/video/$1",
            allowFullscreen: !0
        }], he = {}, pe = function (e, t) {
            var r = Y(e);
            return r ? function (n, i, o) {
                return new ce(function (t, e) {
                    function r(e) {
                        return e.html && (he[n.source1] = e), t({
                            url: n.source1,
                            html: e.html ? e.html : i(n)
                        })
                    }

                    he[n.source1] ? r(he[n.source1]) : o({url: n.source1}, r, e)
                })
            }(t, O(e), r) : function (t, r) {
                return new ce(function (e) {
                    e({html: r(t), url: t.source1})
                })
            }(t, O(e))
        }, ge = function (e) {
            return he.hasOwnProperty(e)
        }, ve = function (n) {
            function i(e) {
                return A(e.getData())
            }

            var e = function (e) {
                var t = e.selection.getNode(), r = function (e) {
                    return e.getAttribute("data-mce-object") || e.getAttribute("data-ephox-embed-iri")
                }(t) ? e.serializer.serialize(t, {selection: !0}) : "";
                return q({embed: r}, w(J(e), r))
            }(n), r = H(e), t = S(e), o = {
                title: "General", name: "general", items: function (e) {
                    for (var t = [], r = 0, n = e.length; r < n; ++r) {
                        if (!U(e[r])) {
                            throw new Error("Arr.flatten item " + r + " was not an array, input: " + e);
                        }
                        W.apply(t, e[r])
                    }
                    return t
                }([[{
                    name: "source1",
                    type: "urlinput",
                    filetype: "media",
                    label: "Source"
                }], te(n) ? [{
                    type: "sizeinput",
                    name: "dimensions",
                    label: "Constrain proportions",
                    constrain: !0
                }] : []])
            }, a = {
                title: "Embed",
                items: [{
                    type: "textarea",
                    name: "embed",
                    label: "Paste your embed code below:"
                }]
            }, c = [];
            Z(n) && c.push({
                name: "source2",
                type: "urlinput",
                filetype: "media",
                label: "Alternative source URL"
            }), ee(n) && c.push({
                name: "poster",
                type: "urlinput",
                filetype: "image",
                label: "Media poster (Image URL)"
            });
            var u = {title: "Advanced", name: "advanced", items: c}, s = [o, a];
            0 < c.length && s.push(u);
            var l = {type: "tabpanel", tabs: s}, m = n.windowManager.open({
                title: "Insert/Edit Media",
                size: "normal",
                body: l,
                buttons: [{
                    type: "cancel",
                    name: "cancel",
                    text: "Cancel"
                }, {type: "submit", name: "save", text: "Save", primary: !0}],
                onSubmit: function (e) {
                    var t = i(e);
                    !function (e, t, r) {
                        t.embed = de(t.embed, t), t.embed && (e.source1 === t.source1 || ge(t.source1)) ? F(r, t.embed) : pe(r, t).then(function (e) {
                            F(r, e.html)
                        })["catch"](_(r))
                    }(r.get(), t, n), e.close()
                },
                onChange: function (e, t) {
                    switch (t.name) {
                        case"source1":
                            !function (e, t) {
                                var r = i(t);
                                e.source1 !== r.source1 && (M(m, n)({
                                    url: r.source1,
                                    html: ""
                                }), pe(n, r).then(M(m, n))["catch"](_(n)))
                            }(r.get(), e);
                            break;
                        case"embed":
                            !function (e) {
                                var t = A(e.getData()), r = C(n, t.embed);
                                e.setData(S(r))
                            }(e);
                            break;
                        case"dimensions":
                        case"poster":
                            !function (e) {
                                var t = i(e), r = j(n, t);
                                e.setData(S(d(d({}, t), {embed: r})))
                            }(e)
                    }
                    r.set(i(e))
                },
                initialData: t
            })
        }, be = function (e) {
            return {
                showDialog: function () {
                    ve(e)
                }
            }
        }, we = function (e) {
            e.addCommand("mceMedia", function () {
                ve(e)
            })
        }, ye = tinymce.util.Tools.resolve("tinymce.html.Node"),
        xe = tinymce.util.Tools.resolve("tinymce.Env"), je = function (i, e) {
            if (!1 === X(i)) {
                return e;
            }
            var o, a = se();
            return ne({
                validate: !1,
                allow_conditional_comments: !1,
                comment: function (e) {
                    a.comment(e)
                },
                cdata: function (e) {
                    a.cdata(e)
                },
                text: function (e, t) {
                    a.text(e, t)
                },
                start: function (e, t, r) {
                    if (o = !0, "script" !== e && "noscript" !== e) {
                        for (var n = 0; n < t.length; n++) {
                            if (0 === t[n].name.indexOf("on")) {
                                return;
                            }
                            "style" === t[n].name && (t[n].value = i.dom.serializeStyle(i.dom.parseStyle(t[n].value), e))
                        }
                        a.start(e, t, r), o = !1
                    }
                },
                end: function (e) {
                    o || a.end(e)
                }
            }, le({})).parse(e), a.getContent()
        }, Oe = function (e, t, r) {
            var n, i, o, a, c;
            for (a = (o = t.attributes).length; a--;) {
                n = o[a].name, i = o[a].value, "width" !== n && "height" !== n && "style" !== n && ("data" !== n && "src" !== n || (i = e.convertURL(i, n)), r.attr("data-mce-p-" + n, i));
            }
            (c = t.firstChild && t.firstChild.value) && (r.attr("data-mce-html", escape(je(e, c))), r.firstChild = null)
        }, Ae = function (i) {
            return function (e) {
                for (var t, r, n = e.length; n--;) {
                    (t = e[n]).parent && (t.parent.attr("data-mce-object") || "script" === t.name && !(r = v(J(i), t.attr("src"))) || (r && (r.width && t.attr("width", r.width.toString()), r.height && t.attr("height", r.height.toString())), "iframe" === t.name && V(i) && xe.ceFalse ? T(t) || t.replace(k(i, t)) : T(t) || t.replace(P(i, t))))
                }
            }
        }, Se = function (d) {
            d.on("preInit", function () {
                var t = d.schema.getSpecialElements();
                re.each("video audio iframe object".split(" "), function (e) {
                    t[e] = new RegExp("</" + e + "[^>]*>", "gi")
                });
                var r = d.schema.getBoolAttrs();
                re.each("webkitallowfullscreen mozallowfullscreen allowfullscreen".split(" "), function (e) {
                    r[e] = {}
                }), d.parser.addNodeFilter("iframe,video,audio,object,embed,script", Ae(d)), d.serializer.addAttributeFilter("data-mce-object", function (e, t) {
                    for (var r, n, i, o, a, c, u, s, l = e.length; l--;) {
                        if ((r = e[l]).parent) {
                            for (u = r.attr(t), n = new ye(u, 1), "audio" !== u && "script" !== u && ((s = r.attr("class")) && -1 !== s.indexOf("mce-preview-object") ? n.attr({
                                width: r.firstChild.attr("width"),
                                height: r.firstChild.attr("height")
                            }) : n.attr({
                                width: r.attr("width"),
                                height: r.attr("height")
                            })), n.attr({style: r.attr("style")}), i = (o = r.attributes).length; i--;) {
                                var m = o[i].name;
                                0 === m.indexOf("data-mce-p-") && n.attr(m.substr(11), o[i].value)
                            }
                            "script" === u && n.attr("type", "text/javascript"), (a = r.attr("data-mce-html")) && ((c = new ye("#text", 3)).raw = !0, c.value = je(d, unescape(a)), n.append(c)), r.replace(n)
                        }
                    }
                })
            }), d.on("SetContent", function () {
                d.$("span.mce-preview-object").each(function (e, t) {
                    var r = d.$(t);
                    0 === r.find("span.mce-shim").length && r.append('<span class="mce-shim"></span>')
                })
            })
        }, _e = function (e) {
            e.on("ResolveName", function (e) {
                var t;
                1 === e.target.nodeType && (t = e.target.getAttribute("data-mce-object")) && (e.name = t)
            })
        }, Ce = function (t) {
            t.on("click keyup touchend", function () {
                var e = t.selection.getNode();
                e && t.dom.hasClass(e, "mce-preview-object") && t.dom.getAttrib(e, "data-mce-selected") && e.setAttribute("data-mce-selected", "2")
            }), t.on("ObjectSelected", function (e) {
                var t = e.target.getAttribute("data-mce-object");
                "audio" !== t && "script" !== t || e.preventDefault()
            }), t.on("ObjectResized", function (e) {
                var t, r = e.target;
                r.getAttribute("data-mce-object") && (t = r.getAttribute("data-mce-html")) && (t = unescape(t), r.setAttribute("data-mce-html", escape(de(t, {
                    width: String(e.width),
                    height: String(e.height)
                }))))
            })
        }, Me = function (e) {
            e.ui.registry.addToggleButton("media", {
                tooltip: "Insert/edit media",
                icon: "embed",
                onAction: function () {
                    e.execCommand("mceMedia")
                },
                onSetup: function (t, r) {
                    return function (e) {
                        return t.selection.selectorChangedWithUnbind(r.join(","), e.setActive).unbind
                    }
                }(e, ["img[data-mce-object]", "span[data-mce-object]", "div[data-ephox-embed-iri]"])
            }), e.ui.registry.addMenuItem("media", {
                icon: "embed",
                text: "Media...",
                onAction: function () {
                    e.execCommand("mceMedia")
                }
            })
        };
    !function Fe() {
        n.add("media", function (e) {
            return we(e), Me(e), _e(e), Se(e), Ce(e), be(e)
        })
    }()
}();
/* CodeMirror - Minified & Bundled
 Generated on 13.7.2016 with http://codemirror.net/doc/compress.html
 Version: HEAD

 Modes:
 - css.js
 - php.js
 Add-ons:
 - css-hint.js
 */

!function(a) {
	"object" == typeof exports && "object" == typeof module ? a(require("../../lib/codemirror")) : "function" == typeof define && define.amd ? define(["../../lib/codemirror"], a) : a(CodeMirror)
}(function(a) {
	"use strict";

	function b(a) {
		for (var b = {}, c = 0; c < a.length; ++c) {
			b[a[c]] = !0;
		}
		return b
	}

	function x(a, b) {
		for (var d, c = !1; null != (d = a.next());) {
			if (c && "/" == d) {
				b.tokenize = null;
				break
			}
			c = "*" == d
		}
		return ["comment", "comment"]
	}

	a.defineMode("css", function(b, c) {
		function u(a, b) {
			return s = b, a
		}

		function v(a, b) {
			var c = a.next();
			if (f[c]) {
				var d = f[c](a, b);
				if (d !== !1) {
					return d
				}
			}
			return "@" == c ? (a.eatWhile(/[\w\\\-]/), u("def", a.current())) : "=" == c || ("~" == c || "|" == c) && a.eat("=") ? u(null, "compare") : '"' == c || "'" == c ? (b.tokenize = w(c), b.tokenize(a, b)) : "#" == c ? (a.eatWhile(/[\w\\\-]/), u("atom", "hash")) : "!" == c ? (a.match(/^\s*\w*/), u("keyword", "important")) : /\d/.test(c) || "." == c && a.eat(/\d/) ? (a.eatWhile(/[\w.%]/), u("number", "unit")) : "-" !== c ? /[,+>*\/]/.test(c) ? u(null, "select-op") : "." == c && a.match(/^-?[_a-z][_a-z0-9-]*/i) ? u("qualifier", "qualifier") : /[:;{}\[\]\(\)]/.test(c) ? u(null, c) : "u" == c && a.match(/rl(-prefix)?\(/) || "d" == c && a.match("omain(") || "r" == c && a.match("egexp(") ? (a.backUp(1), b.tokenize = x, u("property", "word")) : /[\w\\\-]/.test(c) ? (a.eatWhile(/[\w\\\-]/), u("property", "word")) : u(null, null) : /[\d.]/.test(a.peek()) ? (a.eatWhile(/[\w.%]/), u("number", "unit")) : a.match(/^-[\w\\\-]+/) ? (a.eatWhile(/[\w\\\-]/), a.match(/^\s*:/, !1) ? u("variable-2", "variable-definition") : u("variable-2", "variable")) : a.match(/^\w+-/) ? u("meta", "meta") : void 0
		}

		function w(a) {
			return function(b, c) {
				for (var e, d = !1; null != (e = b.next());) {
					if (e == a && !d) {
						")" == a && b.backUp(1);
						break
					}
					d = !d && "\\" == e
				}
				return (e == a || !d && ")" != a) && (c.tokenize = null), u("string", "string")
			}
		}

		function x(a, b) {
			return a.next(), a.match(/\s*[\"\')]/, !1) ? b.tokenize = null : b.tokenize = w(")"), u(null, "(")
		}

		function y(a, b, c) {
			this.type = a, this.indent = b, this.prev = c
		}

		function z(a, b, c, d) {
			return a.context = new y(c, b.indentation() + (d === !1 ? 0 : e), a.context), c
		}

		function A(a) {
			return a.context.prev && (a.context = a.context.prev), a.context.type
		}

		function B(a, b, c) {
			return E[c.context.type](a, b, c)
		}

		function C(a, b, c, d) {
			for (var e = d || 1; e > 0; e--) {
				c.context = c.context.prev;
			}
			return B(a, b, c)
		}

		function D(a) {
			var b = a.current().toLowerCase();
			t = p.hasOwnProperty(b) ? "atom" : o.hasOwnProperty(b) ? "keyword" : "variable"
		}

		var d = c.inline;
		c.propertyKeywords || (c = a.resolveMode("text/css"));
		var s, t, e = b.indentUnit, f = c.tokenHooks, g = c.documentTypes || {}, h = c.mediaTypes || {}, i = c.mediaFeatures || {}, j = c.mediaValueKeywords || {},
			k = c.propertyKeywords || {}, l = c.nonStandardPropertyKeywords || {}, m = c.fontProperties || {}, n = c.counterDescriptors || {}, o = c.colorKeywords || {},
			p = c.valueKeywords || {}, q = c.allowNested, r = c.supportsAtComponent === !0, E = {};
		return E.top = function(a, b, c) {
			if ("{" == a) {
				return z(c, b, "block");
			}
			if ("}" == a && c.context.prev) {
				return A(c);
			}
			if (r && /@component/.test(a)) {
				return z(c, b, "atComponentBlock");
			}
			if (/^@(-moz-)?document$/.test(a)) {
				return z(c, b, "documentTypes");
			}
			if (/^@(media|supports|(-moz-)?document|import)$/.test(a)) {
				return z(c, b, "atBlock");
			}
			if (/^@(font-face|counter-style)/.test(a)) {
				return c.stateArg = a, "restricted_atBlock_before";
			}
			if (/^@(-(moz|ms|o|webkit)-)?keyframes$/.test(a)) {
				return "keyframes";
			}
			if (a && "@" == a.charAt(0)) {
				return z(c, b, "at");
			}
			if ("hash" == a) {
				t = "builtin";
			} else if ("word" == a) {
				t = "tag";
			} else {
				if ("variable-definition" == a) {
					return "maybeprop";
				}
				if ("interpolation" == a) {
					return z(c, b, "interpolation");
				}
				if (":" == a) {
					return "pseudo";
				}
				if (q && "(" == a) {
					return z(c, b, "parens")
				}
			}
			return c.context.type
		}, E.block = function(a, b, c) {
			if ("word" == a) {
				var d = b.current().toLowerCase();
				return k.hasOwnProperty(d) ? (t = "property", "maybeprop") : l.hasOwnProperty(d) ? (t = "string-2", "maybeprop") : q ? (t = b.match(/^\s*:(?:\s|$)/, !1) ? "property" : "tag", "block") : (t += " error", "maybeprop")
			}
			return "meta" == a ? "block" : q || "hash" != a && "qualifier" != a ? E.top(a, b, c) : (t = "error", "block")
		}, E.maybeprop = function(a, b, c) {
			return ":" == a ? z(c, b, "prop") : B(a, b, c)
		}, E.prop = function(a, b, c) {
			if (";" == a) {
				return A(c);
			}
			if ("{" == a && q) {
				return z(c, b, "propBlock");
			}
			if ("}" == a || "{" == a) {
				return C(a, b, c);
			}
			if ("(" == a) {
				return z(c, b, "parens");
			}
			if ("hash" != a || /^#([0-9a-fA-f]{3,4}|[0-9a-fA-f]{6}|[0-9a-fA-f]{8})$/.test(b.current())) {
				if ("word" == a) {
					D(b);
				} else if ("interpolation" == a) {
					return z(c, b, "interpolation")
				}
			} else {
				t += " error";
			}
			return "prop"
		}, E.propBlock = function(a, b, c) {
			return "}" == a ? A(c) : "word" == a ? (t = "property", "maybeprop") : c.context.type
		}, E.parens = function(a, b, c) {
			return "{" == a || "}" == a ? C(a, b, c) : ")" == a ? A(c) : "(" == a ? z(c, b, "parens") : "interpolation" == a ? z(c, b, "interpolation") : ("word" == a && D(b), "parens")
		}, E.pseudo = function(a, b, c) {
			return "word" == a ? (t = "variable-3", c.context.type) : B(a, b, c)
		}, E.documentTypes = function(a, b, c) {
			return "word" == a && g.hasOwnProperty(b.current()) ? (t = "tag", c.context.type) : E.atBlock(a, b, c)
		}, E.atBlock = function(a, b, c) {
			if ("(" == a) {
				return z(c, b, "atBlock_parens");
			}
			if ("}" == a || ";" == a) {
				return C(a, b, c);
			}
			if ("{" == a) {
				return A(c) && z(c, b, q ? "block" : "top");
			}
			if ("interpolation" == a) {
				return z(c, b, "interpolation");
			}
			if ("word" == a) {
				var d = b.current().toLowerCase();
				t = "only" == d || "not" == d || "and" == d || "or" == d ? "keyword" : h.hasOwnProperty(d) ? "attribute" : i.hasOwnProperty(d) ? "property" : j.hasOwnProperty(d) ? "keyword" : k.hasOwnProperty(d) ? "property" : l.hasOwnProperty(d) ? "string-2" : p.hasOwnProperty(d) ? "atom" : o.hasOwnProperty(d) ? "keyword" : "error"
			}
			return c.context.type
		}, E.atComponentBlock = function(a, b, c) {
			return "}" == a ? C(a, b, c) : "{" == a ? A(c) && z(c, b, q ? "block" : "top", !1) : ("word" == a && (t = "error"), c.context.type)
		}, E.atBlock_parens = function(a, b, c) {
			return ")" == a ? A(c) : "{" == a || "}" == a ? C(a, b, c, 2) : E.atBlock(a, b, c)
		}, E.restricted_atBlock_before = function(a, b, c) {
			return "{" == a ? z(c, b, "restricted_atBlock") : "word" == a && "@counter-style" == c.stateArg ? (t = "variable", "restricted_atBlock_before") : B(a, b, c)
		}, E.restricted_atBlock = function(a, b, c) {
			return "}" == a ? (c.stateArg = null, A(c)) : "word" == a ? (t = "@font-face" == c.stateArg && !m.hasOwnProperty(b.current().toLowerCase()) || "@counter-style" == c.stateArg && !n.hasOwnProperty(b.current().toLowerCase()) ? "error" : "property", "maybeprop") : "restricted_atBlock"
		}, E.keyframes = function(a, b, c) {
			return "word" == a ? (t = "variable", "keyframes") : "{" == a ? z(c, b, "top") : B(a, b, c)
		}, E.at = function(a, b, c) {
			return ";" == a ? A(c) : "{" == a || "}" == a ? C(a, b, c) : ("word" == a ? t = "tag" : "hash" == a && (t = "builtin"), "at")
		}, E.interpolation = function(a, b, c) {
			return "}" == a ? A(c) : "{" == a || ";" == a ? C(a, b, c) : ("word" == a ? t = "variable" : "variable" != a && "(" != a && ")" != a && (t = "error"), "interpolation")
		}, {
			startState      : function(a) {
				return {tokenize: null, state: d ? "block" : "top", stateArg: null, context: new y(d ? "block" : "top", a || 0, null)}
			}, token        : function(a, b) {
				if (!b.tokenize && a.eatSpace()) {
					return null;
				}
				var c = (b.tokenize || v)(a, b);
				return c && "object" == typeof c && (s = c[1], c = c[0]), t = c, b.state = E[b.state](s, a, b), t
			}, indent       : function(a, b) {
				var c = a.context, d = b && b.charAt(0), f = c.indent;
				return "prop" != c.type || "}" != d && ")" != d || (c = c.prev), c.prev && ("}" != d || "block" != c.type && "top" != c.type && "interpolation" != c.type && "restricted_atBlock" != c.type ? (")" == d && ("parens" == c.type || "atBlock_parens" == c.type) || "{" == d && ("at" == c.type || "atBlock" == c.type)) && (f = Math.max(0, c.indent - e), c = c.prev) : (c = c.prev, f = c.indent)), f
			}, electricChars: "}", blockCommentStart: "/*", blockCommentEnd: "*/", fold: "brace"
		}
	});
	var c = ["domain", "regexp", "url", "url-prefix"], d = b(c), e = ["all", "aural", "braille", "handheld", "print", "projection", "screen", "tty", "tv", "embossed"], f = b(e),
		g = ["width",
			"min-width",
			"max-width",
			"height",
			"min-height",
			"max-height",
			"device-width",
			"min-device-width",
			"max-device-width",
			"device-height",
			"min-device-height",
			"max-device-height",
			"aspect-ratio",
			"min-aspect-ratio",
			"max-aspect-ratio",
			"device-aspect-ratio",
			"min-device-aspect-ratio",
			"max-device-aspect-ratio",
			"color",
			"min-color",
			"max-color",
			"color-index",
			"min-color-index",
			"max-color-index",
			"monochrome",
			"min-monochrome",
			"max-monochrome",
			"resolution",
			"min-resolution",
			"max-resolution",
			"scan",
			"grid",
			"orientation",
			"device-pixel-ratio",
			"min-device-pixel-ratio",
			"max-device-pixel-ratio",
			"pointer",
			"any-pointer",
			"hover",
			"any-hover"
		], h = b(g), i = ["landscape", "portrait", "none", "coarse", "fine", "on-demand", "hover", "interlace", "progressive"], j = b(i), k = ["align-content",
			"align-items",
			"align-self",
			"alignment-adjust",
			"alignment-baseline",
			"anchor-point",
			"animation",
			"animation-delay",
			"animation-direction",
			"animation-duration",
			"animation-fill-mode",
			"animation-iteration-count",
			"animation-name",
			"animation-play-state",
			"animation-timing-function",
			"appearance",
			"azimuth",
			"backface-visibility",
			"background",
			"background-attachment",
			"background-blend-mode",
			"background-clip",
			"background-color",
			"background-image",
			"background-origin",
			"background-position",
			"background-repeat",
			"background-size",
			"baseline-shift",
			"binding",
			"bleed",
			"bookmark-label",
			"bookmark-level",
			"bookmark-state",
			"bookmark-target",
			"border",
			"border-bottom",
			"border-bottom-color",
			"border-bottom-left-radius",
			"border-bottom-right-radius",
			"border-bottom-style",
			"border-bottom-width",
			"border-collapse",
			"border-color",
			"border-image",
			"border-image-outset",
			"border-image-repeat",
			"border-image-slice",
			"border-image-source",
			"border-image-width",
			"border-left",
			"border-left-color",
			"border-left-style",
			"border-left-width",
			"border-radius",
			"border-right",
			"border-right-color",
			"border-right-style",
			"border-right-width",
			"border-spacing",
			"border-style",
			"border-top",
			"border-top-color",
			"border-top-left-radius",
			"border-top-right-radius",
			"border-top-style",
			"border-top-width",
			"border-width",
			"bottom",
			"box-decoration-break",
			"box-shadow",
			"box-sizing",
			"break-after",
			"break-before",
			"break-inside",
			"caption-side",
			"clear",
			"clip",
			"color",
			"color-profile",
			"column-count",
			"column-fill",
			"column-gap",
			"column-rule",
			"column-rule-color",
			"column-rule-style",
			"column-rule-width",
			"column-span",
			"column-width",
			"columns",
			"content",
			"counter-increment",
			"counter-reset",
			"crop",
			"cue",
			"cue-after",
			"cue-before",
			"cursor",
			"direction",
			"display",
			"dominant-baseline",
			"drop-initial-after-adjust",
			"drop-initial-after-align",
			"drop-initial-before-adjust",
			"drop-initial-before-align",
			"drop-initial-size",
			"drop-initial-value",
			"elevation",
			"empty-cells",
			"fit",
			"fit-position",
			"flex",
			"flex-basis",
			"flex-direction",
			"flex-flow",
			"flex-grow",
			"flex-shrink",
			"flex-wrap",
			"float",
			"float-offset",
			"flow-from",
			"flow-into",
			"font",
			"font-feature-settings",
			"font-family",
			"font-kerning",
			"font-language-override",
			"font-size",
			"font-size-adjust",
			"font-stretch",
			"font-style",
			"font-synthesis",
			"font-variant",
			"font-variant-alternates",
			"font-variant-caps",
			"font-variant-east-asian",
			"font-variant-ligatures",
			"font-variant-numeric",
			"font-variant-position",
			"font-weight",
			"grid",
			"grid-area",
			"grid-auto-columns",
			"grid-auto-flow",
			"grid-auto-rows",
			"grid-column",
			"grid-column-end",
			"grid-column-gap",
			"grid-column-start",
			"grid-gap",
			"grid-row",
			"grid-row-end",
			"grid-row-gap",
			"grid-row-start",
			"grid-template",
			"grid-template-areas",
			"grid-template-columns",
			"grid-template-rows",
			"hanging-punctuation",
			"height",
			"hyphens",
			"icon",
			"image-orientation",
			"image-rendering",
			"image-resolution",
			"inline-box-align",
			"justify-content",
			"left",
			"letter-spacing",
			"line-break",
			"line-height",
			"line-stacking",
			"line-stacking-ruby",
			"line-stacking-shift",
			"line-stacking-strategy",
			"list-style",
			"list-style-image",
			"list-style-position",
			"list-style-type",
			"margin",
			"margin-bottom",
			"margin-left",
			"margin-right",
			"margin-top",
			"marker-offset",
			"marks",
			"marquee-direction",
			"marquee-loop",
			"marquee-play-count",
			"marquee-speed",
			"marquee-style",
			"max-height",
			"max-width",
			"min-height",
			"min-width",
			"move-to",
			"nav-down",
			"nav-index",
			"nav-left",
			"nav-right",
			"nav-up",
			"object-fit",
			"object-position",
			"opacity",
			"order",
			"orphans",
			"outline",
			"outline-color",
			"outline-offset",
			"outline-style",
			"outline-width",
			"overflow",
			"overflow-style",
			"overflow-wrap",
			"overflow-x",
			"overflow-y",
			"padding",
			"padding-bottom",
			"padding-left",
			"padding-right",
			"padding-top",
			"page",
			"page-break-after",
			"page-break-before",
			"page-break-inside",
			"page-policy",
			"pause",
			"pause-after",
			"pause-before",
			"perspective",
			"perspective-origin",
			"pitch",
			"pitch-range",
			"play-during",
			"position",
			"presentation-level",
			"punctuation-trim",
			"quotes",
			"region-break-after",
			"region-break-before",
			"region-break-inside",
			"region-fragment",
			"rendering-intent",
			"resize",
			"rest",
			"rest-after",
			"rest-before",
			"richness",
			"right",
			"rotation",
			"rotation-point",
			"ruby-align",
			"ruby-overhang",
			"ruby-position",
			"ruby-span",
			"shape-image-threshold",
			"shape-inside",
			"shape-margin",
			"shape-outside",
			"size",
			"speak",
			"speak-as",
			"speak-header",
			"speak-numeral",
			"speak-punctuation",
			"speech-rate",
			"stress",
			"string-set",
			"tab-size",
			"table-layout",
			"target",
			"target-name",
			"target-new",
			"target-position",
			"text-align",
			"text-align-last",
			"text-decoration",
			"text-decoration-color",
			"text-decoration-line",
			"text-decoration-skip",
			"text-decoration-style",
			"text-emphasis",
			"text-emphasis-color",
			"text-emphasis-position",
			"text-emphasis-style",
			"text-height",
			"text-indent",
			"text-justify",
			"text-outline",
			"text-overflow",
			"text-shadow",
			"text-size-adjust",
			"text-space-collapse",
			"text-transform",
			"text-underline-position",
			"text-wrap",
			"top",
			"transform",
			"transform-origin",
			"transform-style",
			"transition",
			"transition-delay",
			"transition-duration",
			"transition-property",
			"transition-timing-function",
			"unicode-bidi",
			"vertical-align",
			"visibility",
			"voice-balance",
			"voice-duration",
			"voice-family",
			"voice-pitch",
			"voice-range",
			"voice-rate",
			"voice-stress",
			"voice-volume",
			"volume",
			"white-space",
			"widows",
			"width",
			"word-break",
			"word-spacing",
			"word-wrap",
			"z-index",
			"clip-path",
			"clip-rule",
			"mask",
			"enable-background",
			"filter",
			"flood-color",
			"flood-opacity",
			"lighting-color",
			"stop-color",
			"stop-opacity",
			"pointer-events",
			"color-interpolation",
			"color-interpolation-filters",
			"color-rendering",
			"fill",
			"fill-opacity",
			"fill-rule",
			"image-rendering",
			"marker",
			"marker-end",
			"marker-mid",
			"marker-start",
			"shape-rendering",
			"stroke",
			"stroke-dasharray",
			"stroke-dashoffset",
			"stroke-linecap",
			"stroke-linejoin",
			"stroke-miterlimit",
			"stroke-opacity",
			"stroke-width",
			"text-rendering",
			"baseline-shift",
			"dominant-baseline",
			"glyph-orientation-horizontal",
			"glyph-orientation-vertical",
			"text-anchor",
			"writing-mode"
		], l = b(k), m = ["scrollbar-arrow-color",
			"scrollbar-base-color",
			"scrollbar-dark-shadow-color",
			"scrollbar-face-color",
			"scrollbar-highlight-color",
			"scrollbar-shadow-color",
			"scrollbar-3d-light-color",
			"scrollbar-track-color",
			"shape-inside",
			"searchfield-cancel-button",
			"searchfield-decoration",
			"searchfield-results-button",
			"searchfield-results-decoration",
			"zoom"
		], n = b(m), o = ["font-family", "src", "unicode-range", "font-variant", "font-feature-settings", "font-stretch", "font-weight", "font-style"], p = b(o),
		q = ["additive-symbols", "fallback", "negative", "pad", "prefix", "range", "speak-as", "suffix", "symbols", "system"], r = b(q), s = ["aliceblue",
			"antiquewhite",
			"aqua",
			"aquamarine",
			"azure",
			"beige",
			"bisque",
			"black",
			"blanchedalmond",
			"blue",
			"blueviolet",
			"brown",
			"burlywood",
			"cadetblue",
			"chartreuse",
			"chocolate",
			"coral",
			"cornflowerblue",
			"cornsilk",
			"crimson",
			"cyan",
			"darkblue",
			"darkcyan",
			"darkgoldenrod",
			"darkgray",
			"darkgreen",
			"darkkhaki",
			"darkmagenta",
			"darkolivegreen",
			"darkorange",
			"darkorchid",
			"darkred",
			"darksalmon",
			"darkseagreen",
			"darkslateblue",
			"darkslategray",
			"darkturquoise",
			"darkviolet",
			"deeppink",
			"deepskyblue",
			"dimgray",
			"dodgerblue",
			"firebrick",
			"floralwhite",
			"forestgreen",
			"fuchsia",
			"gainsboro",
			"ghostwhite",
			"gold",
			"goldenrod",
			"gray",
			"grey",
			"green",
			"greenyellow",
			"honeydew",
			"hotpink",
			"indianred",
			"indigo",
			"ivory",
			"khaki",
			"lavender",
			"lavenderblush",
			"lawngreen",
			"lemonchiffon",
			"lightblue",
			"lightcoral",
			"lightcyan",
			"lightgoldenrodyellow",
			"lightgray",
			"lightgreen",
			"lightpink",
			"lightsalmon",
			"lightseagreen",
			"lightskyblue",
			"lightslategray",
			"lightsteelblue",
			"lightyellow",
			"lime",
			"limegreen",
			"linen",
			"magenta",
			"maroon",
			"mediumaquamarine",
			"mediumblue",
			"mediumorchid",
			"mediumpurple",
			"mediumseagreen",
			"mediumslateblue",
			"mediumspringgreen",
			"mediumturquoise",
			"mediumvioletred",
			"midnightblue",
			"mintcream",
			"mistyrose",
			"moccasin",
			"navajowhite",
			"navy",
			"oldlace",
			"olive",
			"olivedrab",
			"orange",
			"orangered",
			"orchid",
			"palegoldenrod",
			"palegreen",
			"paleturquoise",
			"palevioletred",
			"papayawhip",
			"peachpuff",
			"peru",
			"pink",
			"plum",
			"powderblue",
			"purple",
			"rebeccapurple",
			"red",
			"rosybrown",
			"royalblue",
			"saddlebrown",
			"salmon",
			"sandybrown",
			"seagreen",
			"seashell",
			"sienna",
			"silver",
			"skyblue",
			"slateblue",
			"slategray",
			"snow",
			"springgreen",
			"steelblue",
			"tan",
			"teal",
			"thistle",
			"tomato",
			"turquoise",
			"violet",
			"wheat",
			"white",
			"whitesmoke",
			"yellow",
			"yellowgreen"
		], t = b(s), u = ["above",
			"absolute",
			"activeborder",
			"additive",
			"activecaption",
			"afar",
			"after-white-space",
			"ahead",
			"alias",
			"all",
			"all-scroll",
			"alphabetic",
			"alternate",
			"always",
			"amharic",
			"amharic-abegede",
			"antialiased",
			"appworkspace",
			"arabic-indic",
			"armenian",
			"asterisks",
			"attr",
			"auto",
			"avoid",
			"avoid-column",
			"avoid-page",
			"avoid-region",
			"background",
			"backwards",
			"baseline",
			"below",
			"bidi-override",
			"binary",
			"bengali",
			"blink",
			"block",
			"block-axis",
			"bold",
			"bolder",
			"border",
			"border-box",
			"both",
			"bottom",
			"break",
			"break-all",
			"break-word",
			"bullets",
			"button",
			"button-bevel",
			"buttonface",
			"buttonhighlight",
			"buttonshadow",
			"buttontext",
			"calc",
			"cambodian",
			"capitalize",
			"caps-lock-indicator",
			"caption",
			"captiontext",
			"caret",
			"cell",
			"center",
			"checkbox",
			"circle",
			"cjk-decimal",
			"cjk-earthly-branch",
			"cjk-heavenly-stem",
			"cjk-ideographic",
			"clear",
			"clip",
			"close-quote",
			"col-resize",
			"collapse",
			"color",
			"color-burn",
			"color-dodge",
			"column",
			"column-reverse",
			"compact",
			"condensed",
			"contain",
			"content",
			"content-box",
			"context-menu",
			"continuous",
			"copy",
			"counter",
			"counters",
			"cover",
			"crop",
			"cross",
			"crosshair",
			"currentcolor",
			"cursive",
			"cyclic",
			"darken",
			"dashed",
			"decimal",
			"decimal-leading-zero",
			"default",
			"default-button",
			"dense",
			"destination-atop",
			"destination-in",
			"destination-out",
			"destination-over",
			"devanagari",
			"difference",
			"disc",
			"discard",
			"disclosure-closed",
			"disclosure-open",
			"document",
			"dot-dash",
			"dot-dot-dash",
			"dotted",
			"double",
			"down",
			"e-resize",
			"ease",
			"ease-in",
			"ease-in-out",
			"ease-out",
			"element",
			"ellipse",
			"ellipsis",
			"embed",
			"end",
			"ethiopic",
			"ethiopic-abegede",
			"ethiopic-abegede-am-et",
			"ethiopic-abegede-gez",
			"ethiopic-abegede-ti-er",
			"ethiopic-abegede-ti-et",
			"ethiopic-halehame-aa-er",
			"ethiopic-halehame-aa-et",
			"ethiopic-halehame-am-et",
			"ethiopic-halehame-gez",
			"ethiopic-halehame-om-et",
			"ethiopic-halehame-sid-et",
			"ethiopic-halehame-so-et",
			"ethiopic-halehame-ti-er",
			"ethiopic-halehame-ti-et",
			"ethiopic-halehame-tig",
			"ethiopic-numeric",
			"ew-resize",
			"exclusion",
			"expanded",
			"extends",
			"extra-condensed",
			"extra-expanded",
			"fantasy",
			"fast",
			"fill",
			"fixed",
			"flat",
			"flex",
			"flex-end",
			"flex-start",
			"footnotes",
			"forwards",
			"from",
			"geometricPrecision",
			"georgian",
			"graytext",
			"grid",
			"groove",
			"gujarati",
			"gurmukhi",
			"hand",
			"hangul",
			"hangul-consonant",
			"hard-light",
			"hebrew",
			"help",
			"hidden",
			"hide",
			"higher",
			"highlight",
			"highlighttext",
			"hiragana",
			"hiragana-iroha",
			"horizontal",
			"hsl",
			"hsla",
			"hue",
			"icon",
			"ignore",
			"inactiveborder",
			"inactivecaption",
			"inactivecaptiontext",
			"infinite",
			"infobackground",
			"infotext",
			"inherit",
			"initial",
			"inline",
			"inline-axis",
			"inline-block",
			"inline-flex",
			"inline-grid",
			"inline-table",
			"inset",
			"inside",
			"intrinsic",
			"invert",
			"italic",
			"japanese-formal",
			"japanese-informal",
			"justify",
			"kannada",
			"katakana",
			"katakana-iroha",
			"keep-all",
			"khmer",
			"korean-hangul-formal",
			"korean-hanja-formal",
			"korean-hanja-informal",
			"landscape",
			"lao",
			"large",
			"larger",
			"left",
			"level",
			"lighter",
			"lighten",
			"line-through",
			"linear",
			"linear-gradient",
			"lines",
			"list-item",
			"listbox",
			"listitem",
			"local",
			"logical",
			"loud",
			"lower",
			"lower-alpha",
			"lower-armenian",
			"lower-greek",
			"lower-hexadecimal",
			"lower-latin",
			"lower-norwegian",
			"lower-roman",
			"lowercase",
			"ltr",
			"luminosity",
			"malayalam",
			"match",
			"matrix",
			"matrix3d",
			"media-controls-background",
			"media-current-time-display",
			"media-fullscreen-button",
			"media-mute-button",
			"media-play-button",
			"media-return-to-realtime-button",
			"media-rewind-button",
			"media-seek-back-button",
			"media-seek-forward-button",
			"media-slider",
			"media-sliderthumb",
			"media-time-remaining-display",
			"media-volume-slider",
			"media-volume-slider-container",
			"media-volume-sliderthumb",
			"medium",
			"menu",
			"menulist",
			"menulist-button",
			"menulist-text",
			"menulist-textfield",
			"menutext",
			"message-box",
			"middle",
			"min-intrinsic",
			"mix",
			"mongolian",
			"monospace",
			"move",
			"multiple",
			"multiply",
			"myanmar",
			"n-resize",
			"narrower",
			"ne-resize",
			"nesw-resize",
			"no-close-quote",
			"no-drop",
			"no-open-quote",
			"no-repeat",
			"none",
			"normal",
			"not-allowed",
			"nowrap",
			"ns-resize",
			"numbers",
			"numeric",
			"nw-resize",
			"nwse-resize",
			"oblique",
			"octal",
			"open-quote",
			"optimizeLegibility",
			"optimizeSpeed",
			"oriya",
			"oromo",
			"outset",
			"outside",
			"outside-shape",
			"overlay",
			"overline",
			"padding",
			"padding-box",
			"painted",
			"page",
			"paused",
			"persian",
			"perspective",
			"plus-darker",
			"plus-lighter",
			"pointer",
			"polygon",
			"portrait",
			"pre",
			"pre-line",
			"pre-wrap",
			"preserve-3d",
			"progress",
			"push-button",
			"radial-gradient",
			"radio",
			"read-only",
			"read-write",
			"read-write-plaintext-only",
			"rectangle",
			"region",
			"relative",
			"repeat",
			"repeating-linear-gradient",
			"repeating-radial-gradient",
			"repeat-x",
			"repeat-y",
			"reset",
			"reverse",
			"rgb",
			"rgba",
			"ridge",
			"right",
			"rotate",
			"rotate3d",
			"rotateX",
			"rotateY",
			"rotateZ",
			"round",
			"row",
			"row-resize",
			"row-reverse",
			"rtl",
			"run-in",
			"running",
			"s-resize",
			"sans-serif",
			"saturation",
			"scale",
			"scale3d",
			"scaleX",
			"scaleY",
			"scaleZ",
			"screen",
			"scroll",
			"scrollbar",
			"se-resize",
			"searchfield",
			"searchfield-cancel-button",
			"searchfield-decoration",
			"searchfield-results-button",
			"searchfield-results-decoration",
			"semi-condensed",
			"semi-expanded",
			"separate",
			"serif",
			"show",
			"sidama",
			"simp-chinese-formal",
			"simp-chinese-informal",
			"single",
			"skew",
			"skewX",
			"skewY",
			"skip-white-space",
			"slide",
			"slider-horizontal",
			"slider-vertical",
			"sliderthumb-horizontal",
			"sliderthumb-vertical",
			"slow",
			"small",
			"small-caps",
			"small-caption",
			"smaller",
			"soft-light",
			"solid",
			"somali",
			"source-atop",
			"source-in",
			"source-out",
			"source-over",
			"space",
			"space-around",
			"space-between",
			"spell-out",
			"square",
			"square-button",
			"start",
			"static",
			"status-bar",
			"stretch",
			"stroke",
			"sub",
			"subpixel-antialiased",
			"super",
			"sw-resize",
			"symbolic",
			"symbols",
			"table",
			"table-caption",
			"table-cell",
			"table-column",
			"table-column-group",
			"table-footer-group",
			"table-header-group",
			"table-row",
			"table-row-group",
			"tamil",
			"telugu",
			"text",
			"text-bottom",
			"text-top",
			"textarea",
			"textfield",
			"thai",
			"thick",
			"thin",
			"threeddarkshadow",
			"threedface",
			"threedhighlight",
			"threedlightshadow",
			"threedshadow",
			"tibetan",
			"tigre",
			"tigrinya-er",
			"tigrinya-er-abegede",
			"tigrinya-et",
			"tigrinya-et-abegede",
			"to",
			"top",
			"trad-chinese-formal",
			"trad-chinese-informal",
			"translate",
			"translate3d",
			"translateX",
			"translateY",
			"translateZ",
			"transparent",
			"ultra-condensed",
			"ultra-expanded",
			"underline",
			"up",
			"upper-alpha",
			"upper-armenian",
			"upper-greek",
			"upper-hexadecimal",
			"upper-latin",
			"upper-norwegian",
			"upper-roman",
			"uppercase",
			"urdu",
			"url",
			"var",
			"vertical",
			"vertical-text",
			"visible",
			"visibleFill",
			"visiblePainted",
			"visibleStroke",
			"visual",
			"w-resize",
			"wait",
			"wave",
			"wider",
			"window",
			"windowframe",
			"windowtext",
			"words",
			"wrap",
			"wrap-reverse",
			"x-large",
			"x-small",
			"xor",
			"xx-large",
			"xx-small"
		], v = b(u), w = c.concat(e).concat(g).concat(i).concat(k).concat(m).concat(s).concat(u);
	a.registerHelper("hintWords", "css", w), a.defineMIME("text/css", {
		documentTypes              : d,
		mediaTypes                 : f,
		mediaFeatures              : h,
		mediaValueKeywords         : j,
		propertyKeywords           : l,
		nonStandardPropertyKeywords: n,
		fontProperties             : p,
		counterDescriptors         : r,
		colorKeywords              : t,
		valueKeywords              : v,
		tokenHooks                 : {
			"/": function(a, b) {
				return a.eat("*") ? (b.tokenize = x, x(a, b)) : !1
			}
		},
		name                       : "css"
	}), a.defineMIME("text/x-scss", {
		mediaTypes                 : f,
		mediaFeatures              : h,
		mediaValueKeywords         : j,
		propertyKeywords           : l,
		nonStandardPropertyKeywords: n,
		colorKeywords              : t,
		valueKeywords              : v,
		fontProperties             : p,
		allowNested                : !0,
		tokenHooks                 : {
			"/"   : function(a, b) {
				return a.eat("/") ? (a.skipToEnd(), ["comment", "comment"]) : a.eat("*") ? (b.tokenize = x, x(a, b)) : ["operator", "operator"]
			}, ":": function(a) {
				return a.match(/\s*\{/) ? [null, "{"] : !1
			}, $  : function(a) {
				return a.match(/^[\w-]+/), a.match(/^\s*:/, !1) ? ["variable-2", "variable-definition"] : ["variable-2", "variable"]
			}, "#": function(a) {
				return a.eat("{") ? [null, "interpolation"] : !1
			}
		},
		name                       : "css",
		helperType                 : "scss"
	}), a.defineMIME("text/x-less", {
		mediaTypes                 : f,
		mediaFeatures              : h,
		mediaValueKeywords         : j,
		propertyKeywords           : l,
		nonStandardPropertyKeywords: n,
		colorKeywords              : t,
		valueKeywords              : v,
		fontProperties             : p,
		allowNested                : !0,
		tokenHooks                 : {
			"/"   : function(a, b) {
				return a.eat("/") ? (a.skipToEnd(), ["comment", "comment"]) : a.eat("*") ? (b.tokenize = x, x(a, b)) : ["operator", "operator"]
			}, "@": function(a) {
				return a.eat("{") ? [null, "interpolation"
				] : a.match(/^(charset|document|font-face|import|(-(moz|ms|o|webkit)-)?keyframes|media|namespace|page|supports)\b/, !1) ? !1 : (a.eatWhile(/[\w\\\-]/), a.match(/^\s*:/, !1) ? ["variable-2",
					"variable-definition"
				] : ["variable-2", "variable"])
			}, "&": function() {
				return ["atom", "atom"]
			}
		},
		name                       : "css",
		helperType                 : "less"
	}), a.defineMIME("text/x-gss", {
		documentTypes              : d,
		mediaTypes                 : f,
		mediaFeatures              : h,
		propertyKeywords           : l,
		nonStandardPropertyKeywords: n,
		fontProperties             : p,
		counterDescriptors         : r,
		colorKeywords              : t,
		valueKeywords              : v,
		supportsAtComponent        : !0,
		tokenHooks                 : {
			"/": function(a, b) {
				return a.eat("*") ? (b.tokenize = x, x(a, b)) : !1
			}
		},
		name                       : "css",
		helperType                 : "gss"
	})
}), function(a) {
	"object" == typeof exports && "object" == typeof module ? a(require("../../lib/codemirror"), require("../htmlmixed/htmlmixed"), require("../clike/clike")) : "function" == typeof define && define.amd ? define(["../../lib/codemirror",
		"../htmlmixed/htmlmixed",
		"../clike/clike"
	], a) : a(CodeMirror)
}(function(a) {
	"use strict";

	function b(a) {
		for (var b = {}, c = a.split(" "), d = 0; d < c.length; ++d) {
			b[c[d]] = !0;
		}
		return b
	}

	function c(a, b, e) {
		return 0 == a.length ? d(b) : function(f, g) {
			for (var h = a[0], i = 0; i < h.length; i++) {
				if (f.match(h[i][0])) {
					return g.tokenize = c(a.slice(1), b), h[i][1];
				}
			}
			return g.tokenize = d(b, e), "string"
		}
	}

	function d(a, b) {
		return function(c, d) {
			return e(c, d, a, b)
		}
	}

	function e(a, b, d, e) {
		if (e !== !1 && a.match("${", !1) || a.match("{$", !1)) {
			return b.tokenize = null, "string";
		}
		if (e !== !1 && a.match(/^\$[a-zA-Z_][a-zA-Z0-9_]*/)) {
			return a.match("[", !1) && (b.tokenize = c([[["[", null]], [[/\d[\w\.]*/, "number"], [/\$[a-zA-Z_][a-zA-Z0-9_]*/, "variable-2"], [/[\w\$]+/, "variable"]], [["]", null]]
			], d, e)), a.match(/\-\>\w/, !1) && (b.tokenize = c([[["->", null]], [[/[\w]+/, "variable"]]], d, e)), "variable-2";
		}
		for (var f = !1; !a.eol() && (f || e === !1 || !a.match("{$", !1) && !a.match(/^(\$[a-zA-Z_][a-zA-Z0-9_]*|\$\{)/, !1));) {
			if (!f && a.match(d)) {
				b.tokenize = null, b.tokStack.pop(), b.tokStack.pop();
				break
			}
			f = "\\" == a.next() && !f
		}
		return "string"
	}

	var f = "abstract and array as break case catch class clone const continue declare default do else elseif enddeclare endfor endforeach endif endswitch endwhile extends final for foreach function global goto if implements interface instanceof namespace new or private protected public static switch throw trait try use var while xor die echo empty exit eval include include_once isset list require require_once return print unset __halt_compiler self static parent yield insteadof finally",
		g = "true false null TRUE FALSE NULL __CLASS__ __DIR__ __FILE__ __LINE__ __METHOD__ __FUNCTION__ __NAMESPACE__ __TRAIT__",
		h = "func_num_args func_get_arg func_get_args strlen strcmp strncmp strcasecmp strncasecmp each error_reporting define defined trigger_error user_error set_error_handler restore_error_handler get_declared_classes get_loaded_extensions extension_loaded get_extension_funcs debug_backtrace constant bin2hex hex2bin sleep usleep time mktime gmmktime strftime gmstrftime strtotime date gmdate getdate localtime checkdate flush wordwrap htmlspecialchars htmlentities html_entity_decode md5 md5_file crc32 getimagesize image_type_to_mime_type phpinfo phpversion phpcredits strnatcmp strnatcasecmp substr_count strspn strcspn strtok strtoupper strtolower strpos strrpos strrev hebrev hebrevc nl2br basename dirname pathinfo stripslashes stripcslashes strstr stristr strrchr str_shuffle str_word_count strcoll substr substr_replace quotemeta ucfirst ucwords strtr addslashes addcslashes rtrim str_replace str_repeat count_chars chunk_split trim ltrim strip_tags similar_text explode implode setlocale localeconv parse_str str_pad chop strchr sprintf printf vprintf vsprintf sscanf fscanf parse_url urlencode urldecode rawurlencode rawurldecode readlink linkinfo link unlink exec system escapeshellcmd escapeshellarg passthru shell_exec proc_open proc_close rand srand getrandmax mt_rand mt_srand mt_getrandmax base64_decode base64_encode abs ceil floor round is_finite is_nan is_infinite bindec hexdec octdec decbin decoct dechex base_convert number_format fmod ip2long long2ip getenv putenv getopt microtime gettimeofday getrusage uniqid quoted_printable_decode set_time_limit get_cfg_var magic_quotes_runtime set_magic_quotes_runtime get_magic_quotes_gpc get_magic_quotes_runtime import_request_variables error_log serialize unserialize memory_get_usage var_dump var_export debug_zval_dump print_r highlight_file show_source highlight_string ini_get ini_get_all ini_set ini_alter ini_restore get_include_path set_include_path restore_include_path setcookie header headers_sent connection_aborted connection_status ignore_user_abort parse_ini_file is_uploaded_file move_uploaded_file intval floatval doubleval strval gettype settype is_null is_resource is_bool is_long is_float is_int is_integer is_double is_real is_numeric is_string is_array is_object is_scalar ereg ereg_replace eregi eregi_replace split spliti join sql_regcase dl pclose popen readfile rewind rmdir umask fclose feof fgetc fgets fgetss fread fopen fpassthru ftruncate fstat fseek ftell fflush fwrite fputs mkdir rename copy tempnam tmpfile file file_get_contents file_put_contents stream_select stream_context_create stream_context_set_params stream_context_set_option stream_context_get_options stream_filter_prepend stream_filter_append fgetcsv flock get_meta_tags stream_set_write_buffer set_file_buffer set_socket_blocking stream_set_blocking socket_set_blocking stream_get_meta_data stream_register_wrapper stream_wrapper_register stream_set_timeout socket_set_timeout socket_get_status realpath fnmatch fsockopen pfsockopen pack unpack get_browser crypt opendir closedir chdir getcwd rewinddir readdir dir glob fileatime filectime filegroup fileinode filemtime fileowner fileperms filesize filetype file_exists is_writable is_writeable is_readable is_executable is_file is_dir is_link stat lstat chown touch clearstatcache mail ob_start ob_flush ob_clean ob_end_flush ob_end_clean ob_get_flush ob_get_clean ob_get_length ob_get_level ob_get_status ob_get_contents ob_implicit_flush ob_list_handlers ksort krsort natsort natcasesort asort arsort sort rsort usort uasort uksort shuffle array_walk count end prev next reset current key min max in_array array_search extract compact array_fill range array_multisort array_push array_pop array_shift array_unshift array_splice array_slice array_merge array_merge_recursive array_keys array_values array_count_values array_reverse array_reduce array_pad array_flip array_change_key_case array_rand array_unique array_intersect array_intersect_assoc array_diff array_diff_assoc array_sum array_filter array_map array_chunk array_key_exists array_intersect_key array_combine array_column pos sizeof key_exists assert assert_options version_compare ftok str_rot13 aggregate session_name session_module_name session_save_path session_id session_regenerate_id session_decode session_register session_unregister session_is_registered session_encode session_start session_destroy session_unset session_set_save_handler session_cache_limiter session_cache_expire session_set_cookie_params session_get_cookie_params session_write_close preg_match preg_match_all preg_replace preg_replace_callback preg_split preg_quote preg_grep overload ctype_alnum ctype_alpha ctype_cntrl ctype_digit ctype_lower ctype_graph ctype_print ctype_punct ctype_space ctype_upper ctype_xdigit virtual apache_request_headers apache_note apache_lookup_uri apache_child_terminate apache_setenv apache_response_headers apache_get_version getallheaders mysql_connect mysql_pconnect mysql_close mysql_select_db mysql_create_db mysql_drop_db mysql_query mysql_unbuffered_query mysql_db_query mysql_list_dbs mysql_list_tables mysql_list_fields mysql_list_processes mysql_error mysql_errno mysql_affected_rows mysql_insert_id mysql_result mysql_num_rows mysql_num_fields mysql_fetch_row mysql_fetch_array mysql_fetch_assoc mysql_fetch_object mysql_data_seek mysql_fetch_lengths mysql_fetch_field mysql_field_seek mysql_free_result mysql_field_name mysql_field_table mysql_field_len mysql_field_type mysql_field_flags mysql_escape_string mysql_real_escape_string mysql_stat mysql_thread_id mysql_client_encoding mysql_get_client_info mysql_get_host_info mysql_get_proto_info mysql_get_server_info mysql_info mysql mysql_fieldname mysql_fieldtable mysql_fieldlen mysql_fieldtype mysql_fieldflags mysql_selectdb mysql_createdb mysql_dropdb mysql_freeresult mysql_numfields mysql_numrows mysql_listdbs mysql_listtables mysql_listfields mysql_db_name mysql_dbname mysql_tablename mysql_table_name pg_connect pg_pconnect pg_close pg_connection_status pg_connection_busy pg_connection_reset pg_host pg_dbname pg_port pg_tty pg_options pg_ping pg_query pg_send_query pg_cancel_query pg_fetch_result pg_fetch_row pg_fetch_assoc pg_fetch_array pg_fetch_object pg_fetch_all pg_affected_rows pg_get_result pg_result_seek pg_result_status pg_free_result pg_last_oid pg_num_rows pg_num_fields pg_field_name pg_field_num pg_field_size pg_field_type pg_field_prtlen pg_field_is_null pg_get_notify pg_get_pid pg_result_error pg_last_error pg_last_notice pg_put_line pg_end_copy pg_copy_to pg_copy_from pg_trace pg_untrace pg_lo_create pg_lo_unlink pg_lo_open pg_lo_close pg_lo_read pg_lo_write pg_lo_read_all pg_lo_import pg_lo_export pg_lo_seek pg_lo_tell pg_escape_string pg_escape_bytea pg_unescape_bytea pg_client_encoding pg_set_client_encoding pg_meta_data pg_convert pg_insert pg_update pg_delete pg_select pg_exec pg_getlastoid pg_cmdtuples pg_errormessage pg_numrows pg_numfields pg_fieldname pg_fieldsize pg_fieldtype pg_fieldnum pg_fieldprtlen pg_fieldisnull pg_freeresult pg_result pg_loreadall pg_locreate pg_lounlink pg_loopen pg_loclose pg_loread pg_lowrite pg_loimport pg_loexport http_response_code get_declared_traits getimagesizefromstring socket_import_stream stream_set_chunk_size trait_exists header_register_callback class_uses session_status session_register_shutdown echo print global static exit array empty eval isset unset die include require include_once require_once json_decode json_encode json_last_error json_last_error_msg curl_close curl_copy_handle curl_errno curl_error curl_escape curl_exec curl_file_create curl_getinfo curl_init curl_multi_add_handle curl_multi_close curl_multi_exec curl_multi_getcontent curl_multi_info_read curl_multi_init curl_multi_remove_handle curl_multi_select curl_multi_setopt curl_multi_strerror curl_pause curl_reset curl_setopt_array curl_setopt curl_share_close curl_share_init curl_share_setopt curl_strerror curl_unescape curl_version mysqli_affected_rows mysqli_autocommit mysqli_change_user mysqli_character_set_name mysqli_close mysqli_commit mysqli_connect_errno mysqli_connect_error mysqli_connect mysqli_data_seek mysqli_debug mysqli_dump_debug_info mysqli_errno mysqli_error_list mysqli_error mysqli_fetch_all mysqli_fetch_array mysqli_fetch_assoc mysqli_fetch_field_direct mysqli_fetch_field mysqli_fetch_fields mysqli_fetch_lengths mysqli_fetch_object mysqli_fetch_row mysqli_field_count mysqli_field_seek mysqli_field_tell mysqli_free_result mysqli_get_charset mysqli_get_client_info mysqli_get_client_stats mysqli_get_client_version mysqli_get_connection_stats mysqli_get_host_info mysqli_get_proto_info mysqli_get_server_info mysqli_get_server_version mysqli_info mysqli_init mysqli_insert_id mysqli_kill mysqli_more_results mysqli_multi_query mysqli_next_result mysqli_num_fields mysqli_num_rows mysqli_options mysqli_ping mysqli_prepare mysqli_query mysqli_real_connect mysqli_real_escape_string mysqli_real_query mysqli_reap_async_query mysqli_refresh mysqli_rollback mysqli_select_db mysqli_set_charset mysqli_set_local_infile_default mysqli_set_local_infile_handler mysqli_sqlstate mysqli_ssl_set mysqli_stat mysqli_stmt_init mysqli_store_result mysqli_thread_id mysqli_thread_safe mysqli_use_result mysqli_warning_count";
	a.registerHelper("hintWords", "php", [f, g, h].join(" ").split(" ")), a.registerHelper("wordChars", "php", /[\w$]/);
	var i = {
		name            : "clike",
		helperType      : "php",
		keywords        : b(f),
		blockKeywords   : b("catch do else elseif for foreach if switch try while finally"),
		defKeywords     : b("class function interface namespace trait"),
		atoms           : b(g),
		builtin         : b(h),
		multiLineStrings: !0,
		hooks           : {
			$     : function(a) {
				return a.eatWhile(/[\w\$_]/), "variable-2"
			}, "<": function(a, b) {
				var c;
				if (c = a.match(/<<\s*/)) {
					var e = a.eat(/['"]/);
					a.eatWhile(/[\w\.]/);
					var f = a.current().slice(c[0].length + (e ? 2 : 1));
					if (e && a.eat(e), f) {
						return (b.tokStack || (b.tokStack = [])).push(f, 0), b.tokenize = d(f, "'" != e), "string"
					}
				}
				return !1
			}, "#": function(a) {
				for (; !a.eol() && !a.match("?>", !1);) {
					a.next();
				}
				return "comment"
			}, "/": function(a) {
				if (a.eat("/")) {
					for (; !a.eol() && !a.match("?>", !1);) {
						a.next();
					}
					return "comment"
				}
				return !1
			}, '"': function(a, b) {
				return (b.tokStack || (b.tokStack = [])).push('"', 0), b.tokenize = d('"'), "string"
			}, "{": function(a, b) {
				return b.tokStack && b.tokStack.length && b.tokStack[b.tokStack.length - 1]++, !1
			}, "}": function(a, b) {
				return b.tokStack && b.tokStack.length > 0 && !--b.tokStack[b.tokStack.length - 1] && (b.tokenize = d(b.tokStack[b.tokStack.length - 2])), !1
			}
		}
	};
	a.defineMode("php", function(b, c) {
		function f(b, c) {
			var f = c.curMode == e;
			if (b.sol() && c.pending && '"' != c.pending && "'" != c.pending && (c.pending = null), f) {
				return f && null == c.php.tokenize && b.match("?>") ? (c.curMode = d, c.curState = c.html, c.php.context.prev || (c.php = null), "meta") : e.token(b, c.curState);
			}
			if (b.match(/^<\?\w*/)) {
				return c.curMode = e, c.php || (c.php = a.startState(e, d.indent(c.html, ""))), c.curState = c.php, "meta";
			}
			if ('"' == c.pending || "'" == c.pending) {
				for (; !b.eol() && b.next() != c.pending;) {
					;
				}
				var g = "string"
			} else if (c.pending && b.pos < c.pending.end) {
				b.pos = c.pending.end;
				var g = c.pending.style
			} else {
				var g = d.token(b, c.curState);
			}
			c.pending && (c.pending = null);
			var j, h = b.current(), i = h.search(/<\?/);
			return -1 != i && ("string" == g && (j = h.match(/[\'\"]$/)) && !/\?>/.test(h) ? c.pending = j[0] : c.pending = {end: b.pos, style: g}, b.backUp(h.length - i)), g
		}

		var d = a.getMode(b, "text/html"), e = a.getMode(b, i);
		return {
			startState          : function() {
				var b = a.startState(d), f = c.startOpen ? a.startState(e) : null;
				return {html: b, php: f, curMode: c.startOpen ? e : d, curState: c.startOpen ? f : b, pending: null}
			}, copyState        : function(b) {
				var i, c = b.html, f = a.copyState(d, c), g = b.php, h = g && a.copyState(e, g);
				return i = b.curMode == d ? f : h, {html: f, php: h, curMode: b.curMode, curState: i, pending: b.pending}
			}, token            : f, indent: function(a, b) {
				return a.curMode != e && /^\s*<\//.test(b) || a.curMode == e && /^\?>/.test(b) ? d.indent(a.html, b) : a.curMode.indent(a.curState, b)
			}, blockCommentStart: "/*", blockCommentEnd: "*/", lineComment: "//", innerMode: function(a) {
				return {state: a.curState, mode: a.curMode}
			}
		}
	}, "htmlmixed", "clike"), a.defineMIME("application/x-httpd-php", "php"), a.defineMIME("application/x-httpd-php-open", {
		name: "php", startOpen: !0
	}), a.defineMIME("text/x-php", i)
}), function(a) {
	"object" == typeof exports && "object" == typeof module ? a(require("../../lib/codemirror"), require("../../mode/css/css")) : "function" == typeof define && define.amd ? define(["../../lib/codemirror",
		"../../mode/css/css"
	], a) : a(CodeMirror)
}(function(a) {
	"use strict";
	var b = {link: 1, visited: 1, active: 1, hover: 1, focus: 1, "first-letter": 1, "first-line": 1, "first-child": 1, before: 1, after: 1, lang: 1};
	a.registerHelper("hint", "css", function(c) {
		function l(a) {
			for (var b in a) {
				i && 0 != b.lastIndexOf(i, 0) || k.push(b)
			}
		}

		var d = c.getCursor(), e = c.getTokenAt(d), f = a.innerMode(c.getMode(), e.state);
		if ("css" == f.mode.name) {
			if ("keyword" == e.type && 0 == "!important".indexOf(e.string)) {
				return {list: ["!important"], from: a.Pos(d.line, e.start), to: a.Pos(d.line, e.end)};
			}
			var g = e.start, h = d.ch, i = e.string.slice(0, h - g);
			/[^\w$_-]/.test(i) && (i = "", g = h = d.ch);
			var j = a.resolveMode("text/css"), k = [], m = f.state.state;
			return "pseudo" == m || "variable-3" == e.type ? l(b) : "block" == m || "maybeprop" == m ? l(j.propertyKeywords) : "prop" == m || "parens" == m || "at" == m || "params" == m ? (l(j.valueKeywords), l(j.colorKeywords)) : ("media" == m || "media_parens" == m) && (l(j.mediaTypes), l(j.mediaFeatures)), k.length ? {
				list: k, from: a.Pos(d.line, g), to: a.Pos(d.line, h)
			} : void 0
		}
	})
});
!function (t) {
	var e = {};

	function n(o) {
		if (e[o]) return e[o].exports;
		var i = e[o] = {i: o, l: !1, exports: {}};
		return t[o].call(i.exports, i, i.exports, n), i.l = !0, i.exports
	}

	n.m = t, n.c = e, n.d = function (t, e, o) {
		n.o(t, e) || Object.defineProperty(t, e, {enumerable: !0, get: o})
	}, n.r = function (t) {
		"undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(t, Symbol.toStringTag, {value: "Module"}), Object.defineProperty(t, "__esModule", {value: !0})
	}, n.t = function (t, e) {
		if (1 & e && (t = n(t)), 8 & e) return t;
		if (4 & e && "object" == typeof t && t && t.__esModule) return t;
		var o = Object.create(null);
		if (n.r(o), Object.defineProperty(o, "default", {
			enumerable: !0,
			value: t
		}), 2 & e && "string" != typeof t) for (var i in t) n.d(o, i, function (e) {
			return t[e]
		}.bind(null, i));
		return o
	}, n.n = function (t) {
		var e = t && t.__esModule ? function () {
			return t.default
		} : function () {
			return t
		};
		return n.d(e, "a", e), e
	}, n.o = function (t, e) {
		return Object.prototype.hasOwnProperty.call(t, e)
	}, n.p = "/", n(n.s = 0)
}([function (t, e, n) {
	n(2), t.exports = n(3)
}, function (t, e) {
	function n(t, e) {
		var n = Object.keys(t);
		if (Object.getOwnPropertySymbols) {
			var o = Object.getOwnPropertySymbols(t);
			e && (o = o.filter((function (e) {
				return Object.getOwnPropertyDescriptor(t, e).enumerable
			}))), n.push.apply(n, o)
		}
		return n
	}

	function o(t) {
		for (var e = 1; e < arguments.length; e++) {
			var o = null != arguments[e] ? arguments[e] : {};
			e % 2 ? n(Object(o), !0).forEach((function (e) {
				i(t, e, o[e])
			})) : Object.getOwnPropertyDescriptors ? Object.defineProperties(t, Object.getOwnPropertyDescriptors(o)) : n(Object(o)).forEach((function (e) {
				Object.defineProperty(t, e, Object.getOwnPropertyDescriptor(o, e))
			}))
		}
		return t
	}

	function i(t, e, n) {
		return e in t ? Object.defineProperty(t, e, {
			value: n,
			enumerable: !0,
			configurable: !0,
			writable: !0
		}) : t[e] = n, t
	}

	if ("function" != typeof window.CustomEvent) {
		var r = function (t) {
			var e = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {};
			e = o({}, {bubbles: !1, cancelable: !1, detail: void 0}, {}, e);
			var n = document.createEvent("CustomEvent");
			return n.initCustomEvent(event, e.bubbles, e.cancelable, e.detail), n
		};
		r.prototype = window.Event.prototype, window.CustomEvent = r
	}
}, function (t, e, n) {
	"use strict";
	n.r(e);
	n(1);
	var o = function () {
		return document.body.style.overflow = ""
	}, i = function (t, e) {
		return function () {
			for (var n = arguments.length, o = new Array(n), i = 0; i < n; i++) o[i] = arguments[i];
			return t.call.apply(t, [e].concat(o))
		}
	}, r = function (t, e) {
		for (var n = 0; n < t.length; n++) e(t[n], n)
	};

	function s(t) {
		return (s = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (t) {
			return typeof t
		} : function (t) {
			return t && "function" == typeof Symbol && t.constructor === Symbol && t !== Symbol.prototype ? "symbol" : typeof t
		})(t)
	}

	function a(t, e) {
		var n = Object.keys(t);
		if (Object.getOwnPropertySymbols) {
			var o = Object.getOwnPropertySymbols(t);
			e && (o = o.filter((function (e) {
				return Object.getOwnPropertyDescriptor(t, e).enumerable
			}))), n.push.apply(n, o)
		}
		return n
	}

	function c(t) {
		for (var e = 1; e < arguments.length; e++) {
			var n = null != arguments[e] ? arguments[e] : {};
			e % 2 ? a(Object(n), !0).forEach((function (e) {
				l(t, e, n[e])
			})) : Object.getOwnPropertyDescriptors ? Object.defineProperties(t, Object.getOwnPropertyDescriptors(n)) : a(Object(n)).forEach((function (e) {
				Object.defineProperty(t, e, Object.getOwnPropertyDescriptor(n, e))
			}))
		}
		return t
	}

	function l(t, e, n) {
		return e in t ? Object.defineProperty(t, e, {
			value: n,
			enumerable: !0,
			configurable: !0,
			writable: !0
		}) : t[e] = n, t
	}

	function u(t, e) {
		if (!(t instanceof e)) throw new TypeError("Cannot call a class as a function")
	}

	function d(t, e) {
		for (var n = 0; n < e.length; n++) {
			var o = e[n];
			o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(t, o.key, o)
		}
	}

	n.d(e, "ConsentManager", (function () {
		return h
	}));
	var f = {
		prefix: "consent",
		storageKey: "consent",
		version: 1,
		viewsUntilBannerIsShown: 2,
		eventReadyName: "consent.ready",
		eventUpdatedName: "consent.updated"
	}, h = function () {
		function t() {
			var e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : {};
			u(this, t), this.options = c({}, f, {}, e), this.$manager = document.getElementById("".concat(this.options.prefix, "-manager")), this.$banner = document.getElementById("".concat(this.options.prefix, "-banner")), this.$collapseToggle = this.$manager.querySelectorAll("[data-collapse]"), this.$bannerBtnAcceptAll = document.getElementById("".concat(this.options.prefix, "-banner-btn-all")), this.$bannerBtnClose = document.getElementById("".concat(this.options.prefix, "-banner-btn-close")), this.$bannerBtnSettings = document.getElementById("".concat(this.options.prefix, "-banner-btn-settings")), this.$modalSettings = document.getElementById("".concat(this.options.prefix, "-settings")), this.$modalSettingsCheckboxes = this.$modalSettings.querySelectorAll("[data-storage-key]"), this.$modalSettingsCheckAll = this.$modalSettings.querySelectorAll('[data-toggle="'.concat(this.options.prefix, '-all"]')), this.$modalConfirm = document.getElementById("".concat(this.options.prefix, "-confirm")), this.$modalConfirmBtnOnce = document.getElementById("".concat(this.options.prefix, "-btn-once")), this.$modalConfirmBtnAlways = document.getElementById("".concat(this.options.prefix, "-btn-always")), this.$modalConfirmKeyInput = document.getElementById("".concat(this.options.prefix, "-confirm-key")), this.$modalConfirmHeadline = document.getElementById("".concat(this.options.prefix, "-confirm-info-headline")), this.$modalConfirmHelp = document.getElementById("".concat(this.options.prefix, "-confirm-info-help")), this.$modalConfirmDescription = document.getElementById("".concat(this.options.prefix, "-confirm-info-description")), this.$modalClose = document.querySelectorAll('[data-toggle="'.concat(this.options.prefix, '-close"]')), this.$btnOpenSettings = document.getElementById("".concat(this.options.prefix, "-settings-btn")), this._isModalOpen = !1, this._confirmCallback = null, this._checkVersion(), this._events(), this._init()
		}

		var e, n, a;
		return e = t, (n = [{
			key: "openModal", value: function (t) {
				this._isModalOpen = !0, document.body.style.overflow = "hidden", t.classList.add("active"), setTimeout((function () {
					return t.classList.add("show")
				}), 10)
			}
		}, {
			key: "closeModal", value: function () {
				var t = arguments.length > 0 && void 0 !== arguments[0] && arguments[0],
					e = document.querySelector(".".concat(this.options.prefix, "-modal.active"));
				!this._isModalOpen || !1 !== t && e !== t.target || null !== e && (o(), this._isModalOpen = !1, e.classList.remove("show"), setTimeout((function () {
					return e.classList.remove("active")
				}), 200))
			}
		}, {
			key: "closeBanner", value: function () {
				var t = this;
				this.$manager.classList.add("fading"), setTimeout((function () {
					t.$manager.classList.add("mini"), t.$manager.classList.remove("fading")
				}), 200)
			}
		}, {
			key: "setSetting", value: function (t, e) {
				var n = {};
				if ("*" === t) for (var o = 0; o < this.$modalSettingsCheckboxes.length; o++) n[this.$modalSettingsCheckboxes[o].getAttribute("data-storage-key")] = e; else "object" !== s(t) ? n[t] = e : n = t;
				this.closeBanner(), this._setStorageData(n), document.dispatchEvent(new CustomEvent(this.options.eventUpdatedName, {detail: null !== this._getLocalData() && this._getLocalData().settings}))
			}
		}, {
			key: "openConfirmationModal", value: function (t) {
				var e = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : function () {
				}, n = this.$modalSettings.querySelector('[data-storage-key="'.concat(t, '"]'));
				if (null !== n) {
					var o = n.parentElement, i = o.querySelector(".".concat(this.options.prefix, "-label")),
						r = o.querySelector(".".concat(this.options.prefix, "-help")),
						s = o.querySelector(".".concat(this.options.prefix, "-more-description"));
					this._confirmCallback = e, this.$modalConfirmKeyInput.setAttribute("value", t), this.$modalConfirmHeadline.innerHTML = i.innerHTML, this.$modalConfirmHelp.innerHTML = r.innerHTML, this.$modalConfirmDescription.innerHTML = s.innerHTML, this.openModal(this.$modalConfirm)
				}
			}
		}, {
			key: "getSettings", value: function () {
				var t = arguments.length > 0 && void 0 !== arguments[0] && arguments[0],
					e = JSON.parse(localStorage.getItem(this.options.storageKey));
				return null !== e && void 0 !== e.settings && !1 !== t && void 0 !== e.settings[t] && e.settings[t]
			}
		}, {
			key: "_init", value: function () {
				var t = this._getSessionData();
				(t = null === t ? {views: 1} : {views: t.views + 1}).views < this.options.viewsUntilBannerIsShown && this.$banner.classList.add("".concat(this.options.prefix, "-hidden")), null !== this._getLocalData() && this.$manager.classList.add("mini"), this.$manager.classList.add("active"), this._updateSettings(), sessionStorage.setItem(this.options.storageKey, JSON.stringify(t)), document.dispatchEvent(new CustomEvent(this.options.eventReadyName, {detail: null !== this._getLocalData() && this._getLocalData().settings}))
			}
		}, {
			key: "_confirmationClick", value: function () {
				var t = arguments.length > 0 && void 0 !== arguments[0] && arguments[0],
					e = this.$modalConfirmKeyInput.getAttribute("value");
				t && this.setSetting(e, !0), null !== this._confirmCallback && this._confirmCallback(), this.closeModal()
			}
		}, {
			key: "_getSessionData", value: function () {
				return JSON.parse(sessionStorage.getItem(this.options.storageKey))
			}
		}, {
			key: "_getLocalData", value: function () {
				return JSON.parse(localStorage.getItem(this.options.storageKey))
			}
		}, {
			key: "_setStorageData", value: function () {
				var t = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : {}, e = this._getLocalData();
				null !== e && "object" === s(e.settings) && (t = c({}, e.settings, {}, t)), localStorage.setItem(this.options.storageKey, JSON.stringify({
					version: this.options.version,
					settings: t
				})), this._updateSettings()
			}
		}, {
			key: "_updateSettings", value: function () {
				var t = this, e = this._getLocalData(), n = 0;
				null !== e && r(this.$modalSettingsCheckboxes, (function (o) {
					var i = o.getAttribute("data-storage-key");
					e.settings[i] && n++, n === t.$modalSettingsCheckboxes.length && r(t.$modalSettingsCheckAll, (function (t) {
						return t.checked = !0
					})), o.checked = e.settings[i]
				}))
			}
		}, {
			key: "_checkVersion", value: function () {
				var t = this._getLocalData();
				null !== t && t.version !== this.options.version && localStorage.removeItem(this.options.storageKey)
			}
		}, {
			key: "_events", value: function () {
				var t = this;
				this.$bannerBtnAcceptAll.addEventListener("click", (function () {
					return t.setSetting("*", !0)
				})), this.$bannerBtnClose.addEventListener("click", (function () {
					return t.setSetting("*", !1)
				})), this.$bannerBtnSettings.addEventListener("click", (function () {
					return t.openModal(t.$modalSettings)
				})), this.$modalSettings.addEventListener("click", i(this.closeModal, this)), r(this.$modalSettingsCheckboxes, (function (e) {
					e.addEventListener("change", (function (n) {
						return t.setSetting(e.getAttribute("data-storage-key"), e.checked)
					}))
				})), r(this.$modalSettingsCheckAll, (function (e) {
					e.addEventListener("change", (function () {
						t.setSetting("*", e.checked), r(t.$modalSettingsCheckAll, (function (t) {
							t.checked = e.checked
						}))
					}))
				})), this.$modalConfirm.addEventListener("click", i(this.closeModal, this)), this.$modalConfirmBtnOnce.addEventListener("click", (function () {
					return t._confirmationClick(!1)
				})), this.$modalConfirmBtnAlways.addEventListener("click", (function () {
					return t._confirmationClick(!0)
				})), r(this.$modalClose, (function (e) {
					e.addEventListener("click", (function () {
						return t.closeModal()
					}))
				})), document.addEventListener("keyup", (function (e) {
					27 === e.keyCode && t.closeModal()
				})), this.$btnOpenSettings.addEventListener("click", (function () {
					return t.openModal(t.$modalSettings)
				})), r(this.$collapseToggle, (function (e) {
					e.addEventListener("click", (function () {
						var n = document.getElementById("".concat(e.getAttribute("data-collapse")));
						null !== n && n.classList.toggle("".concat(t.options.prefix, "-hidden"))
					}))
				}))
			}
		}]) && d(e.prototype, n), a && d(e, a), t
	}();
	window.ConsentManager = h
}, function (t, e) {
}]);

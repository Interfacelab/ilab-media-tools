/*! @silvermine/videojs-airplay 2023-11-15 v1.3.0 */

(function() {
    function r(e, n, t) {
        function o(i, f) {
            if (!n[i]) {
                if (!e[i]) {
                    var c = "function" == typeof require && require;
                    if (!f && c) return c(i, !0);
                    if (u) return u(i, !0);
                    var a = new Error("Cannot find module '" + i + "'");
                    throw a.code = "MODULE_NOT_FOUND", a;
                }
                var p = n[i] = {
                    exports: {}
                };
                e[i][0].call(p.exports, function(r) {
                    var n = e[i][1][r];
                    return o(n || r);
                }, p, p.exports, r, e, n, t);
            }
            return n[i].exports;
        }
        for (var u = "function" == typeof require && require, i = 0; i < t.length; i++) o(t[i]);
        return o;
    }
    return r;
})()({
    1: [ function(require, module, exports) {
        var isCallable = require("../internals/is-callable");
        var tryToString = require("../internals/try-to-string");
        var $TypeError = TypeError;
        module.exports = function(argument) {
            if (isCallable(argument)) return argument;
            throw $TypeError(tryToString(argument) + " is not a function");
        };
    }, {
        "../internals/is-callable": 49,
        "../internals/try-to-string": 93
    } ],
    2: [ function(require, module, exports) {
        var isConstructor = require("../internals/is-constructor");
        var tryToString = require("../internals/try-to-string");
        var $TypeError = TypeError;
        module.exports = function(argument) {
            if (isConstructor(argument)) return argument;
            throw $TypeError(tryToString(argument) + " is not a constructor");
        };
    }, {
        "../internals/is-constructor": 50,
        "../internals/try-to-string": 93
    } ],
    3: [ function(require, module, exports) {
        var isObject = require("../internals/is-object");
        var $String = String;
        var $TypeError = TypeError;
        module.exports = function(argument) {
            if (isObject(argument)) return argument;
            throw $TypeError($String(argument) + " is not an object");
        };
    }, {
        "../internals/is-object": 54
    } ],
    4: [ function(require, module, exports) {
        var toIndexedObject = require("../internals/to-indexed-object");
        var toAbsoluteIndex = require("../internals/to-absolute-index");
        var lengthOfArrayLike = require("../internals/length-of-array-like");
        var createMethod = function(IS_INCLUDES) {
            return function($this, el, fromIndex) {
                var O = toIndexedObject($this);
                var length = lengthOfArrayLike(O);
                var index = toAbsoluteIndex(fromIndex, length);
                var value;
                if (IS_INCLUDES && el != el) while (length > index) {
                    value = O[index++];
                    if (value != value) return true;
                } else for (;length > index; index++) {
                    if ((IS_INCLUDES || index in O) && O[index] === el) return IS_INCLUDES || index || 0;
                }
                return !IS_INCLUDES && -1;
            };
        };
        module.exports = {
            includes: createMethod(true),
            indexOf: createMethod(false)
        };
    }, {
        "../internals/length-of-array-like": 57,
        "../internals/to-absolute-index": 84,
        "../internals/to-indexed-object": 85
    } ],
    5: [ function(require, module, exports) {
        var bind = require("../internals/function-bind-context");
        var uncurryThis = require("../internals/function-uncurry-this");
        var IndexedObject = require("../internals/indexed-object");
        var toObject = require("../internals/to-object");
        var lengthOfArrayLike = require("../internals/length-of-array-like");
        var arraySpeciesCreate = require("../internals/array-species-create");
        var push = uncurryThis([].push);
        var createMethod = function(TYPE) {
            var IS_MAP = TYPE == 1;
            var IS_FILTER = TYPE == 2;
            var IS_SOME = TYPE == 3;
            var IS_EVERY = TYPE == 4;
            var IS_FIND_INDEX = TYPE == 6;
            var IS_FILTER_REJECT = TYPE == 7;
            var NO_HOLES = TYPE == 5 || IS_FIND_INDEX;
            return function($this, callbackfn, that, specificCreate) {
                var O = toObject($this);
                var self = IndexedObject(O);
                var boundFunction = bind(callbackfn, that);
                var length = lengthOfArrayLike(self);
                var index = 0;
                var create = specificCreate || arraySpeciesCreate;
                var target = IS_MAP ? create($this, length) : IS_FILTER || IS_FILTER_REJECT ? create($this, 0) : undefined;
                var value, result;
                for (;length > index; index++) if (NO_HOLES || index in self) {
                    value = self[index];
                    result = boundFunction(value, index, O);
                    if (TYPE) {
                        if (IS_MAP) target[index] = result; else if (result) switch (TYPE) {
                          case 3:
                            return true;

                          case 5:
                            return value;

                          case 6:
                            return index;

                          case 2:
                            push(target, value);
                        } else switch (TYPE) {
                          case 4:
                            return false;

                          case 7:
                            push(target, value);
                        }
                    }
                }
                return IS_FIND_INDEX ? -1 : IS_SOME || IS_EVERY ? IS_EVERY : target;
            };
        };
        module.exports = {
            forEach: createMethod(0),
            map: createMethod(1),
            filter: createMethod(2),
            some: createMethod(3),
            every: createMethod(4),
            find: createMethod(5),
            findIndex: createMethod(6),
            filterReject: createMethod(7)
        };
    }, {
        "../internals/array-species-create": 9,
        "../internals/function-bind-context": 30,
        "../internals/function-uncurry-this": 36,
        "../internals/indexed-object": 45,
        "../internals/length-of-array-like": 57,
        "../internals/to-object": 88
    } ],
    6: [ function(require, module, exports) {
        var toAbsoluteIndex = require("../internals/to-absolute-index");
        var lengthOfArrayLike = require("../internals/length-of-array-like");
        var createProperty = require("../internals/create-property");
        var $Array = Array;
        var max = Math.max;
        module.exports = function(O, start, end) {
            var length = lengthOfArrayLike(O);
            var k = toAbsoluteIndex(start, length);
            var fin = toAbsoluteIndex(end === undefined ? length : end, length);
            var result = $Array(max(fin - k, 0));
            for (var n = 0; k < fin; k++, n++) createProperty(result, n, O[k]);
            result.length = n;
            return result;
        };
    }, {
        "../internals/create-property": 16,
        "../internals/length-of-array-like": 57,
        "../internals/to-absolute-index": 84
    } ],
    7: [ function(require, module, exports) {
        var uncurryThis = require("../internals/function-uncurry-this");
        module.exports = uncurryThis([].slice);
    }, {
        "../internals/function-uncurry-this": 36
    } ],
    8: [ function(require, module, exports) {
        var isArray = require("../internals/is-array");
        var isConstructor = require("../internals/is-constructor");
        var isObject = require("../internals/is-object");
        var wellKnownSymbol = require("../internals/well-known-symbol");
        var SPECIES = wellKnownSymbol("species");
        var $Array = Array;
        module.exports = function(originalArray) {
            var C;
            if (isArray(originalArray)) {
                C = originalArray.constructor;
                if (isConstructor(C) && (C === $Array || isArray(C.prototype))) C = undefined; else if (isObject(C)) {
                    C = C[SPECIES];
                    if (C === null) C = undefined;
                }
            }
            return C === undefined ? $Array : C;
        };
    }, {
        "../internals/is-array": 48,
        "../internals/is-constructor": 50,
        "../internals/is-object": 54,
        "../internals/well-known-symbol": 100
    } ],
    9: [ function(require, module, exports) {
        var arraySpeciesConstructor = require("../internals/array-species-constructor");
        module.exports = function(originalArray, length) {
            return new (arraySpeciesConstructor(originalArray))(length === 0 ? 0 : length);
        };
    }, {
        "../internals/array-species-constructor": 8
    } ],
    10: [ function(require, module, exports) {
        var uncurryThis = require("../internals/function-uncurry-this");
        var toString = uncurryThis({}.toString);
        var stringSlice = uncurryThis("".slice);
        module.exports = function(it) {
            return stringSlice(toString(it), 8, -1);
        };
    }, {
        "../internals/function-uncurry-this": 36
    } ],
    11: [ function(require, module, exports) {
        var TO_STRING_TAG_SUPPORT = require("../internals/to-string-tag-support");
        var isCallable = require("../internals/is-callable");
        var classofRaw = require("../internals/classof-raw");
        var wellKnownSymbol = require("../internals/well-known-symbol");
        var TO_STRING_TAG = wellKnownSymbol("toStringTag");
        var $Object = Object;
        var CORRECT_ARGUMENTS = classofRaw(function() {
            return arguments;
        }()) == "Arguments";
        var tryGet = function(it, key) {
            try {
                return it[key];
            } catch (error) {}
        };
        module.exports = TO_STRING_TAG_SUPPORT ? classofRaw : function(it) {
            var O, tag, result;
            return it === undefined ? "Undefined" : it === null ? "Null" : typeof (tag = tryGet(O = $Object(it), TO_STRING_TAG)) == "string" ? tag : CORRECT_ARGUMENTS ? classofRaw(O) : (result = classofRaw(O)) == "Object" && isCallable(O.callee) ? "Arguments" : result;
        };
    }, {
        "../internals/classof-raw": 10,
        "../internals/is-callable": 49,
        "../internals/to-string-tag-support": 91,
        "../internals/well-known-symbol": 100
    } ],
    12: [ function(require, module, exports) {
        var hasOwn = require("../internals/has-own-property");
        var ownKeys = require("../internals/own-keys");
        var getOwnPropertyDescriptorModule = require("../internals/object-get-own-property-descriptor");
        var definePropertyModule = require("../internals/object-define-property");
        module.exports = function(target, source, exceptions) {
            var keys = ownKeys(source);
            var defineProperty = definePropertyModule.f;
            var getOwnPropertyDescriptor = getOwnPropertyDescriptorModule.f;
            for (var i = 0; i < keys.length; i++) {
                var key = keys[i];
                if (!hasOwn(target, key) && !(exceptions && hasOwn(exceptions, key))) {
                    defineProperty(target, key, getOwnPropertyDescriptor(source, key));
                }
            }
        };
    }, {
        "../internals/has-own-property": 41,
        "../internals/object-define-property": 62,
        "../internals/object-get-own-property-descriptor": 63,
        "../internals/own-keys": 74
    } ],
    13: [ function(require, module, exports) {
        var fails = require("../internals/fails");
        module.exports = !fails(function() {
            function F() {}
            F.prototype.constructor = null;
            return Object.getPrototypeOf(new F()) !== F.prototype;
        });
    }, {
        "../internals/fails": 28
    } ],
    14: [ function(require, module, exports) {
        var DESCRIPTORS = require("../internals/descriptors");
        var definePropertyModule = require("../internals/object-define-property");
        var createPropertyDescriptor = require("../internals/create-property-descriptor");
        module.exports = DESCRIPTORS ? function(object, key, value) {
            return definePropertyModule.f(object, key, createPropertyDescriptor(1, value));
        } : function(object, key, value) {
            object[key] = value;
            return object;
        };
    }, {
        "../internals/create-property-descriptor": 15,
        "../internals/descriptors": 21,
        "../internals/object-define-property": 62
    } ],
    15: [ function(require, module, exports) {
        module.exports = function(bitmap, value) {
            return {
                enumerable: !(bitmap & 1),
                configurable: !(bitmap & 2),
                writable: !(bitmap & 4),
                value: value
            };
        };
    }, {} ],
    16: [ function(require, module, exports) {
        "use strict";
        var toPropertyKey = require("../internals/to-property-key");
        var definePropertyModule = require("../internals/object-define-property");
        var createPropertyDescriptor = require("../internals/create-property-descriptor");
        module.exports = function(object, key, value) {
            var propertyKey = toPropertyKey(key);
            if (propertyKey in object) definePropertyModule.f(object, propertyKey, createPropertyDescriptor(0, value)); else object[propertyKey] = value;
        };
    }, {
        "../internals/create-property-descriptor": 15,
        "../internals/object-define-property": 62,
        "../internals/to-property-key": 90
    } ],
    17: [ function(require, module, exports) {
        "use strict";
        var anObject = require("../internals/an-object");
        var ordinaryToPrimitive = require("../internals/ordinary-to-primitive");
        var $TypeError = TypeError;
        module.exports = function(hint) {
            anObject(this);
            if (hint === "string" || hint === "default") hint = "string"; else if (hint !== "number") throw $TypeError("Incorrect hint");
            return ordinaryToPrimitive(this, hint);
        };
    }, {
        "../internals/an-object": 3,
        "../internals/ordinary-to-primitive": 73
    } ],
    18: [ function(require, module, exports) {
        var makeBuiltIn = require("../internals/make-built-in");
        var defineProperty = require("../internals/object-define-property");
        module.exports = function(target, name, descriptor) {
            if (descriptor.get) makeBuiltIn(descriptor.get, name, {
                getter: true
            });
            if (descriptor.set) makeBuiltIn(descriptor.set, name, {
                setter: true
            });
            return defineProperty.f(target, name, descriptor);
        };
    }, {
        "../internals/make-built-in": 58,
        "../internals/object-define-property": 62
    } ],
    19: [ function(require, module, exports) {
        var isCallable = require("../internals/is-callable");
        var definePropertyModule = require("../internals/object-define-property");
        var makeBuiltIn = require("../internals/make-built-in");
        var defineGlobalProperty = require("../internals/define-global-property");
        module.exports = function(O, key, value, options) {
            if (!options) options = {};
            var simple = options.enumerable;
            var name = options.name !== undefined ? options.name : key;
            if (isCallable(value)) makeBuiltIn(value, name, options);
            if (options.global) {
                if (simple) O[key] = value; else defineGlobalProperty(key, value);
            } else {
                try {
                    if (!options.unsafe) delete O[key]; else if (O[key]) simple = true;
                } catch (error) {}
                if (simple) O[key] = value; else definePropertyModule.f(O, key, {
                    value: value,
                    enumerable: false,
                    configurable: !options.nonConfigurable,
                    writable: !options.nonWritable
                });
            }
            return O;
        };
    }, {
        "../internals/define-global-property": 20,
        "../internals/is-callable": 49,
        "../internals/make-built-in": 58,
        "../internals/object-define-property": 62
    } ],
    20: [ function(require, module, exports) {
        var global = require("../internals/global");
        var defineProperty = Object.defineProperty;
        module.exports = function(key, value) {
            try {
                defineProperty(global, key, {
                    value: value,
                    configurable: true,
                    writable: true
                });
            } catch (error) {
                global[key] = value;
            }
            return value;
        };
    }, {
        "../internals/global": 40
    } ],
    21: [ function(require, module, exports) {
        var fails = require("../internals/fails");
        module.exports = !fails(function() {
            return Object.defineProperty({}, 1, {
                get: function() {
                    return 7;
                }
            })[1] != 7;
        });
    }, {
        "../internals/fails": 28
    } ],
    22: [ function(require, module, exports) {
        var documentAll = typeof document == "object" && document.all;
        var IS_HTMLDDA = typeof documentAll == "undefined" && documentAll !== undefined;
        module.exports = {
            all: documentAll,
            IS_HTMLDDA: IS_HTMLDDA
        };
    }, {} ],
    23: [ function(require, module, exports) {
        var global = require("../internals/global");
        var isObject = require("../internals/is-object");
        var document = global.document;
        var EXISTS = isObject(document) && isObject(document.createElement);
        module.exports = function(it) {
            return EXISTS ? document.createElement(it) : {};
        };
    }, {
        "../internals/global": 40,
        "../internals/is-object": 54
    } ],
    24: [ function(require, module, exports) {
        module.exports = typeof navigator != "undefined" && String(navigator.userAgent) || "";
    }, {} ],
    25: [ function(require, module, exports) {
        var global = require("../internals/global");
        var userAgent = require("../internals/engine-user-agent");
        var process = global.process;
        var Deno = global.Deno;
        var versions = process && process.versions || Deno && Deno.version;
        var v8 = versions && versions.v8;
        var match, version;
        if (v8) {
            match = v8.split(".");
            version = match[0] > 0 && match[0] < 4 ? 1 : +(match[0] + match[1]);
        }
        if (!version && userAgent) {
            match = userAgent.match(/Edge\/(\d+)/);
            if (!match || match[1] >= 74) {
                match = userAgent.match(/Chrome\/(\d+)/);
                if (match) version = +match[1];
            }
        }
        module.exports = version;
    }, {
        "../internals/engine-user-agent": 24,
        "../internals/global": 40
    } ],
    26: [ function(require, module, exports) {
        module.exports = [ "constructor", "hasOwnProperty", "isPrototypeOf", "propertyIsEnumerable", "toLocaleString", "toString", "valueOf" ];
    }, {} ],
    27: [ function(require, module, exports) {
        var global = require("../internals/global");
        var getOwnPropertyDescriptor = require("../internals/object-get-own-property-descriptor").f;
        var createNonEnumerableProperty = require("../internals/create-non-enumerable-property");
        var defineBuiltIn = require("../internals/define-built-in");
        var defineGlobalProperty = require("../internals/define-global-property");
        var copyConstructorProperties = require("../internals/copy-constructor-properties");
        var isForced = require("../internals/is-forced");
        module.exports = function(options, source) {
            var TARGET = options.target;
            var GLOBAL = options.global;
            var STATIC = options.stat;
            var FORCED, target, key, targetProperty, sourceProperty, descriptor;
            if (GLOBAL) {
                target = global;
            } else if (STATIC) {
                target = global[TARGET] || defineGlobalProperty(TARGET, {});
            } else {
                target = (global[TARGET] || {}).prototype;
            }
            if (target) for (key in source) {
                sourceProperty = source[key];
                if (options.dontCallGetSet) {
                    descriptor = getOwnPropertyDescriptor(target, key);
                    targetProperty = descriptor && descriptor.value;
                } else targetProperty = target[key];
                FORCED = isForced(GLOBAL ? key : TARGET + (STATIC ? "." : "#") + key, options.forced);
                if (!FORCED && targetProperty !== undefined) {
                    if (typeof sourceProperty == typeof targetProperty) continue;
                    copyConstructorProperties(sourceProperty, targetProperty);
                }
                if (options.sham || targetProperty && targetProperty.sham) {
                    createNonEnumerableProperty(sourceProperty, "sham", true);
                }
                defineBuiltIn(target, key, sourceProperty, options);
            }
        };
    }, {
        "../internals/copy-constructor-properties": 12,
        "../internals/create-non-enumerable-property": 14,
        "../internals/define-built-in": 19,
        "../internals/define-global-property": 20,
        "../internals/global": 40,
        "../internals/is-forced": 52,
        "../internals/object-get-own-property-descriptor": 63
    } ],
    28: [ function(require, module, exports) {
        module.exports = function(exec) {
            try {
                return !!exec();
            } catch (error) {
                return true;
            }
        };
    }, {} ],
    29: [ function(require, module, exports) {
        var NATIVE_BIND = require("../internals/function-bind-native");
        var FunctionPrototype = Function.prototype;
        var apply = FunctionPrototype.apply;
        var call = FunctionPrototype.call;
        module.exports = typeof Reflect == "object" && Reflect.apply || (NATIVE_BIND ? call.bind(apply) : function() {
            return call.apply(apply, arguments);
        });
    }, {
        "../internals/function-bind-native": 31
    } ],
    30: [ function(require, module, exports) {
        var uncurryThis = require("../internals/function-uncurry-this-clause");
        var aCallable = require("../internals/a-callable");
        var NATIVE_BIND = require("../internals/function-bind-native");
        var bind = uncurryThis(uncurryThis.bind);
        module.exports = function(fn, that) {
            aCallable(fn);
            return that === undefined ? fn : NATIVE_BIND ? bind(fn, that) : function() {
                return fn.apply(that, arguments);
            };
        };
    }, {
        "../internals/a-callable": 1,
        "../internals/function-bind-native": 31,
        "../internals/function-uncurry-this-clause": 35
    } ],
    31: [ function(require, module, exports) {
        var fails = require("../internals/fails");
        module.exports = !fails(function() {
            var test = function() {}.bind();
            return typeof test != "function" || test.hasOwnProperty("prototype");
        });
    }, {
        "../internals/fails": 28
    } ],
    32: [ function(require, module, exports) {
        "use strict";
        var uncurryThis = require("../internals/function-uncurry-this");
        var aCallable = require("../internals/a-callable");
        var isObject = require("../internals/is-object");
        var hasOwn = require("../internals/has-own-property");
        var arraySlice = require("../internals/array-slice");
        var NATIVE_BIND = require("../internals/function-bind-native");
        var $Function = Function;
        var concat = uncurryThis([].concat);
        var join = uncurryThis([].join);
        var factories = {};
        var construct = function(C, argsLength, args) {
            if (!hasOwn(factories, argsLength)) {
                for (var list = [], i = 0; i < argsLength; i++) list[i] = "a[" + i + "]";
                factories[argsLength] = $Function("C,a", "return new C(" + join(list, ",") + ")");
            }
            return factories[argsLength](C, args);
        };
        module.exports = NATIVE_BIND ? $Function.bind : function bind(that) {
            var F = aCallable(this);
            var Prototype = F.prototype;
            var partArgs = arraySlice(arguments, 1);
            var boundFunction = function bound() {
                var args = concat(partArgs, arraySlice(arguments));
                return this instanceof boundFunction ? construct(F, args.length, args) : F.apply(that, args);
            };
            if (isObject(Prototype)) boundFunction.prototype = Prototype;
            return boundFunction;
        };
    }, {
        "../internals/a-callable": 1,
        "../internals/array-slice": 7,
        "../internals/function-bind-native": 31,
        "../internals/function-uncurry-this": 36,
        "../internals/has-own-property": 41,
        "../internals/is-object": 54
    } ],
    33: [ function(require, module, exports) {
        var NATIVE_BIND = require("../internals/function-bind-native");
        var call = Function.prototype.call;
        module.exports = NATIVE_BIND ? call.bind(call) : function() {
            return call.apply(call, arguments);
        };
    }, {
        "../internals/function-bind-native": 31
    } ],
    34: [ function(require, module, exports) {
        var DESCRIPTORS = require("../internals/descriptors");
        var hasOwn = require("../internals/has-own-property");
        var FunctionPrototype = Function.prototype;
        var getDescriptor = DESCRIPTORS && Object.getOwnPropertyDescriptor;
        var EXISTS = hasOwn(FunctionPrototype, "name");
        var PROPER = EXISTS && function something() {}.name === "something";
        var CONFIGURABLE = EXISTS && (!DESCRIPTORS || DESCRIPTORS && getDescriptor(FunctionPrototype, "name").configurable);
        module.exports = {
            EXISTS: EXISTS,
            PROPER: PROPER,
            CONFIGURABLE: CONFIGURABLE
        };
    }, {
        "../internals/descriptors": 21,
        "../internals/has-own-property": 41
    } ],
    35: [ function(require, module, exports) {
        var classofRaw = require("../internals/classof-raw");
        var uncurryThis = require("../internals/function-uncurry-this");
        module.exports = function(fn) {
            if (classofRaw(fn) === "Function") return uncurryThis(fn);
        };
    }, {
        "../internals/classof-raw": 10,
        "../internals/function-uncurry-this": 36
    } ],
    36: [ function(require, module, exports) {
        var NATIVE_BIND = require("../internals/function-bind-native");
        var FunctionPrototype = Function.prototype;
        var call = FunctionPrototype.call;
        var uncurryThisWithBind = NATIVE_BIND && FunctionPrototype.bind.bind(call, call);
        module.exports = NATIVE_BIND ? uncurryThisWithBind : function(fn) {
            return function() {
                return call.apply(fn, arguments);
            };
        };
    }, {
        "../internals/function-bind-native": 31
    } ],
    37: [ function(require, module, exports) {
        var global = require("../internals/global");
        var isCallable = require("../internals/is-callable");
        var aFunction = function(argument) {
            return isCallable(argument) ? argument : undefined;
        };
        module.exports = function(namespace, method) {
            return arguments.length < 2 ? aFunction(global[namespace]) : global[namespace] && global[namespace][method];
        };
    }, {
        "../internals/global": 40,
        "../internals/is-callable": 49
    } ],
    38: [ function(require, module, exports) {
        var uncurryThis = require("../internals/function-uncurry-this");
        var isArray = require("../internals/is-array");
        var isCallable = require("../internals/is-callable");
        var classof = require("../internals/classof-raw");
        var toString = require("../internals/to-string");
        var push = uncurryThis([].push);
        module.exports = function(replacer) {
            if (isCallable(replacer)) return replacer;
            if (!isArray(replacer)) return;
            var rawLength = replacer.length;
            var keys = [];
            for (var i = 0; i < rawLength; i++) {
                var element = replacer[i];
                if (typeof element == "string") push(keys, element); else if (typeof element == "number" || classof(element) == "Number" || classof(element) == "String") push(keys, toString(element));
            }
            var keysLength = keys.length;
            var root = true;
            return function(key, value) {
                if (root) {
                    root = false;
                    return value;
                }
                if (isArray(this)) return value;
                for (var j = 0; j < keysLength; j++) if (keys[j] === key) return value;
            };
        };
    }, {
        "../internals/classof-raw": 10,
        "../internals/function-uncurry-this": 36,
        "../internals/is-array": 48,
        "../internals/is-callable": 49,
        "../internals/to-string": 92
    } ],
    39: [ function(require, module, exports) {
        var aCallable = require("../internals/a-callable");
        var isNullOrUndefined = require("../internals/is-null-or-undefined");
        module.exports = function(V, P) {
            var func = V[P];
            return isNullOrUndefined(func) ? undefined : aCallable(func);
        };
    }, {
        "../internals/a-callable": 1,
        "../internals/is-null-or-undefined": 53
    } ],
    40: [ function(require, module, exports) {
        (function(global) {
            (function() {
                var check = function(it) {
                    return it && it.Math == Math && it;
                };
                module.exports = check(typeof globalThis == "object" && globalThis) || check(typeof window == "object" && window) || check(typeof self == "object" && self) || check(typeof global == "object" && global) || function() {
                    return this;
                }() || Function("return this")();
            }).call(this);
        }).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {});
    }, {} ],
    41: [ function(require, module, exports) {
        var uncurryThis = require("../internals/function-uncurry-this");
        var toObject = require("../internals/to-object");
        var hasOwnProperty = uncurryThis({}.hasOwnProperty);
        module.exports = Object.hasOwn || function hasOwn(it, key) {
            return hasOwnProperty(toObject(it), key);
        };
    }, {
        "../internals/function-uncurry-this": 36,
        "../internals/to-object": 88
    } ],
    42: [ function(require, module, exports) {
        module.exports = {};
    }, {} ],
    43: [ function(require, module, exports) {
        var getBuiltIn = require("../internals/get-built-in");
        module.exports = getBuiltIn("document", "documentElement");
    }, {
        "../internals/get-built-in": 37
    } ],
    44: [ function(require, module, exports) {
        var DESCRIPTORS = require("../internals/descriptors");
        var fails = require("../internals/fails");
        var createElement = require("../internals/document-create-element");
        module.exports = !DESCRIPTORS && !fails(function() {
            return Object.defineProperty(createElement("div"), "a", {
                get: function() {
                    return 7;
                }
            }).a != 7;
        });
    }, {
        "../internals/descriptors": 21,
        "../internals/document-create-element": 23,
        "../internals/fails": 28
    } ],
    45: [ function(require, module, exports) {
        var uncurryThis = require("../internals/function-uncurry-this");
        var fails = require("../internals/fails");
        var classof = require("../internals/classof-raw");
        var $Object = Object;
        var split = uncurryThis("".split);
        module.exports = fails(function() {
            return !$Object("z").propertyIsEnumerable(0);
        }) ? function(it) {
            return classof(it) == "String" ? split(it, "") : $Object(it);
        } : $Object;
    }, {
        "../internals/classof-raw": 10,
        "../internals/fails": 28,
        "../internals/function-uncurry-this": 36
    } ],
    46: [ function(require, module, exports) {
        var uncurryThis = require("../internals/function-uncurry-this");
        var isCallable = require("../internals/is-callable");
        var store = require("../internals/shared-store");
        var functionToString = uncurryThis(Function.toString);
        if (!isCallable(store.inspectSource)) {
            store.inspectSource = function(it) {
                return functionToString(it);
            };
        }
        module.exports = store.inspectSource;
    }, {
        "../internals/function-uncurry-this": 36,
        "../internals/is-callable": 49,
        "../internals/shared-store": 79
    } ],
    47: [ function(require, module, exports) {
        var NATIVE_WEAK_MAP = require("../internals/weak-map-basic-detection");
        var global = require("../internals/global");
        var isObject = require("../internals/is-object");
        var createNonEnumerableProperty = require("../internals/create-non-enumerable-property");
        var hasOwn = require("../internals/has-own-property");
        var shared = require("../internals/shared-store");
        var sharedKey = require("../internals/shared-key");
        var hiddenKeys = require("../internals/hidden-keys");
        var OBJECT_ALREADY_INITIALIZED = "Object already initialized";
        var TypeError = global.TypeError;
        var WeakMap = global.WeakMap;
        var set, get, has;
        var enforce = function(it) {
            return has(it) ? get(it) : set(it, {});
        };
        var getterFor = function(TYPE) {
            return function(it) {
                var state;
                if (!isObject(it) || (state = get(it)).type !== TYPE) {
                    throw TypeError("Incompatible receiver, " + TYPE + " required");
                }
                return state;
            };
        };
        if (NATIVE_WEAK_MAP || shared.state) {
            var store = shared.state || (shared.state = new WeakMap());
            store.get = store.get;
            store.has = store.has;
            store.set = store.set;
            set = function(it, metadata) {
                if (store.has(it)) throw TypeError(OBJECT_ALREADY_INITIALIZED);
                metadata.facade = it;
                store.set(it, metadata);
                return metadata;
            };
            get = function(it) {
                return store.get(it) || {};
            };
            has = function(it) {
                return store.has(it);
            };
        } else {
            var STATE = sharedKey("state");
            hiddenKeys[STATE] = true;
            set = function(it, metadata) {
                if (hasOwn(it, STATE)) throw TypeError(OBJECT_ALREADY_INITIALIZED);
                metadata.facade = it;
                createNonEnumerableProperty(it, STATE, metadata);
                return metadata;
            };
            get = function(it) {
                return hasOwn(it, STATE) ? it[STATE] : {};
            };
            has = function(it) {
                return hasOwn(it, STATE);
            };
        }
        module.exports = {
            set: set,
            get: get,
            has: has,
            enforce: enforce,
            getterFor: getterFor
        };
    }, {
        "../internals/create-non-enumerable-property": 14,
        "../internals/global": 40,
        "../internals/has-own-property": 41,
        "../internals/hidden-keys": 42,
        "../internals/is-object": 54,
        "../internals/shared-key": 78,
        "../internals/shared-store": 79,
        "../internals/weak-map-basic-detection": 97
    } ],
    48: [ function(require, module, exports) {
        var classof = require("../internals/classof-raw");
        module.exports = Array.isArray || function isArray(argument) {
            return classof(argument) == "Array";
        };
    }, {
        "../internals/classof-raw": 10
    } ],
    49: [ function(require, module, exports) {
        var $documentAll = require("../internals/document-all");
        var documentAll = $documentAll.all;
        module.exports = $documentAll.IS_HTMLDDA ? function(argument) {
            return typeof argument == "function" || argument === documentAll;
        } : function(argument) {
            return typeof argument == "function";
        };
    }, {
        "../internals/document-all": 22
    } ],
    50: [ function(require, module, exports) {
        var uncurryThis = require("../internals/function-uncurry-this");
        var fails = require("../internals/fails");
        var isCallable = require("../internals/is-callable");
        var classof = require("../internals/classof");
        var getBuiltIn = require("../internals/get-built-in");
        var inspectSource = require("../internals/inspect-source");
        var noop = function() {};
        var empty = [];
        var construct = getBuiltIn("Reflect", "construct");
        var constructorRegExp = /^\s*(?:class|function)\b/;
        var exec = uncurryThis(constructorRegExp.exec);
        var INCORRECT_TO_STRING = !constructorRegExp.exec(noop);
        var isConstructorModern = function isConstructor(argument) {
            if (!isCallable(argument)) return false;
            try {
                construct(noop, empty, argument);
                return true;
            } catch (error) {
                return false;
            }
        };
        var isConstructorLegacy = function isConstructor(argument) {
            if (!isCallable(argument)) return false;
            switch (classof(argument)) {
              case "AsyncFunction":
              case "GeneratorFunction":
              case "AsyncGeneratorFunction":
                return false;
            }
            try {
                return INCORRECT_TO_STRING || !!exec(constructorRegExp, inspectSource(argument));
            } catch (error) {
                return true;
            }
        };
        isConstructorLegacy.sham = true;
        module.exports = !construct || fails(function() {
            var called;
            return isConstructorModern(isConstructorModern.call) || !isConstructorModern(Object) || !isConstructorModern(function() {
                called = true;
            }) || called;
        }) ? isConstructorLegacy : isConstructorModern;
    }, {
        "../internals/classof": 11,
        "../internals/fails": 28,
        "../internals/function-uncurry-this": 36,
        "../internals/get-built-in": 37,
        "../internals/inspect-source": 46,
        "../internals/is-callable": 49
    } ],
    51: [ function(require, module, exports) {
        var hasOwn = require("../internals/has-own-property");
        module.exports = function(descriptor) {
            return descriptor !== undefined && (hasOwn(descriptor, "value") || hasOwn(descriptor, "writable"));
        };
    }, {
        "../internals/has-own-property": 41
    } ],
    52: [ function(require, module, exports) {
        var fails = require("../internals/fails");
        var isCallable = require("../internals/is-callable");
        var replacement = /#|\.prototype\./;
        var isForced = function(feature, detection) {
            var value = data[normalize(feature)];
            return value == POLYFILL ? true : value == NATIVE ? false : isCallable(detection) ? fails(detection) : !!detection;
        };
        var normalize = isForced.normalize = function(string) {
            return String(string).replace(replacement, ".").toLowerCase();
        };
        var data = isForced.data = {};
        var NATIVE = isForced.NATIVE = "N";
        var POLYFILL = isForced.POLYFILL = "P";
        module.exports = isForced;
    }, {
        "../internals/fails": 28,
        "../internals/is-callable": 49
    } ],
    53: [ function(require, module, exports) {
        module.exports = function(it) {
            return it === null || it === undefined;
        };
    }, {} ],
    54: [ function(require, module, exports) {
        var isCallable = require("../internals/is-callable");
        var $documentAll = require("../internals/document-all");
        var documentAll = $documentAll.all;
        module.exports = $documentAll.IS_HTMLDDA ? function(it) {
            return typeof it == "object" ? it !== null : isCallable(it) || it === documentAll;
        } : function(it) {
            return typeof it == "object" ? it !== null : isCallable(it);
        };
    }, {
        "../internals/document-all": 22,
        "../internals/is-callable": 49
    } ],
    55: [ function(require, module, exports) {
        module.exports = false;
    }, {} ],
    56: [ function(require, module, exports) {
        var getBuiltIn = require("../internals/get-built-in");
        var isCallable = require("../internals/is-callable");
        var isPrototypeOf = require("../internals/object-is-prototype-of");
        var USE_SYMBOL_AS_UID = require("../internals/use-symbol-as-uid");
        var $Object = Object;
        module.exports = USE_SYMBOL_AS_UID ? function(it) {
            return typeof it == "symbol";
        } : function(it) {
            var $Symbol = getBuiltIn("Symbol");
            return isCallable($Symbol) && isPrototypeOf($Symbol.prototype, $Object(it));
        };
    }, {
        "../internals/get-built-in": 37,
        "../internals/is-callable": 49,
        "../internals/object-is-prototype-of": 68,
        "../internals/use-symbol-as-uid": 95
    } ],
    57: [ function(require, module, exports) {
        var toLength = require("../internals/to-length");
        module.exports = function(obj) {
            return toLength(obj.length);
        };
    }, {
        "../internals/to-length": 87
    } ],
    58: [ function(require, module, exports) {
        var uncurryThis = require("../internals/function-uncurry-this");
        var fails = require("../internals/fails");
        var isCallable = require("../internals/is-callable");
        var hasOwn = require("../internals/has-own-property");
        var DESCRIPTORS = require("../internals/descriptors");
        var CONFIGURABLE_FUNCTION_NAME = require("../internals/function-name").CONFIGURABLE;
        var inspectSource = require("../internals/inspect-source");
        var InternalStateModule = require("../internals/internal-state");
        var enforceInternalState = InternalStateModule.enforce;
        var getInternalState = InternalStateModule.get;
        var $String = String;
        var defineProperty = Object.defineProperty;
        var stringSlice = uncurryThis("".slice);
        var replace = uncurryThis("".replace);
        var join = uncurryThis([].join);
        var CONFIGURABLE_LENGTH = DESCRIPTORS && !fails(function() {
            return defineProperty(function() {}, "length", {
                value: 8
            }).length !== 8;
        });
        var TEMPLATE = String(String).split("String");
        var makeBuiltIn = module.exports = function(value, name, options) {
            if (stringSlice($String(name), 0, 7) === "Symbol(") {
                name = "[" + replace($String(name), /^Symbol\(([^)]*)\)/, "$1") + "]";
            }
            if (options && options.getter) name = "get " + name;
            if (options && options.setter) name = "set " + name;
            if (!hasOwn(value, "name") || CONFIGURABLE_FUNCTION_NAME && value.name !== name) {
                if (DESCRIPTORS) defineProperty(value, "name", {
                    value: name,
                    configurable: true
                }); else value.name = name;
            }
            if (CONFIGURABLE_LENGTH && options && hasOwn(options, "arity") && value.length !== options.arity) {
                defineProperty(value, "length", {
                    value: options.arity
                });
            }
            try {
                if (options && hasOwn(options, "constructor") && options.constructor) {
                    if (DESCRIPTORS) defineProperty(value, "prototype", {
                        writable: false
                    });
                } else if (value.prototype) value.prototype = undefined;
            } catch (error) {}
            var state = enforceInternalState(value);
            if (!hasOwn(state, "source")) {
                state.source = join(TEMPLATE, typeof name == "string" ? name : "");
            }
            return value;
        };
        Function.prototype.toString = makeBuiltIn(function toString() {
            return isCallable(this) && getInternalState(this).source || inspectSource(this);
        }, "toString");
    }, {
        "../internals/descriptors": 21,
        "../internals/fails": 28,
        "../internals/function-name": 34,
        "../internals/function-uncurry-this": 36,
        "../internals/has-own-property": 41,
        "../internals/inspect-source": 46,
        "../internals/internal-state": 47,
        "../internals/is-callable": 49
    } ],
    59: [ function(require, module, exports) {
        var ceil = Math.ceil;
        var floor = Math.floor;
        module.exports = Math.trunc || function trunc(x) {
            var n = +x;
            return (n > 0 ? floor : ceil)(n);
        };
    }, {} ],
    60: [ function(require, module, exports) {
        var anObject = require("../internals/an-object");
        var definePropertiesModule = require("../internals/object-define-properties");
        var enumBugKeys = require("../internals/enum-bug-keys");
        var hiddenKeys = require("../internals/hidden-keys");
        var html = require("../internals/html");
        var documentCreateElement = require("../internals/document-create-element");
        var sharedKey = require("../internals/shared-key");
        var GT = ">";
        var LT = "<";
        var PROTOTYPE = "prototype";
        var SCRIPT = "script";
        var IE_PROTO = sharedKey("IE_PROTO");
        var EmptyConstructor = function() {};
        var scriptTag = function(content) {
            return LT + SCRIPT + GT + content + LT + "/" + SCRIPT + GT;
        };
        var NullProtoObjectViaActiveX = function(activeXDocument) {
            activeXDocument.write(scriptTag(""));
            activeXDocument.close();
            var temp = activeXDocument.parentWindow.Object;
            activeXDocument = null;
            return temp;
        };
        var NullProtoObjectViaIFrame = function() {
            var iframe = documentCreateElement("iframe");
            var JS = "java" + SCRIPT + ":";
            var iframeDocument;
            iframe.style.display = "none";
            html.appendChild(iframe);
            iframe.src = String(JS);
            iframeDocument = iframe.contentWindow.document;
            iframeDocument.open();
            iframeDocument.write(scriptTag("document.F=Object"));
            iframeDocument.close();
            return iframeDocument.F;
        };
        var activeXDocument;
        var NullProtoObject = function() {
            try {
                activeXDocument = new ActiveXObject("htmlfile");
            } catch (error) {}
            NullProtoObject = typeof document != "undefined" ? document.domain && activeXDocument ? NullProtoObjectViaActiveX(activeXDocument) : NullProtoObjectViaIFrame() : NullProtoObjectViaActiveX(activeXDocument);
            var length = enumBugKeys.length;
            while (length--) delete NullProtoObject[PROTOTYPE][enumBugKeys[length]];
            return NullProtoObject();
        };
        hiddenKeys[IE_PROTO] = true;
        module.exports = Object.create || function create(O, Properties) {
            var result;
            if (O !== null) {
                EmptyConstructor[PROTOTYPE] = anObject(O);
                result = new EmptyConstructor();
                EmptyConstructor[PROTOTYPE] = null;
                result[IE_PROTO] = O;
            } else result = NullProtoObject();
            return Properties === undefined ? result : definePropertiesModule.f(result, Properties);
        };
    }, {
        "../internals/an-object": 3,
        "../internals/document-create-element": 23,
        "../internals/enum-bug-keys": 26,
        "../internals/hidden-keys": 42,
        "../internals/html": 43,
        "../internals/object-define-properties": 61,
        "../internals/shared-key": 78
    } ],
    61: [ function(require, module, exports) {
        var DESCRIPTORS = require("../internals/descriptors");
        var V8_PROTOTYPE_DEFINE_BUG = require("../internals/v8-prototype-define-bug");
        var definePropertyModule = require("../internals/object-define-property");
        var anObject = require("../internals/an-object");
        var toIndexedObject = require("../internals/to-indexed-object");
        var objectKeys = require("../internals/object-keys");
        exports.f = DESCRIPTORS && !V8_PROTOTYPE_DEFINE_BUG ? Object.defineProperties : function defineProperties(O, Properties) {
            anObject(O);
            var props = toIndexedObject(Properties);
            var keys = objectKeys(Properties);
            var length = keys.length;
            var index = 0;
            var key;
            while (length > index) definePropertyModule.f(O, key = keys[index++], props[key]);
            return O;
        };
    }, {
        "../internals/an-object": 3,
        "../internals/descriptors": 21,
        "../internals/object-define-property": 62,
        "../internals/object-keys": 70,
        "../internals/to-indexed-object": 85,
        "../internals/v8-prototype-define-bug": 96
    } ],
    62: [ function(require, module, exports) {
        var DESCRIPTORS = require("../internals/descriptors");
        var IE8_DOM_DEFINE = require("../internals/ie8-dom-define");
        var V8_PROTOTYPE_DEFINE_BUG = require("../internals/v8-prototype-define-bug");
        var anObject = require("../internals/an-object");
        var toPropertyKey = require("../internals/to-property-key");
        var $TypeError = TypeError;
        var $defineProperty = Object.defineProperty;
        var $getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
        var ENUMERABLE = "enumerable";
        var CONFIGURABLE = "configurable";
        var WRITABLE = "writable";
        exports.f = DESCRIPTORS ? V8_PROTOTYPE_DEFINE_BUG ? function defineProperty(O, P, Attributes) {
            anObject(O);
            P = toPropertyKey(P);
            anObject(Attributes);
            if (typeof O === "function" && P === "prototype" && "value" in Attributes && WRITABLE in Attributes && !Attributes[WRITABLE]) {
                var current = $getOwnPropertyDescriptor(O, P);
                if (current && current[WRITABLE]) {
                    O[P] = Attributes.value;
                    Attributes = {
                        configurable: CONFIGURABLE in Attributes ? Attributes[CONFIGURABLE] : current[CONFIGURABLE],
                        enumerable: ENUMERABLE in Attributes ? Attributes[ENUMERABLE] : current[ENUMERABLE],
                        writable: false
                    };
                }
            }
            return $defineProperty(O, P, Attributes);
        } : $defineProperty : function defineProperty(O, P, Attributes) {
            anObject(O);
            P = toPropertyKey(P);
            anObject(Attributes);
            if (IE8_DOM_DEFINE) try {
                return $defineProperty(O, P, Attributes);
            } catch (error) {}
            if ("get" in Attributes || "set" in Attributes) throw $TypeError("Accessors not supported");
            if ("value" in Attributes) O[P] = Attributes.value;
            return O;
        };
    }, {
        "../internals/an-object": 3,
        "../internals/descriptors": 21,
        "../internals/ie8-dom-define": 44,
        "../internals/to-property-key": 90,
        "../internals/v8-prototype-define-bug": 96
    } ],
    63: [ function(require, module, exports) {
        var DESCRIPTORS = require("../internals/descriptors");
        var call = require("../internals/function-call");
        var propertyIsEnumerableModule = require("../internals/object-property-is-enumerable");
        var createPropertyDescriptor = require("../internals/create-property-descriptor");
        var toIndexedObject = require("../internals/to-indexed-object");
        var toPropertyKey = require("../internals/to-property-key");
        var hasOwn = require("../internals/has-own-property");
        var IE8_DOM_DEFINE = require("../internals/ie8-dom-define");
        var $getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
        exports.f = DESCRIPTORS ? $getOwnPropertyDescriptor : function getOwnPropertyDescriptor(O, P) {
            O = toIndexedObject(O);
            P = toPropertyKey(P);
            if (IE8_DOM_DEFINE) try {
                return $getOwnPropertyDescriptor(O, P);
            } catch (error) {}
            if (hasOwn(O, P)) return createPropertyDescriptor(!call(propertyIsEnumerableModule.f, O, P), O[P]);
        };
    }, {
        "../internals/create-property-descriptor": 15,
        "../internals/descriptors": 21,
        "../internals/function-call": 33,
        "../internals/has-own-property": 41,
        "../internals/ie8-dom-define": 44,
        "../internals/object-property-is-enumerable": 71,
        "../internals/to-indexed-object": 85,
        "../internals/to-property-key": 90
    } ],
    64: [ function(require, module, exports) {
        var classof = require("../internals/classof-raw");
        var toIndexedObject = require("../internals/to-indexed-object");
        var $getOwnPropertyNames = require("../internals/object-get-own-property-names").f;
        var arraySlice = require("../internals/array-slice-simple");
        var windowNames = typeof window == "object" && window && Object.getOwnPropertyNames ? Object.getOwnPropertyNames(window) : [];
        var getWindowNames = function(it) {
            try {
                return $getOwnPropertyNames(it);
            } catch (error) {
                return arraySlice(windowNames);
            }
        };
        module.exports.f = function getOwnPropertyNames(it) {
            return windowNames && classof(it) == "Window" ? getWindowNames(it) : $getOwnPropertyNames(toIndexedObject(it));
        };
    }, {
        "../internals/array-slice-simple": 6,
        "../internals/classof-raw": 10,
        "../internals/object-get-own-property-names": 65,
        "../internals/to-indexed-object": 85
    } ],
    65: [ function(require, module, exports) {
        var internalObjectKeys = require("../internals/object-keys-internal");
        var enumBugKeys = require("../internals/enum-bug-keys");
        var hiddenKeys = enumBugKeys.concat("length", "prototype");
        exports.f = Object.getOwnPropertyNames || function getOwnPropertyNames(O) {
            return internalObjectKeys(O, hiddenKeys);
        };
    }, {
        "../internals/enum-bug-keys": 26,
        "../internals/object-keys-internal": 69
    } ],
    66: [ function(require, module, exports) {
        exports.f = Object.getOwnPropertySymbols;
    }, {} ],
    67: [ function(require, module, exports) {
        var hasOwn = require("../internals/has-own-property");
        var isCallable = require("../internals/is-callable");
        var toObject = require("../internals/to-object");
        var sharedKey = require("../internals/shared-key");
        var CORRECT_PROTOTYPE_GETTER = require("../internals/correct-prototype-getter");
        var IE_PROTO = sharedKey("IE_PROTO");
        var $Object = Object;
        var ObjectPrototype = $Object.prototype;
        module.exports = CORRECT_PROTOTYPE_GETTER ? $Object.getPrototypeOf : function(O) {
            var object = toObject(O);
            if (hasOwn(object, IE_PROTO)) return object[IE_PROTO];
            var constructor = object.constructor;
            if (isCallable(constructor) && object instanceof constructor) {
                return constructor.prototype;
            }
            return object instanceof $Object ? ObjectPrototype : null;
        };
    }, {
        "../internals/correct-prototype-getter": 13,
        "../internals/has-own-property": 41,
        "../internals/is-callable": 49,
        "../internals/shared-key": 78,
        "../internals/to-object": 88
    } ],
    68: [ function(require, module, exports) {
        var uncurryThis = require("../internals/function-uncurry-this");
        module.exports = uncurryThis({}.isPrototypeOf);
    }, {
        "../internals/function-uncurry-this": 36
    } ],
    69: [ function(require, module, exports) {
        var uncurryThis = require("../internals/function-uncurry-this");
        var hasOwn = require("../internals/has-own-property");
        var toIndexedObject = require("../internals/to-indexed-object");
        var indexOf = require("../internals/array-includes").indexOf;
        var hiddenKeys = require("../internals/hidden-keys");
        var push = uncurryThis([].push);
        module.exports = function(object, names) {
            var O = toIndexedObject(object);
            var i = 0;
            var result = [];
            var key;
            for (key in O) !hasOwn(hiddenKeys, key) && hasOwn(O, key) && push(result, key);
            while (names.length > i) if (hasOwn(O, key = names[i++])) {
                ~indexOf(result, key) || push(result, key);
            }
            return result;
        };
    }, {
        "../internals/array-includes": 4,
        "../internals/function-uncurry-this": 36,
        "../internals/has-own-property": 41,
        "../internals/hidden-keys": 42,
        "../internals/to-indexed-object": 85
    } ],
    70: [ function(require, module, exports) {
        var internalObjectKeys = require("../internals/object-keys-internal");
        var enumBugKeys = require("../internals/enum-bug-keys");
        module.exports = Object.keys || function keys(O) {
            return internalObjectKeys(O, enumBugKeys);
        };
    }, {
        "../internals/enum-bug-keys": 26,
        "../internals/object-keys-internal": 69
    } ],
    71: [ function(require, module, exports) {
        "use strict";
        var $propertyIsEnumerable = {}.propertyIsEnumerable;
        var getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
        var NASHORN_BUG = getOwnPropertyDescriptor && !$propertyIsEnumerable.call({
            1: 2
        }, 1);
        exports.f = NASHORN_BUG ? function propertyIsEnumerable(V) {
            var descriptor = getOwnPropertyDescriptor(this, V);
            return !!descriptor && descriptor.enumerable;
        } : $propertyIsEnumerable;
    }, {} ],
    72: [ function(require, module, exports) {
        "use strict";
        var TO_STRING_TAG_SUPPORT = require("../internals/to-string-tag-support");
        var classof = require("../internals/classof");
        module.exports = TO_STRING_TAG_SUPPORT ? {}.toString : function toString() {
            return "[object " + classof(this) + "]";
        };
    }, {
        "../internals/classof": 11,
        "../internals/to-string-tag-support": 91
    } ],
    73: [ function(require, module, exports) {
        var call = require("../internals/function-call");
        var isCallable = require("../internals/is-callable");
        var isObject = require("../internals/is-object");
        var $TypeError = TypeError;
        module.exports = function(input, pref) {
            var fn, val;
            if (pref === "string" && isCallable(fn = input.toString) && !isObject(val = call(fn, input))) return val;
            if (isCallable(fn = input.valueOf) && !isObject(val = call(fn, input))) return val;
            if (pref !== "string" && isCallable(fn = input.toString) && !isObject(val = call(fn, input))) return val;
            throw $TypeError("Can't convert object to primitive value");
        };
    }, {
        "../internals/function-call": 33,
        "../internals/is-callable": 49,
        "../internals/is-object": 54
    } ],
    74: [ function(require, module, exports) {
        var getBuiltIn = require("../internals/get-built-in");
        var uncurryThis = require("../internals/function-uncurry-this");
        var getOwnPropertyNamesModule = require("../internals/object-get-own-property-names");
        var getOwnPropertySymbolsModule = require("../internals/object-get-own-property-symbols");
        var anObject = require("../internals/an-object");
        var concat = uncurryThis([].concat);
        module.exports = getBuiltIn("Reflect", "ownKeys") || function ownKeys(it) {
            var keys = getOwnPropertyNamesModule.f(anObject(it));
            var getOwnPropertySymbols = getOwnPropertySymbolsModule.f;
            return getOwnPropertySymbols ? concat(keys, getOwnPropertySymbols(it)) : keys;
        };
    }, {
        "../internals/an-object": 3,
        "../internals/function-uncurry-this": 36,
        "../internals/get-built-in": 37,
        "../internals/object-get-own-property-names": 65,
        "../internals/object-get-own-property-symbols": 66
    } ],
    75: [ function(require, module, exports) {
        var global = require("../internals/global");
        module.exports = global;
    }, {
        "../internals/global": 40
    } ],
    76: [ function(require, module, exports) {
        var isNullOrUndefined = require("../internals/is-null-or-undefined");
        var $TypeError = TypeError;
        module.exports = function(it) {
            if (isNullOrUndefined(it)) throw $TypeError("Can't call method on " + it);
            return it;
        };
    }, {
        "../internals/is-null-or-undefined": 53
    } ],
    77: [ function(require, module, exports) {
        var defineProperty = require("../internals/object-define-property").f;
        var hasOwn = require("../internals/has-own-property");
        var wellKnownSymbol = require("../internals/well-known-symbol");
        var TO_STRING_TAG = wellKnownSymbol("toStringTag");
        module.exports = function(target, TAG, STATIC) {
            if (target && !STATIC) target = target.prototype;
            if (target && !hasOwn(target, TO_STRING_TAG)) {
                defineProperty(target, TO_STRING_TAG, {
                    configurable: true,
                    value: TAG
                });
            }
        };
    }, {
        "../internals/has-own-property": 41,
        "../internals/object-define-property": 62,
        "../internals/well-known-symbol": 100
    } ],
    78: [ function(require, module, exports) {
        var shared = require("../internals/shared");
        var uid = require("../internals/uid");
        var keys = shared("keys");
        module.exports = function(key) {
            return keys[key] || (keys[key] = uid(key));
        };
    }, {
        "../internals/shared": 80,
        "../internals/uid": 94
    } ],
    79: [ function(require, module, exports) {
        var global = require("../internals/global");
        var defineGlobalProperty = require("../internals/define-global-property");
        var SHARED = "__core-js_shared__";
        var store = global[SHARED] || defineGlobalProperty(SHARED, {});
        module.exports = store;
    }, {
        "../internals/define-global-property": 20,
        "../internals/global": 40
    } ],
    80: [ function(require, module, exports) {
        var IS_PURE = require("../internals/is-pure");
        var store = require("../internals/shared-store");
        (module.exports = function(key, value) {
            return store[key] || (store[key] = value !== undefined ? value : {});
        })("versions", []).push({
            version: "3.28.0",
            mode: IS_PURE ? "pure" : "global",
            copyright: "© 2014-2023 Denis Pushkarev (zloirock.ru)",
            license: "https://github.com/zloirock/core-js/blob/v3.28.0/LICENSE",
            source: "https://github.com/zloirock/core-js"
        });
    }, {
        "../internals/is-pure": 55,
        "../internals/shared-store": 79
    } ],
    81: [ function(require, module, exports) {
        var V8_VERSION = require("../internals/engine-v8-version");
        var fails = require("../internals/fails");
        module.exports = !!Object.getOwnPropertySymbols && !fails(function() {
            var symbol = Symbol();
            return !String(symbol) || !(Object(symbol) instanceof Symbol) || !Symbol.sham && V8_VERSION && V8_VERSION < 41;
        });
    }, {
        "../internals/engine-v8-version": 25,
        "../internals/fails": 28
    } ],
    82: [ function(require, module, exports) {
        var call = require("../internals/function-call");
        var getBuiltIn = require("../internals/get-built-in");
        var wellKnownSymbol = require("../internals/well-known-symbol");
        var defineBuiltIn = require("../internals/define-built-in");
        module.exports = function() {
            var Symbol = getBuiltIn("Symbol");
            var SymbolPrototype = Symbol && Symbol.prototype;
            var valueOf = SymbolPrototype && SymbolPrototype.valueOf;
            var TO_PRIMITIVE = wellKnownSymbol("toPrimitive");
            if (SymbolPrototype && !SymbolPrototype[TO_PRIMITIVE]) {
                defineBuiltIn(SymbolPrototype, TO_PRIMITIVE, function(hint) {
                    return call(valueOf, this);
                }, {
                    arity: 1
                });
            }
        };
    }, {
        "../internals/define-built-in": 19,
        "../internals/function-call": 33,
        "../internals/get-built-in": 37,
        "../internals/well-known-symbol": 100
    } ],
    83: [ function(require, module, exports) {
        var NATIVE_SYMBOL = require("../internals/symbol-constructor-detection");
        module.exports = NATIVE_SYMBOL && !!Symbol["for"] && !!Symbol.keyFor;
    }, {
        "../internals/symbol-constructor-detection": 81
    } ],
    84: [ function(require, module, exports) {
        var toIntegerOrInfinity = require("../internals/to-integer-or-infinity");
        var max = Math.max;
        var min = Math.min;
        module.exports = function(index, length) {
            var integer = toIntegerOrInfinity(index);
            return integer < 0 ? max(integer + length, 0) : min(integer, length);
        };
    }, {
        "../internals/to-integer-or-infinity": 86
    } ],
    85: [ function(require, module, exports) {
        var IndexedObject = require("../internals/indexed-object");
        var requireObjectCoercible = require("../internals/require-object-coercible");
        module.exports = function(it) {
            return IndexedObject(requireObjectCoercible(it));
        };
    }, {
        "../internals/indexed-object": 45,
        "../internals/require-object-coercible": 76
    } ],
    86: [ function(require, module, exports) {
        var trunc = require("../internals/math-trunc");
        module.exports = function(argument) {
            var number = +argument;
            return number !== number || number === 0 ? 0 : trunc(number);
        };
    }, {
        "../internals/math-trunc": 59
    } ],
    87: [ function(require, module, exports) {
        var toIntegerOrInfinity = require("../internals/to-integer-or-infinity");
        var min = Math.min;
        module.exports = function(argument) {
            return argument > 0 ? min(toIntegerOrInfinity(argument), 9007199254740991) : 0;
        };
    }, {
        "../internals/to-integer-or-infinity": 86
    } ],
    88: [ function(require, module, exports) {
        var requireObjectCoercible = require("../internals/require-object-coercible");
        var $Object = Object;
        module.exports = function(argument) {
            return $Object(requireObjectCoercible(argument));
        };
    }, {
        "../internals/require-object-coercible": 76
    } ],
    89: [ function(require, module, exports) {
        var call = require("../internals/function-call");
        var isObject = require("../internals/is-object");
        var isSymbol = require("../internals/is-symbol");
        var getMethod = require("../internals/get-method");
        var ordinaryToPrimitive = require("../internals/ordinary-to-primitive");
        var wellKnownSymbol = require("../internals/well-known-symbol");
        var $TypeError = TypeError;
        var TO_PRIMITIVE = wellKnownSymbol("toPrimitive");
        module.exports = function(input, pref) {
            if (!isObject(input) || isSymbol(input)) return input;
            var exoticToPrim = getMethod(input, TO_PRIMITIVE);
            var result;
            if (exoticToPrim) {
                if (pref === undefined) pref = "default";
                result = call(exoticToPrim, input, pref);
                if (!isObject(result) || isSymbol(result)) return result;
                throw $TypeError("Can't convert object to primitive value");
            }
            if (pref === undefined) pref = "number";
            return ordinaryToPrimitive(input, pref);
        };
    }, {
        "../internals/function-call": 33,
        "../internals/get-method": 39,
        "../internals/is-object": 54,
        "../internals/is-symbol": 56,
        "../internals/ordinary-to-primitive": 73,
        "../internals/well-known-symbol": 100
    } ],
    90: [ function(require, module, exports) {
        var toPrimitive = require("../internals/to-primitive");
        var isSymbol = require("../internals/is-symbol");
        module.exports = function(argument) {
            var key = toPrimitive(argument, "string");
            return isSymbol(key) ? key : key + "";
        };
    }, {
        "../internals/is-symbol": 56,
        "../internals/to-primitive": 89
    } ],
    91: [ function(require, module, exports) {
        var wellKnownSymbol = require("../internals/well-known-symbol");
        var TO_STRING_TAG = wellKnownSymbol("toStringTag");
        var test = {};
        test[TO_STRING_TAG] = "z";
        module.exports = String(test) === "[object z]";
    }, {
        "../internals/well-known-symbol": 100
    } ],
    92: [ function(require, module, exports) {
        var classof = require("../internals/classof");
        var $String = String;
        module.exports = function(argument) {
            if (classof(argument) === "Symbol") throw TypeError("Cannot convert a Symbol value to a string");
            return $String(argument);
        };
    }, {
        "../internals/classof": 11
    } ],
    93: [ function(require, module, exports) {
        var $String = String;
        module.exports = function(argument) {
            try {
                return $String(argument);
            } catch (error) {
                return "Object";
            }
        };
    }, {} ],
    94: [ function(require, module, exports) {
        var uncurryThis = require("../internals/function-uncurry-this");
        var id = 0;
        var postfix = Math.random();
        var toString = uncurryThis(1..toString);
        module.exports = function(key) {
            return "Symbol(" + (key === undefined ? "" : key) + ")_" + toString(++id + postfix, 36);
        };
    }, {
        "../internals/function-uncurry-this": 36
    } ],
    95: [ function(require, module, exports) {
        var NATIVE_SYMBOL = require("../internals/symbol-constructor-detection");
        module.exports = NATIVE_SYMBOL && !Symbol.sham && typeof Symbol.iterator == "symbol";
    }, {
        "../internals/symbol-constructor-detection": 81
    } ],
    96: [ function(require, module, exports) {
        var DESCRIPTORS = require("../internals/descriptors");
        var fails = require("../internals/fails");
        module.exports = DESCRIPTORS && fails(function() {
            return Object.defineProperty(function() {}, "prototype", {
                value: 42,
                writable: false
            }).prototype != 42;
        });
    }, {
        "../internals/descriptors": 21,
        "../internals/fails": 28
    } ],
    97: [ function(require, module, exports) {
        var global = require("../internals/global");
        var isCallable = require("../internals/is-callable");
        var WeakMap = global.WeakMap;
        module.exports = isCallable(WeakMap) && /native code/.test(String(WeakMap));
    }, {
        "../internals/global": 40,
        "../internals/is-callable": 49
    } ],
    98: [ function(require, module, exports) {
        var path = require("../internals/path");
        var hasOwn = require("../internals/has-own-property");
        var wrappedWellKnownSymbolModule = require("../internals/well-known-symbol-wrapped");
        var defineProperty = require("../internals/object-define-property").f;
        module.exports = function(NAME) {
            var Symbol = path.Symbol || (path.Symbol = {});
            if (!hasOwn(Symbol, NAME)) defineProperty(Symbol, NAME, {
                value: wrappedWellKnownSymbolModule.f(NAME)
            });
        };
    }, {
        "../internals/has-own-property": 41,
        "../internals/object-define-property": 62,
        "../internals/path": 75,
        "../internals/well-known-symbol-wrapped": 99
    } ],
    99: [ function(require, module, exports) {
        var wellKnownSymbol = require("../internals/well-known-symbol");
        exports.f = wellKnownSymbol;
    }, {
        "../internals/well-known-symbol": 100
    } ],
    100: [ function(require, module, exports) {
        var global = require("../internals/global");
        var shared = require("../internals/shared");
        var hasOwn = require("../internals/has-own-property");
        var uid = require("../internals/uid");
        var NATIVE_SYMBOL = require("../internals/symbol-constructor-detection");
        var USE_SYMBOL_AS_UID = require("../internals/use-symbol-as-uid");
        var Symbol = global.Symbol;
        var WellKnownSymbolsStore = shared("wks");
        var createWellKnownSymbol = USE_SYMBOL_AS_UID ? Symbol["for"] || Symbol : Symbol && Symbol.withoutSetter || uid;
        module.exports = function(name) {
            if (!hasOwn(WellKnownSymbolsStore, name)) {
                WellKnownSymbolsStore[name] = NATIVE_SYMBOL && hasOwn(Symbol, name) ? Symbol[name] : createWellKnownSymbol("Symbol." + name);
            }
            return WellKnownSymbolsStore[name];
        };
    }, {
        "../internals/global": 40,
        "../internals/has-own-property": 41,
        "../internals/shared": 80,
        "../internals/symbol-constructor-detection": 81,
        "../internals/uid": 94,
        "../internals/use-symbol-as-uid": 95
    } ],
    101: [ function(require, module, exports) {
        var hasOwn = require("../internals/has-own-property");
        var defineBuiltIn = require("../internals/define-built-in");
        var dateToPrimitive = require("../internals/date-to-primitive");
        var wellKnownSymbol = require("../internals/well-known-symbol");
        var TO_PRIMITIVE = wellKnownSymbol("toPrimitive");
        var DatePrototype = Date.prototype;
        if (!hasOwn(DatePrototype, TO_PRIMITIVE)) {
            defineBuiltIn(DatePrototype, TO_PRIMITIVE, dateToPrimitive);
        }
    }, {
        "../internals/date-to-primitive": 17,
        "../internals/define-built-in": 19,
        "../internals/has-own-property": 41,
        "../internals/well-known-symbol": 100
    } ],
    102: [ function(require, module, exports) {
        var $ = require("../internals/export");
        var getBuiltIn = require("../internals/get-built-in");
        var apply = require("../internals/function-apply");
        var call = require("../internals/function-call");
        var uncurryThis = require("../internals/function-uncurry-this");
        var fails = require("../internals/fails");
        var isCallable = require("../internals/is-callable");
        var isSymbol = require("../internals/is-symbol");
        var arraySlice = require("../internals/array-slice");
        var getReplacerFunction = require("../internals/get-json-replacer-function");
        var NATIVE_SYMBOL = require("../internals/symbol-constructor-detection");
        var $String = String;
        var $stringify = getBuiltIn("JSON", "stringify");
        var exec = uncurryThis(/./.exec);
        var charAt = uncurryThis("".charAt);
        var charCodeAt = uncurryThis("".charCodeAt);
        var replace = uncurryThis("".replace);
        var numberToString = uncurryThis(1..toString);
        var tester = /[\uD800-\uDFFF]/g;
        var low = /^[\uD800-\uDBFF]$/;
        var hi = /^[\uDC00-\uDFFF]$/;
        var WRONG_SYMBOLS_CONVERSION = !NATIVE_SYMBOL || fails(function() {
            var symbol = getBuiltIn("Symbol")();
            return $stringify([ symbol ]) != "[null]" || $stringify({
                a: symbol
            }) != "{}" || $stringify(Object(symbol)) != "{}";
        });
        var ILL_FORMED_UNICODE = fails(function() {
            return $stringify("\udf06\ud834") !== '"\\udf06\\ud834"' || $stringify("\udead") !== '"\\udead"';
        });
        var stringifyWithSymbolsFix = function(it, replacer) {
            var args = arraySlice(arguments);
            var $replacer = getReplacerFunction(replacer);
            if (!isCallable($replacer) && (it === undefined || isSymbol(it))) return;
            args[1] = function(key, value) {
                if (isCallable($replacer)) value = call($replacer, this, $String(key), value);
                if (!isSymbol(value)) return value;
            };
            return apply($stringify, null, args);
        };
        var fixIllFormed = function(match, offset, string) {
            var prev = charAt(string, offset - 1);
            var next = charAt(string, offset + 1);
            if (exec(low, match) && !exec(hi, next) || exec(hi, match) && !exec(low, prev)) {
                return "\\u" + numberToString(charCodeAt(match, 0), 16);
            }
            return match;
        };
        if ($stringify) {
            $({
                target: "JSON",
                stat: true,
                arity: 3,
                forced: WRONG_SYMBOLS_CONVERSION || ILL_FORMED_UNICODE
            }, {
                stringify: function stringify(it, replacer, space) {
                    var args = arraySlice(arguments);
                    var result = apply(WRONG_SYMBOLS_CONVERSION ? stringifyWithSymbolsFix : $stringify, null, args);
                    return ILL_FORMED_UNICODE && typeof result == "string" ? replace(result, tester, fixIllFormed) : result;
                }
            });
        }
    }, {
        "../internals/array-slice": 7,
        "../internals/export": 27,
        "../internals/fails": 28,
        "../internals/function-apply": 29,
        "../internals/function-call": 33,
        "../internals/function-uncurry-this": 36,
        "../internals/get-built-in": 37,
        "../internals/get-json-replacer-function": 38,
        "../internals/is-callable": 49,
        "../internals/is-symbol": 56,
        "../internals/symbol-constructor-detection": 81
    } ],
    103: [ function(require, module, exports) {
        var $ = require("../internals/export");
        var NATIVE_SYMBOL = require("../internals/symbol-constructor-detection");
        var fails = require("../internals/fails");
        var getOwnPropertySymbolsModule = require("../internals/object-get-own-property-symbols");
        var toObject = require("../internals/to-object");
        var FORCED = !NATIVE_SYMBOL || fails(function() {
            getOwnPropertySymbolsModule.f(1);
        });
        $({
            target: "Object",
            stat: true,
            forced: FORCED
        }, {
            getOwnPropertySymbols: function getOwnPropertySymbols(it) {
                var $getOwnPropertySymbols = getOwnPropertySymbolsModule.f;
                return $getOwnPropertySymbols ? $getOwnPropertySymbols(toObject(it)) : [];
            }
        });
    }, {
        "../internals/export": 27,
        "../internals/fails": 28,
        "../internals/object-get-own-property-symbols": 66,
        "../internals/symbol-constructor-detection": 81,
        "../internals/to-object": 88
    } ],
    104: [ function(require, module, exports) {
        var TO_STRING_TAG_SUPPORT = require("../internals/to-string-tag-support");
        var defineBuiltIn = require("../internals/define-built-in");
        var toString = require("../internals/object-to-string");
        if (!TO_STRING_TAG_SUPPORT) {
            defineBuiltIn(Object.prototype, "toString", toString, {
                unsafe: true
            });
        }
    }, {
        "../internals/define-built-in": 19,
        "../internals/object-to-string": 72,
        "../internals/to-string-tag-support": 91
    } ],
    105: [ function(require, module, exports) {
        var $ = require("../internals/export");
        var getBuiltIn = require("../internals/get-built-in");
        var apply = require("../internals/function-apply");
        var bind = require("../internals/function-bind");
        var aConstructor = require("../internals/a-constructor");
        var anObject = require("../internals/an-object");
        var isObject = require("../internals/is-object");
        var create = require("../internals/object-create");
        var fails = require("../internals/fails");
        var nativeConstruct = getBuiltIn("Reflect", "construct");
        var ObjectPrototype = Object.prototype;
        var push = [].push;
        var NEW_TARGET_BUG = fails(function() {
            function F() {}
            return !(nativeConstruct(function() {}, [], F) instanceof F);
        });
        var ARGS_BUG = !fails(function() {
            nativeConstruct(function() {});
        });
        var FORCED = NEW_TARGET_BUG || ARGS_BUG;
        $({
            target: "Reflect",
            stat: true,
            forced: FORCED,
            sham: FORCED
        }, {
            construct: function construct(Target, args) {
                aConstructor(Target);
                anObject(args);
                var newTarget = arguments.length < 3 ? Target : aConstructor(arguments[2]);
                if (ARGS_BUG && !NEW_TARGET_BUG) return nativeConstruct(Target, args, newTarget);
                if (Target == newTarget) {
                    switch (args.length) {
                      case 0:
                        return new Target();

                      case 1:
                        return new Target(args[0]);

                      case 2:
                        return new Target(args[0], args[1]);

                      case 3:
                        return new Target(args[0], args[1], args[2]);

                      case 4:
                        return new Target(args[0], args[1], args[2], args[3]);
                    }
                    var $args = [ null ];
                    apply(push, $args, args);
                    return new (apply(bind, Target, $args))();
                }
                var proto = newTarget.prototype;
                var instance = create(isObject(proto) ? proto : ObjectPrototype);
                var result = apply(Target, instance, args);
                return isObject(result) ? result : instance;
            }
        });
    }, {
        "../internals/a-constructor": 2,
        "../internals/an-object": 3,
        "../internals/export": 27,
        "../internals/fails": 28,
        "../internals/function-apply": 29,
        "../internals/function-bind": 32,
        "../internals/get-built-in": 37,
        "../internals/is-object": 54,
        "../internals/object-create": 60
    } ],
    106: [ function(require, module, exports) {
        var $ = require("../internals/export");
        var call = require("../internals/function-call");
        var isObject = require("../internals/is-object");
        var anObject = require("../internals/an-object");
        var isDataDescriptor = require("../internals/is-data-descriptor");
        var getOwnPropertyDescriptorModule = require("../internals/object-get-own-property-descriptor");
        var getPrototypeOf = require("../internals/object-get-prototype-of");
        function get(target, propertyKey) {
            var receiver = arguments.length < 3 ? target : arguments[2];
            var descriptor, prototype;
            if (anObject(target) === receiver) return target[propertyKey];
            descriptor = getOwnPropertyDescriptorModule.f(target, propertyKey);
            if (descriptor) return isDataDescriptor(descriptor) ? descriptor.value : descriptor.get === undefined ? undefined : call(descriptor.get, receiver);
            if (isObject(prototype = getPrototypeOf(target))) return get(prototype, propertyKey, receiver);
        }
        $({
            target: "Reflect",
            stat: true
        }, {
            get: get
        });
    }, {
        "../internals/an-object": 3,
        "../internals/export": 27,
        "../internals/function-call": 33,
        "../internals/is-data-descriptor": 51,
        "../internals/is-object": 54,
        "../internals/object-get-own-property-descriptor": 63,
        "../internals/object-get-prototype-of": 67
    } ],
    107: [ function(require, module, exports) {
        "use strict";
        var $ = require("../internals/export");
        var global = require("../internals/global");
        var call = require("../internals/function-call");
        var uncurryThis = require("../internals/function-uncurry-this");
        var IS_PURE = require("../internals/is-pure");
        var DESCRIPTORS = require("../internals/descriptors");
        var NATIVE_SYMBOL = require("../internals/symbol-constructor-detection");
        var fails = require("../internals/fails");
        var hasOwn = require("../internals/has-own-property");
        var isPrototypeOf = require("../internals/object-is-prototype-of");
        var anObject = require("../internals/an-object");
        var toIndexedObject = require("../internals/to-indexed-object");
        var toPropertyKey = require("../internals/to-property-key");
        var $toString = require("../internals/to-string");
        var createPropertyDescriptor = require("../internals/create-property-descriptor");
        var nativeObjectCreate = require("../internals/object-create");
        var objectKeys = require("../internals/object-keys");
        var getOwnPropertyNamesModule = require("../internals/object-get-own-property-names");
        var getOwnPropertyNamesExternal = require("../internals/object-get-own-property-names-external");
        var getOwnPropertySymbolsModule = require("../internals/object-get-own-property-symbols");
        var getOwnPropertyDescriptorModule = require("../internals/object-get-own-property-descriptor");
        var definePropertyModule = require("../internals/object-define-property");
        var definePropertiesModule = require("../internals/object-define-properties");
        var propertyIsEnumerableModule = require("../internals/object-property-is-enumerable");
        var defineBuiltIn = require("../internals/define-built-in");
        var defineBuiltInAccessor = require("../internals/define-built-in-accessor");
        var shared = require("../internals/shared");
        var sharedKey = require("../internals/shared-key");
        var hiddenKeys = require("../internals/hidden-keys");
        var uid = require("../internals/uid");
        var wellKnownSymbol = require("../internals/well-known-symbol");
        var wrappedWellKnownSymbolModule = require("../internals/well-known-symbol-wrapped");
        var defineWellKnownSymbol = require("../internals/well-known-symbol-define");
        var defineSymbolToPrimitive = require("../internals/symbol-define-to-primitive");
        var setToStringTag = require("../internals/set-to-string-tag");
        var InternalStateModule = require("../internals/internal-state");
        var $forEach = require("../internals/array-iteration").forEach;
        var HIDDEN = sharedKey("hidden");
        var SYMBOL = "Symbol";
        var PROTOTYPE = "prototype";
        var setInternalState = InternalStateModule.set;
        var getInternalState = InternalStateModule.getterFor(SYMBOL);
        var ObjectPrototype = Object[PROTOTYPE];
        var $Symbol = global.Symbol;
        var SymbolPrototype = $Symbol && $Symbol[PROTOTYPE];
        var TypeError = global.TypeError;
        var QObject = global.QObject;
        var nativeGetOwnPropertyDescriptor = getOwnPropertyDescriptorModule.f;
        var nativeDefineProperty = definePropertyModule.f;
        var nativeGetOwnPropertyNames = getOwnPropertyNamesExternal.f;
        var nativePropertyIsEnumerable = propertyIsEnumerableModule.f;
        var push = uncurryThis([].push);
        var AllSymbols = shared("symbols");
        var ObjectPrototypeSymbols = shared("op-symbols");
        var WellKnownSymbolsStore = shared("wks");
        var USE_SETTER = !QObject || !QObject[PROTOTYPE] || !QObject[PROTOTYPE].findChild;
        var setSymbolDescriptor = DESCRIPTORS && fails(function() {
            return nativeObjectCreate(nativeDefineProperty({}, "a", {
                get: function() {
                    return nativeDefineProperty(this, "a", {
                        value: 7
                    }).a;
                }
            })).a != 7;
        }) ? function(O, P, Attributes) {
            var ObjectPrototypeDescriptor = nativeGetOwnPropertyDescriptor(ObjectPrototype, P);
            if (ObjectPrototypeDescriptor) delete ObjectPrototype[P];
            nativeDefineProperty(O, P, Attributes);
            if (ObjectPrototypeDescriptor && O !== ObjectPrototype) {
                nativeDefineProperty(ObjectPrototype, P, ObjectPrototypeDescriptor);
            }
        } : nativeDefineProperty;
        var wrap = function(tag, description) {
            var symbol = AllSymbols[tag] = nativeObjectCreate(SymbolPrototype);
            setInternalState(symbol, {
                type: SYMBOL,
                tag: tag,
                description: description
            });
            if (!DESCRIPTORS) symbol.description = description;
            return symbol;
        };
        var $defineProperty = function defineProperty(O, P, Attributes) {
            if (O === ObjectPrototype) $defineProperty(ObjectPrototypeSymbols, P, Attributes);
            anObject(O);
            var key = toPropertyKey(P);
            anObject(Attributes);
            if (hasOwn(AllSymbols, key)) {
                if (!Attributes.enumerable) {
                    if (!hasOwn(O, HIDDEN)) nativeDefineProperty(O, HIDDEN, createPropertyDescriptor(1, {}));
                    O[HIDDEN][key] = true;
                } else {
                    if (hasOwn(O, HIDDEN) && O[HIDDEN][key]) O[HIDDEN][key] = false;
                    Attributes = nativeObjectCreate(Attributes, {
                        enumerable: createPropertyDescriptor(0, false)
                    });
                }
                return setSymbolDescriptor(O, key, Attributes);
            }
            return nativeDefineProperty(O, key, Attributes);
        };
        var $defineProperties = function defineProperties(O, Properties) {
            anObject(O);
            var properties = toIndexedObject(Properties);
            var keys = objectKeys(properties).concat($getOwnPropertySymbols(properties));
            $forEach(keys, function(key) {
                if (!DESCRIPTORS || call($propertyIsEnumerable, properties, key)) $defineProperty(O, key, properties[key]);
            });
            return O;
        };
        var $create = function create(O, Properties) {
            return Properties === undefined ? nativeObjectCreate(O) : $defineProperties(nativeObjectCreate(O), Properties);
        };
        var $propertyIsEnumerable = function propertyIsEnumerable(V) {
            var P = toPropertyKey(V);
            var enumerable = call(nativePropertyIsEnumerable, this, P);
            if (this === ObjectPrototype && hasOwn(AllSymbols, P) && !hasOwn(ObjectPrototypeSymbols, P)) return false;
            return enumerable || !hasOwn(this, P) || !hasOwn(AllSymbols, P) || hasOwn(this, HIDDEN) && this[HIDDEN][P] ? enumerable : true;
        };
        var $getOwnPropertyDescriptor = function getOwnPropertyDescriptor(O, P) {
            var it = toIndexedObject(O);
            var key = toPropertyKey(P);
            if (it === ObjectPrototype && hasOwn(AllSymbols, key) && !hasOwn(ObjectPrototypeSymbols, key)) return;
            var descriptor = nativeGetOwnPropertyDescriptor(it, key);
            if (descriptor && hasOwn(AllSymbols, key) && !(hasOwn(it, HIDDEN) && it[HIDDEN][key])) {
                descriptor.enumerable = true;
            }
            return descriptor;
        };
        var $getOwnPropertyNames = function getOwnPropertyNames(O) {
            var names = nativeGetOwnPropertyNames(toIndexedObject(O));
            var result = [];
            $forEach(names, function(key) {
                if (!hasOwn(AllSymbols, key) && !hasOwn(hiddenKeys, key)) push(result, key);
            });
            return result;
        };
        var $getOwnPropertySymbols = function(O) {
            var IS_OBJECT_PROTOTYPE = O === ObjectPrototype;
            var names = nativeGetOwnPropertyNames(IS_OBJECT_PROTOTYPE ? ObjectPrototypeSymbols : toIndexedObject(O));
            var result = [];
            $forEach(names, function(key) {
                if (hasOwn(AllSymbols, key) && (!IS_OBJECT_PROTOTYPE || hasOwn(ObjectPrototype, key))) {
                    push(result, AllSymbols[key]);
                }
            });
            return result;
        };
        if (!NATIVE_SYMBOL) {
            $Symbol = function Symbol() {
                if (isPrototypeOf(SymbolPrototype, this)) throw TypeError("Symbol is not a constructor");
                var description = !arguments.length || arguments[0] === undefined ? undefined : $toString(arguments[0]);
                var tag = uid(description);
                var setter = function(value) {
                    if (this === ObjectPrototype) call(setter, ObjectPrototypeSymbols, value);
                    if (hasOwn(this, HIDDEN) && hasOwn(this[HIDDEN], tag)) this[HIDDEN][tag] = false;
                    setSymbolDescriptor(this, tag, createPropertyDescriptor(1, value));
                };
                if (DESCRIPTORS && USE_SETTER) setSymbolDescriptor(ObjectPrototype, tag, {
                    configurable: true,
                    set: setter
                });
                return wrap(tag, description);
            };
            SymbolPrototype = $Symbol[PROTOTYPE];
            defineBuiltIn(SymbolPrototype, "toString", function toString() {
                return getInternalState(this).tag;
            });
            defineBuiltIn($Symbol, "withoutSetter", function(description) {
                return wrap(uid(description), description);
            });
            propertyIsEnumerableModule.f = $propertyIsEnumerable;
            definePropertyModule.f = $defineProperty;
            definePropertiesModule.f = $defineProperties;
            getOwnPropertyDescriptorModule.f = $getOwnPropertyDescriptor;
            getOwnPropertyNamesModule.f = getOwnPropertyNamesExternal.f = $getOwnPropertyNames;
            getOwnPropertySymbolsModule.f = $getOwnPropertySymbols;
            wrappedWellKnownSymbolModule.f = function(name) {
                return wrap(wellKnownSymbol(name), name);
            };
            if (DESCRIPTORS) {
                defineBuiltInAccessor(SymbolPrototype, "description", {
                    configurable: true,
                    get: function description() {
                        return getInternalState(this).description;
                    }
                });
                if (!IS_PURE) {
                    defineBuiltIn(ObjectPrototype, "propertyIsEnumerable", $propertyIsEnumerable, {
                        unsafe: true
                    });
                }
            }
        }
        $({
            global: true,
            constructor: true,
            wrap: true,
            forced: !NATIVE_SYMBOL,
            sham: !NATIVE_SYMBOL
        }, {
            Symbol: $Symbol
        });
        $forEach(objectKeys(WellKnownSymbolsStore), function(name) {
            defineWellKnownSymbol(name);
        });
        $({
            target: SYMBOL,
            stat: true,
            forced: !NATIVE_SYMBOL
        }, {
            useSetter: function() {
                USE_SETTER = true;
            },
            useSimple: function() {
                USE_SETTER = false;
            }
        });
        $({
            target: "Object",
            stat: true,
            forced: !NATIVE_SYMBOL,
            sham: !DESCRIPTORS
        }, {
            create: $create,
            defineProperty: $defineProperty,
            defineProperties: $defineProperties,
            getOwnPropertyDescriptor: $getOwnPropertyDescriptor
        });
        $({
            target: "Object",
            stat: true,
            forced: !NATIVE_SYMBOL
        }, {
            getOwnPropertyNames: $getOwnPropertyNames
        });
        defineSymbolToPrimitive();
        setToStringTag($Symbol, SYMBOL);
        hiddenKeys[HIDDEN] = true;
    }, {
        "../internals/an-object": 3,
        "../internals/array-iteration": 5,
        "../internals/create-property-descriptor": 15,
        "../internals/define-built-in": 19,
        "../internals/define-built-in-accessor": 18,
        "../internals/descriptors": 21,
        "../internals/export": 27,
        "../internals/fails": 28,
        "../internals/function-call": 33,
        "../internals/function-uncurry-this": 36,
        "../internals/global": 40,
        "../internals/has-own-property": 41,
        "../internals/hidden-keys": 42,
        "../internals/internal-state": 47,
        "../internals/is-pure": 55,
        "../internals/object-create": 60,
        "../internals/object-define-properties": 61,
        "../internals/object-define-property": 62,
        "../internals/object-get-own-property-descriptor": 63,
        "../internals/object-get-own-property-names": 65,
        "../internals/object-get-own-property-names-external": 64,
        "../internals/object-get-own-property-symbols": 66,
        "../internals/object-is-prototype-of": 68,
        "../internals/object-keys": 70,
        "../internals/object-property-is-enumerable": 71,
        "../internals/set-to-string-tag": 77,
        "../internals/shared": 80,
        "../internals/shared-key": 78,
        "../internals/symbol-constructor-detection": 81,
        "../internals/symbol-define-to-primitive": 82,
        "../internals/to-indexed-object": 85,
        "../internals/to-property-key": 90,
        "../internals/to-string": 92,
        "../internals/uid": 94,
        "../internals/well-known-symbol": 100,
        "../internals/well-known-symbol-define": 98,
        "../internals/well-known-symbol-wrapped": 99
    } ],
    108: [ function(require, module, exports) {
        "use strict";
        var $ = require("../internals/export");
        var DESCRIPTORS = require("../internals/descriptors");
        var global = require("../internals/global");
        var uncurryThis = require("../internals/function-uncurry-this");
        var hasOwn = require("../internals/has-own-property");
        var isCallable = require("../internals/is-callable");
        var isPrototypeOf = require("../internals/object-is-prototype-of");
        var toString = require("../internals/to-string");
        var defineBuiltInAccessor = require("../internals/define-built-in-accessor");
        var copyConstructorProperties = require("../internals/copy-constructor-properties");
        var NativeSymbol = global.Symbol;
        var SymbolPrototype = NativeSymbol && NativeSymbol.prototype;
        if (DESCRIPTORS && isCallable(NativeSymbol) && (!("description" in SymbolPrototype) || NativeSymbol().description !== undefined)) {
            var EmptyStringDescriptionStore = {};
            var SymbolWrapper = function Symbol() {
                var description = arguments.length < 1 || arguments[0] === undefined ? undefined : toString(arguments[0]);
                var result = isPrototypeOf(SymbolPrototype, this) ? new NativeSymbol(description) : description === undefined ? NativeSymbol() : NativeSymbol(description);
                if (description === "") EmptyStringDescriptionStore[result] = true;
                return result;
            };
            copyConstructorProperties(SymbolWrapper, NativeSymbol);
            SymbolWrapper.prototype = SymbolPrototype;
            SymbolPrototype.constructor = SymbolWrapper;
            var NATIVE_SYMBOL = String(NativeSymbol("test")) == "Symbol(test)";
            var thisSymbolValue = uncurryThis(SymbolPrototype.valueOf);
            var symbolDescriptiveString = uncurryThis(SymbolPrototype.toString);
            var regexp = /^Symbol\((.*)\)[^)]+$/;
            var replace = uncurryThis("".replace);
            var stringSlice = uncurryThis("".slice);
            defineBuiltInAccessor(SymbolPrototype, "description", {
                configurable: true,
                get: function description() {
                    var symbol = thisSymbolValue(this);
                    if (hasOwn(EmptyStringDescriptionStore, symbol)) return "";
                    var string = symbolDescriptiveString(symbol);
                    var desc = NATIVE_SYMBOL ? stringSlice(string, 7, -1) : replace(string, regexp, "$1");
                    return desc === "" ? undefined : desc;
                }
            });
            $({
                global: true,
                constructor: true,
                forced: true
            }, {
                Symbol: SymbolWrapper
            });
        }
    }, {
        "../internals/copy-constructor-properties": 12,
        "../internals/define-built-in-accessor": 18,
        "../internals/descriptors": 21,
        "../internals/export": 27,
        "../internals/function-uncurry-this": 36,
        "../internals/global": 40,
        "../internals/has-own-property": 41,
        "../internals/is-callable": 49,
        "../internals/object-is-prototype-of": 68,
        "../internals/to-string": 92
    } ],
    109: [ function(require, module, exports) {
        var $ = require("../internals/export");
        var getBuiltIn = require("../internals/get-built-in");
        var hasOwn = require("../internals/has-own-property");
        var toString = require("../internals/to-string");
        var shared = require("../internals/shared");
        var NATIVE_SYMBOL_REGISTRY = require("../internals/symbol-registry-detection");
        var StringToSymbolRegistry = shared("string-to-symbol-registry");
        var SymbolToStringRegistry = shared("symbol-to-string-registry");
        $({
            target: "Symbol",
            stat: true,
            forced: !NATIVE_SYMBOL_REGISTRY
        }, {
            for: function(key) {
                var string = toString(key);
                if (hasOwn(StringToSymbolRegistry, string)) return StringToSymbolRegistry[string];
                var symbol = getBuiltIn("Symbol")(string);
                StringToSymbolRegistry[string] = symbol;
                SymbolToStringRegistry[symbol] = string;
                return symbol;
            }
        });
    }, {
        "../internals/export": 27,
        "../internals/get-built-in": 37,
        "../internals/has-own-property": 41,
        "../internals/shared": 80,
        "../internals/symbol-registry-detection": 83,
        "../internals/to-string": 92
    } ],
    110: [ function(require, module, exports) {
        require("../modules/es.symbol.constructor");
        require("../modules/es.symbol.for");
        require("../modules/es.symbol.key-for");
        require("../modules/es.json.stringify");
        require("../modules/es.object.get-own-property-symbols");
    }, {
        "../modules/es.json.stringify": 102,
        "../modules/es.object.get-own-property-symbols": 103,
        "../modules/es.symbol.constructor": 107,
        "../modules/es.symbol.for": 109,
        "../modules/es.symbol.key-for": 111
    } ],
    111: [ function(require, module, exports) {
        var $ = require("../internals/export");
        var hasOwn = require("../internals/has-own-property");
        var isSymbol = require("../internals/is-symbol");
        var tryToString = require("../internals/try-to-string");
        var shared = require("../internals/shared");
        var NATIVE_SYMBOL_REGISTRY = require("../internals/symbol-registry-detection");
        var SymbolToStringRegistry = shared("symbol-to-string-registry");
        $({
            target: "Symbol",
            stat: true,
            forced: !NATIVE_SYMBOL_REGISTRY
        }, {
            keyFor: function keyFor(sym) {
                if (!isSymbol(sym)) throw TypeError(tryToString(sym) + " is not a symbol");
                if (hasOwn(SymbolToStringRegistry, sym)) return SymbolToStringRegistry[sym];
            }
        });
    }, {
        "../internals/export": 27,
        "../internals/has-own-property": 41,
        "../internals/is-symbol": 56,
        "../internals/shared": 80,
        "../internals/symbol-registry-detection": 83,
        "../internals/try-to-string": 93
    } ],
    112: [ function(require, module, exports) {
        var defineWellKnownSymbol = require("../internals/well-known-symbol-define");
        var defineSymbolToPrimitive = require("../internals/symbol-define-to-primitive");
        defineWellKnownSymbol("toPrimitive");
        defineSymbolToPrimitive();
    }, {
        "../internals/symbol-define-to-primitive": 82,
        "../internals/well-known-symbol-define": 98
    } ],
    113: [ function(require, module, exports) {
        "use strict";
        require("core-js/modules/es.object.to-string.js");
        require("core-js/modules/es.reflect.construct.js");
        require("core-js/modules/es.reflect.get.js");
        require("core-js/modules/es.symbol.to-primitive.js");
        require("core-js/modules/es.date.to-primitive.js");
        require("core-js/modules/es.symbol.js");
        require("core-js/modules/es.symbol.description.js");
        function _classCallCheck(instance, Constructor) {
            if (!(instance instanceof Constructor)) {
                throw new TypeError("Cannot call a class as a function");
            }
        }
        function _defineProperties(target, props) {
            for (var i = 0; i < props.length; i++) {
                var descriptor = props[i];
                descriptor.enumerable = descriptor.enumerable || false;
                descriptor.configurable = true;
                if ("value" in descriptor) descriptor.writable = true;
                Object.defineProperty(target, _toPropertyKey(descriptor.key), descriptor);
            }
        }
        function _createClass(Constructor, protoProps, staticProps) {
            if (protoProps) _defineProperties(Constructor.prototype, protoProps);
            if (staticProps) _defineProperties(Constructor, staticProps);
            Object.defineProperty(Constructor, "prototype", {
                writable: false
            });
            return Constructor;
        }
        function _toPropertyKey(arg) {
            var key = _toPrimitive(arg, "string");
            return typeof key === "symbol" ? key : String(key);
        }
        function _toPrimitive(input, hint) {
            if (typeof input !== "object" || input === null) return input;
            var prim = input[Symbol.toPrimitive];
            if (prim !== undefined) {
                var res = prim.call(input, hint || "default");
                if (typeof res !== "object") return res;
                throw new TypeError("@@toPrimitive must return a primitive value.");
            }
            return (hint === "string" ? String : Number)(input);
        }
        function _get() {
            if (typeof Reflect !== "undefined" && Reflect.get) {
                _get = Reflect.get.bind();
            } else {
                _get = function _get(target, property, receiver) {
                    var base = _superPropBase(target, property);
                    if (!base) return;
                    var desc = Object.getOwnPropertyDescriptor(base, property);
                    if (desc.get) {
                        return desc.get.call(arguments.length < 3 ? target : receiver);
                    }
                    return desc.value;
                };
            }
            return _get.apply(this, arguments);
        }
        function _superPropBase(object, property) {
            while (!Object.prototype.hasOwnProperty.call(object, property)) {
                object = _getPrototypeOf(object);
                if (object === null) break;
            }
            return object;
        }
        function _inherits(subClass, superClass) {
            if (typeof superClass !== "function" && superClass !== null) {
                throw new TypeError("Super expression must either be null or a function");
            }
            subClass.prototype = Object.create(superClass && superClass.prototype, {
                constructor: {
                    value: subClass,
                    writable: true,
                    configurable: true
                }
            });
            Object.defineProperty(subClass, "prototype", {
                writable: false
            });
            if (superClass) _setPrototypeOf(subClass, superClass);
        }
        function _setPrototypeOf(o, p) {
            _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function _setPrototypeOf(o, p) {
                o.__proto__ = p;
                return o;
            };
            return _setPrototypeOf(o, p);
        }
        function _createSuper(Derived) {
            var hasNativeReflectConstruct = _isNativeReflectConstruct();
            return function _createSuperInternal() {
                var Super = _getPrototypeOf(Derived), result;
                if (hasNativeReflectConstruct) {
                    var NewTarget = _getPrototypeOf(this).constructor;
                    result = Reflect.construct(Super, arguments, NewTarget);
                } else {
                    result = Super.apply(this, arguments);
                }
                return _possibleConstructorReturn(this, result);
            };
        }
        function _possibleConstructorReturn(self, call) {
            if (call && (typeof call === "object" || typeof call === "function")) {
                return call;
            } else if (call !== void 0) {
                throw new TypeError("Derived constructors may only return object or undefined");
            }
            return _assertThisInitialized(self);
        }
        function _assertThisInitialized(self) {
            if (self === void 0) {
                throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
            }
            return self;
        }
        function _isNativeReflectConstruct() {
            if (typeof Reflect === "undefined" || !Reflect.construct) return false;
            if (Reflect.construct.sham) return false;
            if (typeof Proxy === "function") return true;
            try {
                Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function() {}));
                return true;
            } catch (e) {
                return false;
            }
        }
        function _getPrototypeOf(o) {
            _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function _getPrototypeOf(o) {
                return o.__proto__ || Object.getPrototypeOf(o);
            };
            return _getPrototypeOf(o);
        }
        var hasAirPlayAPISupport = require("../lib/hasAirPlayAPISupport");
        module.exports = function(videojs) {
            var ButtonComponent = videojs.getComponent("Button");
            var AirPlayButton = function(_ButtonComponent) {
                _inherits(AirPlayButton, _ButtonComponent);
                var _super = _createSuper(AirPlayButton);
                function AirPlayButton(player, options) {
                    var _this;
                    _classCallCheck(this, AirPlayButton);
                    _this = _super.call(this, player, options);
                    if (!hasAirPlayAPISupport()) {
                        _this.hide();
                    }
                    _this._reactToAirPlayAvailableEvents();
                    if (options.addAirPlayLabelToButton) {
                        _this.el().classList.add("vjs-airplay-button-lg");
                        _this._labelEl = document.createElement("span");
                        _this._labelEl.classList.add("vjs-airplay-button-label");
                        _this._labelEl.textContent = _this.localize("AirPlay");
                        _this.el().appendChild(_this._labelEl);
                    } else {
                        _this.controlText("Start AirPlay");
                    }
                    return _this;
                }
                _createClass(AirPlayButton, [ {
                    key: "buildCSSClass",
                    value: function buildCSSClass() {
                        return "vjs-airplay-button " + _get(_getPrototypeOf(AirPlayButton.prototype), "buildCSSClass", this).call(this);
                    }
                }, {
                    key: "handleClick",
                    value: function handleClick() {
                        this.player().trigger("airPlayRequested");
                    }
                }, {
                    key: "_getMediaEl",
                    value: function _getMediaEl() {
                        var playerEl = this.player().el();
                        return playerEl.querySelector("video, audio");
                    }
                }, {
                    key: "_reactToAirPlayAvailableEvents",
                    value: function _reactToAirPlayAvailableEvents() {
                        var mediaEl = this._getMediaEl(), self = this;
                        if (!mediaEl || !hasAirPlayAPISupport()) {
                            return;
                        }
                        function onTargetAvailabilityChanged(event) {
                            if (event.availability === "available") {
                                self.show();
                            } else {
                                self.hide();
                            }
                        }
                        mediaEl.addEventListener("webkitplaybacktargetavailabilitychanged", onTargetAvailabilityChanged);
                        this.on("dispose", function() {
                            mediaEl.removeEventListener("webkitplaybacktargetavailabilitychanged", onTargetAvailabilityChanged);
                        });
                    }
                } ]);
                return AirPlayButton;
            }(ButtonComponent);
            videojs.registerComponent("airPlayButton", AirPlayButton);
        };
    }, {
        "../lib/hasAirPlayAPISupport": 116,
        "core-js/modules/es.date.to-primitive.js": 101,
        "core-js/modules/es.object.to-string.js": 104,
        "core-js/modules/es.reflect.construct.js": 105,
        "core-js/modules/es.reflect.get.js": 106,
        "core-js/modules/es.symbol.description.js": 108,
        "core-js/modules/es.symbol.js": 110,
        "core-js/modules/es.symbol.to-primitive.js": 112
    } ],
    114: [ function(require, module, exports) {
        "use strict";
        var hasAirPlayAPISupport = require("./lib/hasAirPlayAPISupport");
        function getExistingAirPlayButton(player) {
            return player.controlBar.getChild("airPlayButton");
        }
        function ensureAirPlayButtonExists(player, options) {
            var existingAirPlayButton = getExistingAirPlayButton(player), indexOpt;
            if (options.addButtonToControlBar && !existingAirPlayButton) {
                indexOpt = player.controlBar.children().length;
                if (typeof options.buttonPositionIndex !== "undefined") {
                    indexOpt = options.buttonPositionIndex >= 0 ? options.buttonPositionIndex : player.controlBar.children().length + options.buttonPositionIndex;
                }
                player.controlBar.addChild("airPlayButton", options, indexOpt);
            }
        }
        function onAirPlayRequested(player) {
            var mediaEl = player.el().querySelector("video, audio");
            if (mediaEl && mediaEl.webkitShowPlaybackTargetPicker) {
                mediaEl.webkitShowPlaybackTargetPicker();
            }
        }
        function listenForAirPlayEvents(player) {
            player.on("airPlayRequested", onAirPlayRequested.bind(null, player));
        }
        function enableAirPlay(player, options) {
            if (!player.controlBar) {
                return;
            }
            if (hasAirPlayAPISupport()) {
                listenForAirPlayEvents(player);
                ensureAirPlayButtonExists(player, options);
            }
        }
        module.exports = function(videojs) {
            videojs.registerPlugin("airPlay", function(options) {
                var pluginOptions = Object.assign({
                    addButtonToControlBar: true
                }, options || {});
                this.ready(enableAirPlay.bind(this, this, pluginOptions));
            });
        };
    }, {
        "./lib/hasAirPlayAPISupport": 116
    } ],
    115: [ function(require, module, exports) {
        "use strict";
        var createAirPlayButton = require("./components/AirPlayButton"), createAirPlayPlugin = require("./enableAirPlay");
        module.exports = function(videojs) {
            videojs = videojs || window.videojs;
            createAirPlayButton(videojs);
            createAirPlayPlugin(videojs);
        };
    }, {
        "./components/AirPlayButton": 113,
        "./enableAirPlay": 114
    } ],
    116: [ function(require, module, exports) {
        "use strict";
        module.exports = function() {
            return !!window.WebKitPlaybackTargetAvailabilityEvent;
        };
    }, {} ],
    117: [ function(require, module, exports) {
        "use strict";
        require("./index")();
    }, {
        "./index": 115
    } ]
}, {}, [ 117 ]);
/*! @name videojs-contrib-quality-levels @version 2.2.1 @license Apache-2.0 */
!function(e,t){"object"==typeof exports&&"undefined"!=typeof module?module.exports=t(require("video.js")):"function"==typeof define&&define.amd?define(["video.js"],t):e.videojsContribQualityLevels=t(e.videojs)}(this,function(e){"use strict";function t(e){if(void 0===e)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return e}var i=function(e){var i,n;function l(){var i,n=t(t(i=e.call(this)||this));return n.levels_=[],n.selectedIndex_=-1,Object.defineProperty(n,"selectedIndex",{get:function(){return n.selectedIndex_}}),Object.defineProperty(n,"length",{get:function(){return n.levels_.length}}),n||t(i)}n=e,(i=l).prototype=Object.create(n.prototype),i.prototype.constructor=i,i.__proto__=n;var r=l.prototype;return r.addQualityLevel=function(e){var t=this.getQualityLevelById(e.id);if(t)return t;var i=this.levels_.length;return t=new function(e){var t=this;return t.id=e.id,t.label=t.id,t.width=e.width,t.height=e.height,t.bitrate=e.bandwidth,t.frameRate=e.frameRate,t.enabled_=e.enabled,Object.defineProperty(t,"enabled",{get:function(){return t.enabled_()},set:function(e){t.enabled_(e)}}),t}(e),""+i in this||Object.defineProperty(this,i,{get:function(){return this.levels_[i]}}),this.levels_.push(t),this.trigger({qualityLevel:t,type:"addqualitylevel"}),t},r.removeQualityLevel=function(e){for(var t=null,i=0,n=this.length;i<n;i++)if(this[i]===e){t=this.levels_.splice(i,1)[0],this.selectedIndex_===i?this.selectedIndex_=-1:this.selectedIndex_>i&&this.selectedIndex_--;break}return t&&this.trigger({qualityLevel:e,type:"removequalitylevel"}),t},r.getQualityLevelById=function(e){for(var t=0,i=this.length;t<i;t++){var n=this[t];if(n.id===e)return n}return null},r.dispose=function(){this.selectedIndex_=-1,this.levels_.length=0},l}((e=e&&e.hasOwnProperty("default")?e.default:e).EventTarget);for(var n in i.prototype.allowedEvents_={change:"change",addqualitylevel:"addqualitylevel",removequalitylevel:"removequalitylevel"},i.prototype.allowedEvents_)i.prototype["on"+n]=null;var l=function(t){return n=this,e.mergeOptions({},t),l=n.qualityLevels,r=new i,n.on("dispose",function e(){r.dispose(),n.qualityLevels=l,n.off("dispose",e)}),n.qualityLevels=function(){return r},n.qualityLevels.VERSION="2.2.1",r;var n,l,r};return(e.registerPlugin||e.plugin)("qualityLevels",l),l.VERSION="2.2.1",l});

/*! @name videojs-sprite-thumbnails @version 0.5.3 @license MIT */
!function(e,t){"object"==typeof exports&&"undefined"!=typeof module?module.exports=t(require("video.js")):"function"==typeof define&&define.amd?define(["video.js"],t):(e=e||self).videojsSpriteThumbnails=t(e.videojs)}(this,function(e){"use strict";var t=(e=e&&Object.prototype.hasOwnProperty.call(e,"default")?e.default:e).getPlugin("plugin"),o={url:"",width:0,height:0,interval:1,responsive:600},r=function(t){var r,i;function n(r,i){var n;return(n=t.call(this,r)||this).options=e.mergeOptions(o,i),n.player.ready(function(){!function(t,o){var r=o.url,i=o.height,n=o.width,a=o.responsive,p=e.dom||e,s=t.controlBar,u=s.progressControl,l=u.seekBar,d=l.mouseTimeDisplay;if(r&&i&&n&&d){var c=p.createEl("img",{src:r}),f=function(e){Object.keys(e).forEach(function(t){var o=e[t],r=d.timeTooltip.el().style;""!==o?r.setProperty(t,o):r.removeProperty(t)})},h=function(){var e=c.naturalWidth,u=c.naturalHeight;if(t.controls()&&e&&u){var h=parseFloat(d.el().style.left);if(h=t.duration()*(h/l.currentWidth()),!isNaN(h)){h/=o.interval;var g=t.currentWidth(),m=a&&g<a?g/a:1,v=e/n,b=n*m,y=i*m,x=Math.floor(h%v)*-b,k=Math.floor(h/v)*-y,j=e*m+"px "+u*m+"px",w=p.getBoundingClientRect(s.el()).top,O=p.getBoundingClientRect(l.el()).top,P=-y-Math.max(0,O-w);f({width:b+"px",height:y+"px","background-image":"url("+r+")","background-repeat":"no-repeat","background-position":x+"px "+k+"px","background-size":j,top:P+"px",color:"#fff","text-shadow":"1px 1px #000",border:"1px solid #000",margin:"0 1px"})}}};f({width:"",height:"","background-image":"","background-repeat":"","background-position":"","background-size":"",top:"",color:"","text-shadow":"",border:"",margin:""}),u.on("mousemove",h),u.on("touchmove",h),t.addClass("vjs-sprite-thumbnails")}}(n.player,n.options)}),n}return i=t,(r=n).prototype=Object.create(i.prototype),r.prototype.constructor=r,r.__proto__=i,n}(t);return r.defaultState={},r.VERSION="0.5.3",e.registerPlugin("spriteThumbnails",r),r});

/**
 * videojs-mux
 * @version 3.1.4
 * @copyright 2020 Mux, Inc.
 * @license
 */
(function(){var define=false;
!function(e,t){if("object"==typeof exports&&"object"==typeof module)module.exports=t(require("video.js"));else if("function"==typeof define&&define.amd)define(["video.js"],t);else{var r=t("object"==typeof exports?require("video.js"):e.videojs);for(var a in r)("object"==typeof exports?exports:e)[a]=r[a]}}("undefined"!=typeof self?self:this,function(e){return function(e){function t(a){if(r[a])return r[a].exports;var i=r[a]={i:a,l:!1,exports:{}};return e[a].call(i.exports,i,i.exports,t),i.l=!0,i.exports}var r={};return t.m=e,t.c=r,t.d=function(e,r,a){t.o(e,r)||Object.defineProperty(e,r,{configurable:!1,enumerable:!0,get:a})},t.n=function(e){var r=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(r,"a",r),r},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s=2)}([function(e,t,r){!function(){!function(t,r){e.exports=r()}(0,function(){return function(e){function t(a){if(r[a])return r[a].exports;var i=r[a]={i:a,l:!1,exports:{}};return e[a].call(i.exports,i,i.exports,t),i.l=!0,i.exports}var r={};return t.m=e,t.c=r,t.d=function(e,r,a){t.o(e,r)||Object.defineProperty(e,r,{configurable:!1,enumerable:!0,get:a})},t.n=function(e){var r=e&&e.__esModule?function(){return e.default}:function(){return e};return t.d(r,"a",r),r},t.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},t.p="",t(t.s=18)}([function(e,t,r){(function(t){var r;r="undefined"!=typeof window?window:void 0!==t?t:"undefined"!=typeof self?self:{},e.exports=r}).call(t,r(6))},function(e,t){function r(e,t,r){switch(r.length){case 0:return e.call(t);case 1:return e.call(t,r[0]);case 2:return e.call(t,r[0],r[1]);case 3:return e.call(t,r[0],r[1],r[2])}return e.apply(t,r)}function a(e,t){for(var r=-1,a=Array(e);++r<e;)a[r]=t(r);return a}function i(e,t){var r=R(e)||p(e)?a(e.length,String):[],i=r.length,n=!!i;for(var o in e)!t&&!O.call(e,o)||n&&("length"==o||l(o,i))||r.push(o);return r}function n(e,t,r){var a=e[t];O.call(e,t)&&f(a,r)&&(void 0!==r||t in e)||(e[t]=r)}function o(e){if(!c(e))return A(e);var t=[];for(var r in Object(e))O.call(e,r)&&"constructor"!=r&&t.push(r);return t}function s(e,t){return t=q(void 0===t?e.length-1:t,0),function(){for(var a=arguments,i=-1,n=q(a.length-t,0),o=Array(n);++i<n;)o[i]=a[t+i];i=-1;for(var s=Array(t+1);++i<t;)s[i]=a[i];return s[t]=o,r(e,this,s)}}function u(e,t,r,a){r||(r={});for(var i=-1,o=t.length;++i<o;){var s=t[i],u=a?a(r[s],e[s],s,r,e):void 0;n(r,s,void 0===u?e[s]:u)}return r}function l(e,t){return!!(t=null==t?w:t)&&("number"==typeof e||k.test(e))&&e>-1&&e%1==0&&e<t}function d(e,t,r){if(!y(r))return!1;var a=typeof t;return!!("number"==a?h(r)&&l(t,r.length):"string"==a&&t in r)&&f(r[t],e)}function c(e){var t=e&&e.constructor;return e===("function"==typeof t&&t.prototype||E)}function f(e,t){return e===t||e!==e&&t!==t}function p(e){return _(e)&&O.call(e,"callee")&&(!P.call(e,"callee")||M.call(e)==x)}function h(e){return null!=e&&m(e.length)&&!v(e)}function _(e){return b(e)&&h(e)}function v(e){var t=y(e)?M.call(e):"";return t==S||t==T}function m(e){return"number"==typeof e&&e>-1&&e%1==0&&e<=w}function y(e){var t=typeof e;return!!e&&("object"==t||"function"==t)}function b(e){return!!e&&"object"==typeof e}function g(e){return h(e)?i(e):o(e)}var w=9007199254740991,x="[object Arguments]",S="[object Function]",T="[object GeneratorFunction]",k=/^(?:0|[1-9]\d*)$/,E=Object.prototype,O=E.hasOwnProperty,M=E.toString,P=E.propertyIsEnumerable,A=function(e,t){return function(r){return e(t(r))}}(Object.keys,Object),q=Math.max,D=!P.call({valueOf:1},"valueOf"),R=Array.isArray,j=function(e){return s(function(t,r){var a=-1,i=r.length,n=i>1?r[i-1]:void 0,o=i>2?r[2]:void 0;for(n=e.length>3&&"function"==typeof n?(i--,n):void 0,o&&d(r[0],r[1],o)&&(n=i<3?void 0:n,i=1),t=Object(t);++a<i;){var s=r[a];s&&e(t,s)}return t})}(function(e,t){if(D||c(t)||h(t))return void u(t,g(t),e);for(var r in t)O.call(t,r)&&n(e,r,t[r])});e.exports=j},function(e,t,r){"use strict";function a(e,t,r){r=void 0===r?1:r,e[t]=e[t]||0,e[t]+=r}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=r(0),i=function(e){return e&&e.__esModule?e:{default:e}}(a),n={};n.now=function(){var e=i.default.performance,t=e&&e.timing;return t&&"number"==typeof t.navigationStart&&"function"==typeof e.now?t.navigationStart+e.now():Date.now()},t.default=n},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=r(20),i=function(e){return e&&e.__esModule?e:{default:e}}(a),n=i.default.methodFactory;i.default.methodFactory=function(e,t,r){var a=n(e,t,r);return function(){for(var e=["[mux]"],t=0;t<arguments.length;t++)e.push(arguments[t]);a.apply(void 0,e)}},i.default.setLevel(i.default.getLevel()),t.default=i.default},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=function(e){return n(e)[0]},i=function(e){return n(e)[1]},n=function(e){if("string"!=typeof e||""===e)return["localhost"];var t=/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?/,r=e.match(t)||[],a=r[4],i=void 0;return a&&(i=(a.match(/[^\.]+\.[^\.]+$/)||[])[0]),[a,i]};t.extractHostnameAndDomain=n,t.extractHostname=a,t.extractDomain=i},function(e,t){var r;r=function(){return this}();try{r=r||Function("return this")()||(0,eval)("this")}catch(e){"object"==typeof window&&(r=window)}e.exports=r},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=function(){return"xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g,function(e){var t=16*Math.random()|0;return("x"===e?t:3&t|8).toString(16)})},i=function(){return("000000"+(Math.random()*Math.pow(36,6)<<0).toString(36)).slice(-6)};t.generateUUID=a,t.generateShortID=i},function(e,t,r){"use strict";function a(e){e=e||"",e=e.match(/[^\r\n]+/g)||[];for(var t={},r=0;r<e.length;r++){var a=e[r].split(/\s*:\s*(.+)/);3===a.length&&(t[a[0]]=a[1])}return t}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0}),t.findMediaElement=t.getMuxPlayerId=void 0;var a=r(7),i=function(e){return e&&void 0!==e.nodeName?(e.muxId||(e.muxId=e.id||(0,a.generateShortID)()),e.muxId):e},n=function(e){var t=void 0;return e&&void 0!==e.nodeName?(t=e,e=i(t)):t=document.querySelector(e),[t,e,t&&t.nodeName?t.nodeName.toLowerCase():""]};t.getMuxPlayerId=i,t.findMediaElement=n},function(e,t,r){"use strict";function a(){return"1"===(n.default.doNotTrack||n.default.navigator&&(n.default.navigator.doNotTrack||n.default.navigator.msDoNotTrack))}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a;var i=r(0),n=function(e){return e&&e.__esModule?e:{default:e}}(i)},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=r(0),i=function(e){return e&&e.__esModule?e:{default:e}}(a),n={};n.exists=function(){var e=i.default.performance;return void 0!==(e&&e.timing)},n.domContentLoadedEventEnd=function(){var e=i.default.performance,t=e&&e.timing;return t&&t.domContentLoadedEventEnd},n.navigationStart=function(){var e=i.default.performance,t=e&&e.timing;return t&&t.navigationStart},t.default=n},function(e,t,r){(function(t){var a,i=void 0!==t?t:"undefined"!=typeof window?window:{},n=r(23);"undefined"!=typeof document?a=document:(a=i["__GLOBAL_DOCUMENT_CACHE@4"])||(a=i["__GLOBAL_DOCUMENT_CACHE@4"]=n),e.exports=a}).call(t,r(6))},function(e,t){e.exports=function(e){return e.webpackPolyfill||(e.deprecate=function(){},e.paths=[],e.children||(e.children=[]),Object.defineProperty(e,"loaded",{enumerable:!0,get:function(){return e.l}}),Object.defineProperty(e,"id",{enumerable:!0,get:function(){return e.i}}),e.webpackPolyfill=1),e}},function(e,t,r){"use strict";var a=r(42),i=r(43),n=r(16);e.exports={formats:n,parse:i,stringify:a}},function(e,t,r){"use strict";var a=Object.prototype.hasOwnProperty,i=function(){for(var e=[],t=0;t<256;++t)e.push("%"+((t<16?"0":"")+t.toString(16)).toUpperCase());return e}(),n=function(e){for(var t;e.length;){var r=e.pop();if(t=r.obj[r.prop],Array.isArray(t)){for(var a=[],i=0;i<t.length;++i)void 0!==t[i]&&a.push(t[i]);r.obj[r.prop]=a}}return t};t.arrayToObject=function(e,t){for(var r=t&&t.plainObjects?Object.create(null):{},a=0;a<e.length;++a)void 0!==e[a]&&(r[a]=e[a]);return r},t.merge=function(e,r,i){if(!r)return e;if("object"!=typeof r){if(Array.isArray(e))e.push(r);else{if("object"!=typeof e)return[e,r];(i.plainObjects||i.allowPrototypes||!a.call(Object.prototype,r))&&(e[r]=!0)}return e}if("object"!=typeof e)return[e].concat(r);var n=e;return Array.isArray(e)&&!Array.isArray(r)&&(n=t.arrayToObject(e,i)),Array.isArray(e)&&Array.isArray(r)?(r.forEach(function(r,n){a.call(e,n)?e[n]&&"object"==typeof e[n]?e[n]=t.merge(e[n],r,i):e.push(r):e[n]=r}),e):Object.keys(r).reduce(function(e,n){var o=r[n];return a.call(e,n)?e[n]=t.merge(e[n],o,i):e[n]=o,e},n)},t.assign=function(e,t){return Object.keys(t).reduce(function(e,r){return e[r]=t[r],e},e)},t.decode=function(e){try{return decodeURIComponent(e.replace(/\+/g," "))}catch(t){return e}},t.encode=function(e){if(0===e.length)return e;for(var t="string"==typeof e?e:String(e),r="",a=0;a<t.length;++a){var n=t.charCodeAt(a);45===n||46===n||95===n||126===n||n>=48&&n<=57||n>=65&&n<=90||n>=97&&n<=122?r+=t.charAt(a):n<128?r+=i[n]:n<2048?r+=i[192|n>>6]+i[128|63&n]:n<55296||n>=57344?r+=i[224|n>>12]+i[128|n>>6&63]+i[128|63&n]:(a+=1,n=65536+((1023&n)<<10|1023&t.charCodeAt(a)),r+=i[240|n>>18]+i[128|n>>12&63]+i[128|n>>6&63]+i[128|63&n])}return r},t.compact=function(e){for(var t=[{obj:{o:e},prop:"o"}],r=[],a=0;a<t.length;++a)for(var i=t[a],o=i.obj[i.prop],s=Object.keys(o),u=0;u<s.length;++u){var l=s[u],d=o[l];"object"==typeof d&&null!==d&&-1===r.indexOf(d)&&(t.push({obj:o,prop:l}),r.push(d))}return n(t)},t.isRegExp=function(e){return"[object RegExp]"===Object.prototype.toString.call(e)},t.isBuffer=function(e){return null!==e&&void 0!==e&&!!(e.constructor&&e.constructor.isBuffer&&e.constructor.isBuffer(e))}},function(e,t,r){"use strict";var a=String.prototype.replace,i=/%20/g;e.exports={default:"RFC3986",formatters:{RFC1738:function(e){return a.call(e,i,"+")},RFC3986:function(e){return e}},RFC1738:"RFC1738",RFC3986:"RFC3986"}},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}function i(e){var t={};for(var r in e)e.hasOwnProperty(r)&&(t[e[r]]=r);return t}function n(e){var t={},r={};return Object.keys(e).forEach(function(a){var i=!1;if(e.hasOwnProperty(a)&&void 0!==e[a]){var n=a.split("_"),o=n[0],u=c[o];u||(s.default.info("Data key word `"+n[0]+"` not expected in "+a),u=o+"_"),n.splice(1).forEach(function(e){"url"===e&&(i=!0),p[e]?u+=p[e]:(s.default.info("Data key word `"+e+"` not expected in "+a),u+="_"+e+"_")}),i?r[u]=e[a]:t[u]=e[a]}}),(0,l.default)(t,r)}Object.defineProperty(t,"__esModule",{value:!0}),t.default=n;var o=r(4),s=a(o),u=r(1),l=a(u),d={a:"env",b:"beacon",d:"ad",e:"event",f:"experiment",m:"mux",n:"response",p:"player",q:"request",r:"retry",s:"session",t:"timestamp",u:"viewer",v:"video",w:"page",x:"view",y:"sub"},c=i(d),f={ad:"ad",ag:"aggregate",ap:"api",al:"application",ar:"architecture",as:"asset",au:"autoplay",av:"average",bi:"bitrate",br:"break",bw:"browser",by:"bytes",ca:"cached",cb:"cancel",cd:"code",cg:"category",ch:"changed",cn:"config",co:"count",ce:"counter",cp:"complete",cr:"creative",ct:"content",cu:"current",cx:"connection",dg:"downscaling",dm:"domain",dn:"cdn",do:"downscale",du:"duration",dv:"device",ec:"encoding",en:"end",eg:"engine",em:"embed",er:"error",es:"errorcode",et:"errortext",ee:"event",ev:"events",ex:"expires",fi:"first",fm:"family",ft:"format",fq:"frequency",fr:"frame",fs:"fullscreen",he:"headers",ho:"host",hn:"hostname",ht:"height",id:"id",ii:"init",in:"instance",ip:"ip",is:"is",ke:"key",la:"language",lb:"labeled",le:"level",li:"live",ld:"loaded",lo:"load",ls:"lists",lt:"latency",ma:"max",md:"media",me:"message",mi:"mime",ml:"midroll",mm:"min",mn:"manufacturer",mx:"mux",nm:"name",no:"number",on:"on",os:"os",pa:"paused",pb:"playback",pd:"producer",pe:"percentage",pf:"played",pg:"program",ph:"playhead",pi:"plugin",pl:"preroll",po:"poster",pr:"preload",py:"property",ra:"rate",rd:"requested",re:"rebuffer",rf:"rendition",ro:"ratio",rp:"response",rq:"request",rs:"requests",sa:"sample",se:"session",sk:"seek",sm:"stream",so:"source",sq:"sequence",sr:"series",st:"start",su:"startup",sv:"server",sw:"software",ta:"tag",tc:"tech",te:"text",th:"throughput",ti:"time",tl:"total",to:"to",tt:"title",ty:"type",ug:"upscaling",up:"upscale",ur:"url",us:"user",va:"variant",vd:"viewed",vi:"video",ve:"version",vw:"view",vr:"viewer",wd:"width",wa:"watch",wt:"waiting"},p=i(f)},function(e,t,r){"use strict";e.exports=r(19).default},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var i=function(){function e(e,t){var r=[],a=!0,i=!1,n=void 0;try{for(var o,s=e[Symbol.iterator]();!(a=(o=s.next()).done)&&(r.push(o.value),!t||r.length!==t);a=!0);}catch(e){i=!0,n=e}finally{try{!a&&s.return&&s.return()}finally{if(i)throw n}}return r}return function(t,r){if(Array.isArray(t))return t;if(Symbol.iterator in Object(t))return e(t,r);throw new TypeError("Invalid attempt to destructure non-iterable instance")}}(),n=r(0),o=a(n),s=r(9),u=r(4),l=a(u),d=r(10),c=a(d),f=r(3),p=a(f),h=r(21),_=a(h),v=r(51),m=a(v),y=r(52),b=a(y),g={},w=function e(t){var r=arguments;"string"==typeof t?e.hasOwnProperty(t)?o.default.setTimeout(function(){r=Array.prototype.splice.call(r,1),e[t].apply(null,r)},0):l.default.warn("`"+t+"` is an unknown task"):"function"==typeof t?o.default.setTimeout(function(){t(e)},0):l.default.warn("`"+t+"` is invalid.")};w.loaded=p.default.now(),w.VERSION="3.4.3",w.API_VERSION="2.1",w.PLAYER_TRACKED=!1,w.monitor=function(e,t){return(0,m.default)(w,e,t)},w.destroyMonitor=function(e){var t=(0,s.findMediaElement)(e),r=i(t,1),a=r[0];a&&a.mux&&"function"==typeof a.mux.destroy?a.mux.destroy():l.default.error("A video element monitor for `"+e+"` has not been initialized via `mux.monitor`.")},w.addHLSJS=function(e,t){var r=(0,s.getMuxPlayerId)(e);g[r]?g[r].addHLSJS(t):l.default.error("A monitor for `"+r+"` has not been initialized.")},w.addDashJS=function(e,t){var r=(0,s.getMuxPlayerId)(e);g[r]?g[r].addDashJS(t):l.default.error("A monitor for `"+r+"` has not been initialized.")},w.removeHLSJS=function(e){var t=(0,s.getMuxPlayerId)(e);g[t]?g[t].removeHLSJS():l.default.error("A monitor for `"+t+"` has not been initialized.")},w.removeDashJS=function(e){var t=(0,s.getMuxPlayerId)(e);g[t]?g[t].removeDashJS():l.default.error("A monitor for `"+t+"` has not been initialized.")},w.init=function(e,t){(0,c.default)()&&t&&t.respectDoNotTrack&&l.default.info("The browser's Do Not Track flag is enabled - Mux beaconing is disabled.");var r=(0,s.getMuxPlayerId)(e);g[r]=new _.default(w,r,t)},w.emit=function(e,t,r){var a=(0,s.getMuxPlayerId)(e);g[a]?(g[a].emit(t,r),"destroy"===t&&delete g[a]):l.default.error("A monitor for `"+a+"` has not been initialized.")},void 0!==o.default&&"function"==typeof o.default.addEventListener&&o.default.addEventListener("unload",function(){w.WINDOW_UNLOADING=!0},!1),w.checkDoNotTrack=c.default,w.log=l.default,w.utils=b.default,t.default=w},function(e,t,r){var a,i;!function(n,o){"use strict";a=o,void 0!==(i="function"==typeof a?a.call(t,r,t,e):a)&&(e.exports=i)}(0,function(){"use strict";function e(e,t){var r=e[t];if("function"==typeof r.bind)return r.bind(e);try{return Function.prototype.bind.call(r,e)}catch(t){return function(){return Function.prototype.apply.apply(r,[e,arguments])}}}function t(t){return"debug"===t&&(t="log"),typeof console!==s&&(void 0!==console[t]?e(console,t):void 0!==console.log?e(console,"log"):o)}function r(e,t){for(var r=0;r<u.length;r++){var a=u[r];this[a]=r<e?o:this.methodFactory(a,e,t)}this.log=this.debug}function a(e,t,a){return function(){typeof console!==s&&(r.call(this,t,a),this[e].apply(this,arguments))}}function i(e,r,i){return t(e)||a.apply(this,arguments)}function n(e,t,a){function n(e){var t=(u[e]||"silent").toUpperCase();if(typeof window!==s){try{return void(window.localStorage[c]=t)}catch(e){}try{window.document.cookie=encodeURIComponent(c)+"="+t+";"}catch(e){}}}function o(){var e;if(typeof window!==s){try{e=window.localStorage[c]}catch(e){}if(typeof e===s)try{var t=window.document.cookie,r=t.indexOf(encodeURIComponent(c)+"=");r&&(e=/^([^;]+)/.exec(t.slice(r))[1])}catch(e){}return void 0===d.levels[e]&&(e=void 0),e}}var l,d=this,c="loglevel";e&&(c+=":"+e),d.levels={TRACE:0,DEBUG:1,INFO:2,WARN:3,ERROR:4,SILENT:5},d.methodFactory=a||i,d.getLevel=function(){return l},d.setLevel=function(t,a){if("string"==typeof t&&void 0!==d.levels[t.toUpperCase()]&&(t=d.levels[t.toUpperCase()]),!("number"==typeof t&&t>=0&&t<=d.levels.SILENT))throw"log.setLevel() called with invalid level: "+t;if(l=t,!1!==a&&n(t),r.call(d,t,e),typeof console===s&&t<d.levels.SILENT)return"No console available for logging"},d.setDefaultLevel=function(e){o()||d.setLevel(e,!1)},d.enableAll=function(e){d.setLevel(d.levels.TRACE,e)},d.disableAll=function(e){d.setLevel(d.levels.SILENT,e)};var f=o();null==f&&(f=null==t?"WARN":t),d.setLevel(f,!1)}var o=function(){},s="undefined",u=["trace","debug","info","warn","error"],l=new n,d={};l.getLogger=function(e){if("string"!=typeof e||""===e)throw new TypeError("You must supply a name when creating a logger.");var t=d[e];return t||(t=d[e]=new n(e,l.getLevel(),l.methodFactory)),t};var c=typeof window!==s?window.log:void 0;return l.noConflict=function(){return typeof window!==s&&window.log===l&&(window.log=c),l},l})},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var i=function(){function e(e,t){var r=[],a=!0,i=!1,n=void 0;try{for(var o,s=e[Symbol.iterator]();!(a=(o=s.next()).done)&&(r.push(o.value),!t||r.length!==t);a=!0);}catch(e){i=!0,n=e}finally{try{!a&&s.return&&s.return()}finally{if(i)throw n}}return r}return function(t,r){if(Array.isArray(t))return t;if(Symbol.iterator in Object(t))return e(t,r);throw new TypeError("Invalid attempt to destructure non-iterable instance")}}(),n=r(4),o=a(n),s=r(1),u=a(s),l=r(7),d=r(5),c=r(0),f=a(c),p=r(11),h=a(p),_=r(2),v=a(_),m=r(22),y=a(m),b=r(25),g=r(26),w=r(27),x=a(w),S=r(28),T=a(S),k=r(29),E=a(k),O=r(30),M=a(O),P=r(31),A=a(P),q=r(32),D=a(q),R=r(33),j=a(R),I=r(34),L=a(I),C=r(35),H=a(C),N=r(36),B=a(N),F=r(37),W=a(F),U=r(38),G=a(U),V=r(39),J=a(V),Q=r(40),z=a(Q),$=r(50),K=a($),X=["viewstart","ended","loadstart","pause","play","playing","ratechange","waiting","adplay","adpause","adended","aderror","adplaying","adrequest","adresponse","adbreakstart","adbreakend","rebufferstart","rebufferend","seeked","error","hb","requestcompleted","requestfailed","requestcanceled","renditionchange"],Y=function(e,t,r){var a=this;this.DOM_CONTENT_LOADED_EVENT_END=h.default.domContentLoadedEventEnd(),this.NAVIGATION_START=h.default.navigationStart();var i={debug:!1,minimumRebufferDuration:250,sustainedRebufferThreshold:1e3,playbackHeartbeatTime:25,beaconDomain:"litix.io",sampleRate:1,disableCookies:!1,respectDoNotTrack:!1,disableRebufferTracking:!1,errorTranslator:function(e){return e}};this.mux=e,this.id=t,r=(0,u.default)(i,r),r.data=r.data||{},r.data.property_key&&(r.data.env_key=r.data.property_key,delete r.data.property_key),o.default.setLevel(r.debug?"debug":"warn"),this.getPlayheadTime=r.getPlayheadTime,this.getStateData=r.getStateData||function(){},this.getAdData=r.getAdData||function(){},this.minimumRebufferDuration=r.minimumRebufferDuration,this.sustainedRebufferThreshold=r.sustainedRebufferThreshold,this.playbackHeartbeatTime=r.playbackHeartbeatTime,this.disableRebufferTracking=r.disableRebufferTracking,this.disableRebufferTracking&&this.mux.log.warn("Disabling rebuffer tracking. This should only be used in specific circumstances as a last resort when your player is known to unreliably track rebuffering."),this.errorTranslator=r.errorTranslator,this.playbackEventDispatcher=new z.default(e,r.data.env_key,r),this.data={player_instance_id:(0,l.generateUUID)(),mux_sample_rate:r.sampleRate,beacon_domain:r.beaconDomain},this.data.view_sequence_number=1,this.data.player_sequence_number=1,this.oldEmit=this.emit,this.emit=function(e,t){t=(0,u.default)({viewer_time:this.mux.utils.now()},t),this.oldEmit(e,t)};var n=function(){void 0===this.data.view_start&&(this.data.view_start=this.mux.utils.now(),this.emit("viewstart"))}.bind(this);this.on("viewinit",function(e,t){this._resetVideoData(),this._resetViewData(),this._resetErrorData(),this._updateStateData(),(0,u.default)(this.data,t),this._initializeViewData(),this.one("play",n),this.one("adbreakstart",n)});var s=function(e){this.emit("viewend"),this.send("viewend"),this.emit("viewinit",e)}.bind(this);this.on("videochange",function(e,t){s(t)}),this.on("programchange",function(e,t){this.data.player_is_paused&&this.mux.log.warn("The `programchange` event is intended to be used when the content changes mid playback without the video source changing, however the video is not currently playing. If the video source is changing please use the videochange event otherwise you will lose startup time information."),s((0,u.default)(t,{view_program_changed:!0})),n()}),this.on("destroy",this.destroy);var d=this.destroy.bind(this);void 0!==f.default&&"function"==typeof f.default.addEventListener&&f.default.addEventListener("unload",d,!1),this.on("destroy",function(){void 0!==f.default&&"function"==typeof f.default.removeEventListener&&f.default.removeEventListener("unload",d)}),this.on("playerready",function(e,t){(0,u.default)(this.data,t)}),X.forEach(function(e){a.on(e,function(t,r){0!==e.indexOf("ad")&&this._updateStateData(),(0,u.default)(this.data,r),this._sanitizeData()}),a.on("after"+e,function(){("error"!==e||this.viewErrored)&&this.send(e)})}),this.on("viewend",function(e,t){(0,u.default)(a.data,t)});var c=function(e){var t=this.mux.utils.now();this.data.player_init_time&&(this.data.player_startup_time=t-this.data.player_init_time),!this.mux.PLAYER_TRACKED&&this.NAVIGATION_START&&(this.mux.PLAYER_TRACKED=!0,(this.data.player_init_time||this.DOM_CONTENT_LOADED_EVENT_END)&&(this.data.page_load_time=Math.min(this.data.player_init_time||1/0,this.DOM_CONTENT_LOADED_EVENT_END||1/0)-this.NAVIGATION_START)),this.send("playerready"),delete this.data.player_startup_time,delete this.data.page_load_time};this.one("playerready",c),E.default.apply(this),J.default.apply(this),B.default.apply(this),D.default.apply(this),T.default.apply(this),H.default.apply(this),M.default.apply(this),A.default.apply(this),W.default.apply(this),j.default.apply(this),L.default.apply(this),G.default.apply(this),K.default.apply(this),r.hlsjs&&this.addHLSJS(r),r.dashjs&&this.addDashJS(r),this.emit("viewinit",r.data)};(0,u.default)(Y.prototype,x.default.prototype),(0,u.default)(Y.prototype,D.default.prototype),(0,u.default)(Y.prototype,B.default.prototype),(0,u.default)(Y.prototype,T.default.prototype),(0,u.default)(Y.prototype,M.default.prototype),(0,u.default)(Y.prototype,A.default.prototype),(0,u.default)(Y.prototype,W.default.prototype),(0,u.default)(Y.prototype,j.default.prototype),(0,u.default)(Y.prototype,L.default.prototype),Y.prototype.destroy=function(){this._destroyed||(this._destroyed=!0,void 0!==this.data.view_start&&(this.emit("viewend"),this.send("viewend")),this.playbackEventDispatcher.destroy(),this.removeHLSJS(),this.removeDashJS(),f.default.clearTimeout(this._heartBeatTimeout))},Y.prototype.send=function(e){var t=(0,u.default)({},this.data);if(1===t.player_error_code&&(delete t.player_error_code,delete t.player_error_message),t.player_source_duration===1/0||t.video_source_duration===1/0?t.video_source_is_live=!0:(t.player_source_duration>0||t.video_source_duration>0)&&(t.video_source_is_live=!1),t.video_source_url=t.video_source_url||t.player_source_url,t.video_source_url){var r=(0,d.extractHostnameAndDomain)(t.video_source_url),a=i(r,2),n=a[0],o=a[1];t.video_source_domain=o,t.video_source_hostname=n}delete t.ad_request_id,this.playbackEventDispatcher.send(e,t),this.data.view_sequence_number++,this.data.player_sequence_number++,this._restartHeartBeat()},Y.prototype._updateStateData=function(){(0,u.default)(this.data,this.getStateData()),this.getPlayheadTime&&(this.data.player_playhead_time=this.getPlayheadTime()),this._sanitizeData()},Y.prototype._sanitizeData=function(){var e=this;["player_width","player_height","video_source_width","video_source_height","player_playhead_time","video_source_bitrate"].forEach(function(t){var r=parseInt(e.data[t],10);e.data[t]=isNaN(r)?void 0:r}),["player_source_url","video_source_url"].forEach(function(t){e.data[t]&&0===e.data[t].toLowerCase().indexOf("data:")&&(e.data[t]="MSE style URL")})},Y.prototype._resetVideoData=function(e,t){var r=this;Object.keys(this.data).forEach(function(e){0===e.indexOf("video_")&&delete r.data[e]})},Y.prototype._resetViewData=function(){var e=this;Object.keys(this.data).forEach(function(t){0===t.indexOf("view_")&&delete e.data[t]}),this.data.view_sequence_number=1},Y.prototype._resetErrorData=function(e,t){delete this.data.player_error_code,delete this.data.player_error_message},Y.prototype._initializeViewData=function(){var e=this,t=this.data.view_id=(0,l.generateUUID)();this.data.video_id||(this.data.video_id=(0,y.default)(this.data.player_source_url));var r=function(){t===e.data.view_id&&(0,v.default)(e.data,"player_view_count",1)};this.data.player_is_paused?this.one("play",r):r()},Y.prototype._restartHeartBeat=function(){var e=this;f.default.clearTimeout(this._heartBeatTimeout),this.viewErrored||(this._heartBeatTimeout=f.default.setTimeout(function(){e.data.player_is_paused||e.emit("hb")},1e4))},Y.prototype.addHLSJS=function(e){return e.hlsjs?this.hlsjs?void this.mux.log.warn("An instance of HLS.js is already being monitored for this player."):(this.hlsjs=e.hlsjs,void(0,b.monitorHlsJs)(this.mux,this.id,e.hlsjs,{},e.Hls||f.default.Hls)):void this.mux.log.warn("You must pass a valid hlsjs instance in order to track it.")},Y.prototype.removeHLSJS=function(){this.hlsjs&&((0,b.stopMonitoringHlsJs)(this.hlsjs),this.hlsjs=void 0)},Y.prototype.addDashJS=function(e){return e.dashjs?this.dashjs?void this.mux.log.warn("An instance of Dash.js is already being monitored for this player."):(this.dashjs=e.dashjs,void(0,g.monitorDashJS)(this.mux,this.id,e.dashjs)):void this.mux.log.warn("You must pass a valid dashjs instance in order to track it.")},Y.prototype.removeDashJS=function(){this.dashjs&&((0,g.stopMonitoringDashJS)(this.dashjs),this.dashjs=void 0)},t.default=Y},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}function i(e){var t=o.default.createElement("a");t.href=e;var r=t.pathname.replace(/\.[^\/.]+$/,"");return u.default.encode(t.host+r).split("=")[0]}Object.defineProperty(t,"__esModule",{value:!0}),t.default=i;var n=r(12),o=a(n),s=r(24),u=a(s)},function(e,t){},function(e,t,r){(function(e,a){var i;!function(a){var n=("object"==typeof e&&e&&e.exports,function(e){this.message=e});n.prototype=new Error,n.prototype.name="InvalidCharacterError";var o=function(e){throw new n(e)},s="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",u=/[\t\n\f\r ]/g,l=function(e){e=String(e).replace(u,"");var t=e.length;t%4==0&&(e=e.replace(/==?$/,""),t=e.length),(t%4==1||/[^+a-zA-Z0-9\/]/.test(e))&&o("Invalid character: the string to be decoded is not correctly encoded.");for(var r,a,i=0,n="",l=-1;++l<t;)a=s.indexOf(e.charAt(l)),r=i%4?64*r+a:a,i++%4&&(n+=String.fromCharCode(255&r>>(-2*i&6)));return n},d=function(e){e=String(e),/[^\0-\xFF]/.test(e)&&o("The string to be encoded contains characters outside of the Latin1 range.");for(var t,r,a,i,n=e.length%3,u="",l=-1,d=e.length-n;++l<d;)t=e.charCodeAt(l)<<16,r=e.charCodeAt(++l)<<8,a=e.charCodeAt(++l),i=t+r+a,u+=s.charAt(i>>18&63)+s.charAt(i>>12&63)+s.charAt(i>>6&63)+s.charAt(63&i);return 2==n?(t=e.charCodeAt(l)<<8,r=e.charCodeAt(++l),i=t+r,u+=s.charAt(i>>10)+s.charAt(i>>4&63)+s.charAt(i<<2&63)+"="):1==n&&(i=e.charCodeAt(l),u+=s.charAt(i>>2)+s.charAt(i<<4&63)+"=="),u},c={encode:d,decode:l,version:"0.1.0"};void 0!==(i=function(){return c}.call(t,r,t,e))&&(e.exports=i)}()}).call(t,r(13)(e),r(6))},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0}),t.stopMonitoringHlsJs=t.monitorHlsJs=void 0;var i=r(8),n=a(i),o=r(11),s=a(o),u=r(5),l=function(e){if(!e)return{};var t=s.default.navigationStart();return{bytesLoaded:e.total,requestStart:Math.round(t+e.trequest),responseStart:Math.round(t+e.tfirst),responseEnd:Math.round(t+e.tload)}},d=function(e){if(e&&"function"==typeof e.getAllResponseHeaders)return(0,n.default)(e.getAllResponseHeaders())},c=function(e,t,r){var a=(arguments.length>3&&void 0!==arguments[3]&&arguments[3],arguments[4]),i=e.log;if(!s.default.exists())return void i.warn("performance timing not supported. Not tracking HLS.js.");var n=function(r,a){return e.emit(t,r,a)},o=function(e,t){var r=t.levels,a=t.audioTracks,i=t.url,o=t.stats,s=t.networkDetails,c={},f={};r.forEach(function(e,t){c[t]={width:e.width,height:e.height,bitrate:e.bitrate,attrs:e.attrs}}),a.forEach(function(e,t){f[t]={name:e.name,language:e.lang,bitrate:e.bitrate}});var p=l(o),h=p.bytesLoaded,_=p.requestStart,v=p.responseStart,m=p.responseEnd;n("requestcompleted",{request_event_type:e,request_bytes_loaded:h,request_start:_,request_response_start:v,request_response_end:m,request_type:"manifest",request_hostname:(0,u.extractHostname)(i),request_response_headers:d(s),request_rendition_lists:{media:c,audio:f,video:{}}})};r.on(a.Events.MANIFEST_LOADED,o);var c=function(e,t){var r=t.details,a=t.level,i=t.networkDetails,o=t.stats,s=l(o),c=s.bytesLoaded,f=s.requestStart,p=s.responseStart,h=s.responseEnd;n("requestcompleted",{request_event_type:e,request_bytes_loaded:c,request_start:f,request_response_start:p,request_response_end:h,request_current_level:a,request_type:"manifest",request_hostname:(0,u.extractHostname)(r.url),request_response_headers:d(i)})};r.on(a.Events.LEVEL_LOADED,c);var f=function(e,t){var r=t.details,a=t.networkDetails,i=t.stats,o=l(i),s=o.bytesLoaded,c=o.requestStart,f=o.responseStart,p=o.responseEnd;n("requestcompleted",{request_event_type:e,request_bytes_loaded:s,request_start:c,request_response_start:f,request_response_end:p,request_type:"manifest",request_hostname:(0,u.extractHostname)(r.url),request_response_headers:d(a)})};r.on(a.Events.AUDIO_TRACK_LOADED,f);var p=function(e,t){var a=t.stats,i=t.networkDetails,o=t.frag,s=l(a),c=s.bytesLoaded,f=s.requestStart,p=s.responseStart,h=s.responseEnd,_={request_event_type:e,request_bytes_loaded:c,request_start:f,request_response_start:p,request_response_end:h,request_hostname:i?(0,u.extractHostname)(i.responseURL):void 0,request_response_headers:d(i),request_media_duration:o.duration};"main"===o.type?(_.request_type="media",_.request_current_level=o.level,_.request_video_width=(r.levels[o.level]||{}).width,_.request_video_height=(r.levels[o.level]||{}).height):_.request_type=o.type,n("requestcompleted",_)};r.on(a.Events.FRAG_LOADED,p);var h=function(e,t){var r=t.details,i=t.response,o=t.context,s=t.frag;if(r===a.ErrorDetails.MANIFEST_LOAD_ERROR||r===a.ErrorDetails.MANIFEST_LOAD_TIMEOUT||r===a.ErrorDetails.FRAG_LOAD_ERROR||r===a.ErrorDetails.FRAG_LOAD_TIMEOUT||r===a.ErrorDetails.LEVEL_LOAD_ERROR||r===a.ErrorDetails.LEVEL_LOAD_TIMEOUT){var l=s&&s.url||o&&o.url||"";n("requestfailed",{request_error:r,request_url:l,request_hostname:(0,u.extractHostname)(l),request_type:r===a.ErrorDetails.FRAG_LOAD_ERROR||r===a.ErrorDetails.FRAG_LOAD_TIMEOUT?"media":"manifest",request_error_code:i&&i.code,request_error_text:i&&i.text})}};r.on(a.Events.ERROR,h);var _=function(e,t){var r=t.frag,a=r&&r._url||"";n("requestcanceled",{request_cancel:e,request_url:a,request_type:"media",request_hostname:(0,u.extractHostname)(a)})};r.on(a.Events.FRAG_LOAD_EMERGENCY_ABORTED,_);var v=function(e,t){var a=t.level,o=r.levels.find(function(e){return e.level===a});if(o&&o.attrs&&o.attrs.BANDWIDTH){var s=o.attrs.BANDWIDTH;s?n("renditionchange",{video_source_bitrate:s,video_source_width:o.width,video_source_height:o.height}):i.warn("missing BANDWIDTH from HLS manifest parsed by HLS.js")}};r.on(a.Events.LEVEL_SWITCHED,v),r._stopMuxMonitor=function(){r.off(a.Events.MANIFEST_LOADED,o),r.off(a.Events.LEVEL_LOADED,c),r.off(a.Events.AUDIO_TRACK_LOADED,f),r.off(a.Events.FRAG_LOADED,p),r.off(a.Events.ERROR,h),r.off(a.Events.FRAG_LOAD_EMERGENCY_ABORTED,_),r.off(a.Events.LEVEL_SWITCHED,v),r.off(a.Events.DESTROYING,r._stopMuxMonitor),delete r._stopMuxMonitor},r.on(a.Events.DESTROYING,r._stopMuxMonitor)},f=function(e){e&&"function"==typeof e._stopMuxMonitor&&e._stopMuxMonitor()};t.monitorHlsJs=c,t.stopMonitoringHlsJs=f},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0}),t.stopMonitoringDashJS=t.monitorDashJS=void 0;var i=r(0),n=a(i),o=r(8),s=a(o),u=r(5),l=function(e,t){if(!e||"function"!=typeof e.getRequests)return{};var r=e.getRequests({state:"executed"});if(0===r.length)return{};var a=r[r.length-1],i=(0,u.extractHostname)(a.url),n=a.bytesLoaded,o=new Date(a.requestStartDate).getTime(),l=new Date(a.firstByteDate).getTime(),d=new Date(a.requestEndDate).getTime(),c=isNaN(a.duration)?0:a.duration,f="function"==typeof t.getMetricsFor?t.getMetricsFor(a.mediaType).HttpList:t.getDashMetrics().getHttpRequests(a.mediaType),p=void 0;return f.length>0&&(p=(0,s.default)(f[f.length-1]._responseHeaders||"")),{requestStart:o,requestResponseStart:l,requestResponseEnd:d,requestBytesLoaded:n,requestResponseHeaders:p,requestMediaDuration:c,requestHostname:i}},d=function(e,t){var r=t.getQualityFor(e),a=t.getCurrentTrackFor(e),i=a.bitrateList;return i?{currentLevel:r,renditionWidth:i[r].width||null,renditionHeight:i[r].height||null,renditionBitrate:i[r].bandwidth}:{}},c=function(e,t,r){var a=(arguments.length>3&&void 0!==arguments[3]&&arguments[3],e.log);if(!r||!r.on)return void a.warn("Invalid dash.js player reference. Monitoring blocked.");var i=function(r,a){return e.emit(t,r,a)},o=function(e){var t=e.type,r=e.data,a=r||{},n=a.url;i("requestcompleted",{request_event_type:t,request_start:0,request_response_start:0,request_response_end:0,request_bytes_loaded:-1,request_type:"manifest",request_hostname:(0,u.extractHostname)(n)})};r.on("manifestLoaded",o);var s={},c=function(e){var t=e.type,a=e.fragmentModel,n=e.chunk,o=n||{},u=o.mediaInfo,d=u||{},c=d.type,f=d.bitrateList;f=f||[];var p={};f.forEach(function(e,t){p[t]={},p[t].width=e.width,p[t].height=e.height,p[t].bitrate=e.bandwidth,p[t].attrs={}}),"video"===c?s.video=p:"audio"===c?s.audio=p:s.media=p;var h=l(a,r),_=h.requestStart,v=h.requestResponseStart,m=h.requestResponseEnd,y=h.requestResponseHeaders,b=h.requestMediaDuration,g=h.requestHostname;i("requestcompleted",{request_event_type:t,request_start:_,request_response_start:v,request_response_end:m,request_bytes_loaded:-1,request_type:c+"_init",request_response_headers:y,request_hostname:g,request_media_duration:b,request_rendition_lists:s})};r.on("initFragmentLoaded",c);var f=function(e){var t=e.type,a=e.fragmentModel,n=e.chunk,o=n||{},s=o.mediaInfo,u=o.start,c=s||{},f=c.type,p=l(a,r),h=p.requestStart,_=p.requestResponseStart,v=p.requestResponseEnd,m=p.requestBytesLoaded,y=p.requestResponseHeaders,b=p.requestMediaDuration,g=p.requestHostname,w=d(f,r),x=w.currentLevel,S=w.renditionWidth,T=w.renditionHeight,k=w.renditionBitrate;i("requestcompleted",{request_event_type:t,request_start:h,request_response_start:_,request_response_end:v,request_bytes_loaded:m,request_type:f,request_response_headers:y,request_hostname:g,request_media_start_time:u,request_media_duration:b,request_current_level:x,request_labeled_bitrate:k,request_video_width:S,request_video_height:T})};r.on("mediaFragmentLoaded",f);var p={video:void 0,audio:void 0,totalBitrate:void 0},h=function(){if(p.video&&"number"==typeof p.video.bitrate){if(!p.video.width||!p.video.height)return void a.warn("have bitrate info for video but missing width/height");var e=p.video.bitrate;return p.audio&&"number"==typeof p.audio.bitrate&&(e+=p.audio.bitrate),e!==p.totalBitrate?(p.totalBitrate=e,{video_source_bitrate:e,video_source_height:p.video.height,video_source_width:p.video.width}):void 0}},_=function(e,t,n){if("number"!=typeof e.newQuality)return void a.warn("missing evt.newQuality in qualityChangeRendered event",e);var o=e.mediaType;if("audio"===o||"video"===o){var s=r.getBitrateInfoListFor(o).find(function(t){return t.qualityIndex===e.newQuality});if(!s||"number"!=typeof s.bitrate)return void a.warn("missing bitrate info for "+o);p[o]=s;var u=h();u&&i("renditionchange",u)}};r.on("qualityChangeRendered",_);var v=function(e){var t=e.error,r=e.event;r=r||{};var a=r.request||{},o=n.default.event&&n.default.event.currentTarget||{};i("requestfailed",{request_error:t+"_"+r.id+"_"+a.type,request_url:r.url,request_hostname:(0,u.extractHostname)(r.url),request_type:a.mediaType,request_error_code:o.status,request_error_type:o.statusText})};r.on("error",v),r._stopMuxMonitor=function(){r.off("manifestLoaded",o),r.off("initFragmentLoaded",c),r.off("mediaFragmentLoaded",f),r.off("qualityChangeRendered",_),r.off("error",v),delete r._stopMuxMonitor}},f=function(e){e&&"function"==typeof e._stopMuxMonitor&&e._stopMuxMonitor()};t.monitorDashJS=c,t.stopMonitoringDashJS=f},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=function(){},i=0;a.prototype.on=function(e,t,r){return t._eventEmitterGuid=t._eventEmitterGuid||++i,this._listeners=this._listeners||{},this._listeners[e]=this._listeners[e]||[],r&&(t=t.bind(r)),this._listeners[e].push(t),t},a.prototype.off=function(e,t){var r=this._listeners&&this._listeners[e];r&&r.forEach(function(e,a){e._eventEmitterGuid===t._eventEmitterGuid&&r.splice(a,1)})},a.prototype.one=function(e,t,r){var a=this;t._eventEmitterGuid=t._eventEmitterGuid||++i;var n=function i(){a.off(e,i),t.apply(r||this,arguments)};n._eventEmitterGuid=t._eventEmitterGuid,this.on(e,n)},a.prototype.emit=function(e,t){var r=this;if(this._listeners){t=t||{};var a=this._listeners["before*"]||[],i=this._listeners[e]||[],n=this._listeners["after"+e]||[],o=function(t,a){t=t.slice(),t.forEach(function(t){t.call(r,{type:e},a)})};o(a,t),o(i,t),o(n,t)}},t.default=a},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=r(0),i=function(e){return e&&e.__esModule?e:{default:e}}(a),n=function(){this._playbackHeartbeatInterval=null,this._playheadShouldBeProgressing=!1,this.on("playing",function(){this._playheadShouldBeProgressing=!0}),this.on("play",this._startPlaybackHeartbeatInterval),this.on("playing",this._startPlaybackHeartbeatInterval),this.on("adbreakstart",this._startPlaybackHeartbeatInterval),this.on("adplay",this._startPlaybackHeartbeatInterval),this.on("adplaying",this._startPlaybackHeartbeatInterval),this.on("seeking",this._startPlaybackHeartbeatInterval),this.on("devicewake",this._startPlaybackHeartbeatInterval),this.on("viewstart",this._startPlaybackHeartbeatInterval),this.on("pause",this._stopPlaybackHeartbeatInterval),this.on("ended",this._stopPlaybackHeartbeatInterval),this.on("viewend",this._stopPlaybackHeartbeatInterval),this.on("error",this._stopPlaybackHeartbeatInterval),this.on("aderror",this._stopPlaybackHeartbeatInterval),this.on("adpause",this._stopPlaybackHeartbeatInterval),this.on("adended",this._stopPlaybackHeartbeatInterval),this.on("adbreakend",this._stopPlaybackHeartbeatInterval),this.on("seeked",function(){this.data.player_is_paused?this._stopPlaybackHeartbeatInterval():this._startPlaybackHeartbeatInterval()}),this.on("timeupdate",function(){null!==this._playbackHeartbeatInterval&&this.emit("playbackheartbeat")}),this.on("devicesleep",function(e,t){null!==this._playbackHeartbeatInterval&&(i.default.clearInterval(this._playbackHeartbeatInterval),this.emit("playbackheartbeatend",{viewer_time:t.viewer_time}),this._playbackHeartbeatInterval=null)})};n.prototype._startPlaybackHeartbeatInterval=function(){var e=this;null===this._playbackHeartbeatInterval&&(this.emit("playbackheartbeat"),this._playbackHeartbeatInterval=i.default.setInterval(function(){e.emit("playbackheartbeat")},this.playbackHeartbeatTime))},n.prototype._stopPlaybackHeartbeatInterval=function(){this._playheadShouldBeProgressing=!1,null!==this._playbackHeartbeatInterval&&(i.default.clearInterval(this._playbackHeartbeatInterval),this.emit("playbackheartbeatend"),this._playbackHeartbeatInterval=null)},t.default=n},function(e,t,r){"use strict";function a(){var e=this;this.on("viewinit",function(){e.viewErrored=!1}),this.on("error",function(){try{var t=e.errorTranslator({player_error_code:e.data.player_error_code,player_error_message:e.data.player_error_message});t?(e.data.player_error_code=t.player_error_code,e.data.player_error_message=t.player_error_message,e.viewErrored=!0):(delete e.data.player_error_code,delete e.data.player_error_message)}catch(t){e.mux.log.warn("Exception in error translator callback.",t),e.viewErrored=!0}})}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=r(2),i=function(e){return e&&e.__esModule?e:{default:e}}(a),n=function(){this._watchTimeTrackerLastCheckedTime=null,this.on("playbackheartbeat",this._updateWatchTime),this.on("playbackheartbeatend",this._clearWatchTimeState)};n.prototype._updateWatchTime=function(e,t){var r=t.viewer_time;null===this._watchTimeTrackerLastCheckedTime&&(this._watchTimeTrackerLastCheckedTime=r),(0,i.default)(this.data,"view_watch_time",r-this._watchTimeTrackerLastCheckedTime),this._watchTimeTrackerLastCheckedTime=r},n.prototype._clearWatchTimeState=function(e,t){this._updateWatchTime(e,t),this._watchTimeTrackerLastCheckedTime=null},t.default=n},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=r(2),i=function(e){return e&&e.__esModule?e:{default:e}}(a),n=function(){this._playbackTimeTrackerLastPlayheadPosition=-1,this.on("playbackheartbeat",this._updatePlaybackTime),this.on("playbackheartbeatend",this._clearPlaybackTimeState),this.on("seeking",this._clearPlaybackTimeState)};n.prototype._updatePlaybackTime=function(){var e=this.data.player_playhead_time;if(this._playbackTimeTrackerLastPlayheadPosition>=0&&e>this._playbackTimeTrackerLastPlayheadPosition){var t=e-this._playbackTimeTrackerLastPlayheadPosition;t<=1e3&&(0,i.default)(this.data,"view_content_playback_time",t)}this._playbackTimeTrackerLastPlayheadPosition=e},n.prototype._clearPlaybackTimeState=function(){this._updatePlaybackTime(),this._playbackTimeTrackerLastPlayheadPosition=-1},t.default=n},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=function(){this.on("playbackheartbeat",this._updatePlayheadTime),this.on("playbackheartbeatend",this._updatePlayheadTime),this.on("timeupdate",this._updatePlayheadTime),this.on("destroy",function(){this.off("timeupdate",this._updatePlayheadTime)})};a.prototype._updatePlayheadTime=function(e,t){if(t.player_playhead_time)this.data.player_playhead_time=t.player_playhead_time;else if(this.getPlayheadTime){var r=this.getPlayheadTime();void 0!==r&&(this.data.player_playhead_time=r)}},t.default=a},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var i=r(3),n=a(i),o=r(2),s=a(o),u=function(){this._lastCheckedTime=null,this._lastPlayheadTime=null,this._lastPlayheadTimeUpdatedTime=null,this.on("playbackheartbeat",this._checkIfRebuffering),this.on("playbackheartbeatend",this._cleanupRebufferTracker),this.on("seeking",function(){this._cleanupRebufferTracker(null,{viewer_time:n.default.now()})})};u.prototype._checkIfRebuffering=function(e,t){if(!this.disableRebufferTracking){if(this.isSeeking||this.isAdBreak||!this._playheadShouldBeProgressing)return void this._cleanupRebufferTracker(e,t);if(null===this._lastCheckedTime)return this._prepareRebufferTrackerState(t.viewer_time),void this._updateRebufferMetrics();if(this._lastPlayheadTime!==this.data.player_playhead_time)return void this._cleanupRebufferTracker(e,t,!0);var r=t.viewer_time-this._lastPlayheadTimeUpdatedTime;r>=this.sustainedRebufferThreshold&&(this._rebuffering?this._updateRebufferMetrics(t.viewer_time-this._lastCheckedTime):(this._rebuffering=!0,(0,s.default)(this.data,"view_rebuffer_count",1),this._updateRebufferMetrics(r),this.emit("rebufferstart"))),this._lastCheckedTime=t.viewer_time}},u.prototype._clearRebufferTrackerState=function(){this._lastCheckedTime=null,this._lastPlayheadTime=null,this._lastPlayheadTimeUpdatedTime=null},u.prototype._prepareRebufferTrackerState=function(e){this._lastCheckedTime=e,this._lastPlayheadTime=this.data.player_playhead_time,this._lastPlayheadTimeUpdatedTime=e},u.prototype._cleanupRebufferTracker=function(e,t){var r=arguments.length>2&&void 0!==arguments[2]&&arguments[2];if(!this.disableRebufferTracking){if(this._rebuffering)this._rebuffering=!1,this._updateRebufferMetrics(t.viewer_time-this._lastCheckedTime),this.emit("rebufferend",{viewer_time:t.viewer_time});else{if(null===this._lastCheckedTime)return void this._updateRebufferMetrics();var a=this.data.player_playhead_time-this._lastPlayheadTime,i=t.viewer_time-this._lastPlayheadTimeUpdatedTime;a>0&&i-a>this.minimumRebufferDuration?((0,s.default)(this.data,"view_rebuffer_count",1),this._updateRebufferMetrics(i-a),this.emit("rebufferstart",{viewer_time:this._lastPlayheadTimeUpdatedTime}),this.emit("rebufferend",{viewer_time:this._lastPlayheadTimeUpdatedTime+i-a})):this._updateRebufferMetrics()}r?this._prepareRebufferTrackerState(t.viewer_time):this._clearRebufferTrackerState()}},u.prototype._updateRebufferMetrics=function(e){e>0&&(0,s.default)(this.data,"view_rebuffer_duration",e),this.data.view_watch_time>=0&&this.data.view_rebuffer_count>0&&(this.data.view_rebuffer_frequency=this.data.view_rebuffer_count/this.data.view_watch_time,this.data.view_rebuffer_percentage=this.data.view_rebuffer_duration/this.data.view_watch_time)},t.default=u},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=r(3),i=function(e){return e&&e.__esModule?e:{default:e}}(a),n=function(){this.on("viewinit",function(){var e=this.data,t=e.view_id;if(!e.view_program_changed){var r=function(e,r){var a=r.viewer_time;"playing"===e.type&&void 0===this.data.view_time_to_first_frame?this.calculateTimeToFirstFrame(a||i.default.now(),t):"adplaying"!==e.type||void 0!==this.data.view_time_to_first_frame&&!this.inPrerollPosition()||this.calculateTimeToFirstFrame(a||i.default.now(),t)};this.one("playing",r),this.one("adplaying",r),this.one("viewend",function(){this.off("playing",r),this.off("adplaying",r)})}})};n.prototype.calculateTimeToFirstFrame=function(e,t){t===this.data.view_id&&(this._updateWatchTime(null,{viewer_time:e}),this.data.view_time_to_first_frame=this.data.view_watch_time,(this.data.player_autoplay_on||this.data.video_is_autoplay)&&this.NAVIGATION_START&&(this.data.view_aggregate_startup_time=this.data.view_start+this.data.view_watch_time-this.NAVIGATION_START))},t.default=n},function(e,t,r){"use strict";function a(){var e=this;this.on("viewinit",function(){this._lastPlayheadPosition=-1});var t=["pause","rebufferstart","seeking","error","adbreakstart","hb"],r=["playing","hb"];t.forEach(function(t){e.on(t,function(){if(this._lastPlayheadPosition>=0&&this.data.player_playhead_time>=0&&this._lastPlayerWidth>=0&&this._lastSourceWidth>0&&this._lastPlayerHeight>=0&&this._lastSourceHeight>0){var e=this.data.player_playhead_time-this._lastPlayheadPosition;if(e<0)return void(this._lastPlayheadPosition=-1);var t=Math.min(this._lastPlayerWidth/this._lastSourceWidth,this._lastPlayerHeight/this._lastSourceHeight),r=Math.max(0,t-1),a=Math.max(0,1-t);this.data.view_max_upscale_percentage=Math.max(this.data.view_max_upscale_percentage||0,r),this.data.view_max_downscale_percentage=Math.max(this.data.view_max_downscale_percentage||0,a),(0,n.default)(this.data,"view_total_content_playback_time",e),(0,n.default)(this.data,"view_total_upscaling",r*e),(0,n.default)(this.data,"view_total_downscaling",a*e)}this._lastPlayheadPosition=-1})}),r.forEach(function(t){e.on(t,function(){this._lastPlayheadPosition=this.data.player_playhead_time,this._lastPlayerWidth=this.data.player_width,this._lastPlayerHeight=this.data.player_height,this._lastSourceWidth=this.data.video_source_width,this._lastSourceHeight=this.data.video_source_height})})}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a;var i=r(2),n=function(e){return e&&e.__esModule?e:{default:e}}(i)},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}function i(){this.isSeeking=!1,this.on("seeking",function(e,t){(0,d.default)(this.data,t),this._lastSeekingTime=o.default.now(),!1===this.isSeeking&&(this.isSeeking=!0,this.send("seeking"))}),this.on("seeked",function(){this.isSeeking=!1;var e=this._lastSeekingTime||o.default.now(),t=o.default.now()-e;(0,u.default)(this.data,"view_seek_count",1),(0,u.default)(this.data,"view_seek_duration",t);var r=this.data.view_max_seek_time||0;this.data.view_max_seek_time=Math.max(r,t)}),this.on("viewend",function(){this.isSeeking=!1})}Object.defineProperty(t,"__esModule",{value:!0}),t.default=i;var n=r(3),o=a(n),s=r(2),u=a(s),l=r(1),d=a(l)},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var i=function(){function e(e,t){var r=[],a=!0,i=!1,n=void 0;try{for(var o,s=e[Symbol.iterator]();!(a=(o=s.next()).done)&&(r.push(o.value),!t||r.length!==t);a=!0);}catch(e){i=!0,n=e}finally{try{!a&&s.return&&s.return()}finally{if(i)throw n}}return r}return function(t,r){if(Array.isArray(t))return t;if(Symbol.iterator in Object(t))return e(t,r);throw new TypeError("Invalid attempt to destructure non-iterable instance")}}(),n=r(2),o=a(n),s=r(5),u=r(1),l=a(u),d=function(e,t){e.push(t),e.sort(function(e,t){return e.viewer_time-t.viewer_time})},c=["adbreakstart","adrequest","adresponse","adplay","adplaying","adpause","adended","adbreakend","aderror"],f=function(){var e=this;this.on("viewinit",function(){this.isAdBreak=!1,this._currentAdRequestNumber=0,this._currentAdResponseNumber=0,this._adRequests=[],this._adResponses=[],this._adHasPlayed=!1,this._wouldBeNewAdPlay=!0,this._prerollPlayTime=void 0}),c.forEach(function(t){return e.on(t,e._updateAdData)});var t=function(){e.isAdBreak=!1};this.on("adbreakstart",function(){this.isAdBreak=!0}),this.on("play",t),this.on("playing",t),this.on("viewend",t),this.on("adrequest",function(e,t){t=(0,l.default)({ad_request_id:"generatedAdRequestId"+this._currentAdRequestNumber++},t),d(this._adRequests,t),(0,o.default)(this.data,"view_ad_request_count"),this.inPrerollPosition()&&(this.data.view_preroll_requested=!0,this._adHasPlayed||(0,o.default)(this.data,"view_preroll_request_count"))}),this.on("adresponse",function(e,t){t=(0,l.default)({ad_request_id:"generatedAdRequestId"+this._currentAdResponseNumber++},t),d(this._adResponses,t);var r=this.findAdRequest(t.ad_request_id);r&&(0,o.default)(this.data,"view_ad_request_time",Math.max(0,t.viewer_time-r.viewer_time))}),this.on("adplay",function(e,t){this._adHasPlayed=!0,this._wouldBeNewAdPlay&&(this._wouldBeNewAdPlay=!1,(0,o.default)(this.data,"view_ad_played_count")),this.inPrerollPosition()&&!this.data.view_preroll_played&&(this.data.view_preroll_played=!0,this._adRequests.length>0&&(this.data.view_preroll_request_time=Math.max(0,t.viewer_time-this._adRequests[0].viewer_time)),this.data.view_start&&(this.data.view_startup_preroll_request_time=Math.max(0,t.viewer_time-this.data.view_start)),this._prerollPlayTime=t.viewer_time)}),this.on("adplaying",function(e,t){this.inPrerollPosition()&&void 0===this.data.view_preroll_load_time&&void 0!==this._prerollPlayTime&&(this.data.view_preroll_load_time=t.viewer_time-this._prerollPlayTime,this.data.view_startup_preroll_load_time=t.viewer_time-this._prerollPlayTime)}),this.on("adended",function(){this._wouldBeNewAdPlay=!0}),this.on("aderror",function(){this._wouldBeNewAdPlay=!0})};f.prototype.inPrerollPosition=function(){return void 0===this.data.view_content_playback_time||this.data.view_content_playback_time<=1e3},f.prototype.findAdRequest=function(e){for(var t=0;t<this._adRequests.length;t++)if(this._adRequests[t].ad_request_id===e)return this._adRequests[t]},f.prototype._updateAdData=function(e,t){if(this.inPrerollPosition()){if(!this.data.view_preroll_ad_tag_hostname&&t.ad_tag_url){var r=(0,s.extractHostnameAndDomain)(t.ad_tag_url),a=i(r,2),n=a[0],o=a[1];this.data.view_preroll_ad_tag_domain=o,this.data.view_preroll_ad_tag_hostname=n}if(!this.data.view_preroll_ad_asset_hostname&&t.ad_asset_url){var u=(0,s.extractHostnameAndDomain)(t.ad_asset_url),l=i(u,2),d=l[0],c=l[1];this.data.view_preroll_ad_asset_domain=c,this.data.view_preroll_ad_asset_hostname=d}}},t.default=f},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}function i(){var e=this,t=void 0,r=void 0,a=function(){e.disableRebufferTracking||((0,u.default)(e.data,"view_waiting_rebuffer_count",1),t=o.default.now(),r=window.setInterval(function(){if(t){var r=o.default.now();(0,u.default)(e.data,"view_waiting_rebuffer_duration",r-t),t=r}},250))},i=function(){e.disableRebufferTracking||t&&((0,u.default)(e.data,"view_waiting_rebuffer_duration",o.default.now()-t),t=!1,window.clearInterval(r))},n=!1,s=function(){n=!0},l=function(){n=!1,i()};this.on("waiting",function(){n&&a()}),this.on("playing",function(){i(),s()}),this.on("pause",l),this.on("seeking",l)}Object.defineProperty(t,"__esModule",{value:!0}),t.default=i;var n=r(3),o=a(n),s=r(2),u=a(s)},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}function i(){var e=this;this.one("playbackheartbeat",l),this.on("playbackheartbeatend",function(){e.off("before*",d),e.one("playbackheartbeat",l)})}Object.defineProperty(t,"__esModule",{value:!0}),t.default=i;var n=r(1),o=a(n),s=r(3),u=a(s),l=function(){this.lastWallClockTime=u.default.now(),this.on("before*",d)},d=function(e){var t=u.default.now(),r=this.lastWallClockTime;this.lastWallClockTime=t,t-r>3e4&&(this.emit("devicesleep",{viewer_time:r}),(0,o.default)(this.data,{viewer_time:r}),this.send("devicesleep"),this.emit("devicewake",{viewer_time:t}),(0,o.default)(this.data,{viewer_time:t}),this.send("devicewake"))}},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var i="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},n=r(0),o=a(n),s=r(41),u=r(4),l=a(u),d=r(45),c=a(d),f=r(10),p=a(f),h=r(46),_=a(h),v=r(17),m=a(v),y=r(47),b=a(y),g=r(49),w=a(g),x=r(1),S=a(x),T=["env_key","view_id","view_sequence_number","player_sequence_number","beacon_domain","player_playhead_time","viewer_time","mux_api_version","event","video_id","player_instance_id"],k=["viewstart","error","ended","viewend"],E=function(e,t){var r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{};this.mux=e,this.envKey=t,this.eventQueue=new b.default((0,c.default)(t,r.beaconDomain)),this.previousBeaconData=null,this.lastEventTime=null,this.sampleRate=r.sampleRate,this.disableCookies=r.disableCookies,this.respectDoNotTrack=r.respectDoNotTrack;var a=(0,S.default)(w.default,r.platform||{});this.pageLevelData={mux_api_version:this.mux.API_VERSION,mux_embed_version:this.mux.VERSION,viewer_application_name:a.name,viewer_application_version:a.version,viewer_application_engine:a.layout,viewer_device_name:a.product,viewer_device_category:"",viewer_device_manufacturer:a.manufacturer,viewer_os_family:a.os&&a.os.family,viewer_os_architecture:a.os&&a.os.architecture,viewer_os_version:a.os&&a.os.version};var i=(0,_.default)();i&&(this.pageLevelData=(0,S.default)(this.pageLevelData,{viewer_connection_type:i})),void 0!==o.default&&o.default.location&&o.default.location.href&&(this.pageLevelData.page_url=o.default.location.href),this.viewerData=this.disableCookies?{}:(0,s.getAndUpdateViewerData)()};E.prototype.send=function(e,t){if(e){if(this.respectDoNotTrack&&(0,p.default)())return l.default.info("Not sending `"+e+"` because Do Not Track is enabled");if(!t||"object"!==(void 0===t?"undefined":i(t)))return l.default.error("A data object was expected in send() but was not provided");var r=this.disableCookies?{}:(0,s.getAndUpdateSessionData)(),a={};(0,S.default)(a,this.pageLevelData),(0,S.default)(a,t),(0,S.default)(a,r),(0,S.default)(a,this.viewerData),a.event=e,a.env_key=this.envKey,a.user_id&&(a.viewer_user_id=a.user_id,delete a.user_id);var n=a.mux_sample_number>=this.sampleRate,o=this._deduplicateBeaconData(e,a),u=(0,m.default)(o);if(this.lastEventTime=this.mux.utils.now(),n)return l.default.info("Not sending event due to sample rate restriction",e,a,u);if(!this.envKey)return l.default.info("Not sending event due to missing environment key",e,a,u);if(!this.rateLimited)if(l.default.info("Sending event",e,a,u),this.rateLimited=!this.eventQueue.queueEvent(e,u),this.mux.WINDOW_UNLOADING&&"viewend"===e)this.eventQueue.destroy(!0);else if(k.indexOf(e)>=0&&this.eventQueue.flushEvents(),this.rateLimited)return a.event="eventrateexceeded",u=(0,m.default)(a),this.eventQueue.queueEvent(a.event,u),l.default.error("Beaconing disabled due to rate limit.")}},E.prototype.destroy=function(){this.eventQueue.destroy(!1)};var O=function(e,t,r,a){return!(!e||0!==t.indexOf("request_")||"request_response_headers"!==t&&"object"===(void 0===r?"undefined":i(r))&&"object"===(void 0===a?"undefined":i(a))&&Object.keys(r||{}).length===Object.keys(a||{}).length)},M=function(e,t){return"renditionchange"===e&&0===t.indexOf("video_source_")};E.prototype._deduplicateBeaconData=function(e,t){var r=this,a={},i=t.view_id;if(!i||"viewstart"===e||"viewend"===e||!this.previousBeaconData||this.mux.utils.now()-this.lastEventTime>=6e5)a=(0,S.default)({},t),i&&(this.previousBeaconData=a),i&&"viewend"===e&&(this.previousBeaconData=null);else{var n=0===e.indexOf("request");Object.keys(t).forEach(function(i){var o=t[i];(o!==r.previousBeaconData[i]||T.indexOf(i)>-1||O(n,i,o,r.previousBeaconData[i])||M(e,i))&&(a[i]=o,r.previousBeaconData[i]=o)})}return a},t.default=E},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0}),t.getAndUpdateSessionData=t.getAndUpdateViewerData=void 0;var i=r(14),n=a(i),o=r(44),s=a(o),u=r(7),l=r(3),d=a(l),c=function(){var e=void 0;try{e=n.default.parse(s.default.get("muxData")||"")}catch(t){e={}}return e},f=function(e){try{s.default.set("muxData",n.default.stringify(e),{expires:7300})}catch(e){}},p=function(){var e=c();return e.mux_viewer_id=e.mux_viewer_id||(0,u.generateUUID)(),e.msn=e.msn||Math.random(),f(e),{mux_viewer_id:e.mux_viewer_id,mux_sample_number:e.msn}},h=function(){var e=c(),t=d.default.now();return e.session_start&&(e.sst=e.session_start,delete e.session_start),e.session_id&&(e.sid=e.session_id,delete e.session_id),e.session_expires&&(e.sex=e.session_expires,delete e.session_expires),(!e.sex||e.sex<t)&&(e.sid=(0,u.generateUUID)(),e.sst=t),e.sex=t+15e5,f(e),{session_id:e.sid,session_start:e.sst,session_expires:e.sex}};t.getAndUpdateViewerData=p,t.getAndUpdateSessionData=h},function(e,t,r){"use strict";var a=r(15),i=r(16),n={brackets:function(e){return e+"[]"},indices:function(e,t){return e+"["+t+"]"},repeat:function(e){return e}},o=Date.prototype.toISOString,s={delimiter:"&",encode:!0,encoder:a.encode,encodeValuesOnly:!1,serializeDate:function(e){return o.call(e)},skipNulls:!1,strictNullHandling:!1},u=function e(t,r,i,n,o,u,l,d,c,f,p,h){var _=t;if("function"==typeof l)_=l(r,_);else if(_ instanceof Date)_=f(_);else if(null===_){if(n)return u&&!h?u(r,s.encoder):r;_=""}if("string"==typeof _||"number"==typeof _||"boolean"==typeof _||a.isBuffer(_))return u?[p(h?r:u(r,s.encoder))+"="+p(u(_,s.encoder))]:[p(r)+"="+p(String(_))];var v=[];if(void 0===_)return v;var m;if(Array.isArray(l))m=l;else{var y=Object.keys(_);m=d?y.sort(d):y}for(var b=0;b<m.length;++b){var g=m[b];o&&null===_[g]||(v=Array.isArray(_)?v.concat(e(_[g],i(r,g),i,n,o,u,l,d,c,f,p,h)):v.concat(e(_[g],r+(c?"."+g:"["+g+"]"),i,n,o,u,l,d,c,f,p,h)))}return v};e.exports=function(e,t){var r=e,o=t?a.assign({},t):{};if(null!==o.encoder&&void 0!==o.encoder&&"function"!=typeof o.encoder)throw new TypeError("Encoder has to be a function.");var l=void 0===o.delimiter?s.delimiter:o.delimiter,d="boolean"==typeof o.strictNullHandling?o.strictNullHandling:s.strictNullHandling,c="boolean"==typeof o.skipNulls?o.skipNulls:s.skipNulls,f="boolean"==typeof o.encode?o.encode:s.encode,p="function"==typeof o.encoder?o.encoder:s.encoder,h="function"==typeof o.sort?o.sort:null,_=void 0!==o.allowDots&&o.allowDots,v="function"==typeof o.serializeDate?o.serializeDate:s.serializeDate,m="boolean"==typeof o.encodeValuesOnly?o.encodeValuesOnly:s.encodeValuesOnly;if(void 0===o.format)o.format=i.default;else if(!Object.prototype.hasOwnProperty.call(i.formatters,o.format))throw new TypeError("Unknown format option provided.");var y,b,g=i.formatters[o.format];"function"==typeof o.filter?(b=o.filter,r=b("",r)):Array.isArray(o.filter)&&(b=o.filter,y=b);var w=[];if("object"!=typeof r||null===r)return"";var x;x=o.arrayFormat in n?o.arrayFormat:"indices"in o?o.indices?"indices":"repeat":"indices";var S=n[x];y||(y=Object.keys(r)),h&&y.sort(h);for(var T=0;T<y.length;++T){var k=y[T];c&&null===r[k]||(w=w.concat(u(r[k],k,S,d,c,f?p:null,b,h,_,v,g,m)))}var E=w.join(l),O=!0===o.addQueryPrefix?"?":"";return E.length>0?O+E:""}},function(e,t,r){"use strict";var a=r(15),i=Object.prototype.hasOwnProperty,n={allowDots:!1,allowPrototypes:!1,arrayLimit:20,decoder:a.decode,delimiter:"&",depth:5,parameterLimit:1e3,plainObjects:!1,strictNullHandling:!1},o=function(e,t){for(var r={},a=t.ignoreQueryPrefix?e.replace(/^\?/,""):e,o=t.parameterLimit===1/0?void 0:t.parameterLimit,s=a.split(t.delimiter,o),u=0;u<s.length;++u){var l,d,c=s[u],f=c.indexOf("]="),p=-1===f?c.indexOf("="):f+1;-1===p?(l=t.decoder(c,n.decoder),d=t.strictNullHandling?null:""):(l=t.decoder(c.slice(0,p),n.decoder),d=t.decoder(c.slice(p+1),n.decoder)),i.call(r,l)?r[l]=[].concat(r[l]).concat(d):r[l]=d}return r},s=function(e,t,r){for(var a=t,i=e.length-1;i>=0;--i){var n,o=e[i];if("[]"===o)n=[],n=n.concat(a);else{n=r.plainObjects?Object.create(null):{};var s="["===o.charAt(0)&&"]"===o.charAt(o.length-1)?o.slice(1,-1):o,u=parseInt(s,10);!isNaN(u)&&o!==s&&String(u)===s&&u>=0&&r.parseArrays&&u<=r.arrayLimit?(n=[],n[u]=a):n[s]=a}a=n}return a},u=function(e,t,r){if(e){var a=r.allowDots?e.replace(/\.([^.[]+)/g,"[$1]"):e,n=/(\[[^[\]]*])/,o=/(\[[^[\]]*])/g,u=n.exec(a),l=u?a.slice(0,u.index):a,d=[];if(l){if(!r.plainObjects&&i.call(Object.prototype,l)&&!r.allowPrototypes)return;d.push(l)}for(var c=0;null!==(u=o.exec(a))&&c<r.depth;){if(c+=1,!r.plainObjects&&i.call(Object.prototype,u[1].slice(1,-1))&&!r.allowPrototypes)return;d.push(u[1])}return u&&d.push("["+a.slice(u.index)+"]"),s(d,t,r)}};e.exports=function(e,t){var r=t?a.assign({},t):{};if(null!==r.decoder&&void 0!==r.decoder&&"function"!=typeof r.decoder)throw new TypeError("Decoder has to be a function.");if(r.ignoreQueryPrefix=!0===r.ignoreQueryPrefix,r.delimiter="string"==typeof r.delimiter||a.isRegExp(r.delimiter)?r.delimiter:n.delimiter,r.depth="number"==typeof r.depth?r.depth:n.depth,r.arrayLimit="number"==typeof r.arrayLimit?r.arrayLimit:n.arrayLimit,r.parseArrays=!1!==r.parseArrays,r.decoder="function"==typeof r.decoder?r.decoder:n.decoder,r.allowDots="boolean"==typeof r.allowDots?r.allowDots:n.allowDots,r.plainObjects="boolean"==typeof r.plainObjects?r.plainObjects:n.plainObjects,r.allowPrototypes="boolean"==typeof r.allowPrototypes?r.allowPrototypes:n.allowPrototypes,r.parameterLimit="number"==typeof r.parameterLimit?r.parameterLimit:n.parameterLimit,r.strictNullHandling="boolean"==typeof r.strictNullHandling?r.strictNullHandling:n.strictNullHandling,""===e||null===e||void 0===e)return r.plainObjects?Object.create(null):{};for(var i="string"==typeof e?o(e,r):e,s=r.plainObjects?Object.create(null):{},l=Object.keys(i),d=0;d<l.length;++d){var c=l[d],f=u(c,i[c],r);s=a.merge(s,f,r)}return a.compact(s)}},function(e,t,r){"use strict";var a,i,n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e};!function(o){var s=!1;if(a=o,void 0!==(i="function"==typeof a?a.call(t,r,t,e):a)&&(e.exports=i),s=!0,"object"===n(t)&&(e.exports=o(),s=!0),!s){var u=window.Cookies,l=window.Cookies=o();l.noConflict=function(){return window.Cookies=u,l}}}(function(){function e(r){function a(e,i,n){var o;if("undefined"!=typeof document){if(arguments.length>1){if(n=t({path:"/"},a.defaults,n),"number"==typeof n.expires){var s=new Date;s.setMilliseconds(s.getMilliseconds()+864e5*n.expires),n.expires=s}try{o=JSON.stringify(i),/^[\{\[]/.test(o)&&(i=o)}catch(e){}return i=r.write?r.write(i,e):encodeURIComponent(String(i)).replace(/%(23|24|26|2B|3A|3C|3E|3D|2F|3F|40|5B|5D|5E|60|7B|7D|7C)/g,decodeURIComponent),e=encodeURIComponent(String(e)),e=e.replace(/%(23|24|26|2B|5E|60|7C)/g,decodeURIComponent),e=e.replace(/[\(\)]/g,escape),document.cookie=[e,"=",i,n.expires?"; expires="+n.expires.toUTCString():"",n.path?"; path="+n.path:"",n.domain?"; domain="+n.domain:"",n.secure?"; secure":""].join("")}e||(o={});for(var u=document.cookie?document.cookie.split("; "):[],l=/(%[0-9A-Z]{2})+/g,d=0;d<u.length;d++){var c=u[d].split("="),f=c.slice(1).join("=");'"'===f.charAt(0)&&(f=f.slice(1,-1));try{var p=c[0].replace(l,decodeURIComponent);if(f=r.read?r.read(f,p):r(f,p)||f.replace(l,decodeURIComponent),this.json)try{f=JSON.parse(f)}catch(e){}if(e===p){o=f;break}e||(o[p]=f)}catch(e){}}return o}}return a.set=a,a.get=function(e){return a.call(a,e)},a.getJSON=function(){return a.apply({json:!0},[].slice.call(arguments))},a.defaults={},a.remove=function(e,r){a(e,"",t(r,{expires:-1}))},a.withConverter=e,a}var t=function(){for(var e=0,t={};e<arguments.length;e++){var r=arguments[e];for(var a in r)t[a]=r[a]}return t};return e(function(){})})},function(e,t,r){"use strict";function a(e,t){return e=e||"",t=t||"litix.io",e.match(/^[a-z0-9]+$/)?"https://"+e+"."+t:"https://img.litix.io/a.gif"}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=r(0),i=function(e){return e&&e.__esModule?e:{default:e}}(a),n=function(){var e=void 0;switch(o()){case"cellular":e="cellular";break;case"ethernet":e="ethernet";break;case"wifi":e="wifi";break;case void 0:break;default:e="other"}return e},o=function(){var e=i.default.navigator,t=e&&(e.connection||e.mozConnection||e.webkitConnection);return t&&t.type};t.default=n},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var i=r(0),n=a(i),o=r(4),s=a(o),u=r(48),l=a(u),d=r(1),c=a(d),f=r(17),p=a(f),h=!!n.default.XMLHttpRequest&&"withCredentials"in new n.default.XMLHttpRequest,_={maxBeaconSize:300,maxQueueLength:3600,baseTimeBetweenBeacons:5e3},v=function(e,t){this._beaconUrl=e||"https://img.litix.io",this._eventQueue=[],this._postInFlight=!1,this._failureCount=0,this._sendTimeout=!1,this._options=(0,c.default)({},_,t)};v.prototype.queueEvent=function(e,t){var r=(0,c.default)({},t);return h?(this._eventQueue.length<=this._options.maxQueueLength||"eventrateexceeded"===e)&&(this._eventQueue.push(r),this._sendTimeout||this._startBeaconSending(),this._eventQueue.length<=this._options.maxQueueLength):(l.default.send(this._beaconUrl,r),!0)},v.prototype.flushEvents=function(){h&&(this._eventQueue.length&&this._sendBeaconQueue(),this._startBeaconSending())},v.prototype.destroy=function(){var e=arguments.length>0&&void 0!==arguments[0]&&arguments[0];this.destroyed=!0,e?this._clearBeaconQueue():this.flushEvents(),n.default.clearTimeout(this._sendTimeout)},v.prototype._clearBeaconQueue=function(){var e=n.default.navigator,t=this._eventQueue.length>this._options.maxBeaconSize?this._eventQueue.length-this._options.maxBeaconSize:0,r=this._eventQueue.slice(t);if(t>0&&(0,c.default)(r[r.length-1],(0,p.default)({mux_view_message:"event queue truncated"})),e.sendBeacon)e.sendBeacon(this._beaconUrl,JSON.stringify({events:r}));else if(n.default.XMLHttpRequest){var a=new n.default.XMLHttpRequest;a.open("POST",this._beaconUrl),a.setRequestHeader("Content-Type","application/json"),a.send(JSON.stringify({events:r}))}else l.default.send(this._beaconUrl,r[r.length-1])},v.prototype._sendBeaconQueue=function(){var e=this;if(n.default.XMLHttpRequest&&!this._postInFlight){var t=new n.default.XMLHttpRequest,r=this._eventQueue.slice(0,this._options.maxBeaconSize);this._eventQueue=this._eventQueue.slice(this._options.maxBeaconSize),this._postInFlight=!0,t.onreadystatechange=function(){4===t.readyState&&(200!==t.status?(e._eventQueue=r.concat(e._eventQueue),e._failureCount+=1,s.default.info("Error sending beacon: "+t.status),s.default.info(t.responseText)):e._failureCount=0,e._postInFlight=!1)},t.open("POST",this._beaconUrl),t.setRequestHeader("Content-Type","application/json"),t.send(JSON.stringify({events:r}))}},v.prototype._getNextBeaconTime=function(){if(!this._failureCount)return this._options.baseTimeBetweenBeacons;var e=Math.pow(2,this._failureCount-1);return(1+(e*=Math.random()))*this._options.baseTimeBetweenBeacons},v.prototype._startBeaconSending=function(){var e=this;n.default.clearTimeout(this._sendTimeout),this.destroyed||(this._sendTimeout=n.default.setTimeout(function(){e._eventQueue.length&&e._sendBeaconQueue(),e._startBeaconSending()},this._getNextBeaconTime()))},t.default=v},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var i=r(14),n=a(i),o=r(0),s=a(o),u={};u.send=function(e,t){function r(){a.src=o+(i?"&rc="+i:"")}var a=new Image,i=0,o=e+"?"+n.default.stringify(t);return a.addEventListener("error",function(){i>3||s.default.setTimeout(function(){i++,r()},5e3*i)}),r(),a},t.default=u},function(e,t,r){(function(e,a){var i;(function(){"use strict";function n(e){return e=String(e),e.charAt(0).toUpperCase()+e.slice(1)}function o(e,t,r){var a={"10.0":"10",6.4:"10 Technical Preview",6.3:"8.1",6.2:"8",6.1:"Server 2008 R2 / 7","6.0":"Server 2008 / Vista",5.2:"Server 2003 / XP 64-bit",5.1:"XP",5.01:"2000 SP1","5.0":"2000","4.0":"NT","4.90":"ME"};return t&&r&&/^Win/i.test(e)&&!/^Windows Phone /i.test(e)&&(a=a[/[\d.]+$/.exec(e)])&&(e="Windows "+a),e=String(e),t&&r&&(e=e.replace(RegExp(t,"i"),r)),e=u(e.replace(/ ce$/i," CE").replace(/\bhpw/i,"web").replace(/\bMacintosh\b/,"Mac OS").replace(/_PowerPC\b/i," OS").replace(/\b(OS X) [^ \d]+/i,"$1").replace(/\bMac (OS X)\b/,"$1").replace(/\/(\d)/," $1").replace(/_/g,".").replace(/(?: BePC|[ .]*fc[ \d.]+)$/i,"").replace(/\bx86\.64\b/gi,"x86_64").replace(/\b(Windows Phone) OS\b/,"$1").replace(/\b(Chrome OS \w+) [\d.]+\b/,"$1").split(" on ")[0])}function s(e,t){var r=-1,a=e?e.length:0;if("number"==typeof a&&a>-1&&a<=x)for(;++r<a;)t(e[r],r,e);else l(e,t)}function u(e){return e=h(e),/^(?:webOS|i(?:OS|P))/.test(e)?e:n(e)}function l(e,t){for(var r in e)E.call(e,r)&&t(e[r],r,e)}function d(e){return null==e?n(e):O.call(e).slice(8,-1)}function c(e,t){var r=null!=e?typeof e[t]:"number";return!(/^(?:boolean|number|string|undefined)$/.test(r)||"object"==r&&!e[t])}function f(e){return String(e).replace(/([ -])(?!$)/g,"$1?")}function p(e,t){var r=null;return s(e,function(a,i){r=t(r,a,i,e)}),r}function h(e){return String(e).replace(/^ +| +$/g,"")}function _(e){function t(t){return p(t,function(t,r){var a=r.pattern||f(r);return!t&&(t=RegExp("\\b"+a+" *\\d+[.\\w_]*","i").exec(e)||RegExp("\\b"+a+" *\\w+-[\\w]*","i").exec(e)||RegExp("\\b"+a+"(?:; *(?:[a-z]+[_-])?[a-z]+\\d+|[^ ();-]*)","i").exec(e))&&((t=String(r.label&&!RegExp(a,"i").test(r.label)?r.label:t).split("/"))[1]&&!/[\d.]+/.test(t[0])&&(t[0]+=" "+t[1]),r=r.label||r,t=u(t[0].replace(RegExp(a,"i"),r).replace(RegExp("; *(?:"+r+"[_-])?","i")," ").replace(RegExp("("+r+")[-_.]?(\\w)","i"),"$1 $2"))),t})}function r(){return this.description||""}var a=m,i=e&&"object"==typeof e&&"String"!=d(e);i&&(a=e,e=null);var n=a.navigator||{},s=n.userAgent||"";e||(e=s);var v,b,g=i||T==y,w=i?!!n.likeChrome:/\bChrome\b/.test(e)&&!/internal|\n/i.test(O.toString()),x=i?"Object":"ScriptBridgingProxyObject",k=i?"Object":"Environment",E=i&&a.java?"JavaPackage":d(a.java),M=i?"Object":"RuntimeObject",P=/\bJava/.test(E)&&a.java,A=P&&d(a.environment)==k,q=P?"a":"α",D=P?"b":"β",R=a.document||{},j=a.operamini||a.opera,I=S.test(I=i&&j?j["[[Class]]"]:d(j))?I:j=null,L=e,C=[],H=null,N=e==s,B=N&&j&&"function"==typeof j.version&&j.version(),F=function(t){return p(t,function(t,r){return t||RegExp("\\b"+(r.pattern||f(r))+"\\b","i").exec(e)&&(r.label||r)})}([{label:"EdgeHTML",pattern:"Edge"},"Trident",{label:"WebKit",pattern:"AppleWebKit"},"iCab","Presto","NetFront","Tasman","KHTML","Gecko"]),W=function(t){return p(t,function(t,r){return t||RegExp("\\b"+(r.pattern||f(r))+"\\b","i").exec(e)&&(r.label||r)})}(["Adobe AIR","Arora","Avant Browser","Breach","Camino","Electron","Epiphany","Fennec","Flock","Galeon","GreenBrowser","iCab","Iceweasel","K-Meleon","Konqueror","Lunascape","Maxthon",{label:"Microsoft Edge",pattern:"Edge"},"Midori","Nook Browser","PaleMoon","PhantomJS","Raven","Rekonq","RockMelt",{label:"Samsung Internet",pattern:"SamsungBrowser"},"SeaMonkey",{label:"Silk",pattern:"(?:Cloud9|Silk-Accelerated)"},"Sleipnir","SlimBrowser",{label:"SRWare Iron",pattern:"Iron"},"Sunrise","Swiftfox","Waterfox","WebPositive","Opera Mini",{label:"Opera Mini",pattern:"OPiOS"},"Opera",{label:"Opera",pattern:"OPR"},"Chrome",{label:"Chrome Mobile",pattern:"(?:CriOS|CrMo)"},{label:"Firefox",pattern:"(?:Firefox|Minefield)"},{label:"Firefox for iOS",pattern:"FxiOS"},{label:"IE",pattern:"IEMobile"},{label:"IE",pattern:"MSIE"},"Safari"]),U=t([{label:"BlackBerry",pattern:"BB10"},"BlackBerry",{label:"Galaxy S",pattern:"GT-I9000"},{label:"Galaxy S2",pattern:"GT-I9100"},{label:"Galaxy S3",pattern:"GT-I9300"},{label:"Galaxy S4",pattern:"GT-I9500"},{label:"Galaxy S5",pattern:"SM-G900"},{label:"Galaxy S6",pattern:"SM-G920"},{label:"Galaxy S6 Edge",pattern:"SM-G925"},{label:"Galaxy S7",pattern:"SM-G930"},{label:"Galaxy S7 Edge",pattern:"SM-G935"},"Google TV","Lumia","iPad","iPod","iPhone","Kindle",{label:"Kindle Fire",pattern:"(?:Cloud9|Silk-Accelerated)"},"Nexus","Nook","PlayBook","PlayStation Vita","PlayStation","TouchPad","Transformer",{label:"Wii U",pattern:"WiiU"},"Wii","Xbox One",{label:"Xbox 360",pattern:"Xbox"},"Xoom"]),G=function(t){return p(t,function(t,r,a){return t||(r[U]||r[/^[a-z]+(?: +[a-z]+\b)*/i.exec(U)]||RegExp("\\b"+f(a)+"(?:\\b|\\w*\\d)","i").exec(e))&&a})}({Apple:{iPad:1,iPhone:1,iPod:1},Archos:{},Amazon:{Kindle:1,"Kindle Fire":1},Asus:{Transformer:1},"Barnes & Noble":{Nook:1},BlackBerry:{PlayBook:1},Google:{"Google TV":1,Nexus:1},HP:{TouchPad:1},HTC:{},LG:{},Microsoft:{Xbox:1,"Xbox One":1},Motorola:{Xoom:1},Nintendo:{"Wii U":1,Wii:1},Nokia:{Lumia:1},Samsung:{"Galaxy S":1,"Galaxy S2":1,"Galaxy S3":1,"Galaxy S4":1},Sony:{PlayStation:1,"PlayStation Vita":1}}),V=function(t){return p(t,function(t,r){var a=r.pattern||f(r);return!t&&(t=RegExp("\\b"+a+"(?:/[\\d.]+|[ \\w.]*)","i").exec(e))&&(t=o(t,a,r.label||r)),t})}(["Windows Phone","Android","CentOS",{label:"Chrome OS",pattern:"CrOS"},"Debian","Fedora","FreeBSD","Gentoo","Haiku","Kubuntu","Linux Mint","OpenBSD","Red Hat","SuSE","Ubuntu","Xubuntu","Cygwin","Symbian OS","hpwOS","webOS ","webOS","Tablet OS","Tizen","Linux","Mac OS X","Macintosh","Mac","Windows 98;","Windows "]);if(F&&(F=[F]),G&&!U&&(U=t([G])),(v=/\bGoogle TV\b/.exec(U))&&(U=v[0]),/\bSimulator\b/i.test(e)&&(U=(U?U+" ":"")+"Simulator"),"Opera Mini"==W&&/\bOPiOS\b/.test(e)&&C.push("running in Turbo/Uncompressed mode"),"IE"==W&&/\blike iPhone OS\b/.test(e)?(v=_(e.replace(/like iPhone OS/,"")),G=v.manufacturer,U=v.product):/^iP/.test(U)?(W||(W="Safari"),V="iOS"+((v=/ OS ([\d_]+)/i.exec(e))?" "+v[1].replace(/_/g,"."):"")):"Konqueror"!=W||/buntu/i.test(V)?G&&"Google"!=G&&(/Chrome/.test(W)&&!/\bMobile Safari\b/i.test(e)||/\bVita\b/.test(U))||/\bAndroid\b/.test(V)&&/^Chrome/.test(W)&&/\bVersion\//i.test(e)?(W="Android Browser",V=/\bAndroid\b/.test(V)?V:"Android"):"Silk"==W?(/\bMobi/i.test(e)||(V="Android",C.unshift("desktop mode")),/Accelerated *= *true/i.test(e)&&C.unshift("accelerated")):"PaleMoon"==W&&(v=/\bFirefox\/([\d.]+)\b/.exec(e))?C.push("identifying as Firefox "+v[1]):"Firefox"==W&&(v=/\b(Mobile|Tablet|TV)\b/i.exec(e))?(V||(V="Firefox OS"),U||(U=v[1])):!W||(v=!/\bMinefield\b/i.test(e)&&/\b(?:Firefox|Safari)\b/.exec(W))?(W&&!U&&/[\/,]|^[^(]+?\)/.test(e.slice(e.indexOf(v+"/")+8))&&(W=null),(v=U||G||V)&&(U||G||/\b(?:Android|Symbian OS|Tablet OS|webOS)\b/.test(V))&&(W=/[a-z]+(?: Hat)?/i.exec(/\bAndroid\b/.test(V)?V:v)+" Browser")):"Electron"==W&&(v=(/\bChrome\/([\d.]+)\b/.exec(e)||0)[1])&&C.push("Chromium "+v):V="Kubuntu",B||(B=function(t){return p(t,function(t,r){return t||(RegExp(r+"(?:-[\\d.]+/|(?: for [\\w-]+)?[ /-])([\\d.]+[^ ();/_-]*)","i").exec(e)||0)[1]||null})}(["(?:Cloud9|CriOS|CrMo|Edge|FxiOS|IEMobile|Iron|Opera ?Mini|OPiOS|OPR|Raven|SamsungBrowser|Silk(?!/[\\d.]+$))","Version",f(W),"(?:Firefox|Minefield|NetFront)"])),(v="iCab"==F&&parseFloat(B)>3&&"WebKit"||/\bOpera\b/.test(W)&&(/\bOPR\b/.test(e)?"Blink":"Presto")||/\b(?:Midori|Nook|Safari)\b/i.test(e)&&!/^(?:Trident|EdgeHTML)$/.test(F)&&"WebKit"||!F&&/\bMSIE\b/i.test(e)&&("Mac OS"==V?"Tasman":"Trident")||"WebKit"==F&&/\bPlayStation\b(?! Vita\b)/i.test(W)&&"NetFront")&&(F=[v]),"IE"==W&&(v=(/; *(?:XBLWP|ZuneWP)(\d+)/i.exec(e)||0)[1])?(W+=" Mobile",V="Windows Phone "+(/\+$/.test(v)?v:v+".x"),C.unshift("desktop mode")):/\bWPDesktop\b/i.test(e)?(W="IE Mobile",V="Windows Phone 8.x",C.unshift("desktop mode"),B||(B=(/\brv:([\d.]+)/.exec(e)||0)[1])):"IE"!=W&&"Trident"==F&&(v=/\brv:([\d.]+)/.exec(e))&&(W&&C.push("identifying as "+W+(B?" "+B:"")),W="IE",B=v[1]),N){if(c(a,"global"))if(P&&(v=P.lang.System,L=v.getProperty("os.arch"),V=V||v.getProperty("os.name")+" "+v.getProperty("os.version")),g&&c(a,"system")&&(v=[a.system])[0]){V||(V=v[0].os||null);try{v[1]=a.require("ringo/engine").version,B=v[1].join("."),W="RingoJS"}catch(e){v[0].global.system==a.system&&(W="Narwhal")}}else"object"==typeof a.process&&!a.process.browser&&(v=a.process)?"object"==typeof v.versions?"string"==typeof v.versions.electron?(C.push("Node "+v.versions.node),W="Electron",B=v.versions.electron):"string"==typeof v.versions.nw&&(C.push("Chromium "+B,"Node "+v.versions.node),W="NW.js",B=v.versions.nw):(W="Node.js",L=v.arch,V=v.platform,B=/[\d.]+/.exec(v.version),B=B?B[0]:"unknown"):A&&(W="Rhino");else d(v=a.runtime)==x?(W="Adobe AIR",V=v.flash.system.Capabilities.os):d(v=a.phantom)==M?(W="PhantomJS",B=(v=v.version||null)&&v.major+"."+v.minor+"."+v.patch):"number"==typeof R.documentMode&&(v=/\bTrident\/(\d+)/i.exec(e))?(B=[B,R.documentMode],(v=+v[1]+4)!=B[1]&&(C.push("IE "+B[1]+" mode"),F&&(F[1]=""),B[1]=v),B="IE"==W?String(B[1].toFixed(1)):B[0]):"number"==typeof R.documentMode&&/^(?:Chrome|Firefox)\b/.test(W)&&(C.push("masking as "+W+" "+B),W="IE",B="11.0",F=["Trident"],V="Windows");V=V&&u(V)}if(B&&(v=/(?:[ab]|dp|pre|[ab]\d+pre)(?:\d+\+?)?$/i.exec(B)||/(?:alpha|beta)(?: ?\d)?/i.exec(e+";"+(N&&n.appMinorVersion))||/\bMinefield\b/i.test(e)&&"a")&&(H=/b/i.test(v)?"beta":"alpha",B=B.replace(RegExp(v+"\\+?$"),"")+("beta"==H?D:q)+(/\d+\+?/.exec(v)||"")),"Fennec"==W||"Firefox"==W&&/\b(?:Android|Firefox OS)\b/.test(V))W="Firefox Mobile";else if("Maxthon"==W&&B)B=B.replace(/\.[\d.]+/,".x");else if(/\bXbox\b/i.test(U))"Xbox 360"==U&&(V=null),"Xbox 360"==U&&/\bIEMobile\b/.test(e)&&C.unshift("mobile mode");else if(!/^(?:Chrome|IE|Opera)$/.test(W)&&(!W||U||/Browser|Mobi/.test(W))||"Windows CE"!=V&&!/Mobi/i.test(e))if("IE"==W&&N)try{null===a.external&&C.unshift("platform preview")}catch(e){C.unshift("embedded")}else(/\bBlackBerry\b/.test(U)||/\bBB10\b/.test(e))&&(v=(RegExp(U.replace(/ +/g," *")+"/([.\\d]+)","i").exec(e)||0)[1]||B)?(v=[v,/BB10/.test(e)],V=(v[1]?(U=null,G="BlackBerry"):"Device Software")+" "+v[0],B=null):this!=l&&"Wii"!=U&&(N&&j||/Opera/.test(W)&&/\b(?:MSIE|Firefox)\b/i.test(e)||"Firefox"==W&&/\bOS X (?:\d+\.){2,}/.test(V)||"IE"==W&&(V&&!/^Win/.test(V)&&B>5.5||/\bWindows XP\b/.test(V)&&B>8||8==B&&!/\bTrident\b/.test(e)))&&!S.test(v=_.call(l,e.replace(S,"")+";"))&&v.name&&(v="ing as "+v.name+((v=v.version)?" "+v:""),S.test(W)?(/\bIE\b/.test(v)&&"Mac OS"==V&&(V=null),v="identify"+v):(v="mask"+v,W=I?u(I.replace(/([a-z])([A-Z])/g,"$1 $2")):"Opera",/\bIE\b/.test(v)&&(V=null),N||(B=null)),F=["Presto"],C.push(v));else W+=" Mobile";(v=(/\bAppleWebKit\/([\d.]+\+?)/i.exec(e)||0)[1])&&(v=[parseFloat(v.replace(/\.(\d)$/,".0$1")),v],"Safari"==W&&"+"==v[1].slice(-1)?(W="WebKit Nightly",H="alpha",B=v[1].slice(0,-1)):B!=v[1]&&B!=(v[2]=(/\bSafari\/([\d.]+\+?)/i.exec(e)||0)[1])||(B=null),v[1]=(/\bChrome\/([\d.]+)/i.exec(e)||0)[1],537.36==v[0]&&537.36==v[2]&&parseFloat(v[1])>=28&&"WebKit"==F&&(F=["Blink"]),N&&(w||v[1])?(F&&(F[1]="like Chrome"),v=v[1]||(v=v[0],v<530?1:v<532?2:v<532.05?3:v<533?4:v<534.03?5:v<534.07?6:v<534.1?7:v<534.13?8:v<534.16?9:v<534.24?10:v<534.3?11:v<535.01?12:v<535.02?"13+":v<535.07?15:v<535.11?16:v<535.19?17:v<536.05?18:v<536.1?19:v<537.01?20:v<537.11?"21+":v<537.13?23:v<537.18?24:v<537.24?25:v<537.36?26:"Blink"!=F?"27":"28")):(F&&(F[1]="like Safari"),v=v[0],v=v<400?1:v<500?2:v<526?3:v<533?4:v<534?"4+":v<535?5:v<537?6:v<538?7:v<601?8:"8"),F&&(F[1]+=" "+(v+="number"==typeof v?".x":/[.+]/.test(v)?"":"+")),"Safari"==W&&(!B||parseInt(B)>45)&&(B=v)),"Opera"==W&&(v=/\bzbov|zvav$/.exec(V))?(W+=" ",C.unshift("desktop mode"),"zvav"==v?(W+="Mini",B=null):W+="Mobile",V=V.replace(RegExp(" *"+v+"$"),"")):"Safari"==W&&/\bChrome\b/.exec(F&&F[1])&&(C.unshift("desktop mode"),W="Chrome Mobile",B=null,/\bOS X\b/.test(V)?(G="Apple",V="iOS 4.3+"):V=null),B&&0==B.indexOf(v=/[\d.]+$/.exec(V))&&e.indexOf("/"+v+"-")>-1&&(V=h(V.replace(v,""))),F&&!/\b(?:Avant|Nook)\b/.test(W)&&(/Browser|Lunascape|Maxthon/.test(W)||"Safari"!=W&&/^iOS/.test(V)&&/\bSafari\b/.test(F[1])||/^(?:Adobe|Arora|Breach|Midori|Opera|Phantom|Rekonq|Rock|Samsung Internet|Sleipnir|Web)/.test(W)&&F[1])&&(v=F[F.length-1])&&C.push(v),C.length&&(C=["("+C.join("; ")+")"]),G&&U&&U.indexOf(G)<0&&C.push("on "+G),U&&C.push((/^on /.test(C[C.length-1])?"":"on ")+U),V&&(v=/ ([\d.+]+)$/.exec(V),b=v&&"/"==V.charAt(V.length-v[0].length-1),V={architecture:32,family:v&&!b?V.replace(v[0],""):V,version:v?v[1]:null,toString:function(){var e=this.version;return this.family+(e&&!b?" "+e:"")+(64==this.architecture?" 64-bit":"")}}),(v=/\b(?:AMD|IA|Win|WOW|x86_|x)64\b/i.exec(L))&&!/\bi686\b/i.test(L)?(V&&(V.architecture=64,V.family=V.family.replace(RegExp(" *"+v),"")),W&&(/\bWOW64\b/i.test(e)||N&&/\w(?:86|32)$/.test(n.cpuClass||n.platform)&&!/\bWin64; x64\b/i.test(e))&&C.unshift("32-bit")):V&&/^OS X/.test(V.family)&&"Chrome"==W&&parseFloat(B)>=39&&(V.architecture=64),e||(e=null);var J={};return J.description=e,J.layout=F&&F[0],J.manufacturer=G,J.name=W,J.prerelease=H,J.product=U,J.ua=e,J.version=W&&B,J.os=V||{architecture:null,family:null,version:null,toString:function(){return"null"}},J.parse=_,J.toString=r,J.version&&C.unshift(B),J.name&&C.unshift(W),V&&W&&(V!=String(V).split(" ")[0]||V!=W.split(" ")[0]&&!U)&&C.push(U?"("+V+")":"on "+V),C.length&&(J.description=C.join(" ")),J}var v={function:!0,object:!0},m=v[typeof window]&&window||this,y=m,b=v[typeof t]&&t,g=v[typeof e]&&e&&!e.nodeType&&e,w=b&&g&&"object"==typeof a&&a;!w||w.global!==w&&w.window!==w&&w.self!==w||(m=w);var x=Math.pow(2,53)-1,S=/\bOpera/,T=this,k=Object.prototype,E=k.hasOwnProperty,O=k.toString,M=_();m.platform=M,void 0!==(i=function(){return M}.call(t,r,t,e))&&(e.exports=i)}).call(this)}).call(t,r(13)(e),r(6))},function(e,t,r){"use strict";function a(){function e(e,t){var r=t.request_start,u=t.request_response_start,l=t.request_response_end,d=t.request_bytes_loaded;o++;var c=void 0,f=void 0;if(u?(c=u-r,f=l-u):f=l-r,f>0&&d>0){var p=d/f*8e3;s++,i+=d,n+=f,this.data.view_min_request_throughput=Math.min(this.data.view_min_request_throughput||1/0,p),this.data.view_average_request_throughput=i/n*8e3,this.data.view_request_count=o,c>0&&(a+=c,this.data.view_max_request_latency=Math.max(this.data.view_max_request_latency||0,c),this.data.view_average_request_latency=a/s)}}function t(e,t){o++,u++,this.data.view_request_count=o,this.data.view_request_failed_count=u}function r(e,t){o++,l++,this.data.view_request_count=o,this.data.view_request_canceled_count=l}var a=0,i=0,n=0,o=0,s=0,u=0,l=0;this.on("requestcompleted",e),this.on("requestfailed",t),this.on("requestcanceled",r)}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a},function(e,t,r){"use strict";function a(e,t,r){var a=(0,u.findMediaElement)(t),o=n(a,3),c=o[0],f=o[1],p=o[2],h=e.log,_=e.utils.getComputedStyle,v=e.utils.secondsToMs,m={automaticErrorTracking:!0};return c?"video"!==p&&"audio"!==p?h.error("The element of `"+f+"` was not a media element."):(r=(0,s.default)(m,r),r.data=(0,s.default)({player_software:"HTML5 Video Element",player_software_version:"No Versions",player_mux_plugin_name:"VideoElementMonitor",player_mux_plugin_version:"3.4.3"},r.data),r.getPlayheadTime=function(){return v(c.currentTime)},r.getStateData=function(){var e=this.hlsjs&&this.hlsjs.url,t=this.dashjs&&i("function"===this.dashjs.getSource)&&this.dashjs.getSource();return{player_is_paused:c.paused,player_playhead_time:v(c.currentTime),player_width:parseInt(_(c,"width")),player_height:parseInt(_(c,"height")),player_autoplay_on:c.autoplay,player_preload_on:c.preload,video_poster_url:c.poster,video_source_url:e||t||c.currentSrc,video_source_duration:v(c.duration),video_source_height:c.videoHeight,video_source_width:c.videoWidth}},c.mux=c.mux||{},c.mux.emit=function(t,r){e.emit(f,t,r)},c.mux.destroy=function(){Object.keys(c.mux.listeners).forEach(function(e){c.removeEventListener(e,c.mux.listeners[e],!1)}),delete c.mux.listeners,e.emit(f,"destroy")},c.mux.swapElement=function(t){var r=(0,u.findMediaElement)(t),a=n(r,3),i=a[0],o=a[1],l=a[2];return i?"video"!==l&&"audio"!==l?e.log.error("The element of `"+o+"` was not a media element."):(i.muxId=c.muxId,delete c.muxId,i.mux=i.mux||{},i.mux.listeners=(0,s.default)({},c.mux.listeners),delete c.mux.listeners,Object.keys(i.mux.listeners).forEach(function(e){c.removeEventListener(e,i.mux.listeners[e],!1),i.addEventListener(e,i.mux.listeners[e],!1)}),i.mux.swapElement=c.mux.swapElement,i.mux.destroy=c.mux.destroy,delete c.mux,void(c=i)):e.log.error("No element was found with the `"+o+"` query selector.")},c.mux.addHLSJS=function(t){e.addHLSJS(f,t)},c.mux.addDashJS=function(t){e.addDashJS(f,t)},c.mux.removeHLSJS=function(){e.removeHLSJS(f)},c.mux.removeDashJS=function(){e.removeDashJS(f)},e.init(f,r),e.emit(f,"playerready"),c.paused||(e.emit(f,"play"),c.readyState>2&&e.emit(f,"playing")),c.mux.listeners={},void l.forEach(function(t){("error"!==t||r.automaticErrorTracking)&&(c.mux.listeners[t]=function(){var r={};"error"===t&&(r.player_error_code=c.error&&c.error.code,r.player_error_message=c.error&&d[c.error.code]),e.emit(f,t,r)},c.addEventListener(t,c.mux.listeners[t],!1))})):h.error("No element was found with the `"+f+"` query selector.")}Object.defineProperty(t,"__esModule",{value:!0});var i="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e},n=function(){function e(e,t){var r=[],a=!0,i=!1,n=void 0;try{for(var o,s=e[Symbol.iterator]();!(a=(o=s.next()).done)&&(r.push(o.value),!t||r.length!==t);a=!0);}catch(e){i=!0,n=e}finally{try{!a&&s.return&&s.return()}finally{if(i)throw n}}return r}return function(t,r){if(Array.isArray(t))return t;if(Symbol.iterator in Object(t))return e(t,r);throw new TypeError("Invalid attempt to destructure non-iterable instance")}}();t.default=a;var o=r(1),s=function(e){return e&&e.__esModule?e:{default:e}}(o),u=r(9),l=["loadstart","pause","play","playing","seeking","seeked","timeupdate","ratechange","stalled","waiting","error","ended"],d={1:"MEDIA_ERR_ABORTED",2:"MEDIA_ERR_NETWORK",3:"MEDIA_ERR_DECODE",4:"MEDIA_ERR_SRC_NOT_SUPPORTED"}},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}Object.defineProperty(t,"__esModule",{value:!0});var i=r(53),n=a(i),o=r(2),s=a(o),u=r(54),l=a(u),d=r(55),c=a(d),f=r(1),p=a(f),h=r(8),_=a(h),v=r(5),m=r(3),y=a(m),b={};b.safeCall=n.default,b.safeIncrement=s.default,b.getComputedStyle=l.default,b.secondsToMs=c.default,b.assign=p.default,b.headersStringToObject=_.default,b.extractHostnameAndDomain=v.extractHostnameAndDomain,b.extractHostname=v.extractHostname,b.now=y.default.now,t.default=b},function(e,t,r){"use strict";function a(e,t,r,a){var i=a;if(e&&"function"==typeof e[t])try{i=e[t].apply(e,r)}catch(e){n.default.info("safeCall error",e)}return i}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a;var i=r(4),n=function(e){return e&&e.__esModule?e:{default:e}}(i)},function(e,t,r){"use strict";function a(e,t){if(n.default.defaultView&&n.default.defaultView.getComputedStyle)return n.default.defaultView.getComputedStyle(e,null).getPropertyValue(t)}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a;var i=r(12),n=function(e){return e&&e.__esModule?e:{default:e}}(i)},function(e,t,r){"use strict";function a(e){return Math.floor(1e3*e)}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a}])})}()},function(e,t,r){"use strict";function a(e,t){var r=arguments.length>2&&void 0!==arguments[2]?arguments[2]:[],a=arguments.length>3&&void 0!==arguments[3]?arguments[3]:[],i=arguments.length>4&&void 0!==arguments[4]?arguments[4]:function(){return{}},s=!1;e.on("adstart",function(){s=!0}),e.on("adend",function(){s=!1}),e.on("adserror",function(){s=!1});var u=function(){var a=o(t.currentTime()),u=function r(){var u=n.default.utils.now(),l=o(t.currentTime());if(s){if(l>a){var d=i();d.viewer_time=u-Math.max(0,l-a),t.off("timeupdate",r),e.mux.emit("adplaying",d)}}else t.off("timeupdate",r)};t.on("timeupdate",u);var l=function e(){t.off("timeupdate",u),r.forEach(function(r){t.off(r,e)})};r.forEach(function(e){t.on(e,l)})};a.forEach(function(e){t.on(e,u)})}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a;var i=r(0),n=function(e){return e&&e.__esModule?e:{default:e}}(i),o=n.default.utils.secondsToMs},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}function i(e){if(o.default.IS_IE8)return!1;var t=this,r=t.id();e=x({automaticErrorTracking:!0},e),e.data=x({player_software_name:"Video.js",player_software_version:o.default.VERSION||"< 4.11",player_mux_plugin_name:"videojs-mux",player_mux_plugin_version:"3.1.4"},e.data),e.getPlayheadTime=function(){return k(t.currentTime())},e.getStateData=function(){var e=S(t,"videoHeight"),r=S(t,"videoWidth");if(void 0===r||void 0===e){var a=t.el().firstChild;a&&"VIDEO"===a.nodeName.toUpperCase()&&(e=a.videoHeight,r=a.videoWidth)}return{player_is_paused:S(t,"paused"),player_is_fullscreen:S(t,"isFullscreen"),player_autoplay_on:!!S(t,"autoplay"),player_preload_on:S(t,"preload"),player_width:T(t.el(),"width"),player_height:T(t.el(),"height"),player_language_code:S(t,"language"),video_poster_url:S(t,"poster"),video_source_url:S(t,"currentSrc"),video_source_mime_type:S(t,"currentType"),video_source_duration:k(S(t,"duration")||0),video_source_height:e,video_source_width:r}},t.mux=function(){w.error("[videojs-mux] The plugin was initialized more than once.")},t.mux.emit=function(e,t){u.default.emit(r,e,t)},t.mux.log=w,t.mux.utils=u.default.utils,u.default.init(r,e);var a=!1,i=function(){a||t.mux.emit("playerready"),a=!0};t.ready(function(){t.addClass("vjs-mux"),d.default.setTimeout(i,0)});var n=!1;t.one("play",i),E.forEach(function(r){("error"!==r||e.automaticErrorTracking)&&t.on(r,function(){if(!n){var e={};if("error"===r){var a=S(t,"error");e.player_error_code=a&&a.code,e.player_error_message=a&&a.message}t.mux.emit(r,e)}})});var s=!1;t.on("play",function(){s=!0}),t.on("pause",function(){s=!1}),t.on("adstart",function(){n=!0,s&&t.mux.emit("pause"),t.mux.emit("adbreakstart")}),t.on("adend",function(){t.mux.emit("adbreakend"),n=!1,S(t,"paused")||(t.mux.emit("play"),s=!0,t.onceux&&t.mux.emit("playing"))}),t.ima&&(0,f.default)(t),t.onceux&&(0,y.default)(t),t.ima3&&"function"==typeof t.ima3.ready&&(0,h.default)(t),t.FreeWheelPlugin&&t.FreeWheelPlugin.VERSION&&(0,v.default)(t),t.on("dispose",function(){t.mux.emit("destroy")})}var n=r(3),o=a(n),s=r(0),u=a(s),l=r(4),d=a(l),c=r(6),f=a(c),p=r(7),h=a(p),_=r(8),v=a(_),m=r(9),y=a(m),b=r(10),g=a(b),w=u.default.log,x=u.default.utils.assign,S=u.default.utils.safeCall,T=u.default.utils.getComputedStyle,k=u.default.utils.secondsToMs,E=["loadstart","pause","play","playing","seeking","seeked","timeupdate","ratechange","stalled","waiting","error","ended"];(o.default.registerPlugin||o.default.plugin||function(){u.default.log.error("No valid method to register videojs plugin available.")})("mux",i),(0,g.default)(o.default)},function(t,r){t.exports=e},function(e,t,r){(function(t){var r;r="undefined"!=typeof window?window:void 0!==t?t:"undefined"!=typeof self?self:{},e.exports=r}).call(t,r(5))},function(e,t){var r;r=function(){return this}();try{r=r||Function("return this")()||(0,eval)("this")}catch(e){"object"==typeof window&&(r=window)}e.exports=r},function(e,t,r){"use strict";function a(e){var t=function(t){t=t||{};var r="function"==typeof t.getAdData?t.getAdData():{},a="function"==typeof t.getAd?t.getAd():{},i="function"==typeof a.getMediaUrl?a.getMediaUrl():r.mediaUrl,n=e.ima.settings||{},o={};return i&&(o.ad_asset_url=i),n.adTagUrl&&(o.ad_tag_url=n.adTagUrl),o},r=void 0,a=void 0,i=void 0,n=void 0,o=void 0,s=void 0;try{var u=window.google.ima.AdEvent.Type;r=u.LOADED,a=u.STARTED,i=u.COMPLETE,n=u.PAUSED,o=u.RESUMED,s=u.SKIPPED}catch(e){r="loaded",a="start",i="complete",n="pause",o="resume",s="skip"}var l=0;e.mux.triggerAdRequest=function(){l++,e.mux.emit("adrequest",t())},e.on("adsready",function(){var u=e.ima.addEventListener||function(){};u(r,function(r){var a=t(r);l>0&&(l--,e.mux.emit("adresponse",a)),e.mux.emit("adplay",a)}),u(a,function(r){var a=t(r);e.mux.emit("adplaying",a)}),u(o,function(r){var a=t(r);e.mux.emit("adplay",a),e.mux.emit("adplaying",a)}),u(n,function(r){var a=t(r);e.mux.emit("adpause",a)}),u(i,function(r){var a=t(r);e.mux.emit("adended",a)}),u(s,function(r){var a=t(r);e.mux.emit("adended",a)}),e.on("adserror",function(){l>0&&(l--,e.mux.emit("adresponse")),e.mux.emit("aderror")})})}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a},function(e,t,r){"use strict";function a(e){if("string"!=typeof e)return!1;var t=e.split(".").map(function(e){return parseInt(e)}),r=t[0],a=t[1];return r>2||2===r&&a>=3}function i(e){var t=function(){var t=e.ima3.currentAd,r=e.ima3.settings,a={};return t&&(a.ad_asset_url=t.mediaUrl),r&&(a.ad_tag_url=r.serverUrl),a};if(a(e.ima3.version)){var r=0,i=function(){r>0&&(r--,e.mux.emit("adresponse",t()))};e.on("ads-request",function(){r++,e.mux.emit("adrequest",t())}),e.on("ads-load",function(){i(),e.mux.emit("adplay",t())}),e.on("adserror",function(){i(),e.mux.emit("aderror")}),e.on("ads-play",function(){e.mux.emit("adplay",t())}),e.on("ads-pause",function(){e.mux.emit("adpause",t())}),e.on("ads-ad-ended",function(){e.mux.emit("adended",t())})}e.ima3.ready(function(){if(e.ima3.adPlayer){a(e.ima3.version)||(e.ima3.adPlayer.on("play",function(){e.mux.emit("adplay")}),e.ima3.adPlayer.on("pause",function(){e.mux.emit("adpause")}),e.ima3.adPlayer.on("ended",function(){e.mux.emit("adended")}));var r=["pause","ended","adserror"],i=["play"];(0,o.default)(e,e.ima3.adPlayer,r,i,t)}else e.mux.log("Legacy IMA3 plugin found, ad events may not track correctly."),e.on("ads-ad-started",function(){e.mux.emit("adplaying",t())}),e.on("ads-play",function(){e.mux.emit("adplaying",t())})})}Object.defineProperty(t,"__esModule",{value:!0}),t.brightcoveImaAdsEventsSupported=a,t.default=i;var n=r(1),o=function(e){return e&&e.__esModule?e:{default:e}}(n)},function(e,t,r){"use strict";function a(e){var t=function(t){var r=e.FreeWheelPlugin,a={};switch(t){case"adplay":case"adplaying":case"adpause":a.ad_asset_url=e.currentSrc()}return r&&(a.ad_tag_url="html5"===r.tech.toLowerCase()?r.settings.Html5.serverUrl:r.settings.Flash.serverUrl),a};e.on("ads-request",function(){e.mux.emit("adrequest",t("adrequest"))}),e.on("ads-load",function(){e.mux.emit("adresponse",t("adresponse"))}),e.on("adserror",function(){e.mux.emit("aderror")}),e.on("ads-ad-started",function(){e.mux.emit("adplay",t("adplay")),e.mux.emit("adplaying",t("adplaying"))}),e.on("ads-play",function(){e.mux.emit("adplay",t("adplay")),e.mux.emit("adplaying",t("adplaying"))}),e.on("ads-pause",function(){e.mux.emit("adpause",t("adpause"))}),e.on("ads-ad-ended",function(){e.mux.emit("adended",t("adended"))}),e.on("adend",function(){e.mux.emit("play")})}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a},function(e,t,r){"use strict";function a(e){var t=["onceux-linearad-pause","onceux-linearad-skipped","onceux-linearad-complete","adserror"],r=["adstart","onceux-linearad-resume"];(0,n.default)(e,e,t,r),e.on("adstart",function(){e.mux.emit("adplay")}),e.on("onceux-linearad-start",function(t){t.linearAd&&t.linearAd.index>0&&(e.mux.emit("adplay"),e.mux.emit("adplaying"))}),e.on("onceux-linearad-resume",function(){e.mux.emit("adplay")}),e.on("onceux-linearad-pause",function(){e.mux.emit("adpause")}),e.on("onceux-linearad-complete",function(){e.mux.emit("adended")}),e.on("onceux-linearad-skipped",function(){e.mux.emit("adended")})}Object.defineProperty(t,"__esModule",{value:!0}),t.default=a;var i=r(1),n=function(e){return e&&e.__esModule?e:{default:e}}(i)},function(e,t,r){"use strict";function a(e){if(!u&&"function"==typeof e.getTech){for(var t=e.getTech("Html5")||{},r=t.sourceHandlers,a=0;a<r.length;a++)!function(t){var a=r[t],n=a.handleSource;a.handleSource=function(t,r){var a=arguments.length>2&&void 0!==arguments[2]?arguments[2]:{},u=n(t,r,a);if(u&&"function"==typeof u.xhr)try{var l=e.VERSION.split("."),d=i(l,2),c=d[0],f=d[1];c=parseInt(c),f=parseInt(f);var p=c>=7&&f>=4?u.player().id():u.player_.el_.parentNode.id;(0,o.default)(e(p),u.xhr),(0,s.trackRenditionChanges)(e(p))}catch(e){}return u}}(a);u=!0}}Object.defineProperty(t,"__esModule",{value:!0});var i=function(){function e(e,t){var r=[],a=!0,i=!1,n=void 0;try{for(var o,s=e[Symbol.iterator]();!(a=(o=s.next()).done)&&(r.push(o.value),!t||r.length!==t);a=!0);}catch(e){i=!0,n=e}finally{try{!a&&s.return&&s.return()}finally{if(i)throw n}}return r}return function(t,r){if(Array.isArray(t))return t;if(Symbol.iterator in Object(t))return e(t,r);throw new TypeError("Invalid attempt to destructure non-iterable instance")}}();t.default=a;var n=r(11),o=function(e){return e&&e.__esModule?e:{default:e}}(n),s=r(13),u=!1},function(e,t,r){"use strict";function a(e){return e&&e.__esModule?e:{default:e}}function i(e,t){var r=function(t,r,a){var i=(0,o.default)(e,t,r);return function(){try{i()}catch(e){}try{a()}catch(e){}}},a=function(e){return function(t){var a=u.default.utils.now();try{e(t)}catch(e){}try{t.onreadystatechange=r(t,a,t.onreadystatechange||function(){})}catch(e){}}};t.beforeRequest=function(e){return function(t){var r=e(t);return r.beforeSend=a(r.beforeSend||function(){}),r}}(t.beforeRequest||function(e){return e})}Object.defineProperty(t,"__esModule",{value:!0}),t.default=i;var n=r(12),o=a(n),s=r(0),u=a(s)},function(e,t,r){"use strict";function a(e,t,r){var a=e.mux.utils.extractHostnameAndDomain,n=e.mux.utils.headersStringToObject,s=function(e){var t=e["content-type"];return t?t.match(/^audio\/mpegurl/i)?"manifest":t.match(/^audio.*/i)?"audio":t.match(/^video.*/)?"video":t.match(/^application\/x-mpegurl/i)?"manifest":t.match(/^application\/vnd.apple.mpegurl/i)?"manifest":t.match(/^application\/dash+xml/i)?"manifest":"unknown":"unknown"},u=0;return function(){var l=t.readyState;if(l>=2){if(u=u||o.default.utils.now(),4!==l)return;if(t.status>=200&&t.status<300){var d=o.default.utils.now(),c="arraybuffer"===t.responseType?t.response.byteLength:t.responseText.length,f=a(t.responseURL),p=i(f,1),h=p[0],_=n(t.getAllResponseHeaders?t.getAllResponseHeaders():""),v=s(_),m={request_start:r,request_response_start:u,request_response_end:d,request_bytes_loaded:c,request_hostname:h,request_response_headers:_,request_type:v};e.mux.emit("requestcompleted",m)}else e.mux.emit("requestfailed")}}}Object.defineProperty(t,"__esModule",{value:!0});var i=function(){function e(e,t){var r=[],a=!0,i=!1,n=void 0;try{for(var o,s=e[Symbol.iterator]();!(a=(o=s.next()).done)&&(r.push(o.value),!t||r.length!==t);a=!0);}catch(e){i=!0,n=e}finally{try{!a&&s.return&&s.return()}finally{if(i)throw n}}return r}return function(t,r){if(Array.isArray(t))return t;if(Symbol.iterator in Object(t))return e(t,r);throw new TypeError("Invalid attempt to destructure non-iterable instance")}}();t.default=a;var n=r(0),o=function(e){return e&&e.__esModule?e:{default:e}}(n)},function(e,t,r){"use strict";Object.defineProperty(t,"__esModule",{value:!0});var a=function(e){for(var t=e.textTracks(),r=void 0,a=0;a<t.length;a++)"segment-metadata"===t[a].label&&(r=t[a]);return r},i=function(e){var t=a(e),r=void 0;t&&t.on&&t.on("cuechange",function(){try{var t=e.tech({IWillNotUseThisInPlugins:!0}),a=t.vhs||t.hls,i=a.playlists.media().attributes.BANDWIDTH;r!==i&&e.mux.emit("renditionchange",{video_source_bitrate:i}),r=i}catch(t){e.mux&&e.mux.log&&e.mux.log.warn&&e.mux.log.warn("Cannot retrieve BANDWIDTH information from player: "+t)}})};t.getSegmentMetadataTrack=a,t.trackRenditionChanges=i}])});})();

/**
 * videojs-vjsdownload
 * @version 1.0.5
 * @copyright 2023 7Ds7
 * @license Apache-2.0
 */
(function(f){if(typeof exports==="object"&&typeof module!=="undefined"){module.exports=f()}else if(typeof define==="function"&&define.amd){define([],f)}else{var g;if(typeof window!=="undefined"){g=window}else if(typeof global!=="undefined"){g=global}else if(typeof self!=="undefined"){g=self}else{g=this}g.videojsVjsdownload = f()}})(function(){var define,module,exports;return (function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
(function (global){
'use strict';

Object.defineProperty(exports, '__esModule', {
  value: true
});

var _createClass = (function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ('value' in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; })();

var _get = function get(_x, _x2, _x3) { var _again = true; _function: while (_again) { var object = _x, property = _x2, receiver = _x3; _again = false; if (object === null) object = Function.prototype; var desc = Object.getOwnPropertyDescriptor(object, property); if (desc === undefined) { var parent = Object.getPrototypeOf(object); if (parent === null) { return undefined; } else { _x = parent; _x2 = property; _x3 = receiver; _again = true; desc = parent = undefined; continue _function; } } else if ('value' in desc) { return desc.value; } else { var getter = desc.get; if (getter === undefined) { return undefined; } return getter.call(receiver); } } };

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { 'default': obj }; }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError('Cannot call a class as a function'); } }

function _inherits(subClass, superClass) { if (typeof superClass !== 'function' && superClass !== null) { throw new TypeError('Super expression must either be null or a function, not ' + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var _videoJs = (typeof window !== "undefined" ? window['videojs'] : typeof global !== "undefined" ? global['videojs'] : null);

var _videoJs2 = _interopRequireDefault(_videoJs);

// Default options for the plugin.
var defaults = {
  beforeElement: 'fullscreenToggle',
  textControl: 'Download video',
  name: 'downloadButton',
  downloadURL: null
};

var vjsButton = _videoJs2['default'].getComponent('Button');

var DownloadButton = (function (_vjsButton) {
  _inherits(DownloadButton, _vjsButton);

  function DownloadButton() {
    _classCallCheck(this, DownloadButton);

    _get(Object.getPrototypeOf(DownloadButton.prototype), 'constructor', this).apply(this, arguments);
  }

  /**
   * Function to invoke when the player is ready.
   *
   * This is a great place for your plugin to initialize itself. When this
   * function is called, the player will have its DOM and child components
   * in place.
   *
   * @function onPlayerReady
   * @param    {Player} player
   * @param    {Object} [options={}]
   */

  _createClass(DownloadButton, [{
    key: 'buildCSSClass',

    /**
    * Allow sub components to stack CSS class names
    *
    * @return {String} The constructed class name
    * @method buildCSSClass
    */
    value: function buildCSSClass() {
      return 'vjs-vjsdownload ' + _get(Object.getPrototypeOf(DownloadButton.prototype), 'buildCSSClass', this).call(this);
    }

    /**
    * Handles click for full screen
    *
    * @method handleClick
    */
  }, {
    key: 'handleClick',
    value: function handleClick() {
      var p = this.player();

      window.open(this.options_.downloadURL || p.currentSrc(), 'Download');
      p.trigger('downloadvideo');
    }
  }]);

  return DownloadButton;
})(vjsButton);

var onPlayerReady = function onPlayerReady(player, options) {
  var DButton = player.controlBar.addChild(new DownloadButton(player, options), {});

  DButton.controlText(options.textControl);

  player.controlBar.el().insertBefore(DButton.el(), player.controlBar.getChild(options.beforeElement).el());

  player.addClass('vjs-vjsdownload');
};

/**
 * A video.js plugin.
 *
 * In the plugin function, the value of `this` is a video.js `Player`
 * instance. You cannot rely on the player being in a "ready" state here,
 * depending on how the plugin is invoked. This may or may not be important
 * to you; if not, remove the wait for "ready"!
 *
 * @function vjsdownload
 * @param    {Object} [options={}]
 *           An object of options left to the plugin author to define.
 */
var vjsdownload = function vjsdownload(options) {
  var _this = this;

  this.ready(function () {
    onPlayerReady(_this, _videoJs2['default'].mergeOptions(defaults, options));
  });
};

// Register the plugin with video.js.
_videoJs2['default'].registerPlugin('vjsdownload', vjsdownload);

exports['default'] = vjsdownload;
module.exports = exports['default'];
}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}]},{},[1])(1)
});

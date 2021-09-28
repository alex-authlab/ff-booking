/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 0);
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/assets/BookingSettings/booking-settings.scss":
/*!**********************************************************!*\
  !*** ./src/assets/BookingSettings/booking-settings.scss ***!
  \**********************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ "./src/assets/js/ff-booking-date-time.js":
/*!***********************************************!*\
  !*** ./src/assets/js/ff-booking-date-time.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

jQuery(document).ready(function ($) {
  $('.ff-booking-container').each(function (index, item) {
    var $container = $(item);
    var elementId = $container.find(".ff-booking-date-time").attr('id');
    var input = document.getElementById(elementId);

    var FF_booking_handler = /*#__PURE__*/function () {
      function FF_booking_handler(element, container) {
        _classCallCheck(this, FF_booking_handler);

        this.element = element;
        this.container = container;
        this.init();
        this.generateTimeSlot();
      }

      _createClass(FF_booking_handler, [{
        key: "init",
        value: function init() {
          var _this = this;

          $('#' + this.element).on('change', function (e) {
            e.preventDefault();

            _this.generateTimeSlot();

            console.log('ok');
          });
        }
      }, {
        key: "getData",
        value: function getData() {}
      }, {
        key: "generateTimeSlot",
        value: function generateTimeSlot() {
          var $slot = this.container.find(".ff-time-slot-container");

          if (!$slot.length) {
            $('<div/>', {
              "class": 'ff-time-slot-container'
            }).appendTo(this.container);
            $slot = $('.ff-time-slot-container');
          }

          jQuery.post(window.ff_booking_date_time_vars.ajaxUrl, {
            action: 'handle_booking_ajax_endpoint',
            route: 'get_slots',
            service_id: '',
            date: $('#' + this.element).val(),
            form_id: window.ff_booking_date_time_vars.formId
          }).then(function (response) {
            if (response.success == true) {
              $slot.html(response.data.html);
            }
          }).always(function () {
            jQuery('.booking-loader').remove();
          });
        }
      }]);

      return FF_booking_handler;
    }();

    new FF_booking_handler(elementId, $container);
    console.log(elementId);
  });
}(jQuery));

/***/ }),

/***/ 0:
/*!********************************************************************************************************!*\
  !*** multi ./src/assets/js/ff-booking-date-time.js ./src/assets/BookingSettings/booking-settings.scss ***!
  \********************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /Users/nkb/Projects/wp_lab/wp-content/plugins/ff-booking/src/assets/js/ff-booking-date-time.js */"./src/assets/js/ff-booking-date-time.js");
module.exports = __webpack_require__(/*! /Users/nkb/Projects/wp_lab/wp-content/plugins/ff-booking/src/assets/BookingSettings/booking-settings.scss */"./src/assets/BookingSettings/booking-settings.scss");


/***/ })

/******/ });
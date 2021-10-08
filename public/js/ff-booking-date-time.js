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

/***/ "./src/assets/css/form_booking.scss":
/*!******************************************!*\
  !*** ./src/assets/css/form_booking.scss ***!
  \******************************************/
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
  $('form.ff_has_booking').each(function () {
    var $form = $(this);
    $form.on('fluentform_init_single', function (event, instance) {
      var booking = new FF_booking_handler($form, instance);
      booking.init();
    });
  });
}(jQuery));

var FF_booking_handler = /*#__PURE__*/function () {
  function FF_booking_handler($form, instance) {
    _classCallCheck(this, FF_booking_handler);

    var formId = instance.settings.id;
    this.$form = $form;
    this.formInstance = instance;
    this.formId = formId;
    this.serviceElmClass = '.ff_booking_service';
    this.providerElmClass = '.ff_booking_provider';
    this.dateTimeElmClass = '.ff-booking-date-time';
  }

  _createClass(FF_booking_handler, [{
    key: "init",
    value: function init() {
      var _this = this;

      this.$form.find(this.serviceElmClass).on('change', function (event) {
        var $element = jQuery(event.target);

        _this.getProviderList($element.val());
      });
      this.initToolTip();
      this.setProviderListener(); //initial datepicker listener

      setTimeout(function () {
        var datepickerElm = jQuery(_this.$form.find(_this.dateTimeElmClass));

        var targetFp = document.getElementById(datepickerElm.attr('id'))._flatpickr;

        var that = _this;
        targetFp.config.onChange.push(function () {
          var providerElm = jQuery(that.$form.find(that.providerElmClass));
          var serviceElm = jQuery(that.$form.find(that.serviceElmClass));

          if (!!serviceElm.val() === false || !!providerElm.val() === false) {
            var $details = that.$form.find(".ff-time-slot-details");

            if (!$details.length) {
              jQuery('<div/>', {
                "class": 'ff-time-slot-details'
              }).appendTo(that.$form.find('.ff-booking-container'));
              $details = jQuery('.ff-time-slot-details');
              var html = "<span>Please Select Service & Provider</span>";
              $details.html(html);
            }
          }
        });
      }, 1000);
    }
  }, {
    key: "initToolTip",
    value: function initToolTip() {
      jQuery(document).on('mouseenter', '.ff-el-booking-tootltip ', function (e) {
        var $element = jQuery(e.target);
        var content = $element.data('content');
        var $popContent = jQuery('.ff-el-pop-content');

        if (!$popContent.length) {
          jQuery('<div/>', {
            "class": 'ff-el-pop-content'
          }).appendTo($element);
          $popContent = jQuery('.ff-el-pop-content');
        }

        $popContent.css('bottom', '-70%');
        $popContent.html(content);
        setTimeout(function () {
          $popContent.remove();
        }, 1500);
      });
    }
  }, {
    key: "getProviderList",
    value: function getProviderList(serviceId) {
      var _this2 = this;

      var providerElm = jQuery(this.$form.find(this.providerElmClass));
      providerElm.html(jQuery('<option>', {
        value: '',
        text: "Please wait Loading.."
      }));
      jQuery.post(window.ff_booking_date_time_vars.ajaxUrl, {
        action: 'handle_booking_frontend_endpoint',
        route: 'get_service_provider',
        service_id: serviceId,
        form_id: this.formId
      }).then(function (res) {
        _this2.setProvidersList(res, providerElm);
      }).fail(function (errors) {
        console.log(errors);
      }).always(function () {
        _this2.setDateListener();

        _this2.resetDateTime();
      });
    }
  }, {
    key: "setProvidersList",
    value: function setProvidersList(res, providerElm) {
      var $details = this.$form.find(".ff-time-slot-details");
      $details.remove();
      providerElm.empty();

      if (res.success == true && Object.keys(res).length > 0) {
        jQuery.each(res.data.providers, function (id, title) {
          jQuery("<option/>", {
            value: id,
            text: title
          }).appendTo(providerElm);
        });
        providerElm.find("option:first").attr('selected', 'selected');
        this.getDateTime();
      } else {
        providerElm.html(jQuery('<option>', {
          value: '',
          text: "No service found !"
        }));
      }
    }
  }, {
    key: "setProviderListener",
    value: function setProviderListener() {
      var _this3 = this;

      this.$form.find(this.providerElmClass).on('change', function (event) {
        _this3.resetDateTime();

        var serviceElm = jQuery(_this3.$form.find(_this3.serviceElmClass));

        if (!!serviceElm.val() === false) {
          return;
        }

        _this3.getDateTime();
      });
    }
  }, {
    key: "getDateTime",
    value: function getDateTime() {
      var datepickerElm = jQuery(this.$form.find(this.dateTimeElmClass));

      var targetFp = document.getElementById(datepickerElm.attr('id'))._flatpickr;

      var providerElm = jQuery(this.$form.find(this.providerElmClass));
      var serviceElm = jQuery(this.$form.find(this.serviceElmClass));
      var that = this;
      var $maskLoader = that.$form.find(".ff-booking-loading-mask");

      if (!$maskLoader.length) {
        datepickerElm.parent().find('.flatpickr-innerContainer').append('<div class="ff-booking-loading-mask"></div>');
        $maskLoader = jQuery('.ff-booking-loading-mask');
        var loader = '<div class="ff-booking-loader"></div>';
        $maskLoader.html(loader);
      }

      jQuery.post(window.ff_booking_date_time_vars.ajaxUrl, {
        action: 'handle_booking_frontend_endpoint',
        route: 'get_dates',
        service_id: serviceElm.val(),
        provider_id: providerElm.val(),
        form_id: this.formId
      }).then(function (res) {
        var $maskLoader = that.$form.find(".ff-booking-loading-mask");
        $maskLoader.remove();

        if (res.success == true) {
          var disabledWeekDays = function disabledWeekDays(date) {
            return formatDays.includes(date.getDay());
          };

          // disable fullbooked
          var disabledDates = res.data.dates_data.disabled_dates;

          if (!disabledDates) {
            disabledDates = [];
          } //disabled week off days


          var offDays = res.data.dates_data.weekend_days;
          var formatDays = offDays.map(function (x) {
            return parseInt(x);
          });

          if (!formatDays) {
            formatDays = [];
          }

          targetFp.config.disable = disabledDates;
          targetFp.config.disable.push(disabledWeekDays); //disable max future days

          targetFp.set('minDate', res.data.dates_data.min_date);
          targetFp.set('maxDate', res.data.dates_data.max_date);
          targetFp.jumpToDate(res.data.dates_data.start_date);
        }
      }).fail(function (errors) {
        console.log(errors);
      }).always(function () {
        var $maskLoader = that.$form.find(".ff-booking-loading-mask");
        $maskLoader.remove();
      });
    }
  }, {
    key: "setDateListener",
    value: function setDateListener() {
      var datepickerElm = jQuery(this.$form.find(this.dateTimeElmClass));

      var targetFp = document.getElementById(datepickerElm.attr('id'))._flatpickr;

      var that = this;
      targetFp.config.onChange.push(function (selectedDates, dateStr, instance) {
        var selectedDate = targetFp.formatDate(selectedDates[0], "Y-m-d");
        that.getTimeSlots(targetFp, selectedDate);
      });
    }
  }, {
    key: "getTimeSlots",
    value: function getTimeSlots(targetFp, selectedDate) {
      var _this4 = this;

      var that = this;
      jQuery.ajaxSetup({
        data: {//todo nonce
        },
        beforeSend: function beforeSend() {
          var $slot = that.$form.find(".ff-time-slot-container");

          if (!$slot.length) {
            jQuery('<div/>', {
              "class": 'ff-time-slot-container'
            }).appendTo(that.$form.find('.ff-booking-container'));
            $slot = jQuery('.ff-time-slot-container');
            var loader = '<div class="ff-booking-loader"></div>';
            $slot.html(loader);
          }
        }
      });
      var providerElm = jQuery(this.$form.find(this.providerElmClass));
      var serviceElm = jQuery(this.$form.find(this.serviceElmClass));
      jQuery.get(window.ff_booking_date_time_vars.ajaxUrl, {
        action: 'handle_booking_frontend_endpoint',
        route: 'get_time_slots',
        service_id: serviceElm.val(),
        provider_id: providerElm.val(),
        form_id: this.formId,
        date: selectedDate
      }).then(function (response) {
        if (response.success == true) {
          _this4.generateTimeSlots(response);
        }
      }).fail(function (errors) {
        console.log(errors);
      }).always(function () {});
    }
  }, {
    key: "generateTimeSlots",
    value: function generateTimeSlots(res) {
      var $slot = this.$form.find(".ff-time-slot-container");

      if (!$slot.length) {
        jQuery('<div/>', {
          "class": 'ff-time-slot-container'
        }).appendTo(this.$form.find('.ff-booking-container'));
        $slot = jQuery('.ff-time-slot-container');
      }

      var slots = '';
      jQuery.each(res.data.time_slots, function (index, slot) {
        var booked = '';
        var input = '';

        if (slot.booked && slot.booked === true) {
          booked = ' ff-booked-slot ff-el-booking-tootltip';
        } else {
          input = '<input type="radio" name="input_radio" class="ff-el-form-check-input ff-el-booking-slot " value="' + slot.value + '" id="input_radio_ff_booking_' + index + '"> ';
        }

        slots += '<div data-content="Slot Booked" class=" ff-el-form-check ff-el-form-check-   ' + booked + ' ">' + '<label class="ff-el-form-check-label" for="input_radio_ff_booking_' + index + '">' + '<span>' + slot.label + '</span>' + input + '</label>' + '</div>';
      });
      var slotHtml = '<div class="ff-el-group  ff_list_buttons">' + '<div class="ff-el-input--label ">' + '<label>Select Time</label> ' + '</div>' + '<div class="ff-el-input--content">' + slots + '</div>' + '</div>';
      $slot.html(slotHtml);
      this.setInputDateTime();
    }
  }, {
    key: "setInputDateTime",
    value: function setInputDateTime() {
      var _this5 = this;

      var $details = this.$form.find(".ff-time-slot-details");

      if (!$details.length) {
        jQuery('<div/>', {
          "class": 'ff-time-slot-details'
        }).appendTo(this.$form.find('.ff-booking-container'));
        $details = jQuery('.ff-time-slot-details');
      }

      jQuery(document).on('change', '.ff-time-slot-container input', function (e) {
        var $element = jQuery(e.target);
        var time = $element.val();
        var providerElm = jQuery(_this5.$form.find(_this5.providerElmClass));
        var temp = document.getElementById(providerElm.attr('id'));
        var provider = temp.options[temp.selectedIndex].innerHTML;
        var serviceElm = jQuery(_this5.$form.find(_this5.serviceElmClass));
        temp = document.getElementById(serviceElm.attr('id'));
        var service = temp.options[temp.selectedIndex].innerHTML;
        var datepickerElm = jQuery(_this5.$form.find(_this5.dateTimeElmClass));

        var targetFp = document.getElementById(datepickerElm.attr('id'))._flatpickr;

        var date = targetFp.formatDate(targetFp.selectedDates[0], "l d M,Y");
        var html = "<span>Selected a booking for <b>".concat(service, "</b>  by <b>").concat(provider, "</b> at <b>").concat(time, "</b> am on <b>").concat(date, "</b></span>");
        $details.html(html); //set input value date+time

        var formattedDate = targetFp.formatDate(targetFp.selectedDates[0], "Y-m-d");
        datepickerElm.val(formattedDate + ' ' + time);
      });
    }
  }, {
    key: "resetDateTime",
    value: function resetDateTime() {
      var $slot = this.$form.find(".ff-time-slot-container, .ff-booking-loading-mask");

      if ($slot.length) {
        $slot.remove();
      }

      var datepickerElm = jQuery(this.$form.find(this.dateTimeElmClass));

      var targetFp = document.getElementById(datepickerElm.attr('id'))._flatpickr;

      if (!datepickerElm.val()) {
        return;
      }

      targetFp.clear();
    }
  }]);

  return FF_booking_handler;
}();

/***/ }),

/***/ 0:
/*!*******************************************************************************************************************************************!*\
  !*** multi ./src/assets/js/ff-booking-date-time.js ./src/assets/BookingSettings/booking-settings.scss ./src/assets/css/form_booking.scss ***!
  \*******************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /Users/nkb/Projects/wp/wp-content/plugins/ff-booking/src/assets/js/ff-booking-date-time.js */"./src/assets/js/ff-booking-date-time.js");
__webpack_require__(/*! /Users/nkb/Projects/wp/wp-content/plugins/ff-booking/src/assets/BookingSettings/booking-settings.scss */"./src/assets/BookingSettings/booking-settings.scss");
module.exports = __webpack_require__(/*! /Users/nkb/Projects/wp/wp-content/plugins/ff-booking/src/assets/css/form_booking.scss */"./src/assets/css/form_booking.scss");


/***/ })

/******/ });
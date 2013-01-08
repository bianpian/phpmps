(function() {
    function ValidatorForm(formObj) {
        this.formId = "";
        this.formObj = formObj;
        this.hasError = false;
        this.firstErrorId = null;
        this.formCallbacks = [];
        this.oldSubmitFunc = null;
        this.submitClicked = false
    }
    ValidatorForm.count = 1;
    ValidatorForm.focusTmpInput = $("<input />");
    ValidatorForm.focusTmpInput.attr("type", "text");
    ValidatorForm.focusTmpInput.css("width", 1);
    var loc = document.location;
    function ValidatorElement(fieldName) {
        var fullFieldName = fieldName,
        fieldNames,
        el,
        form,
        formId,
        parent,
        jFieldV,
        tagN,
        type;
        if (FormValidator.validators[fullFieldName]) {
            return FormValidator.validators[fullFieldName]
        }
        FormValidator.validators[fullFieldName] = this;
        el = ValidatorElement.getElByFullFieldName(fieldName);
        if (el.size() == 0) {
            alert("没有找到表单元素“" + fieldName + "”")
        } else {
            fieldName = el.attr("name")
        }
        form = el.size() > 0 && el.get(0).form || null;
        if (form) {
            parent = $(form);
            formId = FormValidator.getFormId(parent);
            if (!FormValidator.data[formId]) {
                FormValidator.data[formId] = []
            }
            FormValidator.data[formId][fieldName] = fullFieldName;
            FormValidator.addForm(parent)
        } else {
            parent = $(document)
        }
        this.fullFieldName = fullFieldName;
        this.formId = formId;
        this.parent = parent;
        this.form = form;
        this.el = el;
        this.el.data("fullFieldName", this.fullFieldName);
        try {
            var t = this;
            type = this.el.attr("type");
            if (type != "text") {
                this.el.attr("autocomplete", "off")
            }
            this.el.bind("focus", 
            function() {
                FormValidator.focusHandler(t)
            });
            if (type == "radio" || type == "checkbox") {
                this.el.bind("click", 
                function() {
                    t.focused = false;
                    t.el.focus()
                })
            }
            this.el.bind("blur", 
            function() {
                FormValidator.blurHandler(t)
            })
        } catch(e) {}
        this.fieldName = fieldName;
        this.shortInputName = fieldName.split("[")[0];
        this.defaultMsg = "";
        this.focusMsg = "";
        this.rules = {
            required: {},
            ajax: {},
            others: []
        };
        this.emptyValue = "";
        this.showErrorMode = "inline",
        this.compareField = null;
        this.hasCallback = false;
        this.hasCompareField = false;
        this.hasAjax = false;
        this.disabledCallback = null;
        this.type = type;
        this.tipSpan = null;
        this.valid = false;
        this.lastErrorMessage = "";
        this.oldColor = this.el.css("color") || "#000000";
        this.emptyValueColor = "#999999";
        this.focusValue = "";
        this.defaultDbValue = null;
        this.focused = false;
        this.serverCharset = "UTF-8";
        this.strlenType = "symbol";
        this.ajaxLoading = false;
        this.ajaxLoadStart = 0;
        this.checkSameTipField = true;
        this.sameTipsIsValid = false;
        this.sameTipsIsEmpty = true;
        this.focusChangeMsg = true;
        this.tipSpanId = "";
        this.setTipSpanId = function(tipSpanId) {
            if (tipSpanId != this.tipSpanId) {
                if (!ValidatorElement.tipSpanIds[tipSpanId]) {
                    ValidatorElement.tipSpanIds[tipSpanId] = []
                }
                ValidatorElement.tipSpanIds[tipSpanId][ValidatorElement.tipSpanIds[tipSpanId].length] = this.fullFieldName;
                this.tipSpanId = tipSpanId;
                if (this.tipSpan) {
                    var html = this.tipSpan.html();
                    this.tipSpan.remove();
                    this.tipSpan = null;
                    this.getTipSpan();
                    this.tipSpan.html(html)
                }
            }
            return this
        };
        this.setTipSpanId("tip_validator_" + (this.formId ? this.formId: "") + "_" + this.shortInputName);
        this.getTipSpan = function() {
            if (!this.tipSpan) {
                var tipSpan = $("#" + this.tipSpanId);
                if (tipSpan.size() == 0) {
                    $('<span id="' + this.tipSpanId + '"></span>').insertAfter(this.el.get(this.el.size() - 1));
                    tipSpan = $("#" + this.tipSpanId)
                }
                this.tipSpan = tipSpan
            }
            return this.tipSpan
        };
        this.setDefaultMsg = function(defaultMsg) {
            if (defaultMsg) {
                this.defaultMsg = defaultMsg;
                this.setInit()
            }
            return this
        };
        this.setFocusMsg = function(focusMsg) {
            if (focusMsg) {
                this.focusMsg = focusMsg
            }
            return this
        };
        this.setServerCharset = function(serverCharset) {
            this.serverCharset = serverCharset || "UTF-8";
            return this
        };
        this.setEmptyValue = function(emptyValue) {
            var tagName = this.el.attr("tagName").toLowerCase();
            this.emptyValue = emptyValue;
            var val = this.el.val();
            if (this.emptyValue != "" && (this.type == "text" || tagName == "textarea") && (val == "" || val == this.emptyValue || val == this.focusValue)) {
                this.el.val(this.emptyValue);
                this.el.css("color", this.emptyValueColor)
            }
            return this
        };
        this.setFocusValue = function(focusValue) {
            this.focusValue = focusValue;
            return this
        };
        this.setDefaultDbValue = function(defaultDbValue) {
            this.defaultDbValue = defaultDbValue;
            return this
        };
        this.setShowErrorMode = function(showErrorMode) {
            this.showErrorMode = showErrorMode == "alert" ? "alert": "inline";
            return this
        };
        this.setStrlenType = function(strlenType) {
            this.strlenType = strlenType || "symbol";
            return this
        };
        this.setRequired = function(requiredMsg, disabledCallback) {
            if (requiredMsg) {
                this.rules.required.msg = requiredMsg;
                this.rules.required.disabledCallback = disabledCallback
            }
            return this
        };
        this.checkSize = function(func) {
            if (this.el.size() > 1) {
                alert("Name属性相同的多个表单字段，只支持检测是否为空。不支持'" + func + "()'");
                return false
            }
            return true
        };
        this.setType = function(type, msg, exclude, disabledCallback) {
            if (this.checkSize("setType")) {
                eval("var regexp = FormValidator.types." + type + ";");
                if (regexp) {
                    this.setRegexp(regexp, msg, exclude, disabledCallback)
                } else {
                    alert("不存在检测类型'" + type + "'")
                }
            }
            return this
        };
        this.setRegexp = function(regexp, msg, exclude, disabledCallback) {
            if (this.checkSize("setRegexp")) {
                this.rules.others[this.rules.others.length] = {
                    mode: "regexp",
                    regexp: regexp,
                    msg: msg,
                    exclude: exclude,
                    disabledCallback: disabledCallback
                }
            }
            return this
        };
        this.setCompareField = function(operator, compareField, msg, disabledCallback) {
            if (this.checkSize("setCompareField")) {
                this.rules.others[this.rules.others.length] = {
                    mode: "compareField",
                    operator: operator,
                    compareField: compareField,
                    msg: msg,
                    disabledCallback: disabledCallback
                };
                this.compareField = compareField
            }
            return this
        };
        this.setCompareValue = function(operator, compareValue, msg, disabledCallback) {
            if (this.checkSize("setCompareValue")) {
                this.rules.others[this.rules.others.length] = {
                    mode: "compareValue",
                    operator: operator,
                    compareValue: compareValue,
                    msg: msg,
                    disabledCallback: disabledCallback
                }
            }
            return this
        };
        this.setLength = function(minValue, maxValue, msg, disabledCallback) {
            if (this.checkSize("setLength")) {
                this.rules.others[this.rules.others.length] = {
                    mode: "length",
                    minValue: minValue,
                    maxValue: maxValue,
                    msg: msg,
                    disabledCallback: disabledCallback
                }
            }
            return this
        };
        this.setMinLength = function(minValue, msg, disabledCallback) {
            if (this.checkSize("setMinLength")) {
                this.rules.others[this.rules.others.length] = {
                    mode: "minLength",
                    minValue: minValue,
                    msg: msg,
                    disabledCallback: disabledCallback
                }
            }
            return this
        };
        this.setMaxLength = function(maxValue, msg, disabledCallback) {
            if (this.checkSize("setMaxLength")) {
                this.rules.others[this.rules.others.length] = {
                    mode: "maxLength",
                    maxValue: maxValue,
                    msg: msg,
                    disabledCallback: disabledCallback
                }
            }
            return this
        };
        this.setCallback = function(funcName, msg, disabledCallback) {
            if (this.checkSize("setCallback")) {
                this.rules.others[this.rules.others.length] = {
                    mode: "callback",
                    funcName: funcName,
                    msg: msg,
                    disabledCallback: disabledCallback
                };
                this.hasCallback = true
            }
            return this
        };
        this.setAjax = function(url, msg, postData, disabledCallback) {
            if (this.checkSize("setAjax")) {
                if (!postData) {
                    postData = {}
                }
                this.rules.ajax = {
                    url: url,
                    msg: msg,
                    postData: postData,
                    disabledCallback: disabledCallback
                };
                this.hasAjax = true
            }
            return this
        };
        this.setDisabledCallback = function(callback) {
            if (typeof callback != "function") {
                alert(callback + "不是有效的javascript函数")
            }
            this.disabledCallback = callback;
            return this
        };
        this.checkDisabled = function(callback) {
            var cb;
            if (typeof callback == "function") {
                cb = callback
            } else {
                if (typeof this.disabledCallback == "function") {
                    cb = this.disabledCallback
                }
            }
            if (typeof cb == "function") {
                var bool = cb();
                if (bool && this.getTipSpan()) {
                    this.displayEmpty()
                }
                return bool
            }
            return false
        };
        this.displayError = function(errMsg) {
            this.valid = false;
            if (!errMsg) {
                errMsg = this.lastErrorMessage
            }
            if (this.showErrorMode == "alert") {
                alert(errMsg)
            } else {
                this.getTipSpan().html('<span class="validatorMsg validatorError">' + errMsg + "</span>")
            }
            this.lastErrorMessage = errMsg;
            return this
        };
        this.displayValid = function() {
            if (this.showErrorMode != "alert") {
                this.getTipSpan().html('<span class="validatorMsg validatorValid">&nbsp;</span>')
            }
            return this
        };
        this.displayFocus = function() {
            if (this.showErrorMode != "alert") {
                if (this.focusMsg) {
                    this.getTipSpan().html('<span class="validatorMsg validatorFocus">' + this.focusMsg + "</span>")
                } else {
                    this.displayEmpty()
                }
            }
            return this
        };
        this.displayDefault = function() {
            if (this.showErrorMode != "alert") {
                if (this.defaultMsg) {
                    this.getTipSpan().html('<span class="validatorMsg validatorInit">' + this.defaultMsg + "</span>")
                } else {
                    this.displayEmpty()
                }
            }
            return this
        };
        this.displayLoading = function() {
            if (this.showErrorMode != "alert") {
                this.getTipSpan().html('<span class="validatorMsg validatorLoad">正在检测，请稍候...</span>')
            }
            return this
        };
        this.displayEmpty = function() {
            if (this.showErrorMode != "alert") {
                this.getTipSpan().html('<span class="validatorMsg">&nbsp;</span>')
            }
            return this
        };
        this.setInit = function() {
            if (this.type == "radio" || this.type == "checkbox") {
                if ($("[name='" + this.fieldName + "']:checked", this.parent).size() > 0) {
                    return true
                }
            } else {
                var val = $(this.el.get(0)).val();
                if (val != "" && val != this.emptyValue && val != this.focusValue && val != this.defaultDbValue) {
                    return true
                }
            }
            if (this.checkDisabled()) {
                return true
            }
            if (!this.defaultMsg) {
                this.displayEmpty()
            } else {
                this.displayDefault()
            }
        };
        this.validate = function() {
            if (this.compareField || this.hasCallback || this.valid == false) {
                FormValidator.blurHandler(this)
            }
            return this.valid
        };
        this.setValid = function(bool) {
            if (bool) {
                this.valid = true;
                this.displayValid()
            } else {
                this.valid = false
            }
            return this
        };
        this.setFocus = function(showFocusMsg) {
            this.focusChangeMsg = showFocusMsg ? true: false;
            $(this.el.get(0)).focus();
            if (this.type == "hidden") {
                this.el.after(ValidatorForm.focusTmpInput);
                ValidatorForm.focusTmpInput.show();
                ValidatorForm.focusTmpInput.focus();
                ValidatorForm.focusTmpInput.hide()
            }
            return this
        };
        this.remove = function() {
            if (this.formId) {
                delete FormValidator.data[this.formId][this.fieldName]
            }
            delete FormValidator.validators[this.fullFieldName]
        };
        this.isValid = function() {
            if (this.valid == false && this.checkDisabled() == false) {
                return false
            }
            return true
        }
    }
    ValidatorElement.count = 1;
    ValidatorElement.tipSpanIds = [];
    ValidatorElement.getElByFullFieldName = function(fullFieldName) {
        if (fullFieldName.indexOf("::") != -1) {
            fieldNames = fieldName.split("::");
            fieldName = fieldNames[1];
            el = $("[name='" + fieldName + "']", $("#" + fieldNames[1]))
        } else {
            el = $("[name='" + fullFieldName + "']")
        }
        return el
    };
    var FormValidator = {
        forms: [],
        validators: [],
        types: {},
        data: [],
        getFormId: function(formObj) {
            if (formObj && formObj.size() == 0) {
                return null
            }
            var formId = formObj.data("validatorFormId");
            if (!formId) {
                formId = ValidatorForm.count++;
                formObj.data("validatorFormId", formId);
                FormValidator.data[formId] = [];
                $("[name]", formObj).each(function(fieldK, fieldV) {
                    jFieldV = $(fieldV);
                    tagN = jFieldV.attr("tagName").toLowerCase();
                    if (tagN == "input" || tagN == "select" || tagN == "textarea") {
                        FormValidator.data[formId][jFieldV.attr("name")] = null
                    }
                })
            }
            return formId
        },
        getCompareString: function(val) {
            if (val == "") {
                return '""'
            }
            if (!isNaN(val)) {
                if (/^0[0-9]+$/.test(val)) {
                    return '"' + val + '"'
                }
                return val
            }
            var lower = val.toLowerCase;
            if (lower == "false" || lower == "true" || lower == "null") {
                return val
            }
            return '"' + val + '"'
        },
        isEmpty: function(vObj) {
            var val = vObj.el.val();
            if ((vObj.type == "radio" || vObj.type == "checkbox") && $("[name='" + vObj.fieldName + "']:checked", vObj.parent).size() == 0) {
                return true
            }
            if (vObj.el.size() == 1) {
                if (val == "" || val == vObj.emptyValue || val == vObj.focusValue || val == vObj.defaultDbValue) {
                    return true
                }
            } else {
                if (vObj.el.size() > 1) {
                    var empty = true;
                    vObj.el.each(function() {
                        val = $(this).val();
                        if (val != "" && val != vObj.emptyValue && val != vObj.focusValue && val != vObj.defaultDbValue) {
                            empty = false
                        }
                    });
                    return empty
                }
            }
            return false
        },
        addForm: function(formObj) {
            var formId = this.getFormId(formObj);
            if (!formId) {
                return null
            }
            if (!this.forms[formId]) {
                var newForm = new ValidatorForm(formObj);
                newForm.formId = formId;
                var form = formObj.get(0);
                if (form && typeof form.onsubmit == "function") {
                    newForm.oldSubmitFunc = form.onsubmit;
                    form.onsubmit = null
                }
                formObj.bind("submit", this.submitHandler);
                this.forms[formId] = newForm
            }
            return this.forms[formId]
        },
        init: function() {
            var t = this,
            jFieldV,
            tagN,
            formE,
            formId;
            $("form").each(function(formK, formV) {
                formId = ValidatorElement.count++;
                $(formV).data("validatorFormId", formId);
                t.data[formId] = [];
                $("[name]", $(formV)).each(function(fieldK, fieldV) {
                    jFieldV = $(fieldV);
                    tagN = jFieldV.attr("tagName").toLowerCase();
                    if (tagN == "input" || tagN == "select" || tagN == "textarea") {
                        formE = fieldV.form || null;
                        if (formE) {
                            t.data[formId][jFieldV.attr("name")] = null
                        }
                    }
                })
            })
        },
        add: function(fieldName, defaultMsg, focusMsg, emptyValue, tipSpanId) {
            var vObj = new ValidatorElement(fieldName);
            if (defaultMsg != undefined) {
                vObj.setDefaultMsg(defaultMsg)
            }
            if (focusMsg != undefined) {
                vObj.setfocusMsg(focusMsg)
            }
            if (tipSpanId != undefined) {
                vObj.setTipSpanId(tipSpanId)
            }
            if (emptyValue != undefined) {
                vObj.setEmptyValue(emptyValue)
            }
            vObj.setInit();
            return vObj
        },
        focusHandler: function(vObj) {
            if (vObj.focused || vObj.checkDisabled()) {
                return true
            }
            vObj.valid = false;
            vObj.focused = true;
            if (vObj.focusChangeMsg) {
                vObj.displayFocus()
            } else {
                vObj.focusChangeMsg = true
            }
            var val = vObj.el.val();
            if ((vObj.type == "text" || vObj.el.attr("tagName").toLowerCase() == "textarea") && (val == "" || val == vObj.emptyValue || val == vObj.defaultDbValue)) {
                if (vObj.focusValue != "") {
                    if ((vObj.focusValue != vObj.emptyValue)) {
                        vObj.el.val(vObj.focusValue)
                    }
                } else {
                    vObj.el.val("")
                }
                if (vObj.emptyValue != "") {
                    vObj.el.css("color", vObj.oldColor)
                }
            }
            return true
        },
        blurHandler: function(vObj) {
            var val = vObj.el.val();
            if (typeof val == "string") {
                var val_trim = val.replace(/(^\s*)|(\s*$)/g, "");
                if (val != val_trim) {
                    vObj.el.val(val_trim);
                    val = val_trim
                }
            }
            if (vObj.emptyValue != "" && (vObj.type == "text" || vObj.el.attr("tagName").toLowerCase() == "textarea") && (val == "" || val == vObj.focusValue || val == vObj.defaultDbValue)) {
                vObj.el.val(vObj.emptyValue);
                vObj.el.css("color", vObj.emptyValueColor)
            }
            if (vObj.checkDisabled()) {
                return true
            }
            var compare,
            i,
            setting,
            toObj,
            match,
            funcName,
            msg,
            isEmpty = false;
            vObj.focused = false;
            if (!FormValidator.checkSameTipValidators(vObj)) {
                return true
            }
            var required = vObj.rules.required;
            if (required.msg && (!required.disabledCallback || !vObj.checkDisabled(required.disabledCallback))) {
                if (FormValidator.isEmpty(vObj)) {
                    vObj.displayError(required.msg);
                    return true
                }
                if (vObj.el.size() > 1) {
                    vObj.valid = true;
                    vObj.displayValid();
                    return true
                }
            } else {
                if (FormValidator.isEmpty(vObj)) {
                    if (!vObj.hasCallback && !vObj.compareField) {
                        vObj.valid = true;
                        if (vObj.sameTipsIsValid && !vObj.sameTipsIsEmpty) {
                            vObj.displayValid()
                        } else {
                            vObj.displayDefault()
                        }
                        return true
                    }
                    isEmpty = true
                }
            }
            for (i = 0; i < vObj.rules.others.length; i++) {
                setting = vObj.rules.others[i];
                if (setting) {
                    if (setting.mode == "minLength" || setting.mode == "maxLength" || setting.mode == "length") {
                        var length = (vObj.strlenType.toLowerCase() == "symbol" || vObj.serverCharset.toUpperCase() == "UTF-8") ? val.length: FormValidator.countByte(val)
                    }
                    switch (setting.mode) {
                    case "minLength":
                        if (!vObj.valid && !isEmpty && (!setting.disabledCallback || !vObj.checkDisabled(setting.disabledCallback))) {
                            eval("compare = " + length + " >= " + setting.minValue + " ? true : false;");
                            if (!compare) {
                                vObj.displayError(setting.msg);
                                return true
                            }
                        }
                        break;
                    case "maxLength":
                        if (!vObj.valid && !isEmpty && (!setting.disabledCallback || !vObj.checkDisabled(setting.disabledCallback))) {
                            eval("compare = " + length + " <= " + setting.maxValue + " ? true : false;");
                            if (!compare) {
                                vObj.displayError(setting.msg);
                                return true
                            }
                        }
                        break;
                    case "length":
                        if (!vObj.valid && !isEmpty && (!setting.disabledCallback || !vObj.checkDisabled(setting.disabledCallback))) {
                            eval("compare = " + length + " >= " + setting.minValue + " && " + length + " <= " + setting.maxValue + " ? true : false;");
                            if (!compare) {
                                vObj.displayError(setting.msg);
                                return true
                            }
                        }
                        break;
                    case "compareValue":
                        if (!vObj.valid && !isEmpty && (!setting.disabledCallback || !vObj.checkDisabled(setting.disabledCallback))) {
                            eval("compare = " + FormValidator.getCompareString(val) + " " + setting.operator + " " + FormValidator.getCompareString(setting.compareValue) + " ? true : false;");
                            if (!compare) {
                                vObj.displayError(setting.msg);
                                return true
                            }
                        }
                        break;
                    case "regexp":
                        if (!vObj.valid && !isEmpty && (!setting.disabledCallback || !vObj.checkDisabled(setting.disabledCallback))) {
                            match = new RegExp(setting.regexp).test(val);
                            if ((!setting.exclude && !match) || (setting.exclude && match)) {
                                vObj.displayError(setting.msg);
                                return true
                            }
                        }
                        break;
                    case "callback":
                        funcName = setting.funcName;
                        if (typeof funcName == "function" && (!setting.disabledCallback || !vObj.checkDisabled(setting.disabledCallback))) {
                            var func_ret = funcName(val);
                            if (func_ret === true) {} else {
                                if (func_ret === false) {
                                    vObj.displayError(setting.msg || "Unknown error");
                                    return true
                                } else {
                                    vObj.displayError(func_ret);
                                    return true
                                }
                            }
                        }
                        break;
                    case "compareField":
                        toObj = $.validator(setting.compareField);
                        if (toObj.el.length == 0) {
                            alert("Input '" + setting.compareField + "' is not exists");
                            return true
                        }
                        if ((!FormValidator.isEmpty(vObj) || !FormValidator.isEmpty(toObj)) && (!setting.disabledCallback || !vObj.checkDisabled(setting.disabledCallback))) {
                            eval("compare = " + FormValidator.getCompareString(val) + " " + setting.operator + " " + FormValidator.getCompareString(toObj.el.val()) + " ? true : false;");
                            if (!compare) {
                                vObj.displayError(setting.msg);
                                return true
                            }
                        }
                        break;
                    default:
                        break
                    }
                }
            }
            if (!vObj.valid && vObj.rules.ajax.url && (!vObj.rules.ajax.disabledCallback || !vObj.checkDisabled(vObj.rules.ajax.disabledCallback))) {
                if (!vObj.ajaxLoading) {
                    vObj.displayLoading();
                    vObj.ajaxLoading = true;
                    vObj.ajaxLoadStart = new Date().getTime();
                    vObj.rules.ajax.postData[vObj.shortInputName] = val;
                    $.ajax({
                        type: "post",
                        url: vObj.rules.ajax.url,
                        data: vObj.rules.ajax.postData,
                        success: function(data, textStatus) {
                            vObj.ajaxLoading = false;
                            var json = "";
                            try {
                                json = eval("(" + data + ")")
                            } catch(e) {
                                FormValidator.forms[vObj.formId].submitClicked = false;
                                vObj.displayError(data)
                            }
                            if (json === true) {
                                vObj.valid = true;
                                vObj.displayValid();
                                if (vObj.formId && FormValidator.forms[vObj.formId].submitClicked) {
                                    vObj.parent.submit()
                                }
                            } else {
                                FormValidator.forms[vObj.formId].submitClicked = false;
                                vObj.displayError(json == false ? vObj.rules.ajax.msg || "Unknown error": json)
                            }
                        },
                        complete: function(XMLHttpRequest, textStatus) {
                            vObj.ajaxLoading = false
                        },
                        error: function(XMLHttpRequest, textStatus, errorThrown) {
                            vObj.ajaxLoading = false;
                            if (textStatus == "timeout") {
                                textStatus = "超时，请重试。 "
                            }
                            FormValidator.forms[vObj.formId].submitClicked = false;
                            vObj.displayError(textStatus)
                        },
                        timeout: 3000
                    })
                }
            } else {
                vObj.valid = true;
                if (isEmpty && !vObj.sameTipsIsValid) {
                    vObj.displayDefault()
                } else {
                    vObj.displayValid()
                }
                return true
            }
        },
        submitHandler: function(e) {
            var jqForm = $(e.target);
            var formId = jqForm.data("validatorFormId");
            if (!formId || !FormValidator.forms[formId]) {
                return true
            }
            FormValidator.forms[formId].submitClicked = true;
            if (FormValidator.isLoading(formId)) {
                return false
            }
            if (!FormValidator.validateAll(formId)) {
                return false
            }
            var vForm = FormValidator.forms[formId];
            var callback_error = false,
            i;
            for (i = 0; i < vForm.formCallbacks.length; i++) {
                var funcName = vForm.formCallbacks[i];
                if (funcName() === false) {
                    callback_error = true
                }
            }
            if (callback_error) {
                return false
            }
            if (vForm.oldSubmitFunc) {
                var func = vForm.oldSubmitFunc;
                if (func() === false) {
                    return false
                }
            }
			
            FormValidator.forms[formId].submitClicked = false;
			//alert(1);
			//FormValidator.resetById(formId);

            return true
        },
        checkSameTipValidators: function(vObj) {
            vObj.sameTipsIsEmpty = true;
            if (vObj.checkSameTipField && ValidatorElement.tipSpanIds[vObj.tipSpanId].length > 1) {
                var i,
                fieldName,
                obj,
                el;
                for (i = 0; i < ValidatorElement.tipSpanIds[vObj.tipSpanId].length; i++) {
                    fieldName = ValidatorElement.tipSpanIds[vObj.tipSpanId][i];
                    if (fieldName != vObj.fullFieldName) {
                        el = ValidatorElement.getElByFullFieldName(fieldName);
                        if (el.size() > 0) {
                            obj = $.validator(fieldName);
                            if (obj.focused == false) {
                                if (!FormValidator.isEmpty(obj)) {
                                    vObj.sameTipsIsEmpty = false
                                }
                                obj.checkSameTipField = false;
                                obj.validate();
                                obj.checkSameTipField = true;
                                if (!obj.valid) {
                                    vObj.sameTipsIsValid = false;
                                    return false
                                }
                            }
                        }
                    }
                }
                vObj.sameTipsIsValid = true
            }
            if (!vObj.sameTipsIsEmpty) {
                vObj.displayValid()
            }
            return true
        },
        isLoading: function(formId) {
            var id,
            vObj;
            for (id in FormValidator.data[formId]) {
                vObj = FormValidator.validators[id];
                if (vObj && vObj.formId && vObj.formId == formId && vObj.rules.ajax && vObj.valid == false && vObj.ajaxLoading && new Date().getTime() - vObj.ajaxLoadStart < 3000) {
                    FormValidator.focusNoMessage(vObj);
                    return true
                }
            }
            return false
        },
        validateAll: function(formId) {
            var id,
            vObj,
            firstErrorObj;
            if (this.data[formId]) {
                for (id in this.data[formId]) {
                    vObj = FormValidator.validators[id];
                    if (vObj && vObj.formId && vObj.formId == formId && (!vObj.hasAjax || vObj.valid == false)) {
                        vObj.el.blur();
                        if (!vObj.isValid() && !firstErrorObj) {
                            firstErrorObj = vObj;
                            if (!vObj.ajaxLoading) {
                                FormValidator.forms[formId].submitClicked = false
                            }
                        }
                    }
                }
            }
            if (firstErrorObj) {
                FormValidator.focusNoMessage(firstErrorObj);
                return false
            }
            return true
        },
        resetById: function(formId) {
            var id,
            vObj;
            if (this.data[formId]) {
                for (id in this.data[formId]) {
                    vObj = FormValidator.validators[id];
                    if (vObj && vObj.formId && vObj.formId == formId) {
                        vObj.displayDefault();
                        vObj.setValid(false)
                    }
                }
            }
        },
        reset: function(form) {
            var formEl = FormValidator.getForm(form);
            if (!formEl) {
                alert("'FormValidator.reset():'未检测到form");
                return false
            }
            var newForm = this.addForm(formEl);
            FormValidator.resetById(newForm.formId)
        },
        focusNoMessage: function(vObj) {
            vObj.setFocus(false)
        },
        getForm: function(form) {
            var formEl = null;
            if (!form) {
                return formEl
            }
            if (typeof form == "object") {
                formEl = $(form)
            } else {
                if (typeof form == "string") {
                    formEl = $("#" + form);
                    if (formEl.size() == 0) {
                        formEl = $("form[name='" + form + "']");
                        if (formEl.size() == 0) {
                            formEl = null
                        }
                    }
                }
            }
            return formEl
        },
        addFormCallback: function(form, funcName) {
            var formEl = FormValidator.getForm(form);
            if (!formEl) {
                alert("'FormValidator.addFormCallback():'表单检测程序必须置于form表单之后");
                return false
            }
            var newForm = this.addForm(formEl);
            var formId = newForm.formId;
            FormValidator.forms[formId].formCallbacks[FormValidator.forms[formId].formCallbacks.length] = funcName
        },
        setFormSubmitClicked: function(form, bool) {
            var formEl = FormValidator.getForm(form);
            if (!formEl) {
                alert("'FormValidator.addFormCallback():'表单检测程序必须置于form表单之后");
                return false
            }
            var newForm = this.addForm(formEl);
            var formId = newForm.formId;
            FormValidator.forms[formId].submitClicked = bool
        },
        countByte: function(val) {
            var cnRegex = /[^\x00-\xff]/g;
            var strLength = val.replace(cnRegex, "**").length;
            return strLength
        }
    };
    FormValidator.init();
    $.fn.extend({
        validator: function(defaultMsg, focusMsg, emptyValue, tipSpanId) {
            return FormValidator.add($(this), defaultMsg, focusMsg, emptyValue, tipSpanId)
        },
        removeValidator: function() {
            FormValidator.remove($(this));
            return $
        },
        addFormCallback: function(funcName) {
            FormValidator.addFormCallback($(this), funcName);
            return $
        }
    });
    $.validator = function(fieldName, defaultMsg, focusMsg, emptyValue, tipSpanId) {
        return FormValidator.add(fieldName, defaultMsg, focusMsg, emptyValue, tipSpanId)
    };
    $.validator.addFormCallback = function(form, funcName) {
        FormValidator.addFormCallback(form, funcName);
        return $
    };
    $.validator.setFormSubmitClicked = function(form, bool) {
        FormValidator.setFormSubmitClicked(form, bool);
        return $
    };
    $.validator.reset = function(form) {
        FormValidator.reset(form);
        return $
    }
})();
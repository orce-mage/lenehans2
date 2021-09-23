(function (mod) {
    if (typeof exports == "object" && typeof module == "object") // CommonJS
        mod(require("../../lib/codemirror"), require("../../addon/mode/multiplex"), require("../htmlmixed/htmlmixed"), require("../clike/clike"));
    else if (typeof define == "function" && define.amd) // AMD
        define(["../../lib/codemirror", "../../addon/mode/multiplex", "../htmlmixed/htmlmixed", "../clike/clike"], mod);
    else // Plain browser env
        mod(CodeMirror);
})(function (CodeMirror) {
    "use strict";
    CodeMirror.defineMode("magento:inner", function () {
        var keywords = [],
            variables = [],
            keywords_attributes = {
                "template": ["config_path"],
                "depend": [],
                "\/depend": [],
                "if": [],
                "else": [],
                "\/if": [],
                "snm_set": [],
                "\/snm_set": [],
                "snm_when": [],
                "snm_otherwise": [],
                "\/snm_when": [],
                "widget": [],
                "block": ["type", "output", "snm"],
                "config": ["path"],
                "htmlescape": ["var"],
                "var": [],
                "view": ["url"],
                "media": ["url"],
                "skin": ["url"],
                "store": ["url", "direct_url"],
                "trans": ["url", "direct_url"],
                "css": ["file"],
                "protocol": ["url", "http", "https"],
                "customvar": ["code"],
                "inlinecss": ["file"],
                "layout": ["handle", "variable", "variable", "method"],
            },
            operator = /^[+\-*&%=<>!?|~^]/,
            sign = /^[:\[\(\{]/,
            atom = ["true", "false", "null", "empty", "defined", "divisibleby", "divisible by", "even", "odd", "iterable", "sameas", "same as"],
            number = /^(\d[+\-\*\/])?\d+(\.\d+)?/;
        for (var k in keywords_attributes) {
            keywords.push(k);
            var v = keywords_attributes[k];
            for (var i = 0; i < v.length; i++)
                variables.push(v[i]);
        }

        keywords = new RegExp("((" + keywords.join(")|(") + "))\\b");
        atom = new RegExp("((" + atom.join(")|(") + "))\\b");
        variables = new RegExp("((" + variables.join(")|(") + "))\\b");
        function tokenBase(stream, state) {
            var ch = stream.peek();

            if (state.intag) {

                //After operator
                if (state.operator) {
                    state.operator = false;
                    if (stream.match(atom)) {
                        return "atom";
                    }
                    if (stream.match(number)) {
                        return "number";
                    }
                }
                //After sign
                if (state.sign) {
                    state.sign = false;
                    if (stream.match(atom)) {
                        return "atom";
                    }
                    if (stream.match(number)) {
                        return "number";
                    }
                }

                if (state.instring) {
                    if (ch == state.instring) {
                        state.instring = false;
                    }
                    stream.next();
                    return "string";
                } else if (ch == "'" || ch == '"') {
                    state.instring = ch;
                    stream.next();
                    return "string";
                } else if (stream.match("}}")) {
                    state.intag = false;
                    state.hasKeyword = false;
                    return "tag";
                } else if (stream.match(operator)) {
                    state.operator = true;
                    return "operator";
                } else if (stream.match(sign)) {
                    state.sign = true;
                } else {
                    var k;
                    if (k = stream.match(keywords)) {
                        state.hasKeyword = true;
                        state.lastKeyword = k[0];
                        //console.log("Match:"+state.lastKeyword);
                        return "keyword";
                    }
                    if (stream.match(variables)) {
                        return "variable";
                    }
                    if (stream.match(atom)) {
                        return "atom";
                    }
                    if (stream.match(number)) {
                        return "number";
                    }

                    stream.next();
                    if (!state.hasKeyword)
                        return "error";
                    return "";
                }
                return "variable";
            } else if (stream.eat("{")) {
                //Open tag
                if (ch = stream.eat(/\{/)) {
                    //Cache close tag
                    state.intag = true;
                    state.hasKeyword = false;
                    return "tag";
                }
            }
            stream.next();
        };

        return {
            startState: function () {
                return {};
            },
            token: function (stream, state) {
                return tokenBase(stream, state);
            }
        };
    });

    CodeMirror.defineMode("magento", function (config, parserConfig) {
        return CodeMirror.multiplexingMode(CodeMirror.getMode(config, "htmlmixed"), {
            open: /\{\{/,
            close: /\}\}/,
            mode: CodeMirror.getMode(config, "magento:inner"), parseDelimiters: true
        });
    }, "htmlmixed");
    CodeMirror.defineMIME("text/x-magento", "magento");
});

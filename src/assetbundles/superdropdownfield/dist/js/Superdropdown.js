/**
 * Super dropdown plugin for Craft CMS
 *
 * Superdropdown Field JS
 *
 * @author    veryfine
 * @copyright Copyright (c) 2020 veryfine
 * @link      https://github.com/veryfinework
 * @package   Superdropdown
 * @since     1.0.0
 */
;(function ( window, document ) {

    function Superdropdown(options) {

        if (typeof options.id === "string") {
            this.element = document.getElementById(options.id);
        } else {
            this.element = options.element;
        }
        this.options = options;

        this.init();
    }

    Superdropdown.prototype = {

        selects: [],
        activeChildrenOfSelect: {},

        init() {

            const _self = this;

            this.selects = this.element.querySelectorAll("select");

            this.selects.forEach(function(select) {

                select.addEventListener('change', _self.handleChange.bind(_self) );

                // set initial value
                const value = select.getAttribute('data-initialvalue');
                select.selectedIndex = parseInt(value);

                // set up initial states for children
                if (select.classList.contains('type-primary')) {
                    const selectedOption = select.selectedOptions[0];
                    if (selectedOption) {
                        _self.showSelect(select, selectedOption);
                    }
                }

            });

            console.log('find me');

        },

        handleChange(e) {

            const select = e.target;
            const selectedOption = select.selectedOptions[0];

            this.removeChildSelects(select);

            this.showSelect(select, selectedOption);


        },

        showSelect(select, selectedOption) {

            const _self = this;

            const targetData = selectedOption.getAttribute('data-trgt');

            if (targetData !== null) {

                const selectKey = select.getAttribute('data-key');

                const targets = targetData.split(',');

                targets.forEach(function(target) {

                    // show target dropdown & set its first option as selected
                    const childSelect = _self.element.querySelector('[data-key="' + target + '"]');
                    childSelect.closest('.sd-selectWrap').classList.add("isActive");
                    // set the first option as selected if there is no selection
                    if (childSelect.selectedIndex === -1) {
                        childSelect.selectedIndex = 0;
                    }
                    childSelect.setAttribute('data-initialvalue', childSelect.selectedIndex);

                    // initialize child selects array if has not been
                    if (!_self.activeChildrenOfSelect.hasOwnProperty(select.id)) {
                        _self.activeChildrenOfSelect[select.id] = []
                    }

                    // register child select on this select
                    _self.activeChildrenOfSelect[select.id].push( childSelect );

                    // show children
                    const childSelectedOption = childSelect.selectedOptions[0];
                    _self.showSelect(childSelect, childSelectedOption);

                });
            }
        },

        removeSelect(select) {

            select.closest('.sd-selectWrap').classList.remove("isActive");
            select.selectedIndex = -1;

            this.removeChildSelects(select);

        },

        removeChildSelects(select) {

            const _self = this;

            // if a child select is registered on this select, then remove it
            if (this.activeChildrenOfSelect.hasOwnProperty(select.id)) {
                this.activeChildrenOfSelect[select.id].forEach(function(childSelect) {
                    // remove child from array
                    _self.removeSelect(childSelect);
                });
            }

            // reset to empty
            this.activeChildrenOfSelect[select.id] = [];
        },

        getInputValue() {

            let value = [];
            this.element.querySelectorAll("select.isActive").forEach(function(dropdown) {
                const strParts = dropdown.getAttribute('id').split(/\[(.*?)\]/);
                const key = strParts[strParts.length-2];

                value[key] = dropdown.selectedOptions[0].value;
            });

            return value;

        }

    };

    window.CE_Superdropdown = function( options ) {
        return new Superdropdown( options )
    }

})(window, document);

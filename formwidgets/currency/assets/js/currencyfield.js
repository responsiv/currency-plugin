/*
 * Currency field plugin
 *
 * Data attributes:
 * - data-control="currencyfield" - enables the plugin on an element
 * - data-converted-value="12.50" - the auto-converted value to restore on clear
 *
 * Handles the override/clear toggle for currencyable fields on non-default
 * currency sites. A hidden input carries the posted value: the override value
 * when active, or an empty string when cleared (which deletes the sidecar row).
 * The visible input is disabled when cleared, showing the converted value.
 */

'use strict';

oc.registerControl('currencyfield', class extends oc.ControlBase {
    init() {
        this.$el = $(this.element);
        this.$input = this.$el.find('[data-currency-input]');
        this.$hidden = this.$el.find('[data-currency-hidden]');
        this.$overrideLink = this.$el.find('[data-currency-override]');
        this.$clearLink = this.$el.find('[data-currency-clear]');
        this.convertedValue = this.config.convertedValue || '';
    }

    connect() {
        this.$overrideLink.on('click', this.proxy(this.onOverride));
        this.$clearLink.on('click', this.proxy(this.onClear));
        this.$input.on('input', this.proxy(this.onInput));
    }

    disconnect() {
        this.$overrideLink.off('click', this.proxy(this.onOverride));
        this.$clearLink.off('click', this.proxy(this.onClear));
        this.$input.off('input', this.proxy(this.onInput));
    }

    onOverride() {
        this.$input.prop('disabled', false);
        this.$hidden.val(this.$input.val());
        this.$overrideLink.addClass('d-none');
        this.$clearLink.removeClass('d-none');
        setTimeout(() => { this.$input.focus(); }, 0);
    }

    onClear() {
        this.$hidden.val('');
        this.$input.val(this.convertedValue);
        this.$input.prop('disabled', true);
        this.$clearLink.addClass('d-none');
        this.$overrideLink.removeClass('d-none');
    }

    onInput() {
        this.$hidden.val(this.$input.val());
    }
});

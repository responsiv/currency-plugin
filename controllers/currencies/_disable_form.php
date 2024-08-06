<?= Form::open(['id' => 'disableForm']) ?>
    <?php foreach ($checked as $id): ?>
        <input type="hidden" name="checked[]" value="<?= $id ?>" />
    <?php endforeach ?>
    <div class="modal-header">
        <h4 class="modal-title"><?= __("Enable or Disable Currencies") ?></h4>
        <button type="button" class="btn-close" data-dismiss="popup"></button>
    </div>
    <div class="modal-body">

        <?php if ($this->fatalError): ?>
            <p class="flash-message static error"><?= $fatalError ?></p>
        <?php endif ?>

        <p><?= __("Currencies selected: :amount", ['amount' => count($checked)]) ?></p>

        <div class="form-preview">
            <div class="form-group">
                <!-- Checkbox -->
                <div class="form-check">
                    <input
                        type="checkbox"
                        name="enable"
                        value="1"
                        class="form-check-input"
                        id="currencyDisable">
                    <label for="currencyDisable" class="form-check-label">
                        <?= __("Enabled") ?>
                    </label>
                    <p class="help-block mb-0"><?= __("Disabled currencies are not visible on the front-end.") ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <button
            type="submit"
            class="btn btn-primary"
            data-request="onDisableCurrencies"
            data-request-confirm="<?= e(trans('backend::lang.form.action_confirm')) ?>"
            data-stripe-load-indicator>
            <?= __("Apply") ?>
        </button>
        <button
            type="button"
            class="btn btn-default"
            data-dismiss="popup">
            <?= __("Cancel") ?>
        </button>
    </div>
<?= Form::close() ?>

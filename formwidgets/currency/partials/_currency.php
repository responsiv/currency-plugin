<?php if ($this->previewMode): ?>
    <div class="form-control">
        <?= e(Currency::format($value, ['baseValue' => false, 'format' => $format])) ?>
    </div>
<?php else: ?>
    <div
        id="<?= $this->getId('group') ?>"
        class="<?php if ($isCurrencyOverridable): ?>position-relative<?php endif ?>"
        <?php if ($isCurrencyOverridable): ?>
            data-control="currencyfield"
            data-converted-value="<?= e($convertedValue) ?>"
        <?php endif ?>
    >
        <?php if ($isCurrencyOverridable): ?>
            <input
                type="hidden"
                name="<?= $name ?>"
                value="<?= $isCurrencyReadOnly ? '' : e($value) ?>"
                data-currency-hidden
                />
        <?php endif ?>
        <div class="input-group">
            <?php if ($symbolBefore): ?>
                <span
                    class="input-group-addon input-group-text"
                    <?php if ($currencyCode): ?>data-tooltip-text="<?= e($currencyCode) ?>"<?php endif ?>
                ><?= e($symbol) ?></span>
            <?php endif ?>
            <?php if ($isCurrencyOverridable): ?>
                <input
                    type="number"
                    step="any"
                    id="<?= $this->getId('textarea') ?>"
                    value="<?= e($value) ?>"
                    placeholder="<?= e(trans($field->placeholder)) ?>"
                    class="form-control input-no-spinner"
                    autocomplete="off"
                    <?php if ($isCurrencyReadOnly): ?>disabled<?php endif ?>
                    data-currency-input
                    <?= $field->getAttributes() ?>
                    />
            <?php else: ?>
                <input
                    type="number"
                    step="any"
                    name="<?= $name ?>"
                    id="<?= $this->getId('textarea') ?>"
                    value="<?= e($value) ?>"
                    placeholder="<?= e(trans($field->placeholder)) ?>"
                    class="form-control input-no-spinner"
                    autocomplete="off"
                    <?= $field->getAttributes() ?>
                    />
            <?php endif ?>
            <?php if (!$symbolBefore): ?>
                <span class="input-group-addon input-group-text"><?= e($symbol) ?></span>
            <?php endif ?>
        </div>
        <?php if ($isCurrencyOverridable): ?>
            <?php
                $linkRight = $symbolBefore ? '10px' : '50px';
            ?>
            <a
                href="javascript:;"
                class="text-muted small position-absolute <?php if (!$hasCurrencyOverride): ?>d-none<?php endif ?>"
                style="right: <?= $linkRight ?>; top: 50%; transform: translateY(-50%); z-index: 5;"
                data-currency-clear
            ><?= __("Clear") ?></a>
            <a
                href="javascript:;"
                class="text-muted small position-absolute <?php if ($hasCurrencyOverride): ?>d-none<?php endif ?>"
                style="right: <?= $linkRight ?>; top: 50%; transform: translateY(-50%); z-index: 5;"
                data-currency-override
            ><?= __("Override") ?></a>
        <?php endif ?>
    </div>
<?php endif ?>
